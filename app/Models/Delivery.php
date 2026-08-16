<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'driver_id',
        'status',
        'assigned_at',
        'picked_up_at',
        'out_for_delivery_at',
        'delivered_at',
        'failed_at',
        'failure_reason',
        'pickup_latitude',
        'pickup_longitude',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_fee',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'out_for_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
            'pickup_latitude' => 'decimal:7',
            'pickup_longitude' => 'decimal:7',
            'delivery_latitude' => 'decimal:7',
            'delivery_longitude' => 'decimal:7',
            'delivery_fee' => 'decimal:2',
            'settings' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}