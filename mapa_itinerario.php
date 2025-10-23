<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mapa de Itinerario - GQ Turismo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .task-map-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 1rem;
        }
        
        .header-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .task-timeline {
            position: relative;
            padding: 1rem 0;
        }
        
        .timeline-line {
            position: absolute;
            left: 25px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
        }
        
        .task-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            margin-left: 60px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: relative;
            transition: all 0.3s ease;
        }
        
        .task-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .task-icon {
            position: absolute;
            left: -40px;
            top: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            z-index: 10;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .task-icon.pendiente { background: var(--warning); }
        .task-icon.en_progreso { background: var(--info); }
        .task-icon.completado { background: var(--success); }
        .task-icon.cancelado { background: var(--danger); }
        
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .task-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }
        
        .task-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            white-space: nowrap;
        }
        
        .task-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin: 0.75rem 0;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .meta-item i {
            color: var(--primary);
        }
        
        .task-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-task {
            flex: 1;
            min-width: 120px;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .progress-bar-custom {
            height: 8px;
            border-radius: 10px;
            background: #e5e7eb;
            margin: 1rem 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transition: width 0.5s ease;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.25rem;
            border-radius: 12px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 0.25rem;
        }
        
        @media (max-width: 768px) {
            .task-card {
                margin-left: 45px;
                padding: 0.875rem;
            }
            
            .task-icon {
                left: -32px;
                width: 35px;
                height: 35px;
                font-size: 16px;
            }
            
            .timeline-line {
                left: 20px;
            }
            
            .task-title {
                font-size: 1rem;
            }
            
            .task-meta {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
            
            .btn-task {
                min-width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 575px) {
            .task-map-container {
                padding: 0.5rem;
            }
            
            .header-card {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .stats-grid {
                gap: 0.75rem;
            }
            
            .stat-card {
                padding: 1rem;
            }
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
        
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 0.5rem 1rem;
            background: white;
            border: 2px solid transparent;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-tab.active {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }
    </style>
</head>
<body>
<?php
session_start();
require_once 'includes/db_connect.php';

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

// Obtener información del itinerario
$stmt = $conn->prepare("SELECT * FROM itinerarios WHERE id = ?");
$stmt->bind_param("i", $itinerario_id);
$stmt->execute();
$itinerario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$itinerario) {
    header("Location: itinerario.php");
    exit();
}

// Obtener tareas del itinerario
$stmt = $conn->prepare("
    SELECT t.*, 
           d.nombre as destino_nombre,
           u.nombre as proveedor_nombre
    FROM itinerario_tareas t
    LEFT JOIN destinos d ON t.id_destino = d.id
    LEFT JOIN usuarios u ON t.id_proveedor = u.id
    WHERE t.id_itinerario = ?
    ORDER BY t.fecha_hora_inicio ASC, t.id ASC
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

// Función para formatear fechas
function formatDate($date) {
    if (!$date) return 'No asignada';
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="task-map-container">
    <!-- Header Card -->
    <div class="header-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1">
                    <i class="bi bi-map me-2 text-primary"></i>
                    <?= htmlspecialchars($itinerario['nombre_itinerario']) ?>
                </h1>
                <p class="text-muted mb-0">Mapa de tareas y seguimiento</p>
            </div>
            <a href="itinerario.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-value"><?= $total_tareas ?></p>
                <p class="stat-label mb-0">Total Tareas</p>
            </div>
            <div class="stat-card">
                <p class="stat-value"><?= $completadas ?></p>
                <p class="stat-label mb-0">Completadas</p>
            </div>
            <div class="stat-card">
                <p class="stat-value"><?= $en_progreso ?></p>
                <p class="stat-label mb-0">En Progreso</p>
            </div>
            <div class="stat-card">
                <p class="stat-value"><?= $pendientes ?></p>
                <p class="stat-label mb-0">Pendientes</p>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="progress-bar-custom">
            <div class="progress-fill" style="width: <?= $progreso ?>%"></div>
        </div>
        <p class="text-center text-muted mb-0">
            <strong><?= $progreso ?>%</strong> completado
        </p>
    </div>
    
    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <button class="filter-tab active" data-filter="all">
            <i class="bi bi-list-task me-1"></i> Todas
        </button>
        <button class="filter-tab" data-filter="pendiente">
            <i class="bi bi-clock me-1"></i> Pendientes
        </button>
        <button class="filter-tab" data-filter="en_progreso">
            <i class="bi bi-play-circle me-1"></i> En Progreso
        </button>
        <button class="filter-tab" data-filter="completado">
            <i class="bi bi-check-circle me-1"></i> Completadas
        </button>
    </div>
    
    <!-- Tasks Timeline -->
    <div class="task-timeline">
        <div class="timeline-line"></div>
        
        <?php if (empty($tareas)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h3>No hay tareas aún</h3>
                <p>Las tareas aparecerán aquí cuando los proveedores confirmen los servicios</p>
            </div>
        <?php else: ?>
            <?php foreach ($tareas as $tarea): ?>
                <div class="task-card" data-estado="<?= $tarea['estado'] ?>">
                    <div class="task-icon <?= $tarea['estado'] ?>">
                        <i class="bi <?= getTaskIcon($tarea['tipo_tarea']) ?>"></i>
                    </div>
                    
                    <div class="task-header">
                        <h3 class="task-title"><?= htmlspecialchars($tarea['titulo']) ?></h3>
                        <span class="task-badge bg-<?= $tarea['estado'] === 'completado' ? 'success' : ($tarea['estado'] === 'en_progreso' ? 'info' : 'warning') ?>">
                            <?= ucfirst(str_replace('_', ' ', $tarea['estado'])) ?>
                        </span>
                    </div>
                    
                    <?php if ($tarea['descripcion']): ?>
                        <p class="text-muted mb-2"><?= htmlspecialchars($tarea['descripcion']) ?></p>
                    <?php endif; ?>
                    
                    <div class="task-meta">
                        <?php if ($tarea['fecha_hora_inicio']): ?>
                            <div class="meta-item">
                                <i class="bi bi-calendar-event"></i>
                                <span><?= formatDate($tarea['fecha_hora_inicio']) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($tarea['destino_nombre']): ?>
                            <div class="meta-item">
                                <i class="bi bi-geo-alt"></i>
                                <span><?= htmlspecialchars($tarea['destino_nombre']) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($tarea['proveedor_nombre']): ?>
                            <div class="meta-item">
                                <i class="bi bi-person"></i>
                                <span><?= htmlspecialchars($tarea['proveedor_nombre']) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="meta-item">
                            <i class="bi bi-tag"></i>
                            <span><?= ucfirst($tarea['tipo_tarea']) ?></span>
                        </div>
                    </div>
                    
                    <?php if ($tarea['notas']): ?>
                        <div class="alert alert-info py-2 px-3 mb-2">
                            <small><i class="bi bi-info-circle me-1"></i> <?= htmlspecialchars($tarea['notas']) ?></small>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Actions (solo para turistas y proveedores asignados) -->
                    <?php if ($user_type === 'turista' || ($tarea['id_proveedor'] == $user_id)): ?>
                        <div class="task-actions">
                            <?php if ($tarea['estado'] === 'pendiente'): ?>
                                <button class="btn-task btn btn-primary" onclick="updateTaskStatus(<?= $tarea['id'] ?>, 'en_progreso')">
                                    <i class="bi bi-play-circle me-1"></i> Iniciar
                                </button>
                            <?php elseif ($tarea['estado'] === 'en_progreso'): ?>
                                <button class="btn-task btn btn-success" onclick="updateTaskStatus(<?= $tarea['id'] ?>, 'completado')">
                                    <i class="bi bi-check-circle me-1"></i> Completar
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($tarea['estado'] !== 'completado' && $tarea['estado'] !== 'cancelado'): ?>
                                <button class="btn-task btn btn-outline-secondary" onclick="showNotesModal(<?= $tarea['id'] ?>)">
                                    <i class="bi bi-pencil me-1"></i> Notas
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para notas -->
<div class="modal fade" id="notesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Notas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea class="form-control" id="taskNotes" rows="4" placeholder="Escribe tus notas aquí..."></textarea>
                <input type="hidden" id="taskIdForNotes">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveNotes()">Guardar Notas</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Filter functionality
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        document.querySelectorAll('.task-card').forEach(card => {
            if (filter === 'all' || card.dataset.estado === filter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// Update task status
function updateTaskStatus(taskId, newStatus) {
    if (!confirm('¿Estás seguro de cambiar el estado de esta tarea?')) return;
    
    fetch('api/update_task_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: taskId, status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al actualizar: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar la tarea');
    });
}

// Show notes modal
function showNotesModal(taskId) {
    document.getElementById('taskIdForNotes').value = taskId;
    new bootstrap.Modal(document.getElementById('notesModal')).show();
}

// Save notes
function saveNotes() {
    const taskId = document.getElementById('taskIdForNotes').value;
    const notes = document.getElementById('taskNotes').value;
    
    fetch('api/update_task_notes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: taskId, notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al guardar: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar las notas');
    });
}
</script>
</body>
</html>
