ALTER TABLE pedidos_servicios
ADD COLUMN id_destino INT NULL,
ADD CONSTRAINT fk_id_destino FOREIGN KEY (id_destino) REFERENCES destinos(id) ON DELETE SET NULL;