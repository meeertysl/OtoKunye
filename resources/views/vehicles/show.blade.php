@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="bg-gradient-to-r from-indigo-600 to-cyan-500 bg-clip-text text-2xl font-black text-transparent">{{ $vehicle->brand }} {{ $vehicle->model }}</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $vehicle->license_plate }} - {{ $vehicle->customer_name }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <a href="{{ route('vehicles.public.show', $vehicle->uuid) }}" class="rounded-lg border border-indigo-200 px-3 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50 dark:border-indigo-900 dark:text-indigo-400 dark:hover:bg-indigo-900/20">Müşteri Görünümü</a>
            <a href="{{ route('qr.scan', ['uuid' => $vehicle->uuid, 'preview' => 1]) }}" target="_blank" rel="noopener" class="rounded-lg border border-emerald-200 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-900 dark:text-emerald-400 dark:hover:bg-emerald-900/20">QR Linki (Test)</a>
            <a href="{{ route('vehicles.print-label', $vehicle) }}" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">Etiket Yazdır</a>
        </div>
    </div>

    <section class="app-card mb-6 p-4 sm:p-6">
        <h2 class="mb-3 text-lg font-semibold">Araç QR Kodu</h2>
        <p class="mb-4 text-sm text-slate-600 dark:text-slate-300">Bu QR kod dışarıdan okutulduğunda, kullanıcı giriş durumuna göre doğru ekrana yönlendirilir.</p>
        <div class="inline-flex rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-950">
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(180)->generate(route('qr.scan', $vehicle->uuid)) !!}
        </div>
    </section>

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-rose-700 dark:border-rose-900 dark:bg-rose-900/20 dark:text-rose-300">
            <ul class="list-disc pl-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="app-card mb-6 p-4 sm:p-6">
        <h2 class="mb-4 text-lg font-semibold">Araç ve Müşteri Bilgileri</h2>
        <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
            Son güncelleyen:
            <span class="font-semibold">{{ $vehicle->lastUpdatedBy?->name ?? 'Bilinmiyor' }}</span>
            <span class="mx-2 text-slate-400">|</span>
            Güncelleme zamanı:
            <span class="font-semibold">{{ optional($vehicle->updated_at)->format('d.m.Y H:i') ?? '-' }}</span>
        </div>
        <form action="{{ route('vehicles.update', $vehicle) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Genel Bilgiler</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Marka</label>
                        <input name="brand" value="{{ old('brand', $vehicle->brand) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Model</label>
                        <input name="model" value="{{ old('model', $vehicle->model) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Plaka</label>
                        <input name="license_plate" required value="{{ old('license_plate', $vehicle->license_plate) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm uppercase dark:border-slate-700 dark:bg-slate-800" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Güncel KM</label>
                        <input type="number" min="0" name="current_km" required value="{{ old('current_km', $vehicle->current_km) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                    </div>
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Teknik Bilgiler</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Şasi Numarası</label>
                        <input name="chassis_number" minlength="17" maxlength="17" required value="{{ old('chassis_number', $vehicle->chassis_number) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm uppercase dark:border-slate-700 dark:bg-slate-800" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Motor Numarası</label>
                        <input name="engine_number" value="{{ old('engine_number', $vehicle->engine_number) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Yakıt Tipi</label>
                        <select name="fuel_type" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                            @foreach(['Benzin', 'Dizel', 'LPG', 'Hibrit', 'Elektrik'] as $fuel)
                                <option value="{{ $fuel }}" @selected(old('fuel_type', $vehicle->fuel_type) === $fuel)>{{ $fuel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Şanzıman Tipi</label>
                        <select name="transmission_type" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                            @foreach(['Manuel', 'Otomatik', 'Yarı-Otomatik'] as $transmission)
                                <option value="{{ $transmission }}" @selected(old('transmission_type', $vehicle->transmission_type) === $transmission)>{{ $transmission }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Muayene Tarihi</label>
                        <input type="date" name="inspection_date" value="{{ old('inspection_date', optional($vehicle->inspection_date)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                    </div>
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Müşteri Bilgileri</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Ad Soyad</label>
                        <input name="customer_name" required value="{{ old('customer_name', $vehicle->customer_name) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Telefon</label>
                        <input name="customer_phone" required value="{{ old('customer_phone', $vehicle->customer_phone) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">E-posta</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email', $vehicle->customer_email) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                    </div>
                </div>
            </div>

            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-500">
                Araç ve Müşteri Bilgilerini Güncelle
            </button>
        </form>
    </section>

    <section class="app-card mb-6 p-4 sm:p-6">
        <h2 class="mb-4 text-lg font-semibold">Yeni Bakım Kaydı</h2>
        <form action="{{ route('vehicles.service-records.store', $vehicle) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Giriş Tarihi</label>
                    <input type="date" name="entry_date" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Çıkış Tarihi</label>
                    <input type="date" name="exit_date" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Tahmini Tutar</label>
                    <input type="number" step="0.01" min="0" name="estimated_amount" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Sonraki Bakım Tarihi</label>
                    <input type="date" name="next_service_date" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Sonraki Bakım KM</label>
                    <input type="number" min="0" name="next_service_km" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Müşteri Onayı</label>
                    <select name="customer_approval_status" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="0">Bekliyor</option>
                        <option value="1">Onaylandi</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium">Usta Notu</label>
                    <textarea name="master_note" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Bakım Öncesi Fotoğrafı</label>
                    <input id="before_image" type="file" name="before_image" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm">
                    <button type="button" data-camera-target="before_image" class="camera-btn mt-2 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                        Kamera ile Çek
                    </button>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Bakım Sonrası Fotoğrafı</label>
                    <input id="after_image" type="file" name="after_image" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm">
                    <button type="button" data-camera-target="after_image" class="camera-btn mt-2 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                        Kamera ile Çek
                    </button>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                <h3 class="mb-3 text-sm font-semibold">Servis Kalemleri (Dinamik Fiş Kalemleri)</h3>
                <div id="service-items-wrapper" class="space-y-3">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_120px_160px_100px_auto]">
                        <select name="service_items[0][inventory_part_id]" class="inventory-select rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                            <option value="">Stok parçası seç (opsiyonel)</option>
                            @foreach($parts as $part)
                                <option value="{{ $part->id }}" data-sale-price="{{ $part->sale_price }}">{{ $part->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" min="0.01" step="0.01" name="service_items[0][quantity]" value="1" placeholder="Miktar" class="item-qty rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <input name="service_items[0][part_name]" placeholder="Parça veya işçilik" class="item-name rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <input type="number" min="0" step="0.01" name="service_items[0][labor_or_part_fee]" placeholder="Ücret" class="item-fee rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <label class="inline-flex items-center gap-2 text-xs">
                            <input type="checkbox" name="service_items[0][use_inventory]" value="1" class="rounded border-slate-300">
                            Stoktan düş
                        </label>
                    </div>
                </div>
                <button type="button" id="add-item-btn" class="mt-3 rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600">Yeni Kalem Ekle</button>
            </div>

            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-500">Bakım Kaydını Oluştur</button>
        </form>
    </section>

    <section class="app-card p-4 sm:p-6">
        <h2 class="mb-4 text-lg font-semibold">Bakım Geçmişi</h2>
        <div class="space-y-3">
            @forelse($vehicle->serviceRecords as $record)
                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-medium">{{ optional($record->entry_date)->format('d.m.Y') }} - {{ $record->serviceItems->count() }} kalem</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Tahmini tutar: {{ $record->estimated_amount !== null ? number_format((float) $record->estimated_amount, 2, ',', '.') . ' TL' : 'Belirtilmedi' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <form method="POST" action="{{ route('vehicles.service-records.invoice-sync', ['vehicle' => $vehicle, 'serviceRecord' => $record]) }}">
                                @csrf
                                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full border-2 border-red-200 bg-gradient-to-b from-red-500 to-red-700 px-4 text-[11px] font-bold uppercase tracking-wide text-white shadow-[0_8px_18px_-8px_rgba(220,38,38,0.8)] transition hover:-translate-y-0.5 hover:from-red-400 hover:to-red-600 hover:shadow-[0_10px_22px_-8px_rgba(239,68,68,0.85)] focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:border-red-900/70 dark:focus:ring-offset-slate-900">
                                    e-fatura
                                </button>
                            </form>
                            <span class="rounded-md border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:text-slate-300">
                                {{ ucfirst($record->invoice_status ?? 'yok') }}
                            </span>
                            <a href="{{ route('vehicles.service-records.edit', ['vehicle' => $vehicle, 'serviceRecord' => $record]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                Güncelle
                            </a>
                            <form method="POST" action="{{ route('vehicles.service-records.destroy', ['vehicle' => $vehicle, 'serviceRecord' => $record]) }}" onsubmit="return confirm('Bu bakım kaydını silmek istediğinize emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-medium text-rose-600 hover:text-rose-500 dark:text-rose-400">
                                    Sil
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">Henüz bakım kaydı yok.</p>
            @endforelse
        </div>
    </section>

    <script>
        const wrapper = document.getElementById('service-items-wrapper');
        const addBtn = document.getElementById('add-item-btn');
        let itemIndex = 1;

        addBtn?.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 gap-3 sm:grid-cols-[1fr_120px_160px_100px_auto_auto]';
            row.innerHTML = `
                <select name="service_items[${itemIndex}][inventory_part_id]" class="inventory-select rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                    <option value="">Stok parçası seç (opsiyonel)</option>
                    @foreach($parts as $part)
                        <option value="{{ $part->id }}" data-sale-price="{{ $part->sale_price }}">{{ $part->name }}</option>
                    @endforeach
                </select>
                <input type="number" min="0.01" step="0.01" name="service_items[${itemIndex}][quantity]" value="1" placeholder="Miktar" class="item-qty rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                <input name="service_items[${itemIndex}][part_name]" placeholder="Parça veya işçilik" class="item-name rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                <input type="number" min="0" step="0.01" name="service_items[${itemIndex}][labor_or_part_fee]" placeholder="Ücret" class="item-fee rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                <label class="inline-flex items-center gap-2 text-xs">
                    <input type="checkbox" name="service_items[${itemIndex}][use_inventory]" value="1" class="rounded border-slate-300">
                    Stoktan düş
                </label>
                <button type="button" class="remove-item rounded-lg border border-rose-300 px-3 py-2 text-xs text-rose-600 dark:border-rose-800 dark:text-rose-300">Sil</button>
            `;
            wrapper.appendChild(row);
            itemIndex++;
        });

        wrapper?.addEventListener('click', (event) => {
            const target = event.target;
            if (target.classList.contains('remove-item')) {
                target.parentElement.remove();
            }
        });

        wrapper?.addEventListener('change', (event) => {
            const target = event.target;
            if (!target.classList.contains('inventory-select')) return;
            const row = target.closest('div');
            const selected = target.options[target.selectedIndex];
            const price = parseFloat(selected?.dataset?.salePrice || '0');
            const nameInput = row?.querySelector('.item-name');
            const qtyInput = row?.querySelector('.item-qty');
            const feeInput = row?.querySelector('.item-fee');
            if (nameInput && selected?.text && target.value) nameInput.value = selected.text;
            const qty = parseFloat(qtyInput?.value || '1');
            if (feeInput && price > 0) feeInput.value = (qty * price).toFixed(2);
        });

        const handleCameraCapture = async (inputId) => {
            const input = document.getElementById(inputId);
            if (!input) return;

            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                const video = document.createElement('video');
                video.srcObject = stream;
                video.playsInline = true;
                video.muted = true;
                await video.play();

                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth || 1280;
                canvas.height = video.videoHeight || 720;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

                stream.getTracks().forEach((track) => track.stop());

                canvas.toBlob((blob) => {
                    if (!blob) return;
                    const file = new File([blob], `kamera-${Date.now()}.jpg`, { type: 'image/jpeg' });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
                }, 'image/jpeg', 0.92);
            } catch (error) {
                window.alert('Kamera açılamadı. Lütfen tarayıcı kamera iznini kontrol edin.');
            }
        };

        document.querySelectorAll('.camera-btn').forEach((button) => {
            button.addEventListener('click', () => handleCameraCapture(button.dataset.cameraTarget));
        });
    </script>
@endsection
