@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <div class="app-card p-5 sm:p-6">
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Audit Log</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                Sistem üzerinde yapılan işlemler burada listelenir.
            </p>
        </div>

        <form method="GET" class="app-card p-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                <div>
                    <label for="action" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">İşlem Türü</label>
                    <select id="action" name="action" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <option value="">Tümü</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" @selected($selectedAction === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Filtrele</button>
            </div>
        </form>

        <div class="app-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Tarih</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Kullanıcı</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">İşlem</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Özet</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $log->created_at?->format('d.m.Y H:i:s') }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $log->user?->name ?? 'Sistem' }}</td>
                            <td class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-300">{{ $log->action }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $log->summary }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Henüz audit log kaydı yok.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $logs->links() }}
        </div>
    </div>
@endsection
