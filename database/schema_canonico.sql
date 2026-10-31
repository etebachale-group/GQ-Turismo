-- ========================================================
-- ESQUEMA CANÓNICO CONSOLIDADO — GQ-TURISMO (FND-05)
-- Motor: MySQL 8 / MariaDB (utf8mb4_unicode_ci)
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = 'STRICT_ALL_TABLES';

-- 1. IDENTIDAD Y ACCESO
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `telefono` VARCHAR(30) NULL,
    `password` VARCHAR(255) NOT NULL,
    `tipo_usuario` ENUM('turista', 'agencia', 'guia', 'local', 'super_admin') DEFAULT 'turista',
    `estado` ENUM('activo', 'pendiente_aprobacion', 'inactivo', 'bloqueado') DEFAULT 'activo',
    `email_verified_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_tipo` (`tipo_usuario`),
    INDEX `idx_users_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. PERFILES DE PROVEEDORES
CREATE TABLE IF NOT EXISTS `agencias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `nombre_agencia` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NULL,
    `contacto_email` VARCHAR(255) NULL,
    `contacto_telefono` VARCHAR(50) NULL,
    `imagen_perfil` VARCHAR(255) NULL,
    `ciudad` VARCHAR(100) NULL,
    `latitude` DECIMAL(10, 8) NULL,
    `longitude` DECIMAL(11, 8) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `guias_turisticos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `nombre_guia` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NULL,
    `especialidades` VARCHAR(255) NULL,
    `idiomas` VARCHAR(255) NULL,
    `ciudad_operacion` VARCHAR(100) NULL,
    `precio_hora` DECIMAL(10, 2) DEFAULT 0.00,
    `imagen_perfil` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `locales` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `nombre_local` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NULL,
    `tipo_local` VARCHAR(100) NULL,
    `direccion` VARCHAR(255) NULL,
    `ciudad` VARCHAR(100) NULL,
    `contacto_email` VARCHAR(255) NULL,
    `contacto_telefono` VARCHAR(50) NULL,
    `imagen_perfil` VARCHAR(255) NULL,
    `latitude` DECIMAL(10, 8) NULL,
    `longitude` DECIMAL(11, 8) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CATÁLOGO DE DESTINOS
CREATE TABLE IF NOT EXISTS `destinos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NOT NULL,
    `categoria` VARCHAR(100) NOT NULL,
    `precio` DECIMAL(10, 2) DEFAULT 0.00,
    `ciudad` VARCHAR(100) NOT NULL,
    `imagen` VARCHAR(255) NULL,
    `latitude` DECIMAL(10, 8) NULL,
    `longitude` DECIMAL(11, 8) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `guia_destino` (
    `id_guia` INT NOT NULL,
    `id_destino` INT NOT NULL,
    PRIMARY KEY (`id_guia`, `id_destino`),
    FOREIGN KEY (`id_guia`) REFERENCES `guias_turisticos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_destino`) REFERENCES `destinos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ITINERARIOS
CREATE TABLE IF NOT EXISTS `itinerarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `nombre_itinerario` VARCHAR(255) NOT NULL,
    `estado` ENUM('planificacion', 'confirmado', 'en_curso', 'completado', 'cancelado') DEFAULT 'planificacion',
    `fecha_inicio` DATE NULL,
    `fecha_fin` DATE NULL,
    `presupuesto_estimado` DECIMAL(10, 2) DEFAULT 0.00,
    `precio_total` DECIMAL(10, 2) DEFAULT 0.00,
    `ciudad` VARCHAR(100) NULL,
    `notas` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. RESERVAS Y PAGOS (SNAPSHOT DE PRECIOS)
CREATE TABLE IF NOT EXISTS `reservas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `codigo_reserva` VARCHAR(50) NOT NULL UNIQUE,
    `estado` ENUM('borrador', 'pendiente_pago', 'pagada', 'confirmada', 'en_curso', 'completada', 'cancelada', 'reembolsada') DEFAULT 'borrador',
    `total_reserva` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `moneda` VARCHAR(10) DEFAULT 'XAF',
    `metodo_pago` VARCHAR(50) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. MENSAJERÍA ENTRE USUARIOS
CREATE TABLE IF NOT EXISTS `conversations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT NOT NULL,
    `sender_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. BANNERS Y PUBLICIDAD UNIFICADA
CREATE TABLE IF NOT EXISTS `banners` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(255) NOT NULL,
    `imagen` VARCHAR(255) NOT NULL,
    `link` VARCHAR(500) NULL,
    `posicion` INT DEFAULT 0,
    `activo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
