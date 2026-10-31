<?php
/**
 * Archivo de Conexión a Base de Datos
 * GQ-Turismo - Soporte .env y Entorno Local XAMPP
 */

if (!defined('DB_ACCESS_ALLOWED')) {
    define('DB_ACCESS_ALLOWED', true);
}

// Cargar variables de entorno desde .env si existe
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $val;
            }
            if (getenv($key) === false) {
                putenv("$key=$val");
            }
        }
    }
}

// Configuración de conexión (con fallback a XAMPP local)
$servername = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost');
$username   = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root');
$password   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($_ENV['DB_PASS'] ?? '');
$dbname     = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'gq_turismo');
$port       = (int)(getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? 3306));

$app_debug  = (getenv('APP_DEBUG') === 'true' || ($_ENV['APP_DEBUG'] ?? '') === 'true');

// Crear conexión
$conn = @new mysqli($servername, $username, $password, $dbname, $port);

// Verificar conexión
if ($conn->connect_error) {
    if ($app_debug) {
        die("Conexión fallida: " . $conn->connect_error . " (Host: $servername, BD: $dbname)");
    } else {
        error_log("Error de conexión MySQL: " . $conn->connect_error);
        die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
    }
}

// Establecer el conjunto de caracteres a utf8mb4
$conn->set_charset("utf8mb4");

// Prevenir inyección SQL estableciendo modo estricto
$conn->query("SET sql_mode = 'STRICT_ALL_TABLES'");
