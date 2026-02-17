-- 4. Actualizaciones y Eliminaciones (UPDATE / DELETE)
-- Script con ejemplos de modificaciones de datos seguras y manejo de transacciones.

USE taller_coches_sql;

-- -----------------------------------------------------
-- 1. Actualización simple: Aumentar precio de servicios un 10%
-- -----------------------------------------------------
UPDATE services 
SET price = price * 1.10 
WHERE service_type_id = 1; -- Solo para Mecánica General

-- -----------------------------------------------------
-- 2. Transacción: Mover stock de un producto (Simulación de venta)
-- Se asegura de que ambas operaciones ocurran o ninguna.
-- -----------------------------------------------------
START TRANSACTION;

-- Paso A: Restar stock
UPDATE products 
SET stock = stock - 1 
WHERE id = 1 AND stock > 0;

-- Paso B: (Aquí insertaríamos en una tabla de ventas/pedidos, simulado)
-- INSERT INTO orders ...

COMMIT; 
-- Si algo falla, usaríamos ROLLBACK;

-- -----------------------------------------------------
-- 3. Eliminación segura con restricciones
-- Intentar borrar una categoría que tiene productos.
-- Si hay restricción (ON DELETE RESTRICT), fallará si hay hijos.
-- Si queremos borrar, primero movemos o borramos los productos.
-- -----------------------------------------------------

-- Opción A: Borrar productos asociados primero (si no son necesarios)
-- DELETE FROM products WHERE category_id = 99;

-- Opción B: Desvincular productos (Poner categoría a NULL)
UPDATE products SET category_id = NULL WHERE category_id = 2;

-- Ahora sí podemos borrar la categoría
DELETE FROM categories WHERE id = 2; -- Borrar categoría 'Accesorios'

-- -----------------------------------------------------
-- 4. Borrar citas antiguas canceladas
-- Limpieza de datos históricos irrelevantes
-- -----------------------------------------------------
DELETE FROM appointments 
WHERE status = 'cancelled' 
AND appointment_date < DATE_SUB(NOW(), INTERVAL 1 YEAR);
