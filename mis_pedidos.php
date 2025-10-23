<?php
require_once 'includes/header.php';

// Redirigir si el usuario no está autenticado o no es un turista
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    header('Location: index.php'); // O a una página de acceso denegado
    exit;
}

$id_turista = $_SESSION['user_id'];
$pedidos = [];

if ($conn) {
    $sql = "SELECT ps.id, ps.tipo_proveedor, ps.id_proveedor, ps.id_servicio_o_menu, ps.tipo_item, ps.fecha_solicitud, ps.fecha_servicio, ps.cantidad_personas, ps.precio_total, ps.estado,
                CASE
                    WHEN ps.tipo_proveedor = 'agencia' THEN a.nombre_agencia
                    WHEN ps.tipo_proveedor = 'guia' THEN g.nombre_guia
                    WHEN ps.tipo_proveedor = 'local' THEN l.nombre_local
                END AS nombre_proveedor,
                CASE
                    WHEN ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'agencia' THEN sa.nombre_servicio
                    WHEN ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'agencia' THEN ma.nombre_menu
                    WHEN ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'guia' THEN sg.nombre_servicio
                    WHEN ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'local' THEN sl.nombre_servicio
                    WHEN ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'local' THEN ml.nombre_menu
                END AS item_name
            FROM pedidos_servicios ps
            LEFT JOIN agencias a ON ps.id_proveedor = a.id AND ps.tipo_proveedor = 'agencia'
            LEFT JOIN guias_turisticos g ON ps.id_proveedor = g.id AND ps.tipo_proveedor = 'guia'
            LEFT JOIN lugares_locales l ON ps.id_proveedor = l.id AND ps.tipo_proveedor = 'local'
            LEFT JOIN servicios_agencia sa ON ps.id_servicio_o_menu = sa.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'agencia'
            LEFT JOIN menus_agencia ma ON ps.id_servicio_o_menu = ma.id AND ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'agencia'
            LEFT JOIN servicios_guia sg ON ps.id_servicio_o_menu = sg.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'guia'
            LEFT JOIN servicios_local sl ON ps.id_servicio_o_menu = sl.id AND ps.tipo_item = 'servicio' AND ps.tipo_proveedor = 'local'
            LEFT JOIN menus_local ml ON ps.id_servicio_o_menu = ml.id AND ps.tipo_item = 'menu' AND ps.tipo_proveedor = 'local'
            WHERE ps.id_turista = ?
            ORDER BY ps.fecha_solicitud DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_turista);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
    }
    $stmt->close();
    $conn->close();
}
?>

<!-- Hero Section -->
<section class="py-5" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);" data-aos="fade-in">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-3 text-white">Mis Pedidos</h1>
                <p class="lead mb-0 text-white">Gestiona todas tus solicitudes de servicios turísticos</p>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <i class="bi bi-bag-check-fill text-white" style="font-size: 8rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Filtros y Estadísticas -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 bg-white h-100 text-center">
                    <div class="card-body p-3">
                        <i class="bi bi-clock-history text-warning fs-1 mb-2"></i>
                        <h4 class="fw-bold mb-0"><?= count(array_filter($pedidos, fn($p) => $p['estado'] === 'pendiente')) ?></h4>
                        <small class="text-muted">Pendientes</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 bg-white h-100 text-center">
                    <div class="card-body p-3">
                        <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                        <h4 class="fw-bold mb-0"><?= count(array_filter($pedidos, fn($p) => $p['estado'] === 'confirmado')) ?></h4>
                        <small class="text-muted">Confirmados</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 bg-white h-100 text-center">
                    <div class="card-body p-3">
                        <i class="bi bi-x-circle text-danger fs-1 mb-2"></i>
                        <h4 class="fw-bold mb-0"><?= count(array_filter($pedidos, fn($p) => $p['estado'] === 'cancelado')) ?></h4>
                        <small class="text-muted">Cancelados</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 bg-white h-100 text-center">
                    <div class="card-body p-3">
                        <i class="bi bi-check-all text-info fs-1 mb-2"></i>
                        <h4 class="fw-bold mb-0"><?= count(array_filter($pedidos, fn($p) => $p['estado'] === 'completado')) ?></h4>
                        <small class="text-muted">Completados</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <?php if (count($pedidos) > 0): ?>
        <!-- Vista de Cards para Móvil/Tablet -->
        <div class="d-lg-none">
            <div class="row g-4">
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="col-12" data-aos="fade-up">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-<?= ($pedido['estado'] == 'confirmado') ? 'success' : (($pedido['estado'] == 'cancelado') ? 'danger' : (($pedido['estado'] == 'completado') ? 'info' : 'warning')) ?> bg-opacity-10 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-uppercase">#<?= htmlspecialchars($pedido['id']) ?></strong>
                                    <span class="badge bg-<?= ($pedido['estado'] == 'confirmado') ? 'success' : (($pedido['estado'] == 'cancelado') ? 'danger' : (($pedido['estado'] == 'completado') ? 'info' : 'warning')) ?>">
                                        <?= htmlspecialchars(ucfirst($pedido['estado'])) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-3">
                                    <i class="bi bi-<?= $pedido['tipo_proveedor'] === 'agencia' ? 'building' : ($pedido['tipo_proveedor'] === 'guia' ? 'person-badge' : 'shop') ?> text-primary me-2"></i>
                                    <?= htmlspecialchars($pedido['nombre_proveedor']) ?>
                                </h5>
                                <p class="mb-2"><strong>Servicio:</strong> <?= htmlspecialchars($pedido['item_name']) ?></p>
                                <p class="mb-2"><strong>Tipo:</strong> <span class="badge bg-secondary"><?= htmlspecialchars($pedido['tipo_item']) ?></span></p>
                                <p class="mb-2"><strong>Fecha Solicitud:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($pedido['fecha_solicitud']))) ?></p>
                                <p class="mb-2"><strong>Fecha Servicio:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($pedido['fecha_servicio']))) ?></p>
                                <p class="mb-2"><strong>Personas/Horas:</strong> <?= htmlspecialchars($pedido['cantidad_personas']) ?></p>
                                <div class="alert alert-primary mb-3">
                                    <strong>Total: <?= htmlspecialchars(number_format($pedido['precio_total'], 2)) ?> €</strong>
                                </div>
                                <?php if ($pedido['estado'] == 'confirmado'): ?>
                                    <a href="pagar.php?id=<?= htmlspecialchars($pedido['id']) ?>" class="btn btn-success w-100">
                                        <i class="bi bi-credit-card me-2"></i>Proceder al Pago
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Vista de Tabla para Desktop -->
        <div class="d-none d-lg-block">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="py-3">Proveedor</th>
                                    <th class="py-3">Servicio/Menú</th>
                                    <th class="py-3">Tipo</th>
                                    <th class="py-3">F. Solicitud</th>
                                    <th class="py-3">F. Servicio</th>
                                    <th class="py-3 text-center">Personas</th>
                                    <th class="py-3 text-end">Precio</th>
                                    <th class="py-3 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $pedido): ?>
                                    <tr>
                                        <td class="px-4">
                                            <strong class="text-primary">#<?= htmlspecialchars($pedido['id']) ?></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-<?= $pedido['tipo_proveedor'] === 'agencia' ? 'building' : ($pedido['tipo_proveedor'] === 'guia' ? 'person-badge' : 'shop') ?> text-primary me-2 fs-5"></i>
                                                <div>
                                                    <div class="fw-semibold"><?= htmlspecialchars($pedido['nombre_proveedor']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($pedido['tipo_proveedor']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($pedido['item_name']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($pedido['tipo_item']) ?></span></td>
                                        <td><small><?= htmlspecialchars(date('d/m/Y H:i', strtotime($pedido['fecha_solicitud']))) ?></small></td>
                                        <td><small><?= htmlspecialchars(date('d/m/Y', strtotime($pedido['fecha_servicio']))) ?></small></td>
                                        <td class="text-center"><?= htmlspecialchars($pedido['cantidad_personas']) ?></td>
                                        <td class="text-end"><strong><?= htmlspecialchars(number_format($pedido['precio_total'], 2)) ?> €</strong></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= ($pedido['estado'] == 'confirmado') ? 'success' : (($pedido['estado'] == 'cancelado') ? 'danger' : (($pedido['estado'] == 'completado') ? 'info' : 'warning')) ?>">
                                                <?= htmlspecialchars(ucfirst($pedido['estado'])) ?>
                                            </span>
                                            <?php if ($pedido['estado'] == 'confirmado'): ?>
                                                <a href="pagar.php?id=<?= htmlspecialchars($pedido['id']) ?>" class="btn btn-sm btn-success mt-2">
                                                    <i class="bi bi-credit-card me-1"></i>Pagar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-bag-x text-muted" style="font-size: 5rem;"></i>
            </div>
            <h3 class="fw-bold mb-3">No has realizado ningún pedido aún</h3>
            <p class="lead text-muted mb-4">Explora nuestros servicios y comienza a planificar tu aventura</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="agencias.php" class="btn btn-primary">
                    <i class="bi bi-building me-2"></i>Ver Agencias
                </a>
                <a href="guias.php" class="btn btn-success">
                    <i class="bi bi-person-badge me-2"></i>Ver Guías
                </a>
                <a href="locales.php" class="btn btn-info">
                    <i class="bi bi-shop me-2"></i>Ver Locales
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
