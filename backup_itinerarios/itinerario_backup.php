<?php 
require_once 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    header('Location: index.php');
    exit;
}

$id_usuario = $_SESSION['user_id'];
$itinerarios = [];
$destinos_en_itinerarios = [];
$categorias_destinos = [];
$guias_recomendados = [];
$locales_recomendados = [];
$agencias_recomendadas = [];

// Obtener itinerarios del usuario con más detalles
$sql_itinerarios = "SELECT 
        i.id, 
        i.nombre_itinerario, 
        i.fecha_creacion,
        i.fecha_inicio,
        i.fecha_fin,
        i.presupuesto_estimado,
        i.notas,
        i.estado,
        GROUP_CONCAT(DISTINCT d.id) as ids_destinos, 
        GROUP_CONCAT(DISTINCT d.nombre ORDER BY id.orden SEPARATOR ', ') as nombres_destinos,
        GROUP_CONCAT(DISTINCT d.categoria) as categorias,
        COUNT(DISTINCT id.id_destino) as total_destinos
    FROM itinerarios i
    LEFT JOIN itinerario_destinos id ON i.id = id.id_itinerario
    LEFT JOIN destinos d ON id.id_destino = d.id
    WHERE i.id_usuario = ?
    GROUP BY i.id
    ORDER BY i.fecha_creacion DESC";

$stmt_itinerarios = $conn->prepare($sql_itinerarios);
$stmt_itinerarios->bind_param("i", $id_usuario);
$stmt_itinerarios->execute();
$result_itinerarios = $stmt_itinerarios->get_result();

while ($row = $result_itinerarios->fetch_assoc()) {
    $itinerarios[] = $row;
    if (!empty($row['ids_destinos'])) {
        $destinos_en_itinerarios = array_merge($destinos_en_itinerarios, explode(',', $row['ids_destinos']));
    }
}
$stmt_itinerarios->close();

// Obtener categorías únicas de los destinos en los itinerarios
if (!empty($destinos_en_itinerarios)) {
    $destinos_unicos = array_unique($destinos_en_itinerarios);
    $placeholders = implode(',', array_fill(0, count($destinos_unicos), '?'));
    $sql_categorias = "SELECT DISTINCT categoria FROM destinos WHERE id IN ($placeholders)";
    $stmt_categorias = $conn->prepare($sql_categorias);
    $types = str_repeat('i', count($destinos_unicos));
    $stmt_categorias->bind_param($types, ...$destinos_unicos);
    $stmt_categorias->execute();
    $result_categorias = $stmt_categorias->get_result();
    while ($row = $result_categorias->fetch_assoc()) {
        if (!empty($row['categoria'])) {
            $categorias_destinos[] = $row['categoria'];
        }
    }
    $stmt_categorias->close();
}

// Buscar guías recomendados
if (!empty($categorias_destinos)) {
    $search_terms = [];
    foreach ($categorias_destinos as $cat) {
        $search_terms[] = "especialidades LIKE '%" . $conn->real_escape_string($cat) . "%'";
    }
    $sql_guias = "SELECT id, nombre_guia, descripcion, especialidades, precio_hora, contacto_email 
                  FROM guias_turisticos 
                  WHERE " . implode(' OR ', $search_terms) . " 
                  LIMIT 6";
    $result_guias = $conn->query($sql_guias);
    if ($result_guias) {
        while ($row = $result_guias->fetch_assoc()) {
            $guias_recomendados[] = $row;
        }
    }
}

// Buscar locales recomendados
if (!empty($categorias_destinos)) {
    $search_terms = [];
    foreach ($categorias_destinos as $cat) {
        $search_terms[] = "tipo_local LIKE '%" . $conn->real_escape_string($cat) . "%'";
    }
    if (empty($search_terms)) {
        $search_terms[] = "tipo_local IN ('Restaurante', 'Hotel', 'Atracción', 'Café', 'Bar')";
    }
    $sql_locales = "SELECT id, nombre_local, descripcion, tipo_local, direccion, contacto_email, imagen_perfil 
                    FROM lugares_locales 
                    WHERE " . implode(' OR ', $search_terms) . " 
                    LIMIT 6";
    $result_locales = $conn->query($sql_locales);
    if ($result_locales) {
        while ($row = $result_locales->fetch_assoc()) {
            $locales_recomendados[] = $row;
        }
    }
}

// Buscar agencias recomendadas
$sql_agencias = "SELECT id, nombre_agencia, descripcion, contacto_email, imagen_perfil 
                 FROM agencias 
                 ORDER BY RAND() 
                 LIMIT 3";
$result_agencias = $conn->query($sql_agencias);
if ($result_agencias) {
    while ($row = $result_agencias->fetch_assoc()) {
        $agencias_recomendadas[] = $row;
    }
}

$conn->close();
?>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
    background-size: cover;
    background-position: bottom;
}

.itinerary-card {
    transition: all 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
    border: none;
}

.itinerary-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
}

.itinerary-card .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 1.25rem;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.875rem;
}

.status-planificacion {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.status-confirmado {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.status-completado {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
}

.recommendation-card {
    transition: all 0.3s ease;
    border-radius: 12px;
    border: none;
    overflow: hidden;
}

.recommendation-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

.icon-box {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.5rem;
}

.empty-state {
    padding: 4rem 2rem;
    text-align: center;
}

.empty-state .icon {
    font-size: 5rem;
    opacity: 0.3;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.stat-card .icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.stat-card .value {
    font-size: 2rem;
    font-weight: 700;
    color: #667eea;
}

.stat-card .label {
    color: #6c757d;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

@media (max-width: 768px) {
    .hero-section h1 {
        font-size: 2rem !important;
    }
}
</style>

<!-- Hero Section -->
<section class="hero-section text-white py-5" data-aos="fade-in">
    <div class="container position-relative">
        <div class="row align-items-center py-4">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-3">
                    <i class="bi bi-map-fill me-3"></i>Mis Itinerarios
                </h1>
                <p class="lead mb-4">Organiza y gestiona tus aventuras en Guinea Ecuatorial</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="crear_itinerario.php" class="btn btn-light btn-lg shadow">
                        <i class="bi bi-plus-circle me-2"></i>Crear Nuevo Itinerario
                    </a>
                    <a href="destinos.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-compass me-2"></i>Explorar Destinos
                    </a>
                </div>
            </div>
            <div class="col-lg-4 d-none d-lg-block text-center">
                <i class="bi bi-geo-alt-fill" style="font-size: 10rem; opacity: 0.2;"></i>
            </div>
        </div>
    </div>
</section>

<?php if (count($itinerarios) > 0): ?>
<!-- Estadísticas -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="icon">
                        <i class="bi bi-map text-primary"></i>
                    </div>
                    <div class="value"><?php echo count($itinerarios); ?></div>
                    <div class="label">Itinerarios</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="icon">
                        <i class="bi bi-geo-alt-fill text-danger"></i>
                    </div>
                    <div class="value"><?php echo count(array_unique($destinos_en_itinerarios)); ?></div>
                    <div class="label">Destinos</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="icon">
                        <i class="bi bi-calendar-check text-success"></i>
                    </div>
                    <div class="value">
                        <?php 
                        $proximos = array_filter($itinerarios, function($it) {
                            return !empty($it['fecha_inicio']) && strtotime($it['fecha_inicio']) >= time();
                        });
                        echo count($proximos);
                        ?>
                    </div>
                    <div class="label">Próximos</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="icon">
                        <i class="bi bi-check-circle text-info"></i>
                    </div>
                    <div class="value">
                        <?php 
                        $completados = array_filter($itinerarios, function($it) {
                            return isset($it['estado']) && $it['estado'] === 'completado';
                        });
                        echo count($completados);
                        ?>
                    </div>
                    <div class="label">Completados</div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="container py-5">
    <!-- Filtros y búsqueda -->
    <?php if (count($itinerarios) > 3): ?>
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control border-start-0" id="searchItinerary" 
                       placeholder="Buscar itinerario por nombre o destino...">
            </div>
        </div>
        <div class="col-md-4">
            <select class="form-select form-select-lg" id="filterStatus">
                <option value="">Todos los estados</option>
                <option value="planificacion">En Planificación</option>
                <option value="confirmado">Confirmado</option>
                <option value="completado">Completado</option>
            </select>
        </div>
    </div>
    <?php endif; ?>

    <div id="itineraries-list">
        <?php if (count($itinerarios) > 0): ?>
            <div class="row g-4">
                <?php foreach ($itinerarios as $itinerario): ?>
                    <div class="col-md-6 col-lg-4 itinerary-item" 
                         data-aos="fade-up" 
                         data-status="<?php echo htmlspecialchars($itinerario['estado'] ?? 'planificacion'); ?>"
                         data-name="<?php echo htmlspecialchars(strtolower($itinerario['nombre_itinerario'] . ' ' . ($itinerario['nombres_destinos'] ?? ''))); ?>">
                        <div class="card itinerary-card h-100 shadow-sm">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="mb-0 fw-bold text-white">
                                        <i class="bi bi-map me-2"></i>
                                        <?php echo htmlspecialchars($itinerario['nombre_itinerario']); ?>
                                    </h5>
                                    <?php 
                                    $estado = $itinerario['estado'] ?? 'planificacion';
                                    $estado_texto = [
                                        'planificacion' => 'En Planificación',
                                        'confirmado' => 'Confirmado',
                                        'completado' => 'Completado'
                                    ];
                                    ?>
                                    <span class="status-badge status-<?php echo $estado; ?>">
                                        <?php echo $estado_texto[$estado]; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($itinerario['nombres_destinos'])): ?>
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-geo-alt-fill text-danger me-2"></i>Destinos:
                                </h6>
                                <p class="mb-3"><?php echo htmlspecialchars($itinerario['nombres_destinos']); ?></p>
                                <?php else: ?>
                                <p class="text-muted fst-italic mb-3">
                                    <i class="bi bi-info-circle me-2"></i>Sin destinos asignados aún
                                </p>
                                <?php endif; ?>
                                
                                <div class="d-flex gap-2 flex-wrap mb-3">
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?php echo $itinerario['total_destinos'] ?? 0; ?> destinos
                                    </span>
                                    <?php if (!empty($itinerario['fecha_inicio'])): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($itinerario['fecha_inicio'])); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php if (!empty($itinerario['presupuesto_estimado']) && $itinerario['presupuesto_estimado'] > 0): ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-cash me-1"></i>
                                        <?php echo number_format($itinerario['presupuesto_estimado'], 2); ?> €
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($itinerario['notas'])): ?>
                                <div class="alert alert-light border mb-0">
                                    <small class="text-muted">
                                        <i class="bi bi-sticky me-2"></i>
                                        <?php echo htmlspecialchars(substr($itinerario['notas'], 0, 80)) . (strlen($itinerario['notas']) > 80 ? '...' : ''); ?>
                                    </small>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <div class="d-flex gap-2">
                                    <a href="crear_itinerario.php?edit=<?php echo $itinerario['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary flex-fill" title="Editar itinerario">
                                        <i class="bi bi-pencil me-1"></i>Editar
                                    </a>
                                    <a href="reservas.php?itinerario=<?php echo $itinerario['id']; ?>" 
                                       class="btn btn-sm btn-success flex-fill" title="Hacer reservas">
                                        <i class="bi bi-check-circle me-1"></i>Reservar
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger delete-itinerary" 
                                            data-id="<?php echo $itinerario['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($itinerario['nombre_itinerario']); ?>"
                                            title="Eliminar itinerario">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">
                    <i class="bi bi-map text-muted"></i>
                </div>
                <h3 class="fw-bold mb-3">Aún no has creado ningún itinerario</h3>
                <p class="lead text-muted mb-4">
                    Comienza a planificar tu aventura en Guinea Ecuatorial.<br>
                    Descubre destinos increíbles y crea tu itinerario personalizado.
                </p>
                <a href="crear_itinerario.php" class="btn btn-lg btn-primary px-5">
                    <i class="bi bi-plus-circle me-2"></i>Crear Mi Primer Itinerario
                </a>
                <div class="mt-4">
                    <a href="destinos.php" class="btn btn-outline-secondary">
                        <i class="bi bi-compass me-2"></i>Ver Destinos Disponibles
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div id="itinerary-delete-feedback" class="mt-3"></div>

    <?php if (count($itinerarios) > 0): ?>
        <!-- Recomendaciones Section -->
        <section class="mt-5 pt-5 border-top">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold">
                    <i class="bi bi-stars text-warning me-2"></i>
                    Recomendaciones para tu Viaje
                </h2>
                <p class="lead text-muted">Servicios seleccionados según tus intereses y destinos</p>
            </div>

            <!-- Guías Recomendados -->
            <?php if (!empty($guias_recomendados)): ?>
                <div class="mb-5">
                    <h3 class="mb-4" data-aos="fade-up">
                        <i class="bi bi-person-badge text-primary me-2"></i>
                        Guías Turísticos Recomendados
                    </h3>
                    <div class="row g-4">
                        <?php foreach ($guias_recomendados as $index => $guia): ?>
                            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                                <div class="card recommendation-card h-100 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="icon-box bg-primary bg-opacity-10 me-3">
                                                <i class="bi bi-person-fill text-primary"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1 fw-bold">
                                                    <?php echo htmlspecialchars($guia['nombre_guia']); ?>
                                                </h5>
                                                <p class="text-muted small mb-0">Guía Profesional</p>
                                            </div>
                                        </div>
                                        
                                        <p class="text-muted small mb-3">
                                            <?php echo htmlspecialchars(substr($guia['descripcion'] ?? 'Guía experimentado', 0, 100)) . '...'; ?>
                                        </p>
                                        
                                        <div class="mb-3">
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                <i class="bi bi-star-fill me-1"></i>
                                                <?php echo htmlspecialchars($guia['especialidades']); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="alert alert-success mb-3 py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="small"><i class="bi bi-cash-coin me-2"></i>Precio/hora:</span>
                                                <strong class="fs-5"><?php echo number_format($guia['precio_hora'], 2); ?> €</strong>
                                            </div>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <a href="detalle_guia.php?id=<?php echo $guia['id']; ?>" class="btn btn-primary">
                                                <i class="bi bi-eye me-2"></i>Ver Perfil Completo
                                            </a>
                                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'turista'): ?>
                                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" 
                                                    data-bs-target="#sendMessageModal"
                                                    data-receiver-id="<?php echo $guia['id']; ?>"
                                                    data-receiver-type="guia"
                                                    data-receiver-name="<?php echo htmlspecialchars($guia['nombre_guia']); ?>">
                                                <i class="bi bi-chat-dots me-1"></i>Contactar
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Locales Recomendados -->
            <?php if (!empty($locales_recomendados)): ?>
                <div class="mb-5">
                    <h3 class="mb-4" data-aos="fade-up">
                        <i class="bi bi-shop text-success me-2"></i>
                        Lugares y Locales Recomendados
                    </h3>
                    <div class="row g-4">
                        <?php foreach ($locales_recomendados as $index => $local): ?>
                            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                                <div class="card recommendation-card h-100 shadow-sm">
                                    <?php if (!empty($local['imagen_perfil'])): ?>
                                    <img src="assets/img/locales/<?php echo htmlspecialchars($local['imagen_perfil']); ?>" 
                                         class="card-img-top" style="height: 180px; object-fit: cover;" 
                                         alt="<?php echo htmlspecialchars($local['nombre_local']); ?>">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="icon-box bg-success bg-opacity-10 me-3">
                                                <i class="bi bi-shop text-success"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1 fw-bold">
                                                    <?php echo htmlspecialchars($local['nombre_local']); ?>
                                                </h5>
                                                <span class="badge bg-success bg-opacity-10 text-success">
                                                    <?php echo htmlspecialchars($local['tipo_local']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <p class="text-muted small mb-3">
                                            <?php echo htmlspecialchars(substr($local['descripcion'] ?? 'Local recomendado', 0, 100)) . '...'; ?>
                                        </p>
                                        
                                        <?php if (!empty($local['direccion'])): ?>
                                        <p class="text-muted small mb-3">
                                            <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                            <?php echo htmlspecialchars($local['direccion']); ?>
                                        </p>
                                        <?php endif; ?>
                                        
                                        <div class="d-grid gap-2">
                                            <a href="detalle_local.php?id=<?php echo $local['id']; ?>" class="btn btn-outline-success">
                                                <i class="bi bi-eye me-2"></i>Ver Detalles
                                            </a>
                                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'turista'): ?>
                                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" 
                                                    data-bs-target="#sendMessageModal"
                                                    data-receiver-id="<?php echo $local['id']; ?>"
                                                    data-receiver-type="local"
                                                    data-receiver-name="<?php echo htmlspecialchars($local['nombre_local']); ?>">
                                                <i class="bi bi-chat-dots me-1"></i>Contactar
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Agencias Recomendadas -->
            <?php if (!empty($agencias_recomendadas)): ?>
                <div class="mb-5">
                    <h3 class="mb-4" data-aos="fade-up">
                        <i class="bi bi-building text-info me-2"></i>
                        Agencias de Viajes Asociadas
                    </h3>
                    <div class="row g-4">
                        <?php foreach ($agencias_recomendadas as $index => $agencia): ?>
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                                <div class="card recommendation-card h-100 shadow-sm">
                                    <?php if (!empty($agencia['imagen_perfil'])): ?>
                                    <img src="assets/img/agencias/<?php echo htmlspecialchars($agencia['imagen_perfil']); ?>" 
                                         class="card-img-top" style="height: 180px; object-fit: cover;" 
                                         alt="<?php echo htmlspecialchars($agencia['nombre_agencia']); ?>">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold mb-3">
                                            <i class="bi bi-building me-2 text-info"></i>
                                            <?php echo htmlspecialchars($agencia['nombre_agencia']); ?>
                                        </h5>
                                        
                                        <p class="text-muted small mb-3">
                                            <?php echo htmlspecialchars(substr($agencia['descripcion'] ?? 'Agencia de viajes profesional', 0, 100)) . '...'; ?>
                                        </p>
                                        
                                        <div class="d-grid gap-2">
                                            <a href="detalle_agencia.php?id=<?php echo $agencia['id']; ?>" class="btn btn-info">
                                                <i class="bi bi-eye me-2"></i>Ver Servicios
                                            </a>
                                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'turista'): ?>
                                            <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" 
                                                    data-bs-target="#sendMessageModal"
                                                    data-receiver-id="<?php echo $agencia['id']; ?>"
                                                    data-receiver-type="agencia"
                                                    data-receiver-name="<?php echo htmlspecialchars($agencia['nombre_agencia']); ?>">
                                                <i class="bi bi-chat-dots me-1"></i>Contactar
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($guias_recomendados) && empty($locales_recomendados) && empty($agencias_recomendadas)): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    Aún no hay recomendaciones disponibles. Agrega destinos a tus itinerarios para obtener sugerencias personalizadas.
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>

<!-- Modal para Enviar Mensaje -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sendMessageModalLabel">Enviar Mensaje a <span id="modalReceiverName"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="sendMessageForm">
          <input type="hidden" id="messageReceiverId" name="receiver_id">
          <input type="hidden" id="messageReceiverType" name="receiver_type">
          <div class="mb-3">
            <label for="messageContent" class="form-label">Tu Mensaje</label>
            <textarea class="form-control" id="messageContent" name="message_content" rows="5" 
                      placeholder="Escribe tu consulta aquí..." required></textarea>
          </div>
          <div id="sendMessageResponse" class="mt-3"></div>
          <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-send-fill me-2"></i>Enviar Mensaje
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Eliminar itinerario
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-itinerary').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name || 'este itinerario';
            
            if (confirm(`¿Estás seguro de que deseas eliminar "${name}"?\n\nEsta acción no se puede deshacer.`)) {
                fetch('api/itinerarios.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remover la tarjeta con animación
                        const card = this.closest('.itinerary-item');
                        card.style.transition = 'all 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.8)';
                        
                        setTimeout(() => {
                            card.remove();
                            
                            // Mostrar mensaje de éxito
                            const feedback = document.getElementById('itinerary-delete-feedback');
                            feedback.innerHTML = `<div class="alert alert-success alert-dismissible fade show">
                                <i class="bi bi-check-circle-fill me-2"></i>${data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>`;
                            
                            // Verificar si ya no hay itinerarios
                            const remaining = document.querySelectorAll('.itinerary-item').length;
                            if (remaining === 0) {
                                location.reload();
                            }
                        }, 300);
                    } else {
                        const feedback = document.getElementById('itinerary-delete-feedback');
                        feedback.innerHTML = `<div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const feedback = document.getElementById('itinerary-delete-feedback');
                    feedback.innerHTML = `<div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Error al eliminar el itinerario.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`;
                });
            }
        });
    });

    // Búsqueda de itinerarios
    const searchInput = document.getElementById('searchItinerary');
    if (searchInput) {
        searchInput.addEventListener('input', filterItineraries);
    }
    
    // Filtro por estado
    const filterStatus = document.getElementById('filterStatus');
    if (filterStatus) {
        filterStatus.addEventListener('change', filterItineraries);
    }
    
    function filterItineraries() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const statusFilter = filterStatus ? filterStatus.value : '';
        
        document.querySelectorAll('.itinerary-item').forEach(item => {
            const name = item.dataset.name || '';
            const status = item.dataset.status || '';
            
            const matchesSearch = name.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;
            
            if (matchesSearch && matchesStatus) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Modal de envío de mensaje
    const sendMessageModal = document.getElementById('sendMessageModal');
    if (sendMessageModal) {
        sendMessageModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('modalReceiverName').textContent = button.dataset.receiverName;
            document.getElementById('messageReceiverId').value = button.dataset.receiverId;
            document.getElementById('messageReceiverType').value = button.dataset.receiverType;
            document.getElementById('messageContent').value = '';
            document.getElementById('sendMessageResponse').innerHTML = '';
        });

        document.getElementById('sendMessageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById('sendMessageResponse').innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split me-2"></i>Enviando mensaje...</div>';

            const formData = {
                receiver_id: document.getElementById('messageReceiverId').value,
                receiver_type: document.getElementById('messageReceiverType').value,
                message: document.getElementById('messageContent').value
            };

            fetch('api/start_conversation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('sendMessageResponse').innerHTML = `<div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>${data.message}</div>`;
                    document.getElementById('sendMessageForm').reset();
                    setTimeout(() => {
                        window.location.href = data.redirect || 'mis_mensajes.php';
                    }, 2000);
                } else {
                    document.getElementById('sendMessageResponse').innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('sendMessageResponse').innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error al enviar el mensaje.</div>';
            });
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>