-- Fix all system issues
-- Fecha: 2025-01-24

-- 1. Asegurar que existe la columna precio en itinerario_destinos
ALTER TABLE `itinerario_destinos` 
ADD COLUMN IF NOT EXISTS `precio` DECIMAL(10,2) DEFAULT 0.00 AFTER `id_destino`;

-- 2. Asegurar columnas en itinerarios
ALTER TABLE `itinerarios` 
ADD COLUMN IF NOT EXISTS `fecha_inicio` DATE NULL AFTER `estado`,
ADD COLUMN IF NOT EXISTS `fecha_fin` DATE NULL AFTER `fecha_inicio`,
ADD COLUMN IF NOT EXISTS `descripcion` TEXT NULL AFTER `nombre_itinerario`;

-- 3. Crear tabla publicidad_carousel si no existe
CREATE TABLE IF NOT EXISTS `publicidad_carousel` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `descripcion` TEXT,
  `imagen` VARCHAR(255) NOT NULL,
  `link` VARCHAR(500) DEFAULT NULL,
  `orden` INT(11) DEFAULT 0,
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Asegurar que telefono existe en usuarios (ya existe)
-- Ya verificado que existe

-- 5. Crear sistema de tracking de itinerarios si no existe
CREATE TABLE IF NOT EXISTS `itinerario_tareas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_itinerario` INT(11) NOT NULL,
  `id_destino` INT(11) DEFAULT NULL,
  `id_proveedor` INT(11) DEFAULT NULL,
  `tipo_proveedor` ENUM('agencia','guia','local') DEFAULT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descripcion` TEXT,
  `tipo_tarea` ENUM('transporte','alojamiento','actividad','comida','guia','otro') DEFAULT 'otro',
  `fecha_hora_inicio` DATETIME NULL,
  `fecha_hora_fin` DATETIME NULL,
  `ubicacion` VARCHAR(255) DEFAULT NULL,
  `latitud` DECIMAL(10, 8) DEFAULT NULL,
  `longitud` DECIMAL(11, 8) DEFAULT NULL,
  `precio` DECIMAL(10,2) DEFAULT 0.00,
  `estado` ENUM('pendiente','en_progreso','completado','cancelado') DEFAULT 'pendiente',
  `notas` TEXT,
  `orden` INT(11) DEFAULT 0,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_itinerario` (`id_itinerario`),
  KEY `idx_estado` (`estado`),
  KEY `idx_orden` (`orden`),
  FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Crear tabla de confirmaciones de proveedores
CREATE TABLE IF NOT EXISTS `itinerario_confirmaciones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_itinerario` INT(11) NOT NULL,
  `id_tarea` INT(11) NOT NULL,
  `id_proveedor` INT(11) NOT NULL,
  `tipo_proveedor` ENUM('agencia','guia','local') NOT NULL,
  `estado_confirmacion` ENUM('pendiente','confirmado','rechazado') DEFAULT 'pendiente',
  `notas_proveedor` TEXT,
  `fecha_confirmacion` DATETIME NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_itinerario` (`id_itinerario`),
  KEY `idx_tarea` (`id_tarea`),
  KEY `idx_proveedor` (`id_proveedor`, `tipo_proveedor`),
  FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_tarea`) REFERENCES `itinerario_tareas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Sistema para que guías elijan destinos
CREATE TABLE IF NOT EXISTS `guia_destinos_disponibles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_guia` INT(11) NOT NULL,
  `id_destino` INT(11) NOT NULL,
  `precio_base` DECIMAL(10,2) DEFAULT 0.00,
  `disponible` TINYINT(1) DEFAULT 1,
  `notas` TEXT,
  `fecha_agregado` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_guia_destino` (`id_guia`, `id_destino`),
  KEY `idx_guia` (`id_guia`),
  KEY `idx_destino` (`id_destino`),
  FOREIGN KEY (`id_guia`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Actualizar tabla de mensajes para sistema emisor-receptor
ALTER TABLE `mensajes` 
ADD COLUMN IF NOT EXISTS `id_emisor` INT(11) NULL AFTER `id`,
ADD COLUMN IF NOT EXISTS `id_receptor` INT(11) NULL AFTER `id_emisor`,
ADD COLUMN IF NOT EXISTS `leido` TINYINT(1) DEFAULT 0 AFTER `mensaje`,
ADD COLUMN IF NOT EXISTS `fecha_lectura` DATETIME NULL AFTER `leido`;

-- Agregar índices para mensajes
ALTER TABLE `mensajes`
ADD INDEX IF NOT EXISTS `idx_emisor` (`id_emisor`),
ADD INDEX IF NOT EXISTS `idx_receptor` (`id_receptor`),
ADD INDEX IF NOT EXISTS `idx_leido` (`leido`);

-- 9. Migrar datos existentes de mensajes si aplica
UPDATE `mensajes` SET `id_emisor` = `id_usuario` WHERE `id_emisor` IS NULL AND `id_usuario` IS NOT NULL;

SELECT 'Sistema actualizado correctamente' AS mensaje;
