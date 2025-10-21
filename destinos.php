<?php require_once 'includes/header.php'; ?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">Explora Nuestros Destinos</h1>
        <p class="lead text-muted">Descubre la belleza y diversidad de Guinea Ecuatorial.</p>
    </div>

    <!-- Controles de Filtro -->
    <div class="row mb-4">
        <div class="col-12 text-center" id="destinos-filters">
            <!-- Los botones de filtro se cargarán aquí dinámicamente -->
        </div>
    </div>

    <div id="destinos-grid" class="row">
        <!-- Indicador de Carga -->
        <div class="col-12 text-center" id="loading-spinner">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando destinos...</p>
        </div>
        <!-- Destinos se cargarán aquí dinámicamente -->
    </div>

    <!-- Controles de Paginación -->
    <div class="row mt-5">
        <div class="col-12" id="destinos-pagination">
            <!-- La paginación se cargará aquí dinámicamente -->
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
