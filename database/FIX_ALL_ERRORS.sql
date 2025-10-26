-- Fix all database errors
-- Ejecutar este archivo para corregir todos los errores de la base de datos

-- 1. Agregar columna precio a itinerario_destinos si no existe
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'itinerario_destinos' AND COLUMN_NAME = 'precio');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE `itinerario_destinos` ADD COLUMN `precio` DECIMAL(10,2) DEFAULT 0.00 AFTER `orden`', 'SELECT "Column precio already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;

-- 2. Agregar columnas de fecha a itinerarios si no existen
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'itinerarios' AND COLUMN_NAME = 'fecha_inicio');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE `itinerarios` ADD COLUMN `fecha_inicio` DATE NULL AFTER `presupuesto_estimado`', 'SELECT "Column fecha_inicio already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;

SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'itinerarios' AND COLUMN_NAME = 'fecha_fin');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE `itinerarios` ADD COLUMN `fecha_fin` DATE NULL AFTER `fecha_inicio`', 'SELECT "Column fecha_fin already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;

-- 3. Agregar columna descripcion a itinerarios si no existe
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'itinerarios' AND COLUMN_NAME = 'descripcion');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE `itinerarios` ADD COLUMN `descripcion` TEXT NULL AFTER `nombre_itinerario`', 'SELECT "Column descripcion already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;

-- 4. Crear tabla itinerario_tareas si no existe
CREATE TABLE IF NOT EXISTS `itinerario_tareas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_itinerario` int(11) NOT NULL,
  `id_destino` int(11) DEFAULT NULL,
  `id_proveedor` int(11) DEFAULT NULL,
  `tipo_proveedor` enum('agencia','guia','local') DEFAULT NULL,
  `tipo_tarea` enum('transporte','alojamiento','actividad','comida','guia','otro') NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` TEXT,
  `fecha_hora_inicio` DATETIME,
  `fecha_hora_fin` DATETIME,
  `ubicacion` varchar(255),
  `estado` enum('pendiente','en_progreso','completado','cancelado') DEFAULT 'pendiente',
  `notas` TEXT,
  `precio` DECIMAL(10,2) DEFAULT 0.00,
  `orden` INT DEFAULT 0,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_itinerario` (`id_itinerario`),
  KEY `id_destino` (`id_destino`),
  KEY `id_proveedor` (`id_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Agregar columna estado a itinerario_destinos si no existe
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'itinerario_destinos' AND COLUMN_NAME = 'estado');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE `itinerario_destinos` ADD COLUMN `estado` enum(\'pendiente\',\'en_progreso\',\'completado\') DEFAULT \'pendiente\' AFTER `orden`', 'SELECT "Column estado already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;

-- 6. Agregar columna id_itinerario a pedidos_servicios si no existe
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedidos_servicios' AND COLUMN_NAME = 'id_itinerario');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE `pedidos_servicios` ADD COLUMN `id_itinerario` INT DEFAULT NULL AFTER `id_turista`', 'SELECT "Column id_itinerario already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;

-- 7. Asegurar que la tabla guia_destinos existe
CREATE TABLE IF NOT EXISTS `guia_destinos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_guia` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `experiencia_anos` INT DEFAULT 0,
  `descripcion` TEXT,
  `certificaciones` TEXT,
  `idiomas` VARCHAR(255),
  `tarifa_dia` DECIMAL(10,2) DEFAULT 0.00,
  `disponible` TINYINT(1) DEFAULT 1,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guia_destino_unique` (`id_guia`, `id_destino`),
  KEY `id_guia` (`id_guia`),
  KEY `id_destino` (`id_destino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Agregar columna imagen a publicidad_carousel si no existe
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'publicidad_carousel' AND COLUMN_NAME = 'imagen');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE `publicidad_carousel` ADD COLUMN `imagen` VARCHAR(255) DEFAULT NULL AFTER `descripcion`', 'SELECT "Column imagen already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;

-- 9. Actualizar itinerarios existentes con fechas default
UPDATE `itinerarios` 
SET `fecha_inicio` = DATE_ADD(CURDATE(), INTERVAL 7 DAY),
    `fecha_fin` = DATE_ADD(CURDATE(), INTERVAL 14 DAY)
WHERE `fecha_inicio` IS NULL OR `fecha_fin` IS NULL;

-- 10. Agregar columna estado_itinerario a itinerarios
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'itinerarios' AND COLUMN_NAME = 'estado_itinerario');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE `itinerarios` ADD COLUMN `estado_itinerario` enum(\'borrador\',\'confirmado\',\'iniciado\',\'en_curso\',\'completado\',\'cancelado\') DEFAULT \'borrador\' AFTER `estado`', 'SELECT "Column estado_itinerario already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;

SELECT 'Base de datos actualizada correctamente' as mensaje;
