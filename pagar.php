<?php
require_once 'includes/header.php';

// Redirigir si el usuario no está autenticado o no es un turista
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    echo "<div class='container py-5'><div class='alert alert-danger text-center'>ID de pedido no válido.</div></div>";
    require_once 'includes/footer.php';
    exit();
}

$id_pedido = $_GET['id'];
$id_turista = $_SESSION['user_id'];
$pedido = null;
$message = '';

if ($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pagar'])) {
        // Simular el proceso de pago
        $nuevo_estado = 'completado';
        $stmt = $conn->prepare("UPDATE pedidos_servicios SET estado = ? WHERE id = ? AND id_turista = ? AND estado = 'confirmado'");
        $stmt->bind_param("sii", $nuevo_estado, $id_pedido, $id_turista);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = "<div class='alert alert-success'>¡Pago realizado con éxito! Tu pedido ha sido pagado.</div>";
        } else {
            $message = "<div class='alert alert-danger'>No se pudo procesar el pago. El pedido no está confirmado o ya ha sido pagado.</div>";
        }
        $stmt->close();
    }

    // Obtener nombre del servicio según el tipo
    $sql = "SELECT ps.id, ps.precio_total, ps.estado, ps.tipo_item, ps.tipo_proveedor,
                ps.fecha_servicio, ps.cantidad_personas,
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
                    ELSE 'Servicio sin nombre'
                END AS item_name,
                CASE
                    WHEN ps.tipo_proveedor = 'agencia' THEN a.nombre_agencia
                    WHEN ps.tipo_proveedor = 'guia' THEN g.nombre_guia
                    WHEN ps.tipo_proveedor = 'local' THEN l.nombre_local
                END AS nombre_proveedor
            FROM pedidos_servicios ps
            LEFT JOIN agencias a ON ps.id_proveedor = a.id AND ps.tipo_proveedor = 'agencia'
            LEFT JOIN guias_turisticos g ON ps.id_proveedor = g.id AND ps.tipo_proveedor = 'guia'
            LEFT JOIN lugares_locales l ON ps.id_proveedor = l.id AND ps.tipo_proveedor = 'local'
            LEFT JOIN servicios_agencia sa ON ps.id_servicio_o_menu = sa.id AND ps.tipo_proveedor = 'agencia' AND ps.tipo_item = 'servicio'
            LEFT JOIN servicios_guia sg ON ps.id_servicio_o_menu = sg.id AND ps.tipo_proveedor = 'guia' AND ps.tipo_item = 'servicio'
            LEFT JOIN servicios_local sl ON ps.id_servicio_o_menu = sl.id AND ps.tipo_proveedor = 'local' AND ps.tipo_item = 'servicio'
            LEFT JOIN menus_agencia ma ON ps.id_servicio_o_menu = ma.id AND ps.tipo_proveedor = 'agencia' AND ps.tipo_item = 'menu'
            LEFT JOIN menus_local ml ON ps.id_servicio_o_menu = ml.id AND ps.tipo_proveedor = 'local' AND ps.tipo_item = 'menu'
            WHERE ps.id = ? AND ps.id_turista = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_pedido, $id_turista);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $pedido = $result->fetch_assoc();
    } else {
        echo "<div class='container py-5'><div class='alert alert-danger text-center'>Pedido no encontrado o no te pertenece.</div></div>";
        require_once 'includes/footer.php';
        exit();
    }
    $stmt->close();
    $conn->close();
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Resumen del Pago</h2>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <?= $message ?>
                    <?php endif; ?>

                    <?php if ($pedido && $pedido['estado'] === 'confirmado'): ?>
                        <h3 class="card-title">Pedido #<?= htmlspecialchars($pedido['id']) ?></h3>
                        <p><strong>Proveedor:</strong> <?= htmlspecialchars($pedido['nombre_proveedor']) ?></p>
                        <p><strong>Servicio/Menú:</strong> <?= htmlspecialchars($pedido['item_name']) ?></p>
                        <hr>
                        <p class="fs-4 fw-bold text-end">Total a Pagar: <?= htmlspecialchars(number_format($pedido['precio_total'], 2)) ?> €</p>
                        
                        <form action="pagar.php?id=<?= $id_pedido ?>" method="POST">
                            <h4 class="mt-4">Método de Pago</h4>
                            <p class="text-muted">Esto es una simulación. Haz clic en el botón para marcar el pedido como pagado.</p>
                            <div class="d-grid">
                                <button type="submit" name="pagar" class="btn btn-success btn-lg">Pagar Ahora</button>
                            </div>
                        </form>
                    <?php elseif ($pedido && ($pedido['estado'] === 'completado' || $pedido['estado'] === 'pagado')): ?>
                        <div class="alert alert-info text-center">
                            <h4 class="alert-heading">Este pedido ya ha sido pagado.</h4>
                            <p>Gracias por tu compra.</p>
                            <a href="mis_pedidos.php" class="btn btn-primary">Volver a Mis Pedidos</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning text-center">
                            <h4 class="alert-heading">Este pedido no puede ser pagado.</h4>
                            <p>El pedido debe estar en estado 'Confirmado' para poder realizar el pago.</p>
                            <a href="mis_pedidos.php" class="btn btn-secondary">Volver a Mis Pedidos</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
