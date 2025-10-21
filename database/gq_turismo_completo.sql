-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-10-2025 a las 11:00:00
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `gq_turismo`
--
CREATE DATABASE IF NOT EXISTS `gq_turismo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gq_turismo`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `es_admin` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `contrasena`, `es_admin`) VALUES
(1, 'Admin', 'admin@gqturismo.com', '$2y$10$If6.g/sH3I5eK1c.V8/f.eX5.2m1jO8i.p6.e/e.g.h', 1); -- Contraseña: admin

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `destinos`
--
CREATE TABLE `destinos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `destinos`
--
INSERT INTO `destinos` (`id`, `nombre`, `descripcion`, `categoria`, `imagen`, `precio`) VALUES
(1, 'Pico Basilé', 'El punto más alto de Guinea Ecuatorial, con vistas panorámicas espectaculares de Malabo y el Monte Camerún.', 'Montaña', 'pico_basile.jpg', 120.00),
(2, 'Playas de Ureka', 'Un paraíso para el ecoturismo, donde las tortugas marinas vienen a desovar en playas vírgenes.', 'Playa', 'ureka.jpg', 220.00),
(3, 'Parque Nacional de Monte Alén', 'Una densa selva tropical que alberga una increíble biodiversidad, incluyendo gorilas, chimpancés y elefantes.', 'Naturaleza', 'monte_alen.jpg', 180.00),
(4, 'Catedral de Santa Isabel', 'Una joya arquitectónica de estilo neogótico en el corazón de Malabo, un símbolo de la historia de la ciudad.', 'Cultura', 'catedral_malabo.jpg', 50.00),
(5, 'Cascadas de Moca', 'Impresionantes caídas de agua en la región de Moca, un lugar perfecto para el senderismo y la fotografía.', 'Naturaleza', 'cascadas_moca.jpg', 90.00),
(6, 'Isla de Corisco', 'Una isla paradisíaca con playas de arena blanca, aguas cristalinas y una rica historia precolonial.', 'Playa', 'corisco.jpg', 250.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `itinerarios`
--
CREATE TABLE `itinerarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `nombre_itinerario` varchar(255) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `itinerario_destinos`
--
CREATE TABLE `itinerario_destinos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_itinerario` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `orden` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_itinerario` (`id_itinerario`),
  KEY `id_destino` (`id_destino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--
CREATE TABLE `reservas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_itinerario` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_reserva` date NOT NULL,
  `personas` int(11) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `total_precio` decimal(10,2) NOT NULL,
  `estado` varchar(50) NOT NULL DEFAULT 'pendiente',
  PRIMARY KEY (`id`),
  KEY `id_itinerario` (`id_itinerario`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- AUTO_INCREMENT de las tablas volcadas
--

ALTER TABLE `destinos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
ALTER TABLE `itinerarios` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `itinerario_destinos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `reservas` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `usuarios` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

ALTER TABLE `itinerarios`
  ADD CONSTRAINT `itinerarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

ALTER TABLE `itinerario_destinos`
  ADD CONSTRAINT `itinerario_destinos_ibfk_1` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `itinerario_destinos_ibfk_2` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE;

ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
