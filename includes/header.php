<?php
// Evitar inicio de sesión duplicada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir db_connect solo si no está ya incluido y no se ha cargado previamente
if (!isset($conn) && !isset($GLOBALS['db_connected'])) {
    require_once __DIR__ . '/db_connect.php';
    $GLOBALS['db_connected'] = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GQ-Turismo - Explora Guinea Ecuatorial</title>

  <!-- Google Fonts: Inter & Poppins (Modern) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- AOS CSS -->
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <link rel="stylesheet" href="assets/css/modern-ui.css">
  <link rel="stylesheet" href="assets/css/mobile-enhancements.css">

  <!-- Logo -->
  <link rel="icon" href="assets/img/logo.png" type="image/x-icon">
</head>
<body>

<!-- Modern Navigation -->
<nav class="navbar">
  <div class="navbar-container">
    <a class="navbar-brand" href="index.php">
      <img src="assets/img/logo.png" alt="GQ-Turismo Logo">
      <span>GQ-Turismo</span>
    </a>
    
    <!-- Desktop Menu -->
    <ul class="navbar-menu">
      <li><a class="nav-link" href="index.php"><i class="bi bi-house-door"></i> Inicio</a></li>
      <?php if (!isset($_SESSION['user_id']) || (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'turista')): ?>
      <li><a class="nav-link" href="destinos.php"><i class="bi bi-compass"></i> Destinos</a></li>
      <li><a class="nav-link" href="agencias.php"><i class="bi bi-building"></i> Agencias</a></li>
      <li><a class="nav-link" href="guias.php"><i class="bi bi-person-badge"></i> Guías</a></li>
      <li><a class="nav-link" href="locales.php"><i class="bi bi-shop"></i> Locales</a></li>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && in_array($_SESSION['user_type'], ['agencia', 'guia', 'local', 'super_admin'])): ?>
      <li><a class="nav-link" href="admin/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
      <?php endif; ?>
      
      <li><a class="nav-link" href="contacto.php"><i class="bi bi-envelope"></i> Contacto</a></li>
      
      <?php if (isset($_SESSION['user_id'])): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'turista'): ?>
            <li><a class="dropdown-item" href="itinerario.php"><i class="bi bi-map"></i> Mi Itinerario</a></li>
            <li><a class="dropdown-item" href="mis_pedidos.php"><i class="bi bi-bag"></i> Mis Pedidos</a></li>
            <li><a class="dropdown-item" href="mis_mensajes.php"><i class="bi bi-chat-dots"></i> Mensajes</a></li>
            <li><hr class="dropdown-divider"></li>
            <?php endif; ?>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a></li>
          </ul>
        </li>
      <?php else: ?>
        <li><a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="bi bi-door-open"></i> Acceder</a></li>
      <?php endif; ?>
    </ul>
    
    <!-- Mobile Toggle -->
    <button class="navbar-toggle" id="navToggle" aria-label="Toggle navigation">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</nav>

<!-- Mobile Menu -->
<div class="navbar-mobile" id="navMobile">
  <ul class="navbar-mobile-menu">
    <li><a class="nav-link" href="index.php"><i class="bi bi-house-door-fill"></i> Inicio</a></li>
    <?php if (!isset($_SESSION['user_id']) || (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'turista')): ?>
    <li><a class="nav-link" href="destinos.php"><i class="bi bi-compass-fill"></i> Destinos</a></li>
    <li><a class="nav-link" href="agencias.php"><i class="bi bi-building-fill"></i> Agencias</a></li>
    <li><a class="nav-link" href="guias.php"><i class="bi bi-person-badge-fill"></i> Guías Turísticos</a></li>
    <li><a class="nav-link" href="locales.php"><i class="bi bi-shop"></i> Lugares Locales</a></li>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'turista'): ?>
    <li><a class="nav-link" href="itinerario.php"><i class="bi bi-map-fill"></i> Mi Itinerario</a></li>
    <li><a class="nav-link" href="mis_pedidos.php"><i class="bi bi-bag-fill"></i> Mis Pedidos</a></li>
    <li><a class="nav-link" href="reservas.php"><i class="bi bi-calendar-check-fill"></i> Reservas</a></li>
    <li><a class="nav-link" href="mis_mensajes.php"><i class="bi bi-chat-dots-fill"></i> Mensajes</a></li>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && in_array($_SESSION['user_type'], ['agencia', 'guia', 'local', 'super_admin'])): ?>
    <li><a class="nav-link" href="admin/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
    <?php endif; ?>
    
    <li><a class="nav-link" href="about.php"><i class="bi bi-info-circle-fill"></i> Sobre Nosotros</a></li>
    <li><a class="nav-link" href="contacto.php"><i class="bi bi-envelope-fill"></i> Contacto</a></li>
    
    <?php if (isset($_SESSION['user_id'])): ?>
    <li><hr style="border-color: var(--gray-300); margin: var(--space-md) 0;"></li>
    <li><a class="nav-link" href="logout.php" style="color: var(--danger);"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
    <?php else: ?>
    <li><hr style="border-color: var(--gray-300); margin: var(--space-md) 0;"></li>
    <li><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="bi bi-door-open-fill"></i> Iniciar Sesión</a></li>
    <?php endif; ?>
  </ul>
</div>

<!-- Bottom Navigation (Mobile Only) -->
<?php if (!isset($_SESSION['user_id']) || (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'turista')): ?>
<div class="bottom-nav">
  <ul class="bottom-nav-menu">
    <li class="bottom-nav-item">
      <a href="index.php" class="bottom-nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
        <i class="bi bi-house-fill"></i>
        <span>Inicio</span>
      </a>
    </li>
    <li class="bottom-nav-item">
      <a href="destinos.php" class="bottom-nav-link <?= basename($_SERVER['PHP_SELF']) == 'destinos.php' ? 'active' : '' ?>">
        <i class="bi bi-compass-fill"></i>
        <span>Explorar</span>
      </a>
    </li>
    <?php if (isset($_SESSION['user_id'])): ?>
    <li class="bottom-nav-item">
      <a href="mis_pedidos.php" class="bottom-nav-link <?= basename($_SERVER['PHP_SELF']) == 'mis_pedidos.php' ? 'active' : '' ?>">
        <i class="bi bi-bag-fill"></i>
        <span>Pedidos</span>
      </a>
    </li>
    <li class="bottom-nav-item">
      <a href="mis_mensajes.php" class="bottom-nav-link <?= basename($_SERVER['PHP_SELF']) == 'mis_mensajes.php' ? 'active' : '' ?>">
        <i class="bi bi-chat-dots-fill"></i>
        <span>Mensajes</span>
      </a>
    </li>
    <li class="bottom-nav-item">
      <a href="itinerario.php" class="bottom-nav-link <?= basename($_SERVER['PHP_SELF']) == 'itinerario.php' ? 'active' : '' ?>">
        <i class="bi bi-person-circle"></i>
        <span>Perfil</span>
      </a>
    </li>
    <?php else: ?>
    <li class="bottom-nav-item">
      <a href="agencias.php" class="bottom-nav-link <?= basename($_SERVER['PHP_SELF']) == 'agencias.php' ? 'active' : '' ?>">
        <i class="bi bi-building-fill"></i>
        <span>Agencias</span>
      </a>
    </li>
    <li class="bottom-nav-item">
      <a href="guias.php" class="bottom-nav-link <?= basename($_SERVER['PHP_SELF']) == 'guias.php' ? 'active' : '' ?>">
        <i class="bi bi-person-badge-fill"></i>
        <span>Guías</span>
      </a>
    </li>
    <li class="bottom-nav-item">
      <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="bottom-nav-link">
        <i class="bi bi-person-circle"></i>
        <span>Acceder</span>
      </a>
    </li>
    <?php endif; ?>
  </ul>
</div>
<?php endif; ?>

<!-- Main Content Wrapper -->
<main class="flex-grow-1">