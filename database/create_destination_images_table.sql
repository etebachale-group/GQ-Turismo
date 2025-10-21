CREATE TABLE IF NOT EXISTS imagenes_destino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_destino INT NOT NULL,
    ruta_imagen VARCHAR(255) NOT NULL,
    descripcion VARCHAR(255),
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE
);