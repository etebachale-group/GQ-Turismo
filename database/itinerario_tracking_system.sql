-- Sistema completo de seguimiento de itinerarios
-- Actualización de tablas para tracking de tareas

-- Agregar columnas a itinerario_destinos
ALTER TABLE itinerario_destinos 
ADD COLUMN IF NOT EXISTS fecha_inicio DATE NULL AFTER estado,
ADD COLUMN IF NOT EXISTS fecha_fin DATE NULL AFTER fecha_inicio,
ADD COLUMN IF NOT EXISTS descripcion TEXT NULL AFTER fecha_fin,
ADD COLUMN IF NOT EXISTS notas TEXT NULL AFTER descripcion,
ADD COLUMN IF NOT EXISTS completado_por INT(11) NULL AFTER notas,
ADD COLUMN IF NOT EXISTS fecha_completado DATETIME NULL AFTER completado_por,
ADD FOREIGN KEY (completado_por) REFERENCES usuarios(id) ON DELETE SET NULL;

-- Crear tabla para tareas del itinerario
CREATE TABLE IF NOT EXISTS itinerario_tareas (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT(11) NOT NULL,
    id_destino INT(11) NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    tipo_tarea ENUM('transporte', 'alojamiento', 'actividad', 'comida', 'guia', 'otro') DEFAULT 'actividad',
    fecha_hora_inicio DATETIME NULL,
    fecha_hora_fin DATETIME NULL,
    ubicacion VARCHAR(255) NULL,
    estado ENUM('pendiente', 'en_progreso', 'completado', 'cancelado') DEFAULT 'pendiente',
    prioridad ENUM('baja', 'media', 'alta') DEFAULT 'media',
    id_proveedor INT(11) NULL,
    tipo_proveedor ENUM('agencia', 'guia', 'local') NULL,
    creado_por INT(11) NOT NULL,
    completado_por INT(11) NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_completado DATETIME NULL,
    notas TEXT NULL,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE SET NULL,
    FOREIGN KEY (id_proveedor) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (completado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_itinerario (id_itinerario),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_hora_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla para confirmaciones de servicios
CREATE TABLE IF NOT EXISTS confirmaciones_servicios (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_pedido_servicio INT(11) NOT NULL,
    id_proveedor INT(11) NOT NULL,
    tipo_proveedor ENUM('agencia', 'guia', 'local') NOT NULL,
    estado_confirmacion ENUM('pendiente', 'confirmado', 'rechazado', 'completado') DEFAULT 'pendiente',
    fecha_confirmacion DATETIME NULL,
    fecha_servicio DATETIME NULL,
    notas_proveedor TEXT NULL,
    valoracion INT(1) NULL CHECK (valoracion >= 1 AND valoracion <= 5),
    comentario_valoracion TEXT NULL,
    fecha_valoracion DATETIME NULL,
    FOREIGN KEY (id_pedido_servicio) REFERENCES pedidos_servicios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_proveedor) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_proveedor (id_proveedor, tipo_proveedor),
    INDEX idx_estado (estado_confirmacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla para notificaciones del sistema
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT(11) NOT NULL,
    tipo ENUM('itinerario', 'reserva', 'mensaje', 'confirmacion', 'sistema') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    url VARCHAR(255) NULL,
    leido TINYINT(1) DEFAULT 0,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario, leido),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar tareas automáticas cuando se confirman servicios
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS after_pedido_confirmado
AFTER UPDATE ON pedidos_servicios
FOR EACH ROW
BEGIN
    IF NEW.estado = 'confirmado' AND OLD.estado != 'confirmado' THEN
        INSERT INTO itinerario_tareas (
            id_itinerario,
            titulo,
            descripcion,
            tipo_tarea,
            id_proveedor,
            tipo_proveedor,
            creado_por,
            estado
        ) SELECT 
            NEW.id_itinerario,
            CONCAT('Servicio: ', 
                CASE
                    WHEN NEW.tipo_item = 'servicio' AND NEW.tipo_proveedor = 'agencia' THEN sa.nombre_servicio
                    WHEN NEW.tipo_item = 'menu' AND NEW.tipo_proveedor = 'agencia' THEN ma.nombre_menu
                    WHEN NEW.tipo_item = 'servicio' AND NEW.tipo_proveedor = 'guia' THEN sg.nombre_servicio
                    WHEN NEW.tipo_item = 'servicio' AND NEW.tipo_proveedor = 'local' THEN sl.nombre_servicio
                    WHEN NEW.tipo_item = 'menu' AND NEW.tipo_proveedor = 'local' THEN ml.nombre_menu
                    ELSE 'Servicio confirmado'
                END
            ),
            'Servicio confirmado por el proveedor',
            CASE NEW.tipo_proveedor
                WHEN 'guia' THEN 'guia'
                WHEN 'local' THEN 'comida'
                ELSE 'actividad'
            END,
            NEW.id_proveedor,
            NEW.tipo_proveedor,
            NEW.id_turista,
            'pendiente'
        FROM pedidos_servicios ps
        LEFT JOIN servicios_agencia sa ON ps.id_servicio_o_menu = sa.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'agencia'
        LEFT JOIN menus_agencia ma ON ps.id_servicio_o_menu = ma.id AND ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'agencia'
        LEFT JOIN servicios_guia sg ON ps.id_servicio_o_menu = sg.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'guia'
        LEFT JOIN servicios_local sl ON ps.id_servicio_o_menu = sl.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'local'
        LEFT JOIN menus_local ml ON ps.id_servicio_o_menu = ml.id AND ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'local'
        WHERE ps.id = NEW.id
        LIMIT 1;
        
        -- Crear notificación para el turista
        INSERT INTO notificaciones (id_usuario, tipo, titulo, mensaje, url)
        VALUES (
            NEW.id_turista,
            'confirmacion',
            'Servicio Confirmado',
            'Tu servicio ha sido confirmado por el proveedor',
            CONCAT('seguimiento_itinerario.php?id=', NEW.id_itinerario)
        );
    END IF;
END$$

DELIMITER ;
