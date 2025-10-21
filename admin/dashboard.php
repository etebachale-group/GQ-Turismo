<?php
session_start();

// Verificar si el usuario ha iniciado sesión y tiene el tipo de usuario correcto
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['agencia', 'guia', 'local', 'super_admin'])) {
    header("Location: login.php"); // Redirigir a la página de login si no está autorizado
    exit();
}

include '../includes/db_connect.php';

// Obtener el nombre del usuario para mostrarlo en el dashboard
$user_name = $_SESSION['user_nombre'];
$user_type = $_SESSION['user_type'];
$user_id = $_SESSION['user_id'];

// Datos para el dashboard
$dashboard_data = [];

if ($user_type === 'super_admin') {
    // Contar usuarios
    $result_users = $conn->query("SELECT COUNT(*) as count FROM usuarios");
    $dashboard_data['total_users'] = $result_users->fetch_assoc()['count'];

    // Contar agencias
    $result_agencias = $conn->query("SELECT COUNT(*) as count FROM agencias");
    $dashboard_data['total_agencias'] = $result_agencias->fetch_assoc()['count'];

    // Contar guías
    $result_guias = $conn->query("SELECT COUNT(*) as count FROM guias_turisticos");
    $dashboard_data['total_guias'] = $result_guias->fetch_assoc()['count'];

    // Contar locales
    $result_locales = $conn->query("SELECT COUNT(*) as count FROM lugares_locales");
    $dashboard_data['total_locales'] = $result_locales->fetch_assoc()['count'];

    // Contar pedidos pendientes
    $result_pedidos_pendientes = $conn->query("SELECT COUNT(*) as count FROM pedidos_servicios WHERE estado = 'pendiente'");
    $dashboard_data['pedidos_pendientes'] = $result_pedidos_pendientes->fetch_assoc()['count'];

} else if (in_array($user_type, ['agencia', 'guia', 'local'])) {
    $provider_table = '';
    $provider_id_field = '';
    $item_name_field = '';

    if ($user_type === 'agencia') { $provider_table = 'agencias'; $provider_id_field = 'id_agencia'; $item_name_field = 'nombre_agencia'; }
    else if ($user_type === 'guia') { $provider_table = 'guias_turisticos'; $provider_id_field = 'id_guia'; $item_name_field = 'nombre_guia'; }
    else if ($user_type === 'local') { $provider_table = 'lugares_locales'; $provider_id_field = 'id_local'; $item_name_field = 'nombre_local'; }

    $stmt_provider_info = $conn->prepare("SELECT id, " . $item_name_field . " as name FROM " . $provider_table . " WHERE id_usuario = ?");
    $stmt_provider_info->bind_param("i", $user_id);
    $stmt_provider_info->execute();
    $result_provider_info = $stmt_provider_info->get_result();
    $provider_entity = $result_provider_info->fetch_assoc();
    $provider_entity_id = $provider_entity['id'] ?? null;
    $dashboard_data['provider_name'] = $provider_entity['name'] ?? 'N/A';
    $stmt_provider_info->close();

    if ($provider_entity_id) {
        // Contar pedidos por estado
        $stmt_pedidos_pendientes = $conn->prepare("SELECT COUNT(*) as count FROM pedidos_servicios WHERE tipo_proveedor = ? AND id_proveedor = ? AND estado = 'pendiente'");
        $stmt_pedidos_pendientes->bind_param("si", $user_type, $provider_entity_id);
        $stmt_pedidos_pendientes->execute();
        $dashboard_data['pedidos_pendientes'] = $stmt_pedidos_pendientes->get_result()->fetch_assoc()['count'];
        $stmt_pedidos_pendientes->close();

        $stmt_pedidos_confirmados = $conn->prepare("SELECT COUNT(*) as count FROM pedidos_servicios WHERE tipo_proveedor = ? AND id_proveedor = ? AND estado = 'confirmado'");
        $stmt_pedidos_confirmados->bind_param("si", $user_type, $provider_entity_id);
        $stmt_pedidos_confirmados->execute();
        $dashboard_data['pedidos_confirmados'] = $stmt_pedidos_confirmados->get_result()->fetch_assoc()['count'];
        $stmt_pedidos_confirmados->close();

        // Calcular ingresos (solo para pedidos completados)
        $stmt_ingresos = $conn->prepare("SELECT SUM(precio_total) as total_income FROM pedidos_servicios WHERE tipo_proveedor = ? AND id_proveedor = ? AND estado = 'completado'");
        $stmt_ingresos->bind_param("si", $user_type, $provider_entity_id);
        $stmt_ingresos->execute();
        $dashboard_data['total_income'] = $stmt_ingresos->get_result()->fetch_assoc()['total_income'] ?? 0;
        $stmt_ingresos->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - GQ-TURISMO</title>
    <!-- Bootstrap CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Bienvenido, <?= htmlspecialchars($user_name) ?>!</h1>
                </div>

                <?php if ($user_type === 'super_admin'): ?>
                    <p>Resumen general del sistema.</p>
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card text-white bg-primary h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Total Usuarios</h5>
                                    <p class="card-text display-4"><?= $dashboard_data['total_users'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card text-white bg-success h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Total Agencias</h5>
                                    <p class="card-text display-4"><?= $dashboard_data['total_agencias'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card text-white bg-info h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Total Guías</h5>
                                    <p class="card-text display-4"><?= $dashboard_data['total_guias'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card text-white bg-warning h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Total Locales</h5>
                                    <p class="card-text display-4"><?= $dashboard_data['total_locales'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card text-white bg-danger h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Pedidos Pendientes</h5>
                                    <p class="card-text display-4"><?= $dashboard_data['pedidos_pendientes'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="manage_users.php" class="btn btn-primary mt-3">Gestionar Usuarios</a>
                    <a href="manage_publicidad_carousel.php" class="btn btn-secondary mt-3">Gestionar Publicidad</a>

                <?php elseif (in_array($user_type, ['agencia', 'guia', 'local'])): ?>
                    <p>Resumen de tu actividad como <?= htmlspecialchars($user_type) ?>.</p>
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card text-white bg-success h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Ingresos Completados</h5>
                                    <p class="card-text display-4"><?= number_format($dashboard_data['total_income'] ?? 0, 2) ?> €</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card text-white bg-warning h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Pedidos Pendientes</h5>
                                    <p class="card-text display-4"><?= $dashboard_data['pedidos_pendientes'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card text-white bg-info h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Pedidos Confirmados</h5>
                                    <p class="card-text display-4"><?= $dashboard_data['pedidos_confirmados'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($user_type === 'agencia'): ?>
                        <a href="manage_agencias.php" class="btn btn-primary mt-3">Gestionar mi Agencia</a>
                    <?php elseif ($user_type === 'guia'): ?>
                        <a href="manage_guias.php" class="btn btn-primary mt-3">Gestionar mi Perfil de Guía</a>
                    <?php elseif ($user_type === 'local'): ?>
                        <a href="manage_locales.php" class="btn btn-primary mt-3">Gestionar mi Local</a>
                    <?php endif; ?>

                <?php else: ?>
                    <p>Desde aquí puedes gestionar los destinos y las reservas de tu plataforma.</p>

                    <!-- Contenido del Dashboard -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card text-white bg-primary mb-3">
                                <div class="card-header">Destinos</div>
                                <div class="card-body">
                                    <h5 class="card-title">Gestionar Destinos</h5>
                                    <p class="card-text">Añade, edita o elimina destinos turísticos.</p>
                                    <a href="manage_destinos.php" class="btn btn-light">Ir a Destinos</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card text-white bg-success mb-3">
                                <div class="card-header">Reservas</div>
                                <div class="card-body">
                                    <h5 class="card-title">Gestionar Reservas</h5>
                                    <p class="card-text">Revisa y actualiza el estado de las reservas.</p>
                                    <a href="reservas.php" class="btn btn-light">Ir a Reservas</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
