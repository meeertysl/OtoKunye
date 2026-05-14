@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <section class="app-card p-5 sm:p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Müşteri Araç Özeti</p>
            <h1 class="mt-2 text-2xl font-bold">{{ $vehicle->brand ?? 'Marka Bilgisi Yok' }} {{ $vehicle->model ?? '' }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Plaka: {{ $maskedPlate }}</p>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Şasi No: {{ $maskedChassisNumber }}</p>
            <div class="mt-4">
                <a
                    href="{{ route('customer.portal.login') }}"
                    class="mr-2 inline-flex items-center rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 dark:hover:bg-emerald-900/50"
                >
                    Müşteri Portalı
                </a>
                <a
                    href="{{ route('qr.scan', ['uuid' => $vehicle->uuid, 'master' => 1]) }}"
                    class="inline-flex items-center rounded-lg border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50"
                >
                    Usta Girişi
                </a>
            </div>
        </section>

        <section class="app-card p-5 sm:p-6">
            <h2 class="mb-4 text-lg font-semibold">Şeffaf Fiş</h2>
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

                        @if(($record->invoice_status ?? 'yok') !== 'yok')
                            <p class="mt-3 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                E-Fatura Durumu: {{ ucfirst($record->invoice_status) }}
                            </p>
                        @endif

                        @if(! $record->customer_approval_status)
                            <div class="mt-4">
                                <button
                                    type="button"
                                    class="approve-btn w-full rounded-xl bg-emerald-600 px-4 py-3 text-base font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-500"
                                    data-url="{{ route('service-records.public-approve', ['uuid' => $vehicle->uuid, 'serviceRecord' => $record->id]) }}"
                                >
                                    İşlemleri ve Tutarı Onaylıyorum
                                </button>
                            </div>
                        @else
                            <p class="mt-4 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300">
                                Bu bakım kaydı onaylanmıştır.
                            </p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">Henüz kayıtlı işlem bulunmuyor.</p>
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
                <a href="{{ route('vehicles.public.show', ['uuid' => $vehicle->uuid, 'gallery_sort' => 'newest']) }}"
                   class="rounded-lg border px-3 py-1.5 text-xs font-semibold {{ ($gallerySort ?? 'newest') === 'newest' ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800' }}">
                    Yeni -> Eski
                </a>
                <a href="{{ route('vehicles.public.show', ['uuid' => $vehicle->uuid, 'gallery_sort' => 'oldest']) }}"
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

    <script>
        document.querySelectorAll('.approve-btn').forEach((button) => {
            button.addEventListener('click', async () => {
                const previousText = button.textContent;
                button.disabled = true;
                    button.textContent = 'Onaylanıyor...';

                try {
                    const response = await fetch(button.dataset.url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Onay islemi basarisiz.');
                    }

                    button.textContent = 'Onaylandı';
                    button.classList.remove('bg-emerald-600', 'hover:bg-emerald-500');
                    button.classList.add('bg-slate-600');
                    setTimeout(() => window.location.reload(), 500);
                } catch (error) {
                    button.disabled = false;
                    button.textContent = previousText;
                    window.alert('Onay işlemi şu anda gerçekleştirilemedi. Lütfen tekrar deneyin.');
                }
            });
        });
    </script>
@endsection
