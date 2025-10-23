-- Fix Chat System: Crear/Actualizar tabla de mensajes
-- Este script asegura que la tabla mensajes tenga la estructura correcta

-- Eliminar la tabla si existe para recrearla correctamente
DROP TABLE IF EXISTS mensajes;

-- Crear la tabla mensajes con la estructura correcta
CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    sender_type ENUM('turista', 'agencia', 'guia', 'local') NOT NULL,
    receiver_id INT NOT NULL,
    receiver_type ENUM('turista', 'agencia', 'guia', 'local') NOT NULL,
    message TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    INDEX idx_sender (sender_id, sender_type),
    INDEX idx_receiver (receiver_id, receiver_type),
    INDEX idx_conversation (sender_id, sender_type, receiver_id, receiver_type),
    INDEX idx_timestamp (timestamp),
    INDEX idx_unread (receiver_id, receiver_type, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nota: Las foreign keys no se aplican porque los IDs pueden estar en diferentes tablas
-- (usuarios, agencias, guias_turisticos, lugares_locales)
