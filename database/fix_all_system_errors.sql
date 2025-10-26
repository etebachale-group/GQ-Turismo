-- =====================================================
-- Script de corrección completa del sistema GQ-Turismo
-- =====================================================

-- 1. Agregar columna telefono a usuarios si no existe
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS telefono VARCHAR(20) AFTER email;

-- 2. Agregar columna precio a itinerario_destinos si no existe
ALTER TABLE itinerario_destinos 
ADD COLUMN IF NOT EXISTS precio DECIMAL(10,2) DEFAULT 0.00 AFTER fecha_visita;

-- 3. Agregar columnas fecha_inicio y fecha_fin a itinerarios si no existen
ALTER TABLE itinerarios 
ADD COLUMN IF NOT EXISTS fecha_inicio DATE AFTER presupuesto_estimado,
ADD COLUMN IF NOT EXISTS fecha_fin DATE AFTER fecha_inicio;

-- 4. Agregar columna descripcion a itinerarios si no existe
ALTER TABLE itinerarios 
ADD COLUMN IF NOT EXISTS descripcion TEXT AFTER nombre_itinerario;

-- 5. Agregar columna descripcion a itinerario_destinos si no existe
ALTER TABLE itinerario_destinos 
ADD COLUMN IF NOT EXISTS descripcion TEXT AFTER fecha_visita;

-- 6. Crear tabla para tracking de tareas del itinerario si no existe
CREATE TABLE IF NOT EXISTS itinerario_tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT NOT NULL,
    id_destino INT,
    id_servicio INT,
    tipo_tarea ENUM('destino', 'servicio', 'actividad', 'transporte', 'alojamiento') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATETIME,
    fecha_fin DATETIME,
    ubicacion_lat DECIMAL(10, 8),
    ubicacion_lng DECIMAL(11, 8),
    direccion TEXT,
    estado ENUM('pendiente', 'en_progreso', 'completado', 'cancelado') DEFAULT 'pendiente',
    orden INT DEFAULT 0,
    completado_por INT,
    fecha_completado DATETIME,
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE SET NULL,
    FOREIGN KEY (completado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_itinerario (id_itinerario),
    INDEX idx_estado (estado),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Crear tabla para confirmaciones de servicios por proveedores
CREATE TABLE IF NOT EXISTS servicio_confirmaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido_servicio INT NOT NULL,
    id_proveedor INT NOT NULL,
    tipo_proveedor ENUM('agencia', 'guia', 'local') NOT NULL,
    estado_confirmacion ENUM('pendiente', 'confirmado', 'rechazado', 'completado') DEFAULT 'pendiente',
    fecha_confirmacion DATETIME,
    fecha_completado DATETIME,
    notas_proveedor TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pedido_servicio) REFERENCES pedidos_servicios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_proveedor) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_pedido (id_pedido_servicio),
    INDEX idx_proveedor (id_proveedor, tipo_proveedor),
    INDEX idx_estado (estado_confirmacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Agregar columnas a publicidad_carousel si no existen
ALTER TABLE publicidad_carousel 
ADD COLUMN IF NOT EXISTS imagen VARCHAR(255) AFTER descripcion,
ADD COLUMN IF NOT EXISTS orden INT DEFAULT 0 AFTER imagen;

-- 9. Crear tabla guias_destinos si no existe (para que guías elijan destinos)
CREATE TABLE IF NOT EXISTS guias_destinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_guia INT NOT NULL,
    id_destino INT NOT NULL,
    especialidad VARCHAR(100),
    experiencia_anos INT DEFAULT 0,
    certificaciones TEXT,
    tarifa_base DECIMAL(10,2),
    disponible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_guia) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_guia_destino (id_guia, id_destino),
    INDEX idx_guia (id_guia),
    INDEX idx_destino (id_destino),
    INDEX idx_disponible (disponible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Agregar campos de seguimiento a itinerarios
ALTER TABLE itinerarios 
ADD COLUMN IF NOT EXISTS estado_seguimiento ENUM('no_iniciado', 'en_curso', 'completado', 'cancelado') DEFAULT 'no_iniciado' AFTER estado,
ADD COLUMN IF NOT EXISTS fecha_inicio_real DATETIME AFTER fecha_fin,
ADD COLUMN IF NOT EXISTS fecha_fin_real DATETIME AFTER fecha_inicio_real,
ADD COLUMN IF NOT EXISTS progreso_porcentaje INT DEFAULT 0 AFTER fecha_fin_real;

-- 11. Actualizar estado de pedidos_servicios con más opciones
ALTER TABLE pedidos_servicios 
MODIFY COLUMN estado ENUM('pendiente', 'confirmado', 'rechazado', 'en_progreso', 'completado', 'cancelado') DEFAULT 'pendiente';

-- 12. Agregar índices para mejorar rendimiento
CREATE INDEX IF NOT EXISTS idx_itinerario_usuario ON itinerarios(id_turista, estado);
CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON pedidos_servicios(estado, fecha_solicitud);
CREATE INDEX IF NOT EXISTS idx_itinerario_destinos_itinerario ON itinerario_destinos(id_itinerario);

-- 13. Actualizar datos existentes
UPDATE itinerarios SET estado_seguimiento = 'no_iniciado' WHERE estado_seguimiento IS NULL;
UPDATE itinerarios SET progreso_porcentaje = 0 WHERE progreso_porcentaje IS NULL;

-- Mensaje de confirmación
SELECT 'Base de datos actualizada correctamente' AS mensaje;
