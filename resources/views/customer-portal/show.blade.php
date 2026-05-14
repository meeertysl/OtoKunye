@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('customer.portal.vehicles') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">← Araçlarıma Dön</a>
            <form method="POST" action="{{ route('customer.portal.logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    Çıkış
                </button>
            </form>
        </div>

        <section class="app-card p-5 sm:p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Araç Özeti</p>
            <h1 class="mt-2 text-2xl font-bold">{{ $vehicle->brand ?? 'Marka Bilgisi Yok' }} {{ $vehicle->model ?? '' }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Plaka: {{ $maskedPlate }}</p>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Şasi No: {{ $maskedChassisNumber }}</p>
        </section>

        <section class="app-card p-5 sm:p-6">
            <h2 class="mb-4 text-lg font-semibold">Dijital Servis Geçmişi</h2>
            <div class="space-y-4">
                @forelse($records as $record)
                    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                        <p class="mb-2 text-sm font-medium">Bakım Tarihi: {{ optional($record->entry_date)->format('d.m.Y') }}</p>
                        <ul class="space-y-2 text-sm">
                            @foreach($record->serviceItems as $item)
                                <li class="flex items-center justify-between gap-3 border-b border-slate-100 pb-2 dark:border-slate-800">
                                    <span>{{ $item->part_name }}</span>
                                    <span class="font-semibold">{{ number_format((float) $item->labor_or_part_fee, 2, ',', '.') }} TL</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">Henüz kayıtlı bakım geçmişi bulunmuyor.</p>
                @endforelse
            </div>

            <div class="mt-5 rounded-xl bg-slate-100 px-4 py-3 text-right dark:bg-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-300">Toplam Tutar</p>
                <p class="text-xl font-bold">{{ number_format((float) $totalAmount, 2, ',', '.') }} TL</p>
            </div>
        </section>

        <section class="app-card p-5 sm:p-6">
            <h2 class="mb-4 text-lg font-semibold">Öncesi / Sonrası Galeri</h2>
            <div class="mb-4 flex flex-wrap gap-2">
                <a href="{{ route('customer.portal.vehicle.show', ['vehicle' => $vehicle->id, 'gallery_sort' => 'newest']) }}"
                   class="rounded-lg border px-3 py-1.5 text-xs font-semibold {{ ($gallerySort ?? 'newest') === 'newest' ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800' }}">
                    Yeni -> Eski
                </a>
                <a href="{{ route('customer.portal.vehicle.show', ['vehicle' => $vehicle->id, 'gallery_sort' => 'oldest']) }}"
                   class="rounded-lg border px-3 py-1.5 text-xs font-semibold {{ ($gallerySort ?? 'newest') === 'oldest' ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800' }}">
                    Eski -> Yeni
                </a>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach($records as $record)
                    @if($record->before_image_path)
                        <a class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700" href="{{ asset('storage/' . $record->before_image_path) }}" target="_blank">
                            <img src="{{ asset('storage/' . $record->before_image_path) }}" alt="Bakım öncesi" class="h-48 w-full object-cover">
                        </a>
                    @endif
                    @if($record->after_image_path)
                        <a class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700" href="{{ asset('storage/' . $record->after_image_path) }}" target="_blank">
                            <img src="{{ asset('storage/' . $record->after_image_path) }}" alt="Bakım sonrası" class="h-48 w-full object-cover">
                        </a>
                    @endif
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-indigo-300 bg-indigo-50 p-5 shadow-sm dark:border-indigo-800 dark:bg-indigo-900/20 sm:p-6">
            <h2 class="text-lg font-semibold">Bir Sonraki Hatırlatma</h2>
            <p class="mt-2 text-sm">
                Sonraki Bakım KM:
                <span class="font-semibold">{{ $lastRecord?->next_service_km ? number_format($lastRecord->next_service_km, 0, ',', '.') . ' km' : 'Belirlenmedi' }}</span>
            </p>
            <p class="mt-1 text-sm">
                Muayene Tarihi:
                <span class="font-semibold">{{ optional($vehicle->inspection_date)->format('d.m.Y') ?? 'Belirlenmedi' }}</span>
            </p>
        </section>
    </div>
@endsection
