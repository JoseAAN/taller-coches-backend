
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

### Facturas de Productos (`facturas_productos`)
Representa la factura generada a partir de un **Pedido**.
- `id`: Identificador único.
- `numero_factura`: Folio.
- `total`: Monto total.
- `user_id`: Cliente.
- `pedido_id`: Relación directa con el pedido (`FK`).

### Facturas de Servicios (`facturas_servicios`)
Representa la factura generada a partir de una **Cita** de taller.
- `id`: Identificador único.
- `numero_factura`: Folio.
- `total`: Monto total.
- `user_id`: Cliente.
- `cita_id`: Relación directa con la cita (`FK`).

---

## Cambio Importante: Separación de Tablas

Anteriormente se utilizaba una estructura "Polimórfica" (Morph) que unificaba todo en una sola tabla `facturas`.
Se ha decidido **separar** en dos tablas para mayor claridad y simplicidad:

1.  `facturas_productos`: Exclusiva para ventas de productos.
2.  `facturas_servicios`: Exclusiva para servicios del taller.

Esto facilita las consultas directas y evita la complejidad de manejar `facturable_type`.

### Ejemplos de Consultas

#### Obtener factura de un Pedido
```php
$pedido = Pedido::find(1);
$factura = $pedido->factura; // Devuelve instancia de FacturaProducto
```

#### Obtener factura de una Cita
```php
$cita = Cita::find(1);
$factura = $cita->factura; // Devuelve instancia de FacturaServicio
```

#### Listar todas las facturas de productos de un usuario
```php
$facturas = FacturaProducto::where('user_id', $usuario->id)->get();
```
