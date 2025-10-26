<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'guia') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Obtener ID del guía
$stmt = $conn->prepare("SELECT id FROM guias_turisticos WHERE id_usuario = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$guia_result = $stmt->get_result();
$guia = $guia_result->fetch_assoc();
$guia_id = $guia['id'] ?? null;
$stmt->close();

if (!$guia_id) {
    die("Error: No se encontró el perfil de guía turístico");
}

// Agregar destino al guía
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'agregar') {
    $id_destino = $_POST['id_destino'];
    $experiencia_anos = $_POST['experiencia_anos'] ?? 0;
    $descripcion = $_POST['descripcion'] ?? '';
    $idiomas = $_POST['idiomas'] ?? '';
    $tarifa_dia = $_POST['tarifa_dia'] ?? 0;
    
    // Verificar si ya existe
    $check = $conn->prepare("SELECT id FROM guia_destinos WHERE id_guia = ? AND id_destino = ?");
    $check->bind_param("ii", $guia_id, $id_destino);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $message = "<div class='alert alert-warning'><i class='bi bi-exclamation-triangle me-2'></i>Ya tienes este destino en tu lista</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO guia_destinos (id_guia, id_destino, experiencia_anos, descripcion, idiomas, tarifa_dia) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiissd", $guia_id, $id_destino, $experiencia_anos, $descripcion, $idiomas, $tarifa_dia);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Destino agregado exitosamente</div>";
        } else {
            $message = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle me-2'></i>Error al agregar destino</div>";
        }
        $stmt->close();
    }
    $check->close();
}

// Actualizar disponibilidad
if (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['id'])) {
    $gd_id = $_GET['id'];
    $stmt = $conn->prepare("UPDATE guia_destinos SET disponible = NOT disponible WHERE id = ? AND id_guia = ?");
    $stmt->bind_param("ii", $gd_id, $guia_id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Disponibilidad actualizada</div>";
    }
    $stmt->close();
}

// Eliminar destino
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $gd_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM guia_destinos WHERE id = ? AND id_guia = ?");
    $stmt->bind_param("ii", $gd_id, $guia_id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Destino eliminado de tu lista</div>";
    }
    $stmt->close();
}

// Obtener destinos del guía
$stmt = $conn->prepare("
    SELECT gd.*, d.nombre, d.descripcion as desc_destino, d.imagen, d.ciudad
    FROM guia_destinos gd
    JOIN destinos d ON gd.id_destino = d.id
    WHERE gd.id_guia = ?
    ORDER BY d.nombre ASC
");
$stmt->bind_param("i", $guia_id);
$stmt->execute();
$mis_destinos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener destinos disponibles para agregar
$stmt = $conn->prepare("
    SELECT d.* FROM destinos d
    WHERE d.id NOT IN (
        SELECT id_destino FROM guia_destinos WHERE id_guia = ?
    )
    ORDER BY d.nombre ASC
");
$stmt->bind_param("i", $guia_id);
$stmt->execute();
$destinos_disponibles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = "Mis Destinos";
include 'admin_header.php';
?>

<div class="admin-page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1><i class="bi bi-geo-alt-fill me-2"></i>Mis Destinos</h1>
            <p class="mb-0">Gestiona los destinos donde ofreces servicios de guía turístico</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDestinoModal">
            <i class="bi bi-plus-circle me-2"></i>Agregar Destino
        </button>
    </div>
</div>

<?= $message ?>

<!-- Mis Destinos -->
<div class="row g-4">
    <?php if (empty($mis_destinos)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No tienes destinos configurados. Agrega destinos donde puedas ofrecer tus servicios de guía.
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($mis_destinos as $destino): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <?php if ($destino['imagen']): ?>
                        <img src="../<?= htmlspecialchars($destino['imagen']) ?>" class="card-img-top" alt="<?= htmlspecialchars($destino['nombre']) ?>" style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?= htmlspecialchars($destino['nombre']) ?></h5>
                            <span class="badge <?= $destino['disponible'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $destino['disponible'] ? 'Disponible' : 'No disponible' ?>
                            </span>
                        </div>
                        
                        <?php if ($destino['ciudad']): ?>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($destino['ciudad']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <?php if ($destino['experiencia_anos'] > 0): ?>
                                <span class="badge bg-primary me-2">
                                    <i class="bi bi-award"></i> <?= $destino['experiencia_anos'] ?> años exp.
                                </span>
                            <?php endif; ?>
                            <?php if ($destino['tarifa_dia'] > 0): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-currency-dollar"></i> <?= number_format($destino['tarifa_dia'], 2) ?>€/día
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($destino['idiomas']): ?>
                            <p class="small mb-2">
                                <strong>Idiomas:</strong> <?= htmlspecialchars($destino['idiomas']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($destino['descripcion']): ?>
                            <p class="card-text small text-muted">
                                <?= htmlspecialchars(substr($destino['descripcion'], 0, 100)) ?>...
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100" role="group">
                            <a href="?action=toggle&id=<?= $destino['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-toggle-<?= $destino['disponible'] ? 'on' : 'off' ?>"></i>
                                <?= $destino['disponible'] ? 'Desactivar' : 'Activar' ?>
                            </a>
                            <a href="?action=eliminar&id=<?= $destino['id'] ?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('¿Eliminar este destino de tu lista?')">
                                <i class="bi bi-trash"></i> Eliminar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Agregar Destino -->
<div class="modal fade" id="addDestinoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="agregar">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Agregar Nuevo Destino</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_destino" class="form-label">Destino *</label>
                        <select name="id_destino" id="id_destino" class="form-select" required>
                            <option value="">Selecciona un destino...</option>
                            <?php foreach ($destinos_disponibles as $dest): ?>
                                <option value="<?= $dest['id'] ?>">
                                    <?= htmlspecialchars($dest['nombre']) ?> 
                                    <?= $dest['ciudad'] ? '- ' . htmlspecialchars($dest['ciudad']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Selecciona un destino donde puedas ofrecer tus servicios</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="experiencia_anos" class="form-label">Años de Experiencia</label>
                            <input type="number" name="experiencia_anos" id="experiencia_anos" 
                                   class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tarifa_dia" class="form-label">Tarifa por Día (€)</label>
                            <input type="number" name="tarifa_dia" id="tarifa_dia" 
                                   class="form-control" step="0.01" min="0" value="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="idiomas" class="form-label">Idiomas que hablas</label>
                        <input type="text" name="idiomas" id="idiomas" class="form-control" 
                               placeholder="Ej: Español, Inglés, Francés">
                        <div class="form-text">Separa los idiomas por comas</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción de tu Experiencia</label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="4" 
                                  placeholder="Describe tu experiencia y conocimientos sobre este destino..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Guardar Destino
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .card-img-top {
        height: 180px !important;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        border-radius: 0.375rem !important;
        margin-bottom: 0.5rem;
    }
}
</style>

<?php include 'admin_footer.php'; ?>
