<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'avatar',
        'vehicle_type',
        'vehicle_number',
        'status',
        'is_active',
        'is_verified',
        'latitude',
        'longitude',
        'last_location_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'last_location_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
    
}