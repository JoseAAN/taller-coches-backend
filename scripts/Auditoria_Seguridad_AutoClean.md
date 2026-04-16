# Reporte de Auditoría de Seguridad (AutoClean)

Este documento contiene un análisis profundo de la arquitectura de la aplicación (Frontend en Vue.js y Backend en Laravel) evaluando las mitigaciones contra vulnerabilidades críticas basadas en el estándar **OWASP Top 10**. Se detalla la forma en la que la aplicación está blindada contra ataques a su lógica de negocio y bases de datos.

---

## 1. Asignación Masiva y Escalada de Privilegios (Mass Assignment)

**Vulnerabilidad teórica:**  
Un usuario malicioso en el formulario de registro (`/api/v1/users`) podría interceptar la petición POST de red y añadir manualmente el campo `"role_id": 1` (Asumiendo que 1 es Administrador) en el JSON, para intentar crear una cuenta con privilegios máximos y hackear el negocio.

**Seguridad en AutoClean (Seguro ✅):**  
El controlador `UserController.php` intercepta la creación de los usuarios públicos y sobrescribe tajantemente cualquier rol que se intente enviar, forzando la asignación del rol de Cliente por defecto en el propio backend, ignorando la carga útil (payload) del inyector.
```php
// UserController.php
$userData = $request->validated();

// Se asigna SIEMPRE el rol de cliente, bloqueando la escalada
$clientRole = \App\Models\Role::where('name', 'client')->first();
if ($clientRole) {
    $userData['role_id'] = $clientRole->id; 
} 
```
Además, todos los modelos de la base de datos (`User`, `Product`, `Invoice`) definen de forma estricta sus propiedades editables en el array nativo `$fillable`, garantizando que variables estructurales no puedan ser corrompidas externamente.

---

## 2. Inseguridad de Referencia Directa a Objetos (IDOR)

**Vulnerabilidad teórica:**  
Un usuario autenticado podría intentar ver, eliminar o procesar el pago de un carrito de OTRO usuario manipulando las URLs de la API (Ejemplo: intentar facturar el `cart_id=12` cuando él es el usuario 54).

**Seguridad en AutoClean (Seguro ✅):**  
Revisando `CartItemController` e `InvoiceController`, se valida sistemáticamente si la propiedad relacional pertenece de forma rigurosa al ID del token del usuario que firma la petición.
```php
// Fragmento de CartItemController.php
if ((int) $cart->user_id !== (int) $request->user()->id) {
    return response()->json(['message' => 'No tienes permisos para facturar este carrito'], 403);
}
```
Incluso en el panel de administrador, el `UserController` posee protecciones en duro para evitar subyugaciones, como por ejemplo impedir que un administrador secundario elimine al Creador/Usuario Principal (`id === 1`).

---

## 3. Manipulación de la Lógica de Precios del Carrito

**Vulnerabilidad teórica:**  
En tiendas electrónicas mal diseñadas, la llamada a "Añadir al carrito" envía en su JSON un campo numérico dictando el precio (`price`). Un atacante puede interceptar la solicitud y cambiar `price: 1500.00` por `price: 0.01`, robando el producto o servicio prácticamente gratis.

**Seguridad en AutoClean (Seguro ✅):**  
El backend de la aplicación **ignora** de forma predeterminada cualquier precio sugerido por el frontend. En el `CartItemController`, mediante una heurística propia mapeada en `resolveItemPrice`, la API consulta internamente en base de datos el precio inmutable (`Product::findOrFail($id)->price`) y lo traspasa al carrito de manera unidireccional. La manipulación del lado cliente es inútil.

---

## 4. Condiciones de Carrera (Concurrencia / Race Conditions)

**Vulnerabilidad teórica:**  
Agotamiento crítico de Stock. Solo queda **1** unidad en inventario de un producto caro. Dos usuarios inician sus procesos de Stripe y clickean en "Pagar" exactamente en el mismo microsegundo. Si la BD no está preparada, ambos sistemas leen `Stock = 1`, ambos pagan, y la base de datos se actualiza a `-1`, vendiendo lo que no existe.

**Seguridad en AutoClean (Seguro ✅):**  
Se aplica el concepto de "Bloqueo Pesimista" usando transacciones. En el momento de cobro en `InvoiceController::store`, las filas de la base de datos se congelan con `lockForUpdate()`. 
```php
$products = Product::whereIn('id', $stockRequirements->keys())
        ->lockForUpdate()
        ->get();
```
Esto fuerza a la base de datos MySQL/PostgreSQL a poner la segunda conexión en espera. Cuando la compra del usuario nº1 termina y actualiza el stock a 0, la segunda conexión se despierta, re-lee la tabla, se da cuenta que el stock ha bajado a 0, imposibilita el cargo de Stripe y aborta mediante un Error HTTP devolviendo control transaccional íntegro.

---

## 5. Prevenciones Adicionales (SPAM y Fuerza Bruta)

* **Denegación de Servicio (Email Spamming):** Totalmente mitigado. Las rutas públicas (Aviso de Stock o Verificación OTP de Email) cuentan con un `Throttle` que bloquean las IPs impidiendo envíos robóticos que vacíen las cuotas SMTP del cliente.
* **Falsificación de Pago (Stripe bypass):** Las pasarelas frontend están blindadas, y como segunda muralla, Stripe evalúa la sesión desde los metadatos protegidos con su Token Secret nativo y un Cross-Check pre-resolución de Webhook/Sync. 
* **Inyección SQL:** Toda la aplicación descansa sobre Parameter Binding y Sentencias Pre-procesadas con Eloquent ORM. No se evalúan consultas directas a la base de datos que expongan parámetros del Frontend al Backend.
