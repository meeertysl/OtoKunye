<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Vehicle $vehicle) {
            if (empty($vehicle->uuid)) {
                $vehicle->uuid = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'uuid',
        'brand',
        'model',
        'license_plate',
        'chassis_number',
        'engine_number',
        'fuel_type',
        'transmission_type',
        'current_km',
        'inspection_date',
        'inspection_reminder_sent_for_date',
        'customer_name',
        'customer_phone',
        'customer_email',
        'last_updated_by_user_id',
        'owner_user_id',
        'branch_id',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'inspection_reminder_sent_for_date' => 'date',
    ];

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class);
    }

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by_user_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
