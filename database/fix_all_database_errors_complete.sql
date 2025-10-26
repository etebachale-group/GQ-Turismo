-- =====================================================
-- FIX ALL DATABASE ERRORS - COMPLETE SYSTEM
-- =====================================================

-- 1. Agregar columna telefono a usuarios si no existe
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS telefono VARCHAR(20) DEFAULT NULL;

-- 2. Agregar columna precio a itinerario_destinos si no existe
ALTER TABLE itinerario_destinos ADD COLUMN IF NOT EXISTS precio DECIMAL(10,2) DEFAULT 0.00;

-- 3. Agregar columnas fecha_inicio y fecha_fin a itinerarios si no existen
ALTER TABLE itinerarios 
ADD COLUMN IF NOT EXISTS fecha_inicio DATE DEFAULT NULL,
ADD COLUMN IF NOT EXISTS fecha_fin DATE DEFAULT NULL;

-- 4. Agregar columna descripcion a itinerarios si no existe
ALTER TABLE itinerarios ADD COLUMN IF NOT EXISTS descripcion TEXT DEFAULT NULL;

-- 5. Cambiar id_turista a id_usuario en itinerarios (si existe como id_turista)
ALTER TABLE itinerarios 
ADD COLUMN IF NOT EXISTS id_usuario INT DEFAULT NULL,
ADD CONSTRAINT fk_itinerarios_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE;

-- Migrar datos de id_turista a id_usuario si existe
UPDATE itinerarios SET id_usuario = id_turista WHERE id_usuario IS NULL AND id_turista IS NOT NULL;

-- 6. Crear tabla publicidad_carousel si no existe
CREATE TABLE IF NOT EXISTS publicidad_carousel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(500) NOT NULL,
    link VARCHAR(500),
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_inicio DATE DEFAULT NULL,
    fecha_fin DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Crear tabla locales_turisticos si no existe
CREATE TABLE IF NOT EXISTS locales_turisticos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    direccion VARCHAR(500),
    ciudad VARCHAR(100),
    pais VARCHAR(100),
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    telefono VARCHAR(20),
    email VARCHAR(255),
    sitio_web VARCHAR(500),
    tipo_local VARCHAR(50),
    horario TEXT,
    calificacion DECIMAL(3,2) DEFAULT 0.00,
    imagen_principal VARCHAR(500),
    estado ENUM('activo', 'inactivo', 'pendiente') DEFAULT 'activo',
    verificado TINYINT(1) DEFAULT 0,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Verificar y actualizar tabla itinerario_tareas
ALTER TABLE itinerario_tareas 
ADD COLUMN IF NOT EXISTS estado ENUM('pendiente', 'en_progreso', 'completado', 'cancelado') DEFAULT 'pendiente',
ADD COLUMN IF NOT EXISTS fecha_hora_inicio DATETIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS fecha_hora_fin DATETIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS id_destino INT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS id_proveedor INT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS tipo_tarea VARCHAR(50) DEFAULT 'otro',
ADD COLUMN IF NOT EXISTS descripcion TEXT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS notas TEXT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS ubicacion_inicio VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS ubicacion_fin VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS completado_por INT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS fecha_completado DATETIME DEFAULT NULL;

-- 9. Crear índices para mejor rendimiento
CREATE INDEX IF NOT EXISTS idx_itinerarios_usuario ON itinerarios(id_usuario);
CREATE INDEX IF NOT EXISTS idx_itinerario_tareas_itinerario ON itinerario_tareas(id_itinerario);
CREATE INDEX IF NOT EXISTS idx_itinerario_tareas_estado ON itinerario_tareas(estado);
CREATE INDEX IF NOT EXISTS idx_pedidos_servicios_proveedor ON pedidos_servicios(id_proveedor, tipo_proveedor);
CREATE INDEX IF NOT EXISTS idx_pedidos_servicios_turista ON pedidos_servicios(id_turista);

-- 10. Actualizar tabla guias_destinos si no existe
CREATE TABLE IF NOT EXISTS guias_destinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_guia INT NOT NULL,
    id_destino INT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_guia_destino (id_guia, id_destino),
    FOREIGN KEY (id_guia) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Agregar columna imagen a manage_publicidad_carousel
ALTER TABLE publicidad_carousel 
MODIFY COLUMN imagen VARCHAR(500) DEFAULT NULL;

-- 12. Verificar estructura de pedidos_servicios
ALTER TABLE pedidos_servicios 
ADD COLUMN IF NOT EXISTS id_itinerario INT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS fecha_servicio DATE DEFAULT NULL,
ADD COLUMN IF NOT EXISTS hora_servicio TIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS numero_personas INT DEFAULT 1,
ADD COLUMN IF NOT EXISTS notas_especiales TEXT DEFAULT NULL;

-- 13. Limpiar datos huérfanos
DELETE FROM itinerario_tareas WHERE id_itinerario NOT IN (SELECT id FROM itinerarios);
DELETE FROM pedidos_servicios WHERE id_turista NOT IN (SELECT id FROM usuarios);

-- Verificación final
SELECT 'Base de datos actualizada correctamente' AS resultado;
