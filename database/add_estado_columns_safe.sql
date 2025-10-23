-- =============================================
-- Script SQL Seguro para Agregar Columnas de Estado
-- Sistema de Seguimiento de Itinerarios
-- Fecha: 23 de Octubre de 2025
-- =============================================

-- Usar la base de datos
USE gq_turismo;

-- =============================================
-- AGREGAR COLUMNA ESTADO A itinerario_destinos
-- =============================================
SET @dbname = DATABASE();
SET @tablename = 'itinerario_destinos';
SET @columnname = 'estado';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
   AND (TABLE_NAME = @tablename)
   AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT ''Column already exists'' AS msg;',
  'ALTER TABLE itinerario_destinos ADD COLUMN estado ENUM(''pendiente'', ''en_progreso'', ''completado'', ''cancelado'') DEFAULT ''pendiente'';'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- AGREGAR COLUMNA ESTADO A itinerario_guias
-- =============================================
SET @tablename = 'itinerario_guias';
SET @columnname = 'estado';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
   AND (TABLE_NAME = @tablename)
   AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT ''Column already exists'' AS msg;',
  'ALTER TABLE itinerario_guias ADD COLUMN estado ENUM(''pendiente'', ''confirmado'', ''completado'', ''cancelado'') DEFAULT ''pendiente'';'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- AGREGAR COLUMNA NOTAS A itinerario_guias
-- =============================================
SET @columnname = 'notas';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
   AND (TABLE_NAME = @tablename)
   AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT ''Column already exists'' AS msg;',
  'ALTER TABLE itinerario_guias ADD COLUMN notas TEXT;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- AGREGAR COLUMNA fecha_confirmacion A itinerario_guias
-- =============================================
SET @columnname = 'fecha_confirmacion';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
   AND (TABLE_NAME = @tablename)
   AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT ''Column already exists'' AS msg;',
  'ALTER TABLE itinerario_guias ADD COLUMN fecha_confirmacion DATETIME;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- AGREGAR COLUMNA fecha_completado A itinerario_guias
-- =============================================
SET @columnname = 'fecha_completado';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
   AND (TABLE_NAME = @tablename)
   AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT ''Column already exists'' AS msg;',
  'ALTER TABLE itinerario_guias ADD COLUMN fecha_completado DATETIME;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- AGREGAR COLUMNA ESTADO A itinerario_agencias
-- =============================================
SET @tablename = 'itinerario_agencias';
SET @columnname = 'estado';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
   AND (TABLE_NAME = @tablename)
   AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT ''Column already exists'' AS msg;',
  'ALTER TABLE itinerario_agencias ADD COLUMN estado ENUM(''pendiente'', ''confirmado'', ''completado'', ''cancelado'') DEFAULT ''pendiente'';'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- AGREGAR COLUMNA NOTAS A itinerario_agencias
-- =============================================
SET @columnname = 'notas';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
   AND (TABLE_NAME = @tablename)
   AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT ''Column already exists'' AS msg;',
  'ALTER TABLE itinerario_agencias ADD COLUMN notas TEXT;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- AGREGAR COLUMNA fecha_confirmacion A itinerario_agencias
-- =============================================
SET @columnname = 'fecha_confirmacion';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
   AND (TABLE_NAME = @tablename)
   AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT ''Column already exists'' AS msg;',
  'ALTER TABLE itinerario_agencias ADD COLUMN fecha_confirmacion DATETIME;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- AGREGAR COLUMNA fecha_completado A itinerario_agencias
-- =============================================
SET @columnname = 'fecha_completado';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
   AND (TABLE_NAME = @tablename)
   AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT ''Column already exists'' AS msg;',
  'ALTER TABLE itinerario_agencias ADD COLUMN fecha_completado DATETIME;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- AGREGAR COLUMNA ESTADO A itinerario_locales
-- =============================================
SET @tablename = 'itinerario_locales';
SET @columnname = 'estado';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
   AND (TABLE_NAME = @tablename)
   AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT ''Column already exists'' AS msg;',
  'ALTER TABLE itinerario_locales ADD COLUMN estado ENUM(''pendiente'', ''confirmado'', ''completado'', ''cancelado'') DEFAULT ''pendiente'';'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- AGREGAR COLUMNA NOTAS A itinerario_locales
-- =============================================
SET @columnname = 'notas';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
   AND (TABLE_NAME = @tablename)
   AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT ''Column already exists'' AS msg;',
  'ALTER TABLE itinerario_locales ADD COLUMN notas TEXT;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- AGREGAR COLUMNA fecha_confirmacion A itinerario_locales
-- =============================================
SET @columnname = 'fecha_confirmacion';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
   AND (TABLE_NAME = @tablename)
   AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT ''Column already exists'' AS msg;',
  'ALTER TABLE itinerario_locales ADD COLUMN fecha_confirmacion DATETIME;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- AGREGAR COLUMNA fecha_completado A itinerario_locales
-- =============================================
SET @columnname = 'fecha_completado';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
   AND (TABLE_NAME = @tablename)
   AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT ''Column already exists'' AS msg;',
  'ALTER TABLE itinerario_locales ADD COLUMN fecha_completado DATETIME;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- VERIFICAR RESULTADOS
-- =============================================
SELECT 'Script ejecutado correctamente. Verificando columnas...' AS mensaje;

SELECT 
    'itinerario_destinos' AS tabla,
    COLUMN_NAME AS columna,
    COLUMN_TYPE AS tipo
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'itinerario_destinos'
  AND COLUMN_NAME IN ('estado');

SELECT 
    'itinerario_guias' AS tabla,
    COLUMN_NAME AS columna,
    COLUMN_TYPE AS tipo
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'itinerario_guias'
  AND COLUMN_NAME IN ('estado', 'notas', 'fecha_confirmacion', 'fecha_completado');

SELECT 
    'itinerario_agencias' AS tabla,
    COLUMN_NAME AS columna,
    COLUMN_TYPE AS tipo
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'itinerario_agencias'
  AND COLUMN_NAME IN ('estado', 'notas', 'fecha_confirmacion', 'fecha_completado');

SELECT 
    'itinerario_locales' AS tabla,
    COLUMN_NAME AS columna,
    COLUMN_TYPE AS tipo
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'itinerario_locales'
  AND COLUMN_NAME IN ('estado', 'notas', 'fecha_confirmacion', 'fecha_completado');

SELECT 'COMPLETADO: Todas las columnas agregadas correctamente.' AS resultado;
