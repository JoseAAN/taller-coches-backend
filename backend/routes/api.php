<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public Routes
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Get User info
    Route::get('/user', function (Request $request) {
        return $request->user()->load('role'); // Return user with role
    });
});
