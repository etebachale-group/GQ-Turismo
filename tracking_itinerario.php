<?php
session_start();
require_once 'includes/db_connect.php';

// Verificar autenticación
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

// AJAX - Actualizar estado de tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'update_task_status') {
        $task_id = $_POST['task_id'] ?? 0;
        $new_status = $_POST['status'] ?? 'pendiente';
        
        // Validar estados permitidos
        $allowed_statuses = ['pendiente', 'en_progreso', 'completado', 'cancelado'];
        if (!in_array($new_status, $allowed_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Estado no válido']);
            exit();
        }
        
        // Verificar permisos
        $canUpdate = false;
        if ($user_type === 'turista') {
            // Turistas pueden actualizar tareas de su itinerario
            $stmt = $conn->prepare("SELECT id FROM itinerario_tareas WHERE id = ? AND id_itinerario IN (SELECT id FROM itinerarios WHERE id_usuario = ?)");
            $stmt->bind_param("ii", $task_id, $user_id);
            $stmt->execute();
            $canUpdate = $stmt->get_result()->num_rows > 0;
            $stmt->close();
        } elseif (in_array($user_type, ['guia', 'agencia', 'local'])) {
            // Proveedores pueden actualizar tareas donde están asignados
            $stmt = $conn->prepare("SELECT id FROM itinerario_tareas WHERE id = ? AND id_proveedor = ?");
            $stmt->bind_param("ii", $task_id, $user_id);
            $stmt->execute();
            $canUpdate = $stmt->get_result()->num_rows > 0;
            $stmt->close();
        }
        
        if ($canUpdate) {
            $stmt = $conn->prepare("UPDATE itinerario_tareas SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?");
            $stmt->bind_param("si", $new_status, $task_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Estado actualizado']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
        }
        exit();
    }
}

// Obtener información del itinerario
$stmt = $conn->prepare("SELECT i.*, u.nombre as turista_nombre FROM itinerarios i LEFT JOIN usuarios u ON i.id_usuario = u.id WHERE i.id = ?");
$stmt->bind_param("i", $itinerario_id);
$stmt->execute();
$itinerario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$itinerario) {
    header("Location: itinerario.php");
    exit();
}

// Verificar permisos de acceso
$hasAccess = false;
if ($user_type === 'turista' && $itinerario['id_usuario'] == $user_id) {
    $hasAccess = true;
} elseif ($user_type === 'super_admin') {
    $hasAccess = true;
} elseif (in_array($user_type, ['guia', 'agencia', 'local'])) {
    // Verificar si el proveedor está asignado al itinerario
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM itinerario_tareas WHERE id_itinerario = ? AND id_proveedor = ?");
    $stmt->bind_param("ii", $itinerario_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $hasAccess = $result['count'] > 0;
    $stmt->close();
}

if (!$hasAccess) {
    header("Location: itinerario.php");
    exit();
}

// Obtener todas las tareas del itinerario
$stmt = $conn->prepare("
    SELECT t.*, 
           d.nombre as destino_nombre,
           d.ciudad as destino_ciudad,
           d.latitude,
           d.longitude,
           u.nombre as proveedor_nombre,
           u.email as proveedor_email
    FROM itinerario_tareas t
    LEFT JOIN destinos d ON t.id_destino = d.id
    LEFT JOIN usuarios u ON t.id_proveedor = u.id
    WHERE t.id_itinerario = ?
    ORDER BY t.fecha_hora_inicio ASC, t.orden ASC, t.id ASC
");
$stmt->bind_param("i", $itinerario_id);
$stmt->execute();
$tareas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcular estadísticas
$total_tareas = count($tareas);
$completadas = count(array_filter($tareas, fn($t) => $t['estado'] === 'completado'));
$en_progreso = count(array_filter($tareas, fn($t) => $t['estado'] === 'en_progreso'));
$pendientes = count(array_filter($tareas, fn($t) => $t['estado'] === 'pendiente'));
$canceladas = count(array_filter($tareas, fn($t) => $t['estado'] === 'cancelado'));
$progreso = $total_tareas > 0 ? round(($completadas / $total_tareas) * 100) : 0;

// Iconos por tipo de tarea
function getTaskIcon($tipo) {
    $icons = [
        'transporte' => 'bi-bus-front',
        'alojamiento' => 'bi-building',
        'actividad' => 'bi-star',
        'comida' => 'bi-cup-hot',
        'guia' => 'bi-person-badge',
        'otro' => 'bi-bookmark'
    ];
    return $icons[$tipo] ?? 'bi-circle';
}

// Colores por estado
function getStatusColor($estado) {
    $colors = [
        'pendiente' => 'warning',
        'en_progreso' => 'info',
        'completado' => 'success',
        'cancelado' => 'danger'
    ];
    return $colors[$estado] ?? 'secondary';
}

// Formatear fechas
function formatDate($date) {
    if (!$date || $date === '0000-00-00 00:00:00') return 'No asignada';
    return date('d/m/Y H:i', strtotime($date));
}

include 'includes/header.php';
?>

<style>
    body {
        background: #f8f9fa;
        padding-top: 80px;
    }
    
    .tracking-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }
    
    .tracking-header {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }
    
    .progress-bar-custom {
        height: 30px;
        border-radius: 15px;
        background: #e9ecef;
        overflow: hidden;
        position: relative;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        transition: width 0.5s ease;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        text-align: center;
        border-left: 4px solid;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    
    .stat-card.total { border-left-color: #6c757d; }
    .stat-card.completadas { border-left-color: #28a745; }
    .stat-card.en-progreso { border-left-color: #17a2b8; }
    .stat-card.pendientes { border-left-color: #ffc107; }
    .stat-card.canceladas { border-left-color: #dc3545; }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .timeline {
        position: relative;
        padding: 0;
        list-style: none;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 30px;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    }
    
    .timeline-item {
        position: relative;
        padding-left: 80px;
        padding-bottom: 3rem;
    }
    
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    
    .timeline-marker {
        position: absolute;
        left: 16px;
        top: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: white;
        border: 3px solid #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        font-size: 14px;
    }
    
    .timeline-marker.completed {
        background: #28a745;
        border-color: #28a745;
        color: white;
    }
    
    .timeline-marker.in-progress {
        background: #17a2b8;
        border-color: #17a2b8;
        color: white;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(23, 162, 184, 0.7); }
        50% { box-shadow: 0 0 0 10px rgba(23, 162, 184, 0); }
    }
    
    .task-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    
    .task-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    
    .task-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
        gap: 1rem;
    }
    
    .task-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
        color: #1f2937;
    }
    
    .task-status {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .task-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .meta-item i {
        font-size: 1.1rem;
    }
    
    .task-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
    }
    
    .btn-task {
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }
    
    .btn-task:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .btn-pendiente { background: #ffc107; color: #000; }
    .btn-en-progreso { background: #17a2b8; color: white; }
    .btn-completado { background: #28a745; color: white; }
    .btn-cancelado { background: #dc3545; color: white; }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 5rem;
        opacity: 0.3;
        margin-bottom: 1rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .tracking-container {
            padding: 1rem 0.5rem;
        }
        
        .tracking-header {
            padding: 1.5rem 1rem;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }
        
        .stat-number {
            font-size: 1.5rem;
        }
        
        .stat-label {
            font-size: 0.75rem;
        }
        
        .timeline::before {
            left: 15px;
        }
        
        .timeline-item {
            padding-left: 50px;
            padding-bottom: 2rem;
        }
        
        .timeline-marker {
            left: 7px;
            width: 20px;
            height: 20px;
            font-size: 10px;
        }
        
        .task-card {
            padding: 1rem;
        }
        
        .task-title {
            font-size: 1.1rem;
        }
        
        .task-header {
            flex-direction: column;
        }
        
        .task-meta {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        
        .btn-task {
            flex: 1;
            min-width: 120px;
        }
    }
</style>

<div class="tracking-container">
    <!-- Header -->
    <div class="tracking-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <h1 class="h3 mb-2">
                    <i class="bi bi-map me-2"></i>
                    <?= htmlspecialchars($itinerario['nombre_itinerario'] ?? 'Itinerario') ?>
                </h1>
                <?php if (isset($itinerario['descripcion']) && !empty($itinerario['descripcion'])): ?>
                    <p class="text-muted mb-0"><?= htmlspecialchars($itinerario['descripcion']) ?></p>
                <?php endif; ?>
                <?php if (isset($itinerario['fecha_inicio']) && isset($itinerario['fecha_fin'])): ?>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-calendar me-1"></i>
                        <?= date('d/m/Y', strtotime($itinerario['fecha_inicio'])) ?> - <?= date('d/m/Y', strtotime($itinerario['fecha_fin'])) ?>
                    </p>
                <?php endif; ?>
            </div>
            <a href="itinerario.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Volver
            </a>
        </div>
        
        <!-- Barra de Progreso -->
        <div class="progress-bar-custom">
            <div class="progress-fill" style="width: <?= $progreso ?>%">
                <?= $progreso ?>% Completado
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?= $total_tareas ?></div>
                <div class="stat-label">Total Tareas</div>
            </div>
            <div class="stat-card completadas">
                <div class="stat-number"><?= $completadas ?></div>
                <div class="stat-label">Completadas</div>
            </div>
            <div class="stat-card en-progreso">
                <div class="stat-number"><?= $en_progreso ?></div>
                <div class="stat-label">En Progreso</div>
            </div>
            <div class="stat-card pendientes">
                <div class="stat-number"><?= $pendientes ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
            <?php if ($canceladas > 0): ?>
            <div class="stat-card canceladas">
                <div class="stat-number"><?= $canceladas ?></div>
                <div class="stat-label">Canceladas</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Timeline de Tareas -->
    <?php if (empty($tareas)): ?>
        <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <h4>No hay tareas en este itinerario</h4>
            <p>Las tareas aparecerán aquí cuando se agreguen al itinerario</p>
        </div>
    <?php else: ?>
        <ul class="timeline">
            <?php foreach ($tareas as $index => $tarea): ?>
                <li class="timeline-item">
                    <div class="timeline-marker <?= $tarea['estado'] === 'completado' ? 'completed' : ($tarea['estado'] === 'en_progreso' ? 'in-progress' : '') ?>">
                        <?php if ($tarea['estado'] === 'completado'): ?>
                            <i class="bi bi-check-lg"></i>
                        <?php else: ?>
                            <i class="bi <?= getTaskIcon($tarea['tipo'] ?? 'otro') ?>"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="task-card">
                        <div class="task-header">
                            <div>
                                <h3 class="task-title"><?= htmlspecialchars($tarea['titulo']) ?></h3>
                                <?php if (!empty($tarea['destino_nombre'])): ?>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <?= htmlspecialchars($tarea['destino_nombre']) ?>
                                        <?php if (!empty($tarea['destino_ciudad'])): ?>
                                            - <?= htmlspecialchars($tarea['destino_ciudad']) ?>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <span class="task-status badge bg-<?= getStatusColor($tarea['estado']) ?>">
                                <?= ucfirst(str_replace('_', ' ', $tarea['estado'])) ?>
                            </span>
                        </div>
                        
                        <?php if (!empty($tarea['descripcion'])): ?>
                            <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($tarea['descripcion'])) ?></p>
                        <?php endif; ?>
                        
                        <div class="task-meta">
                            <div class="meta-item">
                                <i class="bi bi-tag-fill text-primary"></i>
                                <span><?= ucfirst($tarea['tipo'] ?? 'Otro') ?></span>
                            </div>
                            <?php if ($tarea['fecha_hora_inicio']): ?>
                                <div class="meta-item">
                                    <i class="bi bi-calendar-event text-success"></i>
                                    <span>Inicio: <?= formatDate($tarea['fecha_hora_inicio']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($tarea['fecha_hora_fin']): ?>
                                <div class="meta-item">
                                    <i class="bi bi-calendar-check text-danger"></i>
                                    <span>Fin: <?= formatDate($tarea['fecha_hora_fin']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($tarea['proveedor_nombre'])): ?>
                                <div class="meta-item">
                                    <i class="bi bi-person-circle text-info"></i>
                                    <span><?= htmlspecialchars($tarea['proveedor_nombre']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($tarea['precio']) && $tarea['precio'] > 0): ?>
                                <div class="meta-item">
                                    <i class="bi bi-cash text-warning"></i>
                                    <span><?= number_format($tarea['precio'], 2) ?> €</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Acciones -->
                        <div class="task-actions">
                            <?php if ($tarea['estado'] !== 'pendiente'): ?>
                                <button class="btn-task btn-pendiente" onclick="updateTaskStatus(<?= $tarea['id'] ?>, 'pendiente')">
                                    <i class="bi bi-clock me-1"></i> Marcar como Pendiente
                                </button>
                            <?php endif; ?>
                            <?php if ($tarea['estado'] !== 'en_progreso'): ?>
                                <button class="btn-task btn-en-progreso" onclick="updateTaskStatus(<?= $tarea['id'] ?>, 'en_progreso')">
                                    <i class="bi bi-play-circle me-1"></i> Iniciar
                                </button>
                            <?php endif; ?>
                            <?php if ($tarea['estado'] !== 'completado'): ?>
                                <button class="btn-task btn-completado" onclick="updateTaskStatus(<?= $tarea['id'] ?>, 'completado')">
                                    <i class="bi bi-check-circle me-1"></i> Completar
                                </button>
                            <?php endif; ?>
                            <?php if ($tarea['estado'] !== 'cancelado'): ?>
                                <button class="btn-task btn-cancelado" onclick="updateTaskStatus(<?= $tarea['id'] ?>, 'cancelado')">
                                    <i class="bi bi-x-circle me-1"></i> Cancelar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<script>
function updateTaskStatus(taskId, status) {
    if (!confirm('¿Desea cambiar el estado de esta tarea?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'update_task_status');
    formData.append('task_id', taskId);
    formData.append('status', status);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
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

// Auto-refresh cada 30 segundos si hay tareas en progreso
<?php if ($en_progreso > 0): ?>
setTimeout(() => {
    location.reload();
}, 30000);
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
