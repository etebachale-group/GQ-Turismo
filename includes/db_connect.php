<?php
$servername = "localhost";
$username = "etebachalegroup"; // Default XAMPP username
$password = "206849";     // Default XAMPP password
$dbname = "gq_turismo";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
// Establecer el conjunto de caracteres a utf8mb4
$conn->set_charset("utf8mb4");
?>
