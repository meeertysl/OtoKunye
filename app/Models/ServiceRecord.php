<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'branch_id',
        'entry_date',
        'exit_date',
        'master_note',
        'customer_approval_status',
        'estimated_amount',
        'next_service_date',
        'next_service_km',
        'reminder_sent_for_date',
        'reminder_sent_for_km',
        'invoice_url',
        'invoice_status',
        'invoice_external_id',
        'invoice_requested_at',
        'invoice_synced_at',
        'invoice_last_error',
        'before_image_path',
        'after_image_path',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'exit_date' => 'date',
        'next_service_date' => 'date',
        'next_service_km' => 'integer',
        'reminder_sent_for_date' => 'date',
        'reminder_sent_for_km' => 'integer',
        'invoice_requested_at' => 'datetime',
        'invoice_synced_at' => 'datetime',
        'customer_approval_status' => 'boolean',
        'estimated_amount' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function serviceItems(): HasMany
    {
        return $this->hasMany(ServiceItem::class);
    }
}
