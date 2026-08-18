<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'settings' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            if (! $category->parent_id) {
                return;
            }

            if ($category->exists && $category->parent_id === $category->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'A category cannot be its own parent.',
                ]);
            }

            $parent = self::query()->find($category->parent_id);

            if (! $parent || $parent->business_id !== $category->business_id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'The parent category must belong to the same merchant.',
                ]);
            }

            if ($parent->parent_id !== null) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Only one subcategory level is supported.',
                ]);
            }
        });
    }
}
