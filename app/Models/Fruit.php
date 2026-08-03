<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fruit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function varieties(): HasMany
    {
        return $this->hasMany(Variety::class)->orderBy('name');
    }

    public function calibers(): HasMany
    {
        return $this->hasMany(Caliber::class)->orderBy('sort_order');
    }

    public function receptions(): HasMany
    {
        return $this->hasMany(Reception::class);
    }
}