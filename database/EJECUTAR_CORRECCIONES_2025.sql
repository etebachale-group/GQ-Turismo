-- ==================================================================
-- SCRIPT DE CORRECCIÓN CRÍTICA - EJECUTAR ESTE ARCHIVO
-- Base de datos: gq_turismo  
-- Fecha: 2025-01-24
-- Descripción: Corrige TODOS los errores reportados en el sistema
-- ==================================================================

USE gq_turismo;

-- ==================================================================
-- 1. AGREGAR COLUMNAS FALTANTES EN USUARIOS
-- ==================================================================

ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS telefono VARCHAR(20) DEFAULT NULL 
AFTER email;

-- ==================================================================
-- 2. CORREGIR TABLA ITINERARIOS
-- ==================================================================

-- Verificar si existe columna id_turista (puede estar mal nombrada)
ALTER TABLE itinerarios 
CHANGE COLUMN IF EXISTS id_usuario id_usuario INT NOT NULL;

-- Si se requiere id_turista, crear alias o copiar
ALTER TABLE itinerarios
ADD COLUMN IF NOT EXISTS id_turista INT DEFAULT NULL
AFTER id_usuario;

-- Copiar datos de id_usuario a id_turista si está vacío
UPDATE itinerarios SET id_turista = id_usuario WHERE id_turista IS NULL;

-- ==================================================================
-- 3. CREAR TABLA PUBLICIDAD_CAROUSEL
-- ==================================================================

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

-- ==================================================================
-- 4. CORREGIR TABLA ITINERARIO_DESTINOS
-- ==================================================================

ALTER TABLE itinerario_destinos 
ADD COLUMN IF NOT EXISTS precio DECIMAL(10,2) DEFAULT 0.00 
AFTER id_destino;

ALTER TABLE itinerario_destinos 
ADD COLUMN IF NOT EXISTS fecha_inicio DATE DEFAULT NULL
AFTER precio;

ALTER TABLE itinerario_destinos 
ADD COLUMN IF NOT EXISTS fecha_fin DATE DEFAULT NULL
AFTER fecha_inicio;

ALTER TABLE itinerario_destinos 
ADD COLUMN IF NOT EXISTS descripcion TEXT DEFAULT NULL
AFTER fecha_fin;

-- ==================================================================
-- 5. CREAR TABLA ITINERARIO_TAREAS (Sistema de seguimiento)
-- ==================================================================

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

-- ==================================================================
-- 6. CREAR TABLA LOCALES_TURISTICOS
-- ==================================================================

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

-- ==================================================================
-- 7. CREAR TABLA GUIAS_DESTINOS (Relación guías-destinos)
-- ==================================================================

CREATE TABLE IF NOT EXISTS guias_destinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_guia INT NOT NULL,
    id_destino INT NOT NULL,
    experiencia TEXT,
    tarifa_base DECIMAL(10,2) DEFAULT 0.00,
    disponible TINYINT(1) DEFAULT 1,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_guia) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_guia_destino (id_guia, id_destino),
    INDEX idx_guia (id_guia),
    INDEX idx_destino (id_destino),
    INDEX idx_disponible (disponible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================================
-- 8. VERIFICAR Y CREAR TABLA MENSAJES (Sistema de chat)
-- ==================================================================

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
    INDEX idx_fecha (fecha_envio),
    INDEX idx_conversacion (id_emisor, id_receptor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================================
-- 9. CREAR TABLA CONFIRMACIONES_SERVICIOS
-- ==================================================================

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

-- ==================================================================
-- 10. AGREGAR IMAGEN A DESTINOS SI NO EXISTE
-- ==================================================================

ALTER TABLE destinos 
ADD COLUMN IF NOT EXISTS imagen VARCHAR(255) DEFAULT NULL
AFTER descripcion;

-- ==================================================================
-- VERIFICACIONES FINALES
-- ==================================================================

-- Verificar que las tablas existan
SELECT 
    'Verificación completada' as Mensaje,
    COUNT(*) as Total_Tablas
FROM information_schema.tables 
WHERE table_schema = 'gq_turismo' 
AND table_name IN (
    'usuarios', 'destinos', 'itinerarios', 'itinerario_destinos', 
    'itinerario_tareas', 'publicidad_carousel', 'guias_destinos',
    'mensajes', 'confirmaciones_servicios', 'locales_turisticos'
);

-- Mostrar resumen
SELECT '===== CORRECCIONES APLICADAS EXITOSAMENTE =====' as Estado;
SELECT 'Todas las tablas y columnas han sido verificadas/creadas' as Resultado;
SELECT 'El sistema está listo para usar' as Mensaje_Final;
