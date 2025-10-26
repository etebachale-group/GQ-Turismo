-- Corrección de columnas faltantes en la base de datos

-- 1. Agregar columna telefono a la tabla usuarios si no existe
ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(20) DEFAULT NULL;

-- 2. Verificar y agregar columnas de fecha en itinerarios si no existen
ALTER TABLE itinerarios ADD COLUMN fecha_inicio DATE DEFAULT NULL;
ALTER TABLE itinerarios ADD COLUMN fecha_fin DATE DEFAULT NULL;

-- 3. Verificar columna descripcion en itinerarios
ALTER TABLE itinerarios ADD COLUMN descripcion TEXT DEFAULT NULL;

-- 4. Verificar columna descripcion en destinos
ALTER TABLE destinos ADD COLUMN descripcion TEXT DEFAULT NULL;

-- 5. Eliminar columna precio de itinerario_destinos si existe (no se usa)
-- Esta columna genera confusión, el precio está en presupuesto_estimado del itinerario
-- ALTER TABLE itinerario_destinos DROP COLUMN precio;

-- 6. Agregar columnas de fecha y descripción en itinerario_destinos si no existen
ALTER TABLE itinerario_destinos ADD COLUMN fecha_inicio DATE DEFAULT NULL;
ALTER TABLE itinerario_destinos ADD COLUMN fecha_fin DATE DEFAULT NULL;
ALTER TABLE itinerario_destinos ADD COLUMN descripcion TEXT DEFAULT NULL;

-- 7. Asegurar que itinerario_tareas tiene todas las columnas necesarias
ALTER TABLE itinerario_tareas ADD COLUMN fecha_hora_inicio DATETIME DEFAULT NULL;
ALTER TABLE itinerario_tareas ADD COLUMN fecha_hora_fin DATETIME DEFAULT NULL;
ALTER TABLE itinerario_tareas ADD COLUMN estado ENUM('pendiente', 'en_progreso', 'completado', 'cancelado') DEFAULT 'pendiente';
ALTER TABLE itinerario_tareas ADD COLUMN tipo_tarea VARCHAR(50) DEFAULT 'otro';
ALTER TABLE itinerario_tareas ADD COLUMN id_destino INT DEFAULT NULL;
ALTER TABLE itinerario_tareas ADD COLUMN id_proveedor INT DEFAULT NULL;

-- 8. Agregar columna imagen si no existe en publicidad_carousel
ALTER TABLE publicidad_carousel ADD COLUMN imagen VARCHAR(255) DEFAULT NULL;

-- 9. Agregar índices para mejorar rendimiento
ALTER TABLE itinerario_tareas ADD INDEX idx_itinerario (id_itinerario);
ALTER TABLE itinerario_tareas ADD INDEX idx_estado (estado);
ALTER TABLE itinerario_destinos ADD INDEX idx_itinerario (id_itinerario);
