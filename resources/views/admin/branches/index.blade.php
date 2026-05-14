@extends('layouts.app')

@section('content')
    <div class="space-y-5">
        <div class="app-card p-5 sm:p-6">
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Şube Yönetimi</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Yeni şube ekleyin ve şube durumlarını yönetin.</p>
        </div>

        <form method="POST" action="{{ route('admin.branches.store') }}" class="app-card p-4">
            @csrf
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input type="text" name="name" placeholder="Şube adı" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                <input type="text" name="code" placeholder="Şube kodu" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm uppercase dark:border-slate-700 dark:bg-slate-900" required>
                <input type="text" name="phone" placeholder="Telefon" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                <input type="text" name="address" placeholder="Adres" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
            </div>
            <button type="submit" class="mt-3 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Şube Ekle</button>
        </form>

        <div class="app-card overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Şube</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Kod</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">İletişim</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Durum</th>
                    <th class="px-4 py-3"></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($branches as $branch)
                    <tr>
                        <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-100">
                            <form method="POST" action="{{ route('admin.branches.update', $branch) }}" class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $branch->name }}" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-900" required>
                                <input type="text" name="code" value="{{ $branch->code }}" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs uppercase dark:border-slate-700 dark:bg-slate-900" required>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                <input type="text" name="phone" value="{{ $branch->phone }}" placeholder="Telefon" class="mb-2 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-900">
                                <input type="text" name="address" value="{{ $branch->address }}" placeholder="Adres" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-900">
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($branch->is_active)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                                <button type="submit" class="mb-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                                    Güncelle
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.branches.toggle-status', $branch) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                                    {{ $branch->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
