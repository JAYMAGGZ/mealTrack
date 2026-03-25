<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes — no token required
|--------------------------------------------------------------------------
*/
Route::post('/login',  [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| USSD endpoint — called by telco gateway, no Bearer token
|--------------------------------------------------------------------------
*/
// Route::post('/ussd', [UssdController::class, 'handle']);
// Uncomment this when you build the USSD feature

/*
|--------------------------------------------------------------------------
| Protected routes — token required
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    /*
    | Resident routes
    */
    Route::middleware('role:resident')
         ->prefix('v1/resident')
         ->group(base_path('routes/resident.php'));

    /*
    | Chef routes
    */
    Route::middleware('role:chef')
         ->prefix('v1/chef')
         ->group(base_path('routes/chef.php'));

    /*
    | Admin routes — admin can also access chef routes for overrides
    */
    Route::middleware('role:admin')
         ->prefix('v1/admin')
         ->group(base_path('routes/admin.php'));

});