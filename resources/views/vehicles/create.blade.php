@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="bg-gradient-to-r from-indigo-600 to-cyan-500 bg-clip-text text-2xl font-black text-transparent">OTOKÜNYE Araç Kayıt Formu</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Bilgileri 3 bölümde doldurarak kaydı tamamlayın.</p>
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

    <form action="{{ route('vehicles.store') }}" method="POST" class="space-y-6">
        @csrf

        <section class="app-card p-4 sm:p-6">
            <h2 class="mb-4 text-lg font-semibold">Genel Bilgiler</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Marka</label>
                    <input name="brand" value="{{ old('brand') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Model</label>
                    <input name="model" value="{{ old('model') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Plaka</label>
                    <input name="license_plate" required value="{{ old('license_plate') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Güncel KM</label>
                    <input type="number" min="0" name="current_km" required value="{{ old('current_km') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                </div>
            </div>
        </section>

        <section class="app-card p-4 sm:p-6">
            <h2 class="mb-4 text-lg font-semibold">Teknik Bilgiler</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Şasi Numarası</label>
                    <input name="chassis_number" minlength="17" maxlength="17" required value="{{ old('chassis_number') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm uppercase dark:border-slate-700 dark:bg-slate-800" />
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">17 karakter olmalıdır. Boşluk ve tire otomatik temizlenir.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Motor Numarası</label>
                    <input name="engine_number" value="{{ old('engine_number') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Yakıt Tipi</label>
                    <select name="fuel_type" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        @foreach(['Benzin', 'Dizel', 'LPG', 'Hibrit', 'Elektrik'] as $fuel)
                            <option value="{{ $fuel }}" @selected(old('fuel_type') === $fuel)>{{ $fuel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Şanzıman Tipi</label>
                    <select name="transmission_type" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        @foreach(['Manuel', 'Otomatik', 'Yarı-Otomatik'] as $transmission)
                            <option value="{{ $transmission }}" @selected(old('transmission_type') === $transmission)>{{ $transmission }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Muayene Tarihi</label>
                    <input type="date" name="inspection_date" value="{{ old('inspection_date') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                </div>
            </div>
        </section>

        <section class="app-card p-4 sm:p-6">
            <h2 class="mb-4 text-lg font-semibold">Müşteri Bilgileri</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Ad Soyad</label>
                    <input name="customer_name" required value="{{ old('customer_name') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Telefon</label>
                    <input name="customer_phone" required value="{{ old('customer_phone') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium">E-posta</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                </div>
            </div>
        </section>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-500 sm:w-auto">
            Aracı Kaydet
        </button>
    </form>
@endsection
