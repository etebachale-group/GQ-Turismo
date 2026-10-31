<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/evisa.php';
require_once __DIR__ . '/app/Support/FileUpload.php';

use App\Support\FileUpload;

// Redirigir si el usuario no está autenticado o no es un turista
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    header('Location: index.php');
    exit;
}

$id_pedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_pedido) {
    echo "<div class='container py-5'><div class='alert alert-danger text-center'>Identificador de pedido no válido.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$id_turista = (int)$_SESSION['user_id'];
$error_msg = '';

// Obtener detalles completos del pedido
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
            END AS item_name,
            CASE ps.tipo_proveedor
                WHEN 'agencia' THEN a.nombre_agencia
                WHEN 'guia' THEN g.nombre_guia
                WHEN 'local' THEN l.nombre_local
            END AS nombre_proveedor
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
        WHERE ps.id = ? AND ps.id_turista = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_pedido, $id_turista);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pedido) {
    echo "<div class='container py-5'><div class='alert alert-warning text-center'>El pedido no existe o no te pertenece.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Procesar confirmación y selección de método de pago (UX-04, M-10)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_pago'])) {
    verify_csrf();

    $metodo = $_POST['metodo_pago'] ?? 'efectivo';
    if (!in_array($metodo, ['efectivo', 'transferencia'], true)) {
        $metodo = 'efectivo';
    }

    $comprobante_archivo = null;
    $estado_pago = ($metodo === 'efectivo') ? 'pendiente' : 'verificando';

    // Subida de comprobante si eligió transferencia y adjuntó archivo
    if ($metodo === 'transferencia' && isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = FileUpload::uploadImage($_FILES['comprobante'], __DIR__ . '/uploads/comprobantes');
        if ($uploadResult['success']) {
            $comprobante_archivo = $uploadResult['filename'];
        } else {
            $error_msg = $uploadResult['error'];
        }
    }

    if (empty($error_msg)) {
        $monto = (float)$pedido['precio_total'];
        $prefijo = ($metodo === 'efectivo') ? 'GQ-EF-' : 'GQ-TB-';
        $codigo_ref = $prefijo . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $idempotency_key = bin2hex(random_bytes(16));

        // Registrar en tabla de pagos canónica
        $stmt_ins = $conn->prepare("INSERT INTO pagos (id_pedido, id_turista, monto, moneda, metodo, estado, codigo_referencia, comprobante_ruta, idempotency_key, notas) VALUES (?, ?, ?, 'XAF', ?, ?, ?, ?, ?, ?)");
        $notas = "Registrado desde checkout por el turista";
        $stmt_ins->bind_param("iidssssss", $id_pedido, $id_turista, $monto, $metodo, $estado_pago, $codigo_ref, $comprobante_archivo, $idempotency_key, $notas);
        $stmt_ins->execute();
        $pago_id = $stmt_ins->insert_id;
        $stmt_ins->close();

        // Registrar evento de pago
        $stmt_ev = $conn->prepare("INSERT INTO pago_eventos (id_pago, evento, detalles) VALUES (?, 'checkout_seleccionado', ?)");
        $detalles_json = json_encode(['metodo' => $metodo, 'monto' => $monto, 'codigo' => $codigo_ref]);
        $stmt_ev->bind_param("is", $pago_id, $detalles_json);
        $stmt_ev->execute();
        $stmt_ev->close();

        // Redirigir a confirmación de reserva
        header("Location: confirmacion_reserva.php?id=" . $id_pedido);
        exit;
    }
}

$nombre_servicio = $pedido['item_name'] ?? $pedido['nombre_servicio'] ?? 'Servicio Seleccionado';
$nombre_proveedor = $pedido['nombre_proveedor'] ?? 'Proveedor Local';
$fecha_servicio = $pedido['fecha_servicio'] ? date('d/m/Y', strtotime($pedido['fecha_servicio'])) : 'A coordinar';
$num_personas = (int)($pedido['cantidad_personas'] ?? $pedido['numero_personas'] ?? 1);
$precio_total = (float)$pedido['precio_total'];
$precio_unitario = (float)($pedido['precio_unitario'] ?? ($precio_total / max(1, $num_personas)));
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0" style="border-radius: var(--radius-lg, 16px); overflow: hidden;">
                <!-- Header del Checkout -->
                <div class="card-header p-4 text-white" style="background: linear-gradient(135deg, var(--c-selva-900, #0a2f18), var(--c-selva-700, #134e28));">
                    <span class="badge badge--confirmado mb-2">Paso Final de Reserva</span>
                    <h2 class="h3 fw-bold mb-1" style="font-family: var(--font-display, serif);">Resumen y Checkout</h2>
                    <p class="mb-0 text-white-50 small">Verifica tu actividad y selecciona la vía de pago autorizada en Guinea Ecuatorial</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <?php if (!empty($error_msg)): ?>
                        <div class="alert alert-danger mb-4"><?= htmlspecialchars($error_msg) ?></div>
                    <?php endif; ?>

                    <!-- Resumen del Servicio -->
                    <h5 class="fw-bold mb-3" style="font-family: var(--font-display);">Resumen de la Experiencia</h5>
                    <div class="bg-light p-4 rounded mb-4" style="border: 1px solid var(--border-default, #e5e7eb);">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <span class="badge bg-secondary mb-2"><?= htmlspecialchars(ucfirst($pedido['tipo_proveedor'] ?? 'Servicio')) ?></span>
                                <h4 class="fw-bold mb-1" style="color: var(--c-selva-900);"><?= htmlspecialchars($nombre_servicio) ?></h4>
                                <p class="text-muted small mb-0"><i class="bi bi-person-badge me-1"></i> Por: <strong><?= htmlspecialchars($nombre_proveedor) ?></strong></p>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <div class="text-muted small">Fecha programada:</div>
                                <div class="fw-bold fs-5 text-dark mb-1"><i class="bi bi-calendar3 me-1"></i> <?= htmlspecialchars($fecha_servicio) ?></div>
                                <div class="small text-muted"><i class="bi bi-people me-1"></i> <?= $num_personas ?> persona(s)</div>
                            </div>
                        </div>

                        <hr class="my-3">

                        <!-- Desglose de Precios (Calculado en Servidor) -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Precio por persona:</span>
                            <span class="fw-medium"><?= format_currency($precio_unitario, 'XAF') ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Cantidad:</span>
                            <span class="fw-medium"><?= $num_personas ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="h5 fw-bold mb-0">Total a Pagar (Fijo):</span>
                            <span class="h4 fw-bold text-success mb-0"><?= format_currency($precio_total, 'XAF') ?></span>
                        </div>
                    </div>

                    <!-- Política de Cancelación (UX-04) -->
                    <div class="alert alert-success d-flex align-items-center gap-3 mb-4" style="background: var(--c-selva-50, #f0f7f2); border: 1px solid var(--c-selva-200, #c8e6d2);">
                        <i class="bi bi-shield-check text-success fs-2"></i>
                        <div>
                            <strong class="d-block text-success">Cancelación Flexible Garantizada</strong>
                            <small class="text-muted">Puedes cancelar esta reserva sin coste alguno hasta 48 horas antes del inicio programado.</small>
                        </div>
                    </div>

                    <!-- Formulario de Selección de Pago -->
                    <form action="pagar.php?id=<?= $id_pedido ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <h5 class="fw-bold mb-3" style="font-family: var(--font-display);">Selecciona el Método de Pago</h5>

                        <!-- Opción 1: Efectivo directo -->
                        <div class="form-check card p-3 mb-3 border-2" style="cursor: pointer;">
                            <div class="d-flex align-items-start gap-3">
                                <input class="form-check-input mt-2" type="radio" name="metodo_pago" id="metodo_efectivo" value="efectivo" checked onchange="toggleTransferencia(false)">
                                <label class="form-check-label w-100" for="metodo_efectivo" style="cursor: pointer;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="d-block mb-1 fs-6"><i class="bi bi-cash-coin me-1 text-success"></i> Pago en Efectivo al Proveedor</strong>
                                        <span class="badge badge--confirmado">Recomendado</span>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Abonarás el importe exacto en mano en moneda local (FCFA) a tu guía o en el establecimiento el día acordado. No requiere pago previo online.
                                    </p>
                                </label>
                            </div>
                        </div>

                        <!-- Opción 2: Transferencia Bancaria -->
                        <div class="form-check card p-3 mb-4 border-2" style="cursor: pointer;">
                            <div class="d-flex align-items-start gap-3">
                                <input class="form-check-input mt-2" type="radio" name="metodo_pago" id="metodo_transferencia" value="transferencia" onchange="toggleTransferencia(true)">
                                <label class="form-check-label w-100" for="metodo_transferencia" style="cursor: pointer;">
                                    <strong class="d-block mb-1 fs-6"><i class="bi bi-bank me-1 text-primary"></i> Transferencia Bancaria Local (BANGE / CCEI GE)</strong>
                                    <p class="text-muted small mb-0">
                                        Ingreso directo en ventanilla bancaria o por banca móvil de Guinea Ecuatorial.
                                    </p>
                                </label>
                            </div>

                            <!-- Bloque condicional con datos bancarios y comprobante -->
                            <div id="bloque_transferencia" class="mt-3 p-3 bg-light rounded d-none" style="border: 1px solid #dee2e6;">
                                <div class="small mb-3">
                                    <div class="fw-bold text-dark mb-1">Cuentas Bancarias Autorizadas en Guinea Ecuatorial:</div>
                                    <div><strong>Banco:</strong> BANGE (Banco Nacional de Guinea Ecuatorial)</div>
                                    <div><strong>Nº de Cuenta:</strong> 37100-24001-002934-88</div>
                                    <div><strong>Titular:</strong> Eteba Chale Group / GQ-Turismo S.L.</div>
                                    <div><strong>Concepto:</strong> Indicar tu Nombre completo</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold">Adjuntar foto o PDF del comprobante (opcional ahora, puedes subirlo luego):</label>
                                    <input type="file" name="comprobante" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                                    <small class="text-muted">Formatos JPG, PNG o WEBP hasta 5MB.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Botón de Confirmación -->
                        <div class="d-grid">
                            <button type="submit" name="confirmar_pago" class="btn btn-success btn-lg py-3 fw-bold shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Confirmar y Generar Reserva
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleTransferencia(isTransfer) {
    const bloque = document.getElementById('bloque_transferencia');
    if (isTransfer) {
        bloque.classList.remove('d-none');
    } else {
        bloque.classList.add('d-none');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
