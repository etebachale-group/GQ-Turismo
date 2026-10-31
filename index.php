<?php
// GQ-Turismo: Portada Oficial Moderna (Fases 1-9 & UX V2.5)
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

// Obtener datos iniciales con consultas seguras
$destinos_populares = [];
$total_destinos = 0;

$sql_destinos = "SELECT id, nombre, descripcion, imagen, categoria, precio, ciudad FROM destinos ORDER BY id ASC LIMIT 6";
$res_destinos = $conn->query($sql_destinos);
if ($res_destinos) {
    while ($row = $res_destinos->fetch_assoc()) {
        $destinos_populares[] = $row;
    }
}

$sql_count = "SELECT COUNT(*) as total FROM destinos";
$res_count = $conn->query($sql_count);
if ($res_count) {
    $row_c = $res_count->fetch_assoc();
    $total_destinos = (int)($row_c['total'] ?? 0);
}

// Datos de usuario logueado
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_nombre'] ?? 'Viajero';
$user_type = $_SESSION['user_type'] ?? '';

$recent_itineraries = [];
$recent_orders = [];

if ($is_logged_in && $user_type === 'turista') {
    $u_id = (int)$_SESSION['user_id'];
    $stmt_it = $conn->prepare("SELECT id, nombre_itinerario, estado FROM itinerarios WHERE id_usuario = ? ORDER BY id DESC LIMIT 3");
    if ($stmt_it) {
        $stmt_it->bind_param("i", $u_id);
        $stmt_it->execute();
        $res_it = $stmt_it->get_result();
        while ($row = $res_it->fetch_assoc()) {
            $recent_itineraries[] = $row;
        }
        $stmt_it->close();
    }

    $stmt_ord = $conn->prepare("SELECT id, tipo_item, total, estado FROM pedidos WHERE id_usuario = ? ORDER BY id DESC LIMIT 3");
    if ($stmt_ord) {
        $stmt_ord->bind_param("i", $u_id);
        $stmt_ord->execute();
        $res_ord = $stmt_ord->get_result();
        while ($row = $res_ord->fetch_assoc()) {
            $recent_orders[] = $row;
        }
        $stmt_ord->close();
    }
}
?>

<!-- 1. HERO EDITORIAL MODERNO CON SEARCH CAPSULE -->
<header class="hero-editorial">
    <div class="container position-relative z-2 text-center">
        <!-- Badge de Confianza Oficial -->
        <div data-aos="fade-down">
            <span class="hero-glass-badge">
                <i class="bi bi-shield-check text-success"></i> Plataforma Oficial de Turismo de Guinea Ecuatorial
            </span>
        </div>

        <!-- Título y Subtítulo Editorial -->
        <h1 class="hero-title-editorial" data-aos="fade-up" data-aos-delay="100">
            Descubre el Corazón de África
        </h1>
        <p class="hero-subtitle-editorial" data-aos="fade-up" data-aos-delay="200">
            Tu aventura en Guinea Ecuatorial comienza aquí. Explora playas vírgenes, selvas exuberantes y una cultura milenaria intacta.
        </p>

        <!-- Botones de Acción Inmediata Hero -->
        <div class="d-flex justify-content-center gap-3 flex-wrap mb-4" data-aos="fade-up" data-aos-delay="250">
            <a href="destinos.php" class="btn btn-light btn-lg rounded-pill px-4 fw-bold shadow-sm">
                Explorar Destinos <i class="bi bi-arrow-right ms-1"></i>
            </a>
            <?= boton_evisa('es', 'btn btn-outline-light btn-lg rounded-pill px-4') ?>
        </div>

        <!-- Buscador Flotante Cápsula Avanzado (Search Capsule) -->
        <div class="search-capsule-wrapper mt-4" data-aos="zoom-in" data-aos-delay="300">
            <form action="destinos.php" method="GET" class="search-capsule">
                <!-- Campo 1: Región / Zona -->
                <div class="search-field">
                    <label><i class="bi bi-geo-alt me-1 text-success"></i> Región / Zona</label>
                    <select name="region">
                        <option value="all">Toda Guinea Ecuatorial</option>
                        <option value="bioko">Isla de Bioko (Malabo, Luba, Ureka)</option>
                        <option value="riomuni">Río Muni (Bata, Monte Alén, Mongomo)</option>
                    </select>
                </div>

                <!-- Campo 2: Categoría -->
                <div class="search-field">
                    <label><i class="bi bi-tag me-1 text-primary"></i> Categoría</label>
                    <select name="categoria">
                        <option value="all">Todas las Experiencias</option>
                        <option value="Naturaleza">Selva y Ecoturismo</option>
                        <option value="Playa">Playas Paradisiacas</option>
                        <option value="Historico">Cultura e Historia</option>
                    </select>
                </div>

                <!-- Campo 3: Viajeros interactivo con Popover -->
                <div class="search-field position-relative" style="cursor: pointer;">
                    <label><i class="bi bi-people me-1 text-warning"></i> Viajeros</label>
                    <div id="searchTravelersToggle" class="d-flex align-items-center justify-content-between text-dark fw-semibold" style="font-size: 0.95rem;">
                        <span id="searchTravelersSummary">1 Viajero</span>
                        <i class="bi bi-chevron-down text-muted small"></i>
                    </div>
                    <input type="hidden" name="personas" id="searchTravelersInput" value="1">
                    
                    <!-- Popover flotante -->
                    <div id="searchTravelersPopover" class="travelers-popover d-none text-start">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <strong class="d-block text-dark">Adultos y Niños</strong>
                                <small class="text-muted">A partir de 3 años</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="popover-counter-btn popover-minus">-</button>
                                <span class="popover-count fw-bold px-2">1</span>
                                <button type="button" class="popover-counter-btn popover-plus">+</button>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="document.getElementById('searchTravelersPopover').classList.add('d-none');">Aplicar</button>
                        </div>
                    </div>
                </div>

                <!-- Botón de Búsqueda -->
                <button type="submit" class="search-btn-circle">
                    <i class="bi bi-search"></i>
                    <span>Buscar</span>
                </button>
            </form>

            <!-- Píldoras de Tendencias Rápidas -->
            <div class="trending-chips" data-aos="fade-up" data-aos-delay="400">
                <span class="text-white-50 small fw-bold me-1">Tendencias:</span>
                <a href="destinos.php?region=bioko&categoria=Naturaleza" class="trending-chip"><i class="bi bi-water me-1"></i>Playas de Ureka</a>
                <a href="destinos.php?region=riomuni&categoria=Naturaleza" class="trending-chip"><i class="bi bi-tree me-1"></i>Monte Alén</a>
                <a href="destinos.php?region=bioko" class="trending-chip"><i class="bi bi-cloud-drizzle me-1"></i>Cascadas de Moka</a>
                <a href="destinos.php?region=bioko&categoria=Historico" class="trending-chip"><i class="bi bi-bank me-1"></i>Catedral de Malabo</a>
                <a href="destinos.php?region=riomuni" class="trending-chip"><i class="bi bi-building me-1"></i>Basílica de Mongomo</a>
            </div>
        </div>
    </div>
</header>

<!-- 2. FRANJA DE CONFIANZA LOCAL (UX-01) -->
<section class="py-5" style="background: var(--bg-surface, #ffffff); border-bottom: 1px solid rgba(0,0,0,0.06);">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="trust-card-elevated">
                    <div class="trust-icon-box trust-icon-green">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Proveedores Verificados</h5>
                    <p class="text-muted small mb-0">
                        Agencias locales, guías oficiales certificados y alojamientos verificados por el Ministerio de Información, Prensa y Radio / Turismo.
                    </p>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="trust-card-elevated">
                    <div class="trust-icon-box trust-icon-gold">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Pago en Efectivo o Transferencia</h5>
                    <p class="text-muted small mb-0">
                        Transparencia total. Pago en efectivo al inicio de la actividad o transferencia directa local en Francos CFA (XAF) sin sorpresas ni comisiones ocultas.
                    </p>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="trust-card-elevated">
                    <div class="trust-icon-box trust-icon-blue">
                        <i class="bi bi-passport-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Acceso Oficial con e-Visa</h5>
                    <p class="text-muted small mb-0">
                        Información directa y enlace oficial al visado electrónico gubernamental para turistas internacionales antes de su llegada.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. EXPLORACIÓN REGIONAL DUAL: BIOKO vs RÍO MUNI (UX-01) -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge badge--confirmado mb-2">Geografía Exclusiva</span>
            <h2 class="display-6 fw-bold" style="font-family: var(--font-display, serif); color: var(--c-selva-900, #0a2f18);">
                Dos Mundos en un Solo País
            </h2>
            <p class="text-muted mx-auto" style="max-width: 620px;">
                Descubre los dos ecosistemas complementarios de Guinea Ecuatorial: la majestuosidad volcánica insular y la inmensidad de la selva continental.
            </p>
        </div>

        <div class="row g-4">
            <!-- Isla de Bioko -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="region-hero-card">
                    <img src="assets/img/destinos/ureka.jpg" alt="Isla de Bioko" onerror="this.src='assets/img/destinos/default.jpg'">
                    <div class="region-hero-overlay">
                        <span class="badge bg-warning text-dark align-self-start mb-2 px-3 py-1 fw-bold rounded-pill">
                            <i class="bi bi-water me-1"></i> Isla Insular
                        </span>
                        <h3>Isla de Bioko</h3>
                        <p>Playas de arena volcánica negra en Ureka, cascadas de Moka, arquitectura colonial en Malabo y el imponente Pico Basilé.</p>
                        <div>
                            <a href="destinos.php?region=bioko" class="btn btn-light rounded-pill px-4 fw-bold">
                                Explorar Bioko <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Río Muni -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="region-hero-card">
                    <img src="assets/img/destinos/monte_alen.jpg" alt="Región Continental Río Muni" onerror="this.src='assets/img/destinos/default.jpg'">
                    <div class="region-hero-overlay">
                        <span class="badge bg-success align-self-start mb-2 px-3 py-1 fw-bold rounded-pill">
                            <i class="bi bi-tree me-1"></i> Selva Continental
                        </span>
                        <h3>Río Muni Continental</h3>
                        <p>El Parque Nacional Monte Alén con gorilas y elefantes de bosque, el paseo marítimo de Bata y la Basílica de Mongomo.</p>
                        <div>
                            <a href="destinos.php?region=riomuni" class="btn btn-light rounded-pill px-4 fw-bold">
                                Explorar Río Muni <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 4. EXPERIENCIAS DESTACADAS (SSR) -->
<section class="py-5" style="background: var(--bg-surface-alt, #f7f9f8);">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
            <div>
                <span class="badge badge--confirmado mb-2">Experiencias Populares</span>
                <h2 class="display-6 fw-bold mb-0" style="font-family: var(--font-display, serif); color: var(--c-selva-900, #0a2f18);">
                    Santuarios Naturales y Ciudades
                </h2>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="destinos.php" class="btn btn-outline-success rounded-pill px-4 fw-bold">
                    Ver Catálogo Completo (<?= $total_destinos ?>) <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($destinos_populares)): ?>
                <?php foreach ($destinos_populares as $destino): 
                    $img = !empty($destino['imagen']) ? 'assets/img/destinos/' . $destino['imagen'] : 'assets/img/destinos/default.jpg';
                    $precio_eur = round((float)$destino['precio'] / 655.957, 1);
                ?>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up">
                        <article class="card-experience position-relative">
                            <!-- Wishlist Heart Button -->
                            <button type="button" class="btn-wishlist" data-destination-id="<?= $destino['id'] ?>" title="Guardar en lista de deseos">
                                <i class="bi bi-heart"></i>
                            </button>

                            <div class="card-experience-img-wrapper">
                                <img src="<?= htmlspecialchars($img) ?>" 
                                     alt="<?= htmlspecialchars($destino['nombre']) ?>" 
                                     class="card-experience-img" 
                                     loading="lazy"
                                     onerror="this.src='assets/img/destinos/default.jpg'">
                                <span class="card-experience-badge">
                                    <?= htmlspecialchars($destino['categoria'] ?? 'Ecoturismo') ?>
                                </span>
                            </div>
                            <div class="card-experience-body">
                                <?php if (!empty($destino['ciudad'])): ?>
                                    <div class="card-experience-location">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($destino['ciudad']) ?>
                                    </div>
                                <?php endif; ?>
                                <h4 class="card-experience-title">
                                    <a href="detalle_destino.php?id=<?= $destino['id'] ?>">
                                        <?= htmlspecialchars($destino['nombre']) ?>
                                    </a>
                                </h4>
                                <p class="card-experience-desc">
                                    <?= htmlspecialchars(substr($destino['descripcion'], 0, 110)) ?>...
                                </p>
                                <div class="card-experience-footer">
                                    <div class="price-tag-modern">
                                        <span class="price-tag-label">Precio desde</span>
                                        <span class="price-tag-amount" data-currency-target data-base-xaf="<?= (float)$destino['precio'] ?>">
                                            <?= format_currency((float)$destino['precio'], 'XAF') ?>
                                        </span>
                                        <span class="price-tag-eur">&asymp; <?= $precio_eur ?> &euro;</span>
                                    </div>
                                    <a href="detalle_destino.php?id=<?= $destino['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-semibold">
                                        Detalles <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No se pudieron cargar los destinos en este momento.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 5. PREGUNTAS FRECUENTES DEL VIAJERO A GUINEA ECUATORIAL (FAQ) -->
<section class="py-5 bg-white border-top">
    <div class="container" style="max-width: 900px;">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge badge--confirmado mb-2">Guía Práctica</span>
            <h2 class="display-6 fw-bold" style="font-family: var(--font-display, serif); color: var(--c-selva-900, #0a2f18);">
                Preguntas Frecuentes para Tu Viaje
            </h2>
            <p class="text-muted">Todo lo que necesitas saber antes de embarcar hacia Guinea Ecuatorial.</p>
        </div>

        <div class="accordion accordion-flush shadow-sm rounded-4 overflow-hidden border" id="gqFaqAccordion">
            <div class="accordion-item">
                <h3 class="accordion-header" id="faqHeading1">
                    <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
                        <i class="bi bi-passport text-success me-2 fs-5"></i> ¿Cómo solicito el visado electrónico (e-Visa)?
                    </button>
                </h3>
                <div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#gqFaqAccordion">
                    <div class="accordion-body text-muted" style="line-height: 1.7;">
                        El visado electrónico es un trámite 100% gubernamental y en línea a través del portal oficial. En GQ-Turismo facilitamos el acceso oficial mediante nuestros botones de e-Visa. Deberás completar el formulario con tu pasaporte con vigencia mínima de 6 meses y comprobante de reserva.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h3 class="accordion-header" id="faqHeading2">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                        <i class="bi bi-cash-stack text-warning me-2 fs-5"></i> ¿Qué moneda se utiliza y cómo se pagan las excursiones?
                    </button>
                </h3>
                <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#gqFaqAccordion">
                    <div class="accordion-body text-muted" style="line-height: 1.7;">
                        La moneda oficial es el Franco CFA de África Central (XAF), con paridad fija al Euro (1 EUR = 655,957 XAF). En GQ-Turismo los precios están fijados en FCFA y puedes abonar tus reservas cómodamente en efectivo directamente con tu operador local o por transferencia bancaria nacional.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h3 class="accordion-header" id="faqHeading3">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                        <i class="bi bi-brightness-high text-info me-2 fs-5"></i> ¿Cuál es la mejor época para viajar y ver fauna?
                    </button>
                </h3>
                <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#gqFaqAccordion">
                    <div class="accordion-body text-muted" style="line-height: 1.7;">
                        La temporada seca principal comprende de noviembre a febrero, ideal para senderismo y avistamiento de tortugas marinas gigantes desovando en las playas de Ureka. La segunda temporada seca (julio y agosto) es magnífica para expediciones en el Parque Nacional Monte Alén.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. SECCIÓN INFORMATIVA OFICIAL E-VISA (VI-02) -->
<section class="py-5" style="background: var(--c-selva-50, #f0f7f2); border-top: 1px solid var(--c-selva-100, #e3f1e9); border-bottom: 1px solid var(--c-selva-100, #e3f1e9);">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8" data-aos="fade-right">
                <span class="badge badge--confirmado mb-2">Trámite Oficial de Entrada</span>
                <h3 class="display-6 fw-bold mb-2" style="font-family: var(--font-display, serif); color: var(--c-selva-900, #0a2f18);">
                    ¿Viajas a Guinea Ecuatorial desde el exterior?
                </h3>
                <p class="text-muted mb-0" style="font-size: 1.05rem;">
                    Gestiona tu visado electrónico oficial antes de viajar en el portal gubernamental. Trámite 100% online, seguro y sin intermediarios para turistas internacionales.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <?= boton_evisa('es', 'btn btn--primary btn-lg rounded-pill px-4 shadow') ?>
            </div>
        </div>
    </div>
</section>

<!-- 7. RESUMEN DE ACTIVIDAD PERSONALIZADO (SI EL USUARIO ESTÁ LOGUEADO) -->
<?php if ($is_logged_in): ?>
<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.5rem;">
                <i class="bi bi-person"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="font-family: var(--font-display);">Bienvenido, <?= htmlspecialchars($user_name) ?>!</h3>
                <p class="text-muted small mb-0">Panel personalizado de tu cuenta en GQ-Turismo</p>
            </div>
        </div>

        <div class="row g-4">
            <?php if ($user_type === 'turista'): ?>
                <div class="col-md-6">
                    <div class="card h-100 p-4 border shadow-sm" style="border-radius: 16px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-calendar3 me-2 text-success"></i>Tus Itinerarios</h5>
                            <a href="itinerario.php" class="btn btn-sm btn-outline-success rounded-pill px-3">Ver Todos</a>
                        </div>
                        <?php if (!empty($recent_itineraries)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recent_itineraries as $it): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span><?= htmlspecialchars($it['nombre_itinerario']) ?></span>
                                        <a href="itinerario.php" class="btn btn-sm btn-light rounded-pill">Abrir</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted small">Aún no has creado un itinerario. <a href="crear_itinerario.php">¡Crea uno ahora!</a></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 p-4 border shadow-sm" style="border-radius: 16px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-bag-check me-2 text-primary"></i>Tus Pedidos y Reservas</h5>
                            <a href="mis_pedidos.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">Ver Todos</a>
                        </div>
                        <?php if (!empty($recent_orders)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recent_orders as $ord): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span>Reserva #<?= $ord['id'] ?> (<?= htmlspecialchars($ord['tipo_item']) ?>)</span>
                                        <span class="badge bg-<?= ($ord['estado'] === 'confirmado') ? 'success' : 'warning' ?> rounded-pill"><?= htmlspecialchars(ucfirst($ord['estado'])) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted small">No tienes reservas activas. Explora el catálogo de destinos.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-3 p-4" style="border-radius: 16px;">
                        <div>
                            <h5 class="fw-bold mb-1">Acceso de Gestión Profesional</h5>
                            <p class="mb-0 text-muted">Gestiona tus servicios, disponibilidad y pedidos recibidos desde el panel administrativo.</p>
                        </div>
                        <a href="admin/dashboard.php" class="btn btn-primary px-4 rounded-pill">Ir a mi Dashboard <i class="bi bi-speedometer2 ms-1"></i></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 8. LLAMADA A LA ACCIÓN (PLANIFICADOR DE ITINERARIOS) -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, var(--c-selva-900, #0a2f18), var(--c-selva-700, #17613A));">
    <div class="container text-center py-4" data-aos="zoom-in">
        <span class="badge badge--confirmado mb-3" style="background: rgba(255,255,255,0.2); color: #fff;">Planificador Inteligente</span>
        <h2 class="display-6 fw-bold mb-3" style="font-family: var(--font-display, serif);">
            ¿Listo para vivir una experiencia inolvidable?
        </h2>
        <p class="lead mb-4 mx-auto text-white-50" style="max-width: 650px;">
            Diseña tu propio itinerario día por día, calcula tu presupuesto en FCFA y reserva con guías locales certificados.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="crear_itinerario.php" class="btn btn-light btn-lg rounded-pill px-4 fw-bold shadow">
                <i class="bi bi-calendar-plus me-1"></i> Crear Mi Itinerario
            </a>
            <a href="destinos.php" class="btn btn-outline-light btn-lg rounded-pill px-4">
                Ver Todos los Destinos
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
