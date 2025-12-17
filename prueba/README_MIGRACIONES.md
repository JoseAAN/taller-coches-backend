
## Estructura de las Tablas

### Pedidos (`pedidos`)
Representa una compra realizada por un usuario.
- `id`: Identificador único del pedido (PK).
- `user_id`: Relación con el usuario que hizo el pedido.
- `total`: Monto total del pedido.
- `estado`: Estado del pedido (ej. 'pagado').

### Citas (`citas`)
Representa una reserva o cita de taller.
- `id`: Identificador único de la cita (PK).
- `user_id`: Relación con el usuario.
- `fecha_hora`: Fecha y hora de la cita.
- `matricula`: Matrícula del vehículo asociado.

### Facturas (`facturas`)
Representa el documento de facturación. **Una factura puede pertenecer TANTO a un Pedido COMO a una Cita.**
- `id`: Identificador único de la factura.
- `numero_factura`: Folio o código de la factura.
- `total`: Monto total facturado.
- `user_id`: Usuario al que pertenece la factura.
- `facturable_id`: ID del registro original (sea pedido o cita).
- `facturable_type`: **El "Valor Morph"** (Nombre de la clase del modelo).

---

## El Funcionamiento del "Morph" (`facturable`)

En la migración de `facturas`:
```php
$table->morphs('facturable');
```
Esta línea crea automáticamente dos columnas clave:

1.  **`facturable_id`**: Guarda el ID numérico del pedido o de la cita.
2.  **`facturable_type`**: Guarda el nombre del Modelo (Clase PHP) al que pertenece ese ID. **Este es el valor "Morph".**

### ¿Cómo funciona el valor de `morph` (`facturable_type`)?

Laravel usa este campo para saber en qué tabla buscar el ID almacenado en `facturable_id`.

#### Ejemplo Práctico:

Imagina que tienes los siguientes datos:

- Un **Pedido** con ID **105**.
- Una **Cita** con ID **30**.

Si generas facturas para ambos, la tabla `facturas` se vería así:

| id | numero_factura | total | user_id | facturable_id | facturable_type |
|----|---------------|-------|---------|---------------|-----------------|
| 1 | FAC-001 | 50.00 | 5 | **105** | **App\Models\Pedido** |
| 2 | FAC-002 | 120.00| 8 | **30** | **App\Models\Cita** |

- En el **Registro 1**: `facturable_type` dice `App\Models\Pedido`. Laravel sabe que debe ir a la tabla `pedidos` y buscar el ID `105`.
- En el **Registro 2**: `facturable_type` dice `App\Models\Cita`. Laravel sabe que debe ir a la tabla `citas` y buscar el ID `30`.

### Ventaja
No necesitas crear tablas separadas como `facturas_pedidos` y `facturas_citas`, ni llenar la tabla de facturas con columnas vacías (`pedido_id`, `cita_id`). El sistema polimórfico se adapta dinámicamente.

---

## Consultas y Relaciones (Ejemplos)

### Búsqueda de Facturas por Usuario y Viceversa

#### De Usuario a Facturas
Para obtener todas las facturas de un usuario específico (`$userId`), puedes usar una consulta estándar de Eloquent:

```
php
$facturas = Factura::where('user_id', $userId)->get();

```

> **Nota:** Si agregas `public function facturas() { return $this->hasMany(Factura::class); }` a tu modelo `User`, podrías simplemente usar `$user->facturas`.

#### De Factura a Usuario (Inverso)
Para saber a quién pertenece una factura, usas la relación `user()` definida en el modelo `Factura`:

```
php
$factura = Factura::find(1);
$usuario = $factura->user; // Devuelve el modelo User
echo $usuario->name;
```

## Listado de Facturas con Filtro (Polimórfico)

Gracias a la columna `facturable_type`, puedes filtrar fácilmente qué facturas son de pedidos y cuáles son de citas.

### Solo Facturas de Pedidos
Para obtener únicamente las facturas generadas por ventas de productos:

```
php
use App\Models\Pedido;
use App\Models\Factura;

$facturasDePedidos = Factura::where('facturable_type', 'pedido')->get();
```

### Solo Facturas de Citas
Para obtener únicamente las facturas generadas por servicios de taller:

```
php
use App\Models\Cita;
use App\Models\Factura;

$facturasDeCitas = Factura::where('facturable_type', 'cita')->get();
```

### Obtener el objeto original (Pedido o Cita) desde la Factura
Si tienes una factura y quieres acceder a los detalles originales (ej. ver los productos o la matrícula del coche):

```
php
$factura = Factura::find(1);
$origen = $factura->facturable; // Devuelve un modelo Pedido O Cita automáticamente

if ($origen instanceof App\Models\Pedido) {
    echo "Es un pedido con estado: " . $origen->estado;
} elseif ($origen instanceof App\Models\Cita) {
    echo "Es una cita para el coche: " . $origen->matricula;
}
```

---

## Consultas Avanzadas y Optimización

### Evitar Problemas de Rendimiento (Eager Loading)
Si vas a listar muchas facturas, **SIEMPRE** usa `with('facturable')`. Esto carga todos los pedidos y citas relacionados en solo 2 consultas extra, en lugar de una consulta por cada factura (Problema N+1).

```
php
// ✅ FORMA CORRECTA (Rápida)
$facturas = Factura::with('facturable')->get();

foreach ($facturas as $factura) {
    // Esto ya no hace consultas a la base de datos
    echo $factura->facturable->id; 
}
```

### Relaciones Inversas (Desde el Origen)
Si tienes un Pedido o una Cita y quieres ver su factura asociada:

**Desde un Pedido:**
```
php
// Obtener un pedido y su factura en una sola llamada (Eager Loading)
$pedido = Pedido::with('factura')->find(10);
$suFactura = $pedido->factura; // Devuelve el modelo Factura o null
```

**Desde una Cita:**
```
php
$cita = Cita::find(5);
$suFactura = $cita->factura;
```
*(Esto funciona porque definimos `morphOne` en los modelos `Pedido` y `Cita`).*

### Filtros Combinados (Usuario + Tipo)
Para obtener, por ejemplo, **"Todas las facturas de Citas del Usuario Juan"**:

```
php
use App\Models\Cita;

$facturasDeTallerDeJuan = Factura::where('user_id', $juanId)
                                 ->where('facturable_type', 'cita')
                                 ->get();
```

### Buscar Facturas por Fecha (Global)
Al ser una tabla única, reportear por fechas es muy sencillo:

```
php
// Facturas generadas en Diciembre 2025
$reporte = Factura::whereBetween('created_at', ['2025-12-01', '2025-12-31'])
                  ->with(['user', 'facturable']) // Optimizado
                  ->get();
```

---

### Personalización (MorphMap)

Por defecto, Laravel guarda el nombre completo de la clase en la base de datos (ej. `App\Models\Pedido`).
Tú preguntas: **¿Se puede guardar solo "pedido" en su lugar?**
**Respuesta: SÍ.**

### ¿Cómo se hace?
En tu archivo `app/Providers/AppServiceProvider.php`, en el método `boot()`:

```
php
use Illuminate\Database\Eloquent\Relations\Relation;

public function boot(): void
{
    Relation::morphMap([
        'pedido' => 'App\Models\Pedido',
        'cita'   => 'App\Models\Cita',
    ]);
}
```

### Resultado en Base de Datos
Ahora, cuando crees una factura, la tabla se verá así:

| id | numero_factura | facturable_id | facturable_type |
|----|---------------|---------------|-----------------|
| 1 | FAC-001 | 105 | **pedido** |
| 2 | FAC-002 | 30 | **cita** |


