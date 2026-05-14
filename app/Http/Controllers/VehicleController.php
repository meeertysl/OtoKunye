<?php

namespace App\Http\Controllers;

use App\Models\ServiceRecord;
use App\Models\Part;
use App\Models\UserNotification;
use App\Models\Vehicle;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('publicShow');
    }

    public function index()
    {
        $search = trim((string) request('q', ''));
        $userId = Auth::id();
        $currentUser = Auth::user();
        $isAdmin = $currentUser instanceof \App\Models\User && $currentUser->isAdmin();
        $branchId = $currentUser?->branch_id;
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $quickFilter = (string) request('filter', '');
        $notificationItems = collect();
        $unreadNotificationCount = 0;

        if ($currentUser instanceof \App\Models\User) {
            $this->syncNotificationsForUser($currentUser, $isAdmin);
            $notificationItems = $currentUser->notifications()
                ->with('vehicle')
                ->latest()
                ->limit(8)
                ->get();
            $unreadNotificationCount = $currentUser->notifications()
                ->where('is_read', false)
                ->count();
        }

        $vehicles = Vehicle::query()
            ->when(! $isAdmin, function ($query) use ($userId) {
                $query->where('owner_user_id', $userId);
            })
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('license_plate', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($quickFilter === 'upcoming-inspection', function ($query) {
                $query->whereDate('inspection_date', '>=', Carbon::today())
                    ->whereDate('inspection_date', '<=', Carbon::today()->addDays(30));
            })
            ->when($quickFilter === 'monthly-service', function ($query) use ($userId, $startOfMonth, $endOfMonth) {
                $query->whereHas('serviceRecords', function ($serviceQuery) use ($userId, $startOfMonth, $endOfMonth) {
                    $serviceQuery
                        ->where('user_id', $userId)
                        ->whereBetween('entry_date', [$startOfMonth, $endOfMonth]);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total_vehicles' => Vehicle::query()
                ->when(! $isAdmin, function ($query) use ($userId) {
                    $query->where('owner_user_id', $userId);
                })
                ->when($branchId, function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->count(),
            'monthly_service_records' => ServiceRecord::query()
                ->when(! $isAdmin, function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->when($branchId, function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->whereBetween('entry_date', [$startOfMonth, $endOfMonth])
                ->count(),
            'upcoming_inspections' => Vehicle::query()
                ->when(! $isAdmin, function ($query) use ($userId) {
                    $query->where('owner_user_id', $userId);
                })
                ->when($branchId, function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->whereDate('inspection_date', '>=', Carbon::today())
                ->whereDate('inspection_date', '<=', Carbon::today()->addDays(30))
                ->count(),
        ];

        return view('vehicles.index', compact('vehicles', 'search', 'stats', 'quickFilter', 'notificationItems', 'unreadNotificationCount'));
    }

    public function create(): View
    {
        return view('vehicles.create');
    }

    public function show(Vehicle $vehicle)
    {
        $this->ensureVehicleOwnedByAuthenticatedUser($vehicle);

        $vehicle->load([
            'lastUpdatedBy',
            'serviceRecords' => function ($query) {
                $query->orderByDesc('entry_date')
                    ->orderByDesc('id')
                    ->with('serviceItems');
            },
        ]);

        $parts = Part::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sale_price']);

        return view('vehicles.show', compact('vehicle', 'parts'));
    }

    public function printLabel(Vehicle $vehicle): View
    {
        $this->ensureVehicleOwnedByAuthenticatedUser($vehicle);

        return view('vehicles.print-label', compact('vehicle'));
    }

    public function publicShow(string $uuid): View
    {
        $gallerySort = request()->query('gallery_sort', 'newest');
        $sortDirection = $gallerySort === 'oldest' ? 'asc' : 'desc';

        $vehicle = Vehicle::query()
            ->where('uuid', $uuid)
            ->with(['serviceRecords' => function ($query) use ($sortDirection) {
                $query->orderBy('entry_date', $sortDirection)
                    ->orderBy('id', $sortDirection)
                    ->with('serviceItems');
            }])
            ->firstOrFail();

        $records = $vehicle->serviceRecords;
        $totalAmount = $records->sum(function ($record) {
            return $record->serviceItems->sum('labor_or_part_fee');
        });

        $lastRecord = $records->first();
        $maskedPlate = $this->maskLicensePlate($vehicle->license_plate);
        $maskedChassisNumber = $this->maskChassisNumber($vehicle->chassis_number);

        return view('public.show', compact('vehicle', 'records', 'totalAmount', 'lastRecord', 'maskedPlate', 'maskedChassisNumber', 'gallerySort'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'chassis_number' => strtoupper(str_replace([' ', '-'], '', (string) $request->input('chassis_number'))),
            'license_plate' => strtoupper(trim((string) $request->input('license_plate'))),
        ]);

        $validated = $request->validate([
            'brand' => ['nullable', 'string', 'max:60'],
            'model' => ['nullable', 'string', 'max:60'],
            'license_plate' => [
                'required',
                'string',
                'max:15',
                'regex:/^(0[1-9]|[1-7][0-9]|8[01])\s?[A-Z]{1,3}\s?[0-9]{2,4}$/',
                'unique:vehicles,license_plate',
            ],
            'chassis_number' => [
                'required',
                'string',
                'size:17',
                'regex:/^[A-HJ-NPR-Z0-9]{17}$/',
                'unique:vehicles,chassis_number',
            ],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'fuel_type' => ['required', Rule::in(['Benzin', 'Dizel', 'LPG', 'Hibrit', 'Elektrik'])],
            'transmission_type' => ['required', Rule::in(['Manuel', 'Otomatik', 'Yarı-Otomatik'])],
            'current_km' => ['required', 'integer', 'min:0'],
            'inspection_date' => ['nullable', 'date'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_email' => ['nullable', 'email', 'max:255'],
        ]);

        $validated['license_plate'] = strtoupper($validated['license_plate']);
        $validated['chassis_number'] = strtoupper($validated['chassis_number']);
        $validated['last_updated_by_user_id'] = $request->user()?->id;
        $validated['owner_user_id'] = $request->user()?->id;
        $validated['branch_id'] = $request->user()?->branch_id;

        $vehicle = Vehicle::create($validated);
        AuditLogger::log(
            'vehicle_created',
            'vehicle',
            $vehicle->id,
            $vehicle->license_plate.' plakalı araç oluşturuldu.',
            null,
            $vehicle->only(['brand', 'model', 'license_plate', 'customer_name', 'owner_user_id'])
        );

        return redirect()->route('vehicles.index')->with('success', 'Araç kaydı oluşturuldu.');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->ensureVehicleOwnedByAuthenticatedUser($vehicle);

        $request->merge([
            'chassis_number' => strtoupper(str_replace([' ', '-'], '', (string) $request->input('chassis_number'))),
            'license_plate' => strtoupper(trim((string) $request->input('license_plate'))),
        ]);

        $validated = $request->validate([
            'brand' => ['nullable', 'string', 'max:60'],
            'model' => ['nullable', 'string', 'max:60'],
            'license_plate' => [
                'required',
                'string',
                'max:15',
                'regex:/^(0[1-9]|[1-7][0-9]|8[01])\s?[A-Z]{1,3}\s?[0-9]{2,4}$/',
                Rule::unique('vehicles', 'license_plate')->ignore($vehicle->id),
            ],
            'chassis_number' => [
                'required',
                'string',
                'size:17',
                'regex:/^[A-HJ-NPR-Z0-9]{17}$/',
                Rule::unique('vehicles', 'chassis_number')->ignore($vehicle->id),
            ],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'fuel_type' => ['required', Rule::in(['Benzin', 'Dizel', 'LPG', 'Hibrit', 'Elektrik'])],
            'transmission_type' => ['required', Rule::in(['Manuel', 'Otomatik', 'Yarı-Otomatik'])],
            'current_km' => ['required', 'integer', 'min:0'],
            'inspection_date' => ['nullable', 'date'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_email' => ['nullable', 'email', 'max:255'],
        ]);

        $validated['license_plate'] = strtoupper($validated['license_plate']);
        $validated['chassis_number'] = strtoupper($validated['chassis_number']);
        $validated['last_updated_by_user_id'] = $request->user()?->id;
        $validated['owner_user_id'] = $vehicle->owner_user_id ?: $request->user()?->id;
        $validated['branch_id'] = $vehicle->branch_id ?: $request->user()?->branch_id;

        $beforeData = $vehicle->only(['brand', 'model', 'license_plate', 'customer_name', 'customer_phone', 'current_km', 'inspection_date']);
        $vehicle->update($validated);
        AuditLogger::log(
            'vehicle_updated',
            'vehicle',
            $vehicle->id,
            $vehicle->license_plate.' plakalı araç güncellendi.',
            $beforeData,
            $vehicle->fresh()->only(['brand', 'model', 'license_plate', 'customer_name', 'customer_phone', 'current_km', 'inspection_date'])
        );

        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Araç bilgileri güncellendi.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->ensureVehicleOwnedByAuthenticatedUser($vehicle);
        $beforeData = $vehicle->only(['brand', 'model', 'license_plate', 'customer_name', 'owner_user_id']);

        try {
            $vehicle->delete();
        } catch (\Throwable $exception) {
            return redirect()
                ->route('vehicles.index')
                ->withErrors(['vehicle' => 'Araç silinemedi. Bu araca bağlı bakım kayıtları olabilir.']);
        }

        AuditLogger::log(
            'vehicle_deleted',
            'vehicle',
            $vehicle->id,
            ($beforeData['license_plate'] ?? 'Bilinmeyen').' plakalı araç silindi.',
            $beforeData
        );

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Araç kaydı silindi.');
    }

    private function maskLicensePlate(string $plate): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($plate));
        $parts = explode(' ', $normalized);

        if (count($parts) < 3) {
            return mb_substr($normalized, 0, 2) . ' ***';
        }

        $prefix = $parts[0];
        $middle = $parts[1];
        $suffix = $parts[2];

        $maskedMiddle = mb_substr($middle, 0, 1) . str_repeat('*', max(0, mb_strlen($middle) - 1));

        return $prefix . ' ' . $maskedMiddle . ' ' . $suffix;
    }

    private function maskChassisNumber(?string $chassisNumber): string
    {
        if (empty($chassisNumber)) {
            return 'Belirlenmedi';
        }

        $normalized = strtoupper(preg_replace('/\s+/', '', $chassisNumber));
        $length = mb_strlen($normalized);

        if ($length <= 8) {
            return mb_substr($normalized, 0, 2) . str_repeat('*', max(0, $length - 4)) . mb_substr($normalized, -2);
        }

        $prefix = mb_substr($normalized, 0, 4);
        $suffix = mb_substr($normalized, -4);

        return $prefix . str_repeat('*', max(0, $length - 8)) . $suffix;
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

    private function syncNotificationsForUser(\App\Models\User $user, bool $isAdmin): void
    {
        $today = Carbon::today();
        $threeDaysLater = Carbon::today()->addDays(3);

        $serviceDateRecords = ServiceRecord::query()
            ->whereDate('next_service_date', '>=', $today)
            ->whereDate('next_service_date', '<=', $threeDaysLater)
            ->with('vehicle')
            ->when(! $isAdmin, function ($query) use ($user) {
                $query->whereHas('vehicle', function ($vehicleQuery) use ($user) {
                    $vehicleQuery->where('owner_user_id', $user->id);
                });
            })
            ->get();

        foreach ($serviceDateRecords as $record) {
            $vehicle = $record->vehicle;
            if (! $vehicle) {
                continue;
            }

            UserNotification::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'notification_key' => 'service-date-'.$record->id.'-'.$record->next_service_date?->format('Y-m-d'),
                ],
                [
                    'type' => 'bakim_tarihi',
                    'title' => 'Yaklaşan bakım tarihi',
                    'message' => $vehicle->license_plate.' plakalı araç için bakım tarihi yaklaşıyor.',
                    'vehicle_id' => $vehicle->id,
                    'service_record_id' => $record->id,
                    'due_date' => $record->next_service_date,
                ]
            );
        }

        $serviceKmRecords = ServiceRecord::query()
            ->whereNotNull('next_service_km')
            ->with('vehicle')
            ->when(! $isAdmin, function ($query) use ($user) {
                $query->whereHas('vehicle', function ($vehicleQuery) use ($user) {
                    $vehicleQuery->where('owner_user_id', $user->id);
                });
            })
            ->get()
            ->filter(function (ServiceRecord $record) {
                $vehicle = $record->vehicle;
                if (! $vehicle || $record->next_service_km === null) {
                    return false;
                }

                $remainingKm = (int) $record->next_service_km - (int) $vehicle->current_km;

                return $remainingKm >= 0 && $remainingKm <= 1000;
            });

        foreach ($serviceKmRecords as $record) {
            $vehicle = $record->vehicle;
            if (! $vehicle) {
                continue;
            }

            $remainingKm = max(0, (int) $record->next_service_km - (int) $vehicle->current_km);

            UserNotification::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'notification_key' => 'service-km-'.$record->id.'-'.$record->next_service_km,
                ],
                [
                    'type' => 'bakim_km',
                    'title' => 'Yaklaşan bakım kilometresi',
                    'message' => $vehicle->license_plate.' plakalı araç için '.number_format($remainingKm, 0, ',', '.').' km kaldı.',
                    'vehicle_id' => $vehicle->id,
                    'service_record_id' => $record->id,
                ]
            );
        }

        $inspectionVehicles = Vehicle::query()
            ->whereDate('inspection_date', '>=', $today)
            ->whereDate('inspection_date', '<=', $threeDaysLater)
            ->when(! $isAdmin, function ($query) use ($user) {
                $query->where('owner_user_id', $user->id);
            })
            ->get();

        foreach ($inspectionVehicles as $vehicle) {
            UserNotification::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'notification_key' => 'inspection-date-'.$vehicle->id.'-'.$vehicle->inspection_date?->format('Y-m-d'),
                ],
                [
                    'type' => 'muayene_tarihi',
                    'title' => 'Yaklaşan muayene tarihi',
                    'message' => $vehicle->license_plate.' plakalı aracın muayene tarihi yaklaşıyor.',
                    'vehicle_id' => $vehicle->id,
                    'due_date' => $vehicle->inspection_date,
                ]
            );
        }
    }
}
