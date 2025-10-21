<?php
  session_start();
  require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GQ-Turismo - Explora Guinea Ecuatorial</title>

  <!-- Google Fonts: Poppins & Lato -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- AOS CSS -->
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/responsive.css">

  <!-- Logo -->
  <link rel="icon" href="assets/img/logo.png" type="image/x-icon">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">
      <img src="assets/img/logo.png" alt="GQ-Turismo Logo" width="30" height="30" class="d-inline-block align-text-top">
      GQ-Turismo
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Inicio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="about.php">Sobre Nosotros</a>
        </li>
        <?php if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] === 'turista'): ?>
        <li class="nav-item">
          <a class="nav-link" href="destinos.php">Destinos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="agencias.php">Agencias</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="guias.php">Guías</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="locales.php">Locales</a>
        </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'turista'): ?>
        <li class="nav-item">
          <a class="nav-link" href="itinerario.php">Mi Itinerario</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="reservas.php">Reservas</a>
        </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_id']) && in_array($_SESSION['user_type'], ['agencia', 'guia', 'local', 'super_admin'])): ?>
        <li class="nav-item">
          <a class="nav-link" href="admin/dashboard.php">Dashboard</a>
        </li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link" href="contacto.php">Contacto</a>
        </li>
      </ul>
      <ul class="navbar-nav ms-lg-3">
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i> Bienvenido, <?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#">Mi Perfil</a></li>
                    <?php if ($_SESSION['user_type'] === 'turista'): ?>
                    <li><a class="dropdown-item" href="itinerario.php">Mi Itinerario</a></li>
                    <li><a class="dropdown-item" href="mis_pedidos.php">Mis Pedidos</a></li>
                    <?php endif; ?>
                    <?php if (in_array($_SESSION['user_type'], ['agencia', 'guia', 'local', 'super_admin'])): ?>
                    <li><a class="dropdown-item" href="admin/dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Content Wrapper -->
<main class="flex-grow-1">