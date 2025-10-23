CREATE TABLE guias_destinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_guia INT NOT NULL,
    id_destino INT NOT NULL,
    FOREIGN KEY (id_guia) REFERENCES guias_turisticos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE CASCADE,
    UNIQUE (id_guia, id_destino) -- Un guía no puede ofrecer el mismo destino dos veces
);