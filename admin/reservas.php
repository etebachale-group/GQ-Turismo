<?php
session_start();

// Verificar si el usuario ha iniciado sesión y tiene el tipo de usuario correcto
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['agencia', 'guia', 'local', 'super_admin'])) {
    header("Location: login.php"); // Redirigir a la página de login si no está autorizado
    exit();
}

include '../includes/db_connect.php';

// Obtener todas las reservas con el nombre del destino
$query = "SELECT r.id, d.nombre AS destino, u.nombre AS usuario, r.fecha, r.personas, r.estado 
          FROM reservas r 
          JOIN destinos d ON r.id_destino = d.id
          JOIN usuarios u ON r.id_usuario = u.id
          ORDER BY r.fecha DESC";
$result = $conn->query($query);
$reservas = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reservas[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Reservas - Admin</title>
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
                                                    <a class="nav-link" href="manage_destinos.php">
                                                        Destinos
                                                    </a>
                                                </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reservas.php">
                                Reservas
                            </a>
                        </li>
                        <?php if ($_SESSION['user_type'] === 'super_admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_users.php">
                                Usuarios
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (in_array($_SESSION['user_type'], ['agencia', 'super_admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_agencias.php">
                                Agencias
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (in_array($_SESSION['user_type'], ['guia', 'super_admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="manage_guias.php">
                                Guías
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (in_array($_SESSION['user_type'], ['local', 'super_admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_locales.php">
                                Locales
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($_SESSION['user_type'] === 'super_admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="manage_publicidad_carousel.php">
                                Publicidad/Carousel
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestionar Reservas</h1>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Destino</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                                <th>Personas</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reserva['id']); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['destino']); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['fecha']); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['personas']); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['estado']); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-secondary">Editar</a>
                                        <a href="#" class="btn btn-sm btn-outline-danger">Cancelar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>