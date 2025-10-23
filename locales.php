<?php require_once 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="py-5" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);" data-aos="fade-in">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-3 text-white">Lugares y Locales</h1>
                <p class="lead mb-4 text-white">Descubre los mejores restaurantes, hoteles y sitios de interés para hacer de tu estadía una experiencia completa y memorable.</p>
                <div class="d-flex gap-3 flex-wrap text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-cup-hot me-2 fs-5"></i>
                        <span>Restaurantes</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-house-heart me-2 fs-5"></i>
                        <span>Hoteles</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shop me-2 fs-5"></i>
                        <span>Tiendas</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-camera me-2 fs-5"></i>
                        <span>Atracciones</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <i class="bi bi-shop-window text-white" style="font-size: 10rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Filtros y Búsqueda -->
<section class="bg-light py-4 border-bottom">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-md-5">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="search-locales" placeholder="Buscar restaurantes, hoteles, tiendas...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-lg" id="filter-tipo">
                    <option value="">Todos los Tipos</option>
                    <option value="restaurante">Restaurantes</option>
                    <option value="hotel">Hoteles</option>
                    <option value="tienda">Tiendas</option>
                    <option value="atraccion">Atracciones</option>
                    <option value="cafe">Cafés</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-lg" id="filter-ciudad">
                    <option value="">Todas las Ciudades</option>
                    <option value="malabo">Malabo</option>
                    <option value="bata">Bata</option>
                    <option value="ebebiyin">Ebebiyin</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-lg" id="sort-locales">
                    <option value="nombre">Nombre</option>
                    <option value="valoracion">Mejor Valorados</option>
                    <option value="popular">Más Populares</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Categorías Rápidas -->
<section class="py-4 bg-white">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg-2">
                <button class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-filter="restaurante">
                    <i class="bi bi-cup-hot fs-1 mb-2"></i>
                    <span class="fw-semibold">Restaurantes</span>
                </button>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <button class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-filter="hotel">
                    <i class="bi bi-house-heart fs-1 mb-2"></i>
                    <span class="fw-semibold">Hoteles</span>
                </button>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <button class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-filter="tienda">
                    <i class="bi bi-shop fs-1 mb-2"></i>
                    <span class="fw-semibold">Tiendas</span>
                </button>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <button class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-filter="atraccion">
                    <i class="bi bi-camera fs-1 mb-2"></i>
                    <span class="fw-semibold">Atracciones</span>
                </button>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <button class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-filter="cafe">
                    <i class="bi bi-cup-straw fs-1 mb-2"></i>
                    <span class="fw-semibold">Cafés</span>
                </button>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <button class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-filter="">
                    <i class="bi bi-grid-3x3-gap fs-1 mb-2"></i>
                    <span class="fw-semibold">Ver Todos</span>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Estadísticas -->
<section class="py-3 bg-light">
    <div class="container">
        <div class="row text-center g-3">
            <div class="col-6 col-md-3">
                <div class="p-3">
                    <h3 class="fw-bold text-primary mb-0" id="total-locales">-</h3>
                    <small class="text-muted">Locales Registrados</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3">
                    <h3 class="fw-bold text-success mb-0">200+</h3>
                    <small class="text-muted">Menús Disponibles</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3">
                    <h3 class="fw-bold text-warning mb-0">4.7</h3>
                    <small class="text-muted">Valoración Media</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3">
                    <h3 class="fw-bold text-info mb-0">10K+</h3>
                    <small class="text-muted">Visitas Mensuales</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Listado de Locales -->
<div class="container py-5">
    <div id="locales-list" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <!-- Indicador de Carga -->
        <div class="col-12 text-center" id="loading-spinner">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 text-muted fw-semibold">Cargando locales...</p>
        </div>
        <!-- Los locales se cargarán aquí dinámicamente -->
    </div>
</div>

<!-- CTA Section -->
<section class="py-5 bg-gradient text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
    <div class="container text-center">
        <h2 class="display-5 fw-bold mb-3">¿Tienes un Negocio Local?</h2>
        <p class="lead mb-4">Únete a nuestra plataforma y llega a miles de turistas que visitan Guinea Ecuatorial</p>
        <a href="admin/login.php" class="btn btn-light btn-lg px-5">
            <i class="bi bi-shop me-2"></i>Registra tu Local
        </a>
    </div>
</section>

<script>
// Filtrado por categorías rápidas
document.addEventListener('DOMContentLoaded', function() {
    const categoryButtons = document.querySelectorAll('[data-filter]');
    const filterSelect = document.getElementById('filter-tipo');
    
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterValue = this.dataset.filter;
            filterSelect.value = filterValue;
            filterSelect.dispatchEvent(new Event('change'));
            
            // Resaltar botón activo
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
