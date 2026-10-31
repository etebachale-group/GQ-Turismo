<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Prevenir inyección SQL usando sentencias preparadas
    $stmt = $conn->prepare("SELECT id, nombre, contrasena, tipo_usuario FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verificar que el tipo de usuario sea uno de los permitidos para el panel de administración
        if (!in_array($user['tipo_usuario'], ['agencia', 'guia', 'local', 'super_admin'])) {
            $error = "Acceso denegado. Tu tipo de cuenta no tiene permisos de administrador o proveedor.";
        } else if (password_verify($password, $user['contrasena'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_type'] = $user['tipo_usuario'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "No se encontró ningún usuario con ese correo electrónico.";
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
    <title>Acceso Proveedores & Gestión - GQ-Turismo</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/gq-design-tokens.css">
    <link rel="stylesheet" href="../assets/css/modern-ui.css">
    <link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 80% 20%, rgba(200, 149, 79, 0.2) 0%, transparent 50%), radial-gradient(circle at 10% 80%, rgba(23, 97, 58, 0.95) 0%, rgba(13, 56, 33, 0.98) 100%);
            padding: 1.5rem;
            font-family: var(--font-sans, 'Inter', sans-serif);
        }
        .admin-login-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            padding: 2.75rem 2.25rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <div class="admin-login-card text-center">
        <!-- Logo -->
        <a href="../index.php" class="d-inline-block mb-3">
            <img src="../assets/img/logo.png" alt="GQ-Turismo" style="height: 64px;" onerror="this.src='../assets/img/default.jpg'">
        </a>
        
        <span class="badge rounded-pill px-3 py-1 mb-2" style="background: var(--c-selva-50, #f0f7f2); color: var(--c-selva-800, #104629); font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase;">
            Portal de Proveedores y Gestión
        </span>

        <h1 class="h3 fw-bold mb-2" style="font-family: var(--font-display, 'Fraunces', serif); color: var(--c-selva-900, #0D3821);">
            Iniciar Sesión
        </h1>
        <p class="text-muted small mb-4">Acceso para guías, agencias receptivas y administradores</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 px-3 small rounded-pill mb-4 text-start" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="text-start">
            <div class="mb-3">
                <label for="email" class="form-label small fw-bold text-dark">
                    <i class="bi bi-envelope text-success me-1"></i> Correo Electrónico
                </label>
                <input type="email" class="form-control rounded-pill bg-light py-2" id="email" name="email" placeholder="proveedor@ejemplo.com" required autofocus>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label small fw-bold text-dark">
                    <i class="bi bi-lock text-success me-1"></i> Contraseña
                </label>
                <input type="password" class="form-control rounded-pill bg-light py-2" id="password" name="password" placeholder="Tu contraseña" required>
            </div>

            <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-bold shadow-sm mb-3">
                Acceder al Dashboard <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </form>

        <div class="pt-3 border-top">
            <a href="../index.php" class="text-decoration-none small text-muted">
                <i class="bi bi-arrow-left me-1"></i> Volver a la portada de GQ-Turismo
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
