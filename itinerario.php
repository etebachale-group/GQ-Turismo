<?php
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

// Verificar que sea turista
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Obtener itinerarios del usuario
$itinerarios = [];
$sql = "SELECT 
            i.id,
            i.nombre_itinerario,
            i.estado,
            i.fecha_inicio,
            i.fecha_fin,
            i.presupuesto_estimado,
            i.ciudad,
            i.notas,
            i.precio_total,
            i.fecha_creacion,
            COUNT(DISTINCT id_dest.id_destino) as total_destinos 
        FROM itinerarios i 
        LEFT JOIN itinerario_destinos id_dest ON i.id = id_dest.id_itinerario 
        WHERE i.id_usuario = ? 
        GROUP BY i.id 
        ORDER BY i.fecha_creacion DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $itinerarios[] = $row;
}
$stmt->close();

// Estadísticas
$stats = [
    'total' => count($itinerarios),
    'planificacion' => 0,
    'confirmado' => 0,
    'completado' => 0,
    'destinos_unicos' => 0
];

$destinos_set = [];
foreach ($itinerarios as $it) {
    $stats[$it['estado']]++;
    
    // Obtener destinos únicos
    $stmt_d = $conn->prepare("SELECT id_destino FROM itinerario_destinos WHERE id_itinerario = ?");
    $stmt_d->bind_param("i", $it['id']);
    $stmt_d->execute();
    $res_d = $stmt_d->get_result();
    while ($row_d = $res_d->fetch_assoc()) {
        $destinos_set[$row_d['id_destino']] = true;
    }
    $stmt_d->close();
}
$stats['destinos_unicos'] = count($destinos_set);

// Obtener recomendaciones (guías, locales, agencias)
$guias_recomendados = [];
$sql_guias = "SELECT id, nombre_guia, especialidades, precio_hora, descripcion, imagen_perfil 
              FROM guias_turisticos ORDER BY RAND() LIMIT 6";
$result_guias = $conn->query($sql_guias);
while ($row = $result_guias->fetch_assoc()) {
    $guias_recomendados[] = $row;
}

$locales_recomendados = [];
$sql_locales = "SELECT id, nombre_local, tipo_local, direccion, descripcion, imagen_perfil 
                FROM lugares_locales ORDER BY RAND() LIMIT 6";
$result_locales = $conn->query($sql_locales);
while ($row = $result_locales->fetch_assoc()) {
    $locales_recomendados[] = $row;
}

$agencias_recomendadas = [];
$sql_agencias = "SELECT id, nombre_agencia, descripcion, imagen_perfil 
                 FROM agencias ORDER BY RAND() LIMIT 3";
$result_agencias = $conn->query($sql_agencias);
while ($row = $result_agencias->fetch_assoc()) {
    $agencias_recomendadas[] = $row;
}

$conn->close();
?>

<style>
.hero-itinerarios {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 4rem 0;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
}

.hero-itinerarios::before {
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

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.itinerario-card {
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.itinerario-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.875rem;
}

.recommendation-card {
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: none;
}

.recommendation-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state-icon {
    font-size: 5rem;
    color: #e9ecef;
    margin-bottom: 2rem;
}

.search-filter-bar {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}
</style>

<section class="hero-itinerarios text-white">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-3 fw-bold mb-3" data-aos="fade-down">
                    <i class="bi bi-map-fill me-3"></i>Mis Itinerarios
                </h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    Gestiona tus viajes y planifica nuevas aventuras
                </p>
                <div data-aos="fade-up" data-aos-delay="200">
                    <a href="crear_itinerario.php" class="btn btn-light btn-lg">
                        <i class="bi bi-plus-circle me-2"></i>Crear Nuevo Itinerario
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <!-- Estadísticas -->
    <div class="row g-4 mb-5" data-aos="fade-up">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary mx-auto">
                    <i class="bi bi-collection-fill"></i>
                </div>
                <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                <p class="text-muted mb-0">Total Itinerarios</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger mx-auto">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <h3 class="mb-0"><?php echo $stats['destinos_unicos']; ?></h3>
                <p class="text-muted mb-0">Destinos Únicos</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h3 class="mb-0"><?php echo $stats['planificacion']; ?></h3>
                <p class="text-muted mb-0">En Planificación</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h3 class="mb-0"><?php echo $stats['completado']; ?></h3>
                <p class="text-muted mb-0">Completados</p>
            </div>
        </div>
    </div>

    <!-- Barra de búsqueda y filtros -->
    <div class="search-filter-bar" data-aos="fade-up">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Buscar itinerarios...">
                </div>
            </div>
            <div class="col-md-6">
                <select class="form-select" id="filterStatus">
                    <option value="all">Todos los estados</option>
                    <option value="planificacion">En Planificación</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="completado">Completado</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Lista de Itinerarios -->
    <?php if (count($itinerarios) > 0): ?>
    <div class="row g-4 mb-5" id="itinerariosContainer">
        <?php foreach ($itinerarios as $index => $it): ?>
        <div class="col-lg-6 itinerario-item" 
             data-estado="<?php echo htmlspecialchars($it['estado']); ?>"
             data-nombre="<?php echo strtolower(htmlspecialchars($it['nombre_itinerario'])); ?>"
             data-aos="fade-up" 
             data-aos-delay="<?php echo $index * 100; ?>">
            <div class="card itinerario-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h4 class="card-title fw-bold mb-0">
                            <?php echo htmlspecialchars($it['nombre_itinerario']); ?>
                        </h4>
                        <?php
                        $badge_colors = [
                            'planificacion' => 'warning',
                            'confirmado' => 'info',
                            'completado' => 'success'
                        ];
                        $badge_icons = [
                            'planificacion' => 'clock-history',
                            'confirmado' => 'check-circle',
                            'completado' => 'check-circle-fill'
                        ];
                        $badge_texts = [
                            'planificacion' => 'Planificación',
                            'confirmado' => 'Confirmado',
                            'completado' => 'Completado'
                        ];
                        ?>
                        <span class="status-badge bg-<?php echo $badge_colors[$it['estado']]; ?> text-white">
                            <i class="bi bi-<?php echo $badge_icons[$it['estado']]; ?> me-1"></i>
                            <?php echo $badge_texts[$it['estado']]; ?>
                        </span>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <?php if (!empty($it['fecha_inicio'])): ?>
                        <div class="col-md-6">
                            <small class="text-muted d-block">
                                <i class="bi bi-calendar-event me-1"></i>
                                <strong>Inicio:</strong> <?php echo date('d/m/Y', strtotime($it['fecha_inicio'])); ?>
                            </small>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($it['fecha_fin'])): ?>
                        <div class="col-md-6">
                            <small class="text-muted d-block">
                                <i class="bi bi-calendar-check me-1"></i>
                                <strong>Fin:</strong> <?php echo date('d/m/Y', strtotime($it['fecha_fin'])); ?>
                            </small>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-6">
                            <small class="text-muted d-block">
                                <i class="bi bi-geo-alt-fill me-1"></i>
                                <strong>Destinos:</strong> <?php echo $it['total_destinos']; ?>
                            </small>
                        </div>
                        
                        <?php if (isset($it['presupuesto_estimado']) && $it['presupuesto_estimado'] > 0): ?>
                        <div class="col-md-6">
                            <small class="text-muted d-block">
                                <i class="bi bi-cash me-1"></i>
                                <strong>Presupuesto:</strong> <?php echo number_format($it['presupuesto_estimado'], 2); ?> €
                            </small>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($it['ciudad'])): ?>
                        <div class="col-12">
                            <small class="text-muted d-block">
                                <i class="bi bi-pin-map-fill me-1"></i>
                                <strong>Ciudad:</strong> <?php echo htmlspecialchars($it['ciudad']); ?>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($it['notas'])): ?>
                    <p class="card-text text-muted small mb-3">
                        <?php echo htmlspecialchars(substr($it['notas'], 0, 120)); ?>
                        <?php echo strlen($it['notas']) > 120 ? '...' : ''; ?>
                    </p>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-2 flex-wrap">
                        <?php if ($it['estado'] === 'confirmado' || $it['estado'] === 'completado'): ?>
                        <a href="seguimiento_itinerario.php?id=<?php echo $it['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-map-fill me-1"></i>Seguimiento
                        </a>
                        <?php endif; ?>
                        <a href="crear_itinerario.php?edit=<?php echo $it['id']; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </a>
                        <a href="reservas.php?itinerario=<?php echo $it['id']; ?>" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-calendar-check me-1"></i>Reservar
                        </a>
                        <button class="btn btn-outline-danger btn-sm ms-auto" onclick="eliminarItinerario(<?php echo $it['id']; ?>, '<?php echo htmlspecialchars($it['nombre_itinerario']); ?>')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state" data-aos="fade-up">
        <div class="empty-state-icon">
            <i class="bi bi-map"></i>
        </div>
        <h3 class="fw-bold mb-3">No tienes itinerarios aún</h3>
        <p class="text-muted mb-4">Comienza a planificar tu próxima aventura creando tu primer itinerario</p>
        <a href="crear_itinerario.php" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-circle me-2"></i>Crear Primer Itinerario
        </a>
        <div class="mt-4">
            <a href="destinos.php" class="btn btn-outline-primary">
                <i class="bi bi-compass me-2"></i>Explorar Destinos
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recomendaciones -->
    <?php if (count($guias_recomendados) > 0): ?>
    <div class="mt-5" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-person-badge text-primary me-2"></i>
                Guías Recomendados
            </h3>
            <a href="guias.php" class="btn btn-sm btn-outline-primary">Ver Todos</a>
        </div>
        <div class="row g-3">
            <?php foreach ($guias_recomendados as $guia): ?>
            <div class="col-md-4 col-lg-2">
                <div class="card recommendation-card h-100 shadow-sm">
                    <?php if (!empty($guia['imagen_perfil'])): ?>
                    <img src="assets/img/guias/<?php echo htmlspecialchars($guia['imagen_perfil']); ?>" 
                         class="card-img-top" style="height: 120px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-1 small"><?php echo htmlspecialchars($guia['nombre_guia']); ?></h6>
                        <p class="text-muted mb-2 small">
                            <?php echo htmlspecialchars(substr($guia['especialidades'], 0, 30)); ?>...
                        </p>
                        <p class="text-primary fw-bold mb-2 small"><?php echo number_format($guia['precio_hora'], 2); ?> €/h</p>
                        <a href="detalle_guia.php?id=<?php echo $guia['id']; ?>" class="btn btn-sm btn-outline-primary w-100">
                            Ver Perfil
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (count($locales_recomendados) > 0): ?>
    <div class="mt-5" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-shop text-success me-2"></i>
                Locales y Restaurantes
            </h3>
            <a href="locales.php" class="btn btn-sm btn-outline-success">Ver Todos</a>
        </div>
        <div class="row g-3">
            <?php foreach ($locales_recomendados as $local): ?>
            <div class="col-md-4 col-lg-2">
                <div class="card recommendation-card h-100 shadow-sm">
                    <?php if (!empty($local['imagen_perfil'])): ?>
                    <img src="assets/img/locales/<?php echo htmlspecialchars($local['imagen_perfil']); ?>" 
                         class="card-img-top" style="height: 120px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body p-3">
                        <span class="badge bg-success mb-2 small"><?php echo htmlspecialchars($local['tipo_local']); ?></span>
                        <h6 class="fw-bold mb-1 small"><?php echo htmlspecialchars($local['nombre_local']); ?></h6>
                        <p class="text-muted mb-2 small">
                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars(substr($local['direccion'], 0, 25)); ?>...
                        </p>
                        <a href="detalle_local.php?id=<?php echo $local['id']; ?>" class="btn btn-sm btn-outline-success w-100">
                            Ver Más
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (count($agencias_recomendadas) > 0): ?>
    <div class="mt-5" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-building text-info me-2"></i>
                Agencias de Viaje
            </h3>
            <a href="agencias.php" class="btn btn-sm btn-outline-info">Ver Todas</a>
        </div>
        <div class="row g-3">
            <?php foreach ($agencias_recomendadas as $agencia): ?>
            <div class="col-md-4">
                <div class="card recommendation-card h-100 shadow-sm">
                    <?php if (!empty($agencia['imagen_perfil'])): ?>
                    <img src="assets/img/agencias/<?php echo htmlspecialchars($agencia['imagen_perfil']); ?>" 
                         class="card-img-top" style="height: 150px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($agencia['nombre_agencia']); ?></h5>
                        <p class="card-text text-muted small">
                            <?php echo htmlspecialchars(substr($agencia['descripcion'], 0, 80)); ?>...
                        </p>
                        <a href="detalle_agencia.php?id=<?php echo $agencia['id']; ?>" class="btn btn-outline-info w-100">
                            Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Búsqueda y filtros
document.getElementById('searchInput').addEventListener('input', filterItinerarios);
document.getElementById('filterStatus').addEventListener('change', filterItinerarios);

function filterItinerarios() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    
    document.querySelectorAll('.itinerario-item').forEach(item => {
        const nombre = item.dataset.nombre;
        const estado = item.dataset.estado;
        
        const matchSearch = !search || nombre.includes(search);
        const matchStatus = status === 'all' || estado === status;
        
        item.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
}

// Eliminar itinerario
function eliminarItinerario(id, nombre) {
    if (!confirm(`¿Estás seguro de eliminar el itinerario "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        return;
    }
    
    fetch('api/itinerarios.php?action=delete&id=' + id, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error al eliminar el itinerario');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
