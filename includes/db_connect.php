<?php
$servername = "db.fr-pari1.bengt.wasmernet.com";
$username = "7dac3e31711c80004701f5adfe4b"; // Default XAMPP username
$password = "068f7dac-3e31-72ec-8000-ff18f4270a94";     // Default XAMPP password
$dbname = "gqturismo_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
// Establecer el conjunto de caracteres a utf8mb4
$conn->set_charset("utf8mb4");
?>
