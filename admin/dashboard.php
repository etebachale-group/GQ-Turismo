<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión y tiene el tipo de usuario correcto
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['agencia', 'guia', 'local', 'super_admin'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

// Obtener el nombre del usuario para mostrarlo en el dashboard
$user_name = $_SESSION['user_nombre'];
$user_type = $_SESSION['user_type'];
$user_id = $_SESSION['user_id'];

// Configurar título de página
$page_title = "Dashboard";

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

    // Contar destinos
    $result_destinos = $conn->query("SELECT COUNT(*) as count FROM destinos");
    $dashboard_data['total_destinos'] = $result_destinos->fetch_assoc()['count'];

    // Contar pedidos pendientes
    $result_pedidos_pendientes = $conn->query("SELECT COUNT(*) as count FROM pedidos_servicios WHERE estado = 'pendiente'");
    $dashboard_data['pedidos_pendientes'] = $result_pedidos_pendientes->fetch_assoc()['count'];
    
    // Contar pedidos completados
    $result_pedidos_completados = $conn->query("SELECT COUNT(*) as count FROM pedidos_servicios WHERE estado = 'completado'");
    $dashboard_data['pedidos_completados'] = $result_pedidos_completados->fetch_assoc()['count'];
    
    // Calcular ingresos totales
    $result_ingresos = $conn->query("SELECT SUM(precio_total) as total FROM pedidos_servicios WHERE estado = 'completado'");
    $dashboard_data['total_income'] = $result_ingresos->fetch_assoc()['total'] ?? 0;

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

// Incluir header
include 'admin_header.php';
?>

<!-- Page Header -->
<div class="admin-page-header">
    <h1>Dashboard</h1>
    <p>Bienvenido, <strong><?= htmlspecialchars($user_name) ?></strong> - <?php
        $type_labels = [
            'super_admin' => 'Super Administrador',
            'agencia' => 'Agencia de Vuelos',
            'guia' => 'Guía Turístico',
            'local' => 'Lugar Local'
        ];
        echo $type_labels[$user_type] ?? 'Usuario';
    ?></p>
</div>

<?php if ($user_type === 'super_admin'): ?>
    
    <!-- Stats Grid para Super Admin -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-content">
                <h3><?= $dashboard_data['total_users'] ?? 0 ?></h3>
                <p>Total Usuarios</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="bi bi-building-fill"></i>
            </div>
            <div class="stat-content">
                <h3><?= $dashboard_data['total_agencias'] ?? 0 ?></h3>
                <p>Agencias de Vuelos</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div class="stat-content">
                <h3><?= $dashboard_data['total_guias'] ?? 0 ?></h3>
                <p>Guías Turísticos</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon secondary">
                <i class="bi bi-shop"></i>
            </div>
            <div class="stat-content">
                <h3><?= $dashboard_data['total_locales'] ?? 0 ?></h3>
                <p>Lugares Locales</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div class="stat-content">
                <h3><?= $dashboard_data['total_destinos'] ?? 0 ?></h3>
                <p>Destinos Turísticos</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-content">
                <h3><?= $dashboard_data['pedidos_pendientes'] ?? 0 ?></h3>
                <p>Pedidos Pendientes</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="stat-content">
                <h3><?= $dashboard_data['pedidos_completados'] ?? 0 ?></h3>
                <p>Pedidos Completados</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="stat-content">
                <h3><?= number_format($dashboard_data['total_income'] ?? 0, 2) ?> €</h3>
                <p>Ingresos Totales</p>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="admin-page-header" style="margin-top: var(--space-xl);">
        <h2 style="font-size: 1.5rem; margin-bottom: var(--space-md);">Acciones Rápidas</h2>
        <div style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
            <a href="manage_users.php" class="btn btn-primary">
                <i class="bi bi-people"></i> Gestionar Usuarios
            </a>
            <a href="manage_destinos.php" class="btn btn-secondary">
                <i class="bi bi-geo-alt"></i> Gestionar Destinos
            </a>
            <a href="manage_agencias.php" class="btn btn-success">
                <i class="bi bi-building"></i> Gestionar Agencias
            </a>
            <a href="manage_guias.php" class="btn btn-info">
                <i class="bi bi-person-badge"></i> Gestionar Guías
            </a>
            <a href="manage_locales.php" class="btn btn-warning">
                <i class="bi bi-shop"></i> Gestionar Locales
            </a>
            <a href="manage_publicidad_carousel.php" class="btn btn-outline-primary">
                <i class="bi bi-images"></i> Publicidad
            </a>
            <a href="messages.php" class="btn btn-outline-secondary">
                <i class="bi bi-chat-dots"></i> Mensajes
            </a>
            <a href="reservas.php" class="btn btn-outline-success">
                <i class="bi bi-calendar-check"></i> Reservas
            </a>
        </div>
    </div>

<?php elseif (in_array($user_type, ['agencia', 'guia', 'local'])): ?>
    
    <!-- Stats Grid para Proveedores -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="stat-content">
                <h3><?= number_format($dashboard_data['total_income'] ?? 0, 2) ?> €</h3>
                <p>Ingresos Completados</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-content">
                <h3><?= $dashboard_data['pedidos_pendientes'] ?? 0 ?></h3>
                <p>Pedidos Pendientes</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?= $dashboard_data['pedidos_confirmados'] ?? 0 ?></h3>
                <p>Pedidos Confirmados</p>
            </div>
        </div>
    </div>
    
    <!-- Provider Info -->
    <div class="admin-page-header" style="margin-top: var(--space-xl);">
        <h2 style="font-size: 1.5rem; margin-bottom: var(--space-sm);">
            Tu Perfil: <?= htmlspecialchars($dashboard_data['provider_name']) ?>
        </h2>
        <p style="margin-bottom: var(--space-md);">
            Gestiona tu información y revisa tus pedidos desde aquí.
        </p>
        <div style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
            <?php if ($user_type === 'agencia'): ?>
                <a href="manage_agencias.php" class="btn btn-primary">
                    <i class="bi bi-building"></i> Gestionar mi Agencia
                </a>
            <?php elseif ($user_type === 'guia'): ?>
                <a href="manage_guias.php" class="btn btn-primary">
                    <i class="bi bi-person-badge"></i> Gestionar mi Perfil
                </a>
            <?php elseif ($user_type === 'local'): ?>
                <a href="manage_locales.php" class="btn btn-primary">
                    <i class="bi bi-shop"></i> Gestionar mi Local
                </a>
            <?php endif; ?>
            
            <a href="mis_pedidos.php" class="btn btn-success">
                <i class="bi bi-bag-check"></i> Mis Pedidos Recibidos
            </a>
            <a href="../mis_mensajes.php" class="btn btn-outline-primary">
                <i class="bi bi-chat-dots"></i> Mensajes
            </a>
        </div>
    </div>

<?php else: ?>
    
    <!-- Dashboard genérico para otros tipos de usuarios -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card card-hover-lift">
                <div class="card-body" style="padding: var(--space-xl);">
                    <div style="display: flex; align-items: center; gap: var(--space-lg); margin-bottom: var(--space-md);">
                        <div style="width: 56px; height: 56px; background: var(--gradient-primary); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-geo-alt-fill" style="font-size: 1.75rem; color: var(--white);"></i>
                        </div>
                        <h3 style="margin: 0; font-size: 1.5rem;">Gestionar Destinos</h3>
                    </div>
                    <p style="color: var(--gray-600); margin-bottom: var(--space-lg);">
                        Añade, edita o elimina destinos turísticos de la plataforma.
                    </p>
                    <a href="manage_destinos.php" class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> Ir a Destinos
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card card-hover-lift">
                <div class="card-body" style="padding: var(--space-xl);">
                    <div style="display: flex; align-items: center; gap: var(--space-lg); margin-bottom: var(--space-md);">
                        <div style="width: 56px; height: 56px; background: var(--gradient-secondary); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-calendar-check-fill" style="font-size: 1.75rem; color: var(--dark);"></i>
                        </div>
                        <h3 style="margin: 0; font-size: 1.5rem;">Gestionar Reservas</h3>
                    </div>
                    <p style="color: var(--gray-600); margin-bottom: var(--space-lg);">
                        Revisa y actualiza el estado de todas las reservas.
                    </p>
                    <a href="reservas.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-right"></i> Ir a Reservas
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php include 'admin_footer.php'; ?>
