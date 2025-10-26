-- FIX COMPLETO DEL SISTEMA GQ-TURISMO
-- Ejecutar este archivo para arreglar todas las tablas y relaciones

USE gq_turismo;

-- 1. ARREGLAR TABLA ITINERARIOS
ALTER TABLE itinerarios 
ADD COLUMN IF NOT EXISTS presupuesto_estimado DECIMAL(10,2) DEFAULT 0.00 AFTER fecha_fin;

-- 2. CREAR/VERIFICAR TABLAS DE RELACIONES DE ITINERARIOS

-- Tabla itinerario_destinos (relación muchos a muchos)
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

-- Tabla itinerario_agencias
CREATE TABLE IF NOT EXISTS itinerario_agencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT NOT NULL,
    id_agencia INT NOT NULL,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_agencia) REFERENCES agencias(id) ON DELETE CASCADE,
    UNIQUE KEY unique_itinerario_agencia (id_itinerario, id_agencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla itinerario_guias (NO itinerario_guias - nombre correcto)
CREATE TABLE IF NOT EXISTS itinerario_guias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT NOT NULL,
    id_guia INT NOT NULL,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_guia) REFERENCES guias_turisticos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_itinerario_guia (id_itinerario, id_guia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla itinerario_locales
CREATE TABLE IF NOT EXISTS itinerario_locales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT NOT NULL,
    id_local INT NOT NULL,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_local) REFERENCES lugares_locales(id) ON DELETE CASCADE,
    UNIQUE KEY unique_itinerario_local (id_itinerario, id_local)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. SISTEMA DE MENSAJES - Verificar y arreglar
CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    sender_type ENUM('turista', 'agencia', 'guia', 'local', 'super_admin') NOT NULL,
    receiver_id INT NOT NULL,
    receiver_type ENUM('turista', 'agencia', 'guia', 'local', 'super_admin') NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sender (sender_id, sender_type),
    INDEX idx_receiver (receiver_id, receiver_type),
    INDEX idx_timestamp (timestamp),
    INDEX idx_conversation (sender_id, sender_type, receiver_id, receiver_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. IMAGENES DE DESTINOS
CREATE TABLE IF NOT EXISTS imagenes_destino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_destino INT NOT NULL,
    ruta_imagen VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. VERIFICAR COLUMNAS EN DESTINOS
ALTER TABLE destinos 
ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 7) NULL AFTER ciudad,
ADD COLUMN IF NOT EXISTS longitude DECIMAL(10, 7) NULL AFTER latitude;

-- 6. SISTEMA DE RESERVAS - Verificar tabla
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_itinerario INT,
    tipo_servicio ENUM('agencia', 'guia', 'local', 'destino') NOT NULL,
    id_servicio INT NOT NULL,
    fecha_reserva DATE NOT NULL,
    hora_reserva TIME,
    numero_personas INT DEFAULT 1,
    precio_total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'cancelada', 'completada') DEFAULT 'pendiente',
    notas TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (id_usuario),
    INDEX idx_servicio (tipo_servicio, id_servicio),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. PEDIDOS
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_reserva INT,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'procesando', 'completado', 'cancelado') DEFAULT 'pendiente',
    metodo_pago VARCHAR(50),
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_reserva) REFERENCES reservas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. LIMPIAR DUPLICADOS EN DESTINOS (si existen)
-- Crear tabla temporal con destinos únicos
CREATE TEMPORARY TABLE temp_destinos_unicos AS
SELECT MIN(id) as id
FROM destinos
GROUP BY nombre, categoria, ciudad;

-- Marcar para mantener (no eliminar duplicados si tienen referencias)
-- Solo mostrar información, no borrar automáticamente
SELECT d.id, d.nombre, d.categoria, d.ciudad, 
       COUNT(*) OVER (PARTITION BY d.nombre, d.categoria, d.ciudad) as duplicados
FROM destinos d
WHERE (d.nombre, d.categoria, d.ciudad) IN (
    SELECT nombre, categoria, ciudad
    FROM destinos
    GROUP BY nombre, categoria, ciudad
    HAVING COUNT(*) > 1
)
ORDER BY d.nombre, d.categoria, d.ciudad;

-- 9. ÍNDICES PARA OPTIMIZACIÓN
CREATE INDEX IF NOT EXISTS idx_destinos_categoria ON destinos(categoria);
CREATE INDEX IF NOT EXISTS idx_destinos_ciudad ON destinos(ciudad);
CREATE INDEX IF NOT EXISTS idx_destinos_precio ON destinos(precio);

CREATE INDEX IF NOT EXISTS idx_guias_ciudad ON guias_turisticos(ciudad_operacion);
CREATE INDEX IF NOT EXISTS idx_locales_tipo ON lugares_locales(tipo_local);

-- 10. VERIFICAR DATOS
SELECT 'Itinerarios' as Tabla, COUNT(*) as Total FROM itinerarios
UNION ALL
SELECT 'Destinos', COUNT(*) FROM destinos
UNION ALL
SELECT 'Mensajes', COUNT(*) FROM mensajes
UNION ALL
SELECT 'Reservas', COUNT(*) FROM reservas
UNION ALL
SELECT 'Itinerario-Destinos', COUNT(*) FROM itinerario_destinos
UNION ALL
SELECT 'Itinerario-Guias', COUNT(*) FROM itinerario_guias
UNION ALL
SELECT 'Itinerario-Agencias', COUNT(*) FROM itinerario_agencias
UNION ALL
SELECT 'Itinerario-Locales', COUNT(*) FROM itinerario_locales;

-- Completado
SELECT '✅ SISTEMA ARREGLADO CORRECTAMENTE' as Estado;
