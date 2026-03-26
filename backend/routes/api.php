<?php

use App\Http\Controllers\AdminNavigationItemController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\vehicleTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Rutas Públicas
Route::post('/login', [AuthController::class, 'login']);

// Rutas Protegidas (Token Manual)
Route::middleware(['auth.token'])->group(function () {

    // Obtener información del usuario
    Route::get('/user', function (Request $request) {
        return $request->user()->load('role'); // Devolver usuario con rol
    });
});

// Rutas Públicas V1
Route::prefix('v1')->group(function () {
    // Productos: Lectura pública
    Route::get('/products', [ProductsController::class, 'index']);
    Route::get('/products/{id}', [ProductsController::class, 'show']);

    // CAtegorias: Lectura pública
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    // Registro de usuarios (Público)
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users', [UserController::class, 'index']);

    // Servicios: Lectura pública
    Route::get('/services/services-home', [ServiceController::class, 'getHomeServices']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);

    // Carts público
    Route::get('/carts', [CartController::class, 'index']);
    Route::get('/carts/{cart}', [CartController::class, 'show']);

    // Facturas Unificadas público
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);

    // Citas
    Route::get('/appointment', [AppointmentController::class, 'index']);
    Route::post('/appointment', [AppointmentController::class, 'store']);
    Route::put('/appointment/{appointmentId}', [AppointmentController::class, 'update']);
    Route::delete('/appointment/{appointmentId}', [AppointmentController::class, 'destroy']);
    Route::get('/appointment/{appointmentId}', [AppointmentController::class, 'show']);

    // Vehicles Types
    Route::get('/vehicleType', [vehicleTypeController::class, 'index']);
    Route::post('/vehicleType', [vehicleTypeController::class, 'store']);
    Route::put('/vehicleType', [vehicleTypeController::class, 'update']);
    Route::delete('/vehicleType', [vehicleTypeController::class, 'delete']);

    // Service Types
    Route::get('/serviceType', [ServiceTypeController::class, 'index']);
    Route::get('/serviceType/{id}', [ServiceTypeController::class, 'show']);
    

    // Admin Navigation Items
    Route::get('/admin-navigation', [AdminNavigationItemController::class, 'getSidenav']);
});

// Rutas Protegidas V1 (General)
Route::middleware(['auth.token'])->prefix('v1')->group(function () {
    // Cerrar Sesión
    Route::post('/logout', [AuthController::class, 'logout']);

    // Facturación Unificada
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/by-cart/{cartId}', [InvoiceController::class, 'getByCart']);

    Route::get("/user-cart", [CartController::class, "getCartByUserId"]);
    Route::get('/carts/{cart}', [CartController::class, 'show']);
    
    // Rutas de Carrito Unificado
    Route::post('/cart-items', [CartItemController::class, 'store']);
    Route::put('/cart-items/{id}', [CartItemController::class, 'update']);
    Route::delete('/cart-items/{id}', [CartItemController::class, 'destroy']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy']);
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/vehicles/{id}', [VehicleController::class, 'show']);
});

// Rutas Protegidas V1 (Admin)
Route::middleware(['auth.token', 'auth.admin'])->prefix('v1')->group(function () {
    // Productos: Gestión
    Route::post('/products', [ProductsController::class, 'store']);
    Route::put('/products/{id}', [ProductsController::class, 'update']);
    Route::delete('/products/{id}', [ProductsController::class, 'destroy']);

    // Categorias: Gestión
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Servicios: Gestión
    Route::post('/services', [ServiceController::class, 'store']);
    Route::put('/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
    Route::post('/services/{service}/toggle-home', [ServiceController::class, 'toggleShowOnHome']);

    // Service types
    Route::post('/serviceType', [ServiceTypeController::class, 'store']);
    Route::put('/serviceType/{id}', [ServiceTypeController::class, 'update']);
    Route::delete('/serviceType/{id}', [ServiceTypeController::class, 'destroy']);

    // Carts: Gestión
    Route::post('/carts', [CartController::class, 'store']);
    Route::put('/carts/{cart}', [CartController::class, 'update']);
    Route::delete('/carts/{cart}', [CartController::class, 'destroy']);

    // Facturas: Gestión
    Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);

    // Admin Navigation Items: Gestión
    Route::post('/admin-navigation', [AdminNavigationItemController::class, 'store']);
    Route::put('/admin-navigation/{item}', [AdminNavigationItemController::class, 'update']);
    Route::delete('/admin-navigation/{item}', [AdminNavigationItemController::class, 'destroy']);

    // Vehicles: Gestión
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);

    // Users: Gestión
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users/{id}/toggle-block', [UserController::class, 'toggleBlock']);
});
