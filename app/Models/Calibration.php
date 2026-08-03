<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Calibration extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'reception_id',
        'caliber_id',
        'tare_type_id',
        'tare_weight_kg',
        'performed_by',
        'calibrated_at',
        'net_weight_kg',
        'waste_weight_kg',
    ];

    protected $casts = [
        'calibrated_at' => 'datetime',
        'tare_weight_kg' => 'decimal:3',
        'net_weight_kg' => 'decimal:3',
        'waste_weight_kg' => 'decimal:3',
    ];

    public function reception(): BelongsTo
    {
        return $this->belongsTo(Reception::class);
    }

    public function caliber(): BelongsTo
    {
        return $this->belongsTo(Caliber::class);
    }

    public function tareType(): BelongsTo
    {
        return $this->belongsTo(TareType::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function palox(): HasOne
    {
        return $this->hasOne(Palox::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['reception_id', 'caliber_id', 'tare_weight_kg', 'net_weight_kg', 'waste_weight_kg'])
            ->logOnlyDirty();
    }
}