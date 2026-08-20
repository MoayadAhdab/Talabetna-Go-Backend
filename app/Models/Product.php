<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

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
public function modifierGroups()
{
    return $this->belongsToMany(
        ModifierGroup::class,
        'modifier_group_product'
    )
    ->withPivot('sort_order')
    ->orderBy('pivot_sort_order');
}
    }
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            $category = Category::query()->find($product->category_id);

            if (! $category || $category->business_id !== $product->business_id) {
                throw ValidationException::withMessages([
                    'category_id' => 'The category must belong to the selected merchant.',
                ]);
            }
        });
    }
}
