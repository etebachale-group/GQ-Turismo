-- Crear tablas para asociar servicios con itinerarios

-- Tabla para guías en itinerarios
CREATE TABLE IF NOT EXISTS itinerario_guias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT NOT NULL,
    id_guia INT NOT NULL,
    fecha_asociacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_guia) REFERENCES guias_turisticos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_itinerario_guia (id_itinerario, id_guia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para agencias en itinerarios
CREATE TABLE IF NOT EXISTS itinerario_agencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT NOT NULL,
    id_agencia INT NOT NULL,
    fecha_asociacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_agencia) REFERENCES agencias(id) ON DELETE CASCADE,
    UNIQUE KEY unique_itinerario_agencia (id_itinerario, id_agencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para locales en itinerarios
CREATE TABLE IF NOT EXISTS itinerario_locales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_itinerario INT NOT NULL,
    id_local INT NOT NULL,
    fecha_asociacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_itinerario) REFERENCES itinerarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_local) REFERENCES lugares_locales(id) ON DELETE CASCADE,
    UNIQUE KEY unique_itinerario_local (id_itinerario, id_local)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar columna 'orden' a itinerario_destinos si no existe
ALTER TABLE itinerario_destinos 
ADD COLUMN IF NOT EXISTS orden INT DEFAULT 1 AFTER id_destino;

-- Crear índices para mejor rendimiento
CREATE INDEX IF NOT EXISTS idx_itinerario_guias_itinerario ON itinerario_guias(id_itinerario);
CREATE INDEX IF NOT EXISTS idx_itinerario_agencias_itinerario ON itinerario_agencias(id_itinerario);
CREATE INDEX IF NOT EXISTS idx_itinerario_locales_itinerario ON itinerario_locales(id_itinerario);
CREATE INDEX IF NOT EXISTS idx_itinerario_destinos_orden ON itinerario_destinos(id_itinerario, orden);
