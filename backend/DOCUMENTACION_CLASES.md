# Documentación de Clases

## Clase: User

### 1. Objetivo
La clase `User` es el modelo fundamental que representa a los usuarios dentro del sistema. Su objetivo principal es gestionar la identidad, la autenticación y la autorización de los actores que interactúan con la aplicación (clientes, administradores, empleados). Además, actúa como el eje central para relacionar las actividades del negocio, como la gestión de citas y pedidos, con individuos específicos.

### 2. Alcance
Esta clase abarca las siguientes responsabilidades y características:
- **Gestión de Identidad**: Almacena y gestiona información vital del usuario como `name`, `email` y `password`.
- **Autenticación y Seguridad**: Maneja la verificación de credenciales y la gestión de tokens de API (`api_token`) para el acceso seguro.
- **Roles y Permisos**: Se vincula con el modelo `Role` para determinar el nivel de acceso del usuario (`role_id`).
- **Historial de Operaciones**: Mantiene relaciones con los modelos de negocio:
    - `orders`: Pedidos realizados por el usuario.
    - `appointments`: Citas de servicio programadas por el usuario.
- **Funcionalidades Eloquent**: Aprovecha las características de Laravel como `Notifiable` para notificaciones y `HasFactory` para pruebas.

### 3. Dependencias
La clase `User` depende de los siguientes componentes y traits de Laravel y de la aplicación:

**Internas (Modelos):**
- `App\Models\Role` (BelongsTo): Define el rol del usuario.
- `App\Models\Order` (HasMany): Relación con pedidos.
- `App\Models\Appointment` (HasMany): Relación con citas.

**Externas (Laravel Framework):**
- `Illuminate\Foundation\Auth\User`: Clase base para modelos autenticables.
- `Illuminate\Notifications\Notifiable`: Trait para envío de notificaciones.
- `Laravel\Sanctum\HasApiTokens`: Trait para gestión de tokens (aunque el código actual muestra una implementación manual de `api_token`).
- `Illuminate\Database\Eloquent\Factories\HasFactory`: Trait para factories de base de datos.

### 4. Integración en la aplicación

#### 4.1. Referencia al proyecto
El namespace completo de la clase es:
`App\Models\User`

Se encuentra ubicada físicamente en:
`backend/app/Models/User.php`

#### 4.2. Uso de instancia de clases
- **Instanciación Implícita (Eloquent)**: La instancia se crea automáticamente al realizar consultas a la base de datos (ej. `User::find(1)`).
- **Inyección de Dependencias**: En los controladores, Laravel inyecta la instancia del usuario autenticado a través del `Request` o la fachada `Auth`.
- **Creación Manual**: Se puede instanciar manualmente `new User()` para preparar un objeto antes de persistirlo con `save()`.

#### 4.3. Ejemplos de uso

**Crear un nuevo usuario:**
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'name' => 'Juan Perez',
    'email' => 'juan@example.com',
    'password' => Hash::make('password123'),
    'role_id' => 2 // Asumiendo 2 es 'Cliente'
]);
```

**Obtener el usuario autenticado y sus citas:**
```php
// En un controlador
public function misCitas(Request $request) {
    $user = $request->user(); // Instancia de User autenticado
    $citas = $user->appointments; // Acceso a la relación hasMany
    return response()->json($citas);
}
```

**Verificar rol del usuario:**
```php
$user = User::find(1);
if ($user->role->name === 'admin') {
    // Lógica de administrador
}
```
