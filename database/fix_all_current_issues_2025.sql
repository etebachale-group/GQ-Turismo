-- =====================================================
-- FIX PARA TODOS LOS PROBLEMAS ACTUALES 2025
-- =====================================================

USE gq_turismo;

-- 1. Crear tabla publicidad_carousel si no existe
CREATE TABLE IF NOT EXISTS publicidad_carousel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Agregar columna telefono a usuarios si no existe
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS telefono VARCHAR(20) AFTER email;

-- 3. Agregar columna precio a itinerario_destinos si no existe
ALTER TABLE itinerario_destinos 
ADD COLUMN IF NOT EXISTS precio DECIMAL(10,2) DEFAULT 0.00 AFTER duracion;

-- 4. Agregar columnas fecha_inicio y fecha_fin a itinerarios si no existen
ALTER TABLE itinerarios 
ADD COLUMN IF NOT EXISTS fecha_inicio DATE AFTER ciudad,
ADD COLUMN IF NOT EXISTS fecha_fin DATE AFTER fecha_inicio;

-- 5. Agregar descripcion a itinerario_destinos si no existe
ALTER TABLE itinerario_destinos
ADD COLUMN IF NOT EXISTS descripcion TEXT AFTER precio;

-- 6. Crear tabla itinerario_tareas si no existe (para el mapa de tareas)
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
    estado ENUM('pendiente', 'en_progreso', 'completado', 'cancelado') DEFAULT 'pendiente',
    notas TEXT,
    confirmado_proveedor TINYINT(1) DEFAULT 0,
    confirmado_turista TINYINT(1) DEFAULT 0,
    fecha_confirmacion_proveedor DATETIME,
    fecha_confirmacion_turista DATETIME,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE SET NULL,
    FOREIGN KEY (id_proveedor) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_itinerario (id_itinerario),
    INDEX idx_estado (estado),
    INDEX idx_fecha_inicio (fecha_hora_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Crear tabla guias_destinos si no existe (para relación guía-destinos)
CREATE TABLE IF NOT EXISTS guias_destinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_guia INT NOT NULL,
    id_destino INT NOT NULL,
    especialidad VARCHAR(255),
    tarifa DECIMAL(10,2),
    disponible TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_guia) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_guia_destino (id_guia, id_destino),
    INDEX idx_guia (id_guia),
    INDEX idx_destino (id_destino)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Agregar columna id_guia a itinerarios si no existe
ALTER TABLE itinerarios
ADD COLUMN IF NOT EXISTS id_guia INT AFTER id_turista,
ADD FOREIGN KEY IF NOT EXISTS fk_itinerarios_guia (id_guia) REFERENCES usuarios(id) ON DELETE SET NULL;

-- 9. Crear vista para facilitar consultas de pedidos
CREATE OR REPLACE VIEW vista_pedidos_completa AS
SELECT 
    ps.*,
    u.nombre as proveedor_nombre,
    u.email as proveedor_email,
    u.telefono as proveedor_telefono,
    t.nombre as turista_nombre,
    t.email as turista_email,
    t.telefono as turista_telefono,
    d.nombre as destino_nombre,
    d.ciudad as destino_ciudad,
    CASE 
        WHEN ps.tipo_item = 'paquete' AND ps.tipo_proveedor = 'agencia' THEN pa.nombre_paquete
        WHEN ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'guia' THEN sg.nombre
        WHEN ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'local' THEN sl.nombre
        WHEN ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'local' THEN ml.nombre
        ELSE 'Desconocido'
    END as nombre_servicio,
    CASE 
        WHEN ps.tipo_item = 'paquete' AND ps.tipo_proveedor = 'agencia' THEN pa.precio
        WHEN ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'guia' THEN sg.precio
        WHEN ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'local' THEN sl.precio
        WHEN ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'local' THEN ml.precio
        ELSE 0
    END as precio_servicio
FROM pedidos_servicios ps
LEFT JOIN usuarios u ON ps.id_proveedor = u.id
LEFT JOIN usuarios t ON ps.id_turista = t.id
LEFT JOIN destinos d ON ps.id_destino = d.id
LEFT JOIN paquetes_agencia pa ON ps.id_servicio_o_menu = pa.id AND ps.tipo_item = 'paquete' AND ps.tipo_proveedor = 'agencia'
LEFT JOIN servicios_guia sg ON ps.id_servicio_o_menu = sg.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'guia'
LEFT JOIN servicios_local sl ON ps.id_servicio_o_menu = sl.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'local'
LEFT JOIN menus_local ml ON ps.id_servicio_o_menu = ml.id AND ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'local';

-- 10. Verificar que existan índices importantes
ALTER TABLE pedidos_servicios
ADD INDEX IF NOT EXISTS idx_proveedor_tipo (id_proveedor, tipo_proveedor),
ADD INDEX IF NOT EXISTS idx_turista (id_turista),
ADD INDEX IF NOT EXISTS idx_estado (estado),
ADD INDEX IF NOT EXISTS idx_fecha (fecha_solicitud);

ALTER TABLE messages
ADD INDEX IF NOT EXISTS idx_emisor (emisor_id),
ADD INDEX IF NOT EXISTS idx_receptor (receptor_id),
ADD INDEX IF NOT EXISTS idx_leido (is_read),
ADD INDEX IF NOT EXISTS idx_fecha (created_at);

-- 11. Actualizar estados de itinerarios para asegurar coherencia
UPDATE itinerarios 
SET estado = 'activo' 
WHERE estado IS NULL OR estado = '';

-- 12. Limpiar datos huérfanos
DELETE FROM itinerario_destinos 
WHERE id_itinerario NOT IN (SELECT id FROM itinerarios);

DELETE FROM itinerario_tareas 
WHERE id_itinerario NOT IN (SELECT id FROM itinerarios);

-- 13. Agregar triggers para mantener consistencia
DELIMITER //

-- Trigger para actualizar fecha_actualizacion en itinerarios
DROP TRIGGER IF EXISTS before_itinerario_update//
CREATE TRIGGER before_itinerario_update 
BEFORE UPDATE ON itinerarios
FOR EACH ROW
BEGIN
    SET NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
END//

-- Trigger para crear tareas automáticamente cuando se agrega un destino al itinerario
DROP TRIGGER IF EXISTS after_itinerario_destino_insert//
CREATE TRIGGER after_itinerario_destino_insert
AFTER INSERT ON itinerario_destinos
FOR EACH ROW
BEGIN
    INSERT INTO itinerario_tareas (
        id_itinerario,
        id_destino,
        tipo_tarea,
        titulo,
        descripcion,
        estado
    ) VALUES (
        NEW.id_itinerario,
        NEW.id_destino,
        'actividad',
        CONCAT('Visita a destino - ', (SELECT nombre FROM destinos WHERE id = NEW.id_destino)),
        NEW.descripcion,
        'pendiente'
    );
END//

DELIMITER ;

-- 14. Datos de ejemplo para publicidad_carousel si está vacía
INSERT IGNORE INTO publicidad_carousel (id, titulo, descripcion, imagen, link, orden, activo) VALUES
(1, 'Descubre destinos increíbles', 'Explora los mejores lugares turísticos', 'slide1.jpg', '#destinos', 1, 1),
(2, 'Guías profesionales', 'Contrata guías expertos para tu viaje', 'slide2.jpg', '#guias', 2, 1),
(3, 'Servicios locales', 'Restaurantes y servicios de calidad', 'slide3.jpg', '#locales', 3, 1);

-- =====================================================
-- VERIFICACIÓN FINAL
-- =====================================================

-- Mostrar resumen de tablas
SELECT 
    'publicidad_carousel' as tabla,
    COUNT(*) as registros
FROM publicidad_carousel
UNION ALL
SELECT 
    'itinerario_tareas' as tabla,
    COUNT(*) as registros
FROM itinerario_tareas
UNION ALL
SELECT 
    'guias_destinos' as tabla,
    COUNT(*) as registros
FROM guias_destinos;

SELECT 'CORRECCIONES APLICADAS EXITOSAMENTE' as status;
