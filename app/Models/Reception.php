<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Reception extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'reception_number',
        'received_at',
        'supplier_id',
        'fruit_id',
        'variety_id',
        'received_by',
        'gross_weight_kg',
        'conformity_status',
        'non_conformity_reason',
        'processing_status',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'gross_weight_kg' => 'decimal:3',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function fruit(): BelongsTo
    {
        return $this->belongsTo(Fruit::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(Variety::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function calibrations(): HasMany
    {
        return $this->hasMany(Calibration::class);
    }

    public function paloxes(): HasMany
    {
        return $this->hasMany(Palox::class);
    }

    public function isNonConforming(): bool
    {
        return $this->conformity_status === 'non_conforming';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'reception_number',
                'supplier_id',
                'fruit_id',
                'variety_id',
                'gross_weight_kg',
                'conformity_status',
                'processing_status',
            ])
            ->logOnlyDirty();
    }
}