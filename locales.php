<?php 
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

// Filtros y parámetros
$q_filtro = trim($_GET['q'] ?? '');
$tipo_filtro = trim($_GET['tipo'] ?? 'all');
$ciudad_filtro = trim($_GET['ciudad'] ?? 'all');
$orden = $_GET['orden'] ?? 'nombre_asc';

$conditions = [];
$params = [];
$types = '';

if (!empty($q_filtro)) {
    $conditions[] = "(nombre_local LIKE ? OR descripcion LIKE ? OR direccion LIKE ?)";
    $like = '%' . $q_filtro . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

if (!empty($tipo_filtro) && $tipo_filtro !== 'all') {
    $conditions[] = "tipo_local LIKE ?";
    $params[] = '%' . $tipo_filtro . '%';
    $types .= 's';
}

if (!empty($ciudad_filtro) && $ciudad_filtro !== 'all') {
    $conditions[] = "(direccion LIKE ? OR ciudad LIKE ?)";
    $like_c = '%' . $ciudad_filtro . '%';
    $params[] = $like_c;
    $params[] = $like_c;
    $types .= 'ss';
}

$where_clause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

$order_by = match($orden) {
    'nombre_desc' => 'nombre_local DESC',
    default       => 'nombre_local ASC'
};

$sql = "SELECT id, nombre_local, descripcion, tipo_local, direccion, contacto_email, contacto_telefono, imagen_perfil FROM lugares_locales" . $where_clause . " ORDER BY " . $order_by;
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$locales = [];
while ($row = $result->fetch_assoc()) {
    $locales[] = $row;
}
$stmt->close();
$conn->close();

function build_locales_url($overrides = []) {
    $current = [
        'q'      => $GLOBALS['q_filtro'],
        'tipo'   => $GLOBALS['tipo_filtro'],
        'ciudad' => $GLOBALS['ciudad_filtro'],
        'orden'  => $GLOBALS['orden']
    ];
    $merged = array_merge($current, $overrides);
    $clean = [];
    foreach ($merged as $k => $v) {
        if ($v !== '' && $v !== 'all' && $v !== 'nombre_asc') {
            $clean[$k] = $v;
        }
    }
    return 'locales.php' . (!empty($clean) ? '?' . http_build_query($clean) : '');
}
?>

<!-- Hero Editorial Lugares y Locales -->
<header class="catalog-hero" style="background: radial-gradient(circle at 80% 20%, rgba(200, 149, 79, 0.25) 0%, transparent 50%), radial-gradient(circle at 10% 80%, rgba(23, 97, 58, 0.95) 0%, rgba(13, 56, 33, 0.98) 100%); color: #fff; padding: 4.5rem 0 3.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(8px);">
                    <i class="bi bi-shop" style="color: var(--c-arena-400, #E0B470);"></i>
                    <span style="font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase; font-weight: 600;">Directorio de Hostelería y Comercio Local</span>
                </div>
                <h1 style="font-family: var(--font-display); font-size: clamp(2.2rem, 4vw, 3.2rem); font-weight: 700; line-height: 1.15; margin-bottom: 0.75rem;">
                    Restaurantes, Hoteles y Comercios
                </h1>
                <p style="font-size: 1.1rem; color: rgba(255,255,255,0.85); max-width: 680px; margin-bottom: 0;">
                    Vive la auténtica hospitalidad ecuatoguineana. Desde restaurantes frente al mar en Bata hasta cafés históricos en Malabo.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <div class="d-inline-flex align-items-center gap-3 p-3 rounded-4" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(10px);">
                    <div class="text-start">
                        <div style="font-size: 1.8rem; font-weight: 800; font-family: var(--font-display); color: var(--c-arena-300, #F4D396); line-height: 1;">
                            <?= count($locales) ?>
                        </div>
                        <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.7);">Locales Activos</div>
                    </div>
                    <div style="width: 1px; height: 35px; background: rgba(255,255,255,0.15);"></div>
                    <div class="text-start">
                        <div style="font-size: 1.8rem; font-weight: 800; font-family: var(--font-display); color: var(--c-arena-300, #F4D396); line-height: 1;">
                            100%
                        </div>
                        <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.7);">Auténticos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Categorías Rápidas en Píldoras -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
            <a href="<?= build_locales_url(['tipo' => 'all']) ?>" 
               class="btn btn-sm rounded-pill px-3 py-2 <?= $tipo_filtro === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-grid-fill me-1"></i> Todos los Locales
            </a>
            <a href="<?= build_locales_url(['tipo' => 'restaurante']) ?>" 
               class="btn btn-sm rounded-pill px-3 py-2 <?= strtolower($tipo_filtro) === 'restaurante' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-cup-hot me-1"></i> Restaurantes & Gastronomía
            </a>
            <a href="<?= build_locales_url(['tipo' => 'hotel']) ?>" 
               class="btn btn-sm rounded-pill px-3 py-2 <?= strtolower($tipo_filtro) === 'hotel' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-house-door me-1"></i> Hoteles & Alojamientos
            </a>
            <a href="<?= build_locales_url(['tipo' => 'tienda']) ?>" 
               class="btn btn-sm rounded-pill px-3 py-2 <?= strtolower($tipo_filtro) === 'tienda' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-bag-check me-1"></i> Tiendas & Artesanía
            </a>
            <a href="<?= build_locales_url(['tipo' => 'cafe']) ?>" 
               class="btn btn-sm rounded-pill px-3 py-2 <?= strtolower($tipo_filtro) === 'cafe' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-cup-straw me-1"></i> Cafés & Terrazas
            </a>
        </div>
    </div>
</section>

<!-- Buscador y Grid -->
<main class="container py-5">
    <section class="card mb-5 border-0 shadow-sm p-4" style="border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important;">
        <form method="GET" action="locales.php" class="row g-3 align-items-center">
            <?php if ($tipo_filtro !== 'all'): ?><input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo_filtro) ?>"><?php endif; ?>
            <div class="col-lg-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="q" value="<?= htmlspecialchars($q_filtro) ?>" 
                           class="form-control bg-light border-start-0 rounded-end-pill py-2" 
                           placeholder="Buscar por nombre, especialidad o dirección...">
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <select name="ciudad" class="form-select rounded-pill bg-light py-2" onchange="this.form.submit()">
                    <option value="all">Todas las Ciudades</option>
                    <option value="Malabo" <?= $ciudad_filtro === 'Malabo' ? 'selected' : '' ?>>Malabo</option>
                    <option value="Bata" <?= $ciudad_filtro === 'Bata' ? 'selected' : '' ?>>Bata</option>
                    <option value="Luba" <?= $ciudad_filtro === 'Luba' ? 'selected' : '' ?>>Luba</option>
                    <option value="Mongomo" <?= $ciudad_filtro === 'Mongomo' ? 'selected' : '' ?>>Mongomo</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
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

    <!-- Grid de Locales -->
    <div class="row g-4">
        <?php if (!empty($locales)): ?>
            <?php foreach ($locales as $local): 
                $img = !empty($local['imagen_perfil']) ? 'assets/img/locales/' . htmlspecialchars($local['imagen_perfil']) : 'assets/img/locales/default.jpg';
            ?>
                <div class="col-md-6 col-lg-4">
                    <article class="card h-100 border-0 shadow-sm d-flex flex-column overflow-hidden" 
                             style="border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important; transition: transform 0.25s ease, box-shadow 0.25s ease;">
                        
                        <!-- Imagen de Cabecera -->
                        <div class="position-relative overflow-hidden" style="height: 200px;">
                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($local['nombre_local']) ?>" 
                                 class="w-100 h-100 object-fit-cover"
                                 onerror="this.src='assets/img/locales/default.jpg'">
                            <span class="position-absolute top-0 start-0 m-3 px-3 py-1 rounded-pill text-white" 
                                  style="background: rgba(23, 97, 58, 0.88); backdrop-filter: blur(8px); font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                <?= htmlspecialchars($local['tipo_local'] ?? 'Local') ?>
                            </span>
                        </div>

                        <!-- Contenido -->
                        <div class="p-4 d-flex flex-column flex-grow-1 justify-content-between">
                            <div>
                                <h3 class="h5 fw-bold mb-2" style="font-family: var(--font-display); color: var(--c-selva-900);">
                                    <a href="detalle_local.php?id=<?= $local['id'] ?>" class="text-decoration-none text-reset">
                                        <?= htmlspecialchars($local['nombre_local']) ?>
                                    </a>
                                </h3>
                                
                                <?php if (!empty($local['direccion'])): ?>
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($local['direccion']) ?>
                                    </p>
                                <?php endif; ?>

                                <p class="text-muted small mb-3" style="line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= htmlspecialchars($local['descripcion'] ?? 'Establecimiento recomendado para degustar gastronomía local y disfrutar de una experiencia acogedora.') ?>
                                </p>
                            </div>

                            <div class="pt-3 border-top mt-auto d-flex justify-content-between align-items-center">
                                <?php if (!empty($local['contacto_telefono'])): ?>
                                    <small class="text-muted">
                                        <i class="bi bi-telephone me-1 text-success"></i><?= htmlspecialchars($local['contacto_telefono']) ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">Abierto al público</small>
                                <?php endif; ?>

                                <a href="detalle_local.php?id=<?= $local['id'] ?>" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold btn-sm">
                                    Ver Detalles <i class="bi bi-arrow-right ms-1"></i>
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
                        <i class="bi bi-shop-window fs-1 text-muted mb-3 d-block"></i>
                        <h4 class="fw-bold" style="color: var(--c-selva-900);">No se encontraron establecimientos</h4>
                        <p class="text-muted">Prueba a seleccionar otra categoría o cambiar los filtros.</p>
                        <a href="locales.php" class="btn btn-primary rounded-pill px-4 mt-2">Ver Todos los Locales</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- CTA Registrar Local -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, var(--c-selva-900, #0a2f18), var(--c-selva-700, #17613A));">
    <div class="container text-center py-3">
        <span class="badge badge--confirmado mb-2" style="background: rgba(255,255,255,0.2); color: #fff;">Hostelería y Comercio</span>
        <h2 class="display-6 fw-bold mb-3" style="font-family: var(--font-display, serif);">
            ¿Tienes un Restaurante, Hotel o Comercio en Guinea Ecuatorial?
        </h2>
        <p class="lead mb-4 mx-auto text-white-50" style="max-width: 600px;">
            Aparece en la guía oficial y recibe clientes nacionales e internacionales que buscan lo mejor de tu ciudad.
        </p>
        <a href="admin/login.php" class="btn btn-light btn-lg rounded-pill px-4 fw-bold shadow">
            <i class="bi bi-shop me-2"></i>Registrar Mi Establecimiento
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
