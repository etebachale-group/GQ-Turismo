<?php
require_once '../includes/db_connect.php';

echo "<pre>";
echo "Intentando añadir Super Administrador...\n";

if (!$conn) {
    die("Error de conexión a la base de datos en add_super_admin.php: " . $conn->connect_error);
}

$name = 'Eteba Chale Group';
$email = 'etebachalegroup@gmail.com';
$password = 'mX7#Aq!D9v^H5tPz@w3*LuG2s$RkJ8yBn%fC1eQxZo6T!MhKjVr4pW0Nd^Ub'; // La contraseña proporcionada
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verificar si el usuario ya existe
$stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    echo "El Super Administrador con el email '$email' ya existe.\n";
} else {
    // Insertar el nuevo super administrador
    $stmt_insert = $conn->prepare("INSERT INTO usuarios (nombre, email, contrasena, es_admin) VALUES (?, ?, ?, 1)");
    $stmt_insert->bind_param("sss", $name, $email, $hashed_password);

    if ($stmt_insert->execute()) {
        echo "Super Administrador '$name' añadido con éxito.\n";
    } else {
        echo "Error al añadir Super Administrador: " . $stmt_insert->error . "\n";
    }
    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();

echo "</pre>";
?>
