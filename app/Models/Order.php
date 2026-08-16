<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'branch_id',
        'cart_id',
        'status',
        'payment_status',
        'delivery_status',
        'payment_method',
        'customer_note',
        'subtotal',
        'delivery_fee',
        'discount',
        'tax',
        'total',
        'delivery_address',
        'branch_snapshot',
        'settings',
        'coupon_id',
'coupon_code',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',

            'delivery_address' => 'array',
            'branch_snapshot' => 'array',
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

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    public function statusHistory(): HasMany
{
    return $this->hasMany(OrderStatusHistory::class);
}
public function payments(): HasMany
{
    return $this->hasMany(Payment::class);
}
public function delivery(): HasOne
{
    return $this->hasOne(Delivery::class);
}
public function coupon(): BelongsTo
{
    return $this->belongsTo(Coupon::class);
}
}