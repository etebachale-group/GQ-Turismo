-- Correcciones completas del sistema GQ-Turismo
-- Ejecutar este archivo para corregir todos los errores pendientes

USE gq_turismo;

-- 1. Verificar y corregir tabla itinerario_destinos
ALTER TABLE itinerario_destinos 
ADD COLUMN IF NOT EXISTS precio DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS fecha_inicio DATE NULL,
ADD COLUMN IF NOT EXISTS fecha_fin DATE NULL,
ADD COLUMN IF NOT EXISTS descripcion TEXT NULL,
ADD COLUMN IF NOT EXISTS estado ENUM('pendiente','en_progreso','completado','cancelado') DEFAULT 'pendiente';

-- 2. Verificar y corregir tabla usuarios (telefono)
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS telefono VARCHAR(20) NULL;

-- 3. Crear tabla publicidad_carousel si no existe
CREATE TABLE IF NOT EXISTS publicidad_carousel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255),
    link VARCHAR(255),
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_inicio DATE,
    fecha_fin DATE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Crear tabla itinerario_tareas para seguimiento
CREATE TABLE IF NOT EXISTS itinerario_tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT NOT NULL,
    id_destino INT NULL,
    id_proveedor INT NULL,
    tipo_proveedor ENUM('agencia','guia','local') NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    tipo ENUM('transporte','alojamiento','actividad','comida','guia','otro') NOT NULL,
    fecha_hora_inicio DATETIME,
    fecha_hora_fin DATETIME,
    ubicacion VARCHAR(255),
    precio DECIMAL(10,2) DEFAULT 0.00,
    estado ENUM('pendiente','en_progreso','completado','cancelado') DEFAULT 'pendiente',
    notas TEXT,
    orden INT DEFAULT 0,
    fecha_completado DATETIME NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE SET NULL,
    FOREIGN KEY (id_proveedor) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Crear tabla guias_destinos para relación guías-destinos
CREATE TABLE IF NOT EXISTS guias_destinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_guia INT NOT NULL,
    id_destino INT NOT NULL,
    precio_sugerido DECIMAL(10,2) DEFAULT 0.00,
    disponible TINYINT(1) DEFAULT 1,
    notas TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_guia_destino (id_guia, id_destino),
    FOREIGN KEY (id_guia) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Agregar columnas de estado a proveedores
ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS estado_servicio ENUM('disponible','ocupado','no_disponible') DEFAULT 'disponible',
ADD COLUMN IF NOT EXISTS ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 7. Crear índices para mejor rendimiento
CREATE INDEX IF NOT EXISTS idx_itinerario_tareas_itinerario ON itinerario_tareas(id_itinerario);
CREATE INDEX IF NOT EXISTS idx_itinerario_tareas_estado ON itinerario_tareas(estado);
CREATE INDEX IF NOT EXISTS idx_itinerario_tareas_proveedor ON itinerario_tareas(id_proveedor);
CREATE INDEX IF NOT EXISTS idx_guias_destinos_guia ON guias_destinos(id_guia);
CREATE INDEX IF NOT EXISTS idx_guias_destinos_destino ON guias_destinos(id_destino);

-- 8. Actualizar tabla pedidos_servicios con campos de confirmación
ALTER TABLE pedidos_servicios
ADD COLUMN IF NOT EXISTS confirmado_proveedor TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS fecha_confirmacion DATETIME NULL,
ADD COLUMN IF NOT EXISTS notas_proveedor TEXT NULL;

-- Fin del script
SELECT 'Base de datos actualizada correctamente' AS mensaje;
