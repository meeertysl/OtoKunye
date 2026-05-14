@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="app-card p-5 sm:p-6">
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Kullanıcı Yönetimi</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                Admin olarak kullanıcı rollerini ve şifrelerini buradan yönetebilirsiniz.
            </p>
        </div>

        <div class="space-y-4">
            @foreach($users as $user)
                <div class="app-card p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $user->name }}</h2>
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $user->email }}</p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-300">
                                Rol: {{ $user->role }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Şube: {{ $user->branch?->name ?? 'Atanmadı' }}
                            </p>
                        </div>

                        <div class="flex w-full flex-col gap-4 lg:w-auto">
                            <form method="POST" action="{{ route('admin.users.update-branch', $user) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label for="branch-{{ $user->id }}" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Şube</label>
                                    <select
                                        id="branch-{{ $user->id }}"
                                        name="branch_id"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                                    >
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" @selected($user->branch_id === $branch->id)>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-slate-600 dark:hover:bg-slate-500">
                                    Şubeyi Güncelle
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.users.update-role', $user) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label for="role-{{ $user->id }}" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Rol</label>
                                    <select
                                        id="role-{{ $user->id }}"
                                        name="role"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                                    >
                                        <option value="usta" @selected($user->role === 'usta')>Usta</option>
                                        <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                    </select>
                                </div>
                                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                    Rolü Güncelle
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="grid gap-2 sm:grid-cols-2">
                                @csrf
                                @method('PUT')
                                <input
                                    type="password"
                                    name="password"
                                    placeholder="Yeni şifre"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                                    required
                                    minlength="6"
                                >
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    placeholder="Yeni şifre (tekrar)"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                                    required
                                    minlength="6"
                                >
                                <button type="submit" class="sm:col-span-2 rounded-lg border border-rose-300 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100 dark:border-rose-800 dark:bg-rose-900/30 dark:text-rose-300">
                                    Şifreyi Sıfırla
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
