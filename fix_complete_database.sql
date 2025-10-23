-- =====================================================
-- FIX COMPLETO DE LA BASE DE DATOS GQ-TURISMO
-- =====================================================

USE gq_turismo;

-- 1. ARREGLAR TABLA ITINERARIOS
-- Verificar y agregar columnas faltantes
SET @dbname = 'gq_turismo';
SET @tablename = 'itinerarios';
SET @columnname = 'fecha_inicio';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
     AND (TABLE_NAME = @tablename)
     AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DATE NULL AFTER estado')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'fecha_fin';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
     AND (TABLE_NAME = @tablename)
     AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DATE NULL AFTER fecha_inicio')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'presupuesto_estimado';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
     AND (TABLE_NAME = @tablename)
     AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(10,2) DEFAULT 0 AFTER fecha_fin')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'ciudad';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
     AND (TABLE_NAME = @tablename)
     AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL AFTER presupuesto_estimado')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'notas';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
     AND (TABLE_NAME = @tablename)
     AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TEXT NULL AFTER ciudad')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'precio_total';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
     AND (TABLE_NAME = @tablename)
     AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(10,2) DEFAULT 0 AFTER notas')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. CREAR TABLA itinerario_destinos si no existe
CREATE TABLE IF NOT EXISTS itinerario_destinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT NOT NULL,
    id_destino INT NOT NULL,
    orden INT DEFAULT 0,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_itinerario_destino (id_itinerario, id_destino)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CREAR TABLA itinerario_servicios si no existe
CREATE TABLE IF NOT EXISTS itinerario_servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT NOT NULL,
    tipo_servicio ENUM('guia', 'agencia', 'local') NOT NULL,
    id_servicio INT NOT NULL,
    notas TEXT,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    INDEX idx_servicio (tipo_servicio, id_servicio),
    INDEX idx_itinerario (id_itinerario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ARREGLAR TABLA RESERVAS
ALTER TABLE reservas 
MODIFY COLUMN fecha_inicio DATE NULL,
MODIFY COLUMN fecha_fin DATE NULL;

-- Agregar columnas faltantes a reservas
SET @tablename = 'reservas';
SET @columnname = 'id_itinerario';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
     AND (TABLE_NAME = @tablename)
     AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT NULL AFTER id')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar foreign key para itinerario
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
   WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND CONSTRAINT_NAME = 'fk_reservas_itinerario'
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD CONSTRAINT fk_reservas_itinerario FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE SET NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 5. CREAR TABLA reserva_servicios para relación reservas-servicios
CREATE TABLE IF NOT EXISTS reserva_servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva INT NOT NULL,
    tipo_servicio ENUM('guia', 'agencia', 'local', 'destino') NOT NULL,
    id_servicio INT NOT NULL,
    estado ENUM('pendiente', 'confirmado', 'cancelado') DEFAULT 'pendiente',
    fecha_servicio DATE NULL,
    precio DECIMAL(10,2) DEFAULT 0,
    notas TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_reserva) REFERENCES reservas(id) ON DELETE CASCADE,
    INDEX idx_servicio (tipo_servicio, id_servicio),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. LIMPIAR DUPLICADOS EN DESTINOS (si existen)
DELETE t1 FROM destinos t1
INNER JOIN destinos t2 
WHERE t1.id > t2.id 
AND t1.nombre = t2.nombre 
AND t1.categoria = t2.categoria;

-- 7. MEJORAR ÍNDICES
ALTER TABLE mensajes 
ADD INDEX idx_conversation (sender_id, sender_type, receiver_id, receiver_type),
ADD INDEX idx_timestamp (timestamp DESC),
ADD INDEX idx_unread (receiver_id, receiver_type, is_read);

ALTER TABLE destinos
ADD INDEX idx_categoria (categoria),
ADD INDEX idx_ciudad (ciudad);

-- 8. ASEGURAR QUE MENSAJES TIENE LAS COLUMNAS CORRECTAS
ALTER TABLE mensajes 
MODIFY COLUMN sender_type ENUM('turista', 'agencia', 'guia', 'local') NOT NULL,
MODIFY COLUMN receiver_type ENUM('turista', 'agencia', 'guia', 'local') NOT NULL,
MODIFY COLUMN is_read BOOLEAN DEFAULT FALSE;

-- 9. VERIFICAR TABLA IMAGENES_DESTINO
CREATE TABLE IF NOT EXISTS imagenes_destino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_destino INT NOT NULL,
    ruta_imagen VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE,
    INDEX idx_destino (id_destino)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. ACTUALIZAR ESTADO DE ITINERARIOS
UPDATE itinerarios 
SET estado = 'planificacion' 
WHERE estado NOT IN ('planificacion', 'confirmado', 'completado');

-- 11. AGREGAR COORDENADAS A DESTINOS (si no existen)
SET @tablename = 'destinos';
SET @columnname = 'latitude';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
     AND (TABLE_NAME = @tablename)
     AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(10,8) NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'longitude';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (TABLE_SCHEMA = @dbname)
     AND (TABLE_NAME = @tablename)
     AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(11,8) NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =====================================================
-- VERIFICACIÓN FINAL
-- =====================================================

SELECT 'Base de datos actualizada correctamente' AS Status;

-- Mostrar estructura de tablas principales
SHOW COLUMNS FROM itinerarios;
SHOW COLUMNS FROM itinerario_destinos;
SHOW COLUMNS FROM itinerario_servicios;
SHOW COLUMNS FROM reservas;
SHOW COLUMNS FROM reserva_servicios;
