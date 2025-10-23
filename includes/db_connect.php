<?php
/**
 * Archivo de Conexión a Base de Datos
 * GQ-Turismo
 * 
 * SEGURIDAD:
 * - En producción, cambiar credenciales
 * - Usar usuario de BD específico (no root)
 * - Establecer contraseña fuerte
 * - Considerar usar variables de entorno
 * 
 * PROTECCIÓN:
 * Este archivo está protegido por .htaccess en la raíz
 */

// Definir constante para permitir acceso
if (!defined('DB_ACCESS_ALLOWED')) {
    define('DB_ACCESS_ALLOWED', true);
}

// Configuración de conexión
$servername = "localhost";
$username = "root";        // ⚠️ CAMBIAR EN PRODUCCIÓN - Usar usuario específico
$password = "";            // ⚠️ ESTABLECER CONTRASEÑA FUERTE
$dbname = "gq_turismo";

// Configuración de errores (solo en desarrollo)
$show_errors = true; // Cambiar a false en producción

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    // En producción, registrar error en log en lugar de mostrarlo
    if ($show_errors) {
        die("Conexión fallida: " . $conn->connect_error);
    } else {
        error_log("Error de conexión MySQL: " . $conn->connect_error);
        die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
    }
}

// Establecer el conjunto de caracteres a utf8mb4
$conn->set_charset("utf8mb4");

// Configuración adicional de seguridad
// Prevenir inyección SQL estableciendo modo estricto
$conn->query("SET sql_mode = 'STRICT_ALL_TABLES'");

// Opcional: Establecer zona horaria
// $conn->query("SET time_zone = '+00:00'");
