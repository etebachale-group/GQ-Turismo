<?php
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5" style="max-width: 1140px;">
    <div class="text-center mb-5">
        <span class="badge badge--confirmado mb-2">Design System v2.0</span>
        <h1 style="font-family: var(--font-display); font-size: clamp(2rem, 4vw, 3rem); color: var(--c-selva-900);">
            GQ-Turismo Styleguide & Componentes
        </h1>
        <p class="text-muted" style="max-width: 650px; margin: 0 auto;">
            Guía de referencia visual de tokens de diseño, tipografía, paleta de colores cromática de Guinea Ecuatorial y componentes canónicos.
        </p>
    </div>

    <!-- 1. Paleta de Colores -->
    <section class="mb-5">
        <h2 style="font-family: var(--font-display); color: var(--c-selva-900); border-bottom: 2px solid var(--c-selva-100); padding-bottom: 0.5rem; margin-bottom: 1.5rem;">
            1. Paleta de Colores (DS-01)
        </h2>
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div style="background: var(--c-selva-600); height: 100px; border-radius: var(--radius-md); display: flex; align-items: flex-end; padding: 0.75rem; color: #fff;">
                    <strong>Verde Selva 600<br><small>#1b5e20</small></strong>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div style="background: var(--c-atlantico-600); height: 100px; border-radius: var(--radius-md); display: flex; align-items: flex-end; padding: 0.75rem; color: #fff;">
                    <strong>Azul Atlántico 600<br><small>#0277bd</small></strong>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div style="background: var(--c-terracota-500); height: 100px; border-radius: var(--radius-md); display: flex; align-items: flex-end; padding: 0.75rem; color: #fff;">
                    <strong>Terracota 500<br><small>#d84315</small></strong>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div style="background: var(--c-arena-100); border: 1px solid var(--c-arena-300); height: 100px; border-radius: var(--radius-md); display: flex; align-items: flex-end; padding: 0.75rem; color: var(--c-ebano-900);">
                    <strong>Arena 100<br><small>#f5f0e6</small></strong>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Tipografía -->
    <section class="mb-5">
        <h2 style="font-family: var(--font-display); color: var(--c-selva-900); border-bottom: 2px solid var(--c-selva-100); padding-bottom: 0.5rem; margin-bottom: 1.5rem;">
            2. Tipografía (DS-02)
        </h2>
        <div class="p-4" style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--gray-200);">
            <h1 style="font-family: var(--font-display); color: var(--c-selva-900);">Display H1: Fraunces Serif para Títulos</h1>
            <h2 style="font-family: var(--font-display); color: var(--c-selva-800);">Display H2: Playas de Ureka y Pico Basilé</h2>
            <p style="font-family: var(--font-sans); color: var(--gray-700); font-size: 1.125rem; line-height: 1.7;">
                Cuerpo de texto con <strong>Inter</strong> para una legibilidad óptima en dispositivos móviles y de escritorio. Espaciado fluido, pesos 400, 500, 600 y 700.
            </p>
        </div>
    </section>

    <!-- 3. Botones y e-Visa -->
    <section class="mb-5">
        <h2 style="font-family: var(--font-display); color: var(--c-selva-900); border-bottom: 2px solid var(--c-selva-100); padding-bottom: 0.5rem; margin-bottom: 1.5rem;">
            3. Botones & Módulo e-Visa (CMP-01, CMP-03)
        </h2>
        <div class="row g-3">
            <div class="col-md-4">
                <button class="btn btn--primary btn--block">Botón Primario</button>
            </div>
            <div class="col-md-4">
                <button class="btn btn--outline btn--block">Botón Outline</button>
            </div>
            <div class="col-md-4">
                <?= boton_evisa('es') ?>
            </div>
        </div>
    </section>

    <!-- 4. Badges Semánticos -->
    <section class="mb-5">
        <h2 style="font-family: var(--font-display); color: var(--c-selva-900); border-bottom: 2px solid var(--c-selva-100); padding-bottom: 0.5rem; margin-bottom: 1.5rem;">
            4. Badges Semánticos (CMP-04)
        </h2>
        <div class="d-flex gap-3 flex-wrap">
            <span class="badge badge--pendiente">Pendiente de Confirmación</span>
            <span class="badge badge--confirmado">Confirmado / Aprobado</span>
            <span class="badge badge--cancelado">Cancelado / Rechazado</span>
            <span class="badge badge--completado">Completado</span>
        </div>
    </section>

    <!-- 5. Tarjetas Canónicas -->
    <section class="mb-5">
        <h2 style="font-family: var(--font-display); color: var(--c-selva-900); border-bottom: 2px solid var(--c-selva-100); padding-bottom: 0.5rem; margin-bottom: 1.5rem;">
            5. Tarjetas Canónicas (CMP-02)
        </h2>
        <div class="row g-4">
            <!-- Card Destino -->
            <div class="col-md-4">
                <div class="card card-destino">
                    <img src="assets/img/destinos/6723ef9c0d750_parque-nacional-monte-alen.jpg" 
                         onerror="this.src='assets/img/destinos/placeholder.jpg'" 
                         class="card-img-top" 
                         alt="Monte Alén" 
                         style="height: 200px; object-fit: cover;">
                    <div class="card-body p-4">
                        <span class="badge badge--confirmado mb-2">Parque Nacional</span>
                        <h3 class="card-title" style="font-family: var(--font-display); font-size: 1.25rem;">
                            Parque Nacional Monte Alén
                        </h3>
                        <p class="card-text text-muted" style="font-size: 0.9rem;">
                            Reserva de biodiversidad con senderos selváticos, cascadas vírgenes y fauna endémica.
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <span class="fw-bold" style="color: var(--c-selva-700); font-size: 1.15rem;">
                                <?= format_currency(35000, 'XAF') ?>
                            </span>
                            <a href="destinos.php" class="btn btn--primary btn-sm">Explorar</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Proveedor -->
            <div class="col-md-4">
                <div class="card card-proveedor p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--c-selva-100); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--c-selva-700);">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div>
                            <h4 class="mb-0" style="font-size: 1.1rem; font-weight: 600;">Ecori Tours Guinea</h4>
                            <small class="text-muted"><i class="bi bi-geo-alt"></i> Malabo / Bioko</small>
                        </div>
                    </div>
                    <p class="text-muted" style="font-size: 0.9rem;">
                        Agencia certificada especializada en expediciones al Pico Basilé y observación de tortugas en Ureka.
                    </p>
                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                        <span class="badge badge--confirmado">Verificado</span>
                        <a href="agencias.php" class="btn btn--outline btn-sm">Contactar</a>
                    </div>
                </div>
            </div>

            <!-- Card Itinerario -->
            <div class="col-md-4">
                <div class="card card-itinerario p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge badge--pendiente">En Progreso</span>
                        <small class="text-muted">4 días / 3 noches</small>
                    </div>
                    <h4 style="font-family: var(--font-display); font-size: 1.2rem;">
                        Ruta Ecoturística Bioko Sur
                    </h4>
                    <p class="text-muted" style="font-size: 0.9rem;">
                        Itinerario personalizado que recorre Luba, Moka y las playas de arena negra volcánica de Ureka.
                    </p>
                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-success"><?= format_currency(120000, 'XAF') ?></span>
                        <a href="itinerario.php" class="btn btn--primary btn-sm">Ver Detalle</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>
