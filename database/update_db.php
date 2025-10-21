<?php
require_once '../includes/db_connect.php';

echo "<pre>";
echo "Iniciando actualización de la base de datos...\n";

if (!$conn) {
    die("Error de conexión a la base de datos en update_db.php: " . $conn->connect_error);
}

// Función para ejecutar consultas SQL
function executeQuery($conn, $sql, $successMessage, $errorMessage) {
    if ($conn->query($sql) === TRUE) {
        echo $successMessage . "\n";
    } else {
        echo $errorMessage . ": " . $conn->error . "\n";
    }
}

// --- 1. Crear tablas si no existen ---

// Tabla usuarios
$sql_create_usuarios = "
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `es_admin` tinyint(1) NOT NULL DEFAULT 0,
  `tipo_usuario` ENUM('turista', 'agencia', 'guia', 'local', 'super_admin') NOT NULL DEFAULT 'turista',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_usuarios, "Tabla 'usuarios' verificada/creada.", "Error al crear tabla 'usuarios'");

// Añadir columna tipo_usuario si no existe
$sql_add_tipo_usuario = "
    ALTER TABLE `usuarios`
    ADD COLUMN `tipo_usuario` ENUM('turista', 'agencia', 'guia', 'local', 'super_admin') NOT NULL DEFAULT 'turista'
    AFTER `es_admin`;";

$check_column_sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'gq_turismo' AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'tipo_usuario'";
$result_check_column = $conn->query($check_column_sql);
$column_exists = $result_check_column->fetch_row()[0];

if ($column_exists == 0) {
    executeQuery($conn, $sql_add_tipo_usuario, "Columna 'tipo_usuario' añadida a la tabla 'usuarios'.", "Error al añadir columna 'tipo_usuario'");

    // Actualizar tipos de usuario existentes
    $sql_update_existing_users = "
        UPDATE `usuarios`
        SET `tipo_usuario` = CASE
            WHEN `es_admin` = 1 THEN 'super_admin'
            ELSE 'turista'
        END
        WHERE `tipo_usuario` = 'turista';"; // Solo actualiza si es el valor por defecto
    executeQuery($conn, $sql_update_existing_users, "Tipos de usuario existentes actualizados.", "Error al actualizar tipos de usuario existentes");
} else {
    echo "Columna 'tipo_usuario' ya existe en la tabla 'usuarios'.\n";
}

// Tabla destinos
$sql_create_destinos = "
CREATE TABLE IF NOT EXISTS `destinos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_destinos, "Tabla 'destinos' verificada/creada.", "Error al crear tabla 'destinos'");

// Tabla itinerarios
$sql_create_itinerarios = "
CREATE TABLE IF NOT EXISTS `itinerarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `nombre_itinerario` varchar(255) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_itinerarios, "Tabla 'itinerarios' verificada/creada.", "Error al crear tabla 'itinerarios'");

// Tabla itinerario_destinos
$sql_create_itinerario_destinos = "
CREATE TABLE IF NOT EXISTS `itinerario_destinos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_itinerario` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `orden` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_itinerario` (`id_itinerario`),
  KEY `id_destino` (`id_destino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_itinerario_destinos, "Tabla 'itinerario_destinos' verificada/creada.", "Error al crear tabla 'itinerario_destinos'");

// Tabla reservas
$sql_create_reservas = "
CREATE TABLE IF NOT EXISTS `reservas` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_reservas, "Tabla 'reservas' verificada/creada.", "Error al crear tabla 'reservas'");

// Nueva tabla agencias
$sql_create_agencias = "
CREATE TABLE IF NOT EXISTS `agencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `nombre_agencia` varchar(255) NOT NULL,
  `descripcion` text,
  `contacto_email` varchar(255),
  `contacto_telefono` varchar(50),
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_usuario` (`id_usuario`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_agencias, "Tabla 'agencias' verificada/creada.", "Error al crear tabla 'agencias'");

// Nueva tabla guias_turisticos
$sql_create_guias = "
CREATE TABLE IF NOT EXISTS `guias_turisticos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `nombre_guia` varchar(255) NOT NULL,
  `descripcion` text,
  `especialidades` varchar(255),
  `precio_hora` decimal(10,2) NOT NULL DEFAULT 0.00,
  `contacto_email` varchar(255),
  `contacto_telefono` varchar(50),
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_usuario` (`id_usuario`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_guias, "Tabla 'guias_turisticos' verificada/creada.", "Error al crear tabla 'guias_turisticos'");

// Nueva tabla lugares_locales
$sql_create_locales = "
CREATE TABLE IF NOT EXISTS `lugares_locales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `nombre_local` varchar(255) NOT NULL,
  `descripcion` text,
  `tipo_local` varchar(100),
  `direccion` varchar(255),
  `contacto_email` varchar(255),
  `contacto_telefono` varchar(50),
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_usuario` (`id_usuario`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_locales, "Tabla 'lugares_locales' verificada/creada.", "Error al crear tabla 'lugares_locales'");

// Nueva tabla servicios_agencia
$sql_create_servicios_agencia = "
CREATE TABLE IF NOT EXISTS `servicios_agencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_agencia` int(11) NOT NULL,
  `nombre_servicio` varchar(255) NOT NULL,
  `descripcion` text,
  `precio` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_agencia` (`id_agencia`),
  FOREIGN KEY (`id_agencia`) REFERENCES `agencias`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_servicios_agencia, "Tabla 'servicios_agencia' verificada/creada.", "Error al crear tabla 'servicios_agencia'");

// Nueva tabla menus_agencia (para paquetes o menús de servicios)
$sql_create_menus_agencia = "
CREATE TABLE IF NOT EXISTS `menus_agencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_agencia` int(11) NOT NULL,
  `nombre_menu` varchar(255) NOT NULL,
  `descripcion` text,
  `precio_total` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_agencia` (`id_agencia`),
  FOREIGN KEY (`id_agencia`) REFERENCES `agencias`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_menus_agencia, "Tabla 'menus_agencia' verificada/creada.", "Error al crear tabla 'menus_agencia'");

// Nueva tabla imagenes_guia
$sql_create_imagenes_guia = "
CREATE TABLE IF NOT EXISTS `imagenes_guia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_guia` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `descripcion` varchar(255),
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_guia` (`id_guia`),
  FOREIGN KEY (`id_guia`) REFERENCES `guias_turisticos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_imagenes_guia, "Tabla 'imagenes_guia' verificada/creada.", "Error al crear tabla 'imagenes_guia'");

// Nueva tabla servicios_guia
$sql_create_servicios_guia = "
CREATE TABLE IF NOT EXISTS `servicios_guia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_guia` int(11) NOT NULL,
  `nombre_servicio` varchar(255) NOT NULL,
  `descripcion` text,
  `precio` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_guia` (`id_guia`),
  FOREIGN KEY (`id_guia`) REFERENCES `guias_turisticos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_servicios_guia, "Tabla 'servicios_guia' verificada/creada.", "Error al crear tabla 'servicios_guia'");

// Nueva tabla imagenes_local
$sql_create_imagenes_local = "
CREATE TABLE IF NOT EXISTS `imagenes_local` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_local` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `descripcion` varchar(255),
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_local` (`id_local`),
  FOREIGN KEY (`id_local`) REFERENCES `lugares_locales`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_imagenes_local, "Tabla 'imagenes_local' verificada/creada.", "Error al crear tabla 'imagenes_local'");

// Nueva tabla servicios_local
$sql_create_servicios_local = "
CREATE TABLE IF NOT EXISTS `servicios_local` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_local` int(11) NOT NULL,
  `nombre_servicio` varchar(255) NOT NULL,
  `descripcion` text,
  `precio` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_local` (`id_local`),
  FOREIGN KEY (`id_local`) REFERENCES `lugares_locales`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_servicios_local, "Tabla 'servicios_local' verificada/creada.", "Error al crear tabla 'servicios_local'");

// Nueva tabla menus_local
$sql_create_menus_local = "
CREATE TABLE IF NOT EXISTS `menus_local` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_local` int(11) NOT NULL,
  `nombre_menu` varchar(255) NOT NULL,
  `descripcion` text,
  `precio_total` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_local` (`id_local`),
  FOREIGN KEY (`id_local`) REFERENCES `lugares_locales`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_menus_local, "Tabla 'menus_local' verificada/creada.", "Error al crear tabla 'menus_local'");

// Nueva tabla pedidos_servicios
$sql_create_pedidos_servicios = "
CREATE TABLE IF NOT EXISTS `pedidos_servicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_turista` int(11) NOT NULL,
  `tipo_proveedor` ENUM('agencia', 'guia', 'local') NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `id_servicio_o_menu` int(11) NOT NULL,
  `tipo_item` ENUM('servicio', 'menu') NOT NULL,
  `id_itinerario` int(11) DEFAULT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_servicio` date,
  `cantidad_personas` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `estado` ENUM('pendiente', 'confirmado', 'cancelado', 'completado') NOT NULL DEFAULT 'pendiente',
  PRIMARY KEY (`id`),
  KEY `id_turista` (`id_turista`),
  KEY `id_itinerario` (`id_itinerario`),
  FOREIGN KEY (`id_turista`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_pedidos_servicios, "Tabla 'pedidos_servicios' verificada/creada.", "Error al crear tabla 'pedidos_servicios'");

// Nueva tabla publicidades
$sql_create_publicidades = "
CREATE TABLE IF NOT EXISTS `publicidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text,
  `imagen` varchar(255) NOT NULL,
  `enlace` varchar(255),
  `fecha_inicio` date,
  `fecha_fin` date,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_publicidades, "Tabla 'publicidades' verificada/creada.", "Error al crear tabla 'publicidades'");

// Nueva tabla carouseles
$sql_create_carouseles = "
CREATE TABLE IF NOT EXISTS `carouseles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `enlace` varchar(255),
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
executeQuery($conn, $sql_create_carouseles, "Tabla 'carouseles' verificada/creada.", "Error al crear tabla 'carouseles'");

// --- 2. Insertar datos por defecto si no existen ---

// Usuario Admin
$email_admin = 'admin@gqturismo.com';
$sql_check_admin = "SELECT id FROM usuarios WHERE email = '$email_admin'";
$result_admin = $conn->query($sql_check_admin);

if ($result_admin->num_rows == 0) {
    $password_admin = password_hash('admin', PASSWORD_DEFAULT); // Contraseña: admin
    $sql_insert_admin = "INSERT INTO `usuarios` (`id`, `nombre`, `email`, `contrasena`, `es_admin`) VALUES
    (1, 'Admin', '$email_admin', '$password_admin', 1);";
    executeQuery($conn, $sql_insert_admin, "Usuario admin insertado.", "Error al insertar usuario admin");
} else {
    echo "Usuario admin ya existe.\n";
}

// Destinos por defecto
$default_destinos = [
    ['Pico Basilé', 'El punto más alto de Guinea Ecuatorial, con vistas panorámicas espectaculares de Malabo y el Monte Camerún.', 'Montaña', 'pico_basile.jpg', 120.00],
    ['Playas de Ureka', 'Un paraíso para el ecoturismo, donde las tortugas marinas vienen a desovar en playas vírgenes.', 'Playa', 'ureka.jpg', 220.00],
    ['Parque Nacional de Monte Alén', 'Una densa selva tropical que alberga una increíble biodiversidad, incluyendo gorilas, chimpancés y elefantes.', 'Naturaleza', 'monte_alen.jpg', 180.00],
    ['Catedral de Santa Isabel', 'Una joya arquitectónica de estilo neogótico en el corazón de Malabo, un símbolo de la historia de la ciudad.', 'Cultura', 'catedral_malabo.jpg', 50.00],
    ['Cascadas de Moca', 'Impresionantes caídas de agua en la región de Moca, un lugar perfecto para el senderismo y la fotografía.', 'Naturaleza', 'cascadas_moca.jpg', 90.00],
    ['Isla de Corisco', 'Una isla paradisíaca con playas de arena blanca, aguas cristalinas y una rica historia precolonial.', 'Playa', 'corisco.jpg', 250.00]
];

foreach ($default_destinos as $destino_data) {
    list($nombre, $descripcion, $categoria, $imagen, $precio) = $destino_data;
    $sql_check_destino = "SELECT id FROM destinos WHERE nombre = '$nombre'";
    $result_destino = $conn->query($sql_check_destino);

    if ($result_destino->num_rows == 0) {
        $sql_insert_destino = "INSERT INTO `destinos` (`nombre`, `descripcion`, `categoria`, `imagen`, `precio`) VALUES
        ('$nombre', '$descripcion', '$categoria', '$imagen', $precio);";
        executeQuery($conn, $sql_insert_destino, "Destino '$nombre' insertado.", "Error al insertar destino '$nombre'");
    } else {
        echo "Destino '$nombre' ya existe.\n";
    }
}

// --- Datos de ejemplo para Agencias, Guías y Locales ---

// Usuario de ejemplo para Agencia
$email_agencia = 'agencia@example.com';
$sql_check_agencia_user = "SELECT id FROM usuarios WHERE email = '$email_agencia'";
$result_agencia_user = $conn->query($sql_check_agencia_user);
$id_agencia_user = null;
if ($result_agencia_user->num_rows == 0) {
    $password_agencia = password_hash('password', PASSWORD_DEFAULT);
    $sql_insert_agencia_user = "INSERT INTO `usuarios` (`nombre`, `email`, `contrasena`, `tipo_usuario`) VALUES
    ('Agencia de Viajes Ejemplo', '$email_agencia', '$password_agencia', 'agencia');";
    if (executeQuery($conn, $sql_insert_agencia_user, "Usuario de agencia insertado.", "Error al insertar usuario de agencia")) {
        $id_agencia_user = $conn->insert_id;
    }
} else {
    $id_agencia_user = $result_agencia_user->fetch_assoc()['id'];
    echo "Usuario de agencia ya existe.\n";
}

// Agencia de ejemplo
if ($id_agencia_user) {
    $nombre_agencia_ejemplo = 'Vuelos GE Express';
    $sql_check_agencia = "SELECT id FROM agencias WHERE nombre_agencia = '$nombre_agencia_ejemplo'";
    $result_agencia = $conn->query($sql_check_agencia);
    $id_agencia_ejemplo = null;
    if ($result_agencia->num_rows == 0) {
        $sql_insert_agencia = "INSERT INTO `agencias` (`id_usuario`, `nombre_agencia`, `descripcion`, `contacto_email`, `contacto_telefono`) VALUES
        ($id_agencia_user, '$nombre_agencia_ejemplo', 'Tu mejor opción para volar por Guinea Ecuatorial y el mundo.', '$email_agencia', '+240111222');";
        if (executeQuery($conn, $sql_insert_agencia, "Agencia de ejemplo insertada.", "Error al insertar agencia de ejemplo")) {
            $id_agencia_ejemplo = $conn->insert_id;
        }
    } else {
        $id_agencia_ejemplo = $result_agencia->fetch_assoc()['id'];
        echo "Agencia de ejemplo ya existe.\n";
    }

    // Servicios de agencia de ejemplo
    if ($id_agencia_ejemplo) {
        $servicios_agencia_ejemplo = [
            ['Vuelo Malabo-Bata', 'Vuelo directo entre las dos ciudades principales.', 150.00],
            ['Paquete Vacacional Bioko', '7 días en Bioko, incluye hotel y tours.', 800.00],
        ];
        foreach ($servicios_agencia_ejemplo as $servicio_data) {
            list($nombre, $descripcion, $precio) = $servicio_data;
            $sql_check_servicio = "SELECT id FROM servicios_agencia WHERE id_agencia = $id_agencia_ejemplo AND nombre_servicio = '$nombre'";
            $result_servicio = $conn->query($sql_check_servicio);
            if ($result_servicio->num_rows == 0) {
                $sql_insert_servicio = "INSERT INTO `servicios_agencia` (`id_agencia`, `nombre_servicio`, `descripcion`, `precio`) VALUES
                ($id_agencia_ejemplo, '$nombre', '$descripcion', $precio);";
                executeQuery($conn, $sql_insert_servicio, "Servicio '$nombre' de agencia insertado.", "Error al insertar servicio de agencia '$nombre'");
            } else {
                echo "Servicio '$nombre' de agencia ya existe.\n";
            }
        }

        // Menús de agencia de ejemplo
        $menus_agencia_ejemplo = [
            ['Oferta Luna de Miel', 'Paquete romántico con todo incluido.', 1500.00],
        ];
        foreach ($menus_agencia_ejemplo as $menu_data) {
            list($nombre, $descripcion, $precio) = $menu_data;
            $sql_check_menu = "SELECT id FROM menus_agencia WHERE id_agencia = $id_agencia_ejemplo AND nombre_menu = '$nombre'";
            $result_menu = $conn->query($sql_check_menu);
            if ($result_menu->num_rows == 0) {
                $sql_insert_menu = "INSERT INTO `menus_agencia` (`id_agencia`, `nombre_menu`, `descripcion`, `precio_total`) VALUES
                ($id_agencia_ejemplo, '$nombre', '$descripcion', $precio);";
                executeQuery($conn, $sql_insert_menu, "Menú '$nombre' de agencia insertado.", "Error al insertar menú de agencia '$nombre'");
            } else {
                echo "Menú '$nombre' de agencia ya existe.\n";
            }
        }
    }
}

// Usuario de ejemplo para Guía Turístico
$email_guia = 'guia@example.com';
$sql_check_guia_user = "SELECT id FROM usuarios WHERE email = '$email_guia'";
$result_guia_user = $conn->query($sql_check_guia_user);
$id_guia_user = null;
if ($result_guia_user->num_rows == 0) {
    $password_guia = password_hash('password', PASSWORD_DEFAULT);
    $sql_insert_guia_user = "INSERT INTO `usuarios` (`nombre`, `email`, `contrasena`, `tipo_usuario`) VALUES
    ('Guía Experto GE', '$email_guia', '$password_guia', 'guia');";
    if (executeQuery($conn, $sql_insert_guia_user, "Usuario de guía insertado.", "Error al insertar usuario de guía")) {
        $id_guia_user = $conn->insert_id;
    }
} else {
    $id_guia_user = $result_guia_user->fetch_assoc()['id'];
    echo "Usuario de guía ya existe.\n";
}

// Guía de ejemplo
if ($id_guia_user) {
    $nombre_guia_ejemplo = 'Carlos Viajero';
    $sql_check_guia = "SELECT id FROM guias_turisticos WHERE nombre_guia = '$nombre_guia_ejemplo'";
    $result_guia = $conn->query($sql_check_guia);
    $id_guia_ejemplo = null;
    if ($result_guia->num_rows == 0) {
        $sql_insert_guia = "INSERT INTO `guias_turisticos` (`id_usuario`, `nombre_guia`, `descripcion`, `especialidades`, `precio_hora`, `contacto_email`, `contacto_telefono`) VALUES
        ($id_guia_user, '$nombre_guia_ejemplo', 'Guía local con 10 años de experiencia en Bioko y Annobón.', 'Historia, Naturaleza, Cultura', 30.00, '$email_guia', '+240333444');";
        if (executeQuery($conn, $sql_insert_guia, "Guía de ejemplo insertado.", "Error al insertar guía de ejemplo")) {
            $id_guia_ejemplo = $conn->insert_id;
        }
    } else {
        $id_guia_ejemplo = $result_guia->fetch_assoc()['id'];
        echo "Guía de ejemplo ya existe.\n";
    }

    // Imágenes de guía de ejemplo
    if ($id_guia_ejemplo) {
        $imagenes_guia_ejemplo = [
            ['guia_playa.jpg', 'Carlos en la playa de Ureka.'],
            ['guia_selva.jpg', 'Carlos explorando la selva.'],
        ];
        foreach ($imagenes_guia_ejemplo as $imagen_data) {
            list($ruta, $descripcion) = $imagen_data;
            $sql_check_imagen = "SELECT id FROM imagenes_guia WHERE id_guia = $id_guia_ejemplo AND ruta_imagen = '$ruta'";
            $result_imagen = $conn->query($sql_check_imagen);
            if ($result_imagen->num_rows == 0) {
                $sql_insert_imagen = "INSERT INTO `imagenes_guia` (`id_guia`, `ruta_imagen`, `descripcion`) VALUES
                ($id_guia_ejemplo, '$ruta', '$descripcion');";
                executeQuery($conn, $sql_insert_imagen, "Imagen '$ruta' de guía insertada.", "Error al insertar imagen de guía '$ruta'");
            } else {
                echo "Imagen '$ruta' de guía ya existe.\n";
            }
        }

        // Servicios de guía de ejemplo
        $servicios_guia_ejemplo = [
            ['Tour Histórico Malabo', 'Recorrido por los sitios históricos de Malabo.', 50.00],
            ['Senderismo Monte Alén', 'Excursión de día completo en el Parque Nacional.', 120.00],
        ];
        foreach ($servicios_guia_ejemplo as $servicio_data) {
            list($nombre, $descripcion, $precio) = $servicio_data;
            $sql_check_servicio = "SELECT id FROM servicios_guia WHERE id_guia = $id_guia_ejemplo AND nombre_servicio = '$nombre'";
            $result_servicio = $conn->query($sql_check_servicio);
            if ($result_servicio->num_rows == 0) {
                $sql_insert_servicio = "INSERT INTO `servicios_guia` (`id_guia`, `nombre_servicio`, `descripcion`, `precio`) VALUES
                ($id_guia_ejemplo, '$nombre', '$descripcion', $precio);";
                executeQuery($conn, $sql_insert_servicio, "Servicio '$nombre' de guía insertado.", "Error al insertar servicio de guía '$nombre'");
            } else {
                echo "Servicio '$nombre' de guía ya existe.\n";
            }
        }
    }
}

// Usuario de ejemplo para Guía Turístico (Segundo Guía)
$email_guia2 = 'guia2@example.com';
$sql_check_guia_user2 = "SELECT id FROM usuarios WHERE email = '$email_guia2'";
$result_guia_user2 = $conn->query($sql_check_guia_user2);
$id_guia_user2 = null;
if ($result_guia_user2->num_rows == 0) {
    $password_guia2 = password_hash('password', PASSWORD_DEFAULT);
    $sql_insert_guia_user2 = "INSERT INTO `usuarios` (`nombre`, `email`, `contrasena`, `tipo_usuario`) VALUES
    ('Ana Exploradora', '$email_guia2', '$password_guia2', 'guia');";
    if (executeQuery($conn, $sql_insert_guia_user2, "Segundo usuario de guía insertado.", "Error al insertar segundo usuario de guía")) {
        $id_guia_user2 = $conn->insert_id;
    }
} else {
    $id_guia_user2 = $result_guia_user2->fetch_assoc()['id'];
    echo "Segundo usuario de guía ya existe.\n";
}

// Segundo Guía de ejemplo
if ($id_guia_user2) {
    $nombre_guia_ejemplo2 = 'Ana Exploradora';
    $sql_check_guia2 = "SELECT id FROM guias_turisticos WHERE nombre_guia = '$nombre_guia_ejemplo2'";
    $result_guia2 = $conn->query($sql_check_guia2);
    $id_guia_ejemplo2 = null;
    if ($result_guia2->num_rows == 0) {
        $sql_insert_guia2 = "INSERT INTO `guias_turisticos` (`id_usuario`, `nombre_guia`, `descripcion`, `especialidades`, `precio_hora`, `contacto_email`, `contacto_telefono`) VALUES
        ($id_guia_user2, '$nombre_guia_ejemplo2', 'Especialista en rutas de aventura y ecoturismo en el interior de Guinea Ecuatorial.', 'Aventura, Ecoturismo, Fauna', 45.00, '$email_guia2', '+240777888');";
        if (executeQuery($conn, $sql_insert_guia2, "Segundo guía de ejemplo insertado.", "Error al insertar segundo guía de ejemplo")) {
            $id_guia_ejemplo2 = $conn->insert_id;
        }
    } else {
        $id_guia_ejemplo2 = $result_guia2->fetch_assoc()['id'];
        echo "Segundo guía de ejemplo ya existe.\n";
    }

    // Imágenes de guía de ejemplo (Segundo Guía)
    if ($id_guia_ejemplo2) {
        $imagenes_guia_ejemplo2 = [
            ['guia_selva2.jpg', 'Ana en la selva de Monte Alén.'],
            ['guia_cascada.jpg', 'Ana en las Cascadas de Moca.'],
        ];
        foreach ($imagenes_guia_ejemplo2 as $imagen_data) {
            list($ruta, $descripcion) = $imagen_data;
            $sql_check_imagen = "SELECT id FROM imagenes_guia WHERE id_guia = $id_guia_ejemplo2 AND ruta_imagen = '$ruta'";
            $result_imagen = $conn->query($sql_check_imagen);
            if ($result_imagen->num_rows == 0) {
                $sql_insert_imagen = "INSERT INTO `imagenes_guia` (`id_guia`, `ruta_imagen`, `descripcion`) VALUES
                ($id_guia_ejemplo2, '$ruta', '$descripcion');";
                executeQuery($conn, $sql_insert_imagen, "Imagen '$ruta' de segundo guía insertada.", "Error al insertar imagen de segundo guía '$ruta'");
            } else {
                echo "Imagen '$ruta' de segundo guía ya existe.\n";
            }
        }

        // Servicios de guía de ejemplo (Segundo Guía)
        $servicios_guia_ejemplo2 = [
            ['Ruta por Monte Alén', 'Exploración de la fauna y flora del Parque Nacional.', 150.00],
            ['Visita a Cascadas de Moca', 'Tour fotográfico y de senderismo.', 80.00],
        ];
        foreach ($servicios_guia_ejemplo2 as $servicio_data) {
            list($nombre, $descripcion, $precio) = $servicio_data;
            $sql_check_servicio = "SELECT id FROM servicios_guia WHERE id_guia = $id_guia_ejemplo2 AND nombre_servicio = '$nombre'";
            $result_servicio = $conn->query($sql_check_servicio);
            if ($result_servicio->num_rows == 0) {
                $sql_insert_servicio = "INSERT INTO `servicios_guia` (`id_guia`, `nombre_servicio`, `descripcion`, `precio`) VALUES
                ($id_guia_ejemplo2, '$nombre', '$descripcion', $precio);";
                executeQuery($conn, $sql_insert_servicio, "Servicio '$nombre' de segundo guía insertado.", "Error al insertar servicio de segundo guía '$nombre'");
            } else {
                echo "Servicio '$nombre' de segundo guía ya existe.\n";
            }
        }
    }
}

// Usuario de ejemplo para Local
$email_local = 'local@example.com';
$sql_check_local_user = "SELECT id FROM usuarios WHERE email = '$email_local'";
$result_local_user = $conn->query($sql_check_local_user);
$id_local_user = null;
if ($result_local_user->num_rows == 0) {
    $password_local = password_hash('password', PASSWORD_DEFAULT);
    $sql_insert_local_user = "INSERT INTO `usuarios` (`nombre`, `email`, `contrasena`, `tipo_usuario`) VALUES
    ('Restaurante Sabor GE', '$email_local', '$password_local', 'local');";
    if (executeQuery($conn, $sql_insert_local_user, "Usuario de local insertado.", "Error al insertar usuario de local")) {
        $id_local_user = $conn->insert_id;
    }
} else {
    $id_local_user = $result_local_user->fetch_assoc()['id'];
    echo "Usuario de local ya existe.\n";
}

// Local de ejemplo
if ($id_local_user) {
    $nombre_local_ejemplo = 'Restaurante Sabor GE';
    $sql_check_local = "SELECT id FROM lugares_locales WHERE nombre_local = '$nombre_local_ejemplo'";
    $result_local = $conn->query($sql_check_local);
    $id_local_ejemplo = null;
    if ($result_local->num_rows == 0) {
        $sql_insert_local = "INSERT INTO `lugares_locales` (`id_usuario`, `nombre_local`, `descripcion`, `tipo_local`, `direccion`, `contacto_email`, `contacto_telefono`) VALUES
        ($id_local_user, '$nombre_local_ejemplo', 'Disfruta de la auténtica gastronomía ecuatoguineana.', 'Restaurante', 'Calle Real, Malabo', '$email_local', '+240555666');";
        if (executeQuery($conn, $sql_insert_local, "Local de ejemplo insertado.", "Error al insertar local de ejemplo")) {
            $id_local_ejemplo = $conn->insert_id;
        }
    } else {
        $id_local_ejemplo = $result_local->fetch_assoc()['id'];
        echo "Local de ejemplo ya existe.\n";
    }

    // Imágenes de local de ejemplo
    if ($id_local_ejemplo) {
        $imagenes_local_ejemplo = [
            ['local_fachada.jpg', 'Fachada del restaurante.'],
            ['local_interior.jpg', 'Interior acogedor.'],
        ];
        foreach ($imagenes_local_ejemplo as $imagen_data) {
            list($ruta, $descripcion) = $imagen_data;
            $sql_check_imagen = "SELECT id FROM imagenes_local WHERE id_local = $id_local_ejemplo AND ruta_imagen = '$ruta'";
            $result_imagen = $conn->query($sql_check_imagen);
            if ($result_imagen->num_rows == 0) {
                $sql_insert_imagen = "INSERT INTO `imagenes_local` (`id_local`, `ruta_imagen`, `descripcion`) VALUES
                ($id_local_ejemplo, '$ruta', '$descripcion');";
                executeQuery($conn, $sql_insert_imagen, "Imagen '$ruta' de local insertada.", "Error al insertar imagen de local '$ruta'");
            } else {
                echo "Imagen '$ruta' de local ya existe.\n";
            }
        }

        // Servicios de local de ejemplo
        $servicios_local_ejemplo = [
            ['Cena Romántica', 'Menú especial para parejas.', 80.00],
            ['Catering para Eventos', 'Servicio de catering para cualquier ocasión.', 200.00],
        ];
        foreach ($servicios_local_ejemplo as $servicio_data) {
            list($nombre, $descripcion, $precio) = $servicio_data;
            $sql_check_servicio = "SELECT id FROM servicios_local WHERE id_local = $id_local_ejemplo AND nombre_servicio = '$nombre'";
            $result_servicio = $conn->query($sql_check_servicio);
            if ($result_servicio->num_rows == 0) {
                $sql_insert_servicio = "INSERT INTO `servicios_local` (`id_local`, `nombre_servicio`, `descripcion`, `precio`) VALUES
                ($id_local_ejemplo, '$nombre', '$descripcion', $precio);";
                executeQuery($conn, $sql_insert_servicio, "Servicio '$nombre' de local insertado.", "Error al insertar servicio de local '$nombre'");
            } else {
                echo "Servicio '$nombre' de local ya existe.\n";
            }
        }

        // Menús de local de ejemplo
        $menus_local_ejemplo = [
            ['Menú del Día', 'Plato principal, bebida y postre.', 15.00],
            ['Menú Degustación', 'Selección de nuestros mejores platos.', 45.00],
        ];
        foreach ($menus_local_ejemplo as $menu_data) {
            list($nombre, $descripcion, $precio) = $menu_data;
            $sql_check_menu = "SELECT id FROM menus_local WHERE id_local = $id_local_ejemplo AND nombre_menu = '$nombre'";
            $result_menu = $conn->query($sql_check_menu);
            if ($result_menu->num_rows == 0) {
                $sql_insert_menu = "INSERT INTO `menus_local` (`id_local`, `nombre_menu`, `descripcion`, `precio_total`) VALUES
                ($id_local_ejemplo, '$nombre', '$descripcion', $precio);";
                executeQuery($conn, $sql_insert_menu, "Menú '$nombre' de local insertado.", "Error al insertar menú de local '$nombre'");
            } else {
                echo "Menú '$nombre' de local ya existe.\n";
            }
        }
    }
}

// --- Datos de ejemplo para Publicidades y Carouseles ---

// Publicidad de ejemplo
$titulo_publicidad_ejemplo = 'Descubre la Isla de Bioko';
$sql_check_publicidad = "SELECT id FROM publicidades WHERE titulo = '$titulo_publicidad_ejemplo'";
$result_publicidad = $conn->query($sql_check_publicidad);
if ($result_publicidad->num_rows == 0) {
    $sql_insert_publicidad = "INSERT INTO `publicidades` (`titulo`, `descripcion`, `imagen`, `enlace`, `fecha_inicio`, `fecha_fin`, `activo`) VALUES
    ('$titulo_publicidad_ejemplo', 'Explora las maravillas naturales y culturales de Bioko.', 'publicidad_bioko.jpg', 'destinos.php?id=1', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1);";
    executeQuery($conn, $sql_insert_publicidad, "Publicidad de ejemplo insertada.", "Error al insertar publicidad de ejemplo");
} else {
    echo "Publicidad '$titulo_publicidad_ejemplo' ya existe.\n";
}

// Carousel de ejemplo
$nombre_carousel_ejemplo = 'Malabo al Atardecer';
$sql_check_carousel = "SELECT id FROM carouseles WHERE nombre = '$nombre_carousel_ejemplo'";
$result_carousel = $conn->query($sql_check_carousel);
if ($result_carousel->num_rows == 0) {
    $sql_insert_carousel = "INSERT INTO `carouseles` (`nombre`, `ruta_imagen`, `enlace`, `orden`, `activo`) VALUES
    ('$nombre_carousel_ejemplo', 'carousel_malabo.jpg', 'destinos.php?id=4', 1, 1);";
    executeQuery($conn, $sql_insert_carousel, "Carousel de ejemplo insertado.", "Error al insertar carousel de ejemplo");
} else {
    echo "Carousel '$nombre_carousel_ejemplo' ya existe.\n";
}

// --- 3. Aplicar restricciones y AUTO_INCREMENT (se pueden ejecutar de forma segura) ---

$sql_alter_auto_increment = [
    "ALTER TABLE `destinos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;",
    "ALTER TABLE `itinerarios` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
    "ALTER TABLE `itinerario_destinos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
    "ALTER TABLE `reservas` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
    "ALTER TABLE `usuarios` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;"
];

foreach ($sql_alter_auto_increment as $sql) {
    executeQuery($conn, $sql, "AUTO_INCREMENT/Modificación de tabla aplicada.", "Error al aplicar AUTO_INCREMENT/Modificación de tabla");
}

$sql_foreign_keys = [
    "ALTER TABLE `itinerarios` ADD CONSTRAINT `itinerarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;",
    "ALTER TABLE `itinerario_destinos` ADD CONSTRAINT `itinerario_destinos_ibfk_1` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE;",
    "ALTER TABLE `itinerario_destinos` ADD CONSTRAINT `itinerario_destinos_ibfk_2` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`) ON DELETE CASCADE;",
    "ALTER TABLE `reservas` ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_itinerario`) REFERENCES `itinerarios` (`id`) ON DELETE CASCADE;",
    "ALTER TABLE `reservas` ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;"
];

foreach ($sql_foreign_keys as $fk_sql) {
    // Extraer el nombre de la restricción de la consulta SQL
    preg_match('/CONSTRAINT `([^`]+)` FOREIGN KEY/', $fk_sql, $matches);
    $constraint_name = $matches[1] ?? null;

    if ($constraint_name) {
        // Verificar si la clave foránea ya existe
        $check_fk_sql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = 'gq_turismo' AND CONSTRAINT_NAME = '$constraint_name' AND CONSTRAINT_TYPE = 'FOREIGN KEY'";
        $result_check_fk = $conn->query($check_fk_sql);

        if ($result_check_fk && $result_check_fk->num_rows == 0) {
            // Si no existe, intentar añadirla
            if ($conn->query($fk_sql) === TRUE) {
                echo "Restricción de clave foránea '$constraint_name' aplicada.\n";
            } else {
                echo "Error al aplicar restricción de clave foránea '$constraint_name': " . $conn->error . "\n";
            }
        } else {
            echo "Restricción de clave foránea '$constraint_name' ya existente.\n";
        }
    } else {
        echo "No se pudo extraer el nombre de la restricción de la consulta: $fk_sql\n";
    }
}

echo "Actualización de la base de datos completada.\n";
echo "</pre>";

$conn->close();
?>
