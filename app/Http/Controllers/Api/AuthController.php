<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login — validate credentials, issue token, return role
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Wrong credentials
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();

        if (!$user instanceof User) {
            return response()->json([
                'message' => 'Authenticated user not found',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'role'  => $user->role,
            'user'  => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => $user->role,
                'room_number' => $user->room_number,
            ],
        ], 200);
    }

    /**
     * Logout — revoke the current token only
     */
    public function logout(Request $request): JsonResponse
    {
        // Deletes only the token used in this request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Return the authenticated user's profile
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('wallet');

        return response()->json([
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'room_number' => $user->room_number,
            'wallet'      => [
                'balance'          => $user->wallet?->balance ?? 0,
                'reserved_balance' => $user->wallet?->reserved_balance ?? 0,
            ],
        ], 200);
    }
}