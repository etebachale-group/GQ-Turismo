<?php
session_start();
include '../includes/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prevenir inyección SQL usando sentencias preparadas
    $stmt = $conn->prepare("SELECT id, nombre, contrasena, tipo_usuario FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verificar que el tipo de usuario sea uno de los permitidos para el panel de administración
        if (!in_array($user['tipo_usuario'], ['agencia', 'guia', 'local', 'super_admin'])) {
            $error = "Acceso denegado. Tu tipo de cuenta no tiene permisos de administrador.";
        } else if (password_verify($password, $user['contrasena'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre']; // Corregido a user_nombre
            $_SESSION['user_type'] = $user['tipo_usuario']; // Guardar el tipo de usuario
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "No se encontró ningún usuario con ese email.";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GQ-TURISMO</title>
    <!-- Bootstrap CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            background-color: #fff;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center mb-4">Acceso de Administrador</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
        </form>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>