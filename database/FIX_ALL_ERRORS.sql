-- ============================================
-- CORRECCIONES COMPLETAS DE BASE DE DATOS
-- GQ-Turismo - Fix All Errors
-- Fecha: 23 de Octubre de 2025
-- ============================================

USE gq_turismo;

-- ============================================
-- 1. AGREGAR COLUMNA nombre_servicio A pedidos_servicios SI NO EXISTE
-- ============================================
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'gq_turismo'
    AND TABLE_NAME = 'pedidos_servicios'
    AND COLUMN_NAME = 'nombre_servicio'
);

SET @sql_add_column = IF(@column_exists = 0,
    'ALTER TABLE `pedidos_servicios` ADD COLUMN `nombre_servicio` VARCHAR(255) NULL AFTER `tipo_item`',
    'SELECT "La columna nombre_servicio ya existe" AS mensaje'
);

PREPARE stmt FROM @sql_add_column;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- 2. MODIFICAR ENUM DE estado EN pedidos_servicios
-- ============================================
ALTER TABLE `pedidos_servicios` 
MODIFY COLUMN `estado` ENUM('pendiente','confirmado','cancelado','completado','pagado') 
NOT NULL DEFAULT 'pendiente';

-- ============================================
-- 3. VERIFICAR Y ACTUALIZAR TABLA reservas
-- ============================================
-- La tabla reservas debe tener fecha_reserva, no fecha
-- Si existe una columna 'fecha', renombrarla a 'fecha_reserva'
SET @column_fecha_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'gq_turismo'
    AND TABLE_NAME = 'reservas'
    AND COLUMN_NAME = 'fecha'
);

SET @sql_rename_fecha = IF(@column_fecha_exists > 0,
    'ALTER TABLE `reservas` CHANGE COLUMN `fecha` `fecha_reserva` DATE NOT NULL',
    'SELECT "La columna fecha no existe, OK" AS mensaje'
);

PREPARE stmt FROM @sql_rename_fecha;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- 4. ACTUALIZAR nombre_servicio PARA REGISTROS EXISTENTES
-- ============================================
-- Para servicios de agencia
UPDATE pedidos_servicios ps
INNER JOIN servicios_agencia sa ON ps.id_servicio_o_menu = sa.id AND ps.tipo_proveedor = 'agencia' AND ps.tipo_item = 'servicio'
SET ps.nombre_servicio = sa.nombre_servicio
WHERE ps.nombre_servicio IS NULL;

-- Para menús de agencia
UPDATE pedidos_servicios ps
INNER JOIN menus_agencia ma ON ps.id_servicio_o_menu = ma.id AND ps.tipo_proveedor = 'agencia' AND ps.tipo_item = 'menu'
SET ps.nombre_servicio = ma.nombre_menu
WHERE ps.nombre_servicio IS NULL;

-- Para servicios de guía
UPDATE pedidos_servicios ps
INNER JOIN servicios_guia sg ON ps.id_servicio_o_menu = sg.id AND ps.tipo_proveedor = 'guia' AND ps.tipo_item = 'servicio'
SET ps.nombre_servicio = sg.nombre_servicio
WHERE ps.nombre_servicio IS NULL;

-- Para servicios de local
UPDATE pedidos_servicios ps
INNER JOIN servicios_local sl ON ps.id_servicio_o_menu = sl.id AND ps.tipo_proveedor = 'local' AND ps.tipo_item = 'servicio'
SET ps.nombre_servicio = sl.nombre_servicio
WHERE ps.nombre_servicio IS NULL;

-- Para menús de local
UPDATE pedidos_servicios ps
INNER JOIN menus_local ml ON ps.id_servicio_o_menu = ml.id AND ps.tipo_proveedor = 'local' AND ps.tipo_item = 'menu'
SET ps.nombre_servicio = ml.nombre_menu
WHERE ps.nombre_servicio IS NULL;

-- ============================================
-- 5. VERIFICAR QUE TABLA mensajes EXISTE
-- ============================================
CREATE TABLE IF NOT EXISTS `mensajes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `sender_type` ENUM('turista', 'agencia', 'guia', 'local', 'super_admin') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_type` ENUM('turista', 'agencia', 'guia', 'local', 'super_admin') NOT NULL,
  `message` TEXT NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id`),
  KEY `idx_sender` (`sender_id`, `sender_type`),
  KEY `idx_receiver` (`receiver_id`, `receiver_type`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- 6. AGREGAR ÍNDICES PARA MEJOR RENDIMIENTO
-- ============================================
-- Índices en pedidos_servicios
ALTER TABLE `pedidos_servicios` 
ADD INDEX IF NOT EXISTS `idx_proveedor` (`tipo_proveedor`, `id_proveedor`),
ADD INDEX IF NOT EXISTS `idx_turista` (`id_turista`),
ADD INDEX IF NOT EXISTS `idx_estado` (`estado`);

-- Índices en reservas
ALTER TABLE `reservas` 
ADD INDEX IF NOT EXISTS `idx_usuario` (`id_usuario`),
ADD INDEX IF NOT EXISTS `idx_fecha` (`fecha_reserva`),
ADD INDEX IF NOT EXISTS `idx_estado` (`estado`);

-- ============================================
-- 7. VERIFICACIÓN FINAL
-- ============================================
SELECT 'VERIFICACIÓN DE CORRECCIONES' AS titulo;

SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'gq_turismo'
AND TABLE_NAME = 'pedidos_servicios'
AND COLUMN_NAME IN ('nombre_servicio', 'estado')
ORDER BY ORDINAL_POSITION;

SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'gq_turismo'
AND TABLE_NAME = 'reservas'
AND COLUMN_NAME LIKE '%fecha%'
ORDER BY ORDINAL_POSITION;

SELECT COUNT(*) AS total_pedidos,
       SUM(CASE WHEN nombre_servicio IS NOT NULL THEN 1 ELSE 0 END) AS con_nombre_servicio,
       SUM(CASE WHEN nombre_servicio IS NULL THEN 1 ELSE 0 END) AS sin_nombre_servicio
FROM pedidos_servicios;

-- ============================================
-- FIN DE CORRECCIONES
-- ============================================
SELECT 'CORRECCIONES APLICADAS EXITOSAMENTE' AS resultado;
