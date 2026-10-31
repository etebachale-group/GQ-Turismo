<?php 
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

// Filtros y parámetros
$q_filtro = trim($_GET['q'] ?? '');
$orden = $_GET['orden'] ?? 'nombre_asc';

$conditions = [];
$params = [];
$types = '';

if (!empty($q_filtro)) {
    $conditions[] = "(nombre_agencia LIKE ? OR descripcion LIKE ?)";
    $like = '%' . $q_filtro . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

$where_clause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

$order_by = match($orden) {
    'nombre_desc' => 'nombre_agencia DESC',
    default       => 'nombre_agencia ASC'
};

$sql = "SELECT id, nombre_agencia, descripcion, contacto_email, contacto_telefono, imagen_perfil FROM agencias" . $where_clause . " ORDER BY " . $order_by;
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$agencias = [];
while ($row = $result->fetch_assoc()) {
    $agencias[] = $row;
}
$stmt->close();
$conn->close();
?>

<!-- Hero Editorial Agencias -->
<header class="catalog-hero" style="background: radial-gradient(circle at 80% 20%, rgba(200, 149, 79, 0.25) 0%, transparent 50%), radial-gradient(circle at 10% 80%, rgba(23, 97, 58, 0.95) 0%, rgba(13, 56, 33, 0.98) 100%); color: #fff; padding: 4.5rem 0 3.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(8px);">
                    <i class="bi bi-building-check" style="color: var(--c-arena-400, #E0B470);"></i>
                    <span style="font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase; font-weight: 600;">Operadores y Receptivos Acreditados</span>
                </div>
                <h1 style="font-family: var(--font-display); font-size: clamp(2.2rem, 4vw, 3.2rem); font-weight: 700; line-height: 1.15; margin-bottom: 0.75rem;">
                    Agencias de Viajes Oficiales
                </h1>
                <p style="font-size: 1.1rem; color: rgba(255,255,255,0.85); max-width: 680px; margin-bottom: 0;">
                    Organiza tus traslados, safaris y expediciones a medida con los turoperadores receptivos más consolidados de Guinea Ecuatorial.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <div class="d-inline-flex align-items-center gap-3 p-3 rounded-4" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(10px);">
                    <div class="text-start">
                        <div style="font-size: 1.8rem; font-weight: 800; font-family: var(--font-display); color: var(--c-arena-300, #F4D396); line-height: 1;">
                            <?= count($agencias) ?>
                        </div>
                        <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.7);">Agencias Registradas</div>
                    </div>
                    <div style="width: 1px; height: 35px; background: rgba(255,255,255,0.15);"></div>
                    <div class="text-start">
                        <div style="font-size: 1.8rem; font-weight: 800; font-family: var(--font-display); color: var(--c-arena-300, #F4D396); line-height: 1;">
                            100%
                        </div>
                        <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.7);">Verificadas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Franja de Confianza Agencias -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-3 text-center text-md-start">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--c-selva-50, #f0f7f2);">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: var(--c-selva-700); color: #fff;">
                        <i class="bi bi-shield-check fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold" style="color: var(--c-selva-900);">Turoperadores con Licencia</h6>
                        <small class="text-muted">Aprobados por el Ministerio de Turismo</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: rgba(200, 149, 79, 0.1);">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: var(--c-arena-600, #A07233); color: #fff;">
                        <i class="bi bi-box-seam fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold" style="color: var(--c-selva-900);">Paquetes a Medida</h6>
                        <small class="text-muted">Vuelos internos, 4x4 y estancias exclusivas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: rgba(30, 95, 140, 0.1);">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #1E5F8C; color: #fff;">
                        <i class="bi bi-headset fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold" style="color: var(--c-selva-900);">Atención Local In Situ</h6>
                        <small class="text-muted">Oficinas y soporte directo en Malabo y Bata</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Buscador y Grid -->
<main class="container py-5">
    <section class="card mb-5 border-0 shadow-sm p-4" style="border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important;">
        <form method="GET" action="agencias.php" class="row g-3 align-items-center">
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="q" value="<?= htmlspecialchars($q_filtro) ?>" 
                           class="form-control bg-light border-start-0 rounded-end-pill py-2" 
                           placeholder="Buscar por nombre de agencia o descripción...">
                </div>
            </div>
            <div class="col-lg-3">
                <select name="orden" class="form-select rounded-pill bg-light py-2" onchange="this.form.submit()">
                    <option value="nombre_asc" <?= $orden === 'nombre_asc' ? 'selected' : '' ?>>Nombre (A - Z)</option>
                    <option value="nombre_desc" <?= $orden === 'nombre_desc' ? 'selected' : '' ?>>Nombre (Z - A)</option>
                </select>
            </div>
            <div class="col-lg-1 text-end">
                <button type="submit" class="btn btn-primary rounded-pill w-100 py-2">
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
        </form>
    </section>

    <!-- Grid de Agencias -->
    <div class="row g-4">
        <?php if (!empty($agencias)): ?>
            <?php foreach ($agencias as $agencia): 
                $img = !empty($agencia['imagen_perfil']) ? 'assets/img/agencias/' . htmlspecialchars($agencia['imagen_perfil']) : 'assets/img/agencias/default.jpg';
            ?>
                <div class="col-md-6 col-lg-4">
                    <article class="card h-100 border-0 shadow-sm d-flex flex-column overflow-hidden" 
                             style="border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important; transition: transform 0.25s ease, box-shadow 0.25s ease;">
                        
                        <!-- Imagen de Cabecera -->
                        <div class="position-relative overflow-hidden" style="height: 180px;">
                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($agencia['nombre_agencia']) ?>" 
                                 class="w-100 h-100 object-fit-cover"
                                 onerror="this.src='assets/img/agencias/default.jpg'">
                            <span class="position-absolute top-0 start-0 m-3 px-3 py-1 rounded-pill text-white" 
                                  style="background: rgba(23, 97, 58, 0.9); backdrop-filter: blur(8px); font-size: 0.75rem; font-weight: 600;">
                                <i class="bi bi-patch-check-fill me-1"></i>Agencia Acreditada
                            </span>
                        </div>

                        <!-- Contenido -->
                        <div class="p-4 d-flex flex-column flex-grow-1 justify-content-between">
                            <div>
                                <h3 class="h5 fw-bold mb-2" style="font-family: var(--font-display); color: var(--c-selva-900);">
                                    <a href="detalle_agencia.php?id=<?= $agencia['id'] ?>" class="text-decoration-none text-reset">
                                        <?= htmlspecialchars($agencia['nombre_agencia']) ?>
                                    </a>
                                </h3>
                                <p class="text-muted small mb-3" style="line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= htmlspecialchars($agencia['descripcion'] ?? 'Agencia receptiva autorizada para la gestión de tours y traslados turísticos en Guinea Ecuatorial.') ?>
                                </p>
                            </div>

                            <div class="pt-3 border-top mt-auto d-flex justify-content-between align-items-center">
                                <?php if (!empty($agencia['contacto_telefono'])): ?>
                                    <small class="text-muted">
                                        <i class="bi bi-telephone me-1 text-success"></i><?= htmlspecialchars($agencia['contacto_telefono']) ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">Atención Oficial</small>
                                <?php endif; ?>

                                <a href="detalle_agencia.php?id=<?= $agencia['id'] ?>" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold btn-sm">
                                    Ver Paquetes <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card border-0 text-center py-5 shadow-sm" style="border-radius: 20px; background: #fafafa;">
                    <div class="card-body py-5">
                        <i class="bi bi-building-x fs-1 text-muted mb-3 d-block"></i>
                        <h4 class="fw-bold" style="color: var(--c-selva-900);">No se encontraron agencias</h4>
                        <p class="text-muted">Prueba a buscar con otro término o limpia los filtros.</p>
                        <a href="agencias.php" class="btn btn-primary rounded-pill px-4 mt-2">Ver Todas las Agencias</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- CTA Registrar Agencia -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, var(--c-selva-900, #0a2f18), var(--c-selva-700, #17613A));">
    <div class="container text-center py-3">
        <span class="badge badge--confirmado mb-2" style="background: rgba(255,255,255,0.2); color: #fff;">Operadores Turísticos</span>
        <h2 class="display-6 fw-bold mb-3" style="font-family: var(--font-display, serif);">
            ¿Gestionas una Agencia de Viajes en Guinea Ecuatorial?
        </h2>
        <p class="lead mb-4 mx-auto text-white-50" style="max-width: 600px;">
            Publica tus paquetes, tours organizados y servicios receptivos ante miles de visitantes internacionales.
        </p>
        <a href="admin/login.php" class="btn btn-light btn-lg rounded-pill px-4 fw-bold shadow">
            <i class="bi bi-building-add me-2"></i>Registrar Agencia
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
