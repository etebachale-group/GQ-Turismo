-- =====================================================
-- ACTUALIZACIÓN DEL SISTEMA DE CHAT
-- =====================================================

-- Verificar tabla de mensajes
CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    sender_type ENUM('turista', 'agencia', 'guia', 'local', 'admin') NOT NULL,
    receiver_id INT NOT NULL,
    receiver_type ENUM('turista', 'agencia', 'guia', 'local', 'admin') NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leido TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Agregar índices
CREATE INDEX IF NOT EXISTS idx_sender ON mensajes(sender_id, sender_type);
CREATE INDEX IF NOT EXISTS idx_receiver ON mensajes(receiver_id, receiver_type);
CREATE INDEX IF NOT EXISTS idx_conversation ON mensajes(sender_id, receiver_id, sender_type, receiver_type);

-- Agregar columna de tipo de mensaje si no existe
ALTER TABLE mensajes 
ADD COLUMN IF NOT EXISTS tipo_mensaje VARCHAR(50) DEFAULT 'texto',
ADD COLUMN IF NOT EXISTS archivo_adjunto VARCHAR(500) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS fecha_lectura DATETIME DEFAULT NULL;

-- Índices adicionales para mejor rendimiento
CREATE INDEX IF NOT EXISTS idx_mensajes_no_leidos ON mensajes(receiver_id, receiver_type, leido);
CREATE INDEX IF NOT EXISTS idx_mensajes_conversacion_fecha ON mensajes(sender_id, receiver_id, id);

SELECT 'Tabla de mensajes actualizada correctamente' AS resultado;
