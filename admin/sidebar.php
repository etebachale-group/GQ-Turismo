<?php
// Este archivo contendrá la lógica para mostrar los enlaces del sidebar según el user_type.
// Se asume que session_start() ya ha sido llamado y $_SESSION['user_type'] está disponible.

$user_type = $_SESSION['user_type'] ?? '';
?>

<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="dashboard.php">
                    Dashboard
                </a>
            </li>
            <?php if (in_array($user_type, ['super_admin', 'agencia', 'guia', 'local'])): // Todos los proveedores y super admin gestionan destinos y reservas ?>
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
            <li class="nav-item">
                <a class="nav-link" href="messages.php">
                    Mensajes
                </a>
            </li>
            <?php endif; ?>

            <?php if ($user_type === 'super_admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="manage_users.php">
                    Usuarios
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_publicidad_carousel.php">
                    Publicidad/Carousel
                </a>
            </li>
            <?php endif; ?>

            <?php if (in_array($user_type, ['agencia', 'super_admin'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="manage_agencias.php">
                    Agencias
                </a>
            </li>
            <?php endif; ?>

            <?php if (in_array($user_type, ['guia', 'super_admin'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="manage_guias.php">
                    Guías
                </a>
            </li>
            <?php endif; ?>

            <?php if (in_array($user_type, ['local', 'super_admin'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="manage_locales.php">
                    Locales
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
