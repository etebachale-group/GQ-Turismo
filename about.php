<?php require_once 'includes/header.php'; ?>

<!-- Hero Editorial Sobre Nosotros -->
<header class="catalog-hero" style="background: radial-gradient(circle at 80% 20%, rgba(200, 149, 79, 0.25) 0%, transparent 50%), radial-gradient(circle at 10% 80%, rgba(23, 97, 58, 0.95) 0%, rgba(13, 56, 33, 0.98) 100%); color: #fff; padding: 4.5rem 0 3.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
    <div class="container text-center">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(8px);">
            <i class="bi bi-compass-fill" style="color: var(--c-arena-400, #E0B470);"></i>
            <span style="font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase; font-weight: 600;">GQ-Turismo Oficial</span>
        </div>
        <h1 style="font-family: var(--font-display); font-size: clamp(2.4rem, 4.5vw, 3.6rem); font-weight: 700; line-height: 1.15; margin-bottom: 1rem;">
            Conectando el Mundo con Guinea Ecuatorial
        </h1>
        <p class="lead mx-auto" style="color: rgba(255,255,255,0.85); max-width: 720px;">
            La plataforma líder de ecoturismo y expediciones. Promovemos un turismo sostenible que protege la biodiversidad y empodera a las comunidades locales de Bioko y Río Muni.
        </p>
    </div>
</header>

<!-- Misión y Visión -->
<section class="py-5" style="background: var(--c-selva-50, #f0f7f2); border-bottom: 1px solid var(--c-selva-100, #e3f1e9);">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <div class="col-md-6 col-lg-5" data-aos="fade-right">
                <div class="card h-100 border-0 shadow-sm p-4 p-md-5" style="border-radius: 20px; background: #fff;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" 
                         style="width: 56px; height: 56px; background: rgba(23, 97, 58, 0.1); color: var(--c-selva-700);">
                        <i class="bi bi-bullseye fs-3"></i>
                    </div>
                    <h2 class="h4 fw-bold mb-3" style="font-family: var(--font-display); color: var(--c-selva-900);">
                        Nuestra Misión
                    </h2>
                    <p class="text-muted" style="line-height: 1.7;">
                        Facilitar el acceso transparente y seguro a las maravillas naturales e históricas de Guinea Ecuatorial, ofreciendo herramientas tecnológicas de reserva directa, guías certificados y pagos locales fiables.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-5" data-aos="fade-left">
                <div class="card h-100 border-0 shadow-sm p-4 p-md-5" style="border-radius: 20px; background: #fff;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" 
                         style="width: 56px; height: 56px; background: rgba(200, 149, 79, 0.15); color: var(--c-arena-600, #A07233);">
                        <i class="bi bi-eye fs-3"></i>
                    </div>
                    <h2 class="h4 fw-bold mb-3" style="font-family: var(--font-display); color: var(--c-selva-900);">
                        Nuestra Visión
                    </h2>
                    <p class="text-muted" style="line-height: 1.7;">
                        Posicionar a Guinea Ecuatorial como el referente supremo de ecoturismo no masificado en el Golfo de Guinea, preservando los santuarios de Monte Alén, Ureka y Annobón para futuras generaciones.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Historia y Biodiversidad -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <span class="badge badge--confirmado mb-2">Compromiso Auténtico</span>
                <h2 class="display-6 fw-bold mb-4" style="font-family: var(--font-display); color: var(--c-selva-900);">
                    El Corazón Verde del Golfo de Guinea
                </h2>
                <p class="text-muted lead fs-6" style="line-height: 1.75;">
                    Guinea Ecuatorial alberga una de las mayores densidades de biodiversidad de todo el continente africano: bosques primarios con elefantes de selva y gorilas de llanura, calderas volcánicas y playas vírgenes donde desovan las tortugas laúd más grandes del planeta.
                </p>
                <p class="text-muted" style="line-height: 1.75;">
                    En <strong>GQ-Turismo</strong> integramos a operadores turísticos oficiales, guías nativos que conocen cada sendero milenario y establecimientos gastronómicos locales para que cada viajero disfrute de una aventura genuina y respetuosa.
                </p>
                <div class="pt-2 d-flex gap-3">
                    <a href="destinos.php" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        Explorar Destinos <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <a href="contacto.php" class="btn btn-outline-secondary rounded-pill px-4">
                        Contactar Equipo
                    </a>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="position-relative">
                    <img src="assets/img/intro-image.jpg" class="img-fluid rounded-4 shadow-lg w-100 object-fit-cover" alt="Guinea Ecuatorial" style="max-height: 440px;" onerror="this.src='assets/img/destinos/default.jpg'">
                    <div class="position-absolute bottom-0 start-0 m-4 p-3 rounded-3 text-white" style="background: rgba(13, 56, 33, 0.9); backdrop-filter: blur(8px); max-width: 320px;">
                        <div class="fw-bold"><i class="bi bi-tree me-1 text-success"></i> 100% Ecoturismo Responsable</div>
                        <small class="text-white-50">Conservación comunitaria activa</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Valores Centrales -->
<section class="py-5" style="background: var(--bg-surface-alt, #f7f9f8);">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge badge--confirmado mb-2">Principios Rectores</span>
            <h2 class="display-6 fw-bold" style="font-family: var(--font-display); color: var(--c-selva-900);">
                Nuestros Pilares Fundamentales
            </h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">Garantizamos los más altos estándares éticos y de hospitalidad.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center" style="border-radius: 16px; background: #fff;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 text-success" style="width: 52px; height: 52px; background: rgba(23, 97, 58, 0.1);">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                    <h3 class="h5 fw-bold mb-2" style="color: var(--c-selva-900);">Autenticidad y Confianza</h3>
                    <p class="text-muted small mb-0">Proveedores e itinerarios verificados bajo supervisión oficial y estándares de seguridad.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center" style="border-radius: 16px; background: #fff;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 text-warning" style="width: 52px; height: 52px; background: rgba(200, 149, 79, 0.15);">
                        <i class="bi bi-globe-americas fs-4"></i>
                    </div>
                    <h3 class="h5 fw-bold mb-2" style="color: var(--c-selva-900);">Conservación Sostenible</h3>
                    <p class="text-muted small mb-0">Cada reserva apoya la protección de los ecosistemas y la economía circular de los pueblos nativos.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center" style="border-radius: 16px; background: #fff;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 text-primary" style="width: 52px; height: 52px; background: rgba(30, 95, 140, 0.1);">
                        <i class="bi bi-heart-fill fs-4"></i>
                    </div>
                    <h3 class="h5 fw-bold mb-2" style="color: var(--c-selva-900);">Hospitalidad Africana</h3>
                    <p class="text-muted small mb-0">Calidez genuina, acogida cultural sincera y atención personalizada 24/7 en tu estancia.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Final -->
<section class="py-5 text-white text-center" style="background: linear-gradient(135deg, var(--c-selva-900, #0a2f18), var(--c-selva-700, #17613A));">
    <div class="container py-3">
        <h2 class="display-6 fw-bold mb-3" style="font-family: var(--font-display);">¿Listo para Descubrir Guinea Ecuatorial?</h2>
        <p class="lead mb-4 mx-auto text-white-50" style="max-width: 600px;">Planifica tu aventura con nosotros o contáctanos para asesoramiento personalizado.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="destinos.php" class="btn btn-light btn-lg rounded-pill px-4 fw-bold shadow">
                Explorar Catálogo
            </a>
            <a href="contacto.php" class="btn btn-outline-light btn-lg rounded-pill px-4">
                Contactar Asesor
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
