<?php

use App\Http\Controllers\TripRequestController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Travel request routes
    Route::post('/trip-requests', [TripRequestController::class, 'store']);
    Route::get('/trip-requests/{id}', [TripRequestController::class, 'show']);
    Route::get('/trip-requests', [TripRequestController::class, 'index']);
    Route::patch('/trip-requests/{id}/status', [TripRequestController::class, 'updateStatus']);
});
