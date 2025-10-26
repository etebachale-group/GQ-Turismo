-- ================================================================
-- SCRIPT DE ACTUALIZACIÓN FINAL - GQ TURISMO v2.0
-- Sistema de Tracking de Itinerarios y Responsive Design
-- Fecha: 23/10/2025
-- ================================================================

-- 1. ACTUALIZAR TABLA itinerario_destinos
-- Agregar columnas faltantes
ALTER TABLE itinerario_destinos 
ADD COLUMN IF NOT EXISTS fecha_inicio DATE NULL AFTER estado,
ADD COLUMN IF NOT EXISTS fecha_fin DATE NULL AFTER fecha_inicio,
ADD COLUMN IF NOT EXISTS descripcion TEXT NULL AFTER fecha_fin,
ADD COLUMN IF NOT EXISTS notas TEXT NULL AFTER descripcion,
ADD COLUMN IF NOT EXISTS completado_por INT(11) NULL AFTER notas,
ADD COLUMN IF NOT EXISTS fecha_completado DATETIME NULL AFTER completado_por;

-- 2. CREAR TABLA DE TAREAS DE ITINERARIO
CREATE TABLE IF NOT EXISTS itinerario_tareas (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT(11) NOT NULL,
    id_destino INT(11) NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    tipo_tarea ENUM('transporte', 'alojamiento', 'actividad', 'comida', 'guia', 'otro') DEFAULT 'actividad',
    fecha_hora_inicio DATETIME NULL,
    fecha_hora_fin DATETIME NULL,
    ubicacion VARCHAR(255) NULL,
    estado ENUM('pendiente', 'en_progreso', 'completado', 'cancelado') DEFAULT 'pendiente',
    prioridad ENUM('baja', 'media', 'alta') DEFAULT 'media',
    id_proveedor INT(11) NULL,
    tipo_proveedor ENUM('agencia', 'guia', 'local') NULL,
    creado_por INT(11) NOT NULL,
    completado_por INT(11) NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_completado DATETIME NULL,
    notas TEXT NULL,
    INDEX idx_itinerario (id_itinerario),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_hora_inicio),
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CREAR TABLA DE CONFIRMACIONES DE SERVICIOS
CREATE TABLE IF NOT EXISTS confirmaciones_servicios (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_pedido_servicio INT(11) NOT NULL,
    id_proveedor INT(11) NOT NULL,
    tipo_proveedor ENUM('agencia', 'guia', 'local') NOT NULL,
    estado_confirmacion ENUM('pendiente', 'confirmado', 'rechazado', 'completado') DEFAULT 'pendiente',
    fecha_confirmacion DATETIME NULL,
    fecha_servicio DATETIME NULL,
    notas_proveedor TEXT NULL,
    valoracion INT(1) NULL CHECK (valoracion >= 1 AND valoracion <= 5),
    comentario_valoracion TEXT NULL,
    fecha_valoracion DATETIME NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pedido (id_pedido_servicio),
    INDEX idx_proveedor (id_proveedor, tipo_proveedor),
    INDEX idx_estado (estado_confirmacion),
    FOREIGN KEY (id_pedido_servicio) REFERENCES pedidos_servicios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. CREAR TABLA DE NOTIFICACIONES
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT(11) NOT NULL,
    tipo ENUM('itinerario', 'reserva', 'mensaje', 'confirmacion', 'sistema') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    url VARCHAR(255) NULL,
    leido TINYINT(1) DEFAULT 0,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (id_usuario, leido),
    INDEX idx_fecha (fecha_creacion),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. CREAR TABLA PARA RELACIÓN GUÍAS-DESTINOS (Para futura implementación)
CREATE TABLE IF NOT EXISTS guias_destinos_disponibles (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_guia INT(11) NOT NULL,
    id_destino INT(11) NOT NULL,
    disponible TINYINT(1) DEFAULT 1,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_guia_destino (id_guia, id_destino),
    FOREIGN KEY (id_guia) REFERENCES guias_turisticos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. INSERTAR NOTIFICACIÓN DE BIENVENIDA PARA SUPER ADMINS
INSERT INTO notificaciones (id_usuario, tipo, titulo, mensaje, url)
SELECT 
    u.id,
    'sistema',
    '¡Sistema Actualizado a v2.0!',
    'El sistema GQ-Turismo ha sido actualizado con nuevas funcionalidades: Mapa de Tareas, Sistema de Confirmaciones, Diseño Responsive y más.',
    'test_system.php'
FROM usuarios u
WHERE u.tipo_usuario = 'super_admin'
AND NOT EXISTS (
    SELECT 1 FROM notificaciones n 
    WHERE n.id_usuario = u.id 
    AND n.titulo = '¡Sistema Actualizado a v2.0!'
);

-- 7. VERIFICACIÓN DE TABLAS
SELECT 
    'Verificación de Tablas Creadas' as Info,
    COUNT(*) as Total
FROM information_schema.tables 
WHERE table_schema = DATABASE()
AND table_name IN (
    'itinerario_tareas',
    'confirmaciones_servicios',
    'notificaciones',
    'guias_destinos_disponibles'
);

-- 8. ESTADÍSTICAS DEL SISTEMA
SELECT 
    'Usuarios' as Tabla,
    COUNT(*) as Total
FROM usuarios
UNION ALL
SELECT 'Itinerarios', COUNT(*) FROM itinerarios
UNION ALL
SELECT 'Tareas', COUNT(*) FROM itinerario_tareas
UNION ALL
SELECT 'Confirmaciones', COUNT(*) FROM confirmaciones_servicios
UNION ALL
SELECT 'Notificaciones', COUNT(*) FROM notificaciones
UNION ALL
SELECT 'Destinos', COUNT(*) FROM destinos
UNION ALL
SELECT 'Agencias', COUNT(*) FROM agencias
UNION ALL
SELECT 'Guías', COUNT(*) FROM guias_turisticos
UNION ALL
SELECT 'Locales', COUNT(*) FROM lugares_locales;

-- 9. OPTIMIZACIÓN DE ÍNDICES
ANALYZE TABLE usuarios, itinerarios, itinerario_tareas, pedidos_servicios, confirmaciones_servicios, notificaciones;

-- ================================================================
-- FIN DEL SCRIPT DE ACTUALIZACIÓN
-- ================================================================

SELECT '✓ Script de actualización ejecutado correctamente' as Resultado;
SELECT '✓ Tablas creadas: itinerario_tareas, confirmaciones_servicios, notificaciones, guias_destinos_disponibles' as Info;
SELECT '✓ Sistema listo para usar v2.0' as Estado;
