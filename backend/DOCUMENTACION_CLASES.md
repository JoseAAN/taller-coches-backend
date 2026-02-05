# Documentación de Clases

## Clase: User

### 1. Objetivo
La clase `User` es el modelo fundamental que representa a los usuarios dentro del sistema. Su objetivo principal es gestionar la identidad, la autenticación y la autorización de los actores que interactúan con la aplicación (clientes, administradores, empleados). Además, actúa como el eje central para relacionar las actividades del negocio, como la gestión de citas y pedidos, con individuos específicos.

### 2. Alcance
Esta clase abarca las siguientes responsabilidades y características:
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

> [!IMPORTANT]
> **Nota de Implementación**: La autenticación de API utiliza un campo `api_token` gestionado manualmente en la tabla `users`, en lugar de una tabla de tokens separada (como Sanctum estándar). Esto afecta cómo se instancia y recupera el usuario en las peticiones API.

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
