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

    /**
     * Book a meal slot for a resident
     * Three guard clauses before any state mutation
     */
    public function book(User $user, MealSlot $slot): Booking
    {
        // Guard 1: does the resident have a spendable credit?
        if ($user->wallet->balance < 1) {
            throw new Exception('Insufficient credits to make a booking.', 422);
        }

        // Guard 2: has the booking window closed?
        if ($slot->is_cutoff_passed) {
            throw new Exception('Booking cutoff has passed for this meal slot.', 422);
        }

        // Guard 3: have they already booked this slot?
        $alreadyBooked = Booking::where('user_id', $user->id)
                                ->where('meal_slot_id', $slot->id)
                                ->exists();

        if ($alreadyBooked) {
            throw new Exception('You have already booked this meal slot.', 409);
        }

        // All guards passed — create booking and reserve credit atomically
        return DB::transaction(function () use ($user, $slot) {
            $booking = Booking::create([
                'user_id'      => $user->id,
                'meal_slot_id' => $slot->id,
                'status'       => 'pending',
            ]);

            // Reserve credit immediately after booking is created
            $this->walletService->reserveCredit($user, $booking->id);

            return $booking;
        });
    }

    /**
     * Resident cancels their booking before cutoff
     */
    public function cancel(Booking $booking): void
    {
        // Can only cancel a pending booking
        if (!$booking->isPending()) {
            throw new Exception('Only pending bookings can be cancelled.', 422);
        }

        // Cannot cancel after cutoff — slot is being prepared
        if ($booking->mealSlot->is_cutoff_passed) {
            throw new Exception('Cannot cancel after the booking cutoff has passed.', 422);
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'cancelled']);

            // Return the credit to spendable balance
            $this->walletService->releaseCredit($booking->user, $booking->id);
        });
    }

    /**
     * Chef confirms a resident checked in — QR scan or self check-in
     */
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

            // Credit fully consumed — removed from reserved
            $this->walletService->deductCredit(
                $booking->user,
                $booking->id,
                'Check-in confirmed'
            );
        });
    }

    /**
     * Scheduler runs this every 30 minutes
     * Finds all pending bookings past their cutoff and marks them no-show
     */
    public function processNoShows(): int
    {
        // Find every pending booking whose slot cutoff has already passed
        $overdueBookings = Booking::where('status', 'pending')
            ->whereHas('mealSlot', function ($query) {
                $query->where('booking_cutoff', '<', now());
            })
            ->with(['user.wallet', 'mealSlot'])
            ->get();

        $processed = 0;

        foreach ($overdueBookings as $booking) {
            DB::transaction(function () use ($booking) {
                $booking->update(['status' => 'no_show']);

                // Credit is consumed — no refund for no-shows
                $this->walletService->deductCredit(
                    $booking->user,
                    $booking->id,
                    'No-show: ' . $booking->mealSlot->meal_type . ' on ' . $booking->mealSlot->date
                );
            });

            $processed++;
        }

        return $processed; // Returns count for the scheduler log
    }

    /**
     * Self check-in — resident scans the dining hall QR
     * Finds their active booking for the slot automatically
     */
    public function selfCheckIn(User $user, MealSlot $slot): Booking
    {
        /** @var Booking|null $booking */
        $booking = Booking::where('user_id', $user->id)
                          ->where('meal_slot_id', $slot->id)
                          ->where('status', 'pending')
                          ->first();

        if (!$booking) {
            throw new Exception('No active booking found for this meal slot.', 422);
        }

        // Reuse confirmCheckIn — same logic regardless of who triggered it
        $this->confirmCheckIn($booking);

        return $booking->fresh();
    }
}