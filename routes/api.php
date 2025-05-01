<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;

// Test route
Route::get('/test', function() {
    return response()->json(['message' => 'API is working']);
});

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

    // Class routes accessible by all authenticated users
    Route::get('classes/upcoming', [ClassController::class, 'upcomingClasses']);
    Route::get('classes/{class}', [ClassController::class, 'show']);

    // Student routes
    Route::middleware('role:student')->group(function () {
        // Add student specific routes here
    });

    // Instructor routes
    Route::middleware('role:instructor')->group(function () {
        Route::post('classes/schedule', [ClassController::class, 'schedule']);
    });

    // Admin routes
    Route::middleware('role:admin')->group(function () {
        // Add admin specific routes here
    });
});