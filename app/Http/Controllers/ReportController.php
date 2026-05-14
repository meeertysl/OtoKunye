<?php

namespace App\Http\Controllers;

use App\Models\ServiceRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $isAdmin = $currentUser instanceof User && $currentUser->isAdmin();

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : Carbon::now()->subMonths(5)->startOfMonth();
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : Carbon::now()->endOfMonth();

        $reportData = $this->buildReportData($currentUser, $isAdmin, $startDate, $endDate);

        return view('reports.index', [
            'isAdmin' => $isAdmin,
            'monthly' => $reportData['monthly'],
            'topBrands' => $reportData['topBrands'],
            'topMasters' => $reportData['topMasters'],
            'summary' => $reportData['summary'],
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $currentUser = $request->user();
        $isAdmin = $currentUser instanceof User && $currentUser->isAdmin();

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : Carbon::now()->subMonths(5)->startOfMonth();
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : Carbon::now()->endOfMonth();

        $rows = $this->buildBaseQuery($currentUser, $isAdmin)
            ->join('users', 'users.id', '=', 'service_records.user_id')
            ->whereBetween('service_records.entry_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('service_records.entry_date')
            ->get([
                'service_records.entry_date',
                'vehicles.license_plate',
                'vehicles.brand',
                'vehicles.model',
                'users.name as master_name',
                'service_records.estimated_amount',
                'service_records.next_service_date',
                'service_records.next_service_km',
            ]);

        $filename = 'rapor_'.now()->format('Ymd_His').'.csv';
        $lines = [
            "Tarih;Plaka;Marka;Model;Usta;Tutar;Sonraki Bakim Tarihi;Sonraki Bakim KM",
        ];

        foreach ($rows as $row) {
            $lines[] = implode(';', [
                $row->entry_date ?? '',
                $row->license_plate ?? '',
                $row->brand ?? '',
                $row->model ?? '',
                $row->master_name ?? '',
                (string) ($row->estimated_amount ?? 0),
                $row->next_service_date ?? '',
                (string) ($row->next_service_km ?? ''),
            ]);
        }

        $content = "\xEF\xBB\xBF".implode("\n", $lines);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function printView(Request $request): View
    {
        $currentUser = $request->user();
        $isAdmin = $currentUser instanceof User && $currentUser->isAdmin();

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : Carbon::now()->subMonths(5)->startOfMonth();
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : Carbon::now()->endOfMonth();

        $reportData = $this->buildReportData($currentUser, $isAdmin, $startDate, $endDate);

        return view('reports.print', [
            'isAdmin' => $isAdmin,
            'monthly' => $reportData['monthly'],
            'topBrands' => $reportData['topBrands'],
            'topMasters' => $reportData['topMasters'],
            'summary' => $reportData['summary'],
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    private function buildBaseQuery(?User $currentUser, bool $isAdmin)
    {
        return ServiceRecord::query()
            ->join('vehicles', 'vehicles.id', '=', 'service_records.vehicle_id')
            ->when(! $isAdmin, function ($query) use ($currentUser) {
                $query->where('vehicles.branch_id', $currentUser?->branch_id);
            });
    }

    private function buildReportData(?User $currentUser, bool $isAdmin, Carbon $startDate, Carbon $endDate): array
    {
        $baseQuery = $this->buildBaseQuery($currentUser, $isAdmin);

        $monthly = (clone $baseQuery)
            ->whereBetween('service_records.entry_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy(DB::raw("strftime('%Y-%m', service_records.entry_date)"))
            ->orderBy(DB::raw("strftime('%Y-%m', service_records.entry_date)"))
            ->get([
                DB::raw("strftime('%Y-%m', service_records.entry_date) as period"),
                DB::raw('count(service_records.id) as total_records'),
                DB::raw('coalesce(sum(service_records.estimated_amount), 0) as total_revenue'),
            ]);

        $topBrands = (clone $baseQuery)
            ->whereBetween('service_records.entry_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('vehicles.brand')
            ->orderByDesc(DB::raw('count(service_records.id)'))
            ->limit(8)
            ->get([
                'vehicles.brand',
                DB::raw('count(service_records.id) as total_records'),
                DB::raw('coalesce(sum(service_records.estimated_amount), 0) as total_revenue'),
            ]);

        $topMasters = (clone $baseQuery)
            ->join('users', 'users.id', '=', 'service_records.user_id')
            ->whereBetween('service_records.entry_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('users.name')
            ->orderByDesc(DB::raw('count(service_records.id)'))
            ->limit(5)
            ->get([
                'users.name',
                DB::raw('count(service_records.id) as total_records'),
                DB::raw('coalesce(sum(service_records.estimated_amount), 0) as total_revenue'),
            ]);

        $summary = [
            'total_records' => (clone $baseQuery)
                ->whereBetween('service_records.entry_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->count('service_records.id'),
            'total_revenue' => (float) ((clone $baseQuery)
                ->whereBetween('service_records.entry_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->sum('service_records.estimated_amount')),
            'avg_ticket' => 0.0,
        ];

        if ($summary['total_records'] > 0) {
            $summary['avg_ticket'] = $summary['total_revenue'] / $summary['total_records'];
        }

        return [
            'monthly' => $monthly,
            'topBrands' => $topBrands,
            'topMasters' => $topMasters,
            'summary' => $summary,
        ];
    }
}
