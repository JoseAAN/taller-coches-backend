
## Modelo sin controlador

| Modelo | Estado |
|--------|--------|
| `Order.php` | Existe pero no tiene controlador ni rutas definidas. |
| `Category.php` | Existe pero no tiene controlador ni rutas definidas. Se usa en la relación con productos pero no se puede gestionar (crear, editar, eliminar) desde la API. |

---

## Rutas duplicadas en api.php

Existen dos bloques idénticos para `cartinvoice` (líneas 93 y 101):
```php
// Bloque 1 (línea 93)
Route::middleware(['auth.token'])->prefix('v1')->group(function () {
    Route::apiResource('cartinvoice', CartInvoiceController::class);
});

// Bloque 2 (línea 101) — DUPLICADO
Route::middleware(['auth.token'])->prefix('v1')->group(function () {
    Route::apiResource('cartinvoice', CartInvoiceController::class);
});
```

---

## Validación de contraseña

El frontend (login-component.vue) exige contraseñas de al menos 8 caracteres, pero la contraseña del usuario admin en el seeder es `1234` (4 caracteres). Esto impide iniciar sesión desde el frontend.

Opciones:
- Cambiar la contraseña del seeder a una de 8+ caracteres (ej: `admin1234`)
- Reducir la validación del frontend a 4 caracteres

---

## Rutas públicas sensibles

Las siguientes rutas están en la zona pública y podrían requerir autenticación:

| Ruta | Problema |
|------|----------|
| `GET /v1/users` | Listar usuarios es público. Cualquiera puede ver la lista de usuarios. |
| `GET /v1/carts` | Listar carritos es público. Los carritos contienen datos de compras de usuarios. |
| `GET /v1/cart-products` | Listar productos del carrito es público. |
| `GET /v1/product-invoices` | Listar facturas de productos es público. Contiene datos financieros. |
| `GET /v1/service-invoices` | Listar facturas de servicios es público. Contiene datos financieros. |
| `POST/PUT/DELETE /v1/vehicleType` | Gestión de tipos de vehículo es pública. Debería ser solo admin. |
| `POST/PUT/DELETE /v1/serviceType` | Gestión de tipos de servicio es pública. Debería ser solo admin. |
| `POST/PUT/DELETE /v1/appointment` | Gestión de citas es pública. Debería requerir autenticación. |
