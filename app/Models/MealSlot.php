<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MealSlot extends Model
{
    protected $fillable = [
        'meal_type',
    'date',
    'booking_cutoff',
    'late_booking_cutoff',
    'late_booking_credit_cost',
    'serving_start',
    'serving_end',
    'max_capacity',
    'is_active',
    ];

    protected $casts = [
        'date'                     => 'date',
    'booking_cutoff'           => 'datetime',
    'late_booking_cutoff'      => 'datetime',
    'serving_start'            => 'datetime',
    'serving_end'              => 'datetime',
    'is_active'                => 'boolean',
    'max_capacity'             => 'integer',
    'late_booking_credit_cost' => 'integer',,
    ];

    public function menu(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(MealRating::class);
    }

    public function getIsCutoffPassedAttribute(): bool
    {
        return now()->isAfter($this->booking_cutoff);
    }

    public function getConfirmedCountAttribute(): int
    {
        return $this->bookings()->where('status', 'confirmed')->count();
    }

    public function getPendingCountAttribute(): int
    {
        return $this->bookings()->where('status', 'pending')->count();
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getIsLateWindowOpenAttribute(): bool
{
    return $this->late_booking_cutoff &&
           now()->isAfter($this->booking_cutoff) &&
           now()->isBefore($this->late_booking_cutoff);
}
}