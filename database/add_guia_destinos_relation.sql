-- =============================================
-- Tabla de Relación: Guías - Destinos
-- Los guías eligen los destinos donde pueden ofrecer servicios
-- Fecha: 23 de Octubre de 2025
-- =============================================

USE gq_turismo;

-- Crear tabla de relación guías-destinos
CREATE TABLE IF NOT EXISTS `guia_destinos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_guia` INT(11) NOT NULL,
  `id_destino` INT(11) NOT NULL,
  `disponible` TINYINT(1) DEFAULT 1,
  `tarifa_especial` DECIMAL(10,2) DEFAULT NULL COMMENT 'Tarifa específica para este destino',
  `descripcion_servicio` TEXT COMMENT 'Descripción del servicio en este destino',
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_guia_destino` (`id_guia`, `id_destino`),
  KEY `idx_guia` (`id_guia`),
  KEY `idx_destino` (`id_destino`),
  CONSTRAINT `fk_guia_destinos_guia` FOREIGN KEY (`id_guia`) REFERENCES `guias_turisticos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_guia_destinos_destino` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla similar para agencias
CREATE TABLE IF NOT EXISTS `agencia_destinos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_agencia` INT(11) NOT NULL,
  `id_destino` INT(11) NOT NULL,
  `disponible` TINYINT(1) DEFAULT 1,
  `tarifa_especial` DECIMAL(10,2) DEFAULT NULL,
  `descripcion_servicio` TEXT,
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_agencia_destino` (`id_agencia`, `id_destino`),
  KEY `idx_agencia` (`id_agencia`),
  KEY `idx_destino` (`id_destino`),
  CONSTRAINT `fk_agencia_destinos_agencia` FOREIGN KEY (`id_agencia`) REFERENCES `agencias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_agencia_destinos_destino` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla similar para locales
CREATE TABLE IF NOT EXISTS `local_destinos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_local` INT(11) NOT NULL,
  `id_destino` INT(11) NOT NULL,
  `disponible` TINYINT(1) DEFAULT 1,
  `tarifa_especial` DECIMAL(10,2) DEFAULT NULL,
  `descripcion_servicio` TEXT,
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_local_destino` (`id_local`, `id_destino`),
  KEY `idx_local` (`id_local`),
  KEY `idx_destino` (`id_destino`),
  CONSTRAINT `fk_local_destinos_local` FOREIGN KEY (`id_local`) REFERENCES `lugares_locales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_local_destinos_destino` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Tablas de relación creadas correctamente' AS resultado;
