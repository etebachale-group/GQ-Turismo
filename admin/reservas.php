<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión y tiene el tipo de usuario correcto
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['agencia', 'guia', 'local', 'super_admin'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

// Configurar título de página
$page_title = "Gestionar Reservas y Pedidos";

// Obtener todas las reservas con información del itinerario
$query = "SELECT r.id, COALESCE(i.nombre_itinerario, 'Sin nombre') AS destino, u.nombre AS usuario, 
                 r.fecha_reserva, r.personas, r.estado, r.fecha_reserva as created_at
          FROM reservas r 
          JOIN itinerarios i ON r.id_itinerario = i.id
          JOIN usuarios u ON r.id_usuario = u.id
          ORDER BY r.fecha_reserva DESC";
$result = $conn->query($query);
$reservas = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reservas[] = $row;
    }
}

// Obtener pedidos de servicios
$query_pedidos = "SELECT ps.id, ps.tipo_proveedor, ps.precio_total, ps.estado, ps.fecha_servicio,
                         u.nombre AS turista, ps.tipo_item, ps.fecha_solicitud as created_at,
                         COALESCE(ps.nombre_servicio, 
                            CASE ps.tipo_item
                                WHEN 'servicio' THEN 
                                    CASE ps.tipo_proveedor
                                        WHEN 'agencia' THEN sa.nombre_servicio
                                        WHEN 'guia' THEN sg.nombre_servicio
                                        WHEN 'local' THEN sl.nombre_servicio
                                    END
                                WHEN 'menu' THEN 
                                    CASE ps.tipo_proveedor
                                        WHEN 'agencia' THEN ma.nombre_menu
                                        WHEN 'local' THEN ml.nombre_menu
                                    END
                            END
                        ) AS nombre_servicio
                  FROM pedidos_servicios ps
                  JOIN usuarios u ON ps.id_turista = u.id
                  LEFT JOIN servicios_agencia sa ON ps.id_servicio_o_menu = sa.id AND ps.tipo_proveedor = 'agencia' AND ps.tipo_item = 'servicio'
                  LEFT JOIN servicios_guia sg ON ps.id_servicio_o_menu = sg.id AND ps.tipo_proveedor = 'guia' AND ps.tipo_item = 'servicio'
                  LEFT JOIN servicios_local sl ON ps.id_servicio_o_menu = sl.id AND ps.tipo_proveedor = 'local' AND ps.tipo_item = 'servicio'
                  LEFT JOIN menus_agencia ma ON ps.id_servicio_o_menu = ma.id AND ps.tipo_proveedor = 'agencia' AND ps.tipo_item = 'menu'
                  LEFT JOIN menus_local ml ON ps.id_servicio_o_menu = ml.id AND ps.tipo_proveedor = 'local' AND ps.tipo_item = 'menu'
                  ORDER BY ps.fecha_solicitud DESC";
$result_pedidos = $conn->query($query_pedidos);
$pedidos = [];
if ($result_pedidos && $result_pedidos->num_rows > 0) {
    while ($row = $result_pedidos->fetch_assoc()) {
        $pedidos[] = $row;
    }
}

$conn->close();

// Incluir el header moderno
include 'admin_header.php';
?>

        <!-- Admin Page Header -->
        <div class="admin-page-header">
            <h1><i class="bi bi-calendar-check me-3"></i>Gestionar Reservas y Pedidos</h1>
            <p class="mb-0">Visualiza y gestiona todas las reservas e itinerarios y pedidos de servicios</p>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="reservasTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="reservas-tab" data-bs-toggle="tab" data-bs-target="#reservas" type="button">
                    <i class="bi bi-calendar3"></i> Reservas de Itinerarios
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pedidos-tab" data-bs-toggle="tab" data-bs-target="#pedidos" type="button">
                    <i class="bi bi-cart-check"></i> Pedidos de Servicios
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="reservasTabContent">
            <!-- Reservas Tab -->
            <div class="tab-pane fade show active" id="reservas">
                <div class="admin-table">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Itinerario</th>
                                <th>Turista</th>
                                <th>Fecha Reserva</th>
                                <th>Personas</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($reservas) > 0): ?>
                                <?php foreach ($reservas as $reserva): ?>
                                    <tr>
                                        <td><strong>#<?= htmlspecialchars($reserva['id']) ?></strong></td>
                                        <td><?= htmlspecialchars($reserva['destino']) ?></td>
                                        <td><?= htmlspecialchars($reserva['usuario']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($reserva['fecha_reserva'])) ?></td>
                                        <td><i class="bi bi-people-fill"></i> <?= htmlspecialchars($reserva['personas']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= htmlspecialchars($reserva['estado']) ?>">
                                                <?= ucfirst(htmlspecialchars($reserva['estado'])) ?>
                                            </span>
                                        </td>
                                        <td><strong>$<?= number_format($reserva['precio_total'] ?? 0, 2) ?></strong></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="#" class="btn btn-outline-primary" title="Ver detalles">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="#" class="btn btn-outline-secondary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: var(--gray-400);"></i>
                                        <p class="mt-3 text-muted">No hay reservas registradas</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pedidos Tab -->
            <div class="tab-pane fade" id="pedidos">
                <div class="admin-table">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Servicio</th>
                                <th>Turista</th>
                                <th>Proveedor</th>
                                <th>Tipo</th>
                                <th>Fecha Servicio</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pedidos) > 0): ?>
                                <?php foreach ($pedidos as $pedido): ?>
                                    <tr>
                                        <td><strong>#<?= htmlspecialchars($pedido['id']) ?></strong></td>
                                        <td><?= htmlspecialchars($pedido['nombre_servicio']) ?></td>
                                        <td><?= htmlspecialchars($pedido['turista']) ?></td>
                                        <td>
                                            <span class="badge" style="background: var(--gray-200); color: var(--dark);">
                                                <?= ucfirst(htmlspecialchars($pedido['tipo_proveedor'])) ?>
                                            </span>
                                        </td>
                                        <td><?= ucfirst(htmlspecialchars($pedido['tipo_item'])) ?></td>
                                        <td><?= $pedido['fecha_servicio'] ? date('d/m/Y', strtotime($pedido['fecha_servicio'])) : 'N/A' ?></td>
                                        <td>
                                            <span class="badge badge-<?= htmlspecialchars($pedido['estado']) ?>">
                                                <?= ucfirst(htmlspecialchars($pedido['estado'])) ?>
                                            </span>
                                        </td>
                                        <td><strong>$<?= number_format($pedido['precio_total'], 2) ?></strong></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="#" class="btn btn-outline-success" title="Confirmar">
                                                    <i class="bi bi-check-circle"></i>
                                                </a>
                                                <a href="#" class="btn btn-outline-danger" title="Cancelar">
                                                    <i class="bi bi-x-circle"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: var(--gray-400);"></i>
                                        <p class="mt-3 text-muted">No hay pedidos de servicios registrados</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

<?php include 'admin_footer.php'; ?>