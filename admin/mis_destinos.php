<?php
session_start();
require_once '../includes/db_connect.php';

// Verificar autenticación
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['guia', 'agencia', 'local'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Determinar las tablas según el tipo de usuario
$tablas = [
    'guia' => [
        'proveedor' => 'guias_turisticos',
        'relacion' => 'guia_destinos',
        'id_campo' => 'id_guia'
    ],
    'agencia' => [
        'proveedor' => 'agencias',
        'relacion' => 'agencia_destinos',
        'id_campo' => 'id_agencia'
    ],
    'local' => [
        'proveedor' => 'lugares_locales',
        'relacion' => 'local_destinos',
        'id_campo' => 'id_local'
    ]
];

$config = $tablas[$user_type];

// Obtener ID del proveedor
$stmt = $conn->prepare("SELECT id FROM {$config['proveedor']} WHERE id_usuario = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$proveedor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$proveedor) {
    die("Error: No se encontró el perfil del proveedor");
}

$id_proveedor = $proveedor['id'];

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'agregar') {
            $id_destino = $_POST['id_destino'];
            $tarifa_especial = !empty($_POST['tarifa_especial']) ? $_POST['tarifa_especial'] : null;
            $descripcion = $_POST['descripcion_servicio'];
            
            $stmt = $conn->prepare("
                INSERT INTO {$config['relacion']} ({$config['id_campo']}, id_destino, tarifa_especial, descripcion_servicio)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    tarifa_especial = VALUES(tarifa_especial),
                    descripcion_servicio = VALUES(descripcion_servicio),
                    disponible = 1
            ");
            $stmt->bind_param("iids", $id_proveedor, $id_destino, $tarifa_especial, $descripcion);
            $stmt->execute();
            $stmt->close();
            
            $mensaje = "Destino agregado correctamente";
            $tipo_mensaje = "success";
            
        } elseif ($_POST['action'] === 'eliminar') {
            $id_relacion = $_POST['id_relacion'];
            
            $stmt = $conn->prepare("DELETE FROM {$config['relacion']} WHERE id = ? AND {$config['id_campo']} = ?");
            $stmt->bind_param("ii", $id_relacion, $id_proveedor);
            $stmt->execute();
            $stmt->close();
            
            $mensaje = "Destino eliminado correctamente";
            $tipo_mensaje = "success";
            
        } elseif ($_POST['action'] === 'toggle_disponible') {
            $id_relacion = $_POST['id_relacion'];
            $disponible = $_POST['disponible'];
            
            $stmt = $conn->prepare("UPDATE {$config['relacion']} SET disponible = ? WHERE id = ? AND {$config['id_campo']} = ?");
            $stmt->bind_param("iii", $disponible, $id_relacion, $id_proveedor);
            $stmt->execute();
            $stmt->close();
            
            echo json_encode(['success' => true]);
            exit();
        }
    }
}

// Obtener destinos asignados
$stmt = $conn->prepare("
    SELECT rd.*, d.nombre, d.descripcion, d.imagen, d.ciudad, d.pais
    FROM {$config['relacion']} rd
    JOIN destinos d ON rd.id_destino = d.id
    WHERE rd.{$config['id_campo']} = ?
    ORDER BY d.nombre ASC
");
$stmt->bind_param("i", $id_proveedor);
$stmt->execute();
$destinos_asignados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener todos los destinos disponibles (que no están asignados)
$ids_asignados = array_column($destinos_asignados, 'id_destino');
$ids_string = !empty($ids_asignados) ? implode(',', $ids_asignados) : '0';

$sql = "SELECT * FROM destinos WHERE id NOT IN ($ids_string) ORDER BY nombre ASC";
$destinos_disponibles = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

$page_title = "Mis Destinos";
include 'admin_header.php';
?>

<style>
.destinos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.destino-card {
    background: white;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: all var(--transition-base);
    position: relative;
}

.destino-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
}

.destino-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.destino-content {
    padding: 1.5rem;
}

.destino-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-full);
    font-weight: 600;
    font-size: 0.875rem;
}

.badge-disponible {
    background: #d1fae5;
    color: #065f46;
}

.badge-no-disponible {
    background: #fee2e2;
    color: #991b1b;
}

.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: var(--success);
}

input:checked + .slider:before {
    transform: translateX(26px);
}

@media (max-width: 767px) {
    .destinos-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-geo-alt-fill me-2"></i>
                        Mis Destinos
                    </h1>
                    <p class="text-muted mb-0">Gestiona los destinos donde ofreces tus servicios</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarDestinoModal">
                    <i class="bi bi-plus-circle me-2"></i>Agregar Destino
                </button>
            </div>
        </div>
    </div>

    <?php if (isset($mensaje)): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
        <?= $mensaje ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Destinos</h6>
                            <h2 class="mb-0"><?= count($destinos_asignados) ?></h2>
                        </div>
                        <i class="bi bi-geo-alt-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Disponibles</h6>
                            <h2 class="mb-0">
                                <?= count(array_filter($destinos_asignados, fn($d) => $d['disponible'])) ?>
                            </h2>
                        </div>
                        <i class="bi bi-check-circle-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Disponibles para Agregar</h6>
                            <h2 class="mb-0"><?= count($destinos_disponibles) ?></h2>
                        </div>
                        <i class="bi bi-plus-circle-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Destinos Asignados -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-list-check me-2"></i>
                Destinos Asignados
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($destinos_asignados)): ?>
            <div class="text-center py-5">
                <i class="bi bi-geo-alt text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">No tienes destinos asignados</h5>
                <p class="text-muted">Comienza agregando destinos donde puedes ofrecer tus servicios</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarDestinoModal">
                    <i class="bi bi-plus-circle me-2"></i>Agregar Primer Destino
                </button>
            </div>
            <?php else: ?>
            <div class="destinos-grid">
                <?php foreach ($destinos_asignados as $destino): ?>
                <div class="destino-card">
                    <span class="destino-badge <?= $destino['disponible'] ? 'badge-disponible' : 'badge-no-disponible' ?>">
                        <?= $destino['disponible'] ? 'Disponible' : 'No disponible' ?>
                    </span>
                    
                    <?php if ($destino['imagen']): ?>
                    <img src="../<?= htmlspecialchars($destino['imagen']) ?>" 
                         alt="<?= htmlspecialchars($destino['nombre']) ?>" 
                         class="destino-img">
                    <?php else: ?>
                    <div class="destino-img bg-light d-flex align-items-center justify-content-center">
                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <?php endif; ?>
                    
                    <div class="destino-content">
                        <h5 class="mb-2"><?= htmlspecialchars($destino['nombre']) ?></h5>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-geo-alt me-1"></i>
                            <?= htmlspecialchars($destino['ciudad']) ?>, <?= htmlspecialchars($destino['pais']) ?>
                        </p>
                        
                        <?php if ($destino['tarifa_especial']): ?>
                        <p class="mb-2">
                            <strong>Tarifa:</strong>
                            <span class="text-success"><?= number_format($destino['tarifa_especial'], 2) ?> €</span>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ($destino['descripcion_servicio']): ?>
                        <p class="text-muted small mb-3">
                            <?= htmlspecialchars(substr($destino['descripcion_servicio'], 0, 100)) ?>
                            <?= strlen($destino['descripcion_servicio']) > 100 ? '...' : '' ?>
                        </p>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <label class="switch mb-0">
                                    <input type="checkbox" 
                                           <?= $destino['disponible'] ? 'checked' : '' ?>
                                           onchange="toggleDisponible(<?= $destino['id'] ?>, this.checked)">
                                    <span class="slider"></span>
                                </label>
                                <small class="text-muted">Disponible</small>
                            </div>
                            
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="confirmarEliminar(<?= $destino['id'] ?>, '<?= htmlspecialchars($destino['nombre']) ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Agregar Destino -->
<div class="modal fade" id="agregarDestinoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    Agregar Destino
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="agregar">
                <div class="modal-body">
                    <?php if (empty($destinos_disponibles)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No hay más destinos disponibles para agregar. El administrador debe crear nuevos destinos.
                    </div>
                    <?php else: ?>
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Destino *</label>
                        <select name="id_destino" class="form-select" required>
                            <option value="">-- Seleccione un destino --</option>
                            <?php foreach ($destinos_disponibles as $destino): ?>
                            <option value="<?= $destino['id'] ?>">
                                <?= htmlspecialchars($destino['nombre']) ?> - 
                                <?= htmlspecialchars($destino['ciudad']) ?>, <?= htmlspecialchars($destino['pais']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tarifa Especial (Opcional)</label>
                        <div class="input-group">
                            <input type="number" name="tarifa_especial" class="form-control" 
                                   step="0.01" min="0" placeholder="0.00">
                            <span class="input-group-text">€</span>
                        </div>
                        <small class="text-muted">Deja en blanco si usas tu tarifa estándar</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción del Servicio</label>
                        <textarea name="descripcion_servicio" class="form-control" rows="4" 
                                  placeholder="Describe los servicios que ofreces en este destino..."></textarea>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <?php if (!empty($destinos_disponibles)): ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Agregar Destino
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form oculto para eliminar -->
<form id="formEliminar" method="POST" style="display: none;">
    <input type="hidden" name="action" value="eliminar">
    <input type="hidden" name="id_relacion" id="eliminar_id">
</form>

<script>
function toggleDisponible(id, disponible) {
    fetch('mis_destinos.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=toggle_disponible&id_relacion=${id}&disponible=${disponible ? 1 : 0}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al actualizar disponibilidad');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar disponibilidad');
    });
}

function confirmarEliminar(id, nombre) {
    if (confirm(`¿Estás seguro de eliminar "${nombre}" de tus destinos?`)) {
        document.getElementById('eliminar_id').value = id;
        document.getElementById('formEliminar').submit();
    }
}
</script>

<?php include 'admin_footer.php'; ?>
