# Backend - Taller de Coches

Este documento describe la instalación, configuración y estructura del backend del sistema de taller de coches.

## 1. Prerrequisitos

Asegúrate de tener instalado:
*   [PHP 8.2+](https://www.php.net/downloads.php)
*   [Composer](https://getcomposer.org/)
*   [MySQL](https://www.mysql.com/)

## 2. Instalación

1.  **Clonar el repositorio**:
    ```bash
    git clone <URL_DEL_REPOSITORIO>
    cd taller-coches-backend/backend
    ```

2.  **Instalar dependencias de PHP**:
    ```bash
    composer install
    ```

3.  **Configurar entorno**:
    Duplica el archivo de ejemplo y genera la clave de la aplicación.
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Configurar Base de Datos**:
    Abre el archivo `.env` y configura tus credenciales de base de datos:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=taller_coches
    DB_USERNAME=root
    DB_PASSWORD=
    ```

## 3. Configuración de Base de Datos (Semillas)

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

## 4. Esquema de Tablas

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

## 5. Relaciones Clave
*   **User -> Role**: `belongsTo` (Un usuario pertenece a un rol).
*   **User -> Vehicles**: `hasMany` (Un usuario tiene muchos vehículos).
*   **Appointment**: Vincula `Vehicle` y `Service` (Un vehículo recibe un servicio).
*   **CartInvoice**: `belongsTo(Cart)` (Una factura pertenece a un carrito).
*   **ServiceInvoice**: `belongsTo(Appointment)` (Una factura pertenece a una cita).

## 6. Documentación de la API

La documentación completa de la API está disponible a través de **Swagger UI**.

### Acceso
Una vez levantado el servidor (`php artisan serve`), puedes acceder a la documentación en:
👉 [http://127.0.0.1:8000/api/documentation](http://127.0.0.1:8000/api/documentation)

### Contenido
La documentación incluye detalles de los siguientes módulos:
*   **Auth**: Login, Logout, Obtener usuario actual.
*   **Usuarios**: Registro de nuevos usuarios.
*   **Productos**: Gestión completa (CRUD) de productos.
*   **Servicios**: Gestión completa de servicios ofrecidos por el taller.
*   **Carts (Carritos)**: Gestión de carritos de compra y sus productos (`CartProducts`).
*   **Invoices (Facturas)**:
    *   `ProductInvoices`: Facturas generadas por compras de productos.
    *   `ServiceInvoices`: Facturas generadas por servicios de citas.

### Generación
Si realizas cambios en las anotaciones de Swagger, es necesario regenerar la documentación con:
```bash
php artisan l5-swagger:generate
```
