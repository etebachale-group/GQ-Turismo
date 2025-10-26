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

// Verificar acceso al itinerario
$stmt = $conn->prepare("SELECT * FROM itinerarios WHERE id = ? AND id_usuario = ?");
$stmt->bind_param("ii", $itinerario_id, $user_id);
$stmt->execute();
$itinerario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$itinerario && $user_type !== 'guia') {
    header("Location: itinerario.php");
    exit();
}

// Si es guía, verificar asignación
if ($user_type === 'guia') {
    $stmt = $conn->prepare("
        SELECT i.* FROM itinerarios i
        INNER JOIN itinerario_guias ig ON i.id = ig.id_itinerario
        INNER JOIN guias_turisticos gt ON ig.id_guia = gt.id
        WHERE i.id = ? AND gt.id_usuario = ?
    ");
    $stmt->bind_param("ii", $itinerario_id, $user_id);
    $stmt->execute();
    $itinerario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$itinerario) {
        header("Location: index.php");
        exit();
    }
}

// Obtener tareas del itinerario
$stmt = $conn->prepare("
    SELECT t.*, 
           d.nombre as destino_nombre,
           d.latitude,
           d.longitude,
           u.nombre as completado_por_nombre
    FROM itinerario_tareas t
    LEFT JOIN destinos d ON t.id_destino = d.id
    LEFT JOIN usuarios u ON t.completado_por = u.id
    WHERE t.id_itinerario = ?
    ORDER BY t.orden ASC, t.fecha_inicio ASC
");
$stmt->bind_param("i", $itinerario_id);
$stmt->execute();
$tareas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcular progreso
$total_tareas = count($tareas);
$tareas_completadas = count(array_filter($tareas, fn($t) => $t['estado'] === 'completado'));
$progreso = $total_tareas > 0 ? round(($tareas_completadas / $total_tareas) * 100) : 0;

// Actualizar progreso en itinerario
$stmt = $conn->prepare("UPDATE itinerarios SET progreso_porcentaje = ? WHERE id = ?");
$stmt->bind_param("ii", $progreso, $itinerario_id);
$stmt->execute();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Tareas - <?= htmlspecialchars($itinerario['nombre_itinerario']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --primary-color: #1a73e8;
            --success-color: #34a853;
            --warning-color: #fbbc04;
            --danger-color: #ea4335;
            --info-color: #4285f4;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary-color), var(--info-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .progress-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .task-timeline {
            position: relative;
            padding-left: 3rem;
        }
        
        .task-timeline::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(to bottom, var(--primary-color), var(--success-color));
        }
        
        .task-item {
            position: relative;
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .task-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .task-item::before {
            content: '';
            position: absolute;
            left: -2rem;
            top: 1.5rem;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px currentColor;
        }
        
        .task-item.pendiente::before {
            background: var(--warning-color);
            color: var(--warning-color);
        }
        
        .task-item.en_progreso::before {
            background: var(--info-color);
            color: var(--info-color);
        }
        
        .task-item.completado::before {
            background: var(--success-color);
            color: var(--success-color);
        }
        
        .task-item.cancelado::before {
            background: var(--danger-color);
            color: var(--danger-color);
        }
        
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        
        .task-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .task-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .badge-pendiente {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-en_progreso {
            background-color: #cfe2ff;
            color: #084298;
        }
        
        .badge-completado {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .badge-cancelado {
            background-color: #f8d7da;
            color: #842029;
        }
        
        .task-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-item i {
            color: var(--primary-color);
        }
        
        .task-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        #map {
            height: 500px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .task-timeline {
                padding-left: 2rem;
            }
            
            .task-timeline::before {
                left: 0.5rem;
            }
            
            .task-item::before {
                left: -1.5rem;
                width: 20px;
                height: 20px;
            }
            
            .task-header {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .task-info {
                grid-template-columns: 1fr;
            }
            
            #map {
                height: 350px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-2">
                        <i class="bi bi-map-fill me-2"></i>
                        <?= htmlspecialchars($itinerario['nombre_itinerario']) ?>
                    </h1>
                    <p class="mb-0 opacity-75">Mapa de Tareas del Itinerario</p>
                </div>
                <a href="seguimiento_itinerario.php?id=<?= $itinerario_id ?>" class="btn btn-light">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Progreso General -->
        <div class="progress-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-3">Progreso del Itinerario</h3>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?= $progreso ?>%"
                             aria-valuenow="<?= $progreso ?>" aria-valuemin="0" aria-valuemax="100">
                            <?= $progreso ?>%
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <h4 class="mb-0"><?= $tareas_completadas ?> / <?= $total_tareas ?></h4>
                    <small class="text-muted">Tareas Completadas</small>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Mapa -->
            <div class="col-lg-5 mb-4">
                <div id="map"></div>
            </div>
            
            <!-- Timeline de Tareas -->
            <div class="col-lg-7">
                <div class="task-timeline">
                    <?php if (empty($tareas)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No hay tareas asignadas a este itinerario todavía.
                        </div>
                    <?php else: ?>
                        <?php foreach ($tareas as $tarea): ?>
                            <div class="task-item <?= $tarea['estado'] ?>" data-task-id="<?= $tarea['id'] ?>">
                                <div class="task-header">
                                    <div>
                                        <div class="task-title"><?= htmlspecialchars($tarea['titulo']) ?></div>
                                        <?php if ($tarea['destino_nombre']): ?>
                                            <div class="text-muted">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                <?= htmlspecialchars($tarea['destino_nombre']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="task-badge badge-<?= $tarea['estado'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $tarea['estado'])) ?>
                                    </span>
                                </div>
                                
                                <?php if ($tarea['descripcion']): ?>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($tarea['descripcion']) ?></p>
                                <?php endif; ?>
                                
                                <div class="task-info">
                                    <?php if ($tarea['fecha_inicio']): ?>
                                        <div class="info-item">
                                            <i class="bi bi-calendar-check"></i>
                                            <small><?= date('d/m/Y H:i', strtotime($tarea['fecha_inicio'])) ?></small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($tarea['fecha_fin']): ?>
                                        <div class="info-item">
                                            <i class="bi bi-calendar-x"></i>
                                            <small><?= date('d/m/Y H:i', strtotime($tarea['fecha_fin'])) ?></small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="info-item">
                                        <i class="bi bi-tag"></i>
                                        <small><?= ucfirst($tarea['tipo_tarea']) ?></small>
                                    </div>
                                </div>
                                
                                <?php if ($tarea['direccion']): ?>
                                    <div class="mt-2">
                                        <i class="bi bi-pin-map me-1"></i>
                                        <small class="text-muted"><?= htmlspecialchars($tarea['direccion']) ?></small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($user_type === 'turista' || $user_type === 'guia'): ?>
                                    <div class="task-actions">
                                        <?php if ($tarea['estado'] === 'pendiente'): ?>
                                            <button class="btn btn-sm btn-primary" onclick="cambiarEstadoTarea(<?= $tarea['id'] ?>, 'en_progreso')">
                                                <i class="bi bi-play-fill me-1"></i>Iniciar
                                            </button>
                                        <?php elseif ($tarea['estado'] === 'en_progreso'): ?>
                                            <button class="btn btn-sm btn-success" onclick="cambiarEstadoTarea(<?= $tarea['id'] ?>, 'completado')">
                                                <i class="bi bi-check-circle me-1"></i>Completar
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($tarea['estado'] !== 'completado' && $tarea['estado'] !== 'cancelado'): ?>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="verDetallesTarea(<?= $tarea['id'] ?>)">
                                                <i class="bi bi-eye me-1"></i>Detalles
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($tarea['completado_por_nombre'] && $tarea['fecha_completado']): ?>
                                    <div class="mt-2 small text-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Completado por <?= htmlspecialchars($tarea['completado_por_nombre']) ?> 
                                        el <?= date('d/m/Y H:i', strtotime($tarea['fecha_completado'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Inicializar mapa
        const map = L.map('map').setView([<?= $tareas[0]['latitude'] ?? '0' ?>, <?= $tareas[0]['longitude'] ?? '0' ?>], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Agregar marcadores de tareas
        const tareas = <?= json_encode($tareas) ?>;
        const markers = [];
        
        tareas.forEach((tarea, index) => {
            if (tarea.latitude && tarea.longitude) {
                let icon;
                switch(tarea.estado) {
                    case 'completado':
                        icon = L.divIcon({className: 'custom-marker', html: '<div style="background:green;color:white;border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;font-weight:bold;">' + (index + 1) + '</div>'});
                        break;
                    case 'en_progreso':
                        icon = L.divIcon({className: 'custom-marker', html: '<div style="background:blue;color:white;border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;font-weight:bold;">' + (index + 1) + '</div>'});
                        break;
                    default:
                        icon = L.divIcon({className: 'custom-marker', html: '<div style="background:orange;color:white;border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;font-weight:bold;">' + (index + 1) + '</div>'});
                }
                
                const marker = L.marker([tarea.latitude, tarea.longitude], {icon: icon})
                    .addTo(map)
                    .bindPopup(`<b>${tarea.titulo}</b><br>${tarea.destino_nombre || ''}`);
                markers.push(marker);
            }
        });
        
        // Ajustar vista para mostrar todos los marcadores
        if (markers.length > 0) {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }
        
        // Funciones para cambiar estado de tareas
        function cambiarEstadoTarea(tareaId, nuevoEstado) {
            if (!confirm('¿Confirmar cambio de estado?')) return;
            
            fetch('api/actualizar_estado_tarea.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    tarea_id: tareaId,
                    estado: nuevoEstado
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
                alert('Error al actualizar estado');
            });
        }
        
        function verDetallesTarea(tareaId) {
            // Implementar modal con detalles
            alert('Función de detalles en desarrollo');
        }
    </script>
</body>
</html>
