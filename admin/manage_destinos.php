<?php
session_start();

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

    if (empty($nombre) || empty($descripcion) || empty($categoria) || empty($precio)) {
        $message = "<div class='alert alert-danger'>Todos los campos obligatorios deben ser rellenados.</div>";
    } else {
        if ($destino_id) {
            // Update existing destination
            $sql = "UPDATE destinos SET nombre = ?, descripcion = ?, categoria = ?, precio = ?, latitude = ?, longitude = ?" . (!empty($main_image_name) ? ", imagen = ?" : "") . " WHERE id = ?";
            if (!empty($main_image_name)) {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssdsssi", $nombre, $descripcion, $categoria, $precio, $latitude, $longitude, $main_image_name, $destino_id);
            } else {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssdssi", $nombre, $descripcion, $categoria, $precio, $latitude, $longitude, $destino_id);
            }
        } else {
            // Add new destination
            if (empty($main_image_name)) {
                $message = "<div class='alert alert-danger'>La imagen principal es obligatoria para un nuevo destino.</div>";
            } else {
                $sql = "INSERT INTO destinos (nombre, descripcion, categoria, imagen, precio, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssdssd", $nombre, $descripcion, $categoria, $main_image_name, $precio, $latitude, $longitude);
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
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, categoria, imagen, precio, latitude, longitude FROM destinos WHERE id = ?");
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Destinos - Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestionar Destinos</h1>
                </div>

                <?php if ($message): ?>
                    <?= $message ?>
                <?php endif; ?>

                <h2><?= $edit_destino ? 'Editar Destino' : 'Añadir Nuevo Destino' ?></h2>
                <form action="manage_destinos.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="form_type" value="destino_info">
                    <input type="hidden" name="destino_id" value="<?= htmlspecialchars($edit_destino['id'] ?? '') ?>">
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Destino</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($edit_destino['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?= htmlspecialchars($edit_destino['descripcion'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="categoria" name="categoria" value="<?= htmlspecialchars($edit_destino['categoria'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="precio" class="form-label">Precio</label>
                        <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="<?= htmlspecialchars($edit_destino['precio'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="latitude" class="form-label">Latitud</label>
                        <input type="text" class="form-control" id="latitude" name="latitude" value="<?= htmlspecialchars($edit_destino['latitude'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="longitude" class="form-label">Longitud</label>
                        <input type="text" class="form-control" id="longitude" name="longitude" value="<?= htmlspecialchars($edit_destino['longitude'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="imagen" class="form-label">Imagen Principal</label>
                        <input type="file" class="form-control" id="imagen" name="imagen">
                        <?php if ($edit_destino && $edit_destino['imagen']): ?>
                            <small class="text-muted">Imagen actual: <?= htmlspecialchars($edit_destino['imagen']) ?></small>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Destino</button>
                </form>

                <?php if ($edit_destino): // Show gallery management only if editing an existing destination ?>
                    <h3 class="mt-5">Galería de Imágenes</h3>
                    <form action="manage_destinos.php?edit_id=<?= htmlspecialchars($edit_destino['id']) ?>" method="POST" enctype="multipart/form-data" class="mb-4">
                        <input type="hidden" name="form_type" value="add_gallery_image">
                        <input type="hidden" name="gallery_destino_id" value="<?= htmlspecialchars($edit_destino['id']) ?>">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label for="gallery_imagen" class="form-label">Archivo de Imagen</label>
                                <input type="file" class="form-control" id="gallery_imagen" name="gallery_imagen" required>
                            </div>
                            <div class="col-md-5">
                                <label for="image_description" class="form-label">Descripción de la Imagen</label>
                                <input type="text" class="form-control" id="image_description" name="image_description" placeholder="Ej: Vista panorámica">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success w-100">Subir Imagen</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Imagen</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($gallery_images) > 0): ?>
                                    <?php foreach ($gallery_images as $image): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($image['id']) ?></td>
                                            <td><img src="../assets/img/destinos/<?= htmlspecialchars($image['ruta_imagen']) ?>" alt="<?= htmlspecialchars($image['descripcion']) ?>" width="100"></td>
                                            <td><?= htmlspecialchars($image['descripcion']) ?></td>
                                            <td>
                                                <a href="manage_destinos.php?action=delete_gallery_image&id=<?= htmlspecialchars($image['id']) ?>&edit_id=<?= htmlspecialchars($edit_destino['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar esta imagen?');">Eliminar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">No hay imágenes en la galería.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <h3 class="mt-5">Listado de Destinos</h3>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
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
                                        <td><?= htmlspecialchars($destino_item['id']) ?></td>
                                        <td><?= htmlspecialchars($destino_item['nombre']) ?></td>
                                        <td><?= htmlspecialchars($destino_item['categoria']) ?></td>
                                        <td><?= htmlspecialchars(number_format($destino_item['precio'], 2)) ?> €</td>
                                        <td>
                                            <a href="manage_destinos.php?edit_id=<?= htmlspecialchars($destino_item['id']) ?>" class="btn btn-info btn-sm">Editar</a>
                                            <a href="manage_destinos.php?action=delete&id=<?= htmlspecialchars($destino_item['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este destino?');">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No hay destinos registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>