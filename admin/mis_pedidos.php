<?php
require_once '../includes/header.php';

// Verificar que sea proveedor
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['agencia', 'guia', 'local'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$pedidos = [];

// Obtener tipo de proveedor en español
$tipo_texto = [
    'agencia' => 'Agencia',
    'guia' => 'Guía Turístico',
    'local' => 'Lugar Local'
];

if ($conn) {
    // Obtener nombre del proveedor
    $nombre_proveedor = '';
    switch($user_type) {
        case 'agencia':
            $stmt = $conn->prepare("SELECT nombre_agencia as nombre FROM agencias WHERE id = ?");
            break;
        case 'guia':
            $stmt = $conn->prepare("SELECT nombre_guia as nombre FROM guias_turisticos WHERE id = ?");
            break;
        case 'local':
            $stmt = $conn->prepare("SELECT nombre_local as nombre FROM lugares_locales WHERE id = ?");
            break;
    }
    
    if (isset($stmt)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $nombre_proveedor = $row['nombre'];
        }
        $stmt->close();
    }
    
    // Obtener pedidos
    $sql = "SELECT ps.*, 
                u.nombre as turista_nombre,
                u.email as turista_email,
                CASE
                    WHEN ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'agencia' THEN sa.nombre_servicio
                    WHEN ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'agencia' THEN ma.nombre_menu
                    WHEN ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'guia' THEN sg.nombre_servicio
                    WHEN ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'local' THEN sl.nombre_servicio
                    WHEN ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'local' THEN ml.nombre_menu
                    WHEN ps.tipo_item = 'guia_destino' THEN CONCAT('Guía para ', d.nombre)
                END AS item_nombre
            FROM pedidos_servicios ps
            LEFT JOIN usuarios u ON ps.id_turista = u.id
            LEFT JOIN servicios_agencia sa ON ps.id_servicio_o_menu = sa.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'agencia'
            LEFT JOIN menus_agencia ma ON ps.id_servicio_o_menu = ma.id AND ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'agencia'
            LEFT JOIN servicios_guia sg ON ps.id_servicio_o_menu = sg.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'guia'
            LEFT JOIN servicios_local sl ON ps.id_servicio_o_menu = sl.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'local'
            LEFT JOIN menus_local ml ON ps.id_servicio_o_menu = ml.id AND ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'local'
            LEFT JOIN destinos d ON ps.id_destino = d.id
            WHERE ps.id_proveedor = ? AND ps.tipo_proveedor = ?
            ORDER BY ps.fecha_solicitud DESC";
    
    if (!$stmt = $conn->prepare($sql)) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("is", $user_id, $user_type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
    $stmt->close();
    $conn->close();
}
?>

<!-- Hero Section -->
<section class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3 text-white">
                    <i class="bi bi-bag-check me-2"></i>Mis Pedidos Recibidos
                </h1>
                <p class="lead mb-0 text-white">
                    <?php echo htmlspecialchars($nombre_proveedor); ?> - <?php echo $tipo_texto[$user_type]; ?>
                </p>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <i class="bi bi-clipboard-check text-white" style="font-size: 6rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Estadísticas -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <div class="card border-0 h-100">
                    <div class="card-body">
                        <i class="bi bi-clock-history text-warning fs-1 mb-2"></i>
                        <h3 class="fw-bold text-dark mb-0"><?= count(array_filter($pedidos, fn($p) => $p['estado'] === 'pendiente')) ?></h3>
                        <small class="text-muted">Pendientes</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 h-100">
                    <div class="card-body">
                        <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                        <h3 class="fw-bold text-dark mb-0"><?= count(array_filter($pedidos, fn($p) => $p['estado'] === 'confirmado')) ?></h3>
                        <small class="text-muted">Confirmados</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 h-100">
                    <div class="card-body">
                        <i class="bi bi-check-all text-info fs-1 mb-2"></i>
                        <h3 class="fw-bold text-dark mb-0"><?= count(array_filter($pedidos, fn($p) => $p['estado'] === 'completado')) ?></h3>
                        <small class="text-muted">Completados</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 h-100">
                    <div class="card-body">
                        <i class="bi bi-x-circle text-danger fs-1 mb-2"></i>
                        <h3 class="fw-bold text-dark mb-0"><?= count(array_filter($pedidos, fn($p) => $p['estado'] === 'cancelado')) ?></h3>
                        <small class="text-muted">Cancelados</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Listado de Pedidos -->
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold"><i class="bi bi-list-check me-2 text-primary"></i>Todos los Pedidos</h3>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary active" data-filter="todos">Todos</button>
                <button type="button" class="btn btn-outline-warning" data-filter="pendiente">Pendientes</button>
                <button type="button" class="btn btn-outline-success" data-filter="confirmado">Confirmados</button>
            </div>
        </div>
    </div>

    <?php if (count($pedidos) === 0): ?>
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <h5>No tienes pedidos aún</h5>
            <p class="mb-0">Cuando los turistas soliciten tus servicios, aparecerán aquí.</p>
        </div>
    <?php else: ?>
        <div class="row g-4" id="pedidos-container">
            <?php foreach ($pedidos as $pedido): ?>
                <div class="col-12 pedido-item" data-estado="<?php echo $pedido['estado']; ?>">
                    <div class="card pedido-card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="flex-grow-1">
                                            <h5 class="fw-bold mb-2">
                                                <i class="bi bi-box-seam text-primary me-2"></i>
                                                <?php echo htmlspecialchars($pedido['item_nombre'] ?? 'Servicio'); ?>
                                            </h5>
                                            <p class="text-muted mb-2">
                                                <i class="bi bi-person me-2"></i>
                                                <strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['turista_nombre']); ?>
                                            </p>
                                            <p class="text-muted mb-2">
                                                <i class="bi bi-envelope me-2"></i>
                                                <?php echo htmlspecialchars($pedido['turista_email']); ?>
                                            </p>
                                            <?php if ($pedido['turista_telefono']): ?>
                                            <p class="text-muted mb-2">
                                                <i class="bi bi-telephone me-2"></i>
                                                <?php echo htmlspecialchars($pedido['turista_telefono']); ?>
                                            </p>
                                            <?php endif; ?>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-calendar me-2"></i>
                                                <strong>Fecha servicio:</strong> <?php echo date('d/m/Y', strtotime($pedido['fecha_servicio'])); ?>
                                            </p>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-people me-2"></i>
                                                <strong>Personas:</strong> <?php echo $pedido['cantidad_personas']; ?>
                                            </p>
                                        </div>
                                        <?php
                                        $badge_class = [
                                            'pendiente' => 'bg-warning',
                                            'confirmado' => 'bg-success',
                                            'cancelado' => 'bg-danger',
                                            'completado' => 'bg-info'
                                        ];
                                        $estado_texto = [
                                            'pendiente' => 'Pendiente',
                                            'confirmado' => 'Confirmado',
                                            'cancelado' => 'Cancelado',
                                            'completado' => 'Completado'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $badge_class[$pedido['estado']]; ?> ms-3">
                                            <?php echo $estado_texto[$pedido['estado']]; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <h3 class="text-primary fw-bold mb-3">
                                        $<?php echo number_format($pedido['precio_total'], 2); ?>
                                    </h3>
                                    <?php if ($pedido['estado'] === 'pendiente'): ?>
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-success btn-confirmar" data-id="<?php echo $pedido['id']; ?>">
                                                <i class="bi bi-check-circle me-2"></i>Aceptar
                                            </button>
                                            <button class="btn btn-danger btn-cancelar" data-id="<?php echo $pedido['id']; ?>">
                                                <i class="bi bi-x-circle me-2"></i>Rechazar
                                            </button>
                                        </div>
                                    <?php elseif ($pedido['estado'] === 'confirmado'): ?>
                                        <button class="btn btn-info btn-completar" data-id="<?php echo $pedido['id']; ?>">
                                            <i class="bi bi-check-all me-2"></i>Marcar Completado
                                        </button>
                                    <?php endif; ?>
                                    <small class="text-muted d-block mt-2">
                                        Solicitado: <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_solicitud'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtros
    document.querySelectorAll('[data-filter]').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Actualizar botones activos
            document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filtrar pedidos
            document.querySelectorAll('.pedido-item').forEach(item => {
                if (filter === 'todos' || item.dataset.estado === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    
    // Confirmar pedido
    document.querySelectorAll('.btn-confirmar').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('¿Confirmar este pedido?')) {
                actualizarEstado(this.dataset.id, 'confirmado');
            }
        });
    });
    
    // Cancelar pedido
    document.querySelectorAll('.btn-cancelar').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('¿Rechazar este pedido?')) {
                actualizarEstado(this.dataset.id, 'cancelado');
            }
        });
    });
    
    // Completar pedido
    document.querySelectorAll('.btn-completar').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('¿Marcar como completado?')) {
                actualizarEstado(this.dataset.id, 'completado');
            }
        });
    });
    
    function actualizarEstado(pedidoId, nuevoEstado) {
        fetch('../api/pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'update_status',
                pedido_id: pedidoId,
                estado: nuevoEstado
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar el pedido');
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
