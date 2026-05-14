<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\PartStock;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PartController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $branchId = $user?->branch_id;
        $search = trim((string) $request->query('q', ''));

        $parts = Part::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->with(['stocks' => function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
            }])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $lowStockParts = collect();
        if ($branchId) {
            $lowStockParts = Part::query()
                ->join('part_stocks', 'part_stocks.part_id', '=', 'parts.id')
                ->where('part_stocks.branch_id', $branchId)
                ->whereColumn('part_stocks.quantity', '<=', 'parts.minimum_stock')
                ->orderBy('part_stocks.quantity')
                ->limit(8)
                ->get([
                    'parts.name',
                    'parts.minimum_stock',
                    'part_stocks.quantity',
                ]);
        }

        return view('parts.index', compact('parts', 'search', 'lowStockParts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['nullable', 'string', 'max:80', 'unique:parts,sku'],
            'category' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:20'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
        ]);

        $part = Part::create([
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? null,
            'category' => $validated['category'] ?? null,
            'unit' => $validated['unit'] ?? 'adet',
            'purchase_price' => $validated['purchase_price'] ?? 0,
            'sale_price' => $validated['sale_price'] ?? 0,
            'minimum_stock' => $validated['minimum_stock'] ?? 0,
            'is_active' => true,
        ]);

        if ($request->user()?->branch_id) {
            PartStock::firstOrCreate([
                'part_id' => $part->id,
                'branch_id' => $request->user()->branch_id,
            ], [
                'quantity' => 0,
            ]);
        }

        return back()->with('success', 'Parça kartı oluşturuldu.');
    }

    public function adjustStock(Request $request, Part $part): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'direction' => ['required', 'in:in,out,adjustment'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $branchId = Auth::user()?->branch_id;
        if (! $branchId) {
            return back()->withErrors(['stock' => 'Bu kullanıcı için şube atanmadı.']);
        }

        $stock = PartStock::firstOrCreate([
            'part_id' => $part->id,
            'branch_id' => $branchId,
        ], [
            'quantity' => 0,
        ]);

        $quantity = (float) $validated['quantity'];
        $direction = $validated['direction'];

        if ($direction === 'out' && (float) $stock->quantity < $quantity) {
            return back()->withErrors(['stock' => 'Stok yetersiz.']);
        }

        $newQuantity = (float) $stock->quantity;
        if ($direction === 'in') {
            $newQuantity += $quantity;
        } elseif ($direction === 'out') {
            $newQuantity -= $quantity;
        } else {
            $newQuantity = $quantity;
        }

        $stock->update(['quantity' => $newQuantity]);

        StockMovement::create([
            'part_id' => $part->id,
            'branch_id' => $branchId,
            'user_id' => Auth::id(),
            'direction' => $direction,
            'quantity' => $quantity,
            'unit_cost' => (float) $part->purchase_price,
            'source_type' => 'manual',
            'source_id' => $part->id,
            'note' => $validated['note'] ?? 'Manuel stok hareketi',
        ]);

        return back()->with('success', 'Stok hareketi kaydedildi.');
    }
}
