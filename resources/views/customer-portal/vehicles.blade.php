@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100">Araçlarım</h1>
            <form method="POST" action="{{ route('customer.portal.logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    Çıkış Yap
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @forelse($vehicles as $vehicle)
                <a href="{{ route('customer.portal.vehicle.show', $vehicle) }}" class="app-card block p-4 transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-300">Dijital Servis Geçmişi</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-slate-100">{{ $vehicle->brand }} {{ $vehicle->model }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $vehicle->license_plate }}</p>
                    <p class="mt-2 text-xs font-medium text-slate-500 dark:text-slate-400">Detayı görüntülemek için tıklayın</p>
                </a>
            @empty
                <div class="app-card p-6">
                    <p class="text-sm text-slate-500 dark:text-slate-400">Bu telefon numarası için araç kaydı bulunmuyor.</p>
                </div>
            @endforelse
        </div>

        <div>
            {{ $vehicles->links() }}
        </div>
    </div>
@endsection
