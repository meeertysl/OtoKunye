<?php

namespace App\Http\Controllers;

use App\Jobs\RunMaintenanceReminderForVehicleJob;
use App\Jobs\SyncParasutInvoiceJob;
use App\Models\Part;
use App\Models\ServiceRecord;
use App\Models\Vehicle;
use App\Services\AuditLogger;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceRecordController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService)
    {
        $this->middleware('auth')->except('approveFromPublic');
    }

    public function store(Request $request, Vehicle $vehicle)
    {
        $this->ensureVehicleOwnedByAuthenticatedUser($vehicle);
        $validated = $this->validatePayload($request);
        $serviceItems = $this->normalizeServiceItems($validated['service_items']);

        $beforeImagePath = null;
        $afterImagePath = null;

        if ($request->hasFile('before_image')) {
            $beforeImagePath = Storage::disk('public')->putFile('service-records/before', $request->file('before_image'));
        }

        if ($request->hasFile('after_image')) {
            $afterImagePath = Storage::disk('public')->putFile('service-records/after', $request->file('after_image'));
        }

        try {
            DB::transaction(function () use ($validated, $vehicle, $request, $beforeImagePath, $afterImagePath, $serviceItems) {
                $record = ServiceRecord::create([
                    'vehicle_id' => $vehicle->id,
                    'user_id' => $request->user()->id,
                    'branch_id' => $vehicle->branch_id ?: $request->user()?->branch_id,
                    'entry_date' => $validated['entry_date'],
                    'exit_date' => $validated['exit_date'] ?? null,
                    'master_note' => $validated['master_note'] ?? null,
                    'customer_approval_status' => $validated['customer_approval_status'] ?? false,
                    'estimated_amount' => $validated['estimated_amount'] ?? null,
                    'next_service_date' => $validated['next_service_date'] ?? null,
                    'next_service_km' => $validated['next_service_km'] ?? null,
                    'before_image_path' => $beforeImagePath,
                    'after_image_path' => $afterImagePath,
                ]);

                $record->serviceItems()->createMany($serviceItems);
                $this->inventoryService->applyForServiceRecord($record, collect($serviceItems), (int) $request->user()->id);
                AuditLogger::log(
                    'service_record_created',
                    'service_record',
                    $record->id,
                    $vehicle->license_plate.' için bakım kaydı oluşturuldu.',
                    null,
                    $record->only(['entry_date', 'exit_date', 'estimated_amount', 'next_service_date', 'next_service_km']),
                    ['vehicle_id' => $vehicle->id]
                );
            });
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['stock' => $exception->getMessage()])->withInput();
        }

        RunMaintenanceReminderForVehicleJob::dispatchSync($vehicle->id);

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', 'Bakım kaydı ve fiş kalemleri oluşturuldu.');
    }

    public function edit(Vehicle $vehicle, ServiceRecord $serviceRecord): View
    {
        $this->ensureVehicleOwnedByAuthenticatedUser($vehicle);
        $this->ensureRecordBelongsToVehicle($vehicle, $serviceRecord);
        $serviceRecord->load('serviceItems');
        $parts = Part::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sale_price']);

        return view('service-records.edit', compact('vehicle', 'serviceRecord', 'parts'));
    }

    public function update(Request $request, Vehicle $vehicle, ServiceRecord $serviceRecord): RedirectResponse
    {
        $this->ensureVehicleOwnedByAuthenticatedUser($vehicle);
        $this->ensureRecordBelongsToVehicle($vehicle, $serviceRecord);
        $validated = $this->validatePayload($request);
        $serviceItems = $this->normalizeServiceItems($validated['service_items']);
        $previousNextServiceDate = optional($serviceRecord->next_service_date)->toDateString();
        $previousNextServiceKm = $serviceRecord->next_service_km;

        $beforeImagePath = $serviceRecord->before_image_path;
        $afterImagePath = $serviceRecord->after_image_path;

        if ($request->hasFile('before_image')) {
            if ($beforeImagePath) {
                Storage::disk('public')->delete($beforeImagePath);
            }
            $beforeImagePath = Storage::disk('public')->putFile('service-records/before', $request->file('before_image'));
        }

        if ($request->hasFile('after_image')) {
            if ($afterImagePath) {
                Storage::disk('public')->delete($afterImagePath);
            }
            $afterImagePath = Storage::disk('public')->putFile('service-records/after', $request->file('after_image'));
        }

        try {
            DB::transaction(function () use ($validated, $serviceRecord, $beforeImagePath, $afterImagePath, $vehicle, $serviceItems, $request) {
                $beforeData = $serviceRecord->only(['entry_date', 'exit_date', 'estimated_amount', 'next_service_date', 'next_service_km', 'customer_approval_status']);
                $dateChanged = ($validated['next_service_date'] ?? null) !== optional($serviceRecord->next_service_date)->toDateString();
                $kmChanged = (int) ($validated['next_service_km'] ?? -1) !== (int) ($serviceRecord->next_service_km ?? -1);

                $this->inventoryService->revertForServiceRecord($serviceRecord, (int) $request->user()->id, 'Bakım kaydı güncelleme iadesi');

                $serviceRecord->update([
                    'entry_date' => $validated['entry_date'],
                    'exit_date' => $validated['exit_date'] ?? null,
                    'master_note' => $validated['master_note'] ?? null,
                    'customer_approval_status' => $validated['customer_approval_status'] ?? false,
                    'estimated_amount' => $validated['estimated_amount'] ?? null,
                    'next_service_date' => $validated['next_service_date'] ?? null,
                    'next_service_km' => $validated['next_service_km'] ?? null,
                    'reminder_sent_for_date' => $dateChanged ? null : $serviceRecord->reminder_sent_for_date,
                    'reminder_sent_for_km' => $kmChanged ? null : $serviceRecord->reminder_sent_for_km,
                    'before_image_path' => $beforeImagePath,
                    'after_image_path' => $afterImagePath,
                ]);

                $serviceRecord->serviceItems()->delete();
                $serviceRecord->serviceItems()->createMany($serviceItems);
                $this->inventoryService->applyForServiceRecord($serviceRecord, collect($serviceItems), (int) $request->user()->id);
                AuditLogger::log(
                    'service_record_updated',
                    'service_record',
                    $serviceRecord->id,
                    $vehicle->license_plate.' için bakım kaydı güncellendi.',
                    $beforeData,
                    $serviceRecord->fresh()->only(['entry_date', 'exit_date', 'estimated_amount', 'next_service_date', 'next_service_km', 'customer_approval_status']),
                    ['vehicle_id' => $vehicle->id]
                );
            });
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['stock' => $exception->getMessage()])->withInput();
        }

        $nextServiceDateChanged = ($validated['next_service_date'] ?? null) !== $previousNextServiceDate;
        $nextServiceKmChanged = (int) ($validated['next_service_km'] ?? -1) !== (int) ($previousNextServiceKm ?? -1);
        if ($nextServiceDateChanged || $nextServiceKmChanged) {
            RunMaintenanceReminderForVehicleJob::dispatchSync($vehicle->id);
        }

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', 'Bakım kaydı güncellendi.');
    }

    public function destroy(Request $request, Vehicle $vehicle, ServiceRecord $serviceRecord): RedirectResponse
    {
        $this->ensureVehicleOwnedByAuthenticatedUser($vehicle);
        $this->ensureRecordBelongsToVehicle($vehicle, $serviceRecord);

        DB::transaction(function () use ($serviceRecord, $vehicle, $request) {
            $beforeData = $serviceRecord->only(['entry_date', 'exit_date', 'estimated_amount', 'next_service_date', 'next_service_km', 'customer_approval_status']);
            $this->inventoryService->revertForServiceRecord($serviceRecord, (int) $request->user()->id, 'Bakım kaydı silme iadesi');
            if ($serviceRecord->before_image_path) {
                Storage::disk('public')->delete($serviceRecord->before_image_path);
            }

            if ($serviceRecord->after_image_path) {
                Storage::disk('public')->delete($serviceRecord->after_image_path);
            }

            $serviceRecord->serviceItems()->delete();
            $serviceRecord->delete();
            AuditLogger::log(
                'service_record_deleted',
                'service_record',
                $serviceRecord->id,
                $vehicle->license_plate.' için bakım kaydı silindi.',
                $beforeData,
                null,
                ['vehicle_id' => $vehicle->id]
            );
        });

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', 'Bakım kaydı silindi.');
    }

    public function requestInvoiceSync(Vehicle $vehicle, ServiceRecord $serviceRecord): RedirectResponse
    {
        $this->ensureVehicleOwnedByAuthenticatedUser($vehicle);
        $this->ensureRecordBelongsToVehicle($vehicle, $serviceRecord);

        $serviceRecord->update([
            'invoice_status' => 'kuyrukta',
            'invoice_requested_at' => now(),
            'invoice_last_error' => null,
        ]);
        AuditLogger::log(
            'invoice_sync_requested',
            'service_record',
            $serviceRecord->id,
            $vehicle->license_plate.' için e-fatura senkronu talep edildi.',
            null,
            ['invoice_status' => 'kuyrukta'],
            ['vehicle_id' => $vehicle->id]
        );

        SyncParasutInvoiceJob::dispatchAfterResponse($serviceRecord->id);

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', 'E-fatura çekme isteği sıraya alındı.');
    }

    public function approveFromPublic(Request $request, string $uuid, ServiceRecord $serviceRecord)
    {
        $serviceRecord->loadMissing('vehicle');
        $vehicle = $serviceRecord->vehicle;

        if (! $vehicle || $vehicle->uuid !== $uuid) {
            abort(404);
        }

        if (! $serviceRecord->customer_approval_status) {
            $serviceRecord->update([
                'customer_approval_status' => true,
            ]);
            AuditLogger::log(
                'service_record_customer_approved',
                'service_record',
                $serviceRecord->id,
                $vehicle->license_plate.' için müşteri onayı verildi.',
                ['customer_approval_status' => false],
                ['customer_approval_status' => true],
                ['vehicle_id' => $vehicle->id, 'public' => true]
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Bakım kaydı onaylandı.',
                'status' => 'approved',
            ]);
        }

        return redirect()
            ->route('vehicles.public.show', $uuid)
            ->with('success', 'Bakım işlemleri onaylandı.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate(
            [
                'entry_date' => ['required', 'date'],
                'exit_date' => ['nullable', 'date', 'after_or_equal:entry_date'],
                'master_note' => ['nullable', 'string'],
                'customer_approval_status' => ['nullable', 'boolean'],
                'estimated_amount' => ['nullable', 'numeric', 'min:0'],
                'next_service_date' => ['nullable', 'date', 'after_or_equal:entry_date'],
                'next_service_km' => ['nullable', 'integer', 'min:0'],
                'before_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'after_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'service_items' => ['required', 'array', 'min:1'],
                'service_items.*.part_name' => ['required', 'string', 'max:150'],
                'service_items.*.inventory_part_id' => ['nullable', 'integer', 'exists:parts,id'],
                'service_items.*.quantity' => ['nullable', 'numeric', 'min:0.01'],
                'service_items.*.use_inventory' => ['nullable', 'boolean'],
                'service_items.*.part_name' => ['nullable', 'string', 'max:150', 'required_without:service_items.*.inventory_part_id'],
                'service_items.*.labor_or_part_fee' => ['nullable', 'numeric', 'min:0'],
            ],
            [
                'service_items.required' => 'En az bir işlem/parça kalemi eklemelisiniz.',
                'service_items.array' => 'İşlem/parça kalemleri geçerli formatta değil.',
                'service_items.min' => 'En az bir işlem/parça kalemi eklemelisiniz.',
                'service_items.*.part_name.required' => 'İşlem/parça adı zorunludur.',
                'service_items.*.part_name.required_without' => 'Stok parçası seçilmediyse işlem/parça adı zorunludur.',
                'service_items.*.part_name.max' => 'İşlem/parça adı en fazla :max karakter olabilir.',
                'service_items.*.labor_or_part_fee.numeric' => 'İşçilik/parça ücreti sayı olmalıdır.',
                'service_items.*.labor_or_part_fee.min' => 'İşçilik/parça ücreti 0 veya daha büyük olmalıdır.',
                'service_items.*.inventory_part_id.exists' => 'Seçilen stok parçası bulunamadı.',
                'service_items.*.quantity.numeric' => 'Miktar sayı olmalıdır.',
                'service_items.*.quantity.min' => 'Miktar 0.01 veya daha büyük olmalıdır.',
            ],
            [
                'service_items.*.part_name' => 'işlem/parça adı',
                'service_items.*.labor_or_part_fee' => 'işçilik/parça ücreti',
            ]
        );
    }

    private function ensureRecordBelongsToVehicle(Vehicle $vehicle, ServiceRecord $serviceRecord): void
    {
        if ($serviceRecord->vehicle_id !== $vehicle->id) {
            abort(404);
        }
    }

    private function ensureVehicleOwnedByAuthenticatedUser(Vehicle $vehicle): void
    {
        $currentUser = Auth::user();
        if ($currentUser instanceof \App\Models\User && $currentUser->isAdmin()) {
            return;
        }

        if ($vehicle->owner_user_id !== Auth::id()) {
            abort(403);
        }

        if ($currentUser && $currentUser->branch_id && $vehicle->branch_id !== $currentUser->branch_id) {
            abort(403);
        }
    }

    private function normalizeServiceItems(array $items): array
    {
        return collect($items)->map(function (array $item) {
            $inventoryPartId = isset($item['inventory_part_id']) && $item['inventory_part_id'] !== '' ? (int) $item['inventory_part_id'] : null;
            $quantity = isset($item['quantity']) && $item['quantity'] !== '' ? (float) $item['quantity'] : 1.0;
            $useInventory = (bool) ($item['use_inventory'] ?? false);
            $partName = trim((string) ($item['part_name'] ?? ''));
            $fee = isset($item['labor_or_part_fee']) && $item['labor_or_part_fee'] !== '' ? (float) $item['labor_or_part_fee'] : 0.0;

            if ($inventoryPartId) {
                $part = Part::query()->find($inventoryPartId);
                if ($part) {
                    $partName = $part->name;
                    if ($fee <= 0) {
                        $fee = (float) $part->sale_price * $quantity;
                    }
                }
            }

            return [
                'inventory_part_id' => $inventoryPartId,
                'part_name' => $partName,
                'quantity' => $quantity,
                'labor_or_part_fee' => $fee,
                'use_inventory' => $useInventory && $inventoryPartId !== null,
            ];
        })->values()->all();
    }
}
