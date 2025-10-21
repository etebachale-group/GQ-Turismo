CREATE TABLE `itinerarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `nombre_itinerario` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `itinerario_destinos` (
  `id_itinerario` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `orden` int(11) NOT NULL,
  PRIMARY KEY (`id_itinerario`, `id_destino`),
  KEY `id_destino` (`id_destino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `itinerarios`
  ADD CONSTRAINT `itinerarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `itinerario_destinos`
  ADD CONSTRAINT `itinerario_destinos_ibfk_1` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `itinerario_destinos_ibfk_2` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
