<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends Model
{
    protected $fillable = [
        'meal_slot_id',
        'dish_name',
        'description',
        'is_vegetarian',
    ];

    protected $casts = [
        'is_vegetarian' => 'boolean',
    ];

    public function mealSlot(): BelongsTo
    {
        return $this->belongsTo(MealSlot::class);
    }

    public function scopeVegetarian($query)
    {
        return $query->where('is_vegetarian', true);
    }
}