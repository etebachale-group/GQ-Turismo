<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Solo proveedores pueden acceder
if (!in_array($user_type, ['guia', 'agencia', 'local'])) {
    header("Location: ../index.php");
    exit();
}

// Obtener pedidos pendientes de confirmación
$sql = "SELECT cs.*, 
               ps.id_turista, ps.tipo_item, ps.precio, ps.fecha_solicitud,
               ps.fecha_servicio, ps.numero_personas, ps.notas,
               u.nombre as turista_nombre, u.email as turista_email,
               i.nombre_itinerario, i.id as id_itinerario,
               CASE
                   WHEN ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'agencia' THEN sa.nombre_servicio
                   WHEN ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'agencia' THEN ma.nombre_menu
                   WHEN ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'guia' THEN sg.nombre_servicio
                   WHEN ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'local' THEN sl.nombre_servicio
                   WHEN ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'local' THEN ml.nombre_menu
                   WHEN ps.tipo_item = 'guia_destino' THEN CONCAT('Guía para ', d.nombre)
               END AS servicio_nombre
        FROM confirmacion_servicios cs
        JOIN pedidos_servicios ps ON cs.id_pedido_servicio = ps.id
        JOIN usuarios u ON ps.id_turista = u.id
        LEFT JOIN itinerarios i ON ps.id_itinerario = i.id
        LEFT JOIN servicios_agencia sa ON ps.id_servicio_o_menu = sa.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'agencia'
        LEFT JOIN menus_agencia ma ON ps.id_servicio_o_menu = ma.id AND ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'agencia'
        LEFT JOIN servicios_guia sg ON ps.id_servicio_o_menu = sg.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'guia'
        LEFT JOIN servicios_local sl ON ps.id_servicio_o_menu = sl.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'local'
        LEFT JOIN menus_local ml ON ps.id_servicio_o_menu = ml.id AND ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'local'
        LEFT JOIN destinos d ON ps.id_destino = d.id
        WHERE cs.id_proveedor = ? AND cs.tipo_proveedor = ?
        ORDER BY cs.fecha_creacion DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $user_type);
$stmt->execute();
$confirmaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Manejar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $confirmacion_id = intval($_POST['confirmacion_id'] ?? 0);
    
    if ($action === 'confirmar' || $action === 'rechazar' || $action === 'completar') {
        $nuevo_estado = '';
        switch ($action) {
            case 'confirmar':
                $nuevo_estado = 'confirmado';
                $fecha_field = 'fecha_confirmacion';
                break;
            case 'rechazar':
                $nuevo_estado = 'rechazado';
                $fecha_field = 'fecha_confirmacion';
                break;
            case 'completar':
                $nuevo_estado = 'completado';
                $fecha_field = 'fecha_completado';
                break;
        }
        
        $notas = trim($_POST['notas'] ?? '');
        
        $stmt = $conn->prepare("UPDATE confirmacion_servicios 
                               SET estado = ?, $fecha_field = NOW(), notas = CONCAT(COALESCE(notas, ''), ?)
                               WHERE id = ? AND id_proveedor = ?");
        $note_text = $notas ? "\n[" . date('Y-m-d H:i:s') . "]: $notas" : '';
        $stmt->bind_param("ssii", $nuevo_estado, $note_text, $confirmacion_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = ucfirst($action) . ' realizado correctamente';
            
            // Si se confirma, también actualizar el pedido_servicio
            if ($action === 'confirmar') {
                $stmt2 = $conn->prepare("UPDATE pedidos_servicios ps
                                        JOIN confirmacion_servicios cs ON ps.id = cs.id_pedido_servicio
                                        SET ps.estado = 'confirmado'
                                        WHERE cs.id = ?");
                $stmt2->bind_param("i", $confirmacion_id);
                $stmt2->execute();
                $stmt2->close();
            }
        } else {
            $_SESSION['error_message'] = 'Error al procesar la acción';
        }
        $stmt->close();
        
        header("Location: confirmacion_servicios.php");
        exit();
    }
}

include 'includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">
            <i class="bi bi-clipboard-check me-2"></i>
            Confirmación de Servicios
        </h1>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Tabs de estado -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pendientes">
                Pendientes <span class="badge bg-warning ms-1"><?= count(array_filter($confirmaciones, fn($c) => $c['estado'] === 'pendiente')) ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#confirmados">
                Confirmados <span class="badge bg-success ms-1"><?= count(array_filter($confirmaciones, fn($c) => $c['estado'] === 'confirmado')) ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#completados">
                Completados <span class="badge bg-info ms-1"><?= count(array_filter($confirmaciones, fn($c) => $c['estado'] === 'completado')) ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rechazados">
                Rechazados <span class="badge bg-danger ms-1"><?= count(array_filter($confirmaciones, fn($c) => $c['estado'] === 'rechazado')) ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <?php foreach (['pendiente' => 'pendientes', 'confirmado' => 'confirmados', 'completado' => 'completados', 'rechazado' => 'rechazados'] as $estado => $tab_id): ?>
        <div class="tab-pane fade <?= $estado === 'pendiente' ? 'show active' : '' ?>" id="<?= $tab_id ?>">
            <div class="row g-3">
                <?php 
                $servicios_filtrados = array_filter($confirmaciones, fn($c) => $c['estado'] === $estado);
                if (empty($servicios_filtrados)): 
                ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No hay servicios <?= $estado === 'pendiente' ? 'pendientes' : $estado . 's' ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($servicios_filtrados as $servicio): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-<?= $estado === 'pendiente' ? 'warning' : ($estado === 'confirmado' ? 'success' : ($estado === 'completado' ? 'info' : 'danger')) ?> text-white">
                                <h5 class="mb-0"><?= htmlspecialchars($servicio['servicio_nombre']) ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong><i class="bi bi-person me-1"></i>Cliente:</strong>
                                    <p class="mb-1"><?= htmlspecialchars($servicio['turista_nombre']) ?></p>
                                    <small class="text-muted"><?= htmlspecialchars($servicio['turista_email']) ?></small>
                                </div>

                                <?php if ($servicio['nombre_itinerario']): ?>
                                <div class="mb-3">
                                    <strong><i class="bi bi-map me-1"></i>Itinerario:</strong>
                                    <p class="mb-0"><?= htmlspecialchars($servicio['nombre_itinerario']) ?></p>
                                </div>
                                <?php endif; ?>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Precio</small>
                                        <strong><?= number_format($servicio['precio'], 2) ?> €</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Personas</small>
                                        <strong><?= $servicio['numero_personas'] ?></strong>
                                    </div>
                                </div>

                                <?php if ($servicio['fecha_servicio']): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Fecha servicio</small>
                                    <strong><i class="bi bi-calendar me-1"></i><?= date('d/m/Y H:i', strtotime($servicio['fecha_servicio'])) ?></strong>
                                </div>
                                <?php endif; ?>

                                <?php if ($servicio['notas']): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Notas:</small>
                                    <div class="border rounded p-2 bg-light">
                                        <small><?= nl2br(htmlspecialchars($servicio['notas'])) ?></small>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="mb-0">
                                    <small class="text-muted">Solicitado: <?= date('d/m/Y H:i', strtotime($servicio['fecha_solicitud'])) ?></small>
                                </div>
                            </div>
                            
                            <?php if ($estado === 'pendiente'): ?>
                            <div class="card-footer bg-white border-top-0">
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="confirmacion_id" value="<?= $servicio['id'] ?>">
                                    <button type="button" class="btn btn-success flex-fill" onclick="confirmarServicio(<?= $servicio['id'] ?>)">
                                        <i class="bi bi-check-circle me-1"></i>Confirmar
                                    </button>
                                    <button type="button" class="btn btn-danger flex-fill" onclick="rechazarServicio(<?= $servicio['id'] ?>)">
                                        <i class="bi bi-x-circle me-1"></i>Rechazar
                                    </button>
                                </form>
                            </div>
                            <?php elseif ($estado === 'confirmado'): ?>
                            <div class="card-footer bg-white border-top-0">
                                <button type="button" class="btn btn-info w-100" onclick="completarServicio(<?= $servicio['id'] ?>)">
                                    <i class="bi bi-check-all me-1"></i>Marcar como Completado
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal para notas -->
<div class="modal fade" id="notasModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Notas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="confirmacion_id" id="modal_confirmacion_id">
                    <input type="hidden" name="action" id="modal_action">
                    <textarea class="form-control" name="notas" rows="3" placeholder="Notas opcionales..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="modal_submit_btn">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const modal = new bootstrap.Modal(document.getElementById('notasModal'));

function confirmarServicio(id) {
    document.getElementById('modal_confirmacion_id').value = id;
    document.getElementById('modal_action').value = 'confirmar';
    document.getElementById('modal_submit_btn').textContent = 'Confirmar Servicio';
    document.getElementById('modal_submit_btn').className = 'btn btn-success';
    modal.show();
}

function rechazarServicio(id) {
    document.getElementById('modal_confirmacion_id').value = id;
    document.getElementById('modal_action').value = 'rechazar';
    document.getElementById('modal_submit_btn').textContent = 'Rechazar Servicio';
    document.getElementById('modal_submit_btn').className = 'btn btn-danger';
    modal.show();
}

function completarServicio(id) {
    document.getElementById('modal_confirmacion_id').value = id;
    document.getElementById('modal_action').value = 'completar';
    document.getElementById('modal_submit_btn').textContent = 'Completar Servicio';
    document.getElementById('modal_submit_btn').className = 'btn btn-info';
    modal.show();
}
</script>

<?php include 'includes/footer.php'; ?>
