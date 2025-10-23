<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión y tiene el tipo de usuario correcto
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['super_admin'])) {
    header("Location: login.php"); // Redirigir a la página de login si no está autorizado
    exit();
}

include '../includes/db_connect.php';

$message = '';
$edit_destino = null;
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Lógica para añadir/editar destino
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'destino_info') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $precio = $_POST['precio'] ?? 0.00;
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $ciudad = $_POST['ciudad'] ?? ''; // Nuevo campo
    $destino_id = $_POST['destino_id'] ?? null;

    // Handle single image upload for main image
    $main_image_name = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/img/destinos/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $main_image_name = uniqid() . "_" . basename($_FILES['imagen']['name']);
        $target_file = $target_dir . $main_image_name;
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
            $message = "<div class='alert alert-danger'>Error al subir la imagen principal.</div>";
            $main_image_name = ''; // Reset if upload fails
        }
    }

    if (empty($nombre) || empty($descripcion) || empty($categoria) || empty($precio) || empty($ciudad)) { // Añadir $ciudad a la validación
        $message = "<div class='alert alert-danger'>Todos los campos obligatorios deben ser rellenados.</div>";
    } else {
        if ($destino_id) {
            // Update existing destination
            $sql = "UPDATE destinos SET nombre = ?, descripcion = ?, categoria = ?, precio = ?, latitude = ?, longitude = ?, ciudad = ?" . (!empty($main_image_name) ? ", imagen = ?" : "") . " WHERE id = ?";
            if (!empty($main_image_name)) {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssdssssi", $nombre, $descripcion, $categoria, $precio, $latitude, $longitude, $ciudad, $main_image_name, $destino_id);
            } else {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssdsssi", $nombre, $descripcion, $categoria, $precio, $latitude, $longitude, $ciudad, $destino_id);
            }
        } else {
            // Add new destination
            if (empty($main_image_name)) {
                $message = "<div class='alert alert-danger'>La imagen principal es obligatoria para un nuevo destino.</div>";
            } else {
                $sql = "INSERT INTO destinos (nombre, descripcion, categoria, imagen, precio, latitude, longitude, ciudad) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssdssss", $nombre, $descripcion, $categoria, $main_image_name, $precio, $latitude, $longitude, $ciudad);
            }
        }

        if (isset($stmt) && $stmt->execute()) {
            $message = "<div class='alert alert-success'>Destino guardado con éxito.</div>";
        } else if (isset($stmt)) {
            $message = "<div class='alert alert-danger'>Error al guardar el destino: " . $stmt->error . "</div>";
        }
        if (isset($stmt)) $stmt->close();
    }
}

// Lógica para eliminar destino
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $destino_to_delete_id = (int)$_GET['id'];
    
    // Primero, obtener la imagen principal y las imágenes de galería para eliminarlas
    $stmt = $conn->prepare("SELECT imagen FROM destinos WHERE id = ?");
    $stmt->bind_param("i", $destino_to_delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $destino_data = $result->fetch_assoc();
    $stmt->close();
    
    if ($destino_data) {
        // Eliminar imagen principal
        if (!empty($destino_data['imagen'])) {
            $file_path = "../assets/img/destinos/" . $destino_data['imagen'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Obtener y eliminar imágenes de galería
        $stmt_gallery = $conn->prepare("SELECT ruta_imagen FROM imagenes_destino WHERE id_destino = ?");
        $stmt_gallery->bind_param("i", $destino_to_delete_id);
        $stmt_gallery->execute();
        $result_gallery = $stmt_gallery->get_result();
        while ($gallery_img = $result_gallery->fetch_assoc()) {
            $gallery_file_path = "../assets/img/destinos/" . $gallery_img['ruta_imagen'];
            if (file_exists($gallery_file_path)) {
                unlink($gallery_file_path);
            }
        }
        $stmt_gallery->close();
        
        // Eliminar registros de imagenes_destino
        $stmt_del_gallery = $conn->prepare("DELETE FROM imagenes_destino WHERE id_destino = ?");
        $stmt_del_gallery->bind_param("i", $destino_to_delete_id);
        $stmt_del_gallery->execute();
        $stmt_del_gallery->close();
        
        // Eliminar registros de itinerario_destinos
        $stmt_del_itinerario = $conn->prepare("DELETE FROM itinerario_destinos WHERE id_destino = ?");
        $stmt_del_itinerario->bind_param("i", $destino_to_delete_id);
        $stmt_del_itinerario->execute();
        $stmt_del_itinerario->close();
        
        // Finalmente, eliminar el destino
        $stmt_del_destino = $conn->prepare("DELETE FROM destinos WHERE id = ?");
        $stmt_del_destino->bind_param("i", $destino_to_delete_id);
        
        if ($stmt_del_destino->execute()) {
            $message = "<div class='alert alert-success'><i class='bi bi-check-circle-fill me-2'></i>Destino eliminado exitosamente.</div>";
        } else {
            $message = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle-fill me-2'></i>Error al eliminar el destino: " . $stmt_del_destino->error . "</div>";
        }
        $stmt_del_destino->close();
    } else {
        $message = "<div class='alert alert-warning'><i class='bi bi-info-circle-fill me-2'></i>El destino no existe.</div>";
    }
    
    // Redirigir para limpiar la URL
    header("Location: manage_destinos.php");
    exit();
}

// Lógica para añadir imágenes a la galería
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'add_gallery_image') {
    $destino_id = $_POST['gallery_destino_id'] ?? null;
    $image_description = $_POST['image_description'] ?? '';

    if (!$destino_id) {
        $message = "<div class='alert alert-danger'>ID de destino no proporcionado para la galería.</div>";
    } else if (isset($_FILES['gallery_imagen']) && $_FILES['gallery_imagen']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/img/destinos/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $image_name = uniqid() . "_" . basename($_FILES['gallery_imagen']['name']);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES['gallery_imagen']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO imagenes_destino (id_destino, ruta_imagen, descripcion) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $destino_id, $image_name, $image_description);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Imagen de galería subida con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al guardar la imagen de galería: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            $message = "<div class='alert alert-danger'>Error al subir la imagen de galería.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>No se seleccionó ninguna imagen para la galería.</div>";
    }
}

// Lógica para eliminar imagen de galería
if (isset($_GET['action']) && $_GET['action'] == 'delete_gallery_image' && isset($_GET['id'])) {
    $image_to_delete_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT ruta_imagen FROM imagenes_destino WHERE id = ?");
    $stmt->bind_param("i", $image_to_delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $image_data = $result->fetch_assoc();
    $stmt->close();

    if ($image_data) {
        $file_path = "../assets/img/destinos/" . $image_data['ruta_imagen'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $stmt = $conn->prepare("DELETE FROM imagenes_destino WHERE id = ?");
        $stmt->bind_param("i", $image_to_delete_id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Imagen de galería eliminada con éxito.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error al eliminar la imagen de galería: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Obtener datos de destinos para edición o listado
$destinos = [];
$edit_destino_id = $_GET['edit_id'] ?? null;

if ($edit_destino_id) {
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, categoria, imagen, precio, latitude, longitude, ciudad FROM destinos WHERE id = ?");
    $stmt->bind_param("i", $edit_destino_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_destino = $result->fetch_assoc();
    $stmt->close();

    // Fetch gallery images for this destination
    $gallery_images = [];
    $stmt_gallery = $conn->prepare("SELECT id, ruta_imagen, descripcion FROM imagenes_destino WHERE id_destino = ? ORDER BY fecha_subida DESC");
    $stmt_gallery->bind_param("i", $edit_destino_id);
    $stmt_gallery->execute();
    $result_gallery = $stmt_gallery->get_result();
    while ($row = $result_gallery->fetch_assoc()) {
        $gallery_images[] = $row;
    }
    $stmt_gallery->close();

} else {
    $query = "SELECT id, nombre, descripcion, categoria, imagen, precio FROM destinos ORDER BY nombre ASC";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $destinos[] = $row;
        }
    }
}

$conn->close();

// Configurar título de página
$page_title = "Gestionar Destinos";
include 'admin_header.php';
?>

<!-- Page Header -->
<div class="admin-page-header">
    <h1><i class="bi bi-geo-alt-fill"></i> Gestionar Destinos</h1>
    <p><?= $edit_destino ? 'Editando: <strong>' . htmlspecialchars($edit_destino['nombre']) . '</strong>' : 'Administra todos los destinos turísticos de la plataforma' ?></p>
</div>

<?php if ($message): ?>
    <?= $message ?>
<?php endif; ?>

<!-- Destino Form Card -->
<div class="card">
    <div class="card-header">
        <h2>
            <i class="bi bi-<?= $edit_destino ? 'pencil-square' : 'plus-circle' ?>"></i>
            <?= $edit_destino ? 'Editar Destino' : 'Añadir Nuevo Destino' ?>
        </h2>
        <?php if ($edit_destino): ?>
            <div class="card-header-actions">
                <a href="manage_destinos.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar Edición
                </a>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form action="manage_destinos.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="form_type" value="destino_info">
            <input type="hidden" name="destino_id" value="<?= htmlspecialchars($edit_destino['id'] ?? '') ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">
                        <i class="bi bi-geo-alt"></i> Nombre del Destino
                    </label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?= htmlspecialchars($edit_destino['nombre'] ?? '') ?>" 
                           placeholder="Ej: Cascada de Moka" required>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="categoria" class="form-label">
                        <i class="bi bi-tags"></i> Categoría
                    </label>
                    <input type="text" class="form-control" id="categoria" name="categoria" 
                           value="<?= htmlspecialchars($edit_destino['categoria'] ?? '') ?>" 
                           placeholder="Ej: Naturaleza" required>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="precio" class="form-label">
                        <i class="bi bi-currency-euro"></i> Precio
                    </label>
                    <input type="number" step="0.01" class="form-control" id="precio" name="precio" 
                           value="<?= htmlspecialchars($edit_destino['precio'] ?? '') ?>" 
                           placeholder="0.00" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="descripcion" class="form-label">
                    <i class="bi bi-card-text"></i> Descripción
                </label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" 
                          placeholder="Describe brevemente el destino turístico..." required><?= htmlspecialchars($edit_destino['descripcion'] ?? '') ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="ciudad" class="form-label">
                        <i class="bi bi-building"></i> Ciudad
                    </label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad" 
                           value="<?= htmlspecialchars($edit_destino['ciudad'] ?? '') ?>" 
                           placeholder="Ej: Malabo" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="latitude" class="form-label">
                        <i class="bi bi-pin-map"></i> Latitud
                    </label>
                    <input type="text" class="form-control" id="latitude" name="latitude" 
                           value="<?= htmlspecialchars($edit_destino['latitude'] ?? '') ?>" 
                           placeholder="Ej: 3.7500">
                    <small class="text-muted">Coordenada norte-sur</small>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="longitude" class="form-label">
                        <i class="bi bi-pin-map-fill"></i> Longitud
                    </label>
                    <input type="text" class="form-control" id="longitude" name="longitude" 
                           value="<?= htmlspecialchars($edit_destino['longitude'] ?? '') ?>" 
                           placeholder="Ej: 8.7833">
                    <small class="text-muted">Coordenada este-oeste</small>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="imagen" class="form-label">
                    <i class="bi bi-image"></i> Imagen Principal
                </label>
                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                <?php if ($edit_destino && $edit_destino['imagen']): ?>
                    <div style="margin-top: var(--space-md); padding: var(--space-md); background: var(--gray-50); border-radius: var(--radius-md); display: flex; align-items: center; gap: var(--space-md);">
                        <img src="../assets/img/destinos/<?= htmlspecialchars($edit_destino['imagen']) ?>" 
                             alt="Imagen actual" 
                             style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius-md);">
                        <div>
                            <small class="text-muted">Imagen actual:</small>
                            <div><strong><?= htmlspecialchars($edit_destino['imagen']) ?></strong></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="display: flex; gap: var(--space-md); margin-top: var(--space-xl);">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> <?= $edit_destino ? 'Actualizar Destino' : 'Guardar Destino' ?>
                </button>
                <?php if ($edit_destino): ?>
                    <a href="manage_destinos.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($edit_destino): ?>
    <!-- Gallery Management Card -->
    <div class="card" style="margin-top: var(--space-xl);">
        <div class="card-header">
            <h2><i class="bi bi-images"></i> Galería de Imágenes</h2>
            <p style="margin: var(--space-sm) 0 0 0; font-size: 0.875rem; color: var(--gray-600);">
                Gestiona las imágenes adicionales del destino
            </p>
        </div>
        <div class="card-body">
            <!-- Upload Form -->
            <form action="manage_destinos.php?edit_id=<?= htmlspecialchars($edit_destino['id']) ?>" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  style="margin-bottom: var(--space-xl); padding: var(--space-lg); background: var(--gray-50); border-radius: var(--radius-md);">
                <input type="hidden" name="form_type" value="add_gallery_image">
                <input type="hidden" name="gallery_destino_id" value="<?= htmlspecialchars($edit_destino['id']) ?>">
                
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="gallery_imagen" class="form-label">
                            <i class="bi bi-image"></i> Archivo de Imagen
                        </label>
                        <input type="file" class="form-control" id="gallery_imagen" name="gallery_imagen" accept="image/*" required>
                    </div>
                    <div class="col-md-5">
                        <label for="image_description" class="form-label">
                            <i class="bi bi-card-text"></i> Descripción
                        </label>
                        <input type="text" class="form-control" id="image_description" name="image_description" 
                               placeholder="Ej: Vista panorámica">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-upload"></i> Subir
                        </button>
                    </div>
                </div>
            </form>

            <!-- Gallery Grid -->
            <?php if (count($gallery_images) > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: var(--space-lg);">
                    <?php foreach ($gallery_images as $image): ?>
                        <div class="card" style="overflow: hidden;">
                            <img src="../assets/img/destinos/<?= htmlspecialchars($image['ruta_imagen']) ?>" 
                                 alt="<?= htmlspecialchars($image['descripcion']) ?>" 
                                 style="width: 100%; height: 150px; object-fit: cover;">
                            <div class="card-body" style="padding: var(--space-md);">
                                <small style="display: block; color: var(--gray-600); margin-bottom: var(--space-sm);">
                                    ID: #<?= htmlspecialchars($image['id']) ?>
                                </small>
                                <p style="font-size: 0.875rem; margin-bottom: var(--space-md);">
                                    <?= htmlspecialchars($image['descripcion'] ?: 'Sin descripción') ?>
                                </p>
                                <a href="manage_destinos.php?action=delete_gallery_image&id=<?= htmlspecialchars($image['id']) ?>&edit_id=<?= htmlspecialchars($edit_destino['id']) ?>" 
                                   class="btn btn-sm btn-danger w-100" 
                                   onclick="return confirm('⚠️ ¿Eliminar esta imagen de la galería?');">
                                    <i class="bi bi-trash"></i> Eliminar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: var(--space-xl); color: var(--gray-500);">
                    <i class="bi bi-images" style="font-size: 3rem; color: var(--gray-400);"></i>
                    <p style="margin-top: var(--space-md);">No hay imágenes en la galería. Sube la primera imagen arriba.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Destinos List Card -->
<?php if (!$edit_destino): ?>
    <div class="card" style="margin-top: var(--space-xl);">
        <div class="card-header">
            <h2><i class="bi bi-list-ul"></i> Listado de Destinos</h2>
            <div class="card-header-actions">
                <input type="text" class="search-input" placeholder="Buscar destinos..." id="searchDestinos">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="destinosTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($destinos) > 0): ?>
                            <?php foreach ($destinos as $destino_item): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($destino_item['id']) ?></strong></td>
                                    <td>
                                        <?php if ($destino_item['imagen']): ?>
                                            <img src="../assets/img/destinos/<?= htmlspecialchars($destino_item['imagen']) ?>" 
                                                 alt="<?= htmlspecialchars($destino_item['nombre']) ?>" 
                                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-md);">
                                        <?php else: ?>
                                            <div style="width: 60px; height: 60px; background: var(--gray-200); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-image" style="color: var(--gray-400);"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($destino_item['nombre']) ?></strong>
                                        <br>
                                        <small style="color: var(--gray-600);">
                                            <?= htmlspecialchars(substr($destino_item['descripcion'], 0, 60)) ?>...
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= htmlspecialchars($destino_item['categoria']) ?>
                                        </span>
                                    </td>
                                    <td><strong><?= number_format($destino_item['precio'], 2) ?> €</strong></td>
                                    <td>
                                        <div style="display: flex; gap: var(--space-sm);">
                                            <a href="manage_destinos.php?edit_id=<?= htmlspecialchars($destino_item['id']) ?>" 
                                               class="btn btn-sm btn-primary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="manage_destinos.php?action=delete&id=<?= htmlspecialchars($destino_item['id']) ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('⚠️ ¿Eliminar el destino <?= htmlspecialchars($destino_item['nombre']) ?>? Esta acción también eliminará todas sus imágenes de galería.');"
                                               title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding: var(--space-xl);">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--gray-400);"></i>
                                    <p style="color: var(--gray-600); margin-top: var(--space-md);">
                                        No hay destinos registrados. ¡Añade el primero arriba!
                                    </p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// Búsqueda en tabla
document.getElementById('searchDestinos')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#destinosTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?php include 'admin_footer.php'; ?>