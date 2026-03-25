<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MealSlot;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChefController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
    ) {}

    /**
     * Headcount for a specific meal slot
     */
    public function headcount(Request $request, MealSlot $mealSlot): JsonResponse
    {
        return response()->json([
            'meal_type'       => $mealSlot->meal_type,
            'date'            => $mealSlot->date,
            'pending_count'   => $mealSlot->pending_count,
            'confirmed_count' => $mealSlot->confirmed_count,
            'total_expected'  => $mealSlot->pending_count + $mealSlot->confirmed_count,
        ]);
    }

    /**
     * Today's slots with live headcounts
     */
    public function todaySlots(): JsonResponse
    {
        $slots = MealSlot::today()
            ->active()
            ->withCount([
                'bookings as pending_bookings'   => fn($q) => $q->where('status', 'pending'),
                'bookings as confirmed_bookings' => fn($q) => $q->where('status', 'confirmed'),
            ])
            ->get();

        return response()->json(['slots' => $slots]);
    }

    /**
     * QR scan check-in — chef scans resident QR
     */
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'qr_token'     => 'required|string',
            'meal_slot_id' => 'required|integer|exists:meal_slots,id',
        ]);

        // Resolve the resident from their QR token
        $user = User::where('qr_token', $request->qr_token)->first();

        if (!$user) {
            return response()->json(['message' => 'Unknown QR code.'], 404);
        }

        // Find their pending booking for this slot
        $booking = Booking::where('user_id', $user->id)
            ->where('meal_slot_id', $request->meal_slot_id)
            ->where('status', 'pending')
            ->first();

        if (!$booking) {
            return response()->json([
                'message' => 'No active booking found for this resident.',
                'name'    => $user->name,
            ], 422);
        }

        try {
            $this->bookingService->confirmCheckIn($booking);

            return response()->json([
                'message'     => 'Checked in successfully.',
                'name'        => $user->name,
                'room_number' => $user->room_number,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }
}