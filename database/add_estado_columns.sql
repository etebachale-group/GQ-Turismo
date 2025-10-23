-- Agregar columnas de estado para seguimiento de itinerarios
-- Fecha: 23 de Octubre de 2025
-- Versión: Simple (sin verificación)

USE gq_turismo;

-- Agregar estado a itinerario_destinos
ALTER TABLE `itinerario_destinos` 
ADD COLUMN `estado` ENUM('pendiente', 'en_progreso', 'completado', 'cancelado') DEFAULT 'pendiente';

-- Agregar estado a itinerario_guias
ALTER TABLE `itinerario_guias` 
ADD COLUMN `estado` ENUM('pendiente', 'confirmado', 'completado', 'cancelado') DEFAULT 'pendiente';

-- Agregar estado a itinerario_agencias
ALTER TABLE `itinerario_agencias` 
ADD COLUMN `estado` ENUM('pendiente', 'confirmado', 'completado', 'cancelado') DEFAULT 'pendiente';

-- Agregar estado a itinerario_locales
ALTER TABLE `itinerario_locales` 
ADD COLUMN `estado` ENUM('pendiente', 'confirmado', 'completado', 'cancelado') DEFAULT 'pendiente';

-- Agregar notas para cada servicio
ALTER TABLE `itinerario_guias` ADD COLUMN `notas` TEXT;
ALTER TABLE `itinerario_agencias` ADD COLUMN `notas` TEXT;
ALTER TABLE `itinerario_locales` ADD COLUMN `notas` TEXT;

-- Agregar fechas de confirmación
ALTER TABLE `itinerario_guias` 
ADD COLUMN `fecha_confirmacion` DATETIME,
ADD COLUMN `fecha_completado` DATETIME;

ALTER TABLE `itinerario_agencias` 
ADD COLUMN `fecha_confirmacion` DATETIME,
ADD COLUMN `fecha_completado` DATETIME;

ALTER TABLE `itinerario_locales` 
ADD COLUMN `fecha_confirmacion` DATETIME,
ADD COLUMN `fecha_completado` DATETIME;

SELECT 'SCRIPT COMPLETADO EXITOSAMENTE' AS resultado;
