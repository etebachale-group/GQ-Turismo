<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/evisa.php';
require_once __DIR__ . '/includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$id_pedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_pedido) {
    echo "<div class='container py-5'><div class='alert alert-danger text-center'>Identificador de reserva no válido.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? 'turista';

// Obtener datos del pedido
$sql = "SELECT ps.*, 
            u.nombre as turista_nombre, u.email as turista_email,
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
                ELSE ps.nombre_servicio
            END AS item_nombre,
            CASE ps.tipo_proveedor
                WHEN 'agencia' THEN a.nombre_agencia
                WHEN 'guia' THEN g.nombre_guia
                WHEN 'local' THEN l.nombre_local
            END AS proveedor_nombre
        FROM pedidos_servicios ps
        JOIN usuarios u ON ps.id_turista = u.id
        LEFT JOIN agencias a ON ps.id_proveedor = a.id AND ps.tipo_proveedor = 'agencia'
        LEFT JOIN guias_turisticos g ON ps.id_proveedor = g.id AND ps.tipo_proveedor = 'guia'
        LEFT JOIN lugares_locales l ON ps.id_proveedor = l.id AND ps.tipo_proveedor = 'local'
        LEFT JOIN servicios_agencia sa ON ps.id_servicio_o_menu = sa.id AND ps.tipo_proveedor = 'agencia' AND ps.tipo_item = 'servicio'
        LEFT JOIN servicios_guia sg ON ps.id_servicio_o_menu = sg.id AND ps.tipo_proveedor = 'guia' AND ps.tipo_item = 'servicio'
        LEFT JOIN servicios_local sl ON ps.id_servicio_o_menu = sl.id AND ps.tipo_proveedor = 'local' AND ps.tipo_item = 'servicio'
        LEFT JOIN menus_agencia ma ON ps.id_servicio_o_menu = ma.id AND ps.tipo_proveedor = 'agencia' AND ps.tipo_item = 'menu'
        LEFT JOIN menus_local ml ON ps.id_servicio_o_menu = ml.id AND ps.tipo_proveedor = 'local' AND ps.tipo_item = 'menu'
        WHERE ps.id = ?";

if ($user_type === 'turista') {
    $sql .= " AND ps.id_turista = ?";
}

$stmt = $conn->prepare($sql);
if ($user_type === 'turista') {
    $stmt->bind_param("ii", $id_pedido, $user_id);
} else {
    $stmt->bind_param("i", $id_pedido);
}
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pedido) {
    echo "<div class='container py-5'><div class='alert alert-warning text-center'>No se encontró la reserva solicitada o no tienes permisos para verla.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Obtener datos del último pago si existe
$pago = null;
$stmt_p = $conn->prepare("SELECT * FROM pagos WHERE id_pedido = ? ORDER BY id DESC LIMIT 1");
$stmt_p->bind_param("i", $id_pedido);
$stmt_p->execute();
$pago = $stmt_p->get_result()->fetch_assoc();
$stmt_p->close();

$codigo_reserva = $pago['codigo_referencia'] ?? ('GQ-RES-' . str_pad((string)$pedido['id'], 6, '0', STR_PAD_LEFT));
$nombre_servicio = $pedido['item_nombre'] ?? $pedido['nombre_servicio'] ?? 'Servicio Turístico';
$nombre_proveedor = $pedido['proveedor_nombre'] ?? 'Proveedor Local';
$fecha_servicio = $pedido['fecha_servicio'] ? date('d/m/Y', strtotime($pedido['fecha_servicio'])) : 'A coordinar';
$total_formateado = format_currency((float)$pedido['precio_total'], 'XAF');

// Enlace de WhatsApp prellenado con código y detalles
$wa_mensaje = "Hola, he reservado en GQ-Turismo el servicio: {$nombre_servicio} (Código: {$codigo_reserva}). Fecha: {$fecha_servicio}. Deseo coordinar el punto de encuentro.";
$wa_url = "https://wa.me/240222000000?text=" . urlencode($wa_mensaje);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Tarjeta de Confirmación / Voucher imprimible -->
            <div class="card shadow-sm border-0" style="border-radius: var(--radius-lg, 16px); overflow: hidden;">
                <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, var(--c-selva-800, #0d381e), var(--c-selva-600, #1b5e20));">
                    <div class="mb-3">
                        <i class="bi bi-check-circle-fill" style="font-size: 3.5rem; color: #a3e9b8;"></i>
                    </div>
                    <span class="badge badge--confirmado mb-2">Comprobante de Reserva Oficial</span>
                    <h2 class="fw-bold mb-1" style="font-family: var(--font-display, serif);">¡Reserva Confirmada!</h2>
                    <p class="mb-0 text-white-50">Guarda este comprobante para presentarlo a tu llegada</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <!-- Código de Reserva Destacado -->
                    <div class="text-center p-3 mb-4" style="background: var(--c-selva-50, #f0f7f2); border: 2px dashed var(--c-selva-300, #a3d9b8); border-radius: 12px;">
                        <span class="text-muted small text-uppercase fw-bold">Código Único de Reserva</span>
                        <div class="h2 fw-bold mb-0 text-success font-monospace"><?= htmlspecialchars($codigo_reserva) ?></div>
                    </div>

                    <!-- Progreso de la Reserva (CMP-03) -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-muted text-uppercase mb-3 small">Estado del Servicio</h6>
                        <div class="d-flex justify-content-between align-items-center text-center position-relative">
                            <div class="flex-fill">
                                <i class="bi bi-calendar-check-fill text-success fs-4"></i>
                                <div class="small fw-bold">1. Solicitado</div>
                                <span class="badge badge--confirmado">Completado</span>
                            </div>
                            <div class="flex-fill">
                                <i class="bi bi-person-check-fill text-success fs-4"></i>
                                <div class="small fw-bold">2. Aceptado</div>
                                <span class="badge badge--confirmado">Confirmado</span>
                            </div>
                            <div class="flex-fill">
                                <i class="bi bi-wallet2 text-success fs-4"></i>
                                <div class="small fw-bold">3. Método Pago</div>
                                <span class="badge badge--pendiente"><?= htmlspecialchars(ucfirst($pago['metodo'] ?? 'Efectivo / Acordado')) ?></span>
                            </div>
                            <div class="flex-fill">
                                <i class="bi bi-compass text-muted fs-4"></i>
                                <div class="small fw-bold text-muted">4. Viaje</div>
                                <span class="badge bg-light text-muted">Pendiente</span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Detalles del Itinerario y Proveedor -->
                    <h5 class="fw-bold mb-3" style="font-family: var(--font-display);">Detalles del Servicio</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Servicio Reservado</small>
                            <strong><?= htmlspecialchars($nombre_servicio) ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Proveedor Responsable</small>
                            <strong><?= htmlspecialchars($nombre_proveedor) ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Fecha Prevista</small>
                            <strong><?= htmlspecialchars($fecha_servicio) ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Personas</small>
                            <strong><?= (int)($pedido['cantidad_personas'] ?? $pedido['numero_personas'] ?? 1) ?> persona(s)</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Turista Registrado</small>
                            <strong><?= htmlspecialchars($pedido['turista_nombre']) ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Total del Servicio (Fijado)</small>
                            <span class="h4 fw-bold text-success mb-0"><?= htmlspecialchars($total_formateado) ?></span>
                        </div>
                    </div>

                    <!-- Instrucciones de Pago según método -->
                    <div class="alert alert-light border p-3 mb-4" style="border-radius: 10px;">
                        <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-1 text-primary"></i> Instrucciones de Pago:</h6>
                        <?php if (($pago['metodo'] ?? '') === 'transferencia'): ?>
                            <p class="mb-1 small">
                                <strong>Transferencia Bancaria Registrada:</strong> Realiza tu ingreso en la cuenta BANGE nº <code>37100-24001-XXXX</code> a nombre de GQ-Turismo indicando el código <code><?= htmlspecialchars($codigo_reserva) ?></code> en el concepto.
                            </p>
                            <small class="text-muted">Si aún no adjuntaste tu comprobante, puedes hacerlo desde <a href="mis_pedidos.php">Mis Pedidos</a>.</small>
                        <?php else: ?>
                            <p class="mb-0 small">
                                <strong>Pago en Efectivo Acordado:</strong> Abonarás la cantidad exacta de <strong><?= htmlspecialchars($total_formateado) ?></strong> directamente al guía o establecimiento al iniciar la actividad en moneda local FCFA (XAF).
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Botones de Acción Inmediata (UX-07) -->
                    <div class="d-grid gap-3 d-sm-flex justify-content-sm-between mb-4">
                        <a href="<?= htmlspecialchars($wa_url) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-lg flex-fill d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-whatsapp fs-5"></i> Contactar por WhatsApp
                        </a>
                        <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-lg flex-fill d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-printer fs-5"></i> Imprimir Comprobante
                        </button>
                    </div>

                    <div class="text-center mb-4">
                        <a href="mis_pedidos.php" class="btn btn-link text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Ir a Mis Pedidos y Reservas
                        </a>
                    </div>

                    <!-- Recordatorio de e-Visa Oficial para Turistas (VI-03, UX-07) -->
                    <div class="p-3 text-center" style="background: var(--c-selva-50, #f0f7f2); border: 1px solid var(--c-selva-100, #e3f1e9); border-radius: 10px;">
                        <span class="badge badge--confirmado mb-2">Trámite de Entrada al País</span>
                        <h6 class="fw-bold mb-1" style="color: var(--c-selva-900);">¿Vienes desde fuera de Guinea Ecuatorial?</h6>
                        <p class="small text-muted mb-3">Recuerda que para abordar tu vuelo y disfrutar de tus servicios necesitas tu visado electrónico aprobado.</p>
                        <?= boton_evisa('es', 'btn btn--primary btn-sm') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
