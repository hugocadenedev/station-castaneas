<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Palox extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'reception_id',
        'calibration_id',
        'created_by',
        'palox_number',
        'initial_net_weight_kg',
        'remaining_net_weight_kg',
        'under_contract',
        'availability_status',
        'labeled_at',
    ];

    protected $casts = [
        'initial_net_weight_kg' => 'decimal:3',
        'remaining_net_weight_kg' => 'decimal:3',
        'under_contract' => 'boolean',
        'labeled_at' => 'datetime',
    ];

    public function reception(): BelongsTo
    {
        return $this->belongsTo(Reception::class);
    }

    public function calibration(): BelongsTo
    {
        return $this->belongsTo(Calibration::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(CustomerOrder::class, 'customer_order_palox')
            ->withPivot('picked_net_weight_kg')
            ->withTimestamps();
    }

    public function refreshAvailabilityStatus(): void
    {
        $remaining = (float) $this->remaining_net_weight_kg;

        if ($remaining === 0.0) {
            $this->availability_status = 'exhausted';

            return;
        }

        $initial = (float) $this->initial_net_weight_kg;
        $this->availability_status = $remaining < $initial ? 'partial' : 'available';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'palox_number',
                'initial_net_weight_kg',
                'remaining_net_weight_kg',
                'under_contract',
                'availability_status',
            ])
            ->logOnlyDirty();
    }
}