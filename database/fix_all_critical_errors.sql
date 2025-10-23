-- ============================================================================
-- CORRECCIÓN DE ERRORES CRÍTICOS - GQ-Turismo
-- Fecha: 2025-10-23
-- Descripción: Corrige todos los errores críticos de base de datos
-- ============================================================================

USE gq_turismo;

-- 1. Verificar y corregir tabla pedidos_servicios
-- ============================================================================

-- Verificar si la tabla existe, si no, crearla
CREATE TABLE IF NOT EXISTS `pedidos_servicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_turista` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `tipo_proveedor` enum('agencia','guia','local') NOT NULL,
  `tipo_item` enum('servicio','menu') NOT NULL,
  `id_servicio_o_menu` int(11) NOT NULL,
  `nombre_servicio` varchar(255) DEFAULT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `cantidad_personas` int(11) NOT NULL DEFAULT 1,
  `fecha_servicio` date DEFAULT NULL,
  `estado` enum('pendiente','confirmado','completado','cancelado') NOT NULL DEFAULT 'pendiente',
  `id_destino` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_turista` (`id_turista`),
  KEY `id_proveedor` (`id_proveedor`),
  KEY `id_destino` (`id_destino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificar si las columnas necesarias existen, si no, agregarlas
SET @dbname = DATABASE();
SET @tablename = 'pedidos_servicios';
SET @columnname = 'nombre_servicio';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " varchar(255) DEFAULT NULL;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Verificar y corregir tabla reservas
-- ============================================================================

-- Verificar estructura de tabla reservas
CREATE TABLE IF NOT EXISTS `reservas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_itinerario` int(11) NOT NULL,
  `fecha_reserva` date NOT NULL,
  `personas` int(11) NOT NULL DEFAULT 1,
  `estado` enum('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente',
  `precio_total` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_itinerario` (`id_itinerario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Verificar tabla itinerarios tiene nombre_itinerario
-- ============================================================================

SET @columnname = 'nombre_itinerario';
SET @tablename = 'itinerarios';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " varchar(255) NOT NULL DEFAULT 'Mi Itinerario';")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 4. Asegurar que todas las tablas de servicios existan
-- ============================================================================

CREATE TABLE IF NOT EXISTS `servicios_agencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_agencia` int(11) NOT NULL,
  `nombre_servicio` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `duracion` varchar(100) DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_agencia` (`id_agencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `servicios_guia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_guia` int(11) NOT NULL,
  `nombre_servicio` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `duracion` varchar(100) DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_guia` (`id_guia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `servicios_local` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_local` int(11) NOT NULL,
  `nombre_servicio` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `duracion` varchar(100) DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_local` (`id_local`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menus_agencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_agencia` int(11) NOT NULL,
  `nombre_menu` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_agencia` (`id_agencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menus_local` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_local` int(11) NOT NULL,
  `nombre_menu` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_local` (`id_local`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Verificar tabla mensajes
-- ============================================================================

CREATE TABLE IF NOT EXISTS `mensajes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('turista','agencia','guia','local','super_admin') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_type` enum('turista','agencia','guia','local','super_admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- FIN DE CORRECCIONES
-- ============================================================================

SELECT 'Todas las correcciones críticas han sido aplicadas exitosamente.' AS Status;
