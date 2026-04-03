<?php

use App\Http\Controllers\Api\ResidentController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard',             [ResidentController::class, 'dashboard']);
Route::get('/wallet',                [ResidentController::class, 'wallet']);
Route::get('/meal-slots',            [ResidentController::class, 'mealSlots']);
Route::get('/my-bookings',           [ResidentController::class, 'myBookings']);
Route::post('/book',                 [ResidentController::class, 'book']);
Route::delete('/bookings/{booking}', [ResidentController::class, 'cancel']);
Route::get('/qr',                    [ResidentController::class, 'qr']);
Route::post('/self-checkin',         [ResidentController::class, 'selfCheckIn']);
Route::post('/rate',                 [ResidentController::class, 'rate']);
Route::post('/book-for-friend', [ResidentController::class, 'bookForFriend']);
Route::post('/device-token', [ResidentController::class, 'saveDeviceToken']);