@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="bg-gradient-to-r from-indigo-600 to-cyan-500 bg-clip-text text-2xl font-black text-transparent">Araç Listesi</h1>
        <a href="{{ route('vehicles.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-500">
            Yeni Araç Ekle
        </a>
    </div>

    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <a href="{{ route('vehicles.index') }}" class="app-card block p-4 transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Toplam Araç</p>
            <p class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $stats['total_vehicles'] ?? 0 }}</p>
        </a>
        <a href="{{ route('vehicles.index', ['filter' => 'monthly-service']) }}" class="app-card block p-4 transition hover:-translate-y-0.5 hover:shadow-md {{ ($quickFilter ?? '') === 'monthly-service' ? 'ring-2 ring-indigo-400' : '' }}">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Bu Ay Bakım Kaydı</p>
            <p class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $stats['monthly_service_records'] ?? 0 }}</p>
        </a>
        <a href="{{ route('vehicles.index', ['filter' => 'upcoming-inspection']) }}" class="app-card block p-4 transition hover:-translate-y-0.5 hover:shadow-md {{ ($quickFilter ?? '') === 'upcoming-inspection' ? 'ring-2 ring-indigo-400' : '' }}">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">30 Gün İçinde Muayene</p>
            <p class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $stats['upcoming_inspections'] ?? 0 }}</p>
        </a>
    </div>

    <form method="GET" action="{{ route('vehicles.index') }}" class="mb-4 app-card p-3 sm:p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-3 inline-flex items-center text-slate-400">🔎</span>
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Marka, model, plaka, müşteri adı veya telefon ara..."
                    class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm dark:border-slate-700 dark:bg-slate-800"
                >
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-500">
                    Ara
                </button>
                @if(!empty($search))
                    <a href="{{ route('vehicles.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                        Temizle
                    </a>
                @endif
            </div>
        </div>
    </form>

    <div class="app-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Araç</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Müşteri</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Plaka</th>
                    <th class="px-4 py-3"></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($vehicles as $vehicle)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="px-4 py-3 text-sm">{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                        <td class="px-4 py-3 text-sm">{{ $vehicle->customer_name }}</td>
                        <td class="px-4 py-3 text-sm">{{ $vehicle->license_plate }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Detay</a>
                                <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" onsubmit="return confirm('Bu araç kaydını silmek istediğinize emin misiniz?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-rose-600 hover:text-rose-500 dark:text-rose-400">
                                        Sil
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Kayıtlı araç bulunmuyor.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $vehicles->links() }}
    </div>
@endsection
