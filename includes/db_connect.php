<?php
/**
 * Archivo de Conexión a Base de Datos
 * GQ-Turismo - Soporte .env y Entorno Local XAMPP
 */

if (!defined('DB_ACCESS_ALLOWED')) {
    define('DB_ACCESS_ALLOWED', true);
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Support\Env;
use App\Support\ErrorHandler;

// Registrar manejador de errores y logs (F-07)
ErrorHandler::register();

// Configuración de conexión (usando Env centralizado con fallback a XAMPP local)
$servername = Env::get('DB_HOST', 'localhost');
$username   = Env::get('DB_USER', 'root');
$password   = Env::get('DB_PASS', '');
$dbname     = Env::get('DB_NAME', 'gq_turismo');
$port       = (int)Env::get('DB_PORT', 3306);
$app_debug  = (bool)Env::get('APP_DEBUG', false);

// Crear conexión
$conn = @new mysqli($servername, $username, $password, $dbname, $port);

// Verificar conexión
if ($conn->connect_error) {
    if ($app_debug) {
        die("Conexión fallida: " . $conn->connect_error . " (Host: $servername, BD: $dbname)");
    } else {
        \App\Support\Logger::error("Error de conexión a la BD: " . $conn->connect_error, [
            'host' => $servername,
            'db' => $dbname
        ]);
        die("No se pudo conectar a la base de datos. Por favor, intente más tarde.");
    }
}

// Configurar charset utf8mb4
$conn->set_charset("utf8mb4");
