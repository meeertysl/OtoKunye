@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <div class="app-card p-5 sm:p-6">
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Stok ve Parça Yönetimi</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Parça kartlarını yönetin, stok giriş/çıkış işlemlerini kaydedin.</p>
        </div>

        <form method="POST" action="{{ route('parts.store') }}" class="app-card p-4">
            @csrf
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <input type="text" name="name" placeholder="Parça adı" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                <input type="text" name="sku" placeholder="Stok kodu" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                <input type="text" name="category" placeholder="Kategori" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                <input type="number" step="0.01" min="0" name="purchase_price" placeholder="Alış fiyatı" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                <input type="number" step="0.01" min="0" name="sale_price" placeholder="Satış fiyatı" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                <input type="number" step="0.01" min="0" name="minimum_stock" placeholder="Min stok" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
            </div>
            <button type="submit" class="mt-3 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Parça Ekle</button>
        </form>

        @if(($lowStockParts ?? collect())->isNotEmpty())
            <div class="app-card p-4">
                <h2 class="mb-2 text-sm font-bold text-amber-700 dark:text-amber-300">Kritik Stok Uyarıları</h2>
                <div class="space-y-1 text-sm">
                    @foreach($lowStockParts as $low)
                        <p class="text-slate-700 dark:text-slate-200">{{ $low->name }} - Mevcut: {{ number_format((float) $low->quantity, 2, ',', '.') }} / Min: {{ number_format((float) $low->minimum_stock, 2, ',', '.') }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="GET" class="app-card p-3">
            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Parça adı veya stok kodu ara..." class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
        </form>

        <div class="space-y-3">
            @foreach($parts as $part)
                @php $stock = $part->stocks->first(); @endphp
                <div class="app-card p-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $part->name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Kod: {{ $part->sku ?: '-' }} | Kategori: {{ $part->category ?: '-' }}</p>
                            <p class="mt-1 text-sm text-slate-700 dark:text-slate-300">
                                Stok: <span class="font-bold">{{ number_format((float) ($stock->quantity ?? 0), 2, ',', '.') }}</span> {{ $part->unit }}
                                | Satış: {{ number_format((float) $part->sale_price, 2, ',', '.') }} TL
                            </p>
                        </div>

                        <form method="POST" action="{{ route('parts.adjust-stock', $part) }}" class="grid w-full gap-2 sm:grid-cols-4 lg:w-auto">
                            @csrf
                            <select name="direction" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <option value="in">Stok Girişi</option>
                                <option value="out">Stok Çıkışı</option>
                                <option value="adjustment">Düzeltme (Yeni Toplam)</option>
                            </select>
                            <input type="number" step="0.01" min="0.01" name="quantity" placeholder="Miktar" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                            <input type="text" name="note" placeholder="Not" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
                                Kaydet
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div>{{ $parts->links() }}</div>
    </div>
@endsection
