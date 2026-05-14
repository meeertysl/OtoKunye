@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-xl">
        <div class="app-card p-6 sm:p-7">
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-300">Müşteri Portalı</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">Dijital Servis Geçmişinize Erişin</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Araç kayıtlarınızı görmek için telefon numaranızı giriniz.</p>

            <form method="POST" action="{{ route('customer.portal.login.attempt') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="customer_phone" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">Telefon Numarası</label>
                    <input
                        type="text"
                        id="customer_phone"
                        name="customer_phone"
                        value="{{ old('customer_phone') }}"
                        placeholder="05xx xxx xx xx"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                        required
                    >
                    @error('customer_phone')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                    Portala Giriş Yap
                </button>
            </form>
        </div>
    </div>
@endsection
