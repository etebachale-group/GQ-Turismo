-- Correcciones completas para GQ-Turismo
-- Fecha: 2025-10-23

USE gq_turismo;

-- 1. Agregar columna telefono a usuarios si no existe
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS telefono VARCHAR(20) DEFAULT NULL AFTER email;

-- 2. Crear tabla publicidad_carousel
CREATE TABLE IF NOT EXISTS publicidad_carousel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255) NOT NULL,
    enlace VARCHAR(255),
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_inicio DATE,
    fecha_fin DATE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activo_orden (activo, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Crear tabla itinerario_tareas para seguimiento
CREATE TABLE IF NOT EXISTS itinerario_tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT NOT NULL,
    id_destino INT,
    id_proveedor INT,
    tipo_tarea ENUM('transporte', 'alojamiento', 'actividad', 'comida', 'guia', 'otro') DEFAULT 'otro',
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_hora_inicio DATETIME,
    fecha_hora_fin DATETIME,
    ubicacion VARCHAR(255),
    estado ENUM('pendiente', 'en_progreso', 'completado', 'cancelado') DEFAULT 'pendiente',
    orden INT DEFAULT 0,
    notas TEXT,
    fecha_completado DATETIME,
    completado_por INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE SET NULL,
    FOREIGN KEY (id_proveedor) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_itinerario (id_itinerario),
    INDEX idx_estado (estado),
    INDEX idx_fecha_inicio (fecha_hora_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Crear tabla guias_destinos para relación guías-destinos
CREATE TABLE IF NOT EXISTS guias_destinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_guia INT NOT NULL,
    id_destino INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_guia) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_guia_destino (id_guia, id_destino)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Agregar columna confirmacion_servicio a pedidos_servicios
ALTER TABLE pedidos_servicios
ADD COLUMN IF NOT EXISTS confirmacion_servicio ENUM('pendiente', 'confirmado', 'en_curso', 'completado', 'cancelado') DEFAULT 'pendiente' AFTER estado;

-- 6. Crear tabla confirmaciones_servicio
CREATE TABLE IF NOT EXISTS confirmaciones_servicio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido_servicio INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo_usuario ENUM('turista', 'guia', 'local', 'agencia') NOT NULL,
    estado_confirmacion ENUM('pendiente', 'confirmado', 'en_curso', 'completado', 'cancelado') NOT NULL,
    comentarios TEXT,
    fecha_confirmacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pedido_servicio) REFERENCES pedidos_servicios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_pedido (id_pedido_servicio),
    INDEX idx_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Asegurarnos que itinerario_destinos tiene todas las columnas necesarias
ALTER TABLE itinerario_destinos
ADD COLUMN IF NOT EXISTS precio DECIMAL(10,2) DEFAULT 0.00 AFTER orden,
ADD COLUMN IF NOT EXISTS estado ENUM('pendiente','en_progreso','completado','cancelado') DEFAULT 'pendiente' AFTER precio,
ADD COLUMN IF NOT EXISTS fecha_inicio DATE AFTER estado,
ADD COLUMN IF NOT EXISTS fecha_fin DATE AFTER fecha_inicio,
ADD COLUMN IF NOT EXISTS descripcion TEXT AFTER fecha_fin;

-- 8. Migrar datos de itinerario_destinos a itinerario_tareas si hay datos
INSERT INTO itinerario_tareas (id_itinerario, id_destino, tipo_tarea, titulo, descripcion, estado, fecha_hora_inicio, fecha_hora_fin, orden)
SELECT 
    id.id_itinerario,
    id.id_destino,
    'actividad' as tipo_tarea,
    d.nombre as titulo,
    id.descripcion,
    id.estado,
    TIMESTAMP(id.fecha_inicio) as fecha_hora_inicio,
    TIMESTAMP(id.fecha_fin) as fecha_hora_fin,
    id.orden
FROM itinerario_destinos id
LEFT JOIN destinos d ON id.id_destino = d.id
WHERE NOT EXISTS (
    SELECT 1 FROM itinerario_tareas it 
    WHERE it.id_itinerario = id.id_itinerario 
    AND it.id_destino = id.id_destino
);

SELECT 'Correcciones aplicadas correctamente' as resultado;
