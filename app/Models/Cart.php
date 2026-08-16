<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'branch_id',
        'status',
        'items_count',
        'subtotal',
        'delivery_fee',
        'discount',
        'total',
        'settings',
        'coupon_id',
'coupon_code',
    ];

    protected function casts(): array
    {
        return [
            'items_count' => 'integer',
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'settings' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
    public function coupon(): BelongsTo
{
    return $this->belongsTo(Coupon::class);
}
}