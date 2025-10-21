<?php
session_start();

// Verificar si el usuario ha iniciado sesión y tiene el tipo de usuario correcto
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['local', 'super_admin'])) {
    header("Location: login.php"); // Redirigir a la página de login si no está autorizado
    exit();
}

include '../includes/db_connect.php';

$message = '';
$edit_local = null;
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$local_id = null;

// Obtener el ID del local del usuario actual si es un local
if ($user_type === 'local') {
    $stmt = $conn->prepare("SELECT id FROM lugares_locales WHERE id_usuario = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $local_id = $result->fetch_assoc()['id'];
    }
    $stmt->close();
}

// Lógica para añadir/editar información de local
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'local_info') {
    $nombre_local = $_POST['nombre_local'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $tipo_local = $_POST['tipo_local'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $contacto_email = $_POST['contacto_email'] ?? '';
    $contacto_telefono = $_POST['contacto_telefono'] ?? '';
    $posted_local_id = $_POST['local_id'] ?? null;

    if (empty($nombre_local) || empty($contacto_email) || empty($tipo_local)) {
        $message = "<div class='alert alert-danger'>El nombre del local, el tipo y el email de contacto son obligatorios.</div>";
    } else {
        if ($posted_local_id) {
            // Editar local existente
            if ($user_type === 'super_admin' || ($user_type === 'local' && $posted_local_id == $local_id)) {
                $stmt = $conn->prepare("UPDATE lugares_locales SET nombre_local = ?, descripcion = ?, tipo_local = ?, direccion = ?, contacto_email = ?, contacto_telefono = ? WHERE id = ?");
                $stmt->bind_param("ssssssi", $nombre_local, $descripcion, $tipo_local, $direccion, $contacto_email, $contacto_telefono, $posted_local_id);
                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success'>Información del local actualizada con éxito.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error al actualizar el local: " . $stmt->error . "</div>";
                }
                $stmt->close();
            } else {
                $message = "<div class='alert alert-danger'>No tienes permiso para editar este local.</div>";
            }
        } else {
            // Añadir nuevo local (solo si el usuario es local y no tiene ya uno registrado)
            if ($user_type === 'local') {
                $check_stmt = $conn->prepare("SELECT id FROM lugares_locales WHERE id_usuario = ?");
                $check_stmt->bind_param("i", $user_id);
                $check_stmt->execute();
                $check_stmt->store_result();
                if ($check_stmt->num_rows > 0) {
                    $message = "<div class='alert alert-warning'>Ya tienes un local registrado. Edita el existente.</div>";
                } else {
                    $stmt = $conn->prepare("INSERT INTO lugares_locales (id_usuario, nombre_local, descripcion, tipo_local, direccion, contacto_email, contacto_telefono) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issssss", $user_id, $nombre_local, $descripcion, $tipo_local, $direccion, $contacto_email, $contacto_telefono);
                    if ($stmt->execute()) {
                        $message = "<div class='alert alert-success'>Local registrado con éxito.</div>";
                        $local_id = $stmt->insert_id; // Actualizar local_id para la sesión actual
                    } else {
                        $message = "<div class='alert alert-danger'>Error al registrar el local: " . $stmt->error . "</div>";
                    }
                    $stmt->close();
                }
                $check_stmt->close();
            } else {
                $message = "<div class='alert alert-danger'>Solo los locales pueden registrar nuevos locales.</div>";
            }
        }
    }
}

// Lógica para añadir/editar imagen de local
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'add_edit_image') {
    $image_description = $_POST['image_description'] ?? '';
    $image_id = $_POST['image_id'] ?? null;
    $image_file = $_FILES['image_file'] ?? null;

    if ($local_id) {
        $target_dir = "../assets/img/locales/"; // Directorio para imágenes de locales
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if ($image_file && $image_file['error'] == UPLOAD_ERR_OK) {
            $image_name = uniqid() . "_" . basename($image_file['name']);
            $target_file = $target_dir . $image_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Validar tipo de archivo
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($imageFileType, $allowed_types)) {
                $message = "<div class='alert alert-danger'>Solo se permiten archivos JPG, JPEG, PNG y GIF.</div>";
            } else if ($image_file['size'] > 5000000) { // 5MB
                $message = "<div class='alert alert-danger'>La imagen es demasiado grande.</div>";
            } else {
                if (move_uploaded_file($image_file['tmp_name'], $target_file)) {
                    if ($image_id) {
                        // Editar imagen existente (solo descripción, la imagen se reemplaza si se sube una nueva)
                        $stmt = $conn->prepare("UPDATE imagenes_local SET ruta_imagen = ?, descripcion = ? WHERE id = ? AND id_local = ?");
                        $stmt->bind_param("ssii", $image_name, $image_description, $image_id, $local_id);
                    } else {
                        // Añadir nueva imagen
                        $stmt = $conn->prepare("INSERT INTO imagenes_local (id_local, ruta_imagen, descripcion) VALUES (?, ?, ?)");
                        $stmt->bind_param("iss", $local_id, $image_name, $image_description);
                    }
                    if ($stmt->execute()) {
                        $message = "<div class='alert alert-success'>Imagen guardada con éxito.</div>";
                    } else {
                        $message = "<div class='alert alert-danger'>Error al guardar la imagen: " . $stmt->error . "</div>";
                    }
                    $stmt->close();
                } else {
                    $message = "<div class='alert alert-danger'>Error al subir la imagen.</div>";
                }
            }
        } else if ($image_id && !$image_file) {
            // Solo actualizar descripción si no se sube nueva imagen
            $stmt = $conn->prepare("UPDATE imagenes_local SET descripcion = ? WHERE id = ? AND id_local = ?");
            $stmt->bind_param("sii", $image_description, $image_id, $local_id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Descripción de imagen actualizada con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al actualizar descripción de imagen: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            $message = "<div class='alert alert-danger'>No se seleccionó ninguna imagen.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Primero debes registrar tu local para añadir imágenes.</div>";
    }
}

// Lógica para eliminar imagen de local
if (isset($_GET['action']) && $_GET['action'] == 'delete_image' && isset($_GET['id'])) {
    $image_to_delete_id = $_GET['id'];
    if ($user_type === 'super_admin') {
        $stmt = $conn->prepare("SELECT ruta_imagen FROM imagenes_local WHERE id = ?");
        $stmt->bind_param("i", $image_to_delete_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $image_data = $result->fetch_assoc();
        $stmt->close();

        if ($image_data) {
            $file_path = "../assets/img/locales/" . $image_data['ruta_imagen'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $stmt = $conn->prepare("DELETE FROM imagenes_local WHERE id = ?");
            $stmt->bind_param("i", $image_to_delete_id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Imagen eliminada con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al eliminar la imagen: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } else if ($user_type === 'local' && $local_id) {
        $stmt = $conn->prepare("SELECT ruta_imagen FROM imagenes_local WHERE id = ? AND id_local = ?");
        $stmt->bind_param("ii", $image_to_delete_id, $local_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $image_data = $result->fetch_assoc();
        $stmt->close();

        if ($image_data) {
            $file_path = "../assets/img/locales/" . $image_data['ruta_imagen'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $stmt = $conn->prepare("DELETE FROM imagenes_local WHERE id = ? AND id_local = ?");
            $stmt->bind_param("ii", $image_to_delete_id, $local_id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Imagen eliminada con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al eliminar la imagen: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } else {
        $message = "<div class='alert alert-danger'>No tienes permiso para eliminar esta imagen.</div>";
    }
}

// Lógica para añadir/editar servicio de local
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'add_edit_service') {
    $service_name = $_POST['service_name'] ?? '';
    $service_description = $_POST['service_description'] ?? '';
    $service_price = $_POST['service_price'] ?? 0.00;
    $service_id = $_POST['service_id'] ?? null;

    if (empty($service_name) || empty($service_price)) {
        $message = "<div class='alert alert-danger'>El nombre y el precio del servicio son obligatorios.</div>";
    } else if ($local_id) {
        if ($service_id) {
            // Editar servicio existente
            $stmt = $conn->prepare("UPDATE servicios_local SET nombre_servicio = ?, descripcion = ?, precio = ? WHERE id = ? AND id_local = ?");
            $stmt->bind_param("ssdii", $service_name, $service_description, $service_price, $service_id, $local_id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Servicio actualizado con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al actualizar el servicio: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            // Añadir nuevo servicio
            $stmt = $conn->prepare("INSERT INTO servicios_local (id_local, nombre_servicio, descripcion, precio) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issd", $local_id, $service_name, $service_description, $service_price);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Servicio añadido con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al añadir el servicio: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } else {
        $message = "<div class='alert alert-danger'>Primero debes registrar tu local para añadir servicios.</div>";
    }
}

// Lógica para eliminar servicio de local
if (isset($_GET['action']) && $_GET['action'] == 'delete_service' && isset($_GET['id'])) {
    $service_to_delete_id = $_GET['id'];
    if ($user_type === 'super_admin') {
        $stmt = $conn->prepare("DELETE FROM servicios_local WHERE id = ?");
        $stmt->bind_param("i", $service_to_delete_id);
    } else if ($user_type === 'local' && $local_id) {
        $stmt = $conn->prepare("DELETE FROM servicios_local WHERE id = ? AND id_local = ?");
        $stmt->bind_param("ii", $service_to_delete_id, $local_id);
    } else {
        $message = "<div class='alert alert-danger'>No tienes permiso para eliminar este servicio.</div>";
        $stmt = null;
    }

    if ($stmt) {
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Servicio eliminado con éxito.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error al eliminar el servicio: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Lógica para añadir/editar menú de local
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'add_edit_menu') {
    $menu_name = $_POST['menu_name'] ?? '';
    $menu_description = $_POST['menu_description'] ?? '';
    $menu_price = $_POST['menu_price'] ?? 0.00;
    $menu_id = $_POST['menu_id'] ?? null;

    if (empty($menu_name) || empty($menu_price)) {
        $message = "<div class='alert alert-danger'>El nombre y el precio del menú son obligatorios.</div>";
    } else if ($local_id) {
        if ($menu_id) {
            // Editar menú existente
            $stmt = $conn->prepare("UPDATE menus_local SET nombre_menu = ?, descripcion = ?, precio_total = ? WHERE id = ? AND id_local = ?");
            $stmt->bind_param("ssdii", $menu_name, $menu_description, $menu_price, $menu_id, $local_id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Menú actualizado con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al actualizar el menú: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            // Añadir nuevo menú
            $stmt = $conn->prepare("INSERT INTO menus_local (id_local, nombre_menu, descripcion, precio_total) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issd", $local_id, $menu_name, $menu_description, $menu_price);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Menú añadido con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al añadir el menú: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

// Lógica para eliminar menú de local
if (isset($_GET['action']) && $_GET['action'] == 'delete_menu' && isset($_GET['id'])) {
    $menu_to_delete_id = $_GET['id'];
    if ($user_type === 'super_admin') {
        $stmt = $conn->prepare("DELETE FROM menus_local WHERE id = ?");
        $stmt->bind_param("i", $menu_to_delete_id);
    } else if ($user_type === 'local' && $local_id) {
        $stmt = $conn->prepare("DELETE FROM menus_local WHERE id = ? AND id_local = ?");
        $stmt->bind_param("ii", $menu_to_delete_id, $local_id);
    } else {
        $message = "<div class='alert alert-danger'>No tienes permiso para eliminar este menú.</div>";
        $stmt = null;
    }

    if ($stmt) {
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Menú eliminado con éxito.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error al eliminar el menú: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Obtener datos del local para edición o listado
$locales = [];
$local_images = [];
$local_services = [];
$local_menus = [];

if ($user_type === 'super_admin') {
    $query = "SELECT l.id, l.nombre_local, l.descripcion, l.tipo_local, l.direccion, l.contacto_email, l.contacto_telefono, u.nombre as usuario_nombre FROM lugares_locales l JOIN usuarios u ON l.id_usuario = u.id ORDER BY l.nombre_local ASC";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $locales[] = $row;
        }
    }
} else if ($user_type === 'local') {
    $query = "SELECT id, nombre_local, descripcion, tipo_local, direccion, contacto_email, contacto_telefono FROM lugares_locales WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $edit_local = $result->fetch_assoc(); // El local solo puede editar su propia información
        $local_id = $edit_local['id']; // Asegurarse de que local_id esté disponible

        // Obtener imágenes del local
        $stmt_images = $conn->prepare("SELECT id, ruta_imagen, descripcion FROM imagenes_local WHERE id_local = ? ORDER BY fecha_subida DESC");
        $stmt_images->bind_param("i", $local_id);
        $stmt_images->execute();
        $result_images = $stmt_images->get_result();
        while ($row = $result_images->fetch_assoc()) {
            $local_images[] = $row;
        }
        $stmt_images->close();

        // Obtener servicios del local
        $stmt_services = $conn->prepare("SELECT id, nombre_servicio, descripcion, precio FROM servicios_local WHERE id_local = ? ORDER BY nombre_servicio ASC");
        $stmt_services->bind_param("i", $local_id);
        $stmt_services->execute();
        $result_services = $stmt_services->get_result();
        while ($row = $result_services->fetch_assoc()) {
            $local_services[] = $row;
        }
        $stmt_services->close();

        // Obtener menús del local
        $stmt_menus = $conn->prepare("SELECT id, nombre_menu, descripcion, precio_total FROM menus_local WHERE id_local = ? ORDER BY nombre_menu ASC");
        $stmt_menus->bind_param("i", $local_id);
        $stmt_menus->execute();
        $result_menus = $stmt_menus->get_result();
        while ($row = $result_menus->fetch_assoc()) {
            $local_menus[] = $row;
        }
        $stmt_menus->close();
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Lugares y Locales - Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_destinos.php">Destinos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reservas.php">Reservas</a>
                        </li>
                        <?php if ($user_type === 'super_admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_users.php">Usuarios</a>
                        </li>
                        <?php endif; ?>
                        <?php if (in_array($user_type, ['agencia', 'super_admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_agencias.php">Agencias</a>
                        </li>
                        <?php endif; ?>
                        <?php if (in_array($user_type, ['guia', 'super_admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_guias.php">Guías</a>
                        </li>
                        <?php endif; ?>
                        <?php if (in_array($user_type, ['local', 'super_admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="manage_locales.php">Locales</a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestionar Lugares y Locales</h1>
                </div>

                <?php if ($message): ?>
                    <?= $message ?>
                <?php endif; ?>

                <?php if ($user_type === 'local'): ?>
                    <?php if (!$edit_local): ?>
                        <h2>Registrar tu Local</h2>
                        <form action="manage_locales.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="local_info">
                            <div class="mb-3">
                                <label for="nombre_local" class="form-label">Nombre del Local</label>
                                <input type="text" class="form-control" id="nombre_local" name="nombre_local" required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="tipo_local" class="form-label">Tipo de Local (Ej: Restaurante, Hotel, Tienda)</label>
                                <input type="text" class="form-control" id="tipo_local" name="tipo_local" required>
                            </div>
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion" name="direccion">
                            </div>
                            <div class="mb-3">
                                <label for="contacto_email" class="form-label">Email de Contacto</label>
                                <input type="email" class="form-control" id="contacto_email" name="contacto_email" required>
                            </div>
                            <div class="mb-3">
                                <label for="contacto_telefono" class="form-label">Teléfono de Contacto</label>
                                <input type="text" class="form-control" id="contacto_telefono" name="contacto_telefono">
                            </div>
                            <button type="submit" class="btn btn-primary">Registrar Local</button>
                        </form>
                    <?php else: // Local ya registrado, mostrar formulario de edición y gestión de imágenes/servicios/menús ?>
                        <h2>Editar Información del Local</h2>
                        <form action="manage_locales.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="local_info">
                            <input type="hidden" name="local_id" value="<?= htmlspecialchars($edit_local['id']) ?>">
                            <div class="mb-3">
                                <label for="nombre_local" class="form-label">Nombre del Local</label>
                                <input type="text" class="form-control" id="nombre_local" name="nombre_local" value="<?= htmlspecialchars($edit_local['nombre_local']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($edit_local['descripcion']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="tipo_local" class="form-label">Tipo de Local (Ej: Restaurante, Hotel, Tienda)</label>
                                <input type="text" class="form-control" id="tipo_local" name="tipo_local" value="<?= htmlspecialchars($edit_local['tipo_local']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" value="<?= htmlspecialchars($edit_local['direccion']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="contacto_email" class="form-label">Email de Contacto</label>
                                <input type="email" class="form-control" id="contacto_email" name="contacto_email" value="<?= htmlspecialchars($edit_local['contacto_email']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="contacto_telefono" class="form-label">Teléfono de Contacto</label>
                                <input type="text" class="form-control" id="contacto_telefono" name="contacto_telefono" value="<?= htmlspecialchars($edit_local['contacto_telefono']) ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Actualizar Local</button>
                        </form>

                        <h3 class="mt-5">Gestionar Imágenes</h3>
                        <?php if ($local_id): ?>
                            <form action="manage_locales.php" method="POST" enctype="multipart/form-data" class="mb-4">
                                <input type="hidden" name="form_type" value="add_edit_image">
                                <input type="hidden" name="image_id" value="">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-5">
                                        <label for="image_file" class="form-label">Archivo de Imagen</label>
                                        <input type="file" class="form-control" id="image_file" name="image_file" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="image_description" class="form-label">Descripción de la Imagen</label>
                                        <input type="text" class="form-control" id="image_description" name="image_description" placeholder="Ej: Fachada del restaurante">
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
                                        <?php if (count($local_images) > 0): ?>
                                            <?php foreach ($local_images as $image): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($image['id']) ?></td>
                                                    <td><img src="../assets/img/locales/<?= htmlspecialchars($image['ruta_imagen']) ?>" alt="<?= htmlspecialchars($image['descripcion']) ?>" width="100"></td>
                                                    <td><?= htmlspecialchars($image['descripcion']) ?></td>
                                                    <td>
                                                        <a href="manage_locales.php?action=delete_image&id=<?= htmlspecialchars($image['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar esta imagen?');">Eliminar</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4">No hay imágenes registradas.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="mt-5">Gestionar Servicios</h3>
                            <form action="manage_locales.php" method="POST" class="mb-4">
                                <input type="hidden" name="form_type" value="add_edit_service">
                                <input type="hidden" name="service_id" value="">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="service_name" placeholder="Nombre del Servicio" required>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="service_description" placeholder="Descripción del Servicio">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" step="0.01" class="form-control" name="service_price" placeholder="Precio" required>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="submit" class="btn btn-success w-100">Añadir</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Precio</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($local_services) > 0): ?>
                                            <?php foreach ($local_services as $service): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($service['id']) ?></td>
                                                    <td><?= htmlspecialchars($service['nombre_servicio']) ?></td>
                                                    <td><?= htmlspecialchars($service['descripcion']) ?></td>
                                                    <td><?= htmlspecialchars(number_format($service['precio'], 2)) ?> €</td>
                                                    <td>
                                                        <a href="manage_locales.php?action=delete_service&id=<?= htmlspecialchars($service['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este servicio?');">Eliminar</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5">No hay servicios registrados.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="mt-5">Gestionar Menús</h3>
                            <form action="manage_locales.php" method="POST" class="mb-4">
                                <input type="hidden" name="form_type" value="add_edit_menu">
                                <input type="hidden" name="menu_id" value="">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="menu_name" placeholder="Nombre del Menú" required>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="menu_description" placeholder="Descripción del Menú">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" step="0.01" class="form-control" name="menu_price" placeholder="Precio Total" required>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="submit" class="btn btn-success w-100">Añadir</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Precio Total</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($local_menus) > 0): ?>
                                            <?php foreach ($local_menus as $menu): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($menu['id']) ?></td>
                                                    <td><?= htmlspecialchars($menu['nombre_menu']) ?></td>
                                                    <td><?= htmlspecialchars($menu['descripcion']) ?></td>
                                                    <td><?= htmlspecialchars(number_format($menu['precio_total'], 2)) ?> €</td>
                                                    <td>
                                                        <a href="manage_locales.php?action=delete_menu&id=<?= htmlspecialchars($menu['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este menú?');">Eliminar</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5">No hay menús registrados.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">Registra tu local para gestionar imágenes, servicios y menús.</div>
                        <?php endif; ?>

                        <h3 class="mt-5">Pedidos Recibidos</h3>
                        <?php if ($local_id): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID Pedido</th>
                                            <th>Servicio/Menú</th>
                                            <th>Tipo</th>
                                            <th>Turista</th>
                                            <th>Fecha Servicio</th>
                                            <th>Personas</th>
                                            <th>Precio Total</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($local_orders) > 0): ?>
                                            <?php foreach ($local_orders as $order): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($order['id']) ?></td>
                                                    <td><?= htmlspecialchars($order['item_name']) ?></td>
                                                    <td><?= htmlspecialchars($order['tipo_item']) ?></td>
                                                    <td><?= htmlspecialchars($order['turista_nombre']) ?></td>
                                                    <td><?= htmlspecialchars($order['fecha_servicio']) ?></td>
                                                    <td><?= htmlspecialchars($order['cantidad_personas']) ?></td>
                                                    <td><?= htmlspecialchars(number_format($order['precio_total'], 2)) ?> €</td>
                                                    <td>
                                                        <form action="manage_locales.php" method="POST" style="display:inline;">
                                                            <input type="hidden" name="form_type" value="update_order_status">
                                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                            <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                                <option value="pendiente" <?= ($order['estado'] == 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                                                                <option value="confirmado" <?= ($order['estado'] == 'confirmado') ? 'selected' : '' ?>>Confirmado</option>
                                                                <option value="cancelado" <?= ($order['estado'] == 'cancelado') ? 'selected' : '' ?>>Cancelado</option>
                                                                <option value="completado" <?= ($order['estado'] == 'completado') ? 'selected' : '' ?>>Completado</option>
                                                            </select>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <!-- Acciones adicionales si son necesarias -->
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9">No hay pedidos recibidos.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">Registra tu local para ver los pedidos.</div>
                        <?php endif; ?>

                    <?php endif; // Fin de la lógica para usuario tipo local ?>

                <?php if ($user_type === 'super_admin'): ?>
                    <h2 class="mt-5">Listado de Lugares y Locales</h2>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Nombre Local</th>
                                    <th>Tipo</th>
                                    <th>Dirección</th>
                                    <th>Email Contacto</th>
                                    <th>Teléfono Contacto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($locales) > 0): ?>
                                    <?php foreach ($locales as $local): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($local['id']) ?></td>
                                            <td><?= htmlspecialchars($local['usuario_nombre']) ?></td>
                                            <td><?= htmlspecialchars($local['nombre_local']) ?></td>
                                            <td><?= htmlspecialchars($local['tipo_local']) ?></td>
                                            <td><?= htmlspecialchars($local['direccion']) ?></td>
                                            <td><?= htmlspecialchars($local['contacto_email']) ?></td>
                                            <td><?= htmlspecialchars($local['contacto_telefono']) ?></td>
                                            <td>
                                                <a href="manage_locales.php?action=delete&id=<?= htmlspecialchars($local['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este local?');">Eliminar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8">No hay lugares o locales registrados.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
