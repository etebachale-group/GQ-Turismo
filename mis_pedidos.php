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

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">Mis Pedidos de Servicios</h1>
        <p class="lead text-muted">Aquí puedes ver el estado de tus solicitudes de servicios y contrataciones.</p>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Proveedor</th>
                    <th>Servicio/Menú</th>
                    <th>Tipo</th>
                    <th>Fecha Solicitud</th>
                    <th>Fecha Servicio</th>
                    <th>Personas/Horas</th>
                    <th>Precio Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pedidos) > 0): ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?= htmlspecialchars($pedido['id']) ?></td>
                            <td><?= htmlspecialchars($pedido['nombre_proveedor']) ?> (<?= htmlspecialchars($pedido['tipo_proveedor']) ?>)</td>
                            <td><?= htmlspecialchars($pedido['item_name']) ?></td>
                            <td><?= htmlspecialchars($pedido['tipo_item']) ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($pedido['fecha_solicitud']))) ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($pedido['fecha_servicio']))) ?></td>
                            <td><?= htmlspecialchars($pedido['cantidad_personas']) ?></td>
                            <td><?= htmlspecialchars(number_format($pedido['precio_total'], 2)) ?> €</td>
                            <td><span class="badge bg-<?= ($pedido['estado'] == 'confirmado') ? 'success' : (($pedido['estado'] == 'cancelado') ? 'danger' : 'warning') ?>"><?= htmlspecialchars(ucfirst($pedido['estado'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No has realizado ningún pedido de servicio aún.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
