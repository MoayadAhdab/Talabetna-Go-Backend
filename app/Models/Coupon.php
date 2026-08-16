<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'max_discount',
        'minimum_order_amount',
        'usage_limit',
        'usage_count',
        'per_customer_limit',
        'starts_at',
        'expires_at',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'per_customer_limit' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}