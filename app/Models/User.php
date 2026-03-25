<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * @mixin \Laravel\Sanctum\HasApiTokens
 * @method \Laravel\Sanctum\NewAccessToken createToken(string $name, array $abilities = ['*'], \DateTimeInterface|\DateInterval|int|null $expiresAt = null)
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'room_number',
        'phone',
        'qr_token',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'qr_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->wallet()->create([
                'balance'          => 0,
                'reserved_balance' => 0,
            ]);
        });
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(CreditWallet::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function mealRatings(): HasMany
    {
        return $this->hasMany(MealRating::class);
    }

    public function pendingBookings(): HasMany
    {
        return $this->hasMany(Booking::class)->where('status', 'pending');
    }

    public function isResident(): bool { return $this->role === 'resident'; }
    public function isChef(): bool     { return $this->role === 'chef'; }
    public function isAdmin(): bool    { return $this->role === 'admin'; }
}