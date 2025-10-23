<?php require_once 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" data-aos="fade-in">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-3 text-white">Agencias de Viajes</h1>
                <p class="lead mb-4 text-white">Descubre las mejores ofertas y paquetes turísticos para explorar Guinea Ecuatorial con las agencias más confiables.</p>
                <div class="d-flex gap-3 flex-wrap text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <span>Agencias Verificadas</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-check me-2 fs-5"></i>
                        <span>Paquetes Personalizados</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-star-fill me-2 fs-5"></i>
                        <span>Mejor Calidad-Precio</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <i class="bi bi-building text-white" style="font-size: 10rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Filtros y Búsqueda -->
<section class="bg-light py-4 border-bottom">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="search-agencias" placeholder="Buscar agencias...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-lg" id="filter-especialidad">
                    <option value="">Todas las Especialidades</option>
                    <option value="aventura">Aventura</option>
                    <option value="cultural">Cultural</option>
                    <option value="playa">Playa</option>
                    <option value="ecoturismo">Ecoturismo</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-lg" id="sort-agencias">
                    <option value="nombre">Ordenar por Nombre</option>
                    <option value="valoracion">Mejor Valoradas</option>
                    <option value="precio">Mejor Precio</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Estadísticas Rápidas -->
<section class="py-4 bg-white">
    <div class="container">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <div class="card border-0 bg-primary bg-opacity-10 h-100">
                    <div class="card-body">
                        <i class="bi bi-building text-primary fs-1 mb-2"></i>
                        <h3 class="fw-bold mb-0 text-dark" id="total-agencias">-</h3>
                        <small class="text-muted">Agencias Activas</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 bg-success bg-opacity-10 h-100">
                    <div class="card-body">
                        <i class="bi bi-box-seam text-success fs-1 mb-2"></i>
                        <h3 class="fw-bold mb-0 text-dark">150+</h3>
                        <small class="text-muted">Paquetes Disponibles</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 bg-warning bg-opacity-10 h-100">
                    <div class="card-body">
                        <i class="bi bi-star-fill text-warning fs-1 mb-2"></i>
                        <h3 class="fw-bold mb-0 text-dark">4.8</h3>
                        <small class="text-muted">Valoración Media</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 bg-info bg-opacity-10 h-100">
                    <div class="card-body">
                        <i class="bi bi-people-fill text-info fs-1 mb-2"></i>
                        <h3 class="fw-bold mb-0 text-dark">5K+</h3>
                        <small class="text-muted">Clientes Satisfechos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Listado de Agencias -->
<div class="container py-5">
    <div id="agencias-list" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <!-- Indicador de Carga -->
        <div class="col-12 text-center" id="loading-spinner">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 text-muted fw-semibold">Cargando agencias...</p>
        </div>
        <!-- Las agencias se cargarán aquí dinámicamente -->
    </div>
</div>

<!-- CTA Section -->
<section class="py-5 bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container text-center">
        <h2 class="display-5 fw-bold mb-3">¿Eres una Agencia de Viajes?</h2>
        <p class="lead mb-4">Únete a nuestra plataforma y llega a miles de turistas interesados en Guinea Ecuatorial</p>
        <a href="admin/login.php" class="btn btn-light btn-lg px-5">
            <i class="bi bi-person-plus me-2"></i>Registra tu Agencia
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
