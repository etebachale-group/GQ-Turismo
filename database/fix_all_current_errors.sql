-- Correcciones completas del sistema
-- Ejecutar en orden

-- 1. Agregar columna telefono a usuarios
ALTER TABLE `usuarios` ADD COLUMN IF NOT EXISTS `telefono` VARCHAR(20) AFTER `email`;

-- 2. Agregar columna precio a itinerario_destinos
ALTER TABLE `itinerario_destinos` ADD COLUMN IF NOT EXISTS `precio` DECIMAL(10,2) DEFAULT 0.00 AFTER `id_destino`;

-- 3. Agregar columnas fecha_inicio, fecha_fin a itinerarios
ALTER TABLE `itinerarios` ADD COLUMN IF NOT EXISTS `fecha_inicio` DATE AFTER `ciudad`;
ALTER TABLE `itinerarios` ADD COLUMN IF NOT EXISTS `fecha_fin` DATE AFTER `fecha_inicio`;
ALTER TABLE `itinerarios` ADD COLUMN IF NOT EXISTS `descripcion` TEXT AFTER `ciudad`;

-- 4. Crear tabla publicidad_carousel
CREATE TABLE IF NOT EXISTS `publicidad_carousel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text,
  `imagen` varchar(500) NOT NULL,
  `enlace` varchar(500),
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Crear tabla guias_destinos si no existe
CREATE TABLE IF NOT EXISTS `guias_destinos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_guia` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_guia_destino` (`id_guia`, `id_destino`),
  KEY `id_guia` (`id_guia`),
  KEY `id_destino` (`id_destino`),
  CONSTRAINT `fk_guias_destinos_guia` FOREIGN KEY (`id_guia`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_guias_destinos_destino` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
