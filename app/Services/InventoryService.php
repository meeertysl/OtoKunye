<?php

namespace App\Services;

use App\Models\Part;
use App\Models\PartStock;
use App\Models\ServiceRecord;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use RuntimeException;

class InventoryService
{
    public function applyForServiceRecord(ServiceRecord $record, Collection $items, ?int $userId): void
    {
        foreach ($items as $item) {
            if (! ($item['use_inventory'] ?? false)) {
                continue;
            }

            $partId = (int) ($item['inventory_part_id'] ?? 0);
            $quantity = (float) ($item['quantity'] ?? 0);
            if ($partId <= 0 || $quantity <= 0) {
                continue;
            }

            $stock = PartStock::query()
                ->where('part_id', $partId)
                ->where('branch_id', $record->branch_id)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                throw new RuntimeException('Seçilen parça için şube stok kaydı bulunamadı.');
            }

            if ((float) $stock->quantity < $quantity) {
                $part = Part::query()->find($partId);
                throw new RuntimeException(($part?->name ?? 'Parça').' için stok yetersiz.');
            }

            $stock->update([
                'quantity' => (float) $stock->quantity - $quantity,
            ]);

            StockMovement::create([
                'part_id' => $partId,
                'branch_id' => $record->branch_id,
                'user_id' => $userId,
                'direction' => 'out',
                'quantity' => $quantity,
                'unit_cost' => (float) ($item['unit_cost'] ?? 0),
                'source_type' => 'service_record',
                'source_id' => $record->id,
                'note' => 'Bakım kaydı parça tüketimi',
            ]);
        }
    }

    public function revertForServiceRecord(ServiceRecord $record, ?int $userId, string $note = 'Bakım kaydı iadesi'): void
    {
        $movements = StockMovement::query()
            ->where('source_type', 'service_record')
            ->where('source_id', $record->id)
            ->where('direction', 'out')
            ->where('is_reverted', false)
            ->lockForUpdate()
            ->get();

        foreach ($movements as $movement) {
            $stock = PartStock::query()
                ->where('part_id', $movement->part_id)
                ->where('branch_id', $movement->branch_id)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = PartStock::create([
                    'part_id' => $movement->part_id,
                    'branch_id' => $movement->branch_id,
                    'quantity' => 0,
                ]);
            }

            $stock->update([
                'quantity' => (float) $stock->quantity + (float) $movement->quantity,
            ]);

            StockMovement::create([
                'part_id' => $movement->part_id,
                'branch_id' => $movement->branch_id,
                'user_id' => $userId,
                'direction' => 'in',
                'quantity' => (float) $movement->quantity,
                'unit_cost' => (float) $movement->unit_cost,
                'source_type' => 'service_record',
                'source_id' => $record->id,
                'note' => $note,
            ]);

            $movement->update(['is_reverted' => true]);
        }
    }
}
