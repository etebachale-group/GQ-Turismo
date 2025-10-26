<?php
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['user_nombre'] ?? 'Usuario';
$user_type = $_SESSION['user_type'] ?? '';
$base_url = '../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $page_title ?? 'Dashboard' ?> - GQ-Turismo Admin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/modern-ui.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/mobile-optimization.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/admin-mobile.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/mobile-fixes.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/mobile-responsive.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/mobile-responsive-admin.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/mobile-responsive-final.css">

    <!-- Logo -->
    <link rel="icon" href="<?= $base_url ?>assets/img/logo.png" type="image/x-icon">
    
    <style>
        :root {
            --admin-sidebar-width: 280px;
        }
        
        body {
            background: var(--gray-100);
            padding-top: 72px;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: calc(100vh - 72px);
        }
        
        .admin-sidebar {
            width: var(--admin-sidebar-width);
            background: var(--white);
            box-shadow: var(--shadow-lg);
            position: fixed;
            top: 72px;
            bottom: 0;
            left: 0;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .admin-sidebar-header {
            padding: var(--space-xl);
            border-bottom: 1px solid var(--gray-200);
            background: var(--gradient-primary);
            color: var(--white);
        }
        
        .admin-sidebar-header h6 {
            margin: 0;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
        }
        
        .admin-sidebar-header p {
            margin: var(--space-sm) 0 0 0;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .admin-sidebar-menu {
            list-style: none;
            padding: var(--space-md);
            margin: 0;
        }
        
        .admin-sidebar-menu li {
            margin-bottom: var(--space-sm);
        }
        
        .admin-sidebar-link {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            padding: var(--space-md) var(--space-lg);
            color: var(--gray-700);
            text-decoration: none;
            border-radius: var(--radius-lg);
            transition: all var(--transition-fast);
            font-weight: 500;
        }
        
        .admin-sidebar-link:hover {
            background: var(--gray-100);
            color: var(--primary);
        }
        
        .admin-sidebar-link.active {
            background: var(--gradient-primary);
            color: var(--white);
        }
        
        .admin-sidebar-link i {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }
        
        .admin-content {
            flex: 1;
            margin-left: var(--admin-sidebar-width);
            padding: var(--space-xl);
        }
        
        .admin-page-header {
            background: var(--white);
            padding: var(--space-xl);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            margin-bottom: var(--space-xl);
        }
        
        .admin-page-header h1 {
            margin: 0;
            font-size: 1.875rem;
            color: var(--dark);
        }
        
        .admin-page-header p {
            margin: var(--space-sm) 0 0 0;
            color: var(--gray-600);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-lg);
            margin-bottom: var(--space-xl);
        }
        
        .stat-card {
            background: var(--white);
            padding: var(--space-xl);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: var(--space-lg);
            transition: all var(--transition-base);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }
        
        .stat-icon {
            width: 64px;
            height: 64px;
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
        }
        
        .stat-icon.primary { background: var(--gradient-primary); color: var(--white); }
        .stat-icon.secondary { background: var(--gradient-secondary); color: var(--dark); }
        .stat-icon.success { background: linear-gradient(135deg, #4caf50 0%, #81c784 100%); color: var(--white); }
        .stat-icon.danger { background: linear-gradient(135deg, #f44336 0%, #e57373 100%); color: var(--white); }
        .stat-icon.info { background: linear-gradient(135deg, #2196f3 0%, #64b5f6 100%); color: var(--white); }
        .stat-icon.warning { background: linear-gradient(135deg, #ff9800 0%, #ffb74d 100%); color: var(--white); }
        
        .stat-content h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            color: var(--dark);
        }
        
        .stat-content p {
            margin: 0;
            color: var(--gray-600);
            font-size: 0.875rem;
        }
        
        /* Mobile Responsive */
        @media (max-width: 991px) {
            .admin-sidebar {
                transform: translateX(-100%);
                z-index: 9999;
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            .admin-content {
                margin-left: 0;
                padding: var(--space-md);
            }
            
            .sidebar-toggle-btn {
                display: flex !important;
            }
        }
        
        .sidebar-toggle-btn {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--white);
            border: none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
            z-index: 10000;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            transition: all 0.3s ease;
            -webkit-tap-highlight-color: transparent;
        }
        
        .sidebar-toggle-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 32px rgba(0,0,0,0.5);
        }
        
        .sidebar-toggle-btn:active {
            transform: scale(0.95);
        }
        
        .sidebar-toggle-btn i {
            pointer-events: none;
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 72px;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }
        
        /* Table Styles */
        .admin-table {
            background: var(--white);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        
        .admin-table table {
            width: 100%;
            margin: 0;
        }
        
        .admin-table thead {
            background: var(--gray-100);
        }
        
        .admin-table th {
            padding: var(--space-lg);
            font-weight: 600;
            color: var(--gray-900);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .admin-table td {
            padding: var(--space-lg);
            border-top: 1px solid var(--gray-200);
        }
        
        .admin-table tbody tr:hover {
            background: var(--gray-50);
        }
        
        .badge {
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-pendiente { background: var(--warning); color: var(--white); }
        .badge-confirmado { background: var(--info); color: var(--white); }
        .badge-completado { background: var(--success); color: var(--white); }
        .badge-cancelado { background: var(--danger); color: var(--white); }
    </style>
</head>
<body>

<!-- Modern Admin Navigation -->
<nav class="navbar">
    <div class="navbar-container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="<?= $base_url ?>assets/img/logo.png" alt="GQ-Turismo">
            <span>GQ-Admin</span>
        </a>
        
        <!-- Desktop Menu -->
        <ul class="navbar-menu">
            <li><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            
            <?php if ($user_type === 'super_admin'): ?>
                <li><a class="nav-link" href="manage_users.php"><i class="bi bi-people"></i> Usuarios</a></li>
            <?php endif; ?>
            
            <li><a class="nav-link" href="messages.php"><i class="bi bi-chat-dots"></i> Mensajes</a></li>
            <li><a class="nav-link" href="<?= $base_url ?>index.php"><i class="bi bi-globe"></i> Sitio Web</a></li>
            
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="adminUserDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user_name) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a></li>
                </ul>
            </li>
        </ul>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggle" id="navToggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>

<!-- Mobile Menu -->
<div class="navbar-mobile" id="navMobile">
    <ul class="navbar-mobile-menu">
        <li><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        
        <?php if ($user_type === 'super_admin'): ?>
            <li><a class="nav-link" href="manage_users.php"><i class="bi bi-people-fill"></i> Usuarios</a></li>
            <li><a class="nav-link" href="manage_destinos.php"><i class="bi bi-geo-alt-fill"></i> Destinos</a></li>
            <li><a class="nav-link" href="manage_agencias.php"><i class="bi bi-building-fill"></i> Agencias</a></li>
            <li><a class="nav-link" href="manage_guias.php"><i class="bi bi-person-badge-fill"></i> Guías</a></li>
            <li><a class="nav-link" href="manage_locales.php"><i class="bi bi-shop"></i> Locales</a></li>
        <?php endif; ?>
        
        <li><a class="nav-link" href="messages.php"><i class="bi bi-chat-dots-fill"></i> Mensajes</a></li>
        <li><a class="nav-link" href="reservas.php"><i class="bi bi-calendar-check-fill"></i> Reservas</a></li>
        <li><a class="nav-link" href="<?= $base_url ?>index.php"><i class="bi bi-globe"></i> Ver Sitio Web</a></li>
        
        <li><hr style="border-color: var(--gray-300); margin: var(--space-md) 0;"></li>
        <li><a class="nav-link" href="logout.php" style="color: var(--danger);"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
    </ul>
</div>

<!-- Admin Layout Wrapper -->
<div class="admin-wrapper">
    
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-header">
            <h6>Panel de Administración</h6>
            <p>
                <?php
                $type_labels = [
                    'super_admin' => 'Super Administrador',
                    'agencia' => 'Agencia de Vuelos',
                    'guia' => 'Guía Turístico',
                    'local' => 'Lugar Local'
                ];
                echo $type_labels[$user_type] ?? 'Usuario';
                ?>
            </p>
        </div>
        
        <ul class="admin-sidebar-menu">
            <li>
                <a href="dashboard.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <?php if ($user_type === 'super_admin'): ?>
            <li>
                <a href="manage_users.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>Gestionar Usuarios</span>
                </a>
            </li>
            <li>
                <a href="manage_destinos.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'manage_destinos.php' ? 'active' : '' ?>">
                    <i class="bi bi-geo-alt"></i>
                    <span>Gestionar Destinos</span>
                </a>
            </li>
            <li>
                <a href="manage_agencias.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'manage_agencias.php' ? 'active' : '' ?>">
                    <i class="bi bi-building"></i>
                    <span>Gestionar Agencias</span>
                </a>
            </li>
            <li>
                <a href="manage_guias.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'manage_guias.php' ? 'active' : '' ?>">
                    <i class="bi bi-person-badge"></i>
                    <span>Gestionar Guías</span>
                </a>
            </li>
            <li>
                <a href="manage_locales.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'manage_locales.php' ? 'active' : '' ?>">
                    <i class="bi bi-shop"></i>
                    <span>Gestionar Locales</span>
                </a>
            </li>
            <li>
                <a href="manage_publicidad_carousel.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'manage_publicidad_carousel.php' ? 'active' : '' ?>">
                    <i class="bi bi-images"></i>
                    <span>Publicidad</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($user_type === 'guia'): ?>
            <li>
                <a href="mis_destinos_guia.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'mis_destinos_guia.php' ? 'active' : '' ?>">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Mis Destinos</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (in_array($user_type, ['guia', 'agencia', 'local'])): ?>
            <li>
                <a href="mis_destinos.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'mis_destinos.php' ? 'active' : '' ?>">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Mis Destinos</span>
                </a>
            </li>
            <?php endif; ?>
            
            <li>
                <a href="messages.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : '' ?>">
                    <i class="bi bi-chat-dots"></i>
                    <span>Mensajes</span>
                </a>
            </li>
            
            <li>
                <a href="reservas.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'reservas.php' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-check"></i>
                    <span>Reservas/Pedidos</span>
                </a>
            </li>
            
            <li style="margin-top: var(--space-xl); padding-top: var(--space-lg); border-top: 1px solid var(--gray-200);">
                <a href="<?= $base_url ?>index.php" class="admin-sidebar-link">
                    <i class="bi bi-globe"></i>
                    <span>Ver Sitio Web</span>
                </a>
            </li>
            
            <li>
                <a href="logout.php" class="admin-sidebar-link" style="color: var(--danger);">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </aside>
    
    <!-- Overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar Toggle Button (Mobile) -->
    <button class="sidebar-toggle-btn" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>
    
    <!-- Main Content -->
    <main class="admin-content">
