<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protected by Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);

    // User profile paths (editable by the user)
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
    
    Route::apiResource('reservations', ReservationController::class);
});

// User Management Routes (CRUD) - For administrators only

Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {
    Route::apiResource('spaces', SpaceController::class);
    Route::apiResource('users', UserController::class);
    Route::get('/reports/reservations-by-space', [ReportController::class, 'reservationsBySpace']);
    Route::get('/reports/active-users', [ReportController::class, 'activeUsers']);
});

