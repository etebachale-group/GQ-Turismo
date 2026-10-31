<?php
require_once 'includes/header.php'; 

$is_logged_in = isset($_SESSION['user_id']);
$user_type = $_SESSION['user_type'] ?? null;
$user_name = $_SESSION['user_nombre'] ?? 'Usuario';

// Obtener carouseles activos
$carouseles_activos = [];
$recent_itineraries = [];
$recent_orders = [];
$destinos_destacados = [];

if ($conn) {
    // 1. Carouseles activos
    $sql_carouseles = "SELECT nombre, ruta_imagen, enlace FROM carouseles WHERE activo = 1 ORDER BY orden ASC";
    $result_carouseles = $conn->query($sql_carouseles);
    if ($result_carouseles) {
        while ($row = $result_carouseles->fetch_assoc()) {
            $carouseles_activos[] = $row;
        }
    }

    // 2. Destinos destacados para SSR inmediato
    $res_destinos = $conn->query("SELECT * FROM destinos ORDER BY id ASC LIMIT 6");
    if ($res_destinos) {
        while ($d = $res_destinos->fetch_assoc()) {
            $destinos_destacados[] = $d;
        }
    }

    // 3. Actividad reciente para usuarios autenticados
    if ($is_logged_in) {
        if ($user_type === 'turista') {
            $stmt_itineraries = $conn->prepare("SELECT id, nombre_itinerario FROM itinerarios WHERE id_usuario = ? ORDER BY fecha_creacion DESC LIMIT 3");
            $stmt_itineraries->bind_param("i", $_SESSION['user_id']);
            $stmt_itineraries->execute();
            $result_itineraries = $stmt_itineraries->get_result();
            while ($row = $result_itineraries->fetch_assoc()) {
                $recent_itineraries[] = $row;
            }
            $stmt_itineraries->close();

            $stmt_orders = $conn->prepare("SELECT id, tipo_proveedor, tipo_item, estado FROM pedidos_servicios WHERE id_turista = ? ORDER BY fecha_solicitud DESC LIMIT 3");
            $stmt_orders->bind_param("i", $_SESSION['user_id']);
            $stmt_orders->execute();
            $result_orders = $stmt_orders->get_result();
            while ($row = $result_orders->fetch_assoc()) {
                $recent_orders[] = $row;
            }
            $stmt_orders->close();

        } else if (in_array($user_type, ['agencia', 'guia', 'local'])) {
            $provider_table = '';
            if ($user_type === 'agencia') { $provider_table = 'agencias'; }
            else if ($user_type === 'guia') { $provider_table = 'guias_turisticos'; }
            else if ($user_type === 'local') { $provider_table = 'lugares_locales'; }

            if ($provider_table) {
                $stmt_provider_id = $conn->prepare("SELECT id FROM " . $provider_table . " WHERE id_usuario = ?");
                $stmt_provider_id->bind_param("i", $_SESSION['user_id']);
                $stmt_provider_id->execute();
                $result_provider_id = $stmt_provider_id->get_result();
                $provider_data = $result_provider_id->fetch_assoc();
                $provider_entity_id = $provider_data['id'] ?? null;
                $stmt_provider_id->close();

                if ($provider_entity_id) {
                    $stmt_orders = $conn->prepare("SELECT ps.id, ps.tipo_item, ps.estado, u.nombre as turista_nombre
                                                    FROM pedidos_servicios ps
                                                    JOIN usuarios u ON ps.id_turista = u.id
                                                    WHERE ps.tipo_proveedor = ? AND ps.id_proveedor = ?
                                                    ORDER BY ps.fecha_solicitud DESC LIMIT 3");
                    $stmt_orders->bind_param("si", $user_type, $provider_entity_id);
                    $stmt_orders->execute();
                    $result_orders = $stmt_orders->get_result();
                    while ($row = $result_orders->fetch_assoc()) {
                        $recent_orders[] = $row;
                    }
                    $stmt_orders->close();
                }
            }
        }
    }
}
?>

<!-- 1. HERO EDITORIAL Y BUSCADOR CÁPSULA (UX AVANZADO) -->
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
            Tu aventura en Guinea Ecuatorial comienza aquí. Explora playas vírgenes, selvas exuberantes y una cultura vibrante.
        </p>

        <!-- Botones de Acción Inmediata Hero -->
        <div class="d-flex justify-content-center gap-3 flex-wrap mb-4" data-aos="fade-up" data-aos-delay="250">
            <a href="destinos.php" class="btn btn-light btn-lg rounded-pill px-4 fw-bold shadow-sm">
                Explorar Destinos <i class="bi bi-arrow-right ms-1"></i>
            </a>
            <?= boton_evisa('es', 'btn btn-outline-light btn-lg rounded-pill px-4') ?>
        </div>

        <!-- Buscador Flotante Cápsula (Search Capsule) -->
        <div class="search-capsule-wrapper mt-4" data-aos="zoom-in" data-aos-delay="300">
            <form action="destinos.php" method="GET" class="search-capsule">
                <!-- Campo 1: Región -->
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
                        <option value="Naturaleza">Selva y Naturaleza</option>
                        <option value="Playa">Playas Paradisiacas</option>
                        <option value="Historico">Cultura e Historia</option>
                    </select>
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
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="trust-card-elevated">
                    <div class="trust-icon-box">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="font-family: var(--font-display);">Proveedores Verificados</h5>
                    <p class="text-muted small mb-0">
                        Guías turísticos y agencias locales formalmente registrados con licencias y seguros al día.
                    </p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="trust-card-elevated">
                    <div class="trust-icon-box" style="background: var(--c-atlantico-100, #E1EEF6); color: var(--c-atlantico-600, #0B5C8C);">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="font-family: var(--font-display);">Pago en Efectivo o Transferencia</h5>
                    <p class="text-muted small mb-0">
                        Precios transparentes en FCFA (XAF). Abona en mano al guía el día del viaje o por banco BANGE/CCEI.
                    </p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="trust-card-elevated">
                    <div class="trust-icon-box" style="background: #e8f5e9; color: #2e7d32;">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="font-family: var(--font-display);">Atención y Soporte Local</h5>
                    <p class="text-muted small mb-0">
                        Contacto y asistencia directa para tu viaje en Guinea Ecuatorial con teléfono oficial +240.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. ACCESOS REGIONALES INMERSIVOS: BIOKO Y RÍO MUNI (UX-01) -->
<section class="py-5" style="background: var(--c-arena-50, #fcfaf7);">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge badge--confirmado mb-2">Las Dos Joyas de Guinea Ecuatorial</span>
            <h2 class="fw-bold display-6" style="font-family: var(--font-display, serif); color: var(--c-selva-900);">
                ¿Qué región deseas descubrir hoy?
            </h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">
                Elige entre la exuberante isla volcánica de Bioko o la inmensidad selvática y costera de Río Muni.
            </p>
        </div>

        <div class="row g-4">
            <!-- Región Insular: Bioko -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="region-hero-card">
                    <img src="assets/img/destinos/68fa7befc3c67_catedral-de-santa-isabel.jpg" alt="Isla de Bioko">
                    <div class="region-hero-overlay">
                        <span class="badge badge--confirmado align-self-start mb-2">Región Insular</span>
                        <h3>Explorar Isla de Bioko</h3>
                        <p>Cascadas de Moka, playas de arena negra en Ureka con anidación de tortugas, Pico Basilé y la arquitectura colonial de Malabo.</p>
                        <a href="destinos.php?region=bioko" class="btn btn-light rounded-pill px-4 fw-bold align-self-start shadow">
                            Ver Destinos en Bioko <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Región Continental: Río Muni -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="region-hero-card">
                    <img src="assets/img/destinos/693d90d836f35_IMG_8906.JPG" alt="Río Muni Continental">
                    <div class="region-hero-overlay">
                        <span class="badge badge--confirmado align-self-start mb-2">Región Continental</span>
                        <h3>Explorar Río Muni</h3>
                        <p>La selva virgen del Parque Nacional de Monte Alén, el estuario de Mbini, la majestuosa Basílica de Mongomo y el dinamismo de Bata.</p>
                        <a href="destinos.php?region=riomuni" class="btn btn-light rounded-pill px-4 fw-bold align-self-start shadow">
                            Ver Destinos en Río Muni <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 4. CATÁLOGO DE DESTINOS DESTACADOS (SSR CON DISEÑO MODERNO) -->
<section class="py-5" style="background: #ffffff;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-5" data-aos="fade-up">
            <div>
                <span class="badge badge--confirmado mb-2">Experiencias Populares</span>
                <h2 class="fw-bold mb-1" style="font-family: var(--font-display); color: var(--c-selva-900);">
                    Destinos que No Te Puedes Perder
                </h2>
                <p class="text-muted mb-0">Selección curada de los parajes más emblemáticos de Guinea Ecuatorial.</p>
            </div>
            <a href="destinos.php" class="btn btn-outline-success rounded-pill px-4 fw-bold">
                Ver Todos los Destinos <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($destinos_destacados as $destino): ?>
                <?php 
                $img_path = !empty($destino['imagen']) ? 'assets/img/destinos/' . $destino['imagen'] : 'assets/img/destinos/default.jpg';
                $ciudad = $destino['ciudad'] ?? 'Guinea Ecuatorial';
                $is_bioko = in_array(strtolower($ciudad), ['malabo', 'luba', 'riaba', 'baney', 'ureka', 'moka']);
                $region_name = $is_bioko ? 'Bioko' : 'Río Muni';
                $precio = (float)($destino['precio'] ?? 0.0);
                $precio_eur = round($precio / 655.957, 1);
                ?>
                <div class="col-md-6 col-lg-4" data-aos="fade-up">
                    <div class="card-experience">
                        <div class="card-experience-media">
                            <img src="<?= htmlspecialchars($img_path) ?>" alt="<?= htmlspecialchars($destino['nombre']) ?>" onerror="this.src='assets/img/destinos/default.jpg'">
                            <span class="badge-floating-category"><?= htmlspecialchars($destino['categoria'] ?? 'Destino') ?></span>
                            <span class="badge-floating-region"><?= htmlspecialchars($region_name) ?></span>
                        </div>
                        <div class="card-experience-body">
                            <div class="card-experience-location">
                                <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($ciudad) ?>
                            </div>
                            <h4 class="card-experience-title"><?= htmlspecialchars($destino['nombre']) ?></h4>
                            <p class="card-experience-desc"><?= htmlspecialchars($destino['descripcion']) ?></p>
                            <div class="card-experience-footer">
                                <div class="price-tag-modern">
                                    <span class="price-tag-label">Precio por persona</span>
                                    <span class="price-tag-amount"><?= format_currency($precio, 'XAF') ?></span>
                                    <span class="price-tag-eur">Aprox. <?= $precio_eur ?> €</span>
                                </div>
                                <a href="detalle_destino.php?id=<?= $destino['id'] ?>" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                                    Explorar <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 5. SECCIÓN INFORMATIVA OFICIAL E-VISA (VI-02) -->
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

<!-- 6. RESUMEN DE ACTIVIDAD PERSONALIZADO (SI EL USUARIO ESTÁ LOGUEADO) -->
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
                    <div class="card h-100 p-4 border shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-calendar3 me-2 text-success"></i>Tus Itinerarios</h5>
                            <a href="itinerario.php" class="btn btn-sm btn-outline-success">Ver Todos</a>
                        </div>
                        <?php if (!empty($recent_itineraries)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recent_itineraries as $it): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span><?= htmlspecialchars($it['nombre_itinerario']) ?></span>
                                        <a href="itinerario.php" class="btn btn-sm btn-light">Abrir</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted small">Aún no has creado un itinerario. <a href="crear_itinerario.php">¡Crea uno ahora!</a></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 p-4 border shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-bag-check me-2 text-primary"></i>Tus Pedidos y Reservas</h5>
                            <a href="mis_pedidos.php" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                        </div>
                        <?php if (!empty($recent_orders)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recent_orders as $ord): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span>Reserva #<?= $ord['id'] ?> (<?= htmlspecialchars($ord['tipo_item']) ?>)</span>
                                        <span class="badge bg-<?= ($ord['estado'] === 'confirmado') ? 'success' : 'warning' ?>"><?= htmlspecialchars(ucfirst($ord['estado'])) ?></span>
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
                    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-3 p-4">
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

<!-- 7. LLAMADA A LA ACCIÓN (PLANIFICADOR DE ITINERARIOS) -->
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
