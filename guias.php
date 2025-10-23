<?php require_once 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="py-5" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);" data-aos="fade-in">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-3 text-white">Guías Turísticos Expertos</h1>
                <p class="lead mb-4 text-white">Conoce Guinea Ecuatorial de la mano de expertos locales certificados que harán de tu viaje una experiencia única e inolvidable.</p>
                <div class="d-flex gap-3 flex-wrap text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-patch-check-fill me-2 fs-5"></i>
                        <span>Certificados</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-translate me-2 fs-5"></i>
                        <span>Multilingües</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-geo-alt-fill me-2 fs-5"></i>
                        <span>Conocimiento Local</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <i class="bi bi-person-badge text-white" style="font-size: 10rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Filtros y Búsqueda -->
<section class="bg-light py-4 border-bottom">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-md-4">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="search-guias" placeholder="Buscar guías...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-lg" id="filter-idiomas">
                    <option value="">Todos los Idiomas</option>
                    <option value="español">Español</option>
                    <option value="frances">Francés</option>
                    <option value="ingles">Inglés</option>
                    <option value="portugues">Portugués</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-lg" id="filter-disponibilidad">
                    <option value="">Disponibilidad</option>
                    <option value="disponible">Disponible</option>
                    <option value="reservado">Reservado</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-lg" id="sort-guias">
                    <option value="nombre">Nombre</option>
                    <option value="precio">Precio</option>
                    <option value="valoracion">Valoración</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Beneficios -->
<section class="py-4 bg-white">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <i class="bi bi-shield-check text-success fs-2 me-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">100% Seguros</h6>
                        <small class="text-muted">Verificados</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <i class="bi bi-chat-dots text-primary fs-2 me-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Contacto Directo</h6>
                        <small class="text-muted">Mensajería</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <i class="bi bi-star-fill text-warning fs-2 me-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Mejor Valorados</h6>
                        <small class="text-muted">4.9/5 estrellas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <i class="bi bi-calendar-check text-info fs-2 me-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Fácil Reserva</h6>
                        <small class="text-muted">En minutos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Listado de Guías -->
<div class="container py-5">
    <div id="guias-list" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <!-- Indicador de Carga -->
        <div class="col-12 text-center" id="loading-spinner">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 text-muted fw-semibold">Cargando guías turísticos...</p>
        </div>
        <!-- Los guías se cargarán aquí dinámicamente -->
    </div>
</div>

<!-- CTA Section -->
<section class="py-5 bg-gradient text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
    <div class="container text-center">
        <h2 class="display-5 fw-bold mb-3">¿Eres Guía Turístico?</h2>
        <p class="lead mb-4">Comparte tu pasión por Guinea Ecuatorial y conecta con viajeros de todo el mundo</p>
        <a href="admin/login.php" class="btn btn-light btn-lg px-5">
            <i class="bi bi-person-plus me-2"></i>Regístrate Como Guía
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
