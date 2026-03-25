<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealRating extends Model
{
    protected $fillable = [
        'user_id',
        'meal_slot_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mealSlot(): BelongsTo
    {
        return $this->belongsTo(MealSlot::class);
    }
}