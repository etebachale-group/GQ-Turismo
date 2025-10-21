<?php
session_start();

// Verificar si el usuario ha iniciado sesión y es un super_admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'super_admin') {
    header("Location: login.php"); // Redirigir a la página de login si no está autorizado
    exit();
}

include '../includes/db_connect.php';

$message = '';

// Lógica para actualizar tipo de usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_type') {
    $user_id = $_POST['user_id'];
    $new_type = $_POST['new_type'];

    // Validar el nuevo tipo de usuario
    $allowed_types = ['turista', 'agencia', 'guia', 'local', 'super_admin'];
    if (!in_array($new_type, $allowed_types)) {
        $message = "<div class='alert alert-danger'>Tipo de usuario no válido.</div>";
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET tipo_usuario = ? WHERE id = ?");
        $stmt->bind_param("si", $new_type, $user_id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Tipo de usuario actualizado con éxito.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error al actualizar el tipo de usuario: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Lógica para eliminar usuario
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    // Prevenir que el super_admin se elimine a sí mismo
    if ($user_id == $_SESSION['user_id']) {
        $message = "<div class='alert alert-danger'>No puedes eliminar tu propia cuenta de super administrador.</div>";
    } else {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Usuario eliminado con éxito.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error al eliminar el usuario: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Obtener todos los usuarios
$query = "SELECT id, nombre, email, tipo_usuario, fecha_registro FROM usuarios ORDER BY fecha_registro DESC";
$result = $conn->query($query);
$users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_destinos.php">Destinos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reservas.php">Reservas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="manage_users.php">Usuarios</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestionar Usuarios</h1>
                </div>

                <?php if ($message): ?>
                    <?= $message ?>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Tipo de Usuario</th>
                                <th>Fecha de Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['id']) ?></td>
                                        <td><?= htmlspecialchars($user['nombre']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <form action="manage_users.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="update_type">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <select name="new_type" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="turista" <?= ($user['tipo_usuario'] == 'turista') ? 'selected' : '' ?>>Turista</option>
                                                    <option value="agencia" <?= ($user['tipo_usuario'] == 'agencia') ? 'selected' : '' ?>>Agencia de Vuelos</option>
                                                    <option value="guia" <?= ($user['tipo_usuario'] == 'guia') ? 'selected' : '' ?>>Guía Turístico</option>
                                                    <option value="local" <?= ($user['tipo_usuario'] == 'local') ? 'selected' : '' ?>>Lugar/Local</option>
                                                    <option value="super_admin" <?= ($user['tipo_usuario'] == 'super_admin') ? 'selected' : '' ?>>Super Admin</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td><?= htmlspecialchars($user['fecha_registro']) ?></td>
                                        <td>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="manage_users.php?action=delete&id=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar a este usuario?');">Eliminar</a>
                                            <?php else: ?>
                                                <button class="btn btn-danger btn-sm" disabled>Eliminar</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No hay usuarios registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
