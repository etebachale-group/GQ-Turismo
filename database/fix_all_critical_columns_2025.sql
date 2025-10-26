-- Script de corrección crítica de todas las columnas faltantes
-- Fecha: 2025-01-24
-- Descripción: Corrige todos los errores de columnas reportados

-- 1. Agregar columna telefono a usuarios si no existe
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS telefono VARCHAR(20) DEFAULT NULL 
AFTER email;

-- 2. Agregar columna id_turista a itinerarios si no existe
ALTER TABLE itinerarios 
ADD COLUMN IF NOT EXISTS id_turista INT NOT NULL 
AFTER id,
ADD INDEX IF NOT EXISTS idx_id_turista (id_turista);

-- 3. Crear tabla publicidad_carousel si no existe
CREATE TABLE IF NOT EXISTS publicidad_carousel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_inicio DATE,
    fecha_fin DATE,
    tipo_publicidad ENUM('banner', 'destacado', 'promocion') DEFAULT 'banner',
    id_proveedor INT,
    tipo_proveedor ENUM('agencia', 'guia', 'local'),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activo (activo),
    INDEX idx_orden (orden),
    INDEX idx_proveedor (id_proveedor, tipo_proveedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Crear tabla locales_turisticos si no existe (renombrada de locales)
CREATE TABLE IF NOT EXISTS locales_turisticos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    direccion VARCHAR(255),
    ciudad VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(100),
    sitio_web VARCHAR(255),
    tipo_local ENUM('restaurante', 'hotel', 'tienda', 'museo', 'otro') DEFAULT 'restaurante',
    calificacion DECIMAL(3,2) DEFAULT 0.00,
    imagen VARCHAR(255),
    latitud DECIMAL(10,8),
    longitud DECIMAL(11,8),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_ciudad (ciudad),
    INDEX idx_tipo (tipo_local),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Agregar columnas faltantes a itinerario_destinos
ALTER TABLE itinerario_destinos 
ADD COLUMN IF NOT EXISTS precio DECIMAL(10,2) DEFAULT 0.00 AFTER id_destino,
ADD COLUMN IF NOT EXISTS fecha_inicio DATE AFTER precio,
ADD COLUMN IF NOT EXISTS fecha_fin DATE AFTER fecha_inicio,
ADD COLUMN IF NOT EXISTS descripcion TEXT AFTER fecha_fin;

-- 6. Agregar columna imagen a destinos si no existe
ALTER TABLE destinos 
ADD COLUMN IF NOT EXISTS imagen VARCHAR(255) DEFAULT NULL 
AFTER descripcion;

-- 7. Crear tabla itinerario_tareas si no existe (para el mapa de tareas)
CREATE TABLE IF NOT EXISTS itinerario_tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT NOT NULL,
    id_destino INT,
    id_proveedor INT,
    tipo_proveedor ENUM('agencia', 'guia', 'local'),
    tipo_tarea ENUM('transporte', 'alojamiento', 'actividad', 'comida', 'guia', 'otro') DEFAULT 'otro',
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_hora_inicio DATETIME,
    fecha_hora_fin DATETIME,
    ubicacion VARCHAR(255),
    latitud DECIMAL(10,8),
    longitud DECIMAL(11,8),
    estado ENUM('pendiente', 'en_progreso', 'completado', 'cancelado') DEFAULT 'pendiente',
    prioridad INT DEFAULT 0,
    notas TEXT,
    costo DECIMAL(10,2) DEFAULT 0.00,
    completado_por INT,
    fecha_completado DATETIME,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE SET NULL,
    FOREIGN KEY (completado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_itinerario (id_itinerario),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_hora_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Crear tabla confirmaciones_servicios si no existe
CREATE TABLE IF NOT EXISTS confirmaciones_servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_proveedor INT NOT NULL,
    tipo_proveedor ENUM('agencia', 'guia', 'local') NOT NULL,
    estado ENUM('pendiente', 'confirmado', 'rechazado', 'completado') DEFAULT 'pendiente',
    notas TEXT,
    fecha_confirmacion DATETIME,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pedido) REFERENCES pedidos_servicios(id) ON DELETE CASCADE,
    INDEX idx_pedido (id_pedido),
    INDEX idx_proveedor (id_proveedor, tipo_proveedor),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Crear tabla guias_destinos si no existe (relación guías-destinos)
CREATE TABLE IF NOT EXISTS guias_destinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_guia INT NOT NULL,
    id_destino INT NOT NULL,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_guia) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_guia_destino (id_guia, id_destino),
    INDEX idx_guia (id_guia),
    INDEX idx_destino (id_destino)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Verificar y actualizar estructura de mensajes
CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_emisor INT NOT NULL,
    id_receptor INT NOT NULL,
    mensaje TEXT NOT NULL,
    leido TINYINT(1) DEFAULT 0,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_emisor) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_receptor) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_emisor (id_emisor),
    INDEX idx_receptor (id_receptor),
    INDEX idx_leido (leido),
    INDEX idx_fecha (fecha_envio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Mensaje de confirmación
SELECT 'Script de corrección ejecutado correctamente' AS mensaje;
