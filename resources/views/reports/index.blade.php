@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <div class="app-card p-5 sm:p-6">
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Rapor Ekranı</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                {{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }} aralığındaki bakım ve ciro özetleri.
            </p>
        </div>

        <form method="GET" action="{{ route('reports.index') }}" class="app-card p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-4 sm:items-end">
                <div>
                    <label for="start_date" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Başlangıç Tarihi</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                </div>
                <div>
                    <label for="end_date" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Bitiş Tarihi</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                </div>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Uygula</button>
                <div class="flex items-center gap-2 sm:justify-end">
                    <a href="{{ route('reports.export.csv', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                        CSV İndir
                    </a>
                    <a href="{{ route('reports.print', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" target="_blank" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                        PDF Çıktısı
                    </a>
                </div>
            </div>
        </form>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="app-card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Toplam Bakım Kaydı</p>
                <p class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">{{ number_format($summary['total_records'], 0, ',', '.') }}</p>
            </div>
            <div class="app-card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Toplam Ciro</p>
                <p class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">₺{{ number_format($summary['total_revenue'], 2, ',', '.') }}</p>
            </div>
            <div class="app-card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Ortalama Fiş Tutarı</p>
                <p class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">₺{{ number_format($summary['avg_ticket'], 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="app-card p-4 sm:p-5">
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Aylık Bakım / Ciro</h2>
            <div class="mt-3 space-y-2">
                @forelse($monthly as $row)
                    <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $row->period)->translatedFormat('F Y') }}</span>
                            <span class="text-slate-600 dark:text-slate-300">{{ $row->total_records }} kayıt</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Ciro: ₺{{ number_format((float) $row->total_revenue, 2, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">Seçilen aralıkta kayıt bulunamadı.</p>
                @endforelse
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="app-card p-4 sm:p-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">En Çok İşlem Gören Markalar</h2>
                <div class="mt-3 space-y-2">
                    @forelse($topBrands as $brand)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-700">
                            <span class="font-medium text-slate-900 dark:text-slate-100">{{ $brand->brand ?: 'Belirtilmedi' }}</span>
                            <span class="text-slate-600 dark:text-slate-300">{{ $brand->total_records }} kayıt / ₺{{ number_format((float) $brand->total_revenue, 2, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">Marka verisi bulunamadı.</p>
                    @endforelse
                </div>
            </div>

            @if($isAdmin)
                <div class="app-card p-4 sm:p-5">
                    <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">En Aktif Ustalar</h2>
                    <div class="mt-3 space-y-2">
                        @forelse($topMasters as $master)
                            <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-700">
                                <span class="font-medium text-slate-900 dark:text-slate-100">{{ $master->name }}</span>
                                <span class="text-slate-600 dark:text-slate-300">{{ $master->total_records }} kayıt / ₺{{ number_format((float) $master->total_revenue, 2, ',', '.') }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Usta verisi bulunamadı.</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
