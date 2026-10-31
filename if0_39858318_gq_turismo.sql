-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql107.infinityfree.com
-- Generation Time: Oct 30, 2026 at 10:37 PM
-- Server version: 11.4.13-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_39858318_gq_turismo`
--

-- --------------------------------------------------------

--
-- Table structure for table `agencias`
--

CREATE TABLE `agencias` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_agencia` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `contacto_email` varchar(255) DEFAULT NULL,
  `contacto_telefono` varchar(50) DEFAULT NULL,
  `imagen_perfil` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agencias`
--

INSERT INTO `agencias` (`id`, `id_usuario`, `nombre_agencia`, `descripcion`, `contacto_email`, `contacto_telefono`, `imagen_perfil`, `fecha_registro`, `latitude`, `longitude`) VALUES
(4, 18, 'Agencia Viajes', 'Creamos experiencias de viaje inolvidables, organizando cada detalle para que tú solo te preocupes por disfrutar. Nos especializamos en diseñar itinerarios personalizados, desde vuelos y hoteles hasta actividades únicas, adaptándonos a tus sueños y presupuesto. Confía en nosotros para planificar tu próxima aventura, con la seguridad de que cada aspecto de tu viaje estará perfectamente cuidado', 'agencia@vuelos', '+233208870387', '68f97778e1ecb_images.jpeg', '2025-10-22 16:22:12', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `carouseles`
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
-- Dumping data for table `carouseles`
--

INSERT INTO `carouseles` (`id`, `nombre`, `ruta_imagen`, `enlace`, `orden`, `activo`, `fecha_creacion`) VALUES
(6, 'Catedral De Malabo', '68f909cd91c37_images (6).jpeg', '', 0, 1, '2025-10-22 16:43:57'),
(7, 'Ciudad de malabo', '68f90a347eaf0_Malabo_tam_fijo_final.jpg', '', 0, 1, '2025-10-22 16:45:40'),
(8, 'Ciudad de malabo', '68f90aa6c5709_Malabo_tam_fijo_final.jpg', '', 0, 1, '2025-10-22 16:47:34');

-- --------------------------------------------------------

--
-- Table structure for table `confirmaciones_servicios`
--

CREATE TABLE `confirmaciones_servicios` (
  `id` int(11) NOT NULL,
  `id_pedido_servicio` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `tipo_proveedor` enum('agencia','guia','local') NOT NULL,
  `estado_confirmacion` enum('pendiente','confirmado','rechazado','completado') DEFAULT 'pendiente',
  `fecha_confirmacion` datetime DEFAULT NULL,
  `fecha_servicio` datetime DEFAULT NULL,
  `notas_proveedor` text DEFAULT NULL,
  `valoracion` int(1) DEFAULT NULL,
  `comentario_valoracion` text DEFAULT NULL,
  `fecha_valoracion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `confirmacion_servicios`
--

CREATE TABLE `confirmacion_servicios` (
  `id` int(11) NOT NULL,
  `id_pedido_servicio` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `tipo_proveedor` enum('agencia','guia','local') NOT NULL,
  `estado` enum('pendiente','confirmado','rechazado','completado') DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `fecha_confirmacion` datetime DEFAULT NULL,
  `fecha_completado` datetime DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `descuentos`
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

--
-- Dumping data for table `descuentos`
--

INSERT INTO `descuentos` (`id`, `agency_id`, `discount_code`, `percentage`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
(1, 4, 'AGENCIA', '10.00', '2025-10-20', '2025-10-26', 1, '2025-10-22 16:26:26');

-- --------------------------------------------------------

--
-- Table structure for table `destinos`
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

--
-- Dumping data for table `destinos`
--

INSERT INTO `destinos` (`id`, `nombre`, `descripcion`, `categoria`, `imagen`, `precio`, `latitude`, `longitude`, `ciudad`) VALUES
(16, 'Cascada de Moka', 'Ubicada en el corazón de la isla de Bioko, la cascada de Moca emerge como una joya escondida en medio de la densa y exuberante selva ecuatoguineana\r\n. Su belleza natural, virgen y misteriosa, invita a los aventureros a adentrarse en un paisaje de ensueño.', 'Naturaleza', '68fa17130f235_caption.jpg', '30.00', '3.34570310', '8.66555920', 'Luba'),
(17, 'Playa de Ureka', 'La playa de Ureca, ubicada en el remoto sur de la isla de Bioko, en Guinea Ecuatorial, es un destino singular y místico donde la naturaleza despliega toda su fuerza y exuberancia\r\n. Es una playa de arena negra, de origen volcánico, enmarcada por una selva virgen que se extiende hasta la orilla del mar.', 'Naturaleza', '69398fe607a0f_Imagen de WhatsApp 2025-12-10 a las 10.22.30_f3183a86.jpg', '20.00', '3.24863800', '8.56598100', 'Luba'),
(18, 'Catedral de Malabo', 'La catedral de Malabo, es la iglesia católica principal de Guinea Ecuatorial, construida en estilo neogótico entre 1897 y 1916. Es un edificio histórico notable por su imponente fachada de dos torres de 40 metros y su cuerpo de tres naves. Actualmente es la sede de la archidiócesis de Malabo.', 'Histórico ', '68fa7c01970fb_catedral-de-santa-isabel.jpg', '2.00', '3.75704371', '8.78274141', 'Malabo'),
(19, 'Parque Nacional del Pico Basilé', 'El parque Nacional del Pico Basilé se encuentra en la montaña más alta de Guinea Ecuatorial; el pico Basilé, localizada en la isla de Bioko. Cercana a la ciudad capital Malabo, con una altitud de 3011 metros (9878 pies) sobre el nivel del mar.', 'Naturaleza-Histórico ', '693c09eb64063_IMG-20251210-WA0009.jpg', '30.00', '3.58333333', '8.76666667', 'Malabo'),
(20, 'Restaurante-Finca El Rancho ', 'Localizada en Malabo, es un sitio conocido por su restaurante de estilo campestre y sus animales (caballos, aves, reptiles, etc).', 'Naturaleza', '693fce6a46d2d_IMG_8906.JPG', '20.00', '3.70004700', '8.76998900', 'Malabo'),
(21, 'Hacienda Marcos Obiang Nguema Nsue', 'El nombre actual de la hacienda es en honor a un agricultor local, anteriormente conocida como finca Muñoz. Situada en la localidad de Riaba, es el lugar perfecto para el turismo y para una experiencia inolvidable.', 'Naturaleza', '693fcdb40ed59_Imagen de WhatsApp 2025-12-10 a las 10.22.23_b4e2b53e.jpg', '50.00', '3.41692800', '8.75094500', 'Riaba');

-- --------------------------------------------------------

--
-- Table structure for table `guias_destinos`
--

CREATE TABLE `guias_destinos` (
  `id` int(11) NOT NULL,
  `id_guia` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guias_turisticos`
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
  `imagen_perfil` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `current_latitude` decimal(10,8) DEFAULT NULL,
  `current_longitude` decimal(11,8) DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  `ciudad_operacion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guias_turisticos`
--

INSERT INTO `guias_turisticos` (`id`, `id_usuario`, `nombre_guia`, `descripcion`, `especialidades`, `precio_hora`, `contacto_email`, `contacto_telefono`, `imagen_perfil`, `fecha_registro`, `current_latitude`, `current_longitude`, `last_updated`, `ciudad_operacion`, `ciudad`) VALUES
(4, 17, 'Guia', 'Descubre la Guinea Ecuatorial: una joya africana con exuberantes selvas, playas vírgenes y una cultura vibrante, donde la naturaleza y la historia se entrelazan. Explora la capital, Malabo, con su arquitectura colonial, o adéntrate en la selva para encontrar cascadas y una fauna única. Como tu guía local, te llevaré a conocer lo mejor de esta nación, desde la vida en sus ciudades hasta la riqueza de sus tradiciones culturales', 'Visitas a lugares remotos, Playas Exoticas, Aventuras Locas', '20.00', 'guia@guia.com', '+233208870387', 'guia_1.jpeg', '2025-10-22 16:15:00', '5.60000000', '-0.22000000', '2025-10-22 16:16:34', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `guia_destinos`
--

CREATE TABLE `guia_destinos` (
  `id` int(11) NOT NULL,
  `id_guia` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `experiencia_anos` int(11) DEFAULT 0,
  `descripcion` text DEFAULT NULL,
  `certificaciones` text DEFAULT NULL,
  `idiomas` varchar(255) DEFAULT NULL,
  `tarifa_dia` decimal(10,2) DEFAULT 0.00,
  `disponible` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guia_destinos_disponibles`
--

CREATE TABLE `guia_destinos_disponibles` (
  `id` int(11) NOT NULL,
  `id_guia` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `precio_base` decimal(10,2) DEFAULT 0.00,
  `disponible` tinyint(1) DEFAULT 1,
  `notas` text DEFAULT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `imagenes_destino`
--

CREATE TABLE `imagenes_destino` (
  `id` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `imagenes_destino`
--

INSERT INTO `imagenes_destino` (`id`, `id_destino`, `ruta_imagen`, `descripcion`, `fecha_subida`) VALUES
(5, 16, '68fa14a923e6c_cascadas_moca.jpg', 'Vista Espectacular', '2025-10-23 11:42:33'),
(6, 17, '69399087df5f4_Imagen de WhatsApp 2025-12-10 a las 10.19.30_951558e6.jpg', '', '2025-12-10 07:23:51'),
(7, 17, '6939910aa2ad2_Imagen de WhatsApp 2025-12-10 a las 10.20.42_174d0c8d.jpg', 'Día despejado y soleado en la playa de Ureka en todo su esplendor', '2025-12-10 07:26:02'),
(8, 17, '6939933c27736_Imagen de WhatsApp 2025-12-10 a las 10.22.27_94b90741.jpg', 'Cascada de Ureka, espectacular fenómeno natural, resultado de las abundantes aguas de lluvia de la isla de Bioko', '2025-12-10 07:35:24'),
(9, 17, '69399389893b7_Imagen de WhatsApp 2025-12-10 a las 10.22.25_ca735163.jpg', '', '2025-12-10 07:36:41'),
(10, 19, '693c09b0efcb9_94922e55-c5aa-41b9-ba62-d59d30b05e373ffa00cde21b51d012_Pico Basile Prohibido Cazar.jpg', 'En el parque Nacional del Pico Basilé existen zonas protegidas, donde la naturaleza se puede apreciar en su forma más pura.', '2025-12-12 04:25:20'),
(11, 19, '693c715f96815_Screenshot_20251212-203418_1.png', '', '2025-12-12 11:47:43'),
(13, 19, '693c74aada108_Screenshot_20251212-203340_1.png', 'La Virgen de Bisila es la patrona de la Isla de Bioko, una advocación mariana que fusiona la fe católica con la tradición local Bubi.', '2025-12-12 12:01:46'),
(14, 19, '693c74cadfa42_Screenshot_20251212-203430_1.png', '', '2025-12-12 12:02:18'),
(15, 19, '693c74ddee679_Screenshot_20251212-203439_1.png', '', '2025-12-12 12:02:37'),
(16, 19, '693c752b891c5_Screenshot_20251212-203445_1.png', '', '2025-12-12 12:03:55'),
(17, 20, '693d8c82a2e6c_unnamed (3).webp', '', '2025-12-13 07:55:46'),
(18, 20, '693d8c9868c5a_unnamed (1).webp', '', '2025-12-13 07:56:08'),
(19, 20, '693d8cb4cc62c_unnamed (2).webp', '', '2025-12-13 07:56:36'),
(20, 20, '693d8cc510457_unnamed (5).webp', '', '2025-12-13 07:56:53'),
(21, 20, '693d8ce3d98ab_unnamed (4).webp', '', '2025-12-13 07:57:23'),
(22, 20, '693d8cf709bb1_unnamed (7).webp', '', '2025-12-13 07:57:43'),
(23, 20, '693d8d1081162_unnamed (9).webp', '', '2025-12-13 07:58:08'),
(24, 20, '693d8d2d5ff30_unnamed (6).webp', '', '2025-12-13 07:58:37'),
(25, 20, '693d8d6526dcb_unnamed (8).webp', '', '2025-12-13 07:59:33'),
(26, 21, '693d9221a97b4_Imagen de WhatsApp 2025-12-10 a las 10.22.18_67f9da52.jpg', '', '2025-12-13 08:19:45'),
(27, 21, '693d92502790c_Imagen de WhatsApp 2025-12-10 a las 10.22.21_b7b98fed.jpg', '', '2025-12-13 08:20:32'),
(28, 21, '693d926fa16af_Imagen de WhatsApp 2025-12-10 a las 10.22.23_b4e2b53e.jpg', '', '2025-12-13 08:21:03'),
(29, 21, '693d92c701abc_IMG_9778.JPG', '', '2025-12-13 08:22:31'),
(30, 21, '693d9331442d1_IMG_9713.JPG', '', '2025-12-13 08:24:17'),
(31, 21, '693d936677a81_IMG_9714.JPG', '', '2025-12-13 08:25:10'),
(32, 20, '693fce04357a7_unnamed (11).webp', '', '2025-12-15 00:59:48');

-- --------------------------------------------------------

--
-- Table structure for table `imagenes_guia`
--

CREATE TABLE `imagenes_guia` (
  `id` int(11) NOT NULL,
  `id_guia` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `imagenes_guia`
--

INSERT INTO `imagenes_guia` (`id`, `id_guia`, `ruta_imagen`, `descripcion`, `fecha_subida`) VALUES
(5, 4, '68f9113f934ba_68f81bf9a0b93_guia_selva2.jpeg', 'Yo', '2025-10-22 17:15:43');

-- --------------------------------------------------------

--
-- Table structure for table `imagenes_local`
--

CREATE TABLE `imagenes_local` (
  `id` int(11) NOT NULL,
  `id_local` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `imagenes_local`
--

INSERT INTO `imagenes_local` (`id`, `id_local`, `ruta_imagen`, `descripcion`, `fecha_subida`) VALUES
(4, 4, '68fa971852c81_images (7).jpeg', 'Fiestas', '2025-10-23 20:59:04');

-- --------------------------------------------------------

--
-- Table structure for table `itinerarios`
--

CREATE TABLE `itinerarios` (
  `id` int(11) NOT NULL,
  `id_turista` int(11) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_itinerario` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ciudad` varchar(255) DEFAULT NULL,
  `alojamiento_id` int(11) DEFAULT NULL,
  `guia_id` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `presupuesto_estimado` decimal(10,2) DEFAULT 0.00,
  `notas` text DEFAULT NULL,
  `estado` enum('planificacion','confirmado','completado') DEFAULT 'planificacion'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `itinerarios`
--

INSERT INTO `itinerarios` (`id`, `id_turista`, `id_usuario`, `nombre_itinerario`, `descripcion`, `precio_total`, `fecha_creacion`, `ciudad`, `alojamiento_id`, `guia_id`, `fecha_inicio`, `fecha_fin`, `presupuesto_estimado`, `notas`, `estado`) VALUES
(12, 16, 16, 'Africa', NULL, '20.00', '2025-10-23 15:15:12', 'Luba', NULL, NULL, '2025-10-23', '2025-10-31', '100.00', 'Flhfjf', 'completado'),
(13, 16, 16, 'Noche Loca', NULL, '52.00', '2025-10-23 18:42:41', 'Luba', NULL, NULL, '2025-10-23', '2025-10-28', '300.00', 'ytuyhj', 'confirmado'),
(14, 16, 16, 'Hy', NULL, '52.00', '2025-10-23 21:05:33', 'Malabo', NULL, NULL, '2025-10-23', '2025-10-31', '50.00', 'Gh', 'confirmado'),
(15, NULL, 22, 'Luna de miel', NULL, '50.00', '2025-10-31 10:57:11', 'Malabo', NULL, NULL, '2025-10-31', '2025-10-31', '50.00', '', 'planificacion'),
(16, NULL, 22, 'Mi aventura', NULL, '32.00', '2025-11-10 18:13:57', 'Bata', NULL, NULL, '2025-11-10', '2025-11-10', '20.00', '', 'planificacion'),
(17, NULL, 22, 'Mi aventura 1', NULL, '32.00', '2025-12-01 17:07:38', 'Malabo', NULL, NULL, '2025-12-01', '2025-12-01', '50.00', 'Vuelta a la isla ', 'planificacion'),
(18, NULL, 16, 'Aventure ', NULL, '132.00', '2025-12-28 12:32:01', 'Malabo.', NULL, NULL, '2025-12-28', '2025-12-31', '300.00', 'Xvh', 'confirmado');

-- --------------------------------------------------------

--
-- Table structure for table `itinerario_agencias`
--

CREATE TABLE `itinerario_agencias` (
  `id` int(11) NOT NULL,
  `id_itinerario` int(11) NOT NULL,
  `id_agencia` int(11) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','confirmado','completado','cancelado') DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `fecha_confirmacion` datetime DEFAULT NULL,
  `fecha_completado` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `itinerario_destinos`
--

CREATE TABLE `itinerario_destinos` (
  `id` int(11) NOT NULL,
  `id_itinerario` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `orden` int(11) NOT NULL,
  `precio` decimal(10,2) DEFAULT 0.00,
  `estado` enum('pendiente','en_progreso','completado','cancelado') DEFAULT 'pendiente',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `itinerario_destinos`
--

INSERT INTO `itinerario_destinos` (`id`, `id_itinerario`, `id_destino`, `orden`, `precio`, `estado`, `fecha_inicio`, `fecha_fin`, `descripcion`) VALUES
(19, 12, 17, 1, '0.00', 'completado', NULL, NULL, NULL),
(21, 13, 17, 1, '0.00', 'completado', NULL, NULL, NULL),
(22, 13, 18, 2, '0.00', 'completado', NULL, NULL, NULL),
(23, 13, 16, 3, '0.00', 'completado', NULL, NULL, NULL),
(24, 14, 16, 1, '0.00', 'completado', NULL, NULL, NULL),
(25, 14, 18, 2, '0.00', 'completado', NULL, NULL, NULL),
(26, 14, 17, 3, '0.00', 'completado', NULL, NULL, NULL),
(27, 15, 16, 1, '0.00', 'pendiente', NULL, NULL, NULL),
(28, 15, 17, 2, '0.00', 'pendiente', NULL, NULL, NULL),
(29, 16, 18, 1, '0.00', 'pendiente', NULL, NULL, NULL),
(30, 16, 16, 2, '0.00', 'pendiente', NULL, NULL, NULL),
(31, 17, 18, 1, '0.00', 'pendiente', NULL, NULL, NULL),
(32, 17, 16, 2, '0.00', 'pendiente', NULL, NULL, NULL),
(38, 18, 16, 1, '0.00', 'completado', NULL, NULL, NULL),
(39, 18, 18, 2, '0.00', 'completado', NULL, NULL, NULL),
(40, 18, 21, 3, '0.00', 'completado', NULL, NULL, NULL),
(41, 18, 19, 4, '0.00', 'completado', NULL, NULL, NULL),
(42, 18, 17, 5, '0.00', 'pendiente', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `itinerario_guias`
--

CREATE TABLE `itinerario_guias` (
  `id` int(11) NOT NULL,
  `id_itinerario` int(11) NOT NULL,
  `id_guia` int(11) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','confirmado','completado','cancelado') DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `fecha_confirmacion` datetime DEFAULT NULL,
  `fecha_completado` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `itinerario_locales`
--

CREATE TABLE `itinerario_locales` (
  `id` int(11) NOT NULL,
  `id_itinerario` int(11) NOT NULL,
  `id_local` int(11) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','confirmado','completado','cancelado') DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `fecha_confirmacion` datetime DEFAULT NULL,
  `fecha_completado` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `itinerario_servicios`
--

CREATE TABLE `itinerario_servicios` (
  `id` int(11) NOT NULL,
  `id_itinerario` int(11) NOT NULL,
  `tipo_servicio` enum('guia','agencia','local') NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `notas` text DEFAULT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `itinerario_servicios`
--

INSERT INTO `itinerario_servicios` (`id`, `id_itinerario`, `tipo_servicio`, `id_servicio`, `notas`, `fecha_agregado`) VALUES
(13, 13, 'guia', 4, NULL, '2025-10-23 19:32:47'),
(14, 13, 'agencia', 4, NULL, '2025-10-23 19:32:47'),
(15, 13, 'local', 3, NULL, '2025-10-23 19:32:47'),
(16, 14, 'guia', 4, NULL, '2025-10-23 21:05:33'),
(17, 14, 'agencia', 4, NULL, '2025-10-23 21:05:34'),
(18, 14, 'local', 3, NULL, '2025-10-23 21:05:34'),
(19, 14, 'local', 4, NULL, '2025-10-23 21:05:34'),
(20, 15, 'guia', 4, NULL, '2025-10-31 10:57:11'),
(21, 16, 'guia', 4, NULL, '2025-11-10 18:13:57'),
(22, 16, 'local', 3, NULL, '2025-11-10 18:13:57'),
(23, 17, 'guia', 4, NULL, '2025-12-01 17:07:38'),
(28, 18, 'guia', 4, NULL, '2025-12-28 12:34:01'),
(29, 18, 'agencia', 4, NULL, '2025-12-28 12:34:01'),
(30, 18, 'local', 3, NULL, '2025-12-28 12:34:01'),
(31, 18, 'local', 4, NULL, '2025-12-28 12:34:01');

-- --------------------------------------------------------

--
-- Table structure for table `itinerario_tareas`
--

CREATE TABLE `itinerario_tareas` (
  `id` int(11) NOT NULL,
  `id_itinerario` int(11) NOT NULL,
  `id_destino` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_tarea` enum('transporte','alojamiento','actividad','comida','guia','otro') DEFAULT 'actividad',
  `fecha_hora_inicio` datetime DEFAULT NULL,
  `fecha_hora_fin` datetime DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `estado` enum('pendiente','en_progreso','completado','cancelado') DEFAULT 'pendiente',
  `prioridad` enum('baja','media','alta') DEFAULT 'media',
  `id_proveedor` int(11) DEFAULT NULL,
  `tipo_proveedor` enum('agencia','guia','local') DEFAULT NULL,
  `creado_por` int(11) NOT NULL,
  `completado_por` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_completado` datetime DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `ubicacion_inicio` varchar(255) DEFAULT NULL,
  `ubicacion_fin` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locales_turisticos`
--

CREATE TABLE `locales_turisticos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `direccion` varchar(500) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `sitio_web` varchar(500) DEFAULT NULL,
  `tipo_local` varchar(50) DEFAULT NULL,
  `horario` text DEFAULT NULL,
  `calificacion` decimal(3,2) DEFAULT 0.00,
  `imagen_principal` varchar(500) DEFAULT NULL,
  `estado` enum('activo','inactivo','pendiente') DEFAULT 'activo',
  `verificado` tinyint(1) DEFAULT 0,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lugares_locales`
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
  `imagen_perfil` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `ciudad` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lugares_locales`
--

INSERT INTO `lugares_locales` (`id`, `id_usuario`, `nombre_local`, `descripcion`, `tipo_local`, `direccion`, `contacto_email`, `contacto_telefono`, `imagen_perfil`, `fecha_registro`, `latitude`, `longitude`, `ciudad`) VALUES
(3, 19, 'Hotel', 'Vista creada con IA\r\nSumérjase en la comodidad y la sofisticación en nuestro hotel en Guinea, donde podrá disfrutar de modernas habitaciones con aire acondicionado, Wi-Fi gratuito y minibar. Relájese en la piscina al aire libre, disfrute de gastronomía internacional o mantenga la productividad en nuestras salas de reuniones. Estamos convenientemente ubicados cerca del aeropuerto, de atracciones culturales y centros de negocios, con aparcamiento privado gratuito disponible para su comodidad', 'Hotel', 'Avenida De La idependencia', 'hotel@local.com', '+233208870387', '68f9589092c64_e0be8b9d_y.webp', '2025-10-22 22:18:10', NULL, NULL, NULL),
(4, 20, 'Viva El Rollo', 'es la discoteca donde Malabo vibra más fuerte. Un lugar lleno de ritmo, luces y energía tropical, donde la música no duerme y cada noche se convierte en una fiesta legendaria. Aquí se mezclan culturas, estilos y emociones — porque en Viva el Rollo, la vida es para bailarla.', 'Discoteca', 'Malabo/banapa', 'viva@club.com', '+233208870387', '68fa9703c67fa_images (8).jpeg', '2025-10-23 20:56:29', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('turista','agencia','guia','local') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_type` enum('turista','agencia','guia','local') NOT NULL,
  `message` text NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `id_emisor` int(11) DEFAULT NULL,
  `id_receptor` int(11) DEFAULT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha_lectura` datetime DEFAULT NULL,
  `tipo_mensaje` varchar(50) DEFAULT 'texto',
  `archivo_adjunto` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mensajes`
--

INSERT INTO `mensajes` (`id`, `sender_id`, `sender_type`, `receiver_id`, `receiver_type`, `message`, `timestamp`, `is_read`, `id_emisor`, `id_receptor`, `leido`, `fecha_lectura`, `tipo_mensaje`, `archivo_adjunto`) VALUES
(1, 16, 'turista', 3, 'local', 'Hola', '2025-10-23 06:13:42', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(2, 16, 'turista', 3, 'local', 'hola', '2025-10-23 06:16:40', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(3, 16, 'turista', 3, 'local', 'Hola', '2025-10-23 11:23:17', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(4, 16, 'turista', 3, 'local', 'Hola', '2025-10-23 14:56:37', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(5, 16, 'turista', 3, 'local', 'Ktal', '2025-10-23 14:56:41', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(6, 16, 'turista', 4, 'guia', 'Nueva reserva para el itinerario \'Africa\'\n\nTurista: Juan\nFecha reserva: 2025-10-23\nPersonas: 1\nComentarios: Hrhrj\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-10-23 15:16:04', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(7, 16, 'turista', 4, 'agencia', 'Nueva reserva para el itinerario \'Africa\'\n\nTurista: Juan\nFecha reserva: 2025-10-23\nPersonas: 1\nComentarios: Hrhrj\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-10-23 15:16:04', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(8, 16, 'turista', 3, 'local', 'Nueva reserva para el itinerario \'Africa\'\n\nTurista: Juan\nFecha reserva: 2025-10-23\nPersonas: 1\nComentarios: Hrhrj\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-10-23 15:16:04', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(9, 16, 'turista', 3, 'local', 'Stop', '2025-10-23 15:19:50', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(10, 16, 'turista', 4, 'guia', 'Nueva reserva para el itinerario \'Africa\'\n\nTurista: Juan\nFecha reserva: 2025-10-23\nPersonas: 1\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-10-23 16:58:54', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(11, 16, 'turista', 4, 'agencia', 'Nueva reserva para el itinerario \'Africa\'\n\nTurista: Juan\nFecha reserva: 2025-10-23\nPersonas: 1\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-10-23 16:58:54', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(12, 16, 'turista', 3, 'local', 'Nueva reserva para el itinerario \'Africa\'\n\nTurista: Juan\nFecha reserva: 2025-10-23\nPersonas: 1\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-10-23 16:58:54', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(13, 16, 'turista', 4, 'guia', 'Hola', '2025-10-23 18:04:39', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(14, 16, 'turista', 4, 'guia', 'Nueva reserva para el itinerario \'Noche Loca\'\n\nTurista: Juan\nFecha reserva: 2025-10-23\nPersonas: 1\nComentarios: fghjk\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-10-23 19:27:49', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(15, 16, 'turista', 4, 'agencia', 'Nueva reserva para el itinerario \'Noche Loca\'\n\nTurista: Juan\nFecha reserva: 2025-10-23\nPersonas: 1\nComentarios: fghjk\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-10-23 19:27:49', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(16, 16, 'turista', 3, 'local', 'Nueva reserva para el itinerario \'Noche Loca\'\n\nTurista: Juan\nFecha reserva: 2025-10-23\nPersonas: 1\nComentarios: fghjk\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-10-23 19:27:49', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(17, 16, 'turista', 4, 'guia', 'Nueva reserva para el itinerario \'Hy\'\n\nTurista: Juan\nFecha reserva: 2025-10-23\nPersonas: 1\nComentarios: Gggh\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-10-23 21:05:53', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(18, 16, 'turista', 4, 'agencia', 'Nueva reserva para el itinerario \'Hy\'\n\nTurista: Juan\nFecha reserva: 2025-10-23\nPersonas: 1\nComentarios: Gggh\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-10-23 21:05:53', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(19, 16, 'turista', 3, 'local', 'Nueva reserva para el itinerario \'Hy\'\n\nTurista: Juan\nFecha reserva: 2025-10-23\nPersonas: 1\nComentarios: Gggh\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-10-23 21:05:53', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(20, 16, 'turista', 4, 'local', 'Nueva reserva para el itinerario \'Hy\'\n\nTurista: Juan\nFecha reserva: 2025-10-23\nPersonas: 1\nComentarios: Gggh\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-10-23 21:05:53', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(21, 16, 'turista', 4, 'agencia', 'hola porque tan carro', '2025-10-24 02:28:46', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(22, 16, 'turista', 4, 'guia', 'Nueva reserva para el itinerario \'Aventure \'\n\nTurista: Juan\nFecha reserva: 2025-12-28\nPersonas: 1\nComentarios: Hdj\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-12-28 04:33:09', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(23, 16, 'turista', 4, 'agencia', 'Nueva reserva para el itinerario \'Aventure \'\n\nTurista: Juan\nFecha reserva: 2025-12-28\nPersonas: 1\nComentarios: Hdj\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-12-28 04:33:09', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(24, 16, 'turista', 3, 'local', 'Nueva reserva para el itinerario \'Aventure \'\n\nTurista: Juan\nFecha reserva: 2025-12-28\nPersonas: 1\nComentarios: Hdj\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-12-28 04:33:09', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(25, 16, 'turista', 4, 'local', 'Nueva reserva para el itinerario \'Aventure \'\n\nTurista: Juan\nFecha reserva: 2025-12-28\nPersonas: 1\nComentarios: Hdj\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-12-28 04:33:09', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(26, 16, 'turista', 4, 'guia', 'Nueva reserva para el itinerario \'Aventure \'\n\nTurista: Juan\nFecha reserva: 2025-12-28\nPersonas: 1\nComentarios: Hdj\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-12-28 04:33:22', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(27, 16, 'turista', 4, 'agencia', 'Nueva reserva para el itinerario \'Aventure \'\n\nTurista: Juan\nFecha reserva: 2025-12-28\nPersonas: 1\nComentarios: Hdj\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-12-28 04:33:22', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(28, 16, 'turista', 3, 'local', 'Nueva reserva para el itinerario \'Aventure \'\n\nTurista: Juan\nFecha reserva: 2025-12-28\nPersonas: 1\nComentarios: Hdj\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-12-28 04:33:22', 0, NULL, NULL, 0, NULL, 'texto', NULL),
(29, 16, 'turista', 4, 'local', 'Nueva reserva para el itinerario \'Aventure \'\n\nTurista: Juan\nFecha reserva: 2025-12-28\nPersonas: 1\nComentarios: Hdj\n\nPor favor, contacta al turista para confirmar el servicio.', '2025-12-28 04:33:22', 0, NULL, NULL, 0, NULL, 'texto', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menus_agencia`
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
-- Table structure for table `menus_local`
--

CREATE TABLE `menus_local` (
  `id` int(11) NOT NULL,
  `id_local` int(11) NOT NULL,
  `nombre_menu` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menus_local`
--

INSERT INTO `menus_local` (`id`, `id_local`, `nombre_menu`, `descripcion`, `precio_total`, `fecha_creacion`) VALUES
(5, 4, 'Combo Bebidas ', 'Un cubo de 7 cervezas Corona', '50.00', '2025-10-23 21:00:11');

-- --------------------------------------------------------

--
-- Table structure for table `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('itinerario','reserva','mensaje','confirmacion','sistema') NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_reserva` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','procesando','completado','cancelado') DEFAULT 'pendiente',
  `metodo_pago` varchar(50) DEFAULT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pedidos_servicios`
--

CREATE TABLE `pedidos_servicios` (
  `id` int(11) NOT NULL,
  `id_turista` int(11) NOT NULL,
  `tipo_proveedor` enum('agencia','guia','local') NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `id_servicio_o_menu` int(11) NOT NULL,
  `tipo_item` enum('servicio','menu') NOT NULL,
  `nombre_servicio` varchar(255) DEFAULT NULL,
  `id_itinerario` int(11) DEFAULT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_servicio` date DEFAULT NULL,
  `cantidad_personas` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','confirmado','cancelado','completado','pagado') NOT NULL DEFAULT 'pendiente',
  `id_destino` int(11) DEFAULT NULL,
  `confirmado_proveedor` tinyint(1) DEFAULT 0,
  `fecha_confirmacion` datetime DEFAULT NULL,
  `notas_proveedor` text DEFAULT NULL,
  `hora_servicio` time DEFAULT NULL,
  `numero_personas` int(11) DEFAULT 1,
  `notas_especiales` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pedidos_servicios`
--

INSERT INTO `pedidos_servicios` (`id`, `id_turista`, `tipo_proveedor`, `id_proveedor`, `id_servicio_o_menu`, `tipo_item`, `nombre_servicio`, `id_itinerario`, `fecha_solicitud`, `fecha_servicio`, `cantidad_personas`, `precio_unitario`, `precio_total`, `estado`, `id_destino`, `confirmado_proveedor`, `fecha_confirmacion`, `notas_proveedor`, `hora_servicio`, `numero_personas`, `notas_especiales`) VALUES
(8, 16, 'agencia', 4, 4, 'servicio', 'Ticket Primera Clase', NULL, '2025-10-23 02:15:08', '2025-10-23', 1, '300.00', '300.00', 'completado', NULL, 0, NULL, NULL, NULL, 1, NULL),
(9, 16, 'guia', 4, 4, 'servicio', 'Visita a Ureka', NULL, '2025-10-23 03:02:22', '2025-10-23', 1, '30.00', '30.00', 'completado', NULL, 0, NULL, NULL, NULL, 1, NULL),
(10, 16, 'local', 3, 4, 'servicio', 'Alojamiento Comodo', NULL, '2025-10-23 03:03:12', '2025-10-23', 1, '40.00', '40.00', 'completado', NULL, 0, NULL, NULL, NULL, 1, NULL),
(11, 16, 'agencia', 4, 4, 'servicio', NULL, NULL, '2025-10-23 05:38:16', '2025-10-23', 1, '300.00', '300.00', 'completado', NULL, 0, NULL, NULL, NULL, 1, NULL),
(12, 16, 'guia', 4, 4, 'servicio', NULL, NULL, '2025-10-23 15:20:59', '2025-10-23', 1, '30.00', '30.00', 'completado', NULL, 0, NULL, NULL, NULL, 1, NULL),
(13, 16, 'guia', 4, 4, 'servicio', NULL, NULL, '2025-10-23 17:16:29', '2025-10-23', 1, '30.00', '30.00', 'completado', NULL, 0, NULL, NULL, NULL, 1, NULL),
(14, 16, 'guia', 4, 4, 'servicio', NULL, NULL, '2025-10-26 18:01:31', '2025-10-26', 50, '30.00', '1500.00', 'completado', NULL, 0, NULL, NULL, NULL, 1, NULL),
(15, 16, 'guia', 4, 4, 'servicio', NULL, NULL, '2025-10-29 00:15:50', '2025-10-29', 1, '30.00', '30.00', 'completado', NULL, 0, NULL, NULL, NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `publicidades`
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
-- Dumping data for table `publicidades`
--

INSERT INTO `publicidades` (`id`, `titulo`, `descripcion`, `imagen`, `enlace`, `fecha_inicio`, `fecha_fin`, `activo`, `fecha_creacion`) VALUES
(4, 'Ábreme Modjobuy Envios', 'es una empresa dedicada al envío rápido y seguro de paquetes desde Ghana hacia Guinea Ecuatorial. Confiable, accesible y cercana a la comunidad, conecta familias y negocios africanos, ofreciendo un servicio que combina eficiencia, confianza y atención personalizada.', '68fa927dbcb48_kuu.png', 'http://192.168.137.1/abeme-modjobuy/', '2025-10-23', '2025-10-25', 1, '2025-10-23 20:39:25');

-- --------------------------------------------------------

--
-- Table structure for table `publicidad_carousel`
--

CREATE TABLE `publicidad_carousel` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(500) DEFAULT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservas`
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

--
-- Dumping data for table `reservas`
--

INSERT INTO `reservas` (`id`, `id_itinerario`, `id_usuario`, `fecha_reserva`, `personas`, `metodo_pago`, `total_precio`, `estado`) VALUES
(2, 12, 16, '2025-10-23', 1, 'efectivo', '20.00', 'pendiente'),
(3, 12, 16, '2025-10-23', 1, 'efectivo', '20.00', 'pendiente'),
(4, 13, 16, '2025-10-23', 1, 'efectivo', '20.00', 'pendiente'),
(5, 14, 16, '2025-10-23', 1, 'efectivo', '52.00', 'pendiente'),
(6, 18, 16, '2025-12-28', 1, 'efectivo', '132.00', 'pendiente'),
(7, 18, 16, '2025-12-28', 1, 'efectivo', '132.00', 'pendiente');

-- --------------------------------------------------------

--
-- Table structure for table `reserva_servicios`
--

CREATE TABLE `reserva_servicios` (
  `id` int(11) NOT NULL,
  `id_reserva` int(11) NOT NULL,
  `tipo_servicio` enum('guia','agencia','local','destino') NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `estado` enum('pendiente','confirmado','cancelado') DEFAULT 'pendiente',
  `fecha_servicio` date DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT 0.00,
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reserva_servicios`
--

INSERT INTO `reserva_servicios` (`id`, `id_reserva`, `tipo_servicio`, `id_servicio`, `estado`, `fecha_servicio`, `precio`, `notas`, `fecha_creacion`) VALUES
(3, 2, 'destino', 17, 'pendiente', NULL, '20.00', NULL, '2025-10-23 15:16:04'),
(4, 2, 'guia', 4, 'pendiente', NULL, '0.00', NULL, '2025-10-23 15:16:04'),
(5, 2, 'agencia', 4, 'pendiente', NULL, '0.00', NULL, '2025-10-23 15:16:04'),
(6, 2, 'local', 3, 'pendiente', NULL, '0.00', NULL, '2025-10-23 15:16:04'),
(7, 3, 'destino', 17, 'pendiente', NULL, '20.00', NULL, '2025-10-23 16:58:49'),
(8, 3, 'guia', 4, 'pendiente', NULL, '0.00', NULL, '2025-10-23 16:58:54'),
(9, 3, 'agencia', 4, 'pendiente', NULL, '0.00', NULL, '2025-10-23 16:58:54'),
(10, 3, 'local', 3, 'pendiente', NULL, '0.00', NULL, '2025-10-23 16:58:54'),
(11, 4, 'destino', 17, 'pendiente', NULL, '20.00', NULL, '2025-10-23 19:27:48'),
(12, 4, 'guia', 4, 'pendiente', NULL, '0.00', NULL, '2025-10-23 19:27:49'),
(13, 4, 'agencia', 4, 'pendiente', NULL, '0.00', NULL, '2025-10-23 19:27:49'),
(14, 4, 'local', 3, 'pendiente', NULL, '0.00', NULL, '2025-10-23 19:27:49'),
(15, 5, 'destino', 16, 'pendiente', NULL, '30.00', NULL, '2025-10-23 21:05:53'),
(16, 5, 'destino', 18, 'pendiente', NULL, '2.00', NULL, '2025-10-23 21:05:53'),
(17, 5, 'destino', 17, 'pendiente', NULL, '20.00', NULL, '2025-10-23 21:05:53'),
(18, 5, 'guia', 4, 'pendiente', NULL, '0.00', NULL, '2025-10-23 21:05:53'),
(19, 5, 'agencia', 4, 'pendiente', NULL, '0.00', NULL, '2025-10-23 21:05:53'),
(20, 5, 'local', 3, 'pendiente', NULL, '0.00', NULL, '2025-10-23 21:05:53'),
(21, 5, 'local', 4, 'pendiente', NULL, '0.00', NULL, '2025-10-23 21:05:53'),
(22, 6, 'destino', 16, 'pendiente', NULL, '30.00', NULL, '2025-12-28 12:33:09'),
(23, 6, 'destino', 18, 'pendiente', NULL, '2.00', NULL, '2025-12-28 12:33:09'),
(24, 6, 'destino', 21, 'pendiente', NULL, '50.00', NULL, '2025-12-28 12:33:09'),
(25, 6, 'destino', 19, 'pendiente', NULL, '30.00', NULL, '2025-12-28 12:33:09'),
(26, 6, 'destino', 17, 'pendiente', NULL, '20.00', NULL, '2025-12-28 12:33:09'),
(27, 6, 'guia', 4, 'pendiente', NULL, '0.00', NULL, '2025-12-28 12:33:09'),
(28, 6, 'agencia', 4, 'pendiente', NULL, '0.00', NULL, '2025-12-28 12:33:09'),
(29, 6, 'local', 3, 'pendiente', NULL, '0.00', NULL, '2025-12-28 12:33:09'),
(30, 6, 'local', 4, 'pendiente', NULL, '0.00', NULL, '2025-12-28 12:33:09'),
(31, 7, 'destino', 16, 'pendiente', NULL, '30.00', NULL, '2025-12-28 12:33:22'),
(32, 7, 'destino', 18, 'pendiente', NULL, '2.00', NULL, '2025-12-28 12:33:22'),
(33, 7, 'destino', 21, 'pendiente', NULL, '50.00', NULL, '2025-12-28 12:33:22'),
(34, 7, 'destino', 19, 'pendiente', NULL, '30.00', NULL, '2025-12-28 12:33:22'),
(35, 7, 'destino', 17, 'pendiente', NULL, '20.00', NULL, '2025-12-28 12:33:22'),
(36, 7, 'guia', 4, 'pendiente', NULL, '0.00', NULL, '2025-12-28 12:33:22'),
(37, 7, 'agencia', 4, 'pendiente', NULL, '0.00', NULL, '2025-12-28 12:33:22'),
(38, 7, 'local', 3, 'pendiente', NULL, '0.00', NULL, '2025-12-28 12:33:22'),
(39, 7, 'local', 4, 'pendiente', NULL, '0.00', NULL, '2025-12-28 12:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `servicios_agencia`
--

CREATE TABLE `servicios_agencia` (
  `id` int(11) NOT NULL,
  `id_agencia` int(11) NOT NULL,
  `nombre_servicio` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `servicios_agencia`
--

INSERT INTO `servicios_agencia` (`id`, `id_agencia`, `nombre_servicio`, `descripcion`, `precio`, `fecha_creacion`) VALUES
(4, 4, 'Ticket Primera Clase', 'es tu puerta a viajes excepcionales, donde diseñamos experiencias de lujo personalizadas, desde la planificación de itinerarios exclusivos hasta la reserva de vuelos y alojamientos de élite, garantizando un servicio impecable y sin preocupaciones. Nos especializamos en transformar tus sueños de viaje en realidades inolvidables, cuidando cada detalle para que tu única tarea sea disfrutar del camino', '300.00', '2025-10-22 16:25:32');

-- --------------------------------------------------------

--
-- Table structure for table `servicios_guia`
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
-- Dumping data for table `servicios_guia`
--

INSERT INTO `servicios_guia` (`id`, `id_guia`, `nombre_servicio`, `descripcion`, `precio`, `fecha_creacion`) VALUES
(4, 4, 'Visita a Ureka', 'Visita a las cascadas de ureka', '30.00', '2025-10-22 16:15:58');

-- --------------------------------------------------------

--
-- Table structure for table `servicios_local`
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
-- Dumping data for table `servicios_local`
--

INSERT INTO `servicios_local` (`id`, `id_local`, `nombre_servicio`, `descripcion`, `precio`, `fecha_creacion`) VALUES
(4, 3, 'Alojamiento Comodo', 'Algo Economico pero comodo', '40.00', '2025-10-22 22:21:04'),
(5, 4, 'Noche de Espectáculos ', 'es una experiencia única donde el arte, la música y el talento se unen bajo un mismo cielo. Un evento vibrante que celebra la creatividad africana con presentaciones en vivo, luces, ritmo y emoción pura. Cada noche es una historia, cada show una explosión de energía y estilo', '30.00', '2025-10-23 21:17:34');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `contrasena` varchar(255) NOT NULL,
  `es_admin` tinyint(1) NOT NULL DEFAULT 0,
  `tipo_usuario` enum('turista','agencia','guia','local','super_admin') NOT NULL DEFAULT 'turista',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_servicio` enum('disponible','ocupado','no_disponible') DEFAULT 'disponible',
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `telefono`, `contrasena`, `es_admin`, `tipo_usuario`, `fecha_registro`, `estado_servicio`, `ultima_actualizacion`) VALUES
(14, 'Super Admin', 'etebachalegroup@gmail.com', NULL, '$2y$10$rVXD8zi4McqhF7uS33EePOGm7IF/bd9fAjH72AoaOdJWuzZViPfxy', 1, 'super_admin', '2025-10-22 13:39:56', 'disponible', '2025-10-24 00:27:07'),
(15, 'GQ-Turismo', 'admin@gqturismo.com', NULL, '$2y$10$1J.ZfX.9wgG2J57R7i0bUeoyI6V6cRfPLL4biQRA3T/QKJFaQ7Wb2', 0, 'super_admin', '2025-10-22 13:42:21', 'disponible', '2025-10-24 00:27:07'),
(16, 'Juan', 'juan@gmail.com', NULL, '$2y$10$6eDWrpDy1Hs5hsopwo9.luJuSsKO8T7vt0PgQ3WIuI8QiqjfIb5t6', 0, 'turista', '2025-10-22 15:38:56', 'disponible', '2025-10-24 00:27:07'),
(17, 'Guia', 'guia@gmail.com', NULL, '$2y$10$.y.F7hIGmiUufbyGdk.tj.NEVm/mbdeHR9a6/zRvphPu6lwseV7NW', 0, 'guia', '2025-10-22 16:03:02', 'disponible', '2025-10-24 00:27:07'),
(18, 'Agencia de viajes', 'agencia@viajes.com', NULL, '$2y$10$tBpZTdxnIvqJs2K8YvMTqOW63Z0Tsrr0FNvu0y4r1TyG1E4RuRqdW', 0, 'agencia', '2025-10-22 16:20:15', 'disponible', '2025-10-24 00:27:07'),
(19, 'Local', 'hotel@local.com', NULL, '$2y$10$jcXF/U9CzJpDjccFT/1ehuED3naprX57ffa3TDYfeQTEwaMrp39sa', 0, 'local', '2025-10-22 22:08:41', 'disponible', '2025-10-24 00:27:07'),
(20, 'Viva El Rollo', 'viva@club.com', NULL, '$2y$10$vTSScG.lGitUJtVh6EhUs.oggsb94iMoqpLTUYzdtRHk00hGQu85y', 0, 'local', '2025-10-23 20:53:39', 'disponible', '2025-10-24 00:27:07'),
(21, 'José María Bilekera', 'jm1bilekerabuilelo@gmail.com', NULL, '$2y$10$U0bdHXABMhTKYex2tN.TjuTWiQwnzZ0dSiZhf9D532.ttfWdjLZ3S', 0, 'super_admin', '2025-10-30 20:22:00', 'disponible', '2025-10-30 21:04:25'),
(22, 'Richi doeswell', 'dreamvibeservices@gmail.com', NULL, '$2y$10$z0C6UED1D9RAZ4w2ZD8GYOIISvRiaXEY6tVeMxV9ycbhbqKrf2JRa', 0, 'turista', '2025-10-31 10:51:30', 'disponible', '2025-10-31 10:51:30');

-- --------------------------------------------------------

--
-- Table structure for table `valoraciones`
--

CREATE TABLE `valoraciones` (
  `id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `provider_type` enum('agencia','guia','local') NOT NULL,
  `rating` int(11) NOT NULL
) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agencias`
--
ALTER TABLE `agencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `carouseles`
--
ALTER TABLE `carouseles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `confirmaciones_servicios`
--
ALTER TABLE `confirmaciones_servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proveedor` (`id_proveedor`,`tipo_proveedor`),
  ADD KEY `idx_estado` (`estado_confirmacion`);

--
-- Indexes for table `confirmacion_servicios`
--
ALTER TABLE `confirmacion_servicios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pedido_proveedor` (`id_pedido_servicio`,`id_proveedor`),
  ADD KEY `idx_proveedor` (`id_proveedor`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indexes for table `descuentos`
--
ALTER TABLE `descuentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `discount_code` (`discount_code`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Indexes for table `destinos`
--
ALTER TABLE `destinos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_destinos_categoria` (`categoria`),
  ADD KEY `idx_destinos_ciudad` (`ciudad`),
  ADD KEY `idx_destinos_precio` (`precio`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_ciudad` (`ciudad`);

--
-- Indexes for table `guias_destinos`
--
ALTER TABLE `guias_destinos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_guia` (`id_guia`,`id_destino`),
  ADD KEY `idx_guias_destinos_guia` (`id_guia`),
  ADD KEY `idx_guias_destinos_destino` (`id_destino`);

--
-- Indexes for table `guias_turisticos`
--
ALTER TABLE `guias_turisticos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_guias_ciudad` (`ciudad_operacion`);

--
-- Indexes for table `guia_destinos`
--
ALTER TABLE `guia_destinos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guia_destino_unique` (`id_guia`,`id_destino`),
  ADD KEY `id_guia` (`id_guia`),
  ADD KEY `id_destino` (`id_destino`);

--
-- Indexes for table `guia_destinos_disponibles`
--
ALTER TABLE `guia_destinos_disponibles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_guia_destino` (`id_guia`,`id_destino`),
  ADD KEY `idx_guia` (`id_guia`),
  ADD KEY `idx_destino` (`id_destino`);

--
-- Indexes for table `imagenes_destino`
--
ALTER TABLE `imagenes_destino`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_destino` (`id_destino`);

--
-- Indexes for table `imagenes_guia`
--
ALTER TABLE `imagenes_guia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_guia` (`id_guia`);

--
-- Indexes for table `imagenes_local`
--
ALTER TABLE `imagenes_local`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_local` (`id_local`);

--
-- Indexes for table `itinerarios`
--
ALTER TABLE `itinerarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_id_turista` (`id_turista`),
  ADD KEY `idx_itinerarios_usuario` (`id_usuario`);

--
-- Indexes for table `itinerario_agencias`
--
ALTER TABLE `itinerario_agencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_itinerario_agencia` (`id_itinerario`,`id_agencia`),
  ADD KEY `id_agencia` (`id_agencia`);

--
-- Indexes for table `itinerario_destinos`
--
ALTER TABLE `itinerario_destinos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_itinerario` (`id_itinerario`),
  ADD KEY `id_destino` (`id_destino`);

--
-- Indexes for table `itinerario_guias`
--
ALTER TABLE `itinerario_guias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_itinerario_guia` (`id_itinerario`,`id_guia`),
  ADD KEY `id_guia` (`id_guia`);

--
-- Indexes for table `itinerario_locales`
--
ALTER TABLE `itinerario_locales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_itinerario_local` (`id_itinerario`,`id_local`),
  ADD KEY `id_local` (`id_local`);

--
-- Indexes for table `itinerario_servicios`
--
ALTER TABLE `itinerario_servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_servicio` (`tipo_servicio`,`id_servicio`),
  ADD KEY `idx_itinerario` (`id_itinerario`);

--
-- Indexes for table `itinerario_tareas`
--
ALTER TABLE `itinerario_tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_itinerario` (`id_itinerario`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha` (`fecha_hora_inicio`),
  ADD KEY `idx_itinerario_tareas_itinerario` (`id_itinerario`),
  ADD KEY `idx_itinerario_tareas_estado` (`estado`),
  ADD KEY `idx_itinerario_tareas_proveedor` (`id_proveedor`);

--
-- Indexes for table `locales_turisticos`
--
ALTER TABLE `locales_turisticos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `lugares_locales`
--
ALTER TABLE `lugares_locales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_locales_tipo` (`tipo_local`);

--
-- Indexes for table `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sender` (`sender_id`,`sender_type`),
  ADD KEY `idx_receiver` (`receiver_id`,`receiver_type`),
  ADD KEY `idx_conversation` (`sender_id`,`sender_type`,`receiver_id`,`receiver_type`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_unread` (`receiver_id`,`receiver_type`,`is_read`),
  ADD KEY `idx_receptor_leido` (`id_receptor`,`leido`),
  ADD KEY `idx_emisor` (`id_emisor`),
  ADD KEY `idx_mensajes_no_leidos` (`receiver_id`,`receiver_type`,`leido`),
  ADD KEY `idx_mensajes_conversacion_fecha` (`sender_id`,`receiver_id`,`id`);

--
-- Indexes for table `menus_agencia`
--
ALTER TABLE `menus_agencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_agencia` (`id_agencia`);

--
-- Indexes for table `menus_local`
--
ALTER TABLE `menus_local`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_local` (`id_local`);

--
-- Indexes for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`id_usuario`,`leido`),
  ADD KEY `idx_fecha` (`fecha_creacion`);

--
-- Indexes for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_reserva` (`id_reserva`);

--
-- Indexes for table `pedidos_servicios`
--
ALTER TABLE `pedidos_servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_turista` (`id_turista`),
  ADD KEY `id_itinerario` (`id_itinerario`),
  ADD KEY `idx_proveedor` (`tipo_proveedor`,`id_proveedor`),
  ADD KEY `idx_turista` (`id_turista`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_itinerario` (`id_itinerario`),
  ADD KEY `idx_destino` (`id_destino`),
  ADD KEY `idx_pedidos_servicios_proveedor` (`id_proveedor`,`tipo_proveedor`),
  ADD KEY `idx_pedidos_servicios_turista` (`id_turista`);

--
-- Indexes for table `publicidades`
--
ALTER TABLE `publicidades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `publicidad_carousel`
--
ALTER TABLE `publicidad_carousel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activo_orden` (`activo`,`orden`);

--
-- Indexes for table `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_itinerario` (`id_itinerario`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_fecha` (`fecha_reserva`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indexes for table `reserva_servicios`
--
ALTER TABLE `reserva_servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_reserva` (`id_reserva`),
  ADD KEY `idx_servicio` (`tipo_servicio`,`id_servicio`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indexes for table `servicios_agencia`
--
ALTER TABLE `servicios_agencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_agencia` (`id_agencia`);

--
-- Indexes for table `servicios_guia`
--
ALTER TABLE `servicios_guia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_guia` (`id_guia`);

--
-- Indexes for table `servicios_local`
--
ALTER TABLE `servicios_local`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_local` (`id_local`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agencias`
--
ALTER TABLE `agencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `carouseles`
--
ALTER TABLE `carouseles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `confirmaciones_servicios`
--
ALTER TABLE `confirmaciones_servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `confirmacion_servicios`
--
ALTER TABLE `confirmacion_servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `descuentos`
--
ALTER TABLE `descuentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `destinos`
--
ALTER TABLE `destinos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `guias_destinos`
--
ALTER TABLE `guias_destinos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guias_turisticos`
--
ALTER TABLE `guias_turisticos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `guia_destinos`
--
ALTER TABLE `guia_destinos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guia_destinos_disponibles`
--
ALTER TABLE `guia_destinos_disponibles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `imagenes_destino`
--
ALTER TABLE `imagenes_destino`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `imagenes_guia`
--
ALTER TABLE `imagenes_guia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `imagenes_local`
--
ALTER TABLE `imagenes_local`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `itinerarios`
--
ALTER TABLE `itinerarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `itinerario_agencias`
--
ALTER TABLE `itinerario_agencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `itinerario_destinos`
--
ALTER TABLE `itinerario_destinos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `itinerario_guias`
--
ALTER TABLE `itinerario_guias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `itinerario_locales`
--
ALTER TABLE `itinerario_locales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `itinerario_servicios`
--
ALTER TABLE `itinerario_servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `itinerario_tareas`
--
ALTER TABLE `itinerario_tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locales_turisticos`
--
ALTER TABLE `locales_turisticos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lugares_locales`
--
ALTER TABLE `lugares_locales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `menus_agencia`
--
ALTER TABLE `menus_agencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `menus_local`
--
ALTER TABLE `menus_local`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pedidos_servicios`
--
ALTER TABLE `pedidos_servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `publicidades`
--
ALTER TABLE `publicidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `publicidad_carousel`
--
ALTER TABLE `publicidad_carousel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reserva_servicios`
--
ALTER TABLE `reserva_servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `servicios_agencia`
--
ALTER TABLE `servicios_agencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `servicios_guia`
--
ALTER TABLE `servicios_guia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `servicios_local`
--
ALTER TABLE `servicios_local`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `valoraciones`
--
ALTER TABLE `valoraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agencias`
--
ALTER TABLE `agencias`
  ADD CONSTRAINT `agencias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `confirmacion_servicios`
--
ALTER TABLE `confirmacion_servicios`
  ADD CONSTRAINT `confirmacion_servicios_ibfk_1` FOREIGN KEY (`id_pedido_servicio`) REFERENCES `pedidos_servicios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `confirmacion_servicios_ibfk_2` FOREIGN KEY (`id_proveedor`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `descuentos`
--
ALTER TABLE `descuentos`
  ADD CONSTRAINT `descuentos_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agencias` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guias_destinos`
--
ALTER TABLE `guias_destinos`
  ADD CONSTRAINT `guias_destinos_ibfk_1` FOREIGN KEY (`id_guia`) REFERENCES `guias_turisticos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guias_destinos_ibfk_2` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guias_turisticos`
--
ALTER TABLE `guias_turisticos`
  ADD CONSTRAINT `guias_turisticos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `imagenes_destino`
--
ALTER TABLE `imagenes_destino`
  ADD CONSTRAINT `imagenes_destino_ibfk_1` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `imagenes_guia`
--
ALTER TABLE `imagenes_guia`
  ADD CONSTRAINT `imagenes_guia_ibfk_1` FOREIGN KEY (`id_guia`) REFERENCES `guias_turisticos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `imagenes_local`
--
ALTER TABLE `imagenes_local`
  ADD CONSTRAINT `imagenes_local_ibfk_1` FOREIGN KEY (`id_local`) REFERENCES `lugares_locales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `itinerarios`
--
ALTER TABLE `itinerarios`
  ADD CONSTRAINT `fk_itinerarios_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `itinerarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `itinerario_agencias`
--
ALTER TABLE `itinerario_agencias`
  ADD CONSTRAINT `itinerario_agencias_ibfk_1` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `itinerario_agencias_ibfk_2` FOREIGN KEY (`id_agencia`) REFERENCES `agencias` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `itinerario_destinos`
--
ALTER TABLE `itinerario_destinos`
  ADD CONSTRAINT `itinerario_destinos_ibfk_1` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `itinerario_destinos_ibfk_2` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `itinerario_guias`
--
ALTER TABLE `itinerario_guias`
  ADD CONSTRAINT `itinerario_guias_ibfk_1` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `itinerario_guias_ibfk_2` FOREIGN KEY (`id_guia`) REFERENCES `guias_turisticos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `itinerario_locales`
--
ALTER TABLE `itinerario_locales`
  ADD CONSTRAINT `itinerario_locales_ibfk_1` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `itinerario_locales_ibfk_2` FOREIGN KEY (`id_local`) REFERENCES `lugares_locales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `itinerario_servicios`
--
ALTER TABLE `itinerario_servicios`
  ADD CONSTRAINT `itinerario_servicios_ibfk_1` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `locales_turisticos`
--
ALTER TABLE `locales_turisticos`
  ADD CONSTRAINT `locales_turisticos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lugares_locales`
--
ALTER TABLE `lugares_locales`
  ADD CONSTRAINT `lugares_locales_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menus_agencia`
--
ALTER TABLE `menus_agencia`
  ADD CONSTRAINT `menus_agencia_ibfk_1` FOREIGN KEY (`id_agencia`) REFERENCES `agencias` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menus_local`
--
ALTER TABLE `menus_local`
  ADD CONSTRAINT `menus_local_ibfk_1` FOREIGN KEY (`id_local`) REFERENCES `lugares_locales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pedidos_servicios`
--
ALTER TABLE `pedidos_servicios`
  ADD CONSTRAINT `fk_id_destino` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pedidos_servicios_ibfk_1` FOREIGN KEY (`id_turista`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedidos_servicios_ibfk_2` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reserva_servicios`
--
ALTER TABLE `reserva_servicios`
  ADD CONSTRAINT `reserva_servicios_ibfk_1` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `servicios_agencia`
--
ALTER TABLE `servicios_agencia`
  ADD CONSTRAINT `servicios_agencia_ibfk_1` FOREIGN KEY (`id_agencia`) REFERENCES `agencias` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `servicios_guia`
--
ALTER TABLE `servicios_guia`
  ADD CONSTRAINT `servicios_guia_ibfk_1` FOREIGN KEY (`id_guia`) REFERENCES `guias_turisticos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `servicios_local`
--
ALTER TABLE `servicios_local`
  ADD CONSTRAINT `servicios_local_ibfk_1` FOREIGN KEY (`id_local`) REFERENCES `lugares_locales` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
