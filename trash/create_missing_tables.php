<?php
require_once 'includes/db_connect.php';

// Crear publicidad_carousel
$sql = "CREATE TABLE IF NOT EXISTS `publicidad_carousel` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `descripcion` TEXT,
  `imagen` VARCHAR(255) DEFAULT NULL,
  `link` VARCHAR(500) DEFAULT NULL,
  `orden` INT(11) DEFAULT 0,
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if($conn->query($sql)) {
    echo "✓ Tabla publicidad_carousel creada\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// Crear itinerario_tareas
$sql = "CREATE TABLE IF NOT EXISTS `itinerario_tareas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_itinerario` INT(11) NOT NULL,
  `id_destino` INT(11) DEFAULT NULL,
  `id_proveedor` INT(11) DEFAULT NULL,
  `tipo_proveedor` ENUM('agencia','guia','local') DEFAULT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descripcion` TEXT,
  `tipo_tarea` ENUM('transporte','alojamiento','actividad','comida','guia','otro') DEFAULT 'otro',
  `fecha_hora_inicio` DATETIME NULL,
  `fecha_hora_fin` DATETIME NULL,
  `ubicacion` VARCHAR(255) DEFAULT NULL,
  `latitud` DECIMAL(10, 8) DEFAULT NULL,
  `longitud` DECIMAL(11, 8) DEFAULT NULL,
  `precio` DECIMAL(10,2) DEFAULT 0.00,
  `estado` ENUM('pendiente','en_progreso','completado','cancelado') DEFAULT 'pendiente',
  `notas` TEXT,
  `orden` INT(11) DEFAULT 0,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_itinerario` (`id_itinerario`),
  KEY `idx_estado` (`estado`),
  KEY `idx_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if($conn->query($sql)) {
    echo "✓ Tabla itinerario_tareas creada\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// Agregar columnas faltantes en itinerarios
$columns_to_add = [
    "ALTER TABLE `itinerarios` ADD COLUMN IF NOT EXISTS `fecha_inicio` DATE NULL",
    "ALTER TABLE `itinerarios` ADD COLUMN IF NOT EXISTS `fecha_fin` DATE NULL",
    "ALTER TABLE `itinerarios` ADD COLUMN IF NOT EXISTS `descripcion` TEXT NULL"
];

foreach($columns_to_add as $sql) {
    if($conn->query($sql)) {
        echo "✓ Columna agregada en itinerarios\n";
    } else {
        // Ignorar error si ya existe
        if(strpos($conn->error, 'Duplicate column') === false) {
            echo "Info: " . $conn->error . "\n";
        }
    }
}

// Agregar precio en itinerario_destinos
$sql = "ALTER TABLE `itinerario_destinos` ADD COLUMN IF NOT EXISTS `precio` DECIMAL(10,2) DEFAULT 0.00";
if($conn->query($sql)) {
    echo "✓ Columna precio agregada en itinerario_destinos\n";
} else {
    if(strpos($conn->error, 'Duplicate column') === false) {
        echo "Info: " . $conn->error . "\n";
    }
}

// Crear guia_destinos_disponibles
$sql = "CREATE TABLE IF NOT EXISTS `guia_destinos_disponibles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_guia` INT(11) NOT NULL,
  `id_destino` INT(11) NOT NULL,
  `precio_base` DECIMAL(10,2) DEFAULT 0.00,
  `disponible` TINYINT(1) DEFAULT 1,
  `notas` TEXT,
  `fecha_agregado` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_guia_destino` (`id_guia`, `id_destino`),
  KEY `idx_guia` (`id_guia`),
  KEY `idx_destino` (`id_destino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if($conn->query($sql)) {
    echo "✓ Tabla guia_destinos_disponibles creada\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// Actualizar mensajes para sistema emisor-receptor
$columns_mensajes = [
    "ALTER TABLE `mensajes` ADD COLUMN IF NOT EXISTS `id_emisor` INT(11) NULL",
    "ALTER TABLE `mensajes` ADD COLUMN IF NOT EXISTS `id_receptor` INT(11) NULL",
    "ALTER TABLE `mensajes` ADD COLUMN IF NOT EXISTS `leido` TINYINT(1) DEFAULT 0",
    "ALTER TABLE `mensajes` ADD COLUMN IF NOT EXISTS `fecha_lectura` DATETIME NULL"
];

foreach($columns_mensajes as $sql) {
    if($conn->query($sql)) {
        echo "✓ Columna agregada en mensajes\n";
    } else {
        if(strpos($conn->error, 'Duplicate column') === false) {
            echo "Info: " . $conn->error . "\n";
        }
    }
}

echo "\n=== COMPLETADO ===\n";
?>
