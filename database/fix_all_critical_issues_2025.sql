-- ================================================================
-- FIX ALL CRITICAL ISSUES 2025
-- Corrección completa de todos los errores críticos del sistema
-- ================================================================

USE gq_turismo;

-- 1. Agregar columna telefono a tabla usuarios si no existe
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS telefono VARCHAR(20) DEFAULT NULL AFTER email;

-- 2. Agregar columna id_turista a tabla itinerarios si no existe
ALTER TABLE itinerarios 
ADD COLUMN IF NOT EXISTS id_turista INT DEFAULT NULL AFTER id,
ADD INDEX IF NOT EXISTS idx_id_turista (id_turista);

-- 3. Crear tabla publicidad_carousel si no existe
CREATE TABLE IF NOT EXISTS publicidad_carousel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(500) NOT NULL,
    enlace VARCHAR(500),
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_inicio DATE,
    fecha_fin DATE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activo (activo),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Asegurar que itinerario_destinos tenga todas las columnas necesarias
ALTER TABLE itinerario_destinos
ADD COLUMN IF NOT EXISTS fecha_inicio DATETIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS fecha_fin DATETIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS descripcion TEXT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS precio DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS orden INT DEFAULT 0;

-- 5. Crear tabla itinerario_tareas si no existe (para mapa de tareas)
CREATE TABLE IF NOT EXISTS itinerario_tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT NOT NULL,
    id_destino INT DEFAULT NULL,
    id_proveedor INT DEFAULT NULL,
    tipo_proveedor ENUM('agencia', 'guia', 'local') DEFAULT NULL,
    tipo_tarea ENUM('transporte', 'alojamiento', 'actividad', 'comida', 'guia', 'otro') DEFAULT 'otro',
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_hora_inicio DATETIME DEFAULT NULL,
    fecha_hora_fin DATETIME DEFAULT NULL,
    estado ENUM('pendiente', 'en_progreso', 'completado', 'cancelado') DEFAULT 'pendiente',
    notas TEXT,
    completado_por INT DEFAULT NULL,
    fecha_completado DATETIME DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE SET NULL,
    FOREIGN KEY (id_proveedor) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (completado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_itinerario (id_itinerario),
    INDEX idx_estado (estado),
    INDEX idx_fecha_inicio (fecha_hora_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Crear tabla guias_destinos si no existe (guías eligen destinos)
CREATE TABLE IF NOT EXISTS guias_destinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_guia INT NOT NULL,
    id_destino INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_guia) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_guia_destino (id_guia, id_destino),
    INDEX idx_guia (id_guia),
    INDEX idx_destino (id_destino),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Actualizar itinerarios existentes para asignar id_turista si es NULL
UPDATE itinerarios i
LEFT JOIN pedidos_servicios ps ON ps.id_itinerario = i.id
SET i.id_turista = ps.id_turista
WHERE i.id_turista IS NULL AND ps.id_turista IS NOT NULL
LIMIT 1000;

-- 8. Verificar y arreglar mensajes - asegurar que tenga columnas necesarias
ALTER TABLE mensajes
ADD COLUMN IF NOT EXISTS leido TINYINT(1) DEFAULT 0 AFTER mensaje,
ADD COLUMN IF NOT EXISTS fecha_lectura DATETIME DEFAULT NULL AFTER leido,
ADD INDEX IF NOT EXISTS idx_receptor_leido (id_receptor, leido),
ADD INDEX IF NOT EXISTS idx_emisor (id_emisor);

-- 9. Asegurar que pedidos_servicios tenga todas las columnas necesarias
ALTER TABLE pedidos_servicios
ADD COLUMN IF NOT EXISTS id_itinerario INT DEFAULT NULL AFTER id,
ADD COLUMN IF NOT EXISTS id_destino INT DEFAULT NULL AFTER id_servicio_o_menu,
ADD INDEX IF NOT EXISTS idx_itinerario (id_itinerario),
ADD INDEX IF NOT EXISTS idx_destino (id_destino);

-- 10. Crear tabla confirmacion_servicios (para que proveedores confirmen servicios)
CREATE TABLE IF NOT EXISTS confirmacion_servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido_servicio INT NOT NULL,
    id_proveedor INT NOT NULL,
    tipo_proveedor ENUM('agencia', 'guia', 'local') NOT NULL,
    estado ENUM('pendiente', 'confirmado', 'rechazado', 'completado') DEFAULT 'pendiente',
    notas TEXT,
    fecha_confirmacion DATETIME DEFAULT NULL,
    fecha_completado DATETIME DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pedido_servicio) REFERENCES pedidos_servicios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_proveedor) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_pedido_proveedor (id_pedido_servicio, id_proveedor),
    INDEX idx_proveedor (id_proveedor),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificar estructura final
SELECT 'Base de datos actualizada correctamente' as Resultado;
