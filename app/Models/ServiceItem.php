<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_record_id',
        'inventory_part_id',
        'part_name',
        'quantity',
        'labor_or_part_fee',
        'use_inventory',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'labor_or_part_fee' => 'decimal:2',
        'use_inventory' => 'boolean',
    ];

    public function serviceRecord(): BelongsTo
    {
        return $this->belongsTo(ServiceRecord::class);
    }

    public function inventoryPart(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'inventory_part_id');
    }
}
