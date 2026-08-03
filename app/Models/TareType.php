<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TareType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'label',
        'weight_kg',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'weight_kg' => 'decimal:3',
    ];

    public function calibrations(): HasMany
    {
        return $this->hasMany(Calibration::class);
    }
}