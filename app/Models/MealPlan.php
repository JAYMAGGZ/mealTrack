<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPlan extends Model
{
    protected $fillable = [
        'name',
        'credit_amount',
        'price',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'credit_amount' => 'integer',
        'price'         => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}