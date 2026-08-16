<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'sale_price',
        'image',
        'preparation_time_minutes',
        'is_available',
        'is_featured',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'preparation_time_minutes' => 'integer',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'settings' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
public function modifierGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            ModifierGroup::class,
            'product_modifier_group'
        )->withPivot([
            'sort_order',
        ]);
    }
    public function cartItems(): HasMany
{
    return $this->hasMany(CartItem::class);
}
}