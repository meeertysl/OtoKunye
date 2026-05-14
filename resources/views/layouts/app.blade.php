<!doctype html>
<html lang="tr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/otokunye-logo.png') }}">
    <title>{{ $title ?? 'OTOKÜNYE' }}</title>
    <script>
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        };
    </script>
    <style>
        .app-card {
            border-radius: 1rem;
            border: 1px solid rgb(226 232 240 / 1);
            background: white;
            box-shadow: 0 1px 2px rgb(15 23 42 / 0.05), 0 0 0 1px rgb(15 23 42 / 0.04);
        }
        .dark .app-card {
            border-color: rgb(30 41 59 / 1);
            background: rgb(15 23 42 / 1);
            box-shadow: 0 1px 2px rgb(2 6 23 / 0.35), 0 0 0 1px rgb(148 163 184 / 0.08);
        }
    </style>
</head>
<body class="h-full bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
<div class="min-h-full">
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/80 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('vehicles.index') }}" class="group flex items-center gap-2.5">
                <span class="inline-flex h-11 w-11 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <img src="{{ asset('images/otokunye-logo.png') }}" alt="OTOKÜNYE logo" class="h-full w-full object-cover">
                </span>
                <span class="text-lg font-black tracking-tight text-slate-900 dark:text-slate-100">OTOKÜNYE</span>
                <span class="hidden rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-indigo-700 group-hover:border-indigo-300 dark:border-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300 sm:inline-flex">Servis</span>
            </a>
            @auth
                @php
                    $headerNotifications = auth()->user()
                        ? auth()->user()->notifications()->with('vehicle')->latest()->limit(8)->get()
                        : collect();
                    $headerUnreadNotificationCount = auth()->user()
                        ? auth()->user()->notifications()->where('is_read', false)->count()
                        : 0;
                @endphp
                <div class="ml-2 flex items-center gap-2">
                    @if(auth()->user()?->branch)
                        <span class="hidden rounded-full border border-slate-300 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 sm:inline-flex">
                            Şube: {{ auth()->user()->branch->name }}
                        </span>
                    @endif
                    <a href="{{ route('reports.index') }}" class="rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50">
                        Raporlar
                    </a>
                    <a href="{{ route('parts.index') }}" class="rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50">
                        Stok
                    </a>

                    <div class="relative">
                        <button
                            type="button"
                            id="notification-toggle"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800"
                        >
                            <span>Bildirim</span>
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                {{ $headerUnreadNotificationCount }}
                            </span>
                        </button>

                        <div
                            id="notification-panel"
                            class="absolute right-0 z-30 mt-2 hidden w-[22rem] rounded-xl border border-slate-200 bg-white p-3 shadow-xl dark:border-slate-700 dark:bg-slate-900"
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Yaklaşan Bakım / Muayene</p>
                                @if($headerUnreadNotificationCount > 0)
                                    <form method="POST" action="{{ route('notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                            Tümünü okundu yap
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="max-h-80 space-y-2 overflow-y-auto pr-1">
                                @forelse($headerNotifications as $notification)
                                    <div class="rounded-lg border px-2.5 py-2 {{ $notification->is_read ? 'border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900/30' : 'border-indigo-200 bg-indigo-50 dark:border-indigo-800 dark:bg-indigo-900/20' }}">
                                        <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ $notification->title }}</p>
                                        <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-300">{{ $notification->message }}</p>
                                        <div class="mt-2 flex items-center justify-between gap-2">
                                            @if($notification->vehicle)
                                                <a href="{{ route('vehicles.show', $notification->vehicle) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                                    Araca Git
                                                </a>
                                            @else
                                                <span></span>
                                            @endif

                                            @if(! $notification->is_read)
                                                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded border border-emerald-300 bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                        Okundu
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Şu an yeni bildirim bulunmuyor.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    @if(auth()->user()?->isAdmin())
                        <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50">
                            Kullanıcılar
                        </a>
                        <a href="{{ route('admin.audit-logs.index') }}" class="rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50">
                            Audit Log
                        </a>
                        <a href="{{ route('admin.backups.index') }}" class="rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50">
                            Yedekleme
                        </a>
                        <a href="{{ route('admin.branches.index') }}" class="rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50">
                            Şubeler
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                            Çıkış
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="relative mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-48 bg-gradient-to-b from-indigo-100/70 to-transparent dark:from-indigo-950/40"></div>
        @yield('content')
    </main>
</div>

<button
    id="theme-toggle"
    type="button"
    class="fixed bottom-4 right-4 z-50 inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
>
    <span id="theme-toggle-icon" aria-hidden="true">🌙</span>
    <span id="theme-toggle-label">Koyu Tema</span>
</button>

@if(session('success'))
    <div
        id="success-toast"
        class="fixed bottom-4 right-4 z-50 max-w-sm rounded-xl border border-emerald-300 bg-white px-4 py-3 text-sm text-emerald-800 shadow-lg dark:border-emerald-700 dark:bg-slate-900 dark:text-emerald-300"
    >
        <p class="font-semibold">İşlem Başarılı</p>
        <p class="mt-1">{{ session('success') }}</p>
    </div>
@endif

<script>
    const syncThemeButton = () => {
        const isDark = document.documentElement.classList.contains('dark');
        const icon = document.getElementById('theme-toggle-icon');
        const label = document.getElementById('theme-toggle-label');

        if (icon) icon.textContent = isDark ? '☀️' : '🌙';
        if (label) label.textContent = isDark ? 'Açık Tema' : 'Koyu Tema';
    };

    syncThemeButton();

    document.getElementById('theme-toggle')?.addEventListener('click', function () {
        document.documentElement.classList.toggle('dark');
        const isDark = document.documentElement.classList.contains('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        syncThemeButton();
    });

    const toast = document.getElementById('success-toast');
    if (toast) {
        setTimeout(() => {
            toast.style.transition = 'opacity 240ms ease';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 260);
        }, 2800);
    }

    const notificationToggle = document.getElementById('notification-toggle');
    const notificationPanel = document.getElementById('notification-panel');

    notificationToggle?.addEventListener('click', function (event) {
        event.stopPropagation();
        notificationPanel?.classList.toggle('hidden');
    });

    notificationPanel?.addEventListener('click', function (event) {
        event.stopPropagation();
    });

    document.addEventListener('click', function () {
        notificationPanel?.classList.add('hidden');
    });
</script>
</body>
</html>
