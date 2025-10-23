<?php 
require_once 'includes/header.php'; 

$is_logged_in = isset($_SESSION['user_id']);
$user_type = $_SESSION['user_type'] ?? null;
$user_name = $_SESSION['user_nombre'] ?? 'Usuario';

// Lógica para obtener datos personalizados si el usuario está logueado
$recent_itineraries = [];
$recent_orders = [];
$carouseles_activos = [];

if ($conn) {
    // Obtener carouseles activos para la página de inicio
    $sql_carouseles = "SELECT nombre, ruta_imagen, enlace FROM carouseles WHERE activo = 1 ORDER BY orden ASC";
    $result_carouseles = $conn->query($sql_carouseles);
    if ($result_carouseles) {
        while ($row = $result_carouseles->fetch_assoc()) {
            $carouseles_activos[] = $row;
        }
    }

    if ($is_logged_in) {
        if ($user_type === 'turista') {
            // Obtener últimos itinerarios del turista
            $stmt_itineraries = $conn->prepare("SELECT id, nombre_itinerario FROM itinerarios WHERE id_usuario = ? ORDER BY fecha_creacion DESC LIMIT 3");
            $stmt_itineraries->bind_param("i", $_SESSION['user_id']);
            $stmt_itineraries->execute();
            $result_itineraries = $stmt_itineraries->get_result();
            while ($row = $result_itineraries->fetch_assoc()) {
                $recent_itineraries[] = $row;
            }
            $stmt_itineraries->close();

            // Obtener últimos pedidos del turista
            $stmt_orders = $conn->prepare("SELECT id, tipo_proveedor, tipo_item, estado FROM pedidos_servicios WHERE id_turista = ? ORDER BY fecha_solicitud DESC LIMIT 3");
            $stmt_orders->bind_param("i", $_SESSION['user_id']);
            $stmt_orders->execute();
            $result_orders = $stmt_orders->get_result();
            while ($row = $result_orders->fetch_assoc()) {
                $recent_orders[] = $row;
            }
            $stmt_orders->close();

        } else if (in_array($user_type, ['agencia', 'guia', 'local'])) {
            // Obtener últimos pedidos recibidos por el proveedor
            $provider_table = '';
            $item_name_field = '';
            if ($user_type === 'agencia') { $provider_table = 'agencias'; $item_name_field = 'nombre_agencia'; }
            else if ($user_type === 'guia') { $provider_table = 'guias_turisticos'; $item_name_field = 'nombre_guia'; }
            else if ($user_type === 'local') { $provider_table = 'lugares_locales'; $item_name_field = 'nombre_local'; }

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
} // End of if ($conn) block

// Lógica para recomendaciones sofisticadas
$recommended_items = [];
if ($is_logged_in && $user_type === 'turista') {
    // 1. Obtener historial de destinos de itinerarios
    $preferred_categories = [];
    $stmt_itinerary_history = $conn->prepare("
        SELECT d.categoria, COUNT(d.categoria) as count
        FROM itinerarios i
        JOIN itinerario_destinos id ON i.id = id.id_itinerario
        JOIN destinos d ON id.id_destino = d.id
        WHERE i.id_usuario = ?
        GROUP BY d.categoria
        ORDER BY count DESC
        LIMIT 3
    ");
    $stmt_itinerary_history->bind_param("i", $_SESSION['user_id']);
    $stmt_itinerary_history->execute();
    $result_itinerary_history = $stmt_itinerary_history->get_result();
    while ($row = $result_itinerary_history->fetch_assoc()) {
        $preferred_categories[] = $row['categoria'];
    }
    $stmt_itinerary_history->close();

    // 2. Obtener historial de tipos de proveedores de pedidos
    $preferred_provider_types = [];
    $stmt_order_history = $conn->prepare("
        SELECT tipo_proveedor, COUNT(tipo_proveedor) as count
        FROM pedidos_servicios
        WHERE id_turista = ?
        GROUP BY tipo_proveedor
        ORDER BY count DESC
        LIMIT 3
    ");
    $stmt_order_history->bind_param("i", $_SESSION['user_id']);
    $stmt_order_history->execute();
    $result_order_history = $stmt_order_history->get_result();
    while ($row = $result_order_history->fetch_assoc()) {
        $preferred_provider_types[] = $row['tipo_proveedor'];
    }
    $stmt_order_history->close();

    // Generar recomendaciones basadas en preferencias
    $recommendation_queries = [];
    $recommendation_params = [];
    $recommendation_param_types = '';

    // Recomendaciones de Destinos basadas en categorías preferidas
    if (!empty($preferred_categories)) {
        $placeholders = implode(',', array_fill(0, count($preferred_categories), '?'));
        $recommendation_queries[] = "(SELECT id, nombre as name, 'destino' as type, imagen as ruta_imagen FROM destinos WHERE categoria IN ($placeholders) ORDER BY RAND() LIMIT 2)";
        foreach ($preferred_categories as $cat) {
            $recommendation_params[] = $cat;
            $recommendation_param_types .= 's';
        }
    }

    // Recomendaciones de Agencias/Guías/Locales basadas en tipos de proveedores preferidos
    foreach ($preferred_provider_types as $p_type) {
        if ($p_type === 'agencia') {
            $recommendation_queries[] = "(SELECT id, nombre_agencia as name, 'agencia' as type, NULL as ruta_imagen FROM agencias ORDER BY RAND() LIMIT 1)";
        } elseif ($p_type === 'guia') {
            $recommendation_queries[] = "(SELECT id, nombre_guia as name, 'guia' as type, NULL as ruta_imagen FROM guias_turisticos ORDER BY RAND() LIMIT 1)";
        } elseif ($p_type === 'local') {
            $recommendation_queries[] = "(SELECT id, nombre_local as name, 'local' as type, NULL as ruta_imagen FROM lugares_locales ORDER BY RAND() LIMIT 1)";
        }
    }

    // Si no hay historial, o para complementar, añadir algunos ítems populares/aleatorios
    if (empty($recommendation_queries)) {
        $recommendation_queries[] = "(SELECT id, nombre as name, 'destino' as type, imagen as ruta_imagen FROM destinos ORDER BY RAND() LIMIT 2)";
        $recommendation_queries[] = "(SELECT id, nombre_agencia as name, 'agencia' as type, NULL as ruta_imagen FROM agencias ORDER BY RAND() LIMIT 1)";
        $recommendation_queries[] = "(SELECT id, nombre_guia as name, 'guia' as type, NULL as ruta_imagen FROM guias_turisticos ORDER BY RAND() LIMIT 1)";
    }

    if (!empty($recommendation_queries)) {
        $full_recommendation_query = implode(' UNION ALL ', $recommendation_queries);
        $stmt_recommendations = $conn->prepare($full_recommendation_query);
        
        if (!empty($recommendation_params)) {
            $stmt_recommendations->bind_param($recommendation_param_types, ...$recommendation_params);
        }
        
        $stmt_recommendations->execute();
        $result_recommendations = $stmt_recommendations->get_result();
        while ($row = $result_recommendations->fetch_assoc()) {
            $recommended_items[] = $row;
        }
        $stmt_recommendations->close();
    }
}

// Fetch recently added items for recommendations
$recently_added_items = [];

if ($conn) { // Ensure connection is open for these queries
    // Fetch recently added destinations
    $stmt_destinos = $conn->prepare("SELECT id, nombre as name, 'destino' as type, imagen as ruta_imagen FROM destinos ORDER BY id DESC LIMIT 3");
    $stmt_destinos->execute();
    $result_destinos = $stmt_destinos->get_result();
    while ($row = $result_destinos->fetch_assoc()) {
        $recently_added_items[] = $row;
    }
    $stmt_destinos->close();

    // Fetch recently added agencies
    $stmt_agencias = $conn->prepare("SELECT id, nombre_agencia as name, 'agencia' as type, imagen_perfil as ruta_imagen FROM agencias ORDER BY id DESC LIMIT 3");
    $stmt_agencias->execute();
    $result_agencias = $stmt_agencias->get_result();
    while ($row = $result_agencias->fetch_assoc()) {
        $recently_added_items[] = $row;
    }
    $stmt_agencias->close();

    // Fetch recently added guides
    $stmt_guias = $conn->prepare("SELECT id, nombre_guia as name, 'guia' as type, imagen_perfil as ruta_imagen FROM guias_turisticos ORDER BY id DESC LIMIT 3");
    $stmt_guias->execute();
    $result_guias = $stmt_guias->get_result();
    while ($row = $result_guias->fetch_assoc()) {
        $recently_added_items[] = $row;
    }
    $stmt_guias->close();

    // Fetch recently added locales
    $stmt_locales = $conn->prepare("SELECT id, nombre_local as name, 'local' as type, imagen_perfil as ruta_imagen FROM lugares_locales ORDER BY id DESC LIMIT 3");
    $stmt_locales->execute();
    $result_locales = $stmt_locales->get_result();
    while ($row = $result_locales->fetch_assoc()) {
        $recently_added_items[] = $row;
    }
    $stmt_locales->close();

    // Sort all recently added items by their ID (assuming higher ID means more recent)
    usort($recently_added_items, function($a, $b) {
        return $b['id'] <=> $a['id'];
    });

    // Limit to top 6 overall recent items
    $recently_added_items = array_slice($recently_added_items, 0, 6);

    $conn->close(); // Close connection after all queries
}
?>

<!-- HERO SECTION -->
<!-- <header class="hero-section video-hero">
    <video playsinline="playsinline" autoplay="autoplay" muted="muted" loop="loop">
        <source src="assets/img/hero-video.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="container h-100">
        <div class="row h-100 align-items-center text-center">
            <div class="col-12" data-aos="fade-in">
                <?php if ($is_logged_in): ?>
                    <h1 class="display-3 fw-bold text-white">Bienvenido, <?= htmlspecialchars($user_name) ?>!</h1>
                    <p class="lead text-white-50 mb-4">Tu portal personalizado para explorar Guinea Ecuatorial.</p>
                    <?php if ($user_type === 'turista'): ?>
                        <a href="itinerario.php" class="btn btn-primary btn-lg">Mis Itinerarios <i class="bi bi-map"></i></a>
                        <a href="mis_pedidos.php" class="btn btn-outline-light btn-lg ms-2">Mis Pedidos <i class="bi bi-box-seam"></i></a>
                    <?php elseif (in_array($user_type, ['agencia', 'guia', 'local', 'super_admin'])): ?>
                        <a href="admin/dashboard.php" class="btn btn-primary btn-lg">Ir a mi Dashboard <i class="bi bi-speedometer"></i></a>
                    <?php endif; ?>
                <?php else: ?>
                    <h1 class="display-3 fw-bold text-white">Descubre el Corazón de África</h1>
                    <p class="lead text-white-50 mb-4">Tu aventura en Guinea Ecuatorial comienza aquí. Explora playas vírgenes, selvas exuberantes y una cultura vibrante.</p>
                    <a href="destinos.php" class="btn btn-primary btn-lg">Explorar Destinos <i class="bi bi-arrow-right"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header> -->

<!-- Sección de bienvenida alternativa si el video está comentado o falla -->
<?php if (!isset($is_logged_in) || !$is_logged_in): ?>
<header class="py-5 bg-primary text-white text-center">
    <div class="container">
        <h1 class="display-3 fw-bold">Descubre el Corazón de África</h1>
        <p class="lead mb-4">Tu aventura en Guinea Ecuatorial comienza aquí. Explora playas vírgenes, selvas exuberantes y una cultura vibrante.</p>
        <a href="destinos.php" class="btn btn-light btn-lg">Explorar Destinos <i class="bi bi-arrow-right"></i></a>
    </div>
</header>
<?php endif; ?>

<?php if ($is_logged_in): ?>
<header class="py-5 bg-primary text-white text-center">
    <div class="container">
        <h1 class="display-3 fw-bold">Bienvenido, <?= htmlspecialchars($user_name) ?>!</h1>
        <p class="lead mb-4">Tu portal personalizado para explorar Guinea Ecuatorial.</p>
        <?php if ($user_type === 'turista'): ?>
            <a href="itinerario.php" class="btn btn-light btn-lg">Mis Itinerarios <i class="bi bi-map"></i></a>
            <a href="mis_pedidos.php" class="btn btn-outline-light btn-lg ms-2">Mis Pedidos <i class="bi bi-box-seam"></i></a>
        <?php elseif (in_array($user_type, ['agencia', 'guia', 'local', 'super_admin'])): ?>
            <a href="admin/dashboard.php" class="btn btn-light btn-lg">Ir a mi Dashboard <i class="bi bi-speedometer"></i></a>
        <?php endif; ?>
    </div>
</header>
<?php endif; ?>

<!-- Sección de Búsqueda Avanzada -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="fw-bold text-center mb-4">Encuentra tu Aventura Perfecta</h2>
        <form action="search_results.php" method="GET" class="row g-3 align-items-center">
            <div class="col-md-6">
                <label for="search_query" class="visually-hidden">Buscar</label>
                <input type="text" class="form-control form-control-lg" id="search_query" name="query" placeholder="Buscar destinos, agencias, guías o locales...">
            </div>
            <div class="col-md-3">
                <label for="search_type" class="visually-hidden">Tipo</label>
                <select class="form-select form-select-lg" id="search_type" name="type">
                    <option value="all">Todos</option>
                    <option value="destinos">Destinos</option>
                    <option value="agencias">Agencias</option>
                    <option value="guias">Guías</option>
                    <option value="locales">Locales</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-lg w-100">Buscar</button>
            </div>
            <div class="col-12 text-center mt-3">
                <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearchCollapse" aria-expanded="false" aria-controls="advancedSearchCollapse">
                    Búsqueda Avanzada <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="collapse" id="advancedSearchCollapse">
                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label for="latitude" class="visually-hidden">Latitud</label>
                        <input type="text" class="form-control" id="latitude" name="latitude" placeholder="Latitud (ej: 1.50)">
                    </div>
                    <div class="col-md-4">
                        <label for="longitude" class="visually-hidden">Longitud</label>
                        <input type="text" class="form-control" id="longitude" name="longitude" placeholder="Longitud (ej: 10.00)">
                    </div>
                    <div class="col-md-4">
                        <label for="radius" class="visually-hidden">Radio (km)</label>
                        <input type="number" step="0.1" class="form-control" id="radius" name="radius" placeholder="Radio en km (ej: 50)">
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php if (!empty($carouseles_activos)): ?>
<!-- Carousel de Publicidades -->
<section class="py-5">
    <div class="container">
        <div id="homeCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($carouseles_activos as $index => $carousel_item): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <a href="<?= htmlspecialchars($carousel_item['enlace']) ?? '#' ?>">
                            <img src="assets/img/carouseles/<?= htmlspecialchars($carousel_item['ruta_imagen']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($carousel_item['nombre']) ?>">
                        </a>
                        <div class="carousel-caption d-none d-md-block">
                            <h5><?= htmlspecialchars($carousel_item['nombre']) ?></h5>
                            <!-- <p>Descripción opcional del carousel.</p> -->
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Sección de Recién Añadidos -->
<section class="py-5">
    <div class="container">
        <h2 class="fw-bold text-center mb-4">Explora lo Más Nuevo</h2>
        <?php if (count($recently_added_items) > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($recently_added_items as $item): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <?php 
                                $image_path = '';
                                if ($item['type'] === 'destino' && !empty($item['ruta_imagen'])) {
                                    $image_path = 'assets/img/destinos/' . $item['ruta_imagen'];
                                } else if ($item['type'] === 'agencia' && !empty($item['ruta_imagen'])) { // Usar imagen_perfil si existe
                                    $image_path = 'assets/img/agencias/' . $item['ruta_imagen'];
                                } else if ($item['type'] === 'agencia') { // Placeholder si no hay imagen de perfil
                                    $image_path = 'assets/img/agencias/default.jpg';
                                } else if ($item['type'] === 'guia' && !empty($item['ruta_imagen'])) { // Usar imagen_perfil si existe
                                    $image_path = 'assets/img/guias/' . $item['ruta_imagen'];
                                } else if ($item['type'] === 'guia') { // Placeholder si no hay imagen de perfil
                                    $image_path = 'assets/img/guias/default.jpg';
                                } else if ($item['type'] === 'local' && !empty($item['ruta_imagen'])) { // Usar imagen_perfil si existe
                                    $image_path = 'assets/img/locales/' . $item['ruta_imagen'];
                                } else if ($item['type'] === 'local') { // Placeholder si no hay imagen de perfil
                                    $image_path = 'assets/img/locales/default.jpg';
                                }
                            ?>
                            <?php if (!empty($image_path)): ?>
                                <img src="<?= $image_path ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars(ucfirst($item['type'])) ?></h6>
                            </div>
                            <div class="card-footer">
                                <?php 
                                    $detail_page = '#';
                                    if ($item['type'] === 'destino') $detail_page = 'detalle_destino.php?id=' . $item['id'];
                                    else if ($item['type'] === 'agencia') $detail_page = 'detalle_agencia.php?id=' . $item['id'];
                                    else if ($item['type'] === 'guia') $detail_page = 'detalle_guia.php?id=' . $item['id'];
                                    else if ($item['type'] === 'local') $detail_page = 'detalle_local.php?id=' . $item['id'];
                                ?>
                                <a href="<?= $detail_page ?>" class="btn btn-primary btn-sm">Ver Detalles</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">No hay elementos recién añadidos para mostrar.</div>
        <?php endif; ?>
    </div>
</section>

<?php if ($is_logged_in && $user_type === 'turista'): ?>
<!-- Sección de Resumen para Turistas -->
<section class="py-5">
    <div class="container">
        <h2 class="fw-bold mb-4">Tu Actividad Reciente</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Últimos Itinerarios</h5>
                        <?php if (count($recent_itineraries) > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recent_itineraries as $itinerary): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= htmlspecialchars($itinerary['nombre_itinerario']) ?>
                                        <a href="itinerario.php" class="btn btn-sm btn-outline-primary">Ver</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">Aún no has creado itinerarios. <a href="crear_itinerario.php">¡Crea uno ahora!</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Últimos Pedidos</h5>
                        <?php if (count($recent_orders) > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recent_orders as $order): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Pedido de <?= htmlspecialchars($order['tipo_item']) ?> a <?= htmlspecialchars($order['tipo_proveedor']) ?>
                                        <span class="badge bg-<?= ($order['estado'] == 'confirmado') ? 'success' : (($order['estado'] == 'cancelado') ? 'danger' : 'warning') ?>"><?= htmlspecialchars(ucfirst($order['estado'])) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No has realizado pedidos. <a href="destinos.php">¡Explora servicios!</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php elseif ($is_logged_in && in_array($user_type, ['agencia', 'guia', 'local'])): ?>
<!-- Sección de Resumen para Proveedores -->
<section class="py-5">
    <div class="container">
        <h2 class="fw-bold mb-4">Tu Resumen de Actividad</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Últimos Pedidos Recibidos</h5>
                        <?php if (count($recent_orders) > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recent_orders as $order): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Pedido de <?= htmlspecialchars($order['tipo_item']) ?> por <?= htmlspecialchars($order['turista_nombre']) ?>
                                        <span class="badge bg-<?= ($order['estado'] == 'confirmado') ? 'success' : (($order['estado'] == 'cancelado') ? 'danger' : 'warning') ?>"><?= htmlspecialchars(ucfirst($order['estado'])) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No has recibido pedidos recientes.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Enlaces Rápidos</h5>
                        <ul class="list-group list-group-flush">
                            <?php if ($user_type === 'agencia'): ?>
                                <li class="list-group-item"><a href="admin/manage_agencias.php">Gestionar mi Agencia</a></li>
                            <?php elseif ($user_type === 'guia'): ?>
                                <li class="list-group-item"><a href="admin/manage_guias.php">Gestionar mi Perfil de Guía</a></li>
                            <?php elseif ($user_type === 'local'): ?>
                                <li class="list-group-item"><a href="admin/manage_locales.php">Gestionar mi Local</a></li>
                            <?php endif; ?>
                            <li class="list-group-item"><a href="admin/dashboard.php">Ir al Dashboard</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php elseif ($is_logged_in && $user_type === 'super_admin'): ?>
<!-- Sección de Resumen para Super Admin -->
<section class="py-5">
    <div class="container">
        <h2 class="fw-bold mb-4">Resumen del Sistema</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Estadísticas Rápidas</h5>
                        <p class="card-text">Aquí se mostrarían estadísticas generales del sitio.</p>
                        <a href="admin/dashboard.php" class="btn btn-primary">Ir al Dashboard Completo</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Tareas Pendientes</h5>
                        <p class="card-text">Aquí se listarían tareas de administración pendientes (ej. nuevas agencias a aprobar).</p>
                        <a href="admin/manage_users.php" class="btn btn-outline-primary">Gestionar Usuarios</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!$is_logged_in || $user_type === 'turista'): // Mostrar secciones generales si no está logueado o es turista ?>
<!-- INTRO SECTION -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6" data-aos="fade-right">
                <h2 class="fw-bold">Planifica el Viaje de Tus Sueños</h2>
                <p class="text-muted">En GQ-Turismo, te ofrecemos las herramientas para descubrir la belleza oculta de Guinea Ecuatorial. Desde las playas paradisíacas de Corisco hasta la cima del Pico Basilé, tu próxima gran aventura está a solo un clic de distancia.</p>
                <p class="text-muted">Explora nuestros destinos, crea un itinerario personalizado y prepárate para una experiencia inolvidable.</p>
                <a href="about.php" class="btn btn-outline-primary">Conócenos Mejor</a>
            </div>
            <div class="col-md-6 text-center" data-aos="fade-left">
                <img src="assets/img/intro-image.jpg" class="img-fluid rounded shadow-lg" alt="Paisaje de Guinea Ecuatorial">
                 <!-- Deberías tener una imagen local en assets/img/intro-image.jpg -->
            </div>
        </div>
    </div>
</section>

<!-- DESTINATIONS GRID -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="fw-bold">Destinos Populares</h2>
            <p class="text-muted">Una selección de lugares que no te puedes perder.</p>
        </div>
        <div id="destinos-grid-container" class="row g-4">
            <!-- Indicador de Carga -->
            <div class="col-12 text-center" id="destinos-loading-spinner">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 text-muted">Cargando destinos...</p>
            </div>
            <!-- Los destinos se cargarán aquí dinámicamente -->
        </div>
        <div class="text-center mt-5">
            <a href="destinos.php" class="btn btn-primary">Ver Todos los Destinos</a>
        </div>
    </div>
</section>

<!-- CTA SECTION -->
<section class="py-5 text-white" style="background-color: var(--bs-primary);">
    <div class="container text-center" data-aos="fade-in">
        <h2 class="fw-bold">¿Listo para la Aventura?</h2>
        <p class="lead mb-4">Crea tu itinerario personalizado y vive una experiencia única en Guinea Ecuatorial.</p>
        <a href="itinerario.php" class="btn btn-light btn-lg">¡Comienza a Planificar! <i class="bi bi-pencil-square"></i></a>
    </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
