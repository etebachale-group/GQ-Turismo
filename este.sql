-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-10-2025 a las 15:28:43
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gq_turismo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agencias`
--

CREATE TABLE `agencias` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_agencia` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `contacto_email` varchar(255) DEFAULT NULL,
  `contacto_telefono` varchar(50) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carouseles`
--

CREATE TABLE `carouseles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carouseles`
--

INSERT INTO `carouseles` (`id`, `nombre`, `ruta_imagen`, `enlace`, `orden`, `activo`, `fecha_creacion`) VALUES
(1, 'Malabo al Atardecer', 'carousel_malabo.jpg', 'destinos.php?id=4', 1, 1, '2025-10-21 13:25:03'),
(2, 'Ventage Mall', '68f7a6b836247_1a_Ventage_Mall.jpg', '', 0, 1, '2025-10-21 15:28:56'),
(5, 'Pico Basile', '68f7a7619e950_pico_basile.jpg', '', 0, 1, '2025-10-21 15:31:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `descuentos`
--

CREATE TABLE `descuentos` (
  `id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `discount_code` varchar(50) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `destinos`
--

CREATE TABLE `destinos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `ciudad` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `guias_destinos`
--

CREATE TABLE `guias_destinos` (
  `id` int(11) NOT NULL,
  `id_guia` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `guias_turisticos`
--

CREATE TABLE `guias_turisticos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_guia` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `especialidades` varchar(255) DEFAULT NULL,
  `precio_hora` decimal(10,2) NOT NULL DEFAULT 0.00,
  `contacto_email` varchar(255) DEFAULT NULL,
  `contacto_telefono` varchar(50) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `current_latitude` decimal(10,8) DEFAULT NULL,
  `current_longitude` decimal(11,8) DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  `ciudad_operacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `guias_turisticos`
--

INSERT INTO `guias_turisticos` (`id`, `id_usuario`, `nombre_guia`, `descripcion`, `especialidades`, `precio_hora`, `contacto_email`, `contacto_telefono`, `fecha_registro`, `current_latitude`, `current_longitude`, `last_updated`, `ciudad_operacion`) VALUES
(1, 8, 'Carlos Viajero', 'Guía local con 10 años de experiencia en Bioko y Annobón.', 'Historia, Naturaleza, Cultura', 30.00, 'guia@example.com', '+240333444', '2025-10-21 16:44:58', NULL, NULL, NULL, NULL),
(2, 4, 'Guia', '', 'viajar', 10.00, 'guia@guia.com', '+233208870387', '2025-10-21 23:11:17', 0.00000000, 0.00000000, '2025-10-22 03:31:38', NULL),
(3, 12, 'Ana Exploradora', 'Especialista en rutas de aventura y ecoturismo en el interior de Guinea Ecuatorial.', 'Aventura, Ecoturismo, Fauna', 45.00, 'guia2@example.com', '+240777888', '2025-10-22 04:11:01', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_destino`
--

CREATE TABLE `imagenes_destino` (
  `id` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_guia`
--

CREATE TABLE `imagenes_guia` (
  `id` int(11) NOT NULL,
  `id_guia` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_local`
--

CREATE TABLE `imagenes_local` (
  `id` int(11) NOT NULL,
  `id_local` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `itinerarios`
--

CREATE TABLE `itinerarios` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_itinerario` varchar(255) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ciudad` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `itinerario_destinos`
--

CREATE TABLE `itinerario_destinos` (
  `id` int(11) NOT NULL,
  `id_itinerario` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `orden` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lugares_locales`
--

CREATE TABLE `lugares_locales` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_local` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_local` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `contacto_email` varchar(255) DEFAULT NULL,
  `contacto_telefono` varchar(50) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `lugares_locales`
--

INSERT INTO `lugares_locales` (`id`, `id_usuario`, `nombre_local`, `descripcion`, `tipo_local`, `direccion`, `contacto_email`, `contacto_telefono`, `fecha_registro`, `latitude`, `longitude`) VALUES
(1, 9, 'Restaurante Sabor GE', 'Disfruta de la auténtica gastronomía ecuatoguineana.', 'Restaurante', 'Calle Real, Malabo', 'local@example.com', '+240555666', '2025-10-21 16:44:59', NULL, NULL),
(2, 6, 'Hotel Banapa ', 'El hotel más económico y confortable', 'Hotel', 'Avenida de la Independencia Malabo', 'ferchewon@gmail.com', '+233208870387', '2025-10-21 23:23:43', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('turista','agencia','guia','local') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_type` enum('turista','agencia','guia','local') NOT NULL,
  `message` text NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menus_agencia`
--

CREATE TABLE `menus_agencia` (
  `id` int(11) NOT NULL,
  `id_agencia` int(11) NOT NULL,
  `nombre_menu` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menus_local`
--

CREATE TABLE `menus_local` (
  `id` int(11) NOT NULL,
  `id_local` int(11) NOT NULL,
  `nombre_menu` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos_servicios`
--

CREATE TABLE `pedidos_servicios` (
  `id` int(11) NOT NULL,
  `id_turista` int(11) NOT NULL,
  `tipo_proveedor` enum('agencia','guia','local') NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `id_servicio_o_menu` int(11) NOT NULL,
  `tipo_item` enum('servicio','menu') NOT NULL,
  `id_itinerario` int(11) DEFAULT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_servicio` date DEFAULT NULL,
  `cantidad_personas` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','confirmado','cancelado','completado') NOT NULL DEFAULT 'pendiente',
  `id_destino` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos_servicios`
--

INSERT INTO `pedidos_servicios` (`id`, `id_turista`, `tipo_proveedor`, `id_proveedor`, `id_servicio_o_menu`, `tipo_item`, `id_itinerario`, `fecha_solicitud`, `fecha_servicio`, `cantidad_personas`, `precio_unitario`, `precio_total`, `estado`, `id_destino`) VALUES
(2, 3, 'agencia', 1, 1, 'servicio', NULL, '2025-10-21 16:32:28', '2025-10-26', 1, 100.00, 100.00, 'pendiente', NULL),
(3, 3, 'agencia', 1, 3, 'menu', NULL, '2025-10-21 16:36:20', '2025-10-21', 2, 200.00, 400.00, 'pendiente', NULL),
(4, 3, 'agencia', 1, 1, 'servicio', NULL, '2025-10-21 22:33:20', '2025-10-21', 1, 100.00, 100.00, 'pendiente', NULL),
(5, 3, 'agencia', 1, 2, 'menu', NULL, '2025-10-21 23:04:46', '2025-10-21', 1, 130.00, 130.00, 'pendiente', NULL),
(6, 3, 'local', 2, 1, 'servicio', NULL, '2025-10-21 23:29:15', '2025-10-21', 1, 3.00, 3.00, 'pendiente', NULL),
(7, 3, 'local', 2, 1, 'servicio', NULL, '2025-10-21 23:29:27', '2025-10-21', 1, 3.00, 3.00, 'pendiente', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicidades`
--

CREATE TABLE `publicidades` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) NOT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `publicidades`
--

INSERT INTO `publicidades` (`id`, `titulo`, `descripcion`, `imagen`, `enlace`, `fecha_inicio`, `fecha_fin`, `activo`, `fecha_creacion`) VALUES
(1, 'Descubre la Isla de Bioko', 'Explora las maravillas naturales y culturales de Bioko.', 'publicidad_bioko.jpg', 'destinos.php?id=1', '2025-10-21', '2025-11-20', 1, '2025-10-21 13:25:03'),
(2, 'Ventage Mall', 'Centro comercial muy reconocido con una abundancia de productos y bienes', '68f7a6970b712_1a_Ventage_Mall.jpg', '', '2025-10-21', '2025-10-23', 1, '2025-10-21 15:28:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `id_itinerario` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_reserva` date NOT NULL,
  `personas` int(11) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `total_precio` decimal(10,2) NOT NULL,
  `estado` varchar(50) NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios_agencia`
--

CREATE TABLE `servicios_agencia` (
  `id` int(11) NOT NULL,
  `id_agencia` int(11) NOT NULL,
  `nombre_servicio` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios_guia`
--

CREATE TABLE `servicios_guia` (
  `id` int(11) NOT NULL,
  `id_guia` int(11) NOT NULL,
  `nombre_servicio` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios_guia`
--

INSERT INTO `servicios_guia` (`id`, `id_guia`, `nombre_servicio`, `descripcion`, `precio`, `fecha_creacion`) VALUES
(1, 2, 'guia', 'visitas ', 10.00, '2025-10-21 23:49:37'),
(2, 1, 'Tour Histórico Malabo', 'Recorrido por los sitios históricos de Malabo.', 50.00, '2025-10-22 04:11:00'),
(3, 1, 'Senderismo Monte Alén', 'Excursión de día completo en el Parque Nacional.', 120.00, '2025-10-22 04:11:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios_local`
--

CREATE TABLE `servicios_local` (
  `id` int(11) NOT NULL,
  `id_local` int(11) NOT NULL,
  `nombre_servicio` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios_local`
--

INSERT INTO `servicios_local` (`id`, `id_local`, `nombre_servicio`, `descripcion`, `precio`, `fecha_creacion`) VALUES
(1, 2, 'Alojamiento', 'DISPONIBILIDAD DE HABITACIONES', 3.00, '2025-10-21 23:27:41'),
(2, 1, 'Cena Romántica', 'Menú especial para parejas.', 80.00, '2025-10-22 04:11:05'),
(3, 1, 'Catering para Eventos', 'Servicio de catering para cualquier ocasión.', 200.00, '2025-10-22 04:11:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `es_admin` tinyint(1) NOT NULL DEFAULT 0,
  `tipo_usuario` enum('turista','agencia','guia','local','super_admin') NOT NULL DEFAULT 'turista',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `contrasena`, `es_admin`, `tipo_usuario`, `fecha_registro`) VALUES
(1, 'Admin', 'admin@gqturismo.com', '$2y$10$PLRwHgKJbE7K0vFe.hlNY.GYP0d21YEx1wT2YU9xzd/4jqf9AC.xq', 1, 'super_admin', '2025-10-21 10:41:02'),
(2, 'Eteba Chale Group', 'etebachalegroup@gmail.com', '$2y$10$7PkejqdDvMz1xfVjWRZqrunw4f1yaWoLX63OGPHVBb.HoFV357ps6', 1, 'super_admin', '2025-10-21 11:16:59'),
(3, 'Juan', 'juan@gmail.com', '$2y$10$t6jmUtem9QD6ra4bHvqN/.Xdf37/GZLGie9K.BNb56c97GGCrzRIS', 0, 'turista', '2025-10-21 11:58:06'),
(4, 'guia', 'guia@gmail.com', '$2y$10$QQyJ6uYlvqKaNBveanpfuuqXw5OdCXf0X8nH4pX3q.n384lvrAlaK', 0, 'guia', '2025-10-21 12:00:18'),
(5, 'Eteba Chale Group', 'etebachalegroup@admin.com', '$2y$10$31r7rW9Nf8V8AM4scRX8fuJt13bMS.YJnEzvaTW/Q5X1miU51B4iO', 0, 'agencia', '2025-10-21 12:15:46'),
(6, 'Hotel Banapa', 'banapa@hotel.com', '$2y$10$H65Q2gCkS3sNer8SGwLpKu8VZhtN/xI/vo/qcRhmALgdk.SrEH6wi', 0, 'local', '2025-10-21 12:50:31'),
(7, 'Agencia de Viajes Ejemplo', 'agencia@example.com', '$2y$10$B3n.D5cExvA.HbFuoOPGoeiMAI1pejcnaRIcUOQpny0jO1iz8DB0u', 0, 'agencia', '2025-10-21 13:25:02'),
(8, 'Guía Experto GE', 'guia@example.com', '$2y$10$4wbEuH3mcwGFndXezYuZiuc/Gr.3joCAALqzwGb16uHE4roUhOzxq', 0, 'guia', '2025-10-21 13:25:02'),
(9, 'Restaurante Sabor GE', 'local@example.com', '$2y$10$zXim26lxXHhgr1HSWHohEOCBiA8jNqsFpDWF3jjdZ19v6MtNTBrcy', 0, 'local', '2025-10-21 13:25:03'),
(10, 'CASA BLANCA', 'casablanca@agencia.com', '$2y$10$RSIlsNRvSj..k/V5D5v9XuhyXbGKdXuzYcWcDzCh1oaa123cgvfUK', 0, 'guia', '2025-10-21 14:54:04'),
(11, 'CEIBA INTERCONTINENTAL', 'ceiba@vuelos.com', '$2y$10$2wx7B.n/ZEmLYGIj3mLjlOB.zA6foMknUW4QNokNnCQ4hDMFHI9Z2', 0, 'agencia', '2025-10-21 14:59:07'),
(12, 'Ana Exploradora', 'guia2@example.com', '$2y$10$gTSHK3BYyObAsd2bR1Ah7.t6yFxaKazONO0n0QX5gLRl3hPBHNttC', 0, 'guia', '2025-10-21 16:44:59'),
(13, 'Cosme', 'cosme@gmail.com', '$2y$10$PHbmosC1wjuzAPy.CFYjwOxhBgM8U6Z6U2TL2F4kihui4vroprTSe', 0, 'guia', '2025-10-21 21:21:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `valoraciones`
--

CREATE TABLE `valoraciones` (
  `id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `provider_type` enum('agencia','guia','local') NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `valoraciones`
--

INSERT INTO `valoraciones` (`id`, `reviewer_id`, `provider_id`, `provider_type`, `rating`, `comment`, `timestamp`) VALUES
(1, 3, 1, 'agencia', 5, '', '2025-10-21 23:04:38');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `agencias`
--
ALTER TABLE `agencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `carouseles`
--
ALTER TABLE `carouseles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `descuentos`
--
ALTER TABLE `descuentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `discount_code` (`discount_code`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Indices de la tabla `destinos`
--
ALTER TABLE `destinos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `guias_destinos`
--
ALTER TABLE `guias_destinos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_guia` (`id_guia`,`id_destino`),
  ADD KEY `id_destino` (`id_destino`);

--
-- Indices de la tabla `guias_turisticos`
--
ALTER TABLE `guias_turisticos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `imagenes_destino`
--
ALTER TABLE `imagenes_destino`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_destino` (`id_destino`);

--
-- Indices de la tabla `imagenes_guia`
--
ALTER TABLE `imagenes_guia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_guia` (`id_guia`);

--
-- Indices de la tabla `imagenes_local`
--
ALTER TABLE `imagenes_local`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_local` (`id_local`);

--
-- Indices de la tabla `itinerarios`
--
ALTER TABLE `itinerarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `itinerario_destinos`
--
ALTER TABLE `itinerario_destinos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_itinerario` (`id_itinerario`),
  ADD KEY `id_destino` (`id_destino`);

--
-- Indices de la tabla `lugares_locales`
--
ALTER TABLE `lugares_locales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indices de la tabla `menus_agencia`
--
ALTER TABLE `menus_agencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_agencia` (`id_agencia`);

--
-- Indices de la tabla `menus_local`
--
ALTER TABLE `menus_local`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_local` (`id_local`);

--
-- Indices de la tabla `pedidos_servicios`
--
ALTER TABLE `pedidos_servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_turista` (`id_turista`),
  ADD KEY `id_itinerario` (`id_itinerario`),
  ADD KEY `fk_id_destino` (`id_destino`);

--
-- Indices de la tabla `publicidades`
--
ALTER TABLE `publicidades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_itinerario` (`id_itinerario`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `servicios_agencia`
--
ALTER TABLE `servicios_agencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_agencia` (`id_agencia`);

--
-- Indices de la tabla `servicios_guia`
--
ALTER TABLE `servicios_guia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_guia` (`id_guia`);

--
-- Indices de la tabla `servicios_local`
--
ALTER TABLE `servicios_local`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_local` (`id_local`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `agencias`
--
ALTER TABLE `agencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `carouseles`
--
ALTER TABLE `carouseles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `descuentos`
--
ALTER TABLE `descuentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `destinos`
--
ALTER TABLE `destinos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `guias_destinos`
--
ALTER TABLE `guias_destinos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `guias_turisticos`
--
ALTER TABLE `guias_turisticos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `imagenes_destino`
--
ALTER TABLE `imagenes_destino`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `imagenes_guia`
--
ALTER TABLE `imagenes_guia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `imagenes_local`
--
ALTER TABLE `imagenes_local`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `itinerarios`
--
ALTER TABLE `itinerarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `itinerario_destinos`
--
ALTER TABLE `itinerario_destinos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `lugares_locales`
--
ALTER TABLE `lugares_locales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `menus_agencia`
--
ALTER TABLE `menus_agencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `menus_local`
--
ALTER TABLE `menus_local`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pedidos_servicios`
--
ALTER TABLE `pedidos_servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `publicidades`
--
ALTER TABLE `publicidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `servicios_agencia`
--
ALTER TABLE `servicios_agencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `servicios_guia`
--
ALTER TABLE `servicios_guia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `servicios_local`
--
ALTER TABLE `servicios_local`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `agencias`
--
ALTER TABLE `agencias`
  ADD CONSTRAINT `agencias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `descuentos`
--
ALTER TABLE `descuentos`
  ADD CONSTRAINT `descuentos_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agencias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `guias_destinos`
--
ALTER TABLE `guias_destinos`
  ADD CONSTRAINT `guias_destinos_ibfk_1` FOREIGN KEY (`id_guia`) REFERENCES `guias_turisticos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guias_destinos_ibfk_2` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `guias_turisticos`
--
ALTER TABLE `guias_turisticos`
  ADD CONSTRAINT `guias_turisticos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `imagenes_destino`
--
ALTER TABLE `imagenes_destino`
  ADD CONSTRAINT `imagenes_destino_ibfk_1` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `imagenes_guia`
--
ALTER TABLE `imagenes_guia`
  ADD CONSTRAINT `imagenes_guia_ibfk_1` FOREIGN KEY (`id_guia`) REFERENCES `guias_turisticos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `imagenes_local`
--
ALTER TABLE `imagenes_local`
  ADD CONSTRAINT `imagenes_local_ibfk_1` FOREIGN KEY (`id_local`) REFERENCES `lugares_locales` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `itinerarios`
--
ALTER TABLE `itinerarios`
  ADD CONSTRAINT `itinerarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `itinerario_destinos`
--
ALTER TABLE `itinerario_destinos`
  ADD CONSTRAINT `itinerario_destinos_ibfk_1` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `itinerario_destinos_ibfk_2` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lugares_locales`
--
ALTER TABLE `lugares_locales`
  ADD CONSTRAINT `lugares_locales_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensajes_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `menus_agencia`
--
ALTER TABLE `menus_agencia`
  ADD CONSTRAINT `menus_agencia_ibfk_1` FOREIGN KEY (`id_agencia`) REFERENCES `agencias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `menus_local`
--
ALTER TABLE `menus_local`
  ADD CONSTRAINT `menus_local_ibfk_1` FOREIGN KEY (`id_local`) REFERENCES `lugares_locales` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos_servicios`
--
ALTER TABLE `pedidos_servicios`
  ADD CONSTRAINT `fk_id_destino` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pedidos_servicios_ibfk_1` FOREIGN KEY (`id_turista`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedidos_servicios_ibfk_2` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `servicios_agencia`
--
ALTER TABLE `servicios_agencia`
  ADD CONSTRAINT `servicios_agencia_ibfk_1` FOREIGN KEY (`id_agencia`) REFERENCES `agencias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `servicios_guia`
--
ALTER TABLE `servicios_guia`
  ADD CONSTRAINT `servicios_guia_ibfk_1` FOREIGN KEY (`id_guia`) REFERENCES `guias_turisticos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `servicios_local`
--
ALTER TABLE `servicios_local`
  ADD CONSTRAINT `servicios_local_ibfk_1` FOREIGN KEY (`id_local`) REFERENCES `lugares_locales` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  ADD CONSTRAINT `valoraciones_ibfk_1` FOREIGN KEY (`reviewer_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
