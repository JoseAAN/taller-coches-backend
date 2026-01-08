<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
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
// Rutas Públicas V1
Route::prefix('v1')->group(function () {
    // Productos: Lectura pública
    Route::get('/products', [ProductsController::class, 'index']);
    Route::get('/products/{product}', [ProductsController::class, 'show']);

    // Registro de usuarios (Público)
    Route::post('/users', [UserController::class, 'store']);

    // All Carts
    Route::get('/carts', [CartController::class, 'index']);
    Route::get('/carts/{cart}', [CartController::class, 'show']);
});

// Rutas Protegidas V1 (Requieren Autenticación)
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    
    // Productos: Gestión (Admin debería ser validado aquí o en controlador)
    Route::post('/products', [ProductsController::class, 'store']);
    Route::put('/products/{product}', [ProductsController::class, 'update']);
    Route::delete('/products/{product}', [ProductsController::class, 'destroy']);

    // Carrito / Facturación
    Route::apiResource('cartinvoice', CartInvoiceController::class);
});
