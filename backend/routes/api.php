<?php

use App\Http\Controllers\AdminNavigationItemController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MailTestController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\vehicleTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Socialite;

Route::get('/google-auth/redirect', [AuthController::class, 'redirectGoogle']);
Route::get('/google-auth/callback', [AuthController::class, 'callbackGoogle']);

// Rutas Publicas
Route::post('/login', [AuthController::class, 'login']);

// Envio de correos
// el middleware hara que solo se puedan mandar 5 peticiones por minuto por cada IP aunque habria que tener en cuenta mas securizacion
Route::post('/contact', [MailTestController::class, 'receiveContact'])->middleware('throttle:5,1');

// Rutas Protegidas (Token Manual)
Route::middleware(['auth.token'])->group(function () {
    // Obtener informacion del usuario
    Route::get('/user', function (Request $request) {
        return $request->user()->load('role');
    });
});

// Rutas Publicas V1
Route::prefix('v1')->group(function () {
    // Productos: Lectura publica
    Route::get('/products', [ProductsController::class, 'index']);
    Route::get('/products/{id}', [ProductsController::class, 'show']);
    Route::post('/products/{id}/restock-subscribe', [ProductsController::class, 'subscribeToRestock']);

    // Categorias: Lectura publica
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    // Registro de usuarios
    Route::post('/users', [UserController::class, 'store']);

    // Servicios: Lectura publica
    Route::get('/services/services-home', [ServiceController::class, 'getHomeServices']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);

    // Vehicle types
    Route::get('/vehicleType', [vehicleTypeController::class, 'index']);

    // Service types
    Route::get('/serviceType', [ServiceTypeController::class, 'index']);
    Route::get('/serviceType/{id}', [ServiceTypeController::class, 'show']);
});

// Rutas Protegidas V1 (Usuario autenticado)
Route::middleware(['auth.token'])->prefix('v1')->group(function () {
    // Cerrar sesion
    Route::post('/logout', [AuthController::class, 'logout']);

    // Facturas: el usuario ve las suyas, el admin ve todas (controlado en el controller)
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/by-cart/{cartId}', [InvoiceController::class, 'getByCart']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);

    // Stripe
    Route::post('/checkout/stripe', [InvoiceController::class, 'createStripeSession']);

    // Carrito del usuario
    Route::get('/user-cart', [CartController::class, 'getCartByUserId']);
    Route::get('/carts/{cart}', [CartController::class, 'show']);

    // Items del carrito
    Route::post('/cart-items', [CartItemController::class, 'store']);
    Route::put('/cart-items/{id}', [CartItemController::class, 'update']);
    Route::delete('/cart-items/{id}', [CartItemController::class, 'destroy']);

    // Perfil del usuario
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Admin Navigation Items: lectura protegida
    Route::get('/admin-navigation', [AdminNavigationItemController::class, 'getSidenav']);

    // Vehiculos del usuario
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy']);
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/vehicles/{id}', [VehicleController::class, 'show']);

    // Citas (creacion y gestion propia)
    Route::get('/appointment', [AppointmentController::class, 'index']);
    Route::post('/appointment', [AppointmentController::class, 'store']);
    Route::put('/appointment/{appointmentId}', [AppointmentController::class, 'update']);
    Route::delete('/appointment/{appointmentId}', [AppointmentController::class, 'destroy']);
    Route::get('/appointment/{appointmentId}', [AppointmentController::class, 'show']);
});

// Rutas Protegidas V1 (Admin)
Route::middleware(['auth.token', 'auth.admin'])->prefix('v1')->group(function () {
    // Dashboard Stats
    Route::get('/dashboard-stats', [\App\Http\Controllers\DashboardController::class, 'getStats']);

    // Productos: Gestion
    Route::post('/products', [ProductsController::class, 'store']);
    Route::put('/products/{id}', [ProductsController::class, 'update']);
    Route::delete('/products/{id}', [ProductsController::class, 'destroy']);

    // Categorias: Gestion
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Servicios: Gestion
    Route::post('/services', [ServiceController::class, 'store']);
    Route::put('/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
    Route::post('/services/{service}/toggle-home', [ServiceController::class, 'toggleShowOnHome']);

    // Service types
    Route::post('/serviceType', [ServiceTypeController::class, 'store']);
    Route::put('/serviceType/{id}', [ServiceTypeController::class, 'update']);
    Route::delete('/serviceType/{id}', [ServiceTypeController::class, 'destroy']);

    // Carts: Gestion
    Route::post('/carts', [CartController::class, 'store']);
    Route::put('/carts/{cart}', [CartController::class, 'update']);
    Route::delete('/carts/{cart}', [CartController::class, 'destroy']);

    // Facturas: Gestion
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);

    // Admin Navigation Items: Gestion
    Route::post('/admin-navigation', [AdminNavigationItemController::class, 'store']);
    Route::put('/admin-navigation/{item}', [AdminNavigationItemController::class, 'update']);
    Route::delete('/admin-navigation/{item}', [AdminNavigationItemController::class, 'destroy']);

    // Vehicles: Gestion
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);

    // Users: Gestion
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/admin-users', [UserController::class, 'adminStore']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users/{id}/toggle-block', [UserController::class, 'toggleBlock']);

    // Citas: Gestion Admin
    Route::get('/appointments/all', [AppointmentController::class, 'adminIndex']);
    Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);
});
