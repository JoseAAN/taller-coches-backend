-- 2. Inserción de datos de ejemplo
-- Script para poblar la base de datos con datos iniciales para pruebas.

USE taller_coches_sql;

-- -----------------------------------------------------
-- Insertar Roles
-- -----------------------------------------------------
INSERT INTO roles (name, created_at, updated_at) VALUES 
('admin', NOW(), NOW()),
('client', NOW(), NOW());

-- -----------------------------------------------------
-- Insertar Usuarios
-- NOTA: Las contraseñas aquí son HASHES simulados de ejemplo. 
-- En una aplicación real, usa password_hash('password', PASSWORD_BCRYPT).
-- Aquí usamos un hash de ejemplo para 'password123': $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- -----------------------------------------------------
INSERT INTO users (name, email, password, role_id, created_at, updated_at) VALUES 
('Administrador', 'admin@taller.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW()), -- ID 1 (Admin)
('Juan Cliente', 'juan@cliente.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NOW(), NOW()), -- ID 2 (Cliente)
('Ana Cliente', 'ana@cliente.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NOW(), NOW()); -- ID 3 (Cliente)

-- -----------------------------------------------------
-- Insertar Tipos de Vehículo y Típso de Servicio
-- -----------------------------------------------------
INSERT INTO vehicle_types (name, created_at, updated_at) VALUES 
('Sedán', NOW(), NOW()),
('SUV', NOW(), NOW()),
('Motocicleta', NOW(), NOW());

INSERT INTO service_types (name, description, created_at, updated_at) VALUES 
('Mecánica General', 'Reparaciones de motor, frenos, etc.', NOW(), NOW()),
('Mantenimiento', 'Cambio de aceite, filtros, etc.', NOW(), NOW());

-- -----------------------------------------------------
-- Insertar Vehículos
-- -----------------------------------------------------
INSERT INTO vehicles (license_plate, brand, model, user_id, vehicle_type_id, created_at, updated_at) VALUES 
('1234ABC', 'Toyota', 'Corolla', 2, 1, NOW(), NOW()),
('5678DEF', 'Honda', 'CR-V', 2, 2, NOW(), NOW()),
('9012GHI', 'Yamaha', 'MT-07', 3, 3, NOW(), NOW());

-- -----------------------------------------------------
-- Insertar Servicios
-- -----------------------------------------------------
INSERT INTO services (name, description, price, estimated_duration, service_type_id, showOnHome, created_at, updated_at) VALUES 
('Cambio de Aceite', 'Aceite sintético 5W30', 50.00, 45, 2, TRUE, NOW(), NOW()),
('Cambio de Pastillas de Freno', 'Eje delantero', 80.00, 60, 1, TRUE, NOW(), NOW()),
('Revisión General', '20 puntos de control', 30.00, 30, 2, FALSE, NOW(), NOW());

-- -----------------------------------------------------
-- Insertar Categorías y Productos
-- -----------------------------------------------------
INSERT INTO categories (name, description, created_at, updated_at) VALUES 
('Aceites', 'Lubricantes para motor', NOW(), NOW()),
('Accesorios', 'Accesorios interiores y exteriores', NOW(), NOW());

INSERT INTO products (name, description, price, stock, category_id, created_at, updated_at) VALUES 
('Castrol Edge 5W30', 'Lata de 5 litros', 45.50, 20, 1, NOW(), NOW()),
('Ambientador Pino', 'Pack de 3', 5.00, 100, 2, NOW(), NOW()),
('Limpiaparabrisas Bosch', 'Juego delantero', 25.00, 15, 2, NOW(), NOW());

-- -----------------------------------------------------
-- Insertar Cita
-- -----------------------------------------------------
INSERT INTO appointments (appointment_date, status, user_id, vehicle_id, service_id, created_at, updated_at) VALUES 
('2024-12-01 10:00:00', 'confirmed', 2, 1, 1, NOW(), NOW()); -- Juan, Toyota, Cambio Aceite

-- -----------------------------------------------------
-- Insertar Carrito y Productos
-- -----------------------------------------------------
INSERT INTO carts (user_id, price, created_at, updated_at) VALUES 
(2, 50.50, NOW(), NOW()); -- Carrito de Juan

INSERT INTO carts_products (cart_id, product_id, quantity, priceInTime, created_at, updated_at) VALUES 
(1, 1, 1, 45.50, NOW(), NOW()), -- 1 Aceite
(1, 2, 1, 5.00, NOW(), NOW());  -- 1 Ambientador

-- Fin del script de inserción
