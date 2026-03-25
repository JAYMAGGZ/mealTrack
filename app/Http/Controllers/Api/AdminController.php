<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MealPlan;
use App\Models\MealSlot;
use App\Models\Menu;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function __construct(
        private WalletService $walletService,
    ) {}

    // -------------------------
    // Users
    // -------------------------

    public function listUsers(): JsonResponse
    {
        $users = User::with('wallet')->where('role', 'resident')->get();
        return response()->json(['users' => $users]);
    }

    public function createUser(Request $request): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|string|min:8',
            'role'        => 'required|in:resident,chef,admin',
            'room_number' => 'nullable|string',
            'phone'       => 'nullable|string',
        ]);

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'room_number' => $request->room_number,
            'phone'       => $request->phone,
            'qr_token'    => Str::uuid(),
        ]);

        return response()->json([
            'message' => 'User created successfully.',
            'user'    => $user->load('wallet'),
        ], 201);
    }

    // -------------------------
    // Meal Plans
    // -------------------------

    public function listMealPlans(): JsonResponse
    {
        return response()->json(['meal_plans' => MealPlan::all()]);
    }

    public function createMealPlan(Request $request): JsonResponse
    {
        $request->validate([
            'name'          => 'required|string',
            'credit_amount' => 'required|integer|min:1',
            'price'         => 'required|numeric|min:0',
            'description'   => 'nullable|string',
        ]);

        $plan = MealPlan::create($request->only(
            'name', 'credit_amount', 'price', 'description'
        ));

        return response()->json([
            'message'   => 'Meal plan created.',
            'meal_plan' => $plan,
        ], 201);
    }

    /**
     * Assign a meal plan to a resident — tops up their credits
     */
    public function assignMealPlan(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'      => 'required|integer|exists:users,id',
            'meal_plan_id' => 'required|integer|exists:meal_plans,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $plan = MealPlan::findOrFail($request->meal_plan_id);

        $this->walletService->topUp(
            $user,
            $plan->credit_amount,
            'Assigned meal plan: ' . $plan->name
        );

        return response()->json([
            'message'       => $plan->credit_amount . ' credits added to ' . $user->name,
            'wallet_balance'=> $user->fresh()->wallet->balance,
        ]);
    }

    // -------------------------
    // Meal Slots
    // -------------------------

    public function listMealSlots(): JsonResponse
    {
        $slots = MealSlot::with('menu')->latest('date')->get();
        return response()->json(['meal_slots' => $slots]);
    }

    public function createMealSlot(Request $request): JsonResponse
    {
       $request->validate([
    'meal_type'                => 'required|in:breakfast,lunch,dinner',
    'date'                     => 'required|date',
    'booking_cutoff'           => 'required|date',
    'late_booking_cutoff'      => 'nullable|date|after:booking_cutoff',
    'late_booking_credit_cost' => 'nullable|integer|min:1',
    'serving_start'            => 'required|date',
    'serving_end'              => 'required|date',
    'max_capacity'             => 'required|integer|min:1',
]);

$slot = MealSlot::create($request->only(
    'meal_type', 'date', 'booking_cutoff',
    'late_booking_cutoff', 'late_booking_credit_cost',
    'serving_start', 'serving_end', 'max_capacity'
));

        return response()->json([
            'message'   => 'Meal slot created.',
            'meal_slot' => $slot,
        ], 201);
    }

    // -------------------------
    // Menus
    // -------------------------

    public function addMenuItem(Request $request): JsonResponse
    {
        $request->validate([
            'meal_slot_id'  => 'required|integer|exists:meal_slots,id',
            'dish_name'     => 'required|string',
            'description'   => 'nullable|string',
            'is_vegetarian' => 'boolean',
        ]);

        $item = Menu::create($request->only(
            'meal_slot_id', 'dish_name', 'description', 'is_vegetarian'
        ));

        return response()->json([
            'message'   => 'Menu item added.',
            'menu_item' => $item,
        ], 201);
    }

    // -------------------------
    // Reports
    // -------------------------

    public function report(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $request->date ?? today();

        $slots = MealSlot::whereDate('date', $date)
            ->withCount([
                'bookings as total_booked',
                'bookings as total_confirmed' => fn($q) => $q->where('status', 'confirmed'),
                'bookings as total_no_show'   => fn($q) => $q->where('status', 'no_show'),
                'bookings as total_cancelled' => fn($q) => $q->where('status', 'cancelled'),
            ])
            ->get();

        return response()->json([
            'date'  => $date,
            'slots' => $slots,
        ]);
    }
}