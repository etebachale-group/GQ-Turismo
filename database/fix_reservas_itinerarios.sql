-- Actualizar tabla de reservas para soportar itinerarios completos

-- Primero verificar la estructura actual
DESCRIBE reservas;

-- Agregar columnas necesarias si no existen
ALTER TABLE reservas 
ADD COLUMN IF NOT EXISTS id_itinerario INT NULL AFTER id_usuario,
ADD COLUMN IF NOT EXISTS fecha_inicio DATE NULL AFTER id_destino,
ADD COLUMN IF NOT EXISTS fecha_fin DATE NULL AFTER fecha_inicio,
ADD COLUMN IF NOT EXISTS num_personas INT DEFAULT 1 AFTER fecha_fin,
ADD COLUMN IF NOT EXISTS telefono_contacto VARCHAR(20) NULL AFTER num_personas,
ADD COLUMN IF NOT EXISTS comentarios TEXT NULL AFTER telefono_contacto,
ADD COLUMN IF NOT EXISTS monto_total DECIMAL(10,2) DEFAULT 0.00 AFTER comentarios,
ADD COLUMN IF NOT EXISTS fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER monto_total;

-- Agregar índices para mejor rendimiento
CREATE INDEX IF NOT EXISTS idx_reservas_itinerario ON reservas(id_itinerario);
CREATE INDEX IF NOT EXISTS idx_reservas_usuario ON reservas(id_usuario);
CREATE INDEX IF NOT EXISTS idx_reservas_estado ON reservas(estado);
CREATE INDEX IF NOT EXISTS idx_reservas_fecha ON reservas(fecha_reserva);

-- Agregar foreign key para itinerarios si no existe
-- Nota: MySQL no permite IF NOT EXISTS en foreign keys, por eso lo hacemos con un procedimiento
DELIMITER $$

CREATE PROCEDURE AddForeignKeyIfNotExists()
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
    
    -- Intentar agregar la foreign key
    ALTER TABLE reservas 
    ADD CONSTRAINT fk_reservas_itinerario 
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE SET NULL;
END$$

DELIMITER ;

CALL AddForeignKeyIfNotExists();
DROP PROCEDURE IF EXISTS AddForeignKeyIfNotExists;

-- Actualizar columna estado si es necesario
ALTER TABLE reservas 
MODIFY COLUMN estado ENUM('pendiente', 'confirmada', 'completada', 'cancelada') DEFAULT 'pendiente';

-- Mostrar estructura actualizada
DESCRIBE reservas;

SELECT 'Tabla de reservas actualizada exitosamente para soportar itinerarios' as Mensaje;
