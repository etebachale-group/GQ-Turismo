-- Script para corregir errores críticos en GQ-Turismo
-- Fecha: 2025-10-23

USE gq_turismo;

-- 1. Verificar y corregir tabla pedidos_servicios
-- Asegurar que la columna estado incluye todos los valores necesarios
ALTER TABLE pedidos_servicios 
MODIFY COLUMN estado ENUM('pendiente', 'confirmado', 'rechazado', 'pagado', 'completado', 'cancelado') 
NOT NULL DEFAULT 'pendiente';

-- Asegurar que exista la columna nombre_servicio
ALTER TABLE pedidos_servicios 
ADD COLUMN IF NOT EXISTS nombre_servicio VARCHAR(255) NULL AFTER id_servicio_o_menu;

-- 2. Verificar estructura de tabla reservas
-- Asegurar que tiene la columna fecha_reserva
ALTER TABLE reservas 
ADD COLUMN IF NOT EXISTS fecha_reserva TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Asegurar que tiene columna total_precio
ALTER TABLE reservas 
ADD COLUMN IF NOT EXISTS total_precio DECIMAL(10,2) DEFAULT 0.00;

-- 3. Asegurar que pedidos_servicios tiene columna fecha_solicitud
ALTER TABLE pedidos_servicios 
ADD COLUMN IF NOT EXISTS fecha_solicitud TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- 4. Verificar que existen todas las tablas necesarias
CREATE TABLE IF NOT EXISTS servicios_agencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_agencia INT NOT NULL,
    nombre_servicio VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    duracion VARCHAR(100),
    disponibilidad ENUM('disponible', 'no_disponible') DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_agencia) REFERENCES agencias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS servicios_guia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_guia INT NOT NULL,
    nombre_servicio VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    duracion VARCHAR(100),
    idiomas VARCHAR(255),
    disponibilidad ENUM('disponible', 'no_disponible') DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_guia) REFERENCES guias_turisticos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS servicios_local (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_local INT NOT NULL,
    nombre_servicio VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    disponibilidad ENUM('disponible', 'no_disponible') DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_local) REFERENCES lugares_locales(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS menus_agencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_agencia INT NOT NULL,
    nombre_menu VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    disponibilidad ENUM('disponible', 'no_disponible') DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_agencia) REFERENCES agencias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS menus_local (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_local INT NOT NULL,
    nombre_menu VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(100),
    disponibilidad ENUM('disponible', 'no_disponible') DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_local) REFERENCES lugares_locales(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Optimización de índices
ALTER TABLE pedidos_servicios 
ADD INDEX IF NOT EXISTS idx_turista (id_turista),
ADD INDEX IF NOT EXISTS idx_proveedor (id_proveedor, tipo_proveedor),
ADD INDEX IF NOT EXISTS idx_estado (estado);

ALTER TABLE reservas 
ADD INDEX IF NOT EXISTS idx_usuario (id_usuario),
ADD INDEX IF NOT EXISTS idx_itinerario (id_itinerario),
ADD INDEX IF NOT EXISTS idx_estado (estado);

-- Mensaje de finalización
SELECT 'Correcciones aplicadas exitosamente' AS Resultado;
