CREATE TABLE IF NOT EXISTS valoraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_id INT NOT NULL,
    provider_id INT NOT NULL,
    provider_type ENUM('agencia', 'guia', 'local') NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reviewer_id) REFERENCES usuarios(id) ON DELETE CASCADE
    -- FOREIGN KEY (provider_id) REFERENCES agencias(id) ON DELETE CASCADE, -- This would require multiple FKs or a more complex design
    -- FOREIGN KEY (provider_id) REFERENCES guias_turisticos(id) ON DELETE CASCADE,
    -- FOREIGN KEY (provider_id) REFERENCES lugares_locales(id) ON DELETE CASCADE
);