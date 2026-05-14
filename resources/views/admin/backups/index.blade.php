@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <div class="app-card p-5 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Veritabani Yedekleme</h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                        SQLite yedeklerini buradan olusturabilir ve geri yukleyebilirsiniz.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.backups.create') }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Simdi Yedek Al
                    </button>
                </form>
            </div>
        </div>

        <div class="app-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Dosya</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Boyut</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Tarih</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($backups as $backup)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $backup['name'] }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ number_format($backup['size'] / 1024, 2, ',', '.') }} KB</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $backup['modified_at'] }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.backups.restore', ['file' => $backup['name']]) }}" onsubmit="return confirm('Bu yedek geri yuklensin mi? Mevcut veritabani dosyasi degisecek.');">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:border-rose-800 dark:bg-rose-900/30 dark:text-rose-300">
                                        Geri Yukle
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Henuz yedek dosyasi bulunmuyor.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
