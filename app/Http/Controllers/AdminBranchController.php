<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBranchController extends Controller
{
    public function index(): View
    {
        $branches = Branch::query()->latest()->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', 'unique:branches,code'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        Branch::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Yeni şube eklendi.');
    }

    public function toggleStatus(Branch $branch): RedirectResponse
    {
        $branch->update([
            'is_active' => ! $branch->is_active,
        ]);

        return back()->with('success', 'Şube durumu güncellendi.');
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', 'unique:branches,code,'.$branch->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $branch->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return back()->with('success', 'Şube bilgileri güncellendi.');
    }
}
