<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión y es un super_admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'super_admin') {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

$message = '';
$user_type = $_SESSION['user_type'];

// Lógica para añadir/editar publicidad
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'publicidad') {
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $enlace = $_POST['enlace'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;
    $activo = isset($_POST['activo']) ? 1 : 0;
    $publicidad_id = $_POST['publicidad_id'] ?? null;

    $imagen_name = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/img/publicidad/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $imagen_name = uniqid() . "_" . basename($_FILES['imagen']['name']);
        $target_file = $target_dir . $imagen_name;
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
            $message = "<div class='alert alert-danger'>Error al subir la imagen de publicidad.</div>";
            $imagen_name = null;
        }
    }

    if (empty($message)) {
        if ($publicidad_id) {
            // Editar publicidad
            if ($imagen_name) {
                $stmt = $conn->prepare("UPDATE publicidades SET titulo = ?, descripcion = ?, imagen = ?, enlace = ?, fecha_inicio = ?, fecha_fin = ?, activo = ? WHERE id = ?");
                $stmt->bind_param("ssssssii", $titulo, $descripcion, $imagen_name, $enlace, $fecha_inicio, $fecha_fin, $activo, $publicidad_id);
            } else {
                $stmt = $conn->prepare("UPDATE publicidades SET titulo = ?, descripcion = ?, enlace = ?, fecha_inicio = ?, fecha_fin = ?, activo = ? WHERE id = ?");
                $stmt->bind_param("sssssii", $titulo, $descripcion, $enlace, $fecha_inicio, $fecha_fin, $activo, $publicidad_id);
            }
        } else {
            // Añadir publicidad
            if ($imagen_name) {
                $stmt = $conn->prepare("INSERT INTO publicidades (titulo, descripcion, imagen, enlace, fecha_inicio, fecha_fin, activo) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssi", $titulo, $descripcion, $imagen_name, $enlace, $fecha_inicio, $fecha_fin, $activo);
            } else {
                $message = "<div class='alert alert-danger'>La imagen es obligatoria para una nueva publicidad.</div>";
            }
        }

        if (isset($stmt)) {
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Publicidad guardada con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle me-2'></i>Error al guardar publicidad: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

// Lógica para eliminar publicidad
if (isset($_GET['action']) && $_GET['action'] == 'delete_publicidad' && isset($_GET['id'])) {
    $publicidad_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM publicidades WHERE id = ?");
    $stmt->bind_param("i", $publicidad_id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Publicidad eliminada con éxito.</div>";
    } else {
        $message = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle me-2'></i>Error al eliminar publicidad: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Lógica para añadir/editar carousel
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'carousel') {
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $enlace = $_POST['link'] ?? '';
    $orden = $_POST['orden'] ?? 0;
    $activo = isset($_POST['activo']) ? 1 : 0;
    $carousel_id = $_POST['carousel_id'] ?? null;

    $imagen_name = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/img/carouseles/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $imagen_name = uniqid() . "_" . basename($_FILES['imagen']['name']);
        $target_file = $target_dir . $imagen_name;
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
            $message = "<div class='alert alert-danger'>Error al subir la imagen del carousel.</div>";
            $imagen_name = null;
        }
    }

    if (empty($message)) {
        if ($carousel_id) {
            // Editar carousel
            if ($imagen_name) {
                $stmt = $conn->prepare("UPDATE publicidad_carousel SET titulo = ?, descripcion = ?, imagen = ?, link = ?, orden = ?, activo = ? WHERE id = ?");
                $stmt->bind_param("sssiii", $titulo, $descripcion, $imagen_name, $enlace, $orden, $activo, $carousel_id);
            } else {
                $stmt = $conn->prepare("UPDATE publicidad_carousel SET titulo = ?, descripcion = ?, link = ?, orden = ?, activo = ? WHERE id = ?");
                $stmt->bind_param("sssiii", $titulo, $descripcion, $enlace, $orden, $activo, $carousel_id);
            }
        } else {
            // Añadir carousel
            if ($imagen_name) {
                $stmt = $conn->prepare("INSERT INTO publicidad_carousel (titulo, descripcion, imagen, link, orden, activo) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssiii", $titulo, $descripcion, $imagen_name, $enlace, $orden, $activo);
            } else {
                $message = "<div class='alert alert-danger'>La imagen es obligatoria para un nuevo carousel.</div>";
            }
        }

        if (isset($stmt)) {
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Carousel guardado con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle me-2'></i>Error al guardar carousel: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

// Lógica para eliminar carousel
if (isset($_GET['action']) && $_GET['action'] == 'delete_carousel' && isset($_GET['id'])) {
    $carousel_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM publicidad_carousel WHERE id = ?");
    $stmt->bind_param("i", $carousel_id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Carousel eliminado con éxito.</div>";
    } else {
        $message = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle me-2'></i>Error al eliminar carousel: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Obtener todas las publicidades
$publicidades = [];
$result_publicidades = $conn->query("SELECT * FROM publicidades ORDER BY fecha_creacion DESC");
if ($result_publicidades) {
    while ($row = $result_publicidades->fetch_assoc()) {
        $publicidades[] = $row;
    }
}

// Obtener todos los carouseles
$carouseles = [];
$result_carouseles = $conn->query("SELECT * FROM publicidad_carousel ORDER BY orden ASC");
if ($result_carouseles) {
    while ($row = $result_carouseles->fetch_assoc()) {
        $carouseles[] = $row;
    }
}

$page_title = "Gestión de Publicidad y Carrusel";
include 'admin_header.php';
?>

<style>
    .modern-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
        transition: all 0.3s ease;
    }
    
    .modern-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .section-title {
        font-size: 1.75rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
    }
    
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }
    
    .media-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        position: relative;
    }
    
    .media-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .media-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }
    
    .media-body {
        padding: 1.25rem;
    }
    
    .media-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #1f2937;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .media-description {
        font-size: 0.9rem;
        color: #6b7280;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .media-meta {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        font-size: 0.85rem;
    }
    
    .meta-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.75rem;
    }
    
    .badge-active {
        background: #d1fae5;
        color: #065f46;
    }
    
    .badge-inactive {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .media-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-modern {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }
    
    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .btn-primary-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-success-gradient {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .btn-danger-gradient {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .form-modern {
        background: #f9fafb;
        padding: 1.5rem;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
    }
    
    .form-modern .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .form-modern .form-control,
    .form-modern .form-select {
        border-radius: 8px;
        border: 1px solid #d1d5db;
        padding: 0.625rem 0.875rem;
        font-size: 0.95rem;
    }
    
    .form-modern .form-control:focus,
    .form-modern .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-check-modern {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .form-check-modern:hover {
        background: #f3f4f6;
    }
    
    .form-check-modern input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #9ca3af;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
    
    @media (max-width: 768px) {
        .modern-card {
            padding: 1.25rem;
        }
        
        .section-title {
            font-size: 1.5rem;
        }
        
        .media-grid {
            grid-template-columns: 1fr;
        }
        
        .media-actions {
            flex-direction: column;
        }
        
        .btn-modern {
            width: 100%;
            justify-content: center;
        }
        
        .section-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
    
    @media (max-width: 575px) {
        .modern-card {
            padding: 1rem;
        }
        
        .form-modern {
            padding: 1rem;
        }
        
        .media-body {
            padding: 1rem;
        }
    }
</style>

<!-- Page Header -->
<div class="modern-card">
    <div class="section-header">
        <div>
            <h1 class="section-title mb-2">
                <i class="bi bi-images me-2"></i>
                Gestión de Publicidad y Carrusel
            </h1>
            <p class="text-muted mb-0">Administra las publicidades y el carrusel de la página principal</p>
        </div>
    </div>
    
    <?php if ($message): ?>
        <?= $message ?>
    <?php endif; ?>
</div>

<!-- Publicidades Section -->
<div class="modern-card">
    <div class="section-header">
        <h2 class="section-title">
            <i class="bi bi-megaphone me-2"></i>
            Publicidades
        </h2>
        <button class="btn-modern btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#publicidadModal">
            <i class="bi bi-plus-circle"></i>
            Nueva Publicidad
        </button>
    </div>
    
    <div class="media-grid">
        <?php if (empty($publicidades)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h4>No hay publicidades</h4>
                <p>Crea tu primera publicidad usando el botón superior</p>
            </div>
        <?php else: ?>
            <?php foreach ($publicidades as $pub): ?>
                <div class="media-card">
                    <?php if ($pub['imagen']): ?>
                        <img src="../assets/img/publicidad/<?= htmlspecialchars($pub['imagen']) ?>" alt="<?= htmlspecialchars($pub['titulo']) ?>" class="media-image">
                    <?php else: ?>
                        <div class="media-image"></div>
                    <?php endif; ?>
                    
                    <div class="media-body">
                        <h3 class="media-title"><?= htmlspecialchars($pub['titulo']) ?></h3>
                        
                        <?php if ($pub['descripcion']): ?>
                            <p class="media-description"><?= htmlspecialchars($pub['descripcion']) ?></p>
                        <?php endif; ?>
                        
                        <div class="media-meta">
                            <span class="meta-badge <?= $pub['activo'] ? 'badge-active' : 'badge-inactive' ?>">
                                <i class="bi bi-<?= $pub['activo'] ? 'check-circle' : 'x-circle' ?>"></i>
                                <?= $pub['activo'] ? 'Activa' : 'Inactiva' ?>
                            </span>
                            
                            <?php if ($pub['fecha_inicio']): ?>
                                <span class="text-muted">
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('d/m/Y', strtotime($pub['fecha_inicio'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($pub['enlace']): ?>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-link-45deg"></i>
                                <a href="<?= htmlspecialchars($pub['enlace']) ?>" target="_blank" class="text-decoration-none">Ver enlace</a>
                            </p>
                        <?php endif; ?>
                        
                        <div class="media-actions">
                            <button class="btn-modern btn-success-gradient" onclick="editPublicidad(<?= $pub['id'] ?>)">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                            <a href="?action=delete_publicidad&id=<?= $pub['id'] ?>" class="btn-modern btn-danger-gradient" onclick="return confirm('¿Eliminar esta publicidad?')">
                                <i class="bi bi-trash"></i> Eliminar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Carouseles Section -->
<div class="modern-card">
    <div class="section-header">
        <h2 class="section-title">
            <i class="bi bi-collection me-2"></i>
            Carrusel Principal
        </h2>
        <button class="btn-modern btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#carouselModal">
            <i class="bi bi-plus-circle"></i>
            Nuevo Slide
        </button>
    </div>
    
    <div class="media-grid">
        <?php if (empty($carouseles)): ?>
            <div class="empty-state">
                <i class="bi bi-images"></i>
                <h4>No hay slides en el carrusel</h4>
                <p>Agrega slides para el carrusel principal</p>
            </div>
        <?php else: ?>
            <?php foreach ($carouseles as $car): ?>
                <div class="media-card">
                    <?php 
                    $imagen = $car['imagen'] ?? '';
                    if (!empty($imagen)): 
                    ?>
                        <img src="../assets/img/carouseles/<?= htmlspecialchars($imagen) ?>" alt="<?= htmlspecialchars($car['titulo'] ?? 'Carousel') ?>" class="media-image">
                    <?php else: ?>
                        <div class="media-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-image" style="font-size: 3rem; color: #ccc;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="media-body">
                        <h3 class="media-title"><?= htmlspecialchars($car['titulo'] ?? 'Sin título') ?></h3>
                        
                        <div class="media-meta">
                            <span class="meta-badge <?= $car['activo'] ? 'badge-active' : 'badge-inactive' ?>">
                                <i class="bi bi-<?= $car['activo'] ? 'check-circle' : 'x-circle' ?>"></i>
                                <?= $car['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                            
                            <span class="text-muted">
                                <i class="bi bi-sort-numeric-down"></i>
                                Orden: <?= $car['orden'] ?>
                            </span>
                        </div>
                        
                        <?php if ($car['enlace']): ?>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-link-45deg"></i>
                                <a href="<?= htmlspecialchars($car['enlace']) ?>" target="_blank" class="text-decoration-none">Ver enlace</a>
                            </p>
                        <?php endif; ?>
                        
                        <div class="media-actions">
                            <button class="btn-modern btn-success-gradient" onclick="editCarousel(<?= $car['id'] ?>)">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                            <a href="?action=delete_carousel&id=<?= $car['id'] ?>" class="btn-modern btn-danger-gradient" onclick="return confirm('¿Eliminar este slide?')">
                                <i class="bi bi-trash"></i> Eliminar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Publicidad -->
<div class="modal fade" id="publicidadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title">
                    <i class="bi bi-megaphone me-2"></i>
                    Agregar/Editar Publicidad
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="" method="POST" enctype="multipart/form-data" class="form-modern">
                    <input type="hidden" name="form_type" value="publicidad">
                    <input type="hidden" name="publicidad_id" id="publicidad_id">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">
                                <i class="bi bi-type me-1"></i> Título *
                            </label>
                            <input type="text" name="titulo" id="pub_titulo" class="form-control" required placeholder="Título de la publicidad">
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">
                                <i class="bi bi-textarea-t me-1"></i> Descripción
                            </label>
                            <textarea name="descripcion" id="pub_descripcion" class="form-control" rows="3" placeholder="Descripción de la publicidad"></textarea>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">
                                <i class="bi bi-image me-1"></i> Imagen *
                            </label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                            <small class="text-muted">Formatos: JPG, PNG, GIF (Max: 5MB)</small>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">
                                <i class="bi bi-link-45deg me-1"></i> Enlace
                            </label>
                            <input type="url" name="enlace" id="pub_enlace" class="form-control" placeholder="https://ejemplo.com">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-calendar-event me-1"></i> Fecha Inicio
                            </label>
                            <input type="date" name="fecha_inicio" id="pub_fecha_inicio" class="form-control">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-calendar-x me-1"></i> Fecha Fin
                            </label>
                            <input type="date" name="fecha_fin" id="pub_fecha_fin" class="form-control">
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-check-modern">
                                <input type="checkbox" name="activo" id="pub_activo" class="form-check-input" checked>
                                <label class="form-check-label">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    Publicidad activa
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn-modern btn-primary-gradient">
                            <i class="bi bi-save me-1"></i> Guardar Publicidad
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Carousel -->
<div class="modal fade" id="carouselModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title">
                    <i class="bi bi-collection me-2"></i>
                    Agregar/Editar Slide del Carrusel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="" method="POST" enctype="multipart/form-data" class="form-modern">
                    <input type="hidden" name="form_type" value="carousel">
                    <input type="hidden" name="carousel_id" id="carousel_id">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">
                                <i class="bi bi-type me-1"></i> Nombre *
                            </label>
                            <input type="text" name="nombre" id="car_nombre" class="form-control" required placeholder="Nombre del slide">
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">
                                <i class="bi bi-image me-1"></i> Imagen *
                            </label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                            <small class="text-muted">Formatos: JPG, PNG, GIF (Max: 5MB). Recomendado: 1920x1080px</small>
                        </div>
                        
                        <div class="col-md-8">
                            <label class="form-label">
                                <i class="bi bi-link-45deg me-1"></i> Enlace
                            </label>
                            <input type="url" name="enlace" id="car_enlace" class="form-control" placeholder="https://ejemplo.com">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-sort-numeric-down me-1"></i> Orden
                            </label>
                            <input type="number" name="orden" id="car_orden" class="form-control" value="0" min="0">
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-check-modern">
                                <input type="checkbox" name="activo" id="car_activo" class="form-check-input" checked>
                                <label class="form-check-label">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    Slide activo
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn-modern btn-primary-gradient">
                            <i class="bi bi-save me-1"></i> Guardar Slide
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Edit publicidad
function editPublicidad(id) {
    // Aquí deberías hacer una llamada AJAX para obtener los datos
    // Por simplicidad, mostramos el modal vacío
    document.getElementById('publicidad_id').value = id;
    new bootstrap.Modal(document.getElementById('publicidadModal')).show();
}

// Edit carousel
function editCarousel(id) {
    // Aquí deberías hacer una llamada AJAX para obtener los datos
    // Por simplicidad, mostramos el modal vacío
    document.getElementById('carousel_id').value = id;
    new bootstrap.Modal(document.getElementById('carouselModal')).show();
}

// Preview image before upload
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Podrías mostrar una preview aquí
                console.log('Image loaded:', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>

<?php
$conn->close();
include 'admin_footer.php';
?>
