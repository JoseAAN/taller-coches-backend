# Medidas de Seguridad en Base de Datos SQL

Este documento detalla las estrategias de seguridad implementadas y recomendadas para el sistema de base de datos "Taller de Coches", alineándose con las mejores prácticas de la industria (OWASP, CIS Benchmarks).

## 1. Prevención de Inyección SQL

La inyección SQL es una de las vulnerabilidades más críticas en aplicaciones web. Aunque este riesgo se mitiga principalmente en la capa de aplicación, el diseño de la base de datos juega un papel de soporte crucial.

### Implementación
*   **Desde la Aplicación**:
    *   **Uso OBLIGATORIO de Sentencias Preparadas (Prepared Statements)**: Nunca concatenar cadenas de entrada de usuario directamente en consultas SQL.
    *   **Ejemplo Seguro (PDO/Laravel)**:
        ```php
        // Correcto: Uso de bindings
        DB::select('SELECT * FROM users WHERE email = ?', [$email]);
        ```
    *   **Ejemplo Vulnerable (PROHIBIDO)**:
        ```php
        // Incorrecto: Concatenación directa
        DB::select("SELECT * FROM users WHERE email = '$email'");
        ```
*   **En Procedimientos Almacenados (si se usaran)**: Evitar SQL dinámico (`EXECUTE`) con entradas no saneadas.

## 2. Gestión de Contraseñas y Datos Sensibles

La protección de datos sensibles es fundamental para la privacidad y cumplimiento legal (GDPR).

### Implementación
*   **Hashing de Contraseñas**:
    *   Las contraseñas **NUNCA** se almacenan en texto plano.
    *   Se utiliza el algoritmo **bcrypt** (estándar actual robusto).
    *   En el script `02_insert_data.sql`, las contraseñas insertadas son hashes válidos generados por `password_hash()`.
*   **Tokens de API**:
    *   Se almacenan tokens para autenticación manual simple. En un entorno de producción de alta seguridad, se recomienda **hashing de tokens** (SHA-256) al almacenarlos y validar el hash entrante, similar a Laravel Sanctum.

## 3. Integridad y Consistencia de Datos

Asegurar que los datos sean válidos y consistentes previene errores lógicos y corrupción.

### Implementación
*   **Claves Foráneas (Foreign Keys)**:
    *   Garantizan la integridad referencial. No se puede crear un `vehicle` para un `user` inexistente.
    *   Reglas `ON DELETE / ON UPDATE`:
        *   `CASCADE`: Para relaciones dependientes fuertes (ej. borrar usuario -> borrar sus carritos).
        *   `RESTRICT`: Para evitar borrados accidentales de datos maestros (ej. impedir borrar una categoría si tiene productos).
*   **Transacciones (ACID)**:
    *   Operaciones críticas que involucran múltiples tablas (ej. crear pedido y descontar stock) deben envolverse en `START TRANSACTION`, `COMMIT` y `ROLLBACK` (ver ejemplo en `04_updates_deletes.sql`).
*   **Tipos de Datos y Restricciones**:
    *   Uso de `ENUM` para estados limitados (`pending`, `completed`).
    *   Restricciones `CHECK` (ej. `price >= 0`, `stock >= 0`) para prevenir datos ilógicos.
    *   Restricciones `UNIQUE` (ej. `email`, `license_plate`, `invoice_number`) para prevenir duplicados.

## 4. Control de Acceso y Privilegios

El principio de "mínimo privilegio" reduce el impacto de una posible brecha.

### Recomendaciones de Despliegue
*   **Evitar usuario `root`**: La aplicación no debe conectarse como `root`.
*   **Usuario Específico**: Crear un usuario `taller_app` con permisos limitados solo a la base de datos `taller_coches_sql` y solo para operaciones necesarias (`SELECT`, `INSERT`, `UPDATE`, `DELETE`).
    ```sql
    CREATE USER 'taller_app'@'localhost' IDENTIFIED BY 'contraseña_segura';
    GRANT SELECT, INSERT, UPDATE, DELETE ON taller_coches_sql.* TO 'taller_app'@'localhost';
    FLUSH PRIVILEGES;
    ```
*   **Restricción de Red**: La base de datos no debe estar expuesta directamente a internet (bind 127.0.0.1 o VPC privada).

## 5. Auditoría y Trazabilidad

Saber qué ocurrió y cuándo es vital para la seguridad forense.

### Implementación
*   **Timestamps**: Todas las tablas incluyen `created_at` y `updated_at` para rastrear el ciclo de vida de los registros.
*   **Logs**: Se recomienda habilitar logs de auditoría en el servidor MySQL para registrar accesos o consultas anómalas en producción.
