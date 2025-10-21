<?php require_once 'includes/header.php'; ?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">Nuestros Lugares y Locales</h1>
        <p class="lead text-muted">Descubre los mejores sitios para visitar, comer y alojarte.</p>
    </div>

    <div id="locales-list" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <!-- Indicador de Carga -->
        <div class="col-12 text-center" id="loading-spinner">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando locales...</p>
        </div>
        <!-- Los locales se cargarán aquí dinámicamente -->
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
