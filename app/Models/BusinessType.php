<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'status',
        'sort_order',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'settings' => 'array',
            'sort_order' => 'integer',
        ];
    }
}