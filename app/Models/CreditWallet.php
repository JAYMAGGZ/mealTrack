<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditWallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'reserved_balance',
    ];

    protected $casts = [
        'balance'          => 'integer',
        'reserved_balance' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalCreditsAttribute(): int
    {
        return $this->balance + $this->reserved_balance;
    }

    public function getCanBookAttribute(): bool
    {
        return $this->balance >= 1;
    }
}