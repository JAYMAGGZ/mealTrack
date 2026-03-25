<?php

namespace App\Services;

use App\Models\User;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class WalletService
{
    /**
     * Admin assigns a meal plan — credits land in spendable balance
     */
    public function topUp(User $user, int $credits, string $notes = 'Meal plan purchase'): void
    {
        DB::transaction(function () use ($user, $credits, $notes) {
            $wallet = $user->wallet;

            $wallet->increment('balance', $credits);

            CreditTransaction::create([
                'user_id'       => $user->id,
                'type'          => 'purchase',
                'amount'        => $credits,
                'balance_after' => $wallet->fresh()->balance,
                'notes'         => $notes,
            ]);
        });
    }

    /**
     * Called when a resident books a meal
     * Moves 1 credit from spendable → reserved
     */
    public function reserveCredit(User $user, int $bookingId, int $amount = 1): void
{
    DB::transaction(function () use ($user, $bookingId, $amount) {
        $wallet = $user->wallet;

        if ($wallet->balance < $amount) {
            throw new Exception('Insufficient balance to reserve credit.');
        }

        $wallet->decrement('balance', $amount);
        $wallet->increment('reserved_balance', $amount);

        CreditTransaction::create([
            'user_id'       => $user->id,
            'booking_id'    => $bookingId,
            'type'          => 'reserve',
            'amount'        => $amount,
            'balance_after' => $wallet->fresh()->balance,
            'notes'         => 'Credit reserved for booking #' . $bookingId,
        ]);
    });
}

    /**
     * Called when resident checks in OR is marked no-show
     * Removes 1 credit from reserved — credit is fully consumed
     */
    public function deductCredit(User $user, int $bookingId, string $reason = 'Check-in'): void
    {
        DB::transaction(function () use ($user, $bookingId, $reason) {
            $wallet = $user->wallet;

            if ($wallet->reserved_balance < 1) {
                throw new Exception('No reserved credit to deduct.');
            }

            $wallet->decrement('reserved_balance', 1);

            CreditTransaction::create([
                'user_id'       => $user->id,
                'booking_id'    => $bookingId,
                'type'          => 'deduct',
                'amount'        => 1,
                'balance_after' => $wallet->fresh()->balance,
                'notes'         => $reason . ' — booking #' . $bookingId,
            ]);
        });
    }

    /**
     * Called when resident cancels before cutoff
     * Returns 1 credit from reserved → spendable
     */
    public function releaseCredit(User $user, int $bookingId): void
    {
        DB::transaction(function () use ($user, $bookingId) {
            $wallet = $user->wallet;

            if ($wallet->reserved_balance < 1) {
                throw new Exception('No reserved credit to release.');
            }

            $wallet->decrement('reserved_balance', 1);
            $wallet->increment('balance', 1);

            CreditTransaction::create([
                'user_id'       => $user->id,
                'booking_id'    => $bookingId,
                'type'          => 'release',
                'amount'        => 1,
                'balance_after' => $wallet->fresh()->balance,
                'notes'         => 'Credit released — booking #' . $bookingId . ' cancelled',
            ]);
        });
    }

    /**
     * Admin manual correction — for disputes or data fixes
     */
    public function adminAdjust(User $user, int $credits, string $notes): void
    {
        DB::transaction(function () use ($user, $credits, $notes) {
            $wallet = $user->wallet;

            // Credits can be positive (adding) or negative (removing)
            $wallet->increment('balance', $credits);

            CreditTransaction::create([
                'user_id'       => $user->id,
                'type'          => 'admin_adjust',
                'amount'        => abs($credits),
                'balance_after' => $wallet->fresh()->balance,
                'notes'         => $notes,
            ]);
        });
    }
}