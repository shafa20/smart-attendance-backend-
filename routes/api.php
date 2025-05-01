<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public routes
Route::post('auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Student routes
    Route::middleware('role:student')->group(function () {
        // Add student specific routes here
    });

    // Instructor routes
    Route::middleware('role:instructor')->group(function () {
        // Add instructor specific routes here
    });

    // Admin routes
    Route::middleware('role:admin')->group(function () {
        // Add admin specific routes here
    });
});
