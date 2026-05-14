@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="bg-gradient-to-r from-indigo-600 to-cyan-500 bg-clip-text text-2xl font-black text-transparent">Bakım Kaydı Düzenle</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $vehicle->brand }} {{ $vehicle->model }} - {{ $vehicle->license_plate }}</p>
        </div>
        <a href="{{ route('vehicles.show', $vehicle) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
            Geri Dön
        </a>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-rose-700 dark:border-rose-900 dark:bg-rose-900/20 dark:text-rose-300">
            <ul class="list-disc pl-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="app-card p-4 sm:p-6">
        <form action="{{ route('vehicles.service-records.update', ['vehicle' => $vehicle, 'serviceRecord' => $serviceRecord]) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Giriş Tarihi</label>
                    <input type="date" name="entry_date" value="{{ old('entry_date', optional($serviceRecord->entry_date)->format('Y-m-d')) }}" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Çıkış Tarihi</label>
                    <input type="date" name="exit_date" value="{{ old('exit_date', optional($serviceRecord->exit_date)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Tahmini Tutar</label>
                    <input type="number" step="0.01" min="0" name="estimated_amount" value="{{ old('estimated_amount', $serviceRecord->estimated_amount) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Sonraki Bakım Tarihi</label>
                    <input type="date" name="next_service_date" value="{{ old('next_service_date', optional($serviceRecord->next_service_date)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Sonraki Bakım KM</label>
                    <input type="number" min="0" name="next_service_km" value="{{ old('next_service_km', $serviceRecord->next_service_km) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">E-Fatura Durumu</label>
                    <input type="text" value="{{ ucfirst($serviceRecord->invoice_status ?? 'yok') }}" readonly class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Müşteri Onayı</label>
                    <select name="customer_approval_status" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="0" @selected(old('customer_approval_status', (int) $serviceRecord->customer_approval_status) === 0)>Bekliyor</option>
                        <option value="1" @selected(old('customer_approval_status', (int) $serviceRecord->customer_approval_status) === 1)>Onaylandı</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium">Usta Notu</label>
                    <textarea name="master_note" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('master_note', $serviceRecord->master_note) }}</textarea>
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
                <h3 class="mb-3 text-sm font-semibold">Servis Kalemleri</h3>
                <div id="service-items-wrapper" class="space-y-3">
                    @php $items = old('service_items', $serviceRecord->serviceItems->toArray()); @endphp
                    @foreach($items as $index => $item)
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_120px_160px_100px_auto_auto]">
                            <select name="service_items[{{ $index }}][inventory_part_id]" class="inventory-select rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                                <option value="">Stok parçası seç (opsiyonel)</option>
                                @foreach($parts as $part)
                                    <option value="{{ $part->id }}" data-sale-price="{{ $part->sale_price }}" @selected((int) ($item['inventory_part_id'] ?? 0) === $part->id)>{{ $part->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" min="0.01" step="0.01" name="service_items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" placeholder="Miktar" class="item-qty rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                            <input name="service_items[{{ $index }}][part_name]" value="{{ $item['part_name'] ?? '' }}" placeholder="Parça veya işçilik" class="item-name rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                            <input type="number" min="0" step="0.01" name="service_items[{{ $index }}][labor_or_part_fee]" value="{{ $item['labor_or_part_fee'] ?? '' }}" placeholder="Ücret" class="item-fee rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                            <label class="inline-flex items-center gap-2 text-xs">
                                <input type="checkbox" name="service_items[{{ $index }}][use_inventory]" value="1" class="rounded border-slate-300" @checked((bool) ($item['use_inventory'] ?? false))>
                                Stoktan düş
                            </label>
                            <button type="button" class="remove-item rounded-lg border border-rose-300 px-3 py-2 text-xs text-rose-600 dark:border-rose-800 dark:text-rose-300">Sil</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-item-btn" class="mt-3 rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600">Yeni Kalem Ekle</button>
            </div>

            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-500">
                Bakım Kaydını Güncelle
            </button>
        </form>
    </section>

    <script>
        const wrapper = document.getElementById('service-items-wrapper');
        const addBtn = document.getElementById('add-item-btn');
        let itemIndex = wrapper?.children.length ?? 0;

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
