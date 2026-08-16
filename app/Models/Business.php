<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_type_id',
        'name',
        'slug',
        'description',
        'logo',
        'cover_image',
        'phone',
        'email',
        'address',
        'city',
        'latitude',
        'longitude',
        'commission_rate',
        'is_active',
        'is_featured',
        'sort_order',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'commission_rate' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'settings' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
    public function categories(): HasMany
{
    return $this->hasMany(Category::class);
}
public function products(): HasMany
{
    return $this->hasMany(Product::class);
}
}