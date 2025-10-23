-- ============================================
-- CORRECCIONES CRÍTICAS BASE DE DATOS
-- GQ-Turismo - Fecha: 23 de Octubre de 2025
-- ============================================

USE gq_turismo;

-- ============================================
-- 1. AGREGAR COLUMNA nombre_servicio A pedidos_servicios
-- ============================================
ALTER TABLE `pedidos_servicios` 
ADD COLUMN `nombre_servicio` VARCHAR(255) NULL AFTER `tipo_item`;

-- ============================================
-- 2. MODIFICAR ENUM DE estado EN pedidos_servicios
-- ============================================
ALTER TABLE `pedidos_servicios` 
MODIFY COLUMN `estado` ENUM('pendiente','confirmado','cancelado','completado','pagado') 
NOT NULL DEFAULT 'pendiente';

-- ============================================
-- 3. ACTUALIZAR nombre_servicio PARA REGISTROS EXISTENTES
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
-- 4. AGREGAR COLUMNA ciudad A lugares_locales SI NO EXISTE
-- ============================================
-- Verificar primero si existe, si no, agregarla
ALTER TABLE `lugares_locales` 
ADD COLUMN IF NOT EXISTS `ciudad` VARCHAR(255) NULL AFTER `direccion`;

-- ============================================
-- VERIFICACIÓN DE COLUMNAS
-- ============================================
SELECT 'pedidos_servicios' AS tabla, 
       COUNT(*) AS registros_con_nombre_servicio
FROM pedidos_servicios 
WHERE nombre_servicio IS NOT NULL;

SELECT 'pedidos_servicios' AS tabla,
       COLUMN_NAME, 
       COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'gq_turismo' 
  AND TABLE_NAME = 'pedidos_servicios' 
  AND COLUMN_NAME IN ('nombre_servicio', 'estado');

-- ============================================
-- FIN DE CORRECCIONES
-- ============================================
