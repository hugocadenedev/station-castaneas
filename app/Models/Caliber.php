<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Caliber extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fruit_id',
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function fruit(): BelongsTo
    {
        return $this->belongsTo(Fruit::class);
    }

    public function calibrations(): HasMany
    {
        return $this->hasMany(Calibration::class);
    }
}