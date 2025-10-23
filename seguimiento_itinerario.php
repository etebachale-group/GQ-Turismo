<?php
session_start();
require_once 'includes/db_connect.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$itinerario_id = $_GET['id'] ?? null;

if (!$itinerario_id) {
    header("Location: itinerario.php");
    exit();
}

// Verificar que el itinerario pertenece al usuario o es un proveedor asignado
if ($user_type === 'turista') {
    $stmt = $conn->prepare("SELECT * FROM itinerarios WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $itinerario_id, $user_id);
} else {
    // Para proveedores, verificar que están asignados al itinerario
    $stmt = $conn->prepare("
        SELECT i.* FROM itinerarios i
        LEFT JOIN itinerario_guias ig ON i.id = ig.id_itinerario
        LEFT JOIN guias_turisticos gt ON ig.id_guia = gt.id
        LEFT JOIN itinerario_agencias ia ON i.id = ia.id_itinerario
        LEFT JOIN agencias a ON ia.id_agencia = a.id
        LEFT JOIN itinerario_locales il ON i.id = il.id_itinerario
        LEFT JOIN lugares_locales ll ON il.id_local = ll.id
        WHERE i.id = ? AND (
            (gt.id_usuario = ? AND ? = 'guia') OR
            (a.id_usuario = ? AND ? = 'agencia') OR
            (ll.id_usuario = ? AND ? = 'local')
        )
    ");
    $stmt->bind_param("iiisisi", $itinerario_id, $user_id, $user_type, $user_id, $user_type, $user_id, $user_type);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: itinerario.php");
    exit();
}

$itinerario = $result->fetch_assoc();
$stmt->close();

// Obtener todos los destinos del itinerario
$stmt = $conn->prepare("
    SELECT id.*, d.nombre, d.descripcion, d.imagen, d.latitude, d.longitude, d.ciudad
    FROM itinerario_destinos id
    JOIN destinos d ON id.id_destino = d.id
    WHERE id.id_itinerario = ?
    ORDER BY id.orden ASC
");
$stmt->bind_param("i", $itinerario_id);
$stmt->execute();
$destinos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener guías asignados
$stmt = $conn->prepare("
    SELECT ig.*, gt.nombre_guia, gt.descripcion, gt.contacto_telefono, u.email, ig.estado as estado_servicio
    FROM itinerario_guias ig
    JOIN guias_turisticos gt ON ig.id_guia = gt.id
    JOIN usuarios u ON gt.id_usuario = u.id
    WHERE ig.id_itinerario = ?
");
$stmt->bind_param("i", $itinerario_id);
$stmt->execute();
$guias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener agencias asignadas
$stmt = $conn->prepare("
    SELECT ia.*, a.nombre_agencia, a.contacto_email, a.contacto_telefono, ia.estado as estado_servicio
    FROM itinerario_agencias ia
    JOIN agencias a ON ia.id_agencia = a.id
    WHERE ia.id_itinerario = ?
");
$stmt->bind_param("i", $itinerario_id);
$stmt->execute();
$agencias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener locales asignados
$stmt = $conn->prepare("
    SELECT il.*, ll.nombre_local, ll.direccion, ll.contacto_telefono, u.email, il.estado as estado_servicio
    FROM itinerario_locales il
    JOIN lugares_locales ll ON il.id_local = ll.id
    JOIN usuarios u ON ll.id_usuario = u.id
    WHERE il.id_itinerario = ?
");
$stmt->bind_param("i", $itinerario_id);
$stmt->execute();
$locales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = "Seguimiento de Itinerario";
include 'includes/header.php';
?>

<style>
.timeline {
    position: relative;
    padding: 2rem 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 50px;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, var(--primary), var(--secondary));
}

.timeline-item {
    position: relative;
    padding-left: 120px;
    margin-bottom: 3rem;
}

.timeline-marker {
    position: absolute;
    left: 35px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: white;
    border: 4px solid var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.timeline-marker.completed {
    background: var(--success);
    border-color: var(--success);
}

.timeline-marker.in-progress {
    background: var(--warning);
    border-color: var(--warning);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
    50% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
}

.timeline-card {
    background: white;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    padding: 2rem;
    position: relative;
}

.timeline-card.completed {
    opacity: 0.8;
}

.task-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-full);
    font-weight: 600;
    font-size: 0.875rem;
}

.status-badge.pendiente { background: #fef3c7; color: #92400e; }
.status-badge.confirmado { background: #dbeafe; color: #1e40af; }
.status-badge.completado { background: #d1fae5; color: #065f46; }
.status-badge.cancelado { background: #fee2e2; color: #991b1b; }

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-label {
    font-size: 0.875rem;
    color: var(--gray-600);
    font-weight: 500;
}

.info-value {
    font-weight: 600;
    color: var(--gray-900);
}

.map-preview {
    height: 200px;
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-top: 1rem;
    background: var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 767px) {
    .timeline::before {
        left: 20px;
    }
    
    .timeline-item {
        padding-left: 60px;
    }
    
    .timeline-marker {
        left: 5px;
        width: 28px;
        height: 28px;
    }
    
    .task-actions {
        flex-direction: column;
    }
    
    .task-actions .btn {
        width: 100%;
    }
}
</style>

<section class="py-5 bg-light">
    <div class="container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <h1 class="h3 mb-2">
                                    <i class="bi bi-map me-2"></i>
                                    <?= htmlspecialchars($itinerario['nombre_itinerario']) ?>
                                </h1>
                                <?php if (!empty($itinerario['descripcion'])): ?>
                                <p class="text-muted mb-0"><?= htmlspecialchars($itinerario['descripcion']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="text-end">
                                <div class="mb-2">
                                    <strong>Presupuesto:</strong>
                                    <span class="text-primary fs-5">
                                        <?= number_format($itinerario['presupuesto_estimado'] ?? 0, 2) ?> €
                                    </span>
                                </div>
                                <?php if (!empty($itinerario['fecha_inicio']) && !empty($itinerario['fecha_fin'])): ?>
                                <div class="small text-muted">
                                    <i class="bi bi-calendar me-1"></i>
                                    <?= date('d/m/Y', strtotime($itinerario['fecha_inicio'])) ?> - 
                                    <?= date('d/m/Y', strtotime($itinerario['fecha_fin'])) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline de destinos -->
        <div class="timeline">
            <?php foreach ($destinos as $index => $destino): 
                $estado = $destino['estado'] ?? 'pendiente';
                $isCompleted = $estado === 'completado';
                $isInProgress = $estado === 'en_progreso';
            ?>
            <div class="timeline-item">
                <div class="timeline-marker <?= $isCompleted ? 'completed' : ($isInProgress ? 'in-progress' : '') ?>">
                    <?php if ($isCompleted): ?>
                        <i class="bi bi-check text-white"></i>
                    <?php else: ?>
                        <?= $index + 1 ?>
                    <?php endif; ?>
                </div>
                
                <div class="timeline-card <?= $isCompleted ? 'completed' : '' ?>">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="h5 mb-1"><?= htmlspecialchars($destino['nombre']) ?></h4>
                            <?php if (!empty($destino['ciudad'])): ?>
                            <p class="text-muted small mb-2"><?= htmlspecialchars($destino['ciudad']) ?></p>
                            <?php endif; ?>
                        </div>
                        <span class="status-badge <?= $estado ?>">
                            <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                            <?= ucfirst(str_replace('_', ' ', $estado)) ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($destino['descripcion'])): ?>
                    <p class="text-muted"><?= htmlspecialchars($destino['descripcion']) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($destino['fecha_inicio']) || !empty($destino['fecha_fin']) || !empty($destino['precio'])): ?>
                    <div class="info-grid">
                        <?php if (!empty($destino['fecha_inicio'])): ?>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="bi bi-calendar-check me-1"></i>Inicio
                            </span>
                            <span class="info-value">
                                <?= date('d/m/Y H:i', strtotime($destino['fecha_inicio'])) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($destino['fecha_fin'])): ?>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="bi bi-calendar-x me-1"></i>Fin
                            </span>
                            <span class="info-value">
                                <?= date('d/m/Y H:i', strtotime($destino['fecha_fin'])) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($destino['precio'])): ?>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="bi bi-currency-euro me-1"></i>Precio
                            </span>
                            <span class="info-value">
                                <?= number_format($destino['precio'], 2) ?> €
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Mapa preview -->
                    <?php if (!empty($destino['latitude']) && !empty($destino['longitude'])): ?>
                    <div class="map-preview" data-lat="<?= $destino['latitude'] ?>" data-lng="<?= $destino['longitude'] ?>">
                        <i class="bi bi-geo-alt-fill text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Acciones -->
                    <?php if ($user_type === 'turista' && !$isCompleted): ?>
                    <div class="task-actions">
                        <?php if (!$isInProgress && $estado === 'pendiente'): ?>
                        <button class="btn btn-warning" onclick="updateDestinationStatus(<?= $destino['id'] ?>, 'en_progreso')">
                            <i class="bi bi-play-circle me-2"></i>Iniciar
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($isInProgress): ?>
                        <button class="btn btn-success" onclick="updateDestinationStatus(<?= $destino['id'] ?>, 'completado')">
                            <i class="bi bi-check-circle me-2"></i>Marcar como Completado
                        </button>
                        <?php endif; ?>
                        
                        <a href="detalle_destino.php?id=<?= $destino['id_destino'] ?>" class="btn btn-outline-primary">
                            <i class="bi bi-info-circle me-2"></i>Ver Detalles
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Servicios Asignados -->
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="h4 mb-4">
                    <i class="bi bi-briefcase me-2"></i>
                    Servicios Asignados
                </h3>
            </div>
            
            <!-- Guías -->
            <?php if (!empty($guias)): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-person-badge me-2"></i>Guías Turísticos
                    </div>
                    <div class="card-body">
                        <?php foreach ($guias as $guia): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <h6 class="mb-1"><?= htmlspecialchars($guia['nombre_guia']) ?></h6>
                            <?php if (!empty($guia['descripcion'])): ?>
                            <p class="small text-muted mb-2"><?= htmlspecialchars($guia['descripcion']) ?></p>
                            <?php endif; ?>
                            <span class="status-badge <?= $guia['estado_servicio'] ?? 'pendiente' ?>">
                                <?= ucfirst($guia['estado_servicio'] ?? 'pendiente') ?>
                            </span>
                            <?php if (!empty($guia['contacto_telefono'])): ?>
                            <div class="mt-2">
                                <a href="tel:<?= $guia['contacto_telefono'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-telephone me-1"></i>Llamar
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Agencias -->
            <?php if (!empty($agencias)): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-building me-2"></i>Agencias de Viajes
                    </div>
                    <div class="card-body">
                        <?php foreach ($agencias as $agencia): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <h6 class="mb-1"><?= htmlspecialchars($agencia['nombre_agencia']) ?></h6>
                            <span class="status-badge <?= $agencia['estado_servicio'] ?? 'pendiente' ?>">
                                <?= ucfirst($agencia['estado_servicio'] ?? 'pendiente') ?>
                            </span>
                            <?php if (!empty($agencia['contacto_telefono'])): ?>
                            <div class="mt-2">
                                <a href="tel:<?= $agencia['contacto_telefono'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-telephone me-1"></i>Llamar
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Locales -->
            <?php if (!empty($locales)): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">
                        <i class="bi bi-shop me-2"></i>Lugares Locales
                    </div>
                    <div class="card-body">
                        <?php foreach ($locales as $local): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <h6 class="mb-1"><?= htmlspecialchars($local['nombre_local']) ?></h6>
                            <?php if (!empty($local['direccion'])): ?>
                            <p class="small text-muted mb-2"><?= htmlspecialchars($local['direccion']) ?></p>
                            <?php endif; ?>
                            <span class="status-badge <?= $local['estado_servicio'] ?? 'pendiente' ?>">
                                <?= ucfirst($local['estado_servicio'] ?? 'pendiente') ?>
                            </span>
                            <?php if (!empty($local['contacto_telefono'])): ?>
                            <div class="mt-2">
                                <a href="tel:<?= $local['contacto_telefono'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-telephone me-1"></i>Llamar
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <a href="itinerario.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver a Mis Itinerarios
                </a>
            </div>
        </div>
    </div>
</section>

<script>
function updateDestinationStatus(destinoId, estado) {
    if (!confirm('¿Estás seguro de actualizar el estado de este destino?')) {
        return;
    }
    
    fetch('api/update_destino_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id_destino: destinoId,
            estado: estado
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el estado');
    });
}
</script>

<?php include 'includes/footer.php'; ?>
