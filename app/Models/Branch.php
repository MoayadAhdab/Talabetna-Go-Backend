<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'description',
        'phone',
        'email',
        'address',
        'city',
        'area',
        'latitude',
        'longitude',
        'delivery_radius_km',
        'minimum_order_amount',
        'delivery_fee',
        'is_active',
        'is_accepting_orders',
        'is_featured',
        'sort_order',
        'working_hours',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'delivery_radius_km' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',

            'is_active' => 'boolean',
            'is_accepting_orders' => 'boolean',
            'is_featured' => 'boolean',

            'working_hours' => 'array',
            'settings' => 'array',

            'sort_order' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
    public function carts(): HasMany
{
    return $this->hasMany(Cart::class);
}
}