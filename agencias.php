<?php require_once 'includes/header.php'; ?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">Nuestras Agencias de Vuelos</h1>
        <p class="lead text-muted">Descubre las mejores ofertas y paquetes de viaje.</p>
    </div>

    <div id="agencias-list" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <!-- Indicador de Carga -->
        <div class="col-12 text-center" id="loading-spinner">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando agencias...</p>
        </div>
        <!-- Las agencias se cargarán aquí dinámicamente -->
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
