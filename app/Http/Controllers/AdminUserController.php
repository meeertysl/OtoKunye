<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->with('branch')->latest()->get();
        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'branches'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,usta'],
        ]);

        $user->update([
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Kullanıcı rolü güncellendi.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kullanıcı şifresi sıfırlandı.');
    }

    public function updateBranch(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $user->update([
            'branch_id' => (int) $validated['branch_id'],
        ]);

        return back()->with('success', 'Kullanıcı şubesi güncellendi.');
    }
}
