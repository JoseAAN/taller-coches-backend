<?php

use App\Http\Controllers\AdminNavigationItemController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartInvoiceController;
use App\Http\Controllers\CartProductController;
use App\Http\Controllers\ProductInvoiceController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceInvoiceController;
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

    Route::get('/Profile', [ProfileController::class, 'show']);
    // Productos: Lectura pública
    Route::get('/products', [ProductsController::class, 'index']);
    Route::get('/products/{id}', [ProductsController::class, 'show']);

    // Registro de usuarios (Público)
    Route::post('/users', [UserController::class, 'store']);

    // Servicios: Lectura pública
    Route::get('/services/services-home', [ServiceController::class, 'getHomeServices']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);

    // Carts público
    Route::get('/carts', [CartController::class, 'index']);
    Route::get('/carts/{cart}', [CartController::class, 'show']);

    // Facturas de productos público
    Route::get('/product-invoices', [ProductInvoiceController::class, 'index']);
    Route::get('/product-invoices/{id}', [ProductInvoiceController::class, 'show']);

    // Facturas de servicios público
    Route::get('/service-invoices', [ServiceInvoiceController::class, 'index']);
    Route::get('/service-invoices/{id}', [ServiceInvoiceController::class, 'show']);

     // Citas
    Route::get('/appointment', [AppointmentController::class, 'index']);
    Route::post('/appointment', [AppointmentController::class, 'store']);
    Route::put('/appointment/{appointmentId}', [AppointmentController::class, 'update']);
    Route::delete('/appointment/{appointmentId}', [AppointmentController::class, 'destroy']);
    Route::get('/appointment/{appointmentId}', [AppointmentController::class, 'show']);
    // Cart Poducts público
    Route::get('/cart-products', [CartProductController::class, 'index']);
    Route::get('/cart-products/{id}', [CartProductController::class, 'show']);
    //vehicles Types
    Route::get('/vehicleType', [vehicleTypeController::class, 'index']);
    Route::post('/vehicleType', [vehicleTypeController::class, 'store']);
    Route::put('/vehicleType', [vehicleTypeController::class, 'update']);
    Route::delete('/vehicleType', [vehicleTypeController::class, 'delete']);

    //Service Types
    Route::get('/serviceType', [ServiceTypeController::class, 'index']);
    Route::post('/serviceType', [ServiceTypeController::class, 'store']);
    Route::put('/serviceType', [ServiceTypeController::class, 'update']);
    Route::delete('/serviceType', [ServiceTypeController::class, 'destroy']);
    // Admin Navigation Items
    Route::get('/admin-navigation', [AdminNavigationItemController::class, 'getSidenav']);


    });

// Rutas Protegidas V1 (General)
Route::middleware(['auth.token'])->prefix('v1')->group(function () {
    // Carrito / Facturación
    Route::apiResource('cartinvoice', CartInvoiceController::class);

    Route::get("/user-cart", [CartController::class, "getCartByUserId"]);
});

// Rutas Protegidas V1 (Admin)
Route::middleware(['auth.token', 'auth.admin'])->prefix('v1')->group(function () {
    // Productos: Gestión
    Route::post('/products', [ProductsController::class, 'store']);
    Route::put('/products/{id}', [ProductsController::class, 'update']);
    Route::delete('/products/{id}', [ProductsController::class, 'destroy']);

    // Servicios: Gestión
    Route::post('/services', [ServiceController::class, 'store']);
    Route::put('/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
    Route::post('/services/{service}/toggle-home', [ServiceController::class, 'toggleShowOnHome']);

    // Carts: Gestión
    Route::post('/carts', [CartController::class, 'store']);
    Route::put('/carts/{cart}', [CartController::class, 'update']);
    Route::delete('/carts/{cart}', [CartController::class, 'destroy']);


    // Facturas de productos: Gestión
    Route::post('/product-invoices', [ProductInvoiceController::class, 'store']);
    Route::put('/product-invoices/{id}', [ProductInvoiceController::class, 'update']);
    Route::delete('/product-invoices/{id}', [ProductInvoiceController::class, 'destroy']);

    //Facturas de servicios: Gestión
    Route::post('/service-invoices', [ServiceInvoiceController::class, 'store']);
    Route::put('/service-invoices/{id}', [ServiceInvoiceController::class, 'update']);
    Route::delete('/service-invoices/{id}', [ServiceInvoiceController::class, 'destroy']);

    // Cart Products: Gestión
    Route::post('/addToCart', [CartProductController::class, 'store']);
    Route::put('/cart-products/{id}', [CartProductController::class, 'update']);
    Route::delete('/cart-products/{id}', [CartProductController::class, 'destroy']);

    // Admin Navigation Items: Gestión
    Route::post('/admin-navigation', [AdminNavigationItemController::class, 'store']);
    Route::put('/admin-navigation/{item}', [AdminNavigationItemController::class, 'update']);
    Route::delete('/admin-navigation/{item}', [AdminNavigationItemController::class, 'destroy']);

    // Vehicles: Gestión
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy']);

});
