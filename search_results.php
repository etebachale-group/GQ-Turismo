<?php
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

$search_query = trim($_GET['query'] ?? '');
$search_type = $_GET['type'] ?? 'all';
$latitude = filter_var($_GET['latitude'] ?? '', FILTER_VALIDATE_FLOAT);
$longitude = filter_var($_GET['longitude'] ?? '', FILTER_VALIDATE_FLOAT);
$radius = filter_var($_GET['radius'] ?? '', FILTER_VALIDATE_FLOAT); // Radio en km

$results = [];
$title = "Resultados de Búsqueda";

// Lógica para construir la cláusula WHERE de ubicación
$location_where_clause = '';
$location_params = [];
$location_param_types = '';

if ($latitude !== false && $longitude !== false && $radius !== false && $radius > 0) {
    $lat_degree_km = 111.045;
    $lon_degree_km = 111.045 * cos(deg2rad($latitude));

    $lat_min = $latitude - ($radius / $lat_degree_km);
    $lat_max = $latitude + ($radius / $lat_degree_km);
    $lon_min = $longitude - ($radius / $lon_degree_km);
    $lon_max = $longitude + ($radius / $lon_degree_km);

    $location_where_clause = " AND (latitude BETWEEN ? AND ?) AND (longitude BETWEEN ? AND ?)";
    $location_params = [$lat_min, $lat_max, $lon_min, $lon_max];
    $location_param_types = 'dddd';
}

if ($conn) {
    $query_parts = [];
    $params = [];
    $param_types = '';

    // Search in Destinos
    if ($search_type === 'all' || $search_type === 'destinos') {
        $query_parts[] = "(SELECT id, nombre as name, 'destino' as type, descripcion, NULL as especialidades, NULL as tipo_local, NULL as precio_hora, NULL as contacto_email, NULL as contacto_telefono, imagen as ruta_imagen, latitude, longitude FROM destinos WHERE (nombre LIKE ? OR descripcion LIKE ?) " . str_replace('latitude', 'destinos.latitude', $location_where_clause) . ")";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $param_types .= 'ss';
        if (!empty($location_params)) {
            $params = array_merge($params, $location_params);
            $param_types .= $location_param_types;
        }
    }

    // Search in Agencias
    if ($search_type === 'all' || $search_type === 'agencias') {
        $query_parts[] = "(SELECT id, nombre_agencia as name, 'agencia' as type, descripcion, NULL as especialidades, NULL as tipo_local, NULL as precio_hora, contacto_email, contacto_telefono, NULL as ruta_imagen, latitude, longitude FROM agencias WHERE (nombre_agencia LIKE ? OR descripcion LIKE ?) " . str_replace('latitude', 'agencias.latitude', $location_where_clause) . ")";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $param_types .= 'ss';
        if (!empty($location_params)) {
            $params = array_merge($params, $location_params);
            $param_types .= $location_param_types;
        }
    }

    // Search in Guías
    if ($search_type === 'all' || $search_type === 'guias') {
        $query_parts[] = "(SELECT id, nombre_guia as name, 'guia' as type, descripcion, especialidades, NULL as tipo_local, precio_hora, contacto_email, contacto_telefono, NULL as ruta_imagen, current_latitude as latitude, current_longitude as longitude FROM guias_turisticos WHERE (nombre_guia LIKE ? OR descripcion LIKE ? OR especialidades LIKE ?) " . str_replace('latitude', 'guias_turisticos.current_latitude', $location_where_clause) . ")";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $param_types .= 'sss';
        if (!empty($location_params)) {
            $params = array_merge($params, $location_params);
            $param_types .= $location_param_types;
        }
    }

    // Search in Locales
    if ($search_type === 'all' || $search_type === 'locales') {
        $query_parts[] = "(SELECT id, nombre_local as name, 'local' as type, descripcion, NULL as especialidades, tipo_local, NULL as precio_hora, contacto_email, contacto_telefono, NULL as ruta_imagen, latitude, longitude FROM lugares_locales WHERE (nombre_local LIKE ? OR descripcion LIKE ? OR tipo_local LIKE ?) " . str_replace('latitude', 'lugares_locales.latitude', $location_where_clause) . ")";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $param_types .= 'sss';
        if (!empty($location_params)) {
            $params = array_merge($params, $location_params);
            $param_types .= $location_param_types;
        }
    }

    if (!empty($query_parts)) {
        $full_query = implode(' UNION ALL ', $query_parts) . " ORDER BY name ASC";
        $stmt = $conn->prepare($full_query);
        
        if (!empty($params)) {
            $stmt->bind_param($param_types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!-- Header Editorial Resultados -->
<header class="catalog-hero" style="background: radial-gradient(circle at 80% 20%, rgba(200, 149, 79, 0.25) 0%, transparent 50%), radial-gradient(circle at 10% 80%, rgba(23, 97, 58, 0.95) 0%, rgba(13, 56, 33, 0.98) 100%); color: #fff; padding: 4rem 0 3rem 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
    <div class="container text-center">
        <span class="badge badge--confirmado mb-2" style="background: rgba(255,255,255,0.15); color: #fff;">Búsqueda Global</span>
        <h1 style="font-family: var(--font-display); font-size: clamp(2rem, 3.5vw, 2.8rem); font-weight: 700; margin-bottom: 0.5rem;">
            <?= htmlspecialchars($title) ?>
        </h1>
        <?php if (!empty($search_query)): ?>
            <p class="lead mb-0 text-white-50">
                Coincidencias para <strong class="text-white">"<?= htmlspecialchars($search_query) ?>"</strong> &bull; <?= count($results) ?> resultados encontrados
            </p>
        <?php else: ?>
            <p class="lead mb-0 text-white-50">Introduce un término de búsqueda para localizar destinos, guías, agencias y locales.</p>
        <?php endif; ?>
    </div>
</header>

<main class="container py-5">
    <?php if (empty($results) && !empty($search_query)): ?>
        <div class="card border-0 text-center py-5 shadow-sm" style="border-radius: 20px; background: #fafafa;">
            <div class="card-body py-5">
                <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                <h4 class="fw-bold" style="color: var(--c-selva-900);">No se encontraron resultados</h4>
                <p class="text-muted mx-auto" style="max-width: 500px;">
                    No encontramos coincidencias para "<strong><?= htmlspecialchars($search_query) ?></strong>". Intenta con un término más general o explora nuestro catálogo completo.
                </p>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <a href="destinos.php" class="btn btn-primary rounded-pill px-4">Ver Todos los Destinos</a>
                    <a href="guias.php" class="btn btn-outline-secondary rounded-pill px-4">Ver Guías</a>
                </div>
            </div>
        </div>
    <?php elseif (!empty($results)): ?>
        <div class="row g-4">
            <?php foreach ($results as $item): 
                $badge_class = match($item['type']) {
                    'destino' => 'bg-success',
                    'guia'    => 'bg-primary',
                    'agencia' => 'bg-info text-dark',
                    'local'   => 'bg-warning text-dark',
                    default   => 'bg-secondary'
                };
                
                $detail_url = match($item['type']) {
                    'destino' => 'detalle_destino.php?id=' . $item['id'],
                    'agencia' => 'detalle_agencia.php?id=' . $item['id'],
                    'guia'    => 'detalle_guia.php?id=' . $item['id'],
                    'local'   => 'detalle_local.php?id=' . $item['id'],
                    default   => '#'
                };

                $img_src = '';
                if ($item['type'] === 'destino' && !empty($item['ruta_imagen'])) {
                    $img_src = 'assets/img/destinos/' . $item['ruta_imagen'];
                }
            ?>
                <div class="col-md-6 col-lg-4">
                    <article class="card h-100 border-0 shadow-sm d-flex flex-column overflow-hidden" 
                             style="border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important; transition: transform 0.2s ease;">
                        
                        <?php if (!empty($img_src)): ?>
                            <div class="position-relative overflow-hidden" style="height: 180px;">
                                <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($item['name']) ?>" 
                                     class="w-100 h-100 object-fit-cover" onerror="this.src='assets/img/destinos/default.jpg'">
                                <span class="position-absolute top-0 start-0 m-3 badge rounded-pill <?= $badge_class ?> px-3 py-1 text-uppercase" style="font-size: 0.75rem;">
                                    <?= htmlspecialchars($item['type']) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="p-4 d-flex flex-column flex-grow-1 justify-content-between">
                            <div>
                                <?php if (empty($img_src)): ?>
                                    <span class="badge rounded-pill <?= $badge_class ?> px-3 py-1 mb-2 text-uppercase" style="font-size: 0.75rem;">
                                        <?= htmlspecialchars($item['type']) ?>
                                    </span>
                                <?php endif; ?>

                                <h3 class="h5 fw-bold mb-2" style="font-family: var(--font-display); color: var(--c-selva-900);">
                                    <a href="<?= $detail_url ?>" class="text-decoration-none text-reset">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </a>
                                </h3>
                                
                                <p class="text-muted small mb-3" style="line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= htmlspecialchars($item['descripcion'] ?? 'Sin descripción disponible') ?>
                                </p>

                                <?php if (!empty($item['especialidades'])): ?>
                                    <p class="small text-muted mb-1"><i class="bi bi-star text-warning me-1"></i><?= htmlspecialchars($item['especialidades']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="pt-3 border-top mt-auto d-flex justify-content-between align-items-center">
                                <span class="text-muted small text-capitalize"><?= htmlspecialchars($item['type']) ?> Oficial</span>
                                <a href="<?= $detail_url ?>" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold btn-sm">
                                    Ver Detalles <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
