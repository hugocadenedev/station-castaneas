<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerOrder extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'customer_id',
        'client_name',
        'order_number',
        'ordered_at',
        'created_by',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function paloxes(): BelongsToMany
    {
        return $this->belongsToMany(Palox::class, 'customer_order_palox')
            ->withPivot('picked_net_weight_kg')
            ->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['customer_id', 'client_name', 'order_number', 'ordered_at'])
            ->logOnlyDirty();
    }
}