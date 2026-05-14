@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md">
        <section class="app-card p-6 sm:p-7">
            <div class="mb-4 flex justify-center">
                <img src="{{ asset('images/otokunye-logo.png') }}" alt="OTOKÜNYE logo" class="h-20 w-auto rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            </div>
            <h1 class="bg-gradient-to-r from-indigo-600 to-cyan-500 bg-clip-text text-2xl font-black text-transparent">Usta Kayıt</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Yeni usta hesabı oluşturup sisteme giriş yapabilirsiniz.</p>

            @if($errors->any())
                <div class="mt-4 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-900/20 dark:text-rose-300">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.attempt') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium">Ad Soyad</label>
                    <input
                        type="text"
                        name="name"
                        required
                        value="{{ old('name') }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">E-posta</label>
                    <input
                        type="email"
                        name="email"
                        required
                        value="{{ old('email') }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Şifre</label>
                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Şifre (Tekrar)</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-500">
                    Usta Hesabı Oluştur
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-slate-600 dark:text-slate-300">
                Zaten hesabın var mı?
                <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Giriş Yap</a>
            </p>
        </section>
    </div>
@endsection
