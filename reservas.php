<?php
require_once 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$itinerario_id = $_GET['itinerario'] ?? null;

// Si viene un itinerario, cargar sus datos
$itinerario = null;
$destinos_itinerario = [];
$servicios_itinerario = ['guias' => [], 'agencias' => [], 'locales' => []];

if ($itinerario_id) {
    $stmt = $conn->prepare("SELECT * FROM itinerarios WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $itinerario_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $itinerario = $result->fetch_assoc();
    $stmt->close();
    
    if ($itinerario) {
        // Obtener destinos del itinerario
        $stmt = $conn->prepare("
            SELECT d.*, id.orden 
            FROM itinerario_destinos id
            JOIN destinos d ON id.id_destino = d.id
            WHERE id.id_itinerario = ?
            ORDER BY id.orden ASC
        ");
        $stmt->bind_param("i", $itinerario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $destinos_itinerario[] = $row;
        }
        $stmt->close();
        
        // Obtener guías del itinerario
        $stmt = $conn->prepare("
            SELECT g.* 
            FROM itinerario_guias ig
            JOIN guias_turisticos g ON ig.id_guia = g.id
            WHERE ig.id_itinerario = ?
        ");
        $stmt->bind_param("i", $itinerario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $servicios_itinerario['guias'][] = $row;
        }
        $stmt->close();
        
        // Obtener agencias del itinerario
        $stmt = $conn->prepare("
            SELECT a.* 
            FROM itinerario_agencias ia
            JOIN agencias a ON ia.id_agencia = a.id
            WHERE ia.id_itinerario = ?
        ");
        $stmt->bind_param("i", $itinerario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $servicios_itinerario['agencias'][] = $row;
        }
        $stmt->close();
        
        // Obtener locales del itinerario
        $stmt = $conn->prepare("
            SELECT l.* 
            FROM itinerario_locales il
            JOIN lugares_locales l ON il.id_local = l.id
            WHERE il.id_itinerario = ?
        ");
        $stmt->bind_param("i", $itinerario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $servicios_itinerario['locales'][] = $row;
        }
        $stmt->close();
    }
}

// Obtener todos los destinos disponibles
$destinos_disponibles = [];
$result = $conn->query("SELECT * FROM destinos ORDER BY nombre ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $destinos_disponibles[] = $row;
    }
}
?>

<style>
.hero-reserva {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4rem 0;
    margin-bottom: 3rem;
}

.service-item {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    background: white;
}

.service-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.summary-box {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 2rem;
    position: sticky;
    top: 100px;
}

.destino-item {
    border-left: 3px solid #667eea;
    padding-left: 1rem;
    margin-bottom: 1rem;
}
</style>

<div class="hero-reserva">
    <div class="container">
        <h1 class="display-4 fw-bold"><i class="bi bi-calendar-check me-3"></i>Reservar Itinerario</h1>
        <p class="lead">Confirma tu reserva y los proveedores recibirán la notificación</p>
    </div>
</div>

<div class="container mb-5">
    <?php if ($itinerario): ?>
    <form id="reservaForm" method="POST">
        <input type="hidden" name="itinerario_id" value="<?php echo $itinerario_id; ?>">
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Información del Itinerario -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información del Itinerario</h4>
                    </div>
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($itinerario['nombre_itinerario']); ?></h5>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>Estado:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($itinerario['estado']); ?></span></p>
                                <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($itinerario['ciudad'] ?: 'No especificada'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Fecha Inicio:</strong> <?php echo $itinerario['fecha_inicio'] ?: 'No definida'; ?></p>
                                <p><strong>Fecha Fin:</strong> <?php echo $itinerario['fecha_fin'] ?: 'No definida'; ?></p>
                            </div>
                        </div>
                        <?php if ($itinerario['notas']): ?>
                        <div class="alert alert-info mt-3">
                            <strong>Notas:</strong> <?php echo nl2br(htmlspecialchars($itinerario['notas'])); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Destinos -->
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Destinos (<?php echo count($destinos_itinerario); ?>)</h4>
                    </div>
                    <div class="card-body">
                        <?php foreach ($destinos_itinerario as $idx => $dest): ?>
                        <div class="destino-item">
                            <h6 class="fw-bold"><?php echo ($idx + 1); ?>. <?php echo htmlspecialchars($dest['nombre']); ?></h6>
                            <p class="mb-1 text-muted"><?php echo htmlspecialchars($dest['descripcion']); ?></p>
                            <p class="mb-0"><strong>Precio:</strong> <?php echo number_format($dest['precio'], 2); ?> €</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Servicios Seleccionados -->
                <?php if (!empty($servicios_itinerario['guias']) || !empty($servicios_itinerario['agencias']) || !empty($servicios_itinerario['locales'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h4 class="mb-0"><i class="bi bi-briefcase me-2"></i>Servicios Incluidos</h4>
                    </div>
                    <div class="card-body">
                        <!-- Guías -->
                        <?php if (!empty($servicios_itinerario['guias'])): ?>
                        <h5 class="mb-3"><i class="bi bi-person-badge me-2"></i>Guías Turísticos</h5>
                        <?php foreach ($servicios_itinerario['guias'] as $guia): ?>
                        <div class="service-item d-flex align-items-center">
                            <?php if ($guia['imagen_perfil']): ?>
                            <img src="assets/img/guias/<?php echo $guia['imagen_perfil']; ?>" alt="Guía" class="me-3">
                            <?php endif; ?>
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($guia['nombre_guia']); ?></h6>
                                <p class="mb-0 small text-muted"><?php echo htmlspecialchars($guia['especialidades']); ?></p>
                                <p class="mb-0 text-primary"><?php echo number_format($guia['precio_hora'], 2); ?> €/hora</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Agencias -->
                        <?php if (!empty($servicios_itinerario['agencias'])): ?>
                        <h5 class="mb-3 mt-4"><i class="bi bi-building me-2"></i>Agencias de Viaje</h5>
                        <?php foreach ($servicios_itinerario['agencias'] as $agencia): ?>
                        <div class="service-item d-flex align-items-center">
                            <?php if ($agencia['imagen_perfil']): ?>
                            <img src="assets/img/agencias/<?php echo $agencia['imagen_perfil']; ?>" alt="Agencia" class="me-3">
                            <?php endif; ?>
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($agencia['nombre_agencia']); ?></h6>
                                <p class="mb-0 small text-muted"><?php echo htmlspecialchars($agencia['descripcion']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Locales -->
                        <?php if (!empty($servicios_itinerario['locales'])): ?>
                        <h5 class="mb-3 mt-4"><i class="bi bi-shop me-2"></i>Locales y Restaurantes</h5>
                        <?php foreach ($servicios_itinerario['locales'] as $local): ?>
                        <div class="service-item d-flex align-items-center">
                            <?php if ($local['imagen_perfil']): ?>
                            <img src="assets/img/locales/<?php echo $local['imagen_perfil']; ?>" alt="Local" class="me-3">
                            <?php endif; ?>
                            <div>
                                <span class="badge bg-success mb-1"><?php echo htmlspecialchars($local['tipo_local']); ?></span>
                                <h6 class="mb-1"><?php echo htmlspecialchars($local['nombre_local']); ?></h6>
                                <p class="mb-0 small text-muted"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($local['direccion']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Detalles de la Reserva -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="bi bi-pencil me-2"></i>Detalles de tu Reserva</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fecha de Inicio *</label>
                                <input type="date" class="form-control" name="fecha_reserva_inicio" 
                                       value="<?php echo $itinerario['fecha_inicio']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fecha de Fin *</label>
                                <input type="date" class="form-control" name="fecha_reserva_fin" 
                                       value="<?php echo $itinerario['fecha_fin']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Número de Personas *</label>
                                <input type="number" class="form-control" name="num_personas" min="1" value="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Teléfono de Contacto *</label>
                                <input type="tel" class="form-control" name="telefono_contacto" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Comentarios Adicionales</label>
                                <textarea class="form-control" name="comentarios" rows="3" 
                                          placeholder="Agrega cualquier solicitud especial..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Resumen de Costos -->
                <div class="summary-box">
                    <h4 class="mb-4"><i class="bi bi-wallet2 me-2"></i>Resumen de Costos</h4>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Destinos:</span>
                            <strong><?php echo number_format($itinerario['precio_total'], 2); ?> €</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Presupuesto Estimado:</span>
                            <strong><?php echo number_format($itinerario['presupuesto_estimado'], 2); ?> €</strong>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <h5>Total:</h5>
                        <h5 class="text-primary"><?php echo number_format($itinerario['precio_total'] + $itinerario['presupuesto_estimado'], 2); ?> €</h5>
                    </div>
                    
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Nota:</strong> Los proveedores recibirán una notificación automática de tu reserva.
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg w-100 mb-2" id="btnReservar">
                        <i class="bi bi-check-circle me-2"></i>Confirmar Reserva
                    </button>
                    
                    <a href="itinerario.php" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </form>
    
    <?php else: ?>
    <div class="alert alert-warning text-center">
        <h4><i class="bi bi-exclamation-triangle me-2"></i>No se encontró el itinerario</h4>
        <p>Por favor, selecciona un itinerario válido desde tu lista de itinerarios.</p>
        <a href="itinerario.php" class="btn btn-primary">Ver Mis Itinerarios</a>
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('reservaForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('btnReservar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
    
    try {
        const formData = new FormData(this);
        const response = await fetch('api/reservas.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ ' + data.message + '\n\nLos proveedores han sido notificados automáticamente.');
            window.location.href = 'mis_pedidos.php';
        } else {
            alert('❌ Error: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Confirmar Reserva';
        }
    } catch (error) {
        alert('❌ Error al procesar la reserva: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Confirmar Reserva';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
