-- =====================================================
-- SCRIPT DE CORRECCIÓN DE BASE DE DATOS GQ-TURISMO
-- Fecha: 2025-10-23
-- Descripción: Corrige problemas encontrados en la BD
-- =====================================================

USE gq_turismo;

-- 1. Actualizar la columna estado de pedidos_servicios para incluir 'completado' y 'pagado'
ALTER TABLE `pedidos_servicios` 
MODIFY COLUMN `estado` ENUM('pendiente','confirmado','cancelado','completado','pagado') NOT NULL DEFAULT 'pendiente';

-- 2. Verificar que todas las tablas necesarias existen
CREATE TABLE IF NOT EXISTS `pedidos_servicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_turista` int(11) NOT NULL,
  `tipo_proveedor` enum('agencia','guia','local') NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `id_servicio_o_menu` int(11) NOT NULL,
  `tipo_item` enum('servicio','menu') NOT NULL,
  `id_itinerario` int(11) DEFAULT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_servicio` date DEFAULT NULL,
  `cantidad_personas` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','confirmado','cancelado','completado','pagado') NOT NULL DEFAULT 'pendiente',
  `id_destino` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Agregar columna 'ciudad' a lugares_locales si no existe
ALTER TABLE `lugares_locales` 
ADD COLUMN IF NOT EXISTS `ciudad` varchar(255) DEFAULT NULL;

-- 4. Agregar columna 'ciudad' a guias_turisticos si no existe
ALTER TABLE `guias_turisticos` 
ADD COLUMN IF NOT EXISTS `ciudad` varchar(255) DEFAULT NULL;

-- 5. Actualizar estructura de itinerarios
ALTER TABLE `itinerarios` 
ADD COLUMN IF NOT EXISTS `alojamiento_id` int(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `guia_id` int(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `fecha_inicio` date DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `fecha_fin` date DEFAULT NULL;

-- 6. Verificar índices importantes
ALTER TABLE `pedidos_servicios`
ADD INDEX IF NOT EXISTS `idx_turista` (`id_turista`),
ADD INDEX IF NOT EXISTS `idx_proveedor` (`tipo_proveedor`, `id_proveedor`),
ADD INDEX IF NOT EXISTS `idx_estado` (`estado`);

ALTER TABLE `mensajes`
ADD INDEX IF NOT EXISTS `idx_sender` (`sender_id`, `sender_type`),
ADD INDEX IF NOT EXISTS `idx_receiver` (`receiver_id`, `receiver_type`);

-- 7. Actualizar permisos para asegurar integridad
UPDATE `usuarios` SET `tipo_usuario` = 'super_admin' WHERE `es_admin` = 1 AND `tipo_usuario` != 'super_admin';

-- 8. Limpiar estados inválidos si existen
UPDATE `pedidos_servicios` SET `estado` = 'completado' WHERE `estado` NOT IN ('pendiente','confirmado','cancelado','completado','pagado');

-- 9. Verificar que reservas tenga la estructura correcta
DESCRIBE reservas;

-- Fin del script
SELECT 'Base de datos actualizada correctamente' AS Resultado;
