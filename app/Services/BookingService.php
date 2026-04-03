<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\MealSlot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class BookingService
{
    public function __construct(
        private WalletService $walletService
    ) {}

    public function book(User $user, MealSlot $slot): Booking
    {
        // Guard 1: sufficient credits
        $creditCost = 1;

        if ($slot->is_cutoff_passed) {
            // Normal cutoff passed — check late window
            if (!$slot->is_late_window_open) {
                throw new Exception('Booking cutoff has passed for this meal slot.', 422);
            }
            // Late booking costs more
            $creditCost = $slot->late_booking_credit_cost ?? 2;
        }

        // Guard 2: enough credits for the cost
        if ($user->wallet->balance < $creditCost) {
            throw new Exception(
                $creditCost > 1
                    ? "Late booking requires {$creditCost} credits. You don't have enough."
                    : 'Insufficient credits to make a booking.',
                422
            );
        }

        // Guard 3: duplicate booking
        $alreadyBooked = Booking::where('user_id', $user->id)
                                ->where('meal_slot_id', $slot->id)
                                ->whereIn('status', ['pending', 'confirmed'])
                                ->exists();

        if ($alreadyBooked) {
            throw new Exception('You have already booked this meal slot.', 409);
        }

        $booking = DB::transaction(function () use ($user, $slot, $creditCost) {
            $booking = Booking::create([
                'user_id'         => $user->id,
                'meal_slot_id'    => $slot->id,
                'status'          => 'pending',
                'is_late_booking' => $creditCost > 1,
            ]);

            $this->walletService->reserveCredit($user, $booking->id, $creditCost);

            return $booking;
        });

        app(NotificationService::class)->sendToUser(
            $user,
            'Meal booked!',
            "Your {$slot->meal_type} on {$slot->date->format('D d M')} is confirmed."
        );

        return $booking;
    }

    public function bookForFriend(User $proxy, User $friend, MealSlot $slot): Booking
    {
        // Guard 1: friend has credits
        if ($friend->wallet->balance < 1) {
            throw new Exception('Your friend has insufficient credits.', 422);
        }

        // Guard 2: cutoff not passed — proxy booking not allowed in late window
        if ($slot->is_cutoff_passed) {
            throw new Exception('Booking cutoff has passed for this meal slot.', 422);
        }

        // Guard 3: friend not already booked
        $alreadyBooked = Booking::where('user_id', $friend->id)
                                ->where('meal_slot_id', $slot->id)
                                ->whereIn('status', ['pending', 'confirmed'])
                                ->exists();

        if ($alreadyBooked) {
            throw new Exception('Your friend has already booked this meal slot.', 409);
        }

        return DB::transaction(function () use ($proxy, $friend, $slot) {
            $booking = Booking::create([
                'user_id'           => $friend->id,
                'booked_by_user_id' => $proxy->id,
                'meal_slot_id'      => $slot->id,
                'status'            => 'pending',
                'is_proxy_booking'  => true,
            ]);

            // Credit from friend's wallet
            $this->walletService->reserveCredit($friend, $booking->id);

            return $booking;
        });
    }

    public function cancel(Booking $booking): void
    {
        if (!$booking->isPending()) {
            throw new Exception('Only pending bookings can be cancelled.', 422);
        }

        if ($booking->mealSlot->is_cutoff_passed && !$booking->mealSlot->is_late_window_open) {
            throw new Exception('Cannot cancel after the booking cutoff has passed.', 422);
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'cancelled']);
            $this->walletService->releaseCredit($booking->user, $booking->id);
        });
    }

    public function confirmCheckIn(Booking $booking): void
    {
        if (!$booking->isPending()) {
            throw new Exception('This booking cannot be checked in.', 422);
        }

        DB::transaction(function () use ($booking) {
            $booking->update([
                'status'        => 'confirmed',
                'checked_in_at' => now(),
            ]);

            $this->walletService->deductCredit(
                $booking->user,
                $booking->id,
                'Check-in confirmed'
            );
        });
    }

    public function processNoShows(): int
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Booking> $overdueBookings */
        $overdueBookings = Booking::where('status', 'pending')
            ->whereHas('mealSlot', function ($query) {
                // Only process after BOTH cutoffs have passed
                $query->where('booking_cutoff', '<', now())
                      ->where(function ($q) {
                          $q->whereNull('late_booking_cutoff')
                            ->orWhere('late_booking_cutoff', '<', now());
                      });
            })
            ->with(['user.wallet', 'mealSlot'])
            ->get();

        $processed = 0;

        foreach ($overdueBookings as $booking) {
            /** @var Booking $booking */
            DB::transaction(function () use ($booking) {
                $booking->update(['status' => 'no_show']);

                $this->walletService->deductCredit(
                    $booking->user,
                    $booking->id,
                    'No-show: ' . $booking->mealSlot->meal_type . ' on ' . $booking->mealSlot->date
                );
            });

            app(NotificationService::class)->sendToUser(
                $booking->user,
                'Missed meal',
                "You missed your {$booking->mealSlot->meal_type}. 1 credit was deducted."
            );

            $processed++;
        }

        return $processed;
    }

    public function selfCheckIn(User $user, MealSlot $slot): Booking
    {
        $booking = Booking::where('user_id', $user->id)
                          ->where('meal_slot_id', $slot->id)
                          ->where('status', 'pending')
                          ->first();

        if (!$booking) {
            throw new Exception('No active booking found for this meal slot.', 422);
        }

        $this->confirmCheckIn($booking);

        return $booking->fresh();
    }
}