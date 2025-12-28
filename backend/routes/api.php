<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CartInvoiceController;


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

//Esto es simplemente de prueba no es la ruta final
Route::group(['prefix' => 'v1'], function() {
    Route::apiResource('products', ProductsController::class);
    Route::apiResource('cartinvoice', CartInvoiceController::class);

    // UserRegistration
    Route::post('/users', [UserController::class, 'store']);
});
