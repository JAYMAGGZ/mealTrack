<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MealSlot;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ResidentController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private WalletService  $walletService,
    ) {}

    /**
     * Dashboard — wallet + today's bookings
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user()->load('wallet');

        $todayBookings = Booking::where('user_id', $user->id)
            ->whereHas('mealSlot', fn($q) => $q->whereDate('date', today()))
            ->with('mealSlot.menu')
            ->get();

        return response()->json([
            'wallet' => [
                'balance'          => $user->wallet->balance,
                'reserved_balance' => $user->wallet->reserved_balance,
                'total_credits'    => $user->wallet->total_credits,
            ],
            'today_bookings' => $todayBookings,
        ]);
    }

    /**
     * Wallet — balance + full transaction history
     */
    public function wallet(Request $request): JsonResponse
    {
        $user = $request->user()->load('wallet');

        $transactions = $user->creditTransactions()
            ->latest()
            ->get();

        return response()->json([
            'balance'          => $user->wallet->balance,
            'reserved_balance' => $user->wallet->reserved_balance,
            'total_credits'    => $user->wallet->total_credits,
            'transactions'     => $transactions,
        ]);
    }

    /**
     * Today's available meal slots with menu
     */
    public function mealSlots(Request $request): JsonResponse
    {
        $slots = MealSlot::active()
            ->today()
            ->with('menu')
            ->get()
            ->map(function ($slot) use ($request) {
                // Check if this resident has already booked this slot
                $booked = Booking::where('user_id', $request->user()->id)
                    ->where('meal_slot_id', $slot->id)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->exists();

                return [
                    'id'              => $slot->id,
                    'meal_type'       => $slot->meal_type,
                    'date'            => $slot->date,
                    'booking_cutoff'  => $slot->booking_cutoff,
                    'serving_start'   => $slot->serving_start,
                    'serving_end'     => $slot->serving_end,
                    'is_cutoff_passed'=> $slot->is_cutoff_passed,
                    'already_booked'  => $booked,
                    'menu'            => $slot->menu,
                ];
            });

        return response()->json(['meal_slots' => $slots]);
    }

    /**
     * Book a meal slot
     */
    public function book(Request $request): JsonResponse
    {
        $request->validate([
            'meal_slot_id' => 'required|integer|exists:meal_slots,id',
        ]);

        $slot = MealSlot::findOrFail($request->meal_slot_id);

        try {
            $booking = $this->bookingService->book($request->user(), $slot);

            return response()->json([
                'message' => 'Meal booked successfully.',
                'booking' => $booking->load('mealSlot'),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }

    /**
     * My bookings — all statuses
     */
    public function myBookings(Request $request): JsonResponse
    {
        $bookings = Booking::where('user_id', $request->user()->id)
            ->with('mealSlot.menu')
            ->latest()
            ->get();

        return response()->json(['bookings' => $bookings]);
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        // Resident can only cancel their own bookings
        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $this->bookingService->cancel($booking);

            return response()->json(['message' => 'Booking cancelled successfully.']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }

    /**
     * QR code — returns the resident's qr_token for display
     */
    public function qr(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'qr_token'    => $user->qr_token,
            'name'        => $user->name,
            'room_number' => $user->room_number,
            'balance'     => $user->wallet->balance,
        ]);
    }

    /**
     * Self check-in — resident scans the dining hall QR
     */
    public function selfCheckIn(Request $request): JsonResponse
    {
        $request->validate([
            'meal_slot_id' => 'required|integer|exists:meal_slots,id',
            'token'        => 'required|string',
        ]);

        $slot = MealSlot::findOrFail($request->meal_slot_id);

        // Validate the rotating token
        $expectedToken  = hash_hmac('sha256', floor(time() / 300) . $slot->id, config('app.key'));
        $previousToken  = hash_hmac('sha256', (floor(time() / 300) - 1) . $slot->id, config('app.key'));

        if (!hash_equals($expectedToken, $request->token) &&
            !hash_equals($previousToken, $request->token)) {
            return response()->json(['message' => 'Invalid or expired check-in token.'], 422);
        }

        try {
            $booking = $this->bookingService->selfCheckIn($request->user(), $slot);

            return response()->json([
                'message' => 'Checked in successfully. Enjoy your meal!',
                'booking' => $booking,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }

    /**
     * Submit a meal rating
     */
    public function rate(Request $request): JsonResponse
    {
        $request->validate([
            'meal_slot_id' => 'required|integer|exists:meal_slots,id',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:500',
        ]);

        // Only confirmed bookings can be rated
        $booking = Booking::where('user_id', $request->user()->id)
            ->where('meal_slot_id', $request->meal_slot_id)
            ->where('status', 'confirmed')
            ->first();

        if (!$booking) {
            return response()->json([
                'message' => 'You can only rate meals you have checked in for.',
            ], 422);
        }

        $rating = $request->user()->mealRatings()->updateOrCreate(
            ['meal_slot_id' => $request->meal_slot_id],
            ['rating' => $request->rating, 'comment' => $request->comment],
        );

        return response()->json([
            'message' => 'Rating submitted.',
            'rating'  => $rating,
        ]);
    }

    public function bookForFriend(Request $request): JsonResponse
{
    $request->validate([
        'meal_slot_id'   => 'required|integer|exists:meal_slots,id',
        'friend_user_id' => 'required|integer|exists:users,id',
    ]);

    $friend = \App\Models\User::findOrFail($request->friend_user_id);
    $slot   = MealSlot::findOrFail($request->meal_slot_id);

    if (!$friend->isResident()) {
        return response()->json([
            'message' => 'You can only book for other residents.',
        ], 422);
    }

    // Cannot book for yourself
    if ($friend->id === $request->user()->id) {
        return response()->json([
            'message' => 'Use the regular book endpoint for yourself.',
        ], 422);
    }

    try {
        $booking = $this->bookingService->bookForFriend(
            $request->user(),
            $friend,
            $slot
        );

        return response()->json([
            'message' => 'Meal booked for ' . $friend->name . ' successfully.',
            'booking' => $booking->load('mealSlot'),
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage(),
        ], $e->getCode() ?: 422);
    }
}
public function saveDeviceToken(Request $request): JsonResponse
{
    $request->validate([
        'token' => 'required|string',
    ]);

    $request->user()->update(['fcm_token' => $request->token]);

    return response()->json(['message' => 'Device token saved.']);
}
}