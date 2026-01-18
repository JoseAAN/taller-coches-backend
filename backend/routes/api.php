<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CartInvoiceController;
use App\Http\Controllers\ServiceController;


// Rutas Públicas
Route::post('/login', [AuthController::class, 'login']);



// Rutas Protegidas (Token Manual)
Route::middleware(['auth.token'])->group(function () {

    // Cerrar Sesión
    Route::post('/logout', [AuthController::class, 'logout']);

    // Obtener información del usuario
    Route::get('/user', function (Request $request) {
        return $request->user()->load('role'); // Devolver usuario con rol
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

    // Servicios: Lectura pública
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);

    // Carts público
    Route::get('/carts', [CartController::class, 'index']);
    Route::get('/carts/{cart}', [CartController::class, 'show']);
});

// Rutas Protegidas V1 (General)
Route::middleware(['auth.token'])->prefix('v1')->group(function () {
    // Carrito / Facturación
    Route::apiResource('cartinvoice', CartInvoiceController::class);
});

// Rutas Protegidas V1 (Admin)
Route::middleware(['auth.token', 'auth.admin'])->prefix('v1')->group(function () {
    // Productos: Gestión
    Route::post('/products', [ProductsController::class, 'store']);
    Route::put('/products/{product}', [ProductsController::class, 'update']);
    Route::delete('/products/{product}', [ProductsController::class, 'destroy']);

    // Servicios: Gestión
    Route::post('/services', [ServiceController::class, 'store']);
    Route::put('/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);

    // Carts: Gestión
    Route::post('/carts', [CartController::class, 'store']);
    Route::put('/carts/{cart}', [CartController::class, 'update']);
    Route::delete('/carts/{cart}', [CartController::class, 'destroy']);
});
