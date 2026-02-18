-- 3. Consultas de ejemplo (SELECT)
-- Script con consultas útiles utilizando JOINs, filtros y agrupaciones.

USE taller_coches_sql;

-- -----------------------------------------------------
-- 1. Obtener todos los usuarios con su nombre de rol
-- Uso de INNER JOIN
-- -----------------------------------------------------
SELECT 
    u.id, 
    u.name AS usuario, 
    u.email, 
    r.name AS rol
FROM users u
INNER JOIN roles r ON u.role_id = r.id;

-- -----------------------------------------------------
-- 2. Listar vehículos de un usuario específico (ej. Juan Cliente)
-- Uso de WHERE y JOIN
-- -----------------------------------------------------
SELECT 
    v.brand, 
    v.model, 
    v.license_plate, 
    vt.name AS tipo
FROM vehicles v
INNER JOIN vehicle_types vt ON v.vehicle_type_id = vt.id
JOIN users u ON v.user_id = u.id
WHERE u.email = 'juan@cliente.com';

-- -----------------------------------------------------
-- 3. Consultar citas confirmadas con detalles
-- Uso de múltiples JOINs
-- -----------------------------------------------------
SELECT 
    a.appointment_date, 
    u.name AS cliente, 
    v.license_plate AS matricula, 
    s.name AS servicio, 
    s.price AS precio_estimado
FROM appointments a
JOIN users u ON a.user_id = u.id
JOIN vehicles v ON a.vehicle_id = v.id
JOIN services s ON a.service_id = s.id
WHERE a.status = 'confirmed'
ORDER BY a.appointment_date ASC;

-- -----------------------------------------------------
-- 4. Productos con stock bajo (menos de 20 unidades)
-- Uso de operadores de comparación
-- -----------------------------------------------------
SELECT 
    name, 
    stock, 
    price 
FROM products 
WHERE stock < 20;

-- -----------------------------------------------------
-- 5. Calcular valor total del inventario por categoría
-- Uso de GROUP BY y funciones de agregación (SUM)
-- -----------------------------------------------------
SELECT 
    c.name AS categoria, 
    COUNT(p.id) AS total_productos, 
    SUM(p.stock * p.price) AS valor_inventario
FROM products p
JOIN categories c ON p.category_id = c.id
GROUP BY c.name;
