# Estructura de Base de Datos y Configuración

Este documento describe el esquema actual de la base de datos, incluyendo Roles, Vehículos, Servicios y la lógica de Facturación, reflejando la estructura implementada en el backend.

## 1. Configuración Rápida

Para crear la base de datos y poblarla con datos iniciales (Roles, Admin, Productos, etc.), ejecuta:

```bash
php artisan migrate:fresh --seed
```

Este comando realizará lo siguiente:
1.  Eliminará todas las tablas existentes.
2.  Ejecutará todas las migraciones en el orden correcto.
3.  Ejecutará el `DatabaseSeeder`, creando:
    *   **Roles**: 'admin', 'client'.
    *   **Usuarios**: 1 Admin (`admin@admin.com`) y 9 Clientes (contraseña: `1234`).

## 2. Esquema de Tablas

### Seguridad y Usuarios (`users`, `roles`, `user_logs`)
*   **`roles`**
    *   `id`, `name` ('admin', 'client').
*   **`users`**
    *   `dni`, `phone` (teléfono), `address` (dirección).
    *   `login_attempts` (intentos de login), `blocked` (bloqueado), `unblock_time` (hora de desbloqueo).
    *   `role_id` (Clave foránea a `roles`).
*   **`user_logs`**
    *   Registra la actividad: `login_time`, `logout_time`, `ip`.

### Taller (`vehicle_types`, `vehicles`, `services`)
*   **`vehicle_types`** (Tipos de Vehículo)
    *   `name` (ej. SUV, Sedán), `dimensions`.
*   **`vehicles`** (Vehículos)
    *   `user_id` (Propietario).
    *   `vehicle_type_id` (Tipo).
    *   `license_plate` (matrícula), `color`, `model`, `brand` (marca).
*   **`service_types`** (Tipos de Servicio)
    *   `name` (ej. Mantenimiento, Reparación).
*   **`services`** (Servicios)
    *   `name` (nombre), `price` (precio), `average_duration` (duración media), `description`.

### Citas (`appointments`)
*   **`appointments`**
    *   `vehicle_id` (Qué coche recibe el servicio).
    *   `service_id` (Qué servicio se realiza).
    *   `appointment_date` (Fecha y hora de la cita).
    *   `final_price` (Precio final acordado).

### Tienda (`categories`, `products`, `carts`, `images`)
*   **`categories`**: Categorías de productos.
*   **`products`**: `name`, `price`, `stock`, `description`.
*   **`carts`*: Carritos de compra de usuarios.
*   **`carts_products`**: Tabla pivote para ítems en el carrito.
*   **`images` / `product_images`**: Gestión de imágenes de productos.

### Facturación (`invoices`)
Separado en dos flujos para diferenciar productos de servicios.
*   **`cart_invoices`**
    *   Vinculado a `cart_id`. Representa facturas de compra de productos de la tienda.
*   **`service_invoices`**
    *   Vinculado a `appointment_id`. Representa facturas de servicios realizados en el taller.

## 3. Relaciones Clave
*   **User -> Role**: `belongsTo` (Un usuario pertenece a un rol).
*   **User -> Vehicles**: `hasMany` (Un usuario tiene muchos vehículos).
*   **Appointment**: Vincula `Vehicle` y `Service` (Un vehículo recibe un servicio).
*   **CartInvoice**: `belongsTo(Cart)` (Una factura pertenece a un carrito).
*   **ServiceInvoice**: `belongsTo(Appointment)` (Una factura pertenece a una cita).
