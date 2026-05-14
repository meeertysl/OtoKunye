<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    public function showLoginForm(): View
    {
        return view('customer-portal.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_phone' => ['required', 'string', 'max:20'],
        ]);

        $normalizedPhone = $this->normalizePhone($validated['customer_phone']);
        if ($normalizedPhone === '') {
            return back()->withErrors(['customer_phone' => 'Geçerli bir telefon numarası giriniz.'])->withInput();
        }

        $matchedVehicleIds = Vehicle::query()
            ->get(['id', 'customer_phone'])
            ->filter(function (Vehicle $vehicle) use ($normalizedPhone) {
                return $this->normalizePhone((string) $vehicle->customer_phone) === $normalizedPhone;
            })
            ->pluck('id')
            ->values()
            ->all();

        if (count($matchedVehicleIds) === 0) {
            return back()->withErrors(['customer_phone' => 'Bu telefon numarası ile eşleşen araç bulunamadı.'])->withInput();
        }

        $request->session()->put('customer_portal.phone', $normalizedPhone);
        $request->session()->put('customer_portal.vehicle_ids', $matchedVehicleIds);
        $request->session()->regenerate();

        return redirect()->route('customer.portal.vehicles');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('customer_portal');

        return redirect()->route('customer.portal.login')->with('success', 'Müşteri portalından çıkış yapıldı.');
    }

    public function vehicles(Request $request): View
    {
        $vehicleIds = $this->sessionVehicleIds($request);
        $allVehicles = Vehicle::query()
            ->whereIn('id', $vehicleIds)
            ->latest()
            ->get();

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = $allVehicles->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $vehicles = new LengthAwarePaginator(
            $items,
            $allVehicles->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('customer-portal.vehicles', compact('vehicles'));
    }

    public function showVehicle(Request $request, Vehicle $vehicle): View
    {
        abort_unless(in_array($vehicle->id, $this->sessionVehicleIds($request), true), 403);

        $gallerySort = $request->query('gallery_sort', 'newest');
        $sortDirection = $gallerySort === 'oldest' ? 'asc' : 'desc';

        $vehicle->load(['serviceRecords' => function ($query) use ($sortDirection) {
            $query->orderBy('entry_date', $sortDirection)
                ->orderBy('id', $sortDirection)
                ->with('serviceItems');
        }]);

        $records = $vehicle->serviceRecords;
        $totalAmount = $records->sum(fn ($record) => $record->serviceItems->sum('labor_or_part_fee'));
        $lastRecord = $records->first();
        $maskedPlate = $this->maskLicensePlate($vehicle->license_plate);
        $maskedChassisNumber = $this->maskChassisNumber($vehicle->chassis_number);

        return view('customer-portal.show', compact('vehicle', 'records', 'totalAmount', 'lastRecord', 'maskedPlate', 'maskedChassisNumber', 'gallerySort'));
    }

    private function sessionVehicleIds(Request $request): array
    {
        return collect($request->session()->get('customer_portal.vehicle_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    private function maskLicensePlate(string $plate): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($plate));
        $parts = explode(' ', (string) $normalized);

        if (count($parts) < 3) {
            return mb_substr((string) $normalized, 0, 2).' ***';
        }

        $middle = $parts[1];
        $maskedMiddle = mb_substr($middle, 0, 1).str_repeat('*', max(0, mb_strlen($middle) - 1));

        return $parts[0].' '.$maskedMiddle.' '.$parts[2];
    }

    private function maskChassisNumber(?string $chassisNumber): string
    {
        if (empty($chassisNumber)) {
            return 'Belirlenmedi';
        }

        $normalized = strtoupper(preg_replace('/\s+/', '', $chassisNumber));
        $length = mb_strlen($normalized);

        if ($length <= 8) {
            return mb_substr($normalized, 0, 2).str_repeat('*', max(0, $length - 4)).mb_substr($normalized, -2);
        }

        return mb_substr($normalized, 0, 4).str_repeat('*', max(0, $length - 8)).mb_substr($normalized, -4);
    }
}
