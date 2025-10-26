-- Agregar columnas faltantes a la tabla itinerarios

-- Verificar y agregar fecha_inicio
ALTER TABLE itinerarios ADD COLUMN IF NOT EXISTS fecha_inicio DATE NULL AFTER fecha_creacion;

-- Verificar y agregar fecha_fin
ALTER TABLE itinerarios ADD COLUMN IF NOT EXISTS fecha_fin DATE NULL AFTER fecha_inicio;

-- Verificar y agregar presupuesto_estimado
ALTER TABLE itinerarios ADD COLUMN IF NOT EXISTS presupuesto_estimado DECIMAL(10,2) DEFAULT 0.00 AFTER fecha_fin;

-- Verificar y agregar notas
ALTER TABLE itinerarios ADD COLUMN IF NOT EXISTS notas TEXT NULL AFTER presupuesto_estimado;

-- Verificar y agregar estado
ALTER TABLE itinerarios ADD COLUMN IF NOT EXISTS estado ENUM('planificacion', 'confirmado', 'completado') DEFAULT 'planificacion' AFTER notas;

-- Agregar índices para optimizar consultas
ALTER TABLE itinerarios ADD INDEX IF NOT EXISTS idx_estado (estado);
ALTER TABLE itinerarios ADD INDEX IF NOT EXISTS idx_fecha_inicio (fecha_inicio);
