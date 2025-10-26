<!-- Navbar Responsivo Mejorado -->
<style>
    /* Navbar Principal */
    .navbar-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        padding: 0.75rem 0;
    }
    
    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        color: white !important;
    }
    
    .navbar-toggler {
        border: none;
        padding: 0.5rem;
        background: rgba(255,255,255,0.1);
        border-radius: 8px;
    }
    
    .navbar-toggler:focus {
        box-shadow: none;
        background: rgba(255,255,255,0.2);
    }
    
    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    
    .nav-link {
        color: rgba(255,255,255,0.9) !important;
        padding: 0.5rem 1rem !important;
        border-radius: 6px;
        transition: all 0.3s ease;
        margin: 0.25rem 0;
    }
    
    .nav-link:hover {
        background: rgba(255,255,255,0.1);
        color: white !important;
    }
    
    .nav-link.active {
        background: rgba(255,255,255,0.2);
        color: white !important;
    }
    
    .user-menu {
        background: rgba(255,255,255,0.1);
        border-radius: 8px;
        padding: 0.5rem 1rem;
    }
    
    /* Mobile Optimizations */
    @media (max-width: 991px) {
        .navbar-collapse {
            background: rgba(0,0,0,0.05);
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 8px;
        }
        
        .navbar-nav {
            gap: 0.5rem;
        }
        
        .user-menu {
            margin-top: 1rem;
            padding: 1rem;
        }
        
        .dropdown-menu {
            border: none;
            background: rgba(255,255,255,0.95);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    }
    
    /* Dropdown */
    .dropdown-menu {
        border-radius: 8px;
        border: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        margin-top: 0.5rem;
    }
    
    .dropdown-item {
        padding: 0.75rem 1.25rem;
        transition: all 0.3s ease;
    }
    
    .dropdown-item:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .dropdown-item i {
        margin-right: 0.5rem;
        width: 20px;
        text-align: center;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container-fluid px-3 px-md-4">
        <a class="navbar-brand" href="<?= ($user_type === 'admin' || $user_type === 'superadmin') ? '/GQ-Turismo/admin/dashboard.php' : '/GQ-Turismo/index.php' ?>">
            <i class="bi bi-geo-alt-fill"></i> GQ Turismo
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" 
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($user_type === 'turista' || !isset($user_type)): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/GQ-Turismo/index.php">
                            <i class="bi bi-house-fill"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/GQ-Turismo/destinos.php">
                            <i class="bi bi-map-fill"></i> Destinos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/GQ-Turismo/guias.php">
                            <i class="bi bi-person-badge-fill"></i> Guías
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/GQ-Turismo/agencias.php">
                            <i class="bi bi-building"></i> Agencias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/GQ-Turismo/locales.php">
                            <i class="bi bi-shop"></i> Locales
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/GQ-Turismo/itinerario.php">
                            <i class="bi bi-calendar-check"></i> Mis Itinerarios
                        </a>
                    </li>
                    <?php endif; ?>
                <?php elseif ($user_type === 'admin' || $user_type === 'superadmin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/GQ-Turismo/admin/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear-fill"></i> Gestión
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/GQ-Turismo/admin/manage_destinos.php">
                                <i class="bi bi-map"></i> Destinos
                            </a></li>
                            <li><a class="dropdown-item" href="/GQ-Turismo/admin/manage_guias.php">
                                <i class="bi bi-person-badge"></i> Guías
                            </a></li>
                            <li><a class="dropdown-item" href="/GQ-Turismo/admin/manage_agencias.php">
                                <i class="bi bi-building"></i> Agencias
                            </a></li>
                            <li><a class="dropdown-item" href="/GQ-Turismo/admin/manage_locales.php">
                                <i class="bi bi-shop"></i> Locales
                            </a></li>
                            <li><a class="dropdown-item" href="/GQ-Turismo/admin/manage_usuarios.php">
                                <i class="bi bi-people"></i> Usuarios
                            </a></li>
                        </ul>
                    </li>
                <?php elseif ($user_type === 'guia' || $user_type === 'agencia' || $user_type === 'local'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/GQ-Turismo/admin/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/GQ-Turismo/admin/mis_pedidos.php">
                            <i class="bi bi-clipboard-check"></i> Mis Pedidos
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <!-- User Menu -->
            <div class="user-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-2"></i>
                            <span><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/GQ-Turismo/admin/perfil.php">
                                <i class="bi bi-person"></i> Mi Perfil
                            </a></li>
                            <li><a class="dropdown-item" href="/GQ-Turismo/mis_mensajes.php">
                                <i class="bi bi-envelope"></i> Mensajes
                            </a></li>
                            <?php if ($user_type === 'turista'): ?>
                            <li><a class="dropdown-item" href="/GQ-Turismo/mis_pedidos.php">
                                <i class="bi bi-bag"></i> Mis Reservas
                            </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/GQ-Turismo/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="d-flex gap-2">
                        <a href="/GQ-Turismo/index.php#login" class="btn btn-light btn-sm">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </a>
                        <a href="/GQ-Turismo/index.php#register" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-person-plus"></i> Registrarse
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Ensure Bootstrap JS is loaded -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-close mobile menu after clicking a link
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
    const navCollapse = document.getElementById('navbarMain');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                const bsCollapse = new bootstrap.Collapse(navCollapse, {
                    toggle: false
                });
                bsCollapse.hide();
            }
        });
    });
});
</script>
