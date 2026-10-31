<?php 
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

// Filtros y parámetros
$categoria_filtro = $_GET['categoria'] ?? 'all';
$region_filtro = strtolower($_GET['region'] ?? 'all');
$presupuesto_filtro = $_GET['presupuesto'] ?? 'all';
$q_filtro = trim($_GET['q'] ?? '');
$orden = $_GET['orden'] ?? 'nombre_asc';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$items_per_page = 9;
$offset = ($page - 1) * $items_per_page;

// Obtener categorías únicas con conteo
$categorias = [];
$sql_categorias = "SELECT categoria, COUNT(*) as total FROM destinos WHERE categoria IS NOT NULL AND categoria != '' GROUP BY categoria ORDER BY categoria ASC";
$result_categorias = $conn->query($sql_categorias);
if ($result_categorias) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Construir query para destinos con filtros
$conditions = [];
$params = [];
$types = '';

if ($categoria_filtro !== 'all') {
    $conditions[] = "categoria = ?";
    $params[] = $categoria_filtro;
    $types .= 's';
}

if (!empty($q_filtro)) {
    $conditions[] = "(nombre LIKE ? OR descripcion LIKE ? OR ciudad LIKE ?)";
    $like = '%' . $q_filtro . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

if ($region_filtro === 'bioko') {
    $conditions[] = "LOWER(COALESCE(ciudad, '')) IN ('malabo', 'luba', 'riaba', 'baney', 'ureka', 'moka')";
} elseif ($region_filtro === 'riomuni') {
    $conditions[] = "(LOWER(COALESCE(ciudad, '')) NOT IN ('malabo', 'luba', 'riaba', 'baney', 'ureka', 'moka') OR LOWER(COALESCE(ciudad, '')) IN ('bata', 'mongomo', 'mbini', 'evinayong', 'ebebiyin', 'cogo', 'niefang', 'oyala', 'añisok', 'akonibe'))";
}

if ($presupuesto_filtro === 'low') {
    $conditions[] = "precio <= 50000";
} elseif ($presupuesto_filtro === 'mid') {
    $conditions[] = "precio > 50000 AND precio <= 150000";
} elseif ($presupuesto_filtro === 'high') {
    $conditions[] = "precio > 150000";
}

$where_clause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

// Contar total de destinos filtrados
$sql_count = "SELECT COUNT(DISTINCT id) as total FROM destinos" . $where_clause;
$stmt_count = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_destinos = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();
$total_pages = max(1, ceil($total_destinos / $items_per_page));

// Ordenamiento
$order_by = match($orden) {
    'precio_asc'  => 'precio ASC, nombre ASC',
    'precio_desc' => 'precio DESC, nombre ASC',
    'nombre_desc' => 'nombre DESC',
    default       => 'nombre ASC'
};

// Obtener destinos
$sql = "SELECT id, nombre, descripcion, imagen, categoria, precio, ciudad FROM destinos" . $where_clause . " ORDER BY " . $order_by . " LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$params[] = $items_per_page;
$params[] = $offset;
$types .= 'ii';

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$destinos = [];
while ($row = $result->fetch_assoc()) {
    $destinos[] = $row;
}
$stmt->close();
$conn->close();

// Helper para construir URLs conservando parámetros
function build_catalog_url($overrides = []) {
    $current = [
        'region'      => $GLOBALS['region_filtro'],
        'categoria'   => $GLOBALS['categoria_filtro'],
        'presupuesto' => $GLOBALS['presupuesto_filtro'],
        'orden'       => $GLOBALS['orden'],
        'q'           => $GLOBALS['q_filtro']
    ];
    $merged = array_merge($current, $overrides);
    $clean = [];
    foreach ($merged as $k => $v) {
        if ($v !== '' && $v !== 'all' && $v !== 'nombre_asc') {
            $clean[$k] = $v;
        }
    }
    return 'destinos.php' . (!empty($clean) ? '?' . http_build_query($clean) : '');
}
?>

<!-- Hero Editorial Catálogo -->
<header class="catalog-hero" style="background: radial-gradient(circle at 80% 20%, rgba(200, 149, 79, 0.25) 0%, transparent 50%), radial-gradient(circle at 10% 80%, rgba(23, 97, 58, 0.95) 0%, rgba(13, 56, 33, 0.98) 100%); color: #fff; padding: 4rem 0 3rem 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(8px);">
                    <i class="bi bi-compass-fill" style="color: var(--c-arena-400, #E0B470);"></i>
                    <span style="font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase; font-weight: 600;">Catálogo Exclusivo Guinea Ecuatorial</span>
                </div>
                <h1 style="font-family: var(--font-display); font-size: clamp(2.2rem, 4vw, 3.2rem); font-weight: 700; line-height: 1.15; margin-bottom: 0.75rem;">
                    Santuarios y Destinos Inexplorados
                </h1>
                <p style="font-size: 1.1rem; color: rgba(255,255,255,0.85); max-width: 680px; margin-bottom: 0;">
                    Desde las playas volcánicas de Ureka hasta las profundidades milenarias del Parque Nacional Monte Alén. Experiencias auténticas verificadas por guías locales.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <div class="d-inline-flex align-items-center gap-4 p-3 rounded-4" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(10px);">
                    <div>
                        <div style="font-size: 2rem; font-weight: 800; font-family: var(--font-display); color: var(--c-arena-300, #F4D396); line-height: 1;">
                            <?= $total_destinos ?>
                        </div>
                        <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.7);">Destinos Activos</div>
                    </div>
                    <div style="width: 1px; height: 38px; background: rgba(255,255,255,0.15);"></div>
                    <div>
                        <div style="font-size: 2rem; font-weight: 800; font-family: var(--font-display); color: var(--c-arena-300, #F4D396); line-height: 1;">
                            2
                        </div>
                        <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.7);">Regiones Únicas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<main class="container py-5">
    <!-- Barra de Filtros y Búsqueda Avanzada -->
    <section class="card mb-5 border-0 shadow-sm" style="border-radius: 20px; background: #fff; padding: 1.75rem; border: 1px solid rgba(0,0,0,0.06) !important;">
        <!-- Selector Regional Superior (UX-02) -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pb-3 mb-4 border-bottom">
            <div>
                <span class="text-muted small text-uppercase fw-bold d-block mb-1" style="letter-spacing: 0.05em;">Región Geográfica</span>
                <div class="nav nav-pills gap-2" role="tablist">
                    <a href="destinos.php?region=all<?= $categoria_filtro !== 'all' ? '&categoria=' . urlencode($categoria_filtro) : '' ?><?= $orden !== 'nombre_asc' ? '&orden=' . urlencode($orden) : '' ?>" 
                       class="btn btn-sm rounded-pill px-3 <?= $region_filtro === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>" 
                       style="font-weight: 600;">
                        <i class="bi bi-globe me-1"></i> Toda Guinea Ecuatorial
                    </a>
                    <a href="destinos.php?region=bioko<?= $categoria_filtro !== 'all' ? '&categoria=' . urlencode($categoria_filtro) : '' ?><?= $orden !== 'nombre_asc' ? '&orden=' . urlencode($orden) : '' ?>" 
                       class="btn btn-sm rounded-pill px-3 <?= $region_filtro === 'bioko' ? 'btn-primary' : 'btn-outline-secondary' ?>" 
                       style="font-weight: 600;">
                        <i class="bi bi-water me-1"></i> Isla de Bioko
                    </a>
                    <a href="destinos.php?region=riomuni<?= $categoria_filtro !== 'all' ? '&categoria=' . urlencode($categoria_filtro) : '' ?><?= $orden !== 'nombre_asc' ? '&orden=' . urlencode($orden) : '' ?>" 
                       class="btn btn-sm rounded-pill px-3 <?= $region_filtro === 'riomuni' ? 'btn-primary' : 'btn-outline-secondary' ?>" 
                       style="font-weight: 600;">
                        <i class="bi bi-tree me-1"></i> Región Continental (Río Muni)
                    </a>
                </div>
            </div>
            <!-- Ordenamiento -->
            <div class="d-flex align-items-center gap-2">
                <label for="sortSelector" class="text-muted small fw-bold mb-0 text-nowrap">Ordenar por:</label>
                <select id="sortSelector" class="form-select form-select-sm rounded-pill" style="min-width: 180px; font-weight: 500;" onchange="location.href=this.value;">
                    <option value="<?= build_catalog_url(['orden' => 'nombre_asc']) ?>" <?= $orden === 'nombre_asc' ? 'selected' : '' ?>>Nombre (A - Z)</option>
                    <option value="<?= build_catalog_url(['orden' => 'nombre_desc']) ?>" <?= $orden === 'nombre_desc' ? 'selected' : '' ?>>Nombre (Z - A)</option>
                    <option value="<?= build_catalog_url(['orden' => 'precio_asc']) ?>" <?= $orden === 'precio_asc' ? 'selected' : '' ?>>Precio: Menor a Mayor</option>
                    <option value="<?= build_catalog_url(['orden' => 'precio_desc']) ?>" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Precio: Mayor a Menor</option>
                </select>
            </div>
        </div>

        <!-- Búsqueda y Chips de Categoría -->
        <div class="row g-3 align-items-center">
            <div class="col-lg-5">
                <form method="GET" action="destinos.php" class="position-relative">
                    <?php if ($region_filtro !== 'all'): ?><input type="hidden" name="region" value="<?= htmlspecialchars($region_filtro) ?>"><?php endif; ?>
                    <?php if ($categoria_filtro !== 'all'): ?><input type="hidden" name="categoria" value="<?= htmlspecialchars($categoria_filtro) ?>"><?php endif; ?>
                    <?php if ($presupuesto_filtro !== 'all'): ?><input type="hidden" name="presupuesto" value="<?= htmlspecialchars($presupuesto_filtro) ?>"><?php endif; ?>
                    <?php if ($orden !== 'nombre_asc'): ?><input type="hidden" name="orden" value="<?= htmlspecialchars($orden) ?>"><?php endif; ?>
                    
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" value="<?= htmlspecialchars($q_filtro) ?>" 
                               class="form-control bg-light border-start-0 rounded-end-pill py-2" 
                               placeholder="Buscar por nombre, cascada, playa o ciudad...">
                    </div>
                </form>
            </div>
            <div class="col-lg-7">
                <div class="d-flex flex-wrap align-items-center gap-1 justify-content-lg-end">
                    <span class="text-muted small me-2 d-none d-xl-inline">Categoría:</span>
                    <a href="<?= build_catalog_url(['categoria' => 'all']) ?>" 
                       class="badge rounded-pill px-3 py-2 text-decoration-none <?= $categoria_filtro === 'all' ? 'bg-dark text-white' : 'bg-light text-dark' ?>" 
                       style="font-size: 0.85rem; font-weight: 500;">
                        Todas
                    </a>
                    <?php foreach ($categorias as $cat): ?>
                        <a href="<?= build_catalog_url(['categoria' => $cat['categoria']]) ?>" 
                           class="badge rounded-pill px-3 py-2 text-decoration-none <?= $categoria_filtro === $cat['categoria'] ? 'bg-dark text-white' : 'bg-light text-dark' ?>" 
                           style="font-size: 0.85rem; font-weight: 500;">
                            <?= htmlspecialchars($cat['categoria']) ?> 
                            <span class="opacity-75 ms-1">(<?= $cat['total'] ?>)</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Filtros Rápidos de Presupuesto -->
        <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top">
            <span class="text-muted small fw-semibold me-1"><i class="bi bi-cash-stack me-1"></i>Presupuesto:</span>
            <a href="<?= build_catalog_url(['presupuesto' => 'all']) ?>" 
               class="btn btn-sm rounded-pill py-1 px-3 <?= $presupuesto_filtro === 'all' ? 'btn-success' : 'btn-outline-secondary' ?>">
                Cualquier rango
            </a>
            <a href="<?= build_catalog_url(['presupuesto' => 'low']) ?>" 
               class="btn btn-sm rounded-pill py-1 px-3 <?= $presupuesto_filtro === 'low' ? 'btn-success' : 'btn-outline-secondary' ?>">
                Hasta 50.000 FCFA
            </a>
            <a href="<?= build_catalog_url(['presupuesto' => 'mid']) ?>" 
               class="btn btn-sm rounded-pill py-1 px-3 <?= $presupuesto_filtro === 'mid' ? 'btn-success' : 'btn-outline-secondary' ?>">
                50.000 - 150.000 FCFA
            </a>
            <a href="<?= build_catalog_url(['presupuesto' => 'high']) ?>" 
               class="btn btn-sm rounded-pill py-1 px-3 <?= $presupuesto_filtro === 'high' ? 'btn-success' : 'btn-outline-secondary' ?>">
                Premium (> 150.000 FCFA)
            </a>
        </div>
    </section>

    <!-- Indicador de Resultados Activos -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h5 fw-bold mb-0" style="color: var(--c-selva-900);">
                <?php if ($region_filtro === 'bioko'): ?>
                    Destinos en Isla de Bioko
                <?php elseif ($region_filtro === 'riomuni'): ?>
                    Destinos en Región Continental (Río Muni)
                <?php else: ?>
                    Todos los Destinos en Guinea Ecuatorial
                <?php endif; ?>
            </h2>
            <small class="text-muted">Mostrando <?= count($destinos) ?> de <?= $total_destinos ?> experiencias disponibles</small>
        </div>
        <?php if ($categoria_filtro !== 'all' || $region_filtro !== 'all' || $presupuesto_filtro !== 'all' || !empty($q_filtro)): ?>
            <a href="destinos.php" class="btn btn-sm btn-outline-danger rounded-pill">
                <i class="bi bi-x-circle me-1"></i> Limpiar Filtros
            </a>
        <?php endif; ?>
    </div>

    <!-- Grid de Destinos de Alta Gama con Wishlist y Divisa Dinámica -->
    <div class="row g-4">
        <?php if (count($destinos) > 0): ?>
            <?php foreach ($destinos as $destino): 
                $img = !empty($destino['imagen']) ? 'assets/img/destinos/' . $destino['imagen'] : 'assets/img/destinos/default.jpg';
                $precio_eur = round((float)$destino['precio'] / 655.957, 1);
            ?>
                <div class="col-md-6 col-lg-4">
                    <article class="card-experience h-100 d-flex flex-column position-relative" style="background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 16px rgba(0,0,0,0.04); transition: transform 0.25s ease, box-shadow 0.25s ease;">
                        <!-- Botón flotante Wishlist -->
                        <button type="button" class="btn-wishlist" data-destination-id="<?= $destino['id'] ?>" title="Guardar en lista de deseos">
                            <i class="bi bi-heart"></i>
                        </button>

                        <!-- Imagen con Badge Flotante -->
                        <div class="position-relative overflow-hidden" style="aspect-ratio: 16 / 10;">
                            <img src="<?= htmlspecialchars($img) ?>" 
                                 alt="<?= htmlspecialchars($destino['nombre']) ?>"
                                 class="w-100 h-100 object-fit-cover" 
                                 loading="lazy"
                                 style="transition: transform 0.4s ease;"
                                 onerror="this.src='assets/img/destinos/default.jpg'">
                            
                            <span class="position-absolute top-0 start-0 m-3 px-3 py-1 rounded-pill text-white" 
                                  style="background: rgba(23, 97, 58, 0.85); backdrop-filter: blur(8px); font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase;">
                                <?= htmlspecialchars($destino['categoria'] ?? 'Ecoturismo') ?>
                            </span>

                            <?php if (!empty($destino['ciudad'])): ?>
                                <span class="position-absolute bottom-0 start-0 m-3 px-2 py-1 rounded-2 text-white" 
                                      style="background: rgba(0, 0, 0, 0.65); backdrop-filter: blur(6px); font-size: 0.75rem; font-weight: 500;">
                                    <i class="bi bi-geo-alt-fill text-warning me-1"></i><?= htmlspecialchars($destino['ciudad']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Contenido Editorial -->
                        <div class="p-4 d-flex flex-column flex-grow-1 justify-content-between">
                            <div>
                                <h3 class="h5 fw-bold mb-2" style="font-family: var(--font-display); color: var(--c-selva-900);">
                                    <a href="detalle_destino.php?id=<?= $destino['id'] ?>" class="text-decoration-none text-reset">
                                        <?= htmlspecialchars($destino['nombre']) ?>
                                    </a>
                                </h3>
                                <p class="text-muted small mb-3" style="line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= htmlspecialchars($destino['descripcion']) ?>
                                </p>
                            </div>

                            <div class="pt-3 border-top d-flex justify-content-between align-items-end mt-2">
                                <div>
                                    <span class="text-muted text-uppercase" style="font-size: 0.7rem; font-weight: 600; letter-spacing: 0.05em; display: block;">Precio por persona</span>
                                    <span class="fw-bold" style="font-size: 1.25rem; color: var(--c-selva-700);" data-currency-target data-base-xaf="<?= (float)$destino['precio'] ?>">
                                        <?= format_currency((float)$destino['precio'], 'XAF') ?>
                                    </span>
                                    <span class="text-muted" style="font-size: 0.75rem; display: block;">
                                        &asymp; <?= $precio_eur ?> &euro;
                                    </span>
                                </div>
                                <a href="detalle_destino.php?id=<?= $destino['id'] ?>" 
                                   class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-semibold">
                                    Explorar <i class="bi bi-arrow-right ms-1"></i>
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
                        <div class="mb-3 d-inline-flex p-4 rounded-circle" style="background: rgba(23, 97, 58, 0.08); color: var(--c-selva-700);">
                            <i class="bi bi-compass fs-1"></i>
                        </div>
                        <h3 class="fw-bold mb-2" style="font-family: var(--font-display); color: var(--c-selva-900);">No se encontraron destinos</h3>
                        <p class="text-muted mx-auto" style="max-width: 480px;">
                            No hay expediciones que coincidan con los criterios seleccionados. Prueba a cambiar los filtros o buscar con otro término.
                        </p>
                        <a href="destinos.php" class="btn btn-primary rounded-pill px-4 mt-2">
                            Restablecer Todos los Filtros
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Paginación Moderna -->
    <?php if ($total_pages > 1): ?>
        <nav class="mt-5" aria-label="Paginación de catálogo">
            <ul class="pagination justify-content-center gap-1">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link rounded-pill px-3" href="<?= build_catalog_url(['page' => $page - 1]) ?>">
                            <i class="bi bi-chevron-left me-1"></i> Anterior
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link rounded-pill px-3" href="<?= build_catalog_url(['page' => $i]) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                        <li class="page-item disabled"><span class="page-link border-0">...</span></li>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link rounded-pill px-3" href="<?= build_catalog_url(['page' => $page + 1]) ?>">
                            Siguiente <i class="bi bi-chevron-right ms-1"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
