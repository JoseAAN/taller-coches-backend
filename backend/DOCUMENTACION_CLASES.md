# Documentación de Clases

## Clase: User

### 1. Objetivo
La clase `User` es el modelo fundamental que representa a los usuarios dentro del sistema. Su objetivo principal es gestionar la identidad, la autenticación y la autorización de los actores que interactúan con la aplicación (clientes, administradores, empleados). Además, actúa como el eje central para relacionar las actividades del negocio, como la gestión de citas y pedidos.

### 2. Alcance
Esta clase abarca las siguientes responsabilidades:
- **Gestión de Identidad**: Almacena y gestiona información vital del usuario. Mapea la tabla `users` de la base de datos.
- **Estructura de Datos**:
    - Campos asignables (`$fillable`): `name`, `email`, `password`, `role_id`, `api_token`.
- **Autenticación y Seguridad**: Maneja la verificación de credenciales y el acceso mediante API.
- **Roles y Permisos**: Se vincula con el modelo `Role` para determinar el nivel de acceso del usuario (`role_id`).
- **Historial de Operaciones**: Mantiene relaciones con los modelos de negocio (`orders`, `appointments`).

### 3. Dependencias
La clase `User` depende de los siguientes componentes y traits de Laravel y de la aplicación:

**Internas (Modelos):**
- `App\Models\Role` (Relación `BelongsTo` - N:1): Define el rol del usuario.
- `App\Models\Order` (Relación `HasMany` - 1:N): Pedidos realizados por el usuario.
- `App\Models\Appointment` (Relación `HasMany` - 1:N): Citas programadas por el usuario.

**Externas (Laravel Framework):**
- `Illuminate\Foundation\Auth\User`: Clase base extendida.
- `Illuminate\Notifications\Notifiable`: Trait para notificaciones.
- `Laravel\Sanctum\HasApiTokens`: Importado (aunque la gestión actual es manual).

### 4. Integración en la aplicación

#### 4.1. Referencia al proyecto
- **Namespace**: `App\Models\User`
- **Ubicación Física**: `backend/app/Models/User.php`

#### 4.2. Uso de instancia de clases
La clase se instancia principalmente a través del ORM Eloquent.
- **Obtención**: `User::find($id)` o a través del request `Auth::user()`.
- **Persistencia**: Se utiliza el método `create()` o `save()` para almacenar nuevos registros.

#### 4.3. Ejemplos de uso

**Creación de un usuario (Registro):**
```php
$user = User::create([
    'name' => 'Nuevo Cliente',
    'email' => 'cliente@example.com',
    'password' => Hash::make('secure_pass'),
    'role_id' => 2,
    'api_token' => Str::random(60) // Generación manual del token
]);
```

**Acceso a relaciones (Citas del usuario):**
```php
$user = Auth::user();
foreach ($user->appointments as $appointment) {
    echo $appointment->date;
}
```

### 5. Controlador: UserController

**Ubicación**: `backend/app/Http/Controllers/UserController.php`

| Método | Descripción |
|--------|-------------|
| `index()` | Lista todos los usuarios con su rol. |
| `store(StoreUserRequest)` | Registra un nuevo usuario. Asigna el rol "client" por defecto, hashea la contraseña y genera un token de autenticación. |
| `show($id)` | Muestra un usuario específico por ID con su rol. Devuelve 404 si no existe. |
| `update(Request, $id)` | Actualiza campos del usuario (name, email, password, role_id). Hashea la contraseña si se cambia. |
| `destroy($id)` | Elimina un usuario. Devuelve 404 si no existe. |
| `checkAccessToken(Request)` | Valida un token de API y devuelve los datos del usuario si es válido. |

### 6. Endpoints API

| Método HTTP | Ruta | Acción | Autenticación |
|-------------|------|--------|---------------|
| `GET` | `/api/v1/users` | Listar usuarios | Pública |
| `POST` | `/api/v1/users` | Registrar usuario | Pública |
| `GET` | `/api/v1/users/{id}` | Ver usuario | Admin |
| `PUT` | `/api/v1/users/{id}` | Actualizar usuario | Admin |
| `DELETE` | `/api/v1/users/{id}` | Eliminar usuario | Admin |

### 7. Estructura de la tabla

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| `id` | BIGINT (auto) | PRIMARY KEY |
| `dni` | VARCHAR(255) | UNIQUE, NULLABLE |
| `name` | VARCHAR(255) | NOT NULL |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL |
| `phone` | VARCHAR(255) | NULLABLE |
| `address` | TEXT | NULLABLE |
| `role_id` | BIGINT (FK) | FOREIGN KEY → `roles.id`, ON DELETE CASCADE |
| `login_attempts` | INTEGER | DEFAULT 0 |
| `blocked` | BOOLEAN | DEFAULT false |
| `unblock_time` | TIMESTAMP | NULLABLE |
| `email_verified_at` | TIMESTAMP | NULLABLE |
| `password` | VARCHAR(255) | NOT NULL |
| `remember_token` | VARCHAR(100) | NULLABLE |
| `created_at` | TIMESTAMP | NULLABLE |
| `updated_at` | TIMESTAMP | NULLABLE |

### 8. Validaciones (StoreUserRequest)

| Campo | Reglas | Mensaje de error |
|-------|--------|------------------|
| `name` | required, string, max:255 | "El nombre es obligatorio." |
| `email` | required, email, max:255, unique:users | "El correo electrónico es obligatorio." / "Este correo electrónico ya está registrado." |
| `password` | required, string, min:8, confirmed | "La contraseña debe tener al menos 8 caracteres." / "Las contraseñas no coinciden." |
| `captcha` | required, captcha | "Es necesario completar el captcha de seguridad." |

El campo `confirmed` espera que se envíe un campo adicional `password_confirmation` en la petición.

### 9. Middleware por endpoint

| Ruta | Middleware |
|------|-----------|
| `GET /api/v1/users` | Ninguno (público) |
| `POST /api/v1/users` | Ninguno (público) |
| `GET /api/v1/users/{id}` | `auth.token`, `auth.admin` |
| `PUT /api/v1/users/{id}` | `auth.token`, `auth.admin` |
| `DELETE /api/v1/users/{id}` | `auth.token`, `auth.admin` |

- `auth.token`: Verifica que el header `Authorization: Bearer <token>` contenga un token válido.
- `auth.admin`: Verifica que el usuario autenticado tenga `role.name === 'admin'`.

### 10. Respuestas JSON

**POST `/api/v1/users` (201 - Registro exitoso):**
```json
{
    "message": "Usuario registrado exitosamente",
    "access_token": "a1b2c3d4e5f6...",
    "token_type": "Bearer",
    "user": {
        "id": 5,
        "name": "Carlos García",
        "email": "carlos@ejemplo.com",
        "role_id": 2,
        "role": {
            "id": 2,
            "name": "client"
        }
    }
}
```

**GET `/api/v1/users` (200 - Listado):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Admin",
            "email": "admin@admin.com",
            "role_id": 1,
            "role": { "id": 1, "name": "admin" }
        },
        {
            "id": 2,
            "name": "Cliente",
            "email": "cliente@ejemplo.com",
            "role_id": 2,
            "role": { "id": 2, "name": "client" }
        }
    ]
}
```

**GET `/api/v1/users/{id}` (200 - Usuario encontrado):**
```json
{
    "data": {
        "id": 1,
        "name": "Admin",
        "email": "admin@admin.com",
        "role_id": 1,
        "role": { "id": 1, "name": "admin" }
    }
}
```

**GET `/api/v1/users/{id}` (404 - No encontrado):**
```json
{
    "message": "Usuario no encontrado"
}
```

**PUT `/api/v1/users/{id}` (200 - Actualizado):**
```json
{
    "message": "Usuario actualizado correctamente",
    "data": {
        "id": 1,
        "name": "Admin Actualizado",
        "email": "admin@admin.com",
        "role_id": 1,
        "role": { "id": 1, "name": "admin" }
    }
}
```

**DELETE `/api/v1/users/{id}` (200 - Eliminado):**
```json
{
    "message": "Usuario eliminado correctamente"
}
```
