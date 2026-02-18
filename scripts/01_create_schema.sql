-- 1. Diseño de la base de datos (Refactorizado)
-- Script de creación del esquema para el sistema "Taller de Coches".
-- SECCIÓN 1: Creación de Tablas y Atributos Básicos
-- SECCIÓN 2: Definición de Claves Foráneas y Restricciones de Integridad
-- TRANSACCIONAL: Se envuelve en una transacción para asegurar consistencia (aunque DDL commit implícito en MySQL).

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS taller_coches_sql CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taller_coches_sql;

-- Desactivar revisión de claves foráneas temporalmente
SET FOREIGN_KEY_CHECKS = 0;
SET AUTOCOMMIT = 0;

START TRANSACTION;

-- ==========================================
-- SECCIÓN 1: Creación de Tablas
-- ==========================================

-- -----------------------------------------------------
-- Tabla: roles
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE, -- 'admin', 'client'
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabla: users
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    api_token VARCHAR(80) DEFAULT NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

CREATE INDEX idx_users_email ON users(email);

-- -----------------------------------------------------
-- Tabla: vehicle_types
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS vehicle_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabla: vehicles
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS vehicles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    license_plate VARCHAR(20) NOT NULL UNIQUE,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    vehicle_type_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

CREATE INDEX idx_vehicles_plate ON vehicles(license_plate);

-- -----------------------------------------------------
-- Tabla: service_types
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS service_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabla: services
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    showOnHome BOOLEAN DEFAULT FALSE,
    price DECIMAL(10, 2) NOT NULL CHECK (price >= 0),
    estimated_duration INT,
    service_type_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabla: appointments
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS appointments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_date DATETIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    user_id BIGINT UNSIGNED NOT NULL,
    vehicle_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabla: categories (Productos)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabla: products
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL CHECK (price >= 0),
    stock INT NOT NULL DEFAULT 0 CHECK (stock >= 0),
    category_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabla: carts
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS carts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    price DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabla: carts_products (Pivote)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS carts_products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 1 CHECK (quantity > 0),
    priceInTime DECIMAL(10, 2) NOT NULL,
    totalPerProduct DECIMAL(10, 2) GENERATED ALWAYS AS (quantity * priceInTime) STORED,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabla: orders (Pedidos)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabla: product_invoices (Facturas de Productos)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS product_invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    total DECIMAL(10, 2) NOT NULL,
    cart_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabla: service_invoices (Facturas de Servicios)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS service_invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    total DECIMAL(10, 2) NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    appointment_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;


-- ==========================================
-- SECCIÓN 2: Creación de Claves Foráneas (Foreign Keys)
-- ==========================================

-- FK Tabla: users
ALTER TABLE users 
ADD CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- FK Tabla: vehicles
ALTER TABLE vehicles 
ADD CONSTRAINT fk_vehicles_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT fk_vehicles_type FOREIGN KEY (vehicle_type_id) REFERENCES vehicle_types (id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- FK Tabla: services
ALTER TABLE services 
ADD CONSTRAINT fk_services_type FOREIGN KEY (service_type_id) REFERENCES service_types (id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- FK Tabla: appointments
ALTER TABLE appointments 
ADD CONSTRAINT fk_appointments_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
ADD CONSTRAINT fk_appointments_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE CASCADE,
ADD CONSTRAINT fk_appointments_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE RESTRICT;

-- FK Tabla: products
ALTER TABLE products 
ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL;

-- FK Tabla: carts
ALTER TABLE carts 
ADD CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE;

-- FK Tabla: carts_products
ALTER TABLE carts_products 
ADD CONSTRAINT fk_cp_cart FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE,
ADD CONSTRAINT fk_cp_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE;

-- FK Tabla: orders
ALTER TABLE orders 
ADD CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE;

-- FK Tabla: product_invoices
ALTER TABLE product_invoices 
ADD CONSTRAINT fk_pi_cart FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE RESTRICT;

-- FK Tabla: service_invoices
ALTER TABLE service_invoices 
ADD CONSTRAINT fk_si_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE RESTRICT,
ADD CONSTRAINT fk_si_appointment FOREIGN KEY (appointment_id) REFERENCES appointments (id) ON DELETE RESTRICT;


COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
SET AUTOCOMMIT = 1;

-- Fin del script de creación refactorizado
