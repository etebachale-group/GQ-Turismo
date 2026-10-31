<?php 
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

// Filtros y parámetros
$q_filtro = trim($_GET['q'] ?? '');
$ciudad_filtro = trim($_GET['ciudad'] ?? '');
$orden = $_GET['orden'] ?? 'nombre_asc';

$conditions = [];
$params = [];
$types = '';

if (!empty($q_filtro)) {
    $conditions[] = "(nombre_guia LIKE ? OR especialidades LIKE ? OR descripcion LIKE ?)";
    $like = '%' . $q_filtro . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

if (!empty($ciudad_filtro) && $ciudad_filtro !== 'all') {
    $conditions[] = "ciudad_operacion LIKE ?";
    $params[] = '%' . $ciudad_filtro . '%';
    $types .= 's';
}

$where_clause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

$order_by = match($orden) {
    'precio_asc'  => 'precio_hora ASC, nombre_guia ASC',
    'precio_desc' => 'precio_hora DESC, nombre_guia ASC',
    default       => 'nombre_guia ASC'
};

$sql = "SELECT id, nombre_guia, descripcion, especialidades, precio_hora, contacto_email, contacto_telefono, imagen_perfil, ciudad_operacion FROM guias_turisticos" . $where_clause . " ORDER BY " . $order_by;
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$guias = [];
while ($row = $result->fetch_assoc()) {
    $guias[] = $row;
}
$stmt->close();
$conn->close();
?>

<!-- Hero Editorial Guías -->
<header class="catalog-hero" style="background: radial-gradient(circle at 80% 20%, rgba(200, 149, 79, 0.25) 0%, transparent 50%), radial-gradient(circle at 10% 80%, rgba(23, 97, 58, 0.95) 0%, rgba(13, 56, 33, 0.98) 100%); color: #fff; padding: 4.5rem 0 3.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(8px);">
                    <i class="bi bi-patch-check-fill" style="color: var(--c-arena-400, #E0B470);"></i>
                    <span style="font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase; font-weight: 600;">Profesionales Oficiales de Guinea Ecuatorial</span>
                </div>
                <h1 style="font-family: var(--font-display); font-size: clamp(2.2rem, 4vw, 3.2rem); font-weight: 700; line-height: 1.15; margin-bottom: 0.75rem;">
                    Guías Turísticos Certificados
                </h1>
                <p style="font-size: 1.1rem; color: rgba(255,255,255,0.85); max-width: 680px; margin-bottom: 0;">
                    Explora el país con expertos nativos bilingües. Conocimiento profundo de rutas ancestrales, biodiversidad y patrimonio cultural.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <div class="d-inline-flex align-items-center gap-3 p-3 rounded-4" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(10px);">
                    <div class="text-start">
                        <div style="font-size: 1.8rem; font-weight: 800; font-family: var(--font-display); color: var(--c-arena-300, #F4D396); line-height: 1;">
                            <?= count($guias) ?>
                        </div>
                        <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.7);">Guías Activos</div>
                    </div>
                    <div style="width: 1px; height: 35px; background: rgba(255,255,255,0.15);"></div>
                    <div class="text-start">
                        <div style="font-size: 1.8rem; font-weight: 800; font-family: var(--font-display); color: var(--c-arena-300, #F4D396); line-height: 1;">
                            100%
                        </div>
                        <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.7);">Verificados</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Franja de Garantías del Guía -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-3 text-center text-md-start">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--c-selva-50, #f0f7f2);">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: var(--c-selva-700); color: #fff;">
                        <i class="bi bi-shield-check fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold" style="color: var(--c-selva-900);">Acreditación Ministerial</h6>
                        <small class="text-muted">Certificación de Guía Nacional</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: rgba(200, 149, 79, 0.1);">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: var(--c-arena-600, #A07233); color: #fff;">
                        <i class="bi bi-translate fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold" style="color: var(--c-selva-900);">Dominio Multilingüe</h6>
                        <small class="text-muted">Español, Francés, Inglés y Lenguas Locales</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: rgba(30, 95, 140, 0.1);">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #1E5F8C; color: #fff;">
                        <i class="bi bi-cash-coin fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold" style="color: var(--c-selva-900);">Tarifa Transparente</h6>
                        <small class="text-muted">Tarifas por hora o día en FCFA sin recargos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filtros y Búsqueda -->
<main class="container py-5">
    <section class="card mb-5 border-0 shadow-sm p-4" style="border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important;">
        <form method="GET" action="guias.php" class="row g-3 align-items-center">
            <div class="col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="q" value="<?= htmlspecialchars($q_filtro) ?>" 
                           class="form-control bg-light border-start-0 rounded-end-pill py-2" 
                           placeholder="Buscar por nombre, especialidad o idioma...">
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <select name="ciudad" class="form-select rounded-pill bg-light py-2" onchange="this.form.submit()">
                    <option value="all">Todas las Ciudades</option>
                    <option value="Malabo" <?= $ciudad_filtro === 'Malabo' ? 'selected' : '' ?>>Malabo (Bioko)</option>
                    <option value="Bata" <?= $ciudad_filtro === 'Bata' ? 'selected' : '' ?>>Bata (Río Muni)</option>
                    <option value="Luba" <?= $ciudad_filtro === 'Luba' ? 'selected' : '' ?>>Luba</option>
                    <option value="Mongomo" <?= $ciudad_filtro === 'Mongomo' ? 'selected' : '' ?>>Mongomo</option>
                    <option value="Ureka" <?= $ciudad_filtro === 'Ureka' ? 'selected' : '' ?>>Ureka</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-3">
                <select name="orden" class="form-select rounded-pill bg-light py-2" onchange="this.form.submit()">
                    <option value="nombre_asc" <?= $orden === 'nombre_asc' ? 'selected' : '' ?>>Nombre (A - Z)</option>
                    <option value="precio_asc" <?= $orden === 'precio_asc' ? 'selected' : '' ?>>Tarifa: Menor a Mayor</option>
                    <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Tarifa: Mayor a Menor</option>
                </select>
            </div>
            <div class="col-lg-1 text-end">
                <button type="submit" class="btn btn-primary rounded-pill w-100 py-2">
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
        </form>
    </section>

    <!-- Grid de Guías Certificados -->
    <div class="row g-4">
        <?php if (!empty($guias)): ?>
            <?php foreach ($guias as $guia): 
                $avatar = !empty($guia['imagen_perfil']) ? 'assets/img/guias/' . htmlspecialchars($guia['imagen_perfil']) : 'assets/img/default-avatar.svg';
                $precio_h = (float)$guia['precio_hora'];
                $precio_eur = round($precio_h / 655.957, 1);
            ?>
                <div class="col-md-6 col-lg-4">
                    <article class="card h-100 border-0 shadow-sm p-4 text-center d-flex flex-column" 
                             style="border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important; transition: transform 0.25s ease, box-shadow 0.25s ease;">
                        
                        <!-- Avatar e Insignia -->
                        <div class="position-relative mx-auto mb-3" style="width: 100px; height: 100px;">
                            <img src="<?= $avatar ?>" alt="<?= htmlspecialchars($guia['nombre_guia']) ?>" 
                                 class="w-100 h-100 rounded-circle object-fit-cover shadow-sm border border-3 border-white"
                                 onerror="this.src='assets/img/default-avatar.svg'">
                            <span class="position-absolute bottom-0 end-0 bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                  style="width: 28px; height: 28px; font-size: 0.85rem;" title="Guía Oficial Verificado">
                                <i class="bi bi-patch-check-fill"></i>
                            </span>
                        </div>

                        <!-- Nombre y Ciudad -->
                        <h3 class="h5 fw-bold mb-1" style="font-family: var(--font-display); color: var(--c-selva-900);">
                            <a href="detalle_guia.php?id=<?= $guia['id'] ?>" class="text-decoration-none text-reset">
                                <?= htmlspecialchars($guia['nombre_guia']) ?>
                            </a>
                        </h3>
                        <?php if (!empty($guia['ciudad_operacion'])): ?>
                            <span class="text-muted small mb-2 d-inline-block">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($guia['ciudad_operacion']) ?>
                            </span>
                        <?php endif; ?>

                        <!-- Especialidades -->
                        <?php if (!empty($guia['especialidades'])): ?>
                            <div class="mb-3">
                                <span class="badge rounded-pill px-3 py-1" style="background: var(--c-selva-50); color: var(--c-selva-800); font-size: 0.75rem; font-weight: 600;">
                                    <?= htmlspecialchars($guia['especialidades']) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- Descripción -->
                        <p class="text-muted small mb-4" style="line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($guia['descripcion'] ?? 'Guía profesional dedicado a mostrar los encantos y secretos naturales de Guinea Ecuatorial.') ?>
                        </p>

                        <!-- Footer con Precio y Botón -->
                        <div class="pt-3 border-top mt-auto d-flex justify-content-between align-items-center">
                            <div class="text-start">
                                <span class="text-muted text-uppercase d-block" style="font-size: 0.7rem; font-weight: 600;">Tarifa</span>
                                <span class="fw-bold" style="font-size: 1.15rem; color: var(--c-selva-700);" data-currency-target data-base-xaf="<?= $precio_h ?>">
                                    <?= format_currency($precio_h, 'XAF') ?>/h
                                </span>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">&asymp; <?= $precio_eur ?> &euro;</small>
                            </div>
                            <a href="detalle_guia.php?id=<?= $guia['id'] ?>" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold btn-sm">
                                Ver Perfil <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card border-0 text-center py-5 shadow-sm" style="border-radius: 20px; background: #fafafa;">
                    <div class="card-body py-5">
                        <i class="bi bi-person-x fs-1 text-muted mb-3 d-block"></i>
                        <h4 class="fw-bold" style="color: var(--c-selva-900);">No se encontraron guías</h4>
                        <p class="text-muted">No hay profesionales disponibles con los criterios seleccionados.</p>
                        <a href="guias.php" class="btn btn-primary rounded-pill px-4 mt-2">Ver Todos los Guías</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Banner de Registro para Nuevos Guías -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, var(--c-selva-900, #0a2f18), var(--c-selva-700, #17613A));">
    <div class="container text-center py-3">
        <span class="badge badge--confirmado mb-2" style="background: rgba(255,255,255,0.2); color: #fff;">Oportunidad Profesional</span>
        <h2 class="display-6 fw-bold mb-3" style="font-family: var(--font-display, serif);">
            ¿Eres Guía Turístico Local Certificado?
        </h2>
        <p class="lead mb-4 mx-auto text-white-50" style="max-width: 600px;">
            Únete a la plataforma oficial de Guinea Ecuatorial y recibe solicitudes de tours directamente de viajeros de todo el mundo.
        </p>
        <a href="admin/login.php" class="btn btn-light btn-lg rounded-pill px-4 fw-bold shadow">
            <i class="bi bi-person-plus me-2"></i>Registrarme como Proveedor
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
