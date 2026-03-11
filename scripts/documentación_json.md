# JSON de Pruebas para Endpoints API

> **Base URL**: `http://127.0.0.1:8000/api`
>
> **Nota**: Los endpoints marcados con [AUTH] requieren header `Authorization: Bearer <TOKEN>`.
> Los endpoints marcados con [ADMIN] requieren además rol **admin**.

---

## 1. Autenticación (Auth)

### POST `/login` — Iniciar sesión
```json
{
    "email": "admin@admin.com",
    "password": "1234"
}
```

### POST `/logout` [AUTH] — Cerrar sesión
> No requiere body. Solo el header `Authorization`.

---

## 2. Usuarios (Users)

### POST `/v1/users` — Registrar usuario
```json
{
    "name": "Carlos García",
    "email": "carlos.garcia@ejemplo.com",
    "password": "password123",
    "password_confirmation": "password123",
    "captcha": "captcha_token"
}
```

---

## 3. Productos (Products)

### GET `/v1/products` — Listar productos
> No requiere body. Filtros opcionales por query params:
> - `?search=aceite` — buscar por nombre/descripción
> - `?category_id=1` — filtrar por categoría
> - `?page=2` — paginación

### GET `/v1/products/{id}` — Ver producto
> No requiere body. Ejemplo: `/v1/products/1`

### POST `/v1/products` [ADMIN] — Crear producto
```json
{
    "name": "Aceite Motor Sintético 5W-30",
    "description": "Aceite de alto rendimiento para motores de gasolina y diésel",
    "price": 45.99,
    "stock": 100,
    "categories": [1, 2]
}
```

**Caso mínimo (sin opcionales):**
```json
{
    "name": "Filtro de Aire",
    "price": 12.50,
    "stock": 30
}
```

### PUT `/v1/products/{id}` [ADMIN] — Actualizar producto
**Actualización parcial (solo campos que cambian):**
```json
{
    "price": 39.99,
    "stock": 85
}
```

**Actualización completa:**
```json
{
    "name": "Aceite Motor Sintético 5W-40",
    "description": "Fórmula mejorada para motores turbo",
    "price": 52.00,
    "stock": 60,
    "categories": [1]
}
```

### DELETE `/v1/products/{id}` [ADMIN] — Eliminar producto
> No requiere body. Ejemplo: DELETE `/v1/products/5`

---

## 4. Servicios (Services)

### GET `/v1/services` — Listar servicios
> Filtros opcionales:
> - `?search=limpieza` — buscar por nombre/descripción
> - `?service_type_id=1` — filtrar por tipo de servicio

### GET `/v1/services/{id}` — Ver servicio
> No requiere body.

### GET `/v1/services/services-home` — Servicios destacados en home
> No requiere body.

### POST `/v1/services` [ADMIN] — Crear servicio
```json
{
    "name": "Lavado Premium Completo",
    "description": "Incluye lavado exterior, interior, encerado y abrillantado",
    "price": 89.99,
    "average_duration": 120,
    "service_type_id": 1
}
```

### PUT `/v1/services/{id}` [ADMIN] — Actualizar servicio
```json
{
    "name": "Lavado Premium Plus",
    "price": 99.99,
    "average_duration": 150
}
```

### DELETE `/v1/services/{id}` [ADMIN] — Eliminar servicio
> No requiere body.

### POST `/v1/services/{id}/toggle-home` [ADMIN] — Mostrar/ocultar servicio en home
```json
{
    "show_on_home": true
}
```

---

## 5. Carritos (Carts)

### GET `/v1/carts` — Listar carritos
> No requiere body.

### GET `/v1/carts/{id}` — Ver carrito
> No requiere body.

### POST `/v1/carts` [ADMIN] — Crear carrito
```json
{
    "user_id": 1,
    "price": 150.00
}
```

### PUT `/v1/carts/{id}` [ADMIN] — Actualizar carrito
```json
{
    "price": 200.50
}
```

### DELETE `/v1/carts/{id}` [ADMIN] — Eliminar carrito
> No requiere body.

---

## 6. Productos del Carrito (Cart Products)

### GET `/v1/cart-products` — Listar productos del carrito
> No requiere body.

### GET `/v1/cart-products/{id}` — Ver producto del carrito
> No requiere body.

### POST `/v1/cart-products` [ADMIN] — Añadir producto al carrito
```json
{
    "cart_id": 1,
    "product_id": 3,
    "quantity": 2,
    "priceInTime": 45.99
}
```

### PUT `/v1/cart-products/{id}` [ADMIN] — Actualizar producto del carrito
```json
{
    "quantity": 5,
    "priceInTime": 42.00
}
```

### DELETE `/v1/cart-products/{id}` [ADMIN] — Eliminar producto del carrito
> No requiere body.

---

## 7. Facturas de Productos (Product Invoices)

### GET `/v1/product-invoices` — Listar facturas
> Filtros opcionales: `?search=PINV`

### GET `/v1/product-invoices/{id}` — Ver factura
> No requiere body.

### POST `/v1/product-invoices` [ADMIN] — Crear factura de productos
```json
{
    "total": 259.97,
    "cart_id": 1
}
```

### PUT `/v1/product-invoices/{id}` [ADMIN] — Actualizar factura
```json
{
    "total": 300.00
}
```

### DELETE `/v1/product-invoices/{id}` [ADMIN] — Eliminar factura
> No requiere body.

---

## 8. Facturas de Servicios (Service Invoices)

### GET `/v1/service-invoices` — Listar facturas
> Filtros opcionales: `?search=SINV`

### GET `/v1/service-invoices/{id}` — Ver factura
> No requiere body.

### POST `/v1/service-invoices` [ADMIN] — Crear factura de servicio
```json
{
    "total": 89.99,
    "user_id": 1,
    "appointment_id": 1
}
```

### PUT `/v1/service-invoices/{id}` [ADMIN] — Actualizar factura
```json
{
    "total": 95.00
}
```

### DELETE `/v1/service-invoices/{id}` [ADMIN] — Eliminar factura
> No requiere body.

---

## 9. Citas (Appointments)

### GET `/v1/appointment` — Listar citas
> No requiere body.

### GET `/v1/appointment/{id}` — Ver cita
> No requiere body.

### POST `/v1/appointment` — Crear cita
```json
{
    "vehicle_id": 1,
    "service_id": 2,
    "date": "2026-04-15",
    "start_time": "10:00"
}
```

### PUT `/v1/appointment/{id}` — Actualizar cita
```json
{
    "date": "2026-04-16",
    "start_time": "14:30"
}
```

### DELETE `/v1/appointment/{id}` — Eliminar cita
> No requiere body.

---

## 10. Tipos de Vehículo (Vehicle Types)

### GET `/v1/vehicleType` — Listar tipos
> No requiere body.

### POST `/v1/vehicleType` — Crear tipo
```json
{
    "name": "SUV"
}
```

---

## 11. Tipos de Servicio (Service Types)

### GET `/v1/serviceType` — Listar tipos
> No requiere body.

### POST `/v1/serviceType` — Crear tipo
```json
{
    "name": "Detailing"
}
```

---

## Configuración en Postman

### Headers comunes
| Header | Valor |
|--------|-------|
| `Content-Type` | `application/json` |
| `Accept` | `application/json` |
| `Authorization` | `Bearer <TOKEN>` (para rutas protegidas) |

### Obtener token
1. Hacer `POST /api/login` con las credenciales
2. Copiar el `access_token` de la respuesta
3. Usar en los headers: `Authorization: Bearer <access_token>`
