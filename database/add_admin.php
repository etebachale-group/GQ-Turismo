<?php
require_once '../includes/db_connect.php';

// --- Configuración del nuevo administrador ---
$nombre = 'admin';
$email = 'admin@gqturismo.com';
$contrasena = 'admin123'; // Contraseña en texto plano

// --- Creación del hash de la contraseña ---
$contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

// --- Inserción en la base de datos ---
$query = "INSERT INTO usuarios (nombre, email, contrasena) VALUES (?, ?, ?)";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Error al preparar la consulta: " . $conn->error);
}

$stmt->bind_param("sss", $nombre, $email, $contrasena_hash);

if ($stmt->execute()) {
    echo "<p>¡Administrador '$nombre' creado con éxito!</p>";
    echo "<p><strong>Email:</strong> $email</p>";
    echo "<p><strong>Contraseña:</strong> $contrasena</p>";
} else {
    echo "<p>Error al crear el administrador: " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();

/*
  Instrucciones:
  1. Sube este archivo a la carpeta `database` de tu proyecto.
  2. Accede a él desde el navegador (ej: http://localhost/GQ-Turismo/database/add_admin.php).
  3. Verifica que el usuario se ha creado en la tabla `usuarios` de tu base de datos.
  4. ¡¡IMPORTANTE!! Elimina este archivo del servidor después de usarlo por seguridad.
*/
?>