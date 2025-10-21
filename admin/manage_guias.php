<?php
session_start();

// Verificar si el usuario ha iniciado sesión y tiene el tipo de usuario correcto
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['guia', 'super_admin'])) {
    header("Location: login.php"); // Redirigir a la página de login si no está autorizado
    exit();
}

include '../includes/db_connect.php';

$message = '';
$edit_guia = null;
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$guia_id = null;

// Obtener el ID del guía del usuario actual si es un guía
if ($user_type === 'guia') {
    $stmt = $conn->prepare("SELECT id FROM guias_turisticos WHERE id_usuario = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $guia_id = $result->fetch_assoc()['id'];
    }
    $stmt->close();
}

// Lógica para añadir/editar información de guía
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'guia_info') {
    $nombre_guia = $_POST['nombre_guia'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $especialidades = $_POST['especialidades'] ?? '';
    $precio_hora = $_POST['precio_hora'] ?? 0.00;
    $contacto_email = $_POST['contacto_email'] ?? '';
    $contacto_telefono = $_POST['contacto_telefono'] ?? '';
    $posted_guia_id = $_POST['guia_id'] ?? null;

    if (empty($nombre_guia) || empty($contacto_email) || empty($precio_hora)) {
        $message = "<div class='alert alert-danger'>El nombre del guía, el email de contacto y el precio por hora son obligatorios.</div>";
    } else {
        if ($posted_guia_id) {
            // Editar guía existente
            if ($user_type === 'super_admin' || ($user_type === 'guia' && $posted_guia_id == $guia_id)) {
                $stmt = $conn->prepare("UPDATE guias_turisticos SET nombre_guia = ?, descripcion = ?, especialidades = ?, precio_hora = ?, contacto_email = ?, contacto_telefono = ? WHERE id = ?");
                $stmt->bind_param("sssdssi", $nombre_guia, $descripcion, $especialidades, $precio_hora, $contacto_email, $contacto_telefono, $posted_guia_id);
                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success'>Información del guía actualizada con éxito.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error al actualizar el guía: " . $stmt->error . "</div>";
                }
                $stmt->close();
            } else {
                $message = "<div class='alert alert-danger'>No tienes permiso para editar este guía.</div>";
            }
        } else {
            // Añadir nuevo guía (solo si el usuario es guía y no tiene ya uno registrado)
            if ($user_type === 'guia') {
                $check_stmt = $conn->prepare("SELECT id FROM guias_turisticos WHERE id_usuario = ?");
                $check_stmt->bind_param("i", $user_id);
                $check_stmt->execute();
                $check_stmt->store_result();
                if ($check_stmt->num_rows > 0) {
                    $message = "<div class='alert alert-warning'>Ya tienes un perfil de guía registrado. Edita el existente.</div>";
                } else {
                    $stmt = $conn->prepare("INSERT INTO guias_turisticos (id_usuario, nombre_guia, descripcion, especialidades, precio_hora, contacto_email, contacto_telefono) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssdss", $user_id, $nombre_guia, $descripcion, $especialidades, $precio_hora, $contacto_email, $contacto_telefono);
                    if ($stmt->execute()) {
                        $message = "<div class='alert alert-success'>Perfil de guía registrado con éxito.</div>";
                        $guia_id = $stmt->insert_id; // Actualizar guia_id para la sesión actual
                    } else {
                        $message = "<div class='alert alert-danger'>Error al registrar el perfil de guía: " . $stmt->error . "</div>";
                    }
                    $stmt->close();
                }
                $check_stmt->close();
            } else {
                $message = "<div class='alert alert alert-danger'>Solo los guías pueden registrar nuevos perfiles de guía.</div>";
            }
        }
    }
}

// Lógica para añadir/editar imagen de guía
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'add_edit_image') {
    $image_description = $_POST['image_description'] ?? '';
    $image_id = $_POST['image_id'] ?? null;
    $image_file = $_FILES['image_file'] ?? null;

    if ($guia_id) {
        $target_dir = "../assets/img/guias/"; // Directorio para imágenes de guías
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
                        $stmt = $conn->prepare("UPDATE imagenes_guia SET ruta_imagen = ?, descripcion = ? WHERE id = ? AND id_guia = ?");
                        $stmt->bind_param("ssii", $image_name, $image_description, $image_id, $guia_id);
                    } else {
                        // Añadir nueva imagen
                        $stmt = $conn->prepare("INSERT INTO imagenes_guia (id_guia, ruta_imagen, descripcion) VALUES (?, ?, ?)");
                        $stmt->bind_param("iss", $guia_id, $image_name, $image_description);
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
            $stmt = $conn->prepare("UPDATE imagenes_guia SET descripcion = ? WHERE id = ? AND id_guia = ?");
            $stmt->bind_param("sii", $image_description, $image_id, $guia_id);
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
        $message = "<div class='alert alert-danger'>Primero debes registrar tu perfil de guía para añadir imágenes.</div>";
    }
}

// Lógica para eliminar imagen de guía
if (isset($_GET['action']) && $_GET['action'] == 'delete_image' && isset($_GET['id'])) {
    $image_to_delete_id = $_GET['id'];
    if ($user_type === 'super_admin') {
        $stmt = $conn->prepare("SELECT ruta_imagen FROM imagenes_guia WHERE id = ?");
        $stmt->bind_param("i", $image_to_delete_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $image_data = $result->fetch_assoc();
        $stmt->close();

        if ($image_data) {
            $file_path = "../assets/img/guias/" . $image_data['ruta_imagen'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $stmt = $conn->prepare("DELETE FROM imagenes_guia WHERE id = ?");
            $stmt->bind_param("i", $image_to_delete_id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Imagen eliminada con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al eliminar la imagen: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } else if ($user_type === 'guia' && $guia_id) {
        $stmt = $conn->prepare("SELECT ruta_imagen FROM imagenes_guia WHERE id = ? AND id_guia = ?");
        $stmt->bind_param("ii", $image_to_delete_id, $guia_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $image_data = $result->fetch_assoc();
        $stmt->close();

        if ($image_data) {
            $file_path = "../assets/img/guias/" . $image_data['ruta_imagen'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $stmt = $conn->prepare("DELETE FROM imagenes_guia WHERE id = ? AND id_guia = ?");
            $stmt->bind_param("ii", $image_to_delete_id, $guia_id);
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

// Lógica para añadir/editar servicio de guía
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'add_edit_service') {
    $service_name = $_POST['service_name'] ?? '';
    $service_description = $_POST['service_description'] ?? '';
    $service_price = $_POST['service_price'] ?? 0.00;
    $service_id = $_POST['service_id'] ?? null;

    if (empty($service_name) || empty($service_price)) {
        $message = "<div class='alert alert-danger'>El nombre y el precio del servicio son obligatorios.</div>";
    } else if ($guia_id) {
        if ($service_id) {
            // Editar servicio existente
            $stmt = $conn->prepare("UPDATE servicios_guia SET nombre_servicio = ?, descripcion = ?, precio = ? WHERE id = ? AND id_guia = ?");
            $stmt->bind_param("ssdii", $service_name, $service_description, $service_price, $service_id, $guia_id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Servicio actualizado con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al actualizar el servicio: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            // Añadir nuevo servicio
            $stmt = $conn->prepare("INSERT INTO servicios_guia (id_guia, nombre_servicio, descripcion, precio) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issd", $guia_id, $service_name, $service_description, $service_price);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Servicio añadido con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al añadir el servicio: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } else {
        $message = "<div class='alert alert-danger'>Primero debes registrar tu perfil de guía para añadir servicios.</div>";
    }
}

// Lógica para eliminar servicio de guía
if (isset($_GET['action']) && $_GET['action'] == 'delete_service' && isset($_GET['id'])) {
    $service_to_delete_id = $_GET['id'];
    if ($user_type === 'super_admin') {
        $stmt = $conn->prepare("DELETE FROM servicios_guia WHERE id = ?");
        $stmt->bind_param("i", $service_to_delete_id);
    } else if ($user_type === 'guia' && $guia_id) {
        $stmt = $conn->prepare("DELETE FROM servicios_guia WHERE id = ? AND id_guia = ?");
        $stmt->bind_param("ii", $service_to_delete_id, $guia_id);
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

// Obtener datos del guía para edición o listado
$guias = [];
$guia_images = [];
$guia_services = [];

if ($user_type === 'super_admin') {
    $query = "SELECT g.id, g.nombre_guia, g.descripcion, g.especialidades, g.precio_hora, g.contacto_email, g.contacto_telefono, u.nombre as usuario_nombre FROM guias_turisticos g JOIN usuarios u ON g.id_usuario = u.id ORDER BY g.nombre_guia ASC";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $guias[] = $row;
        }
    }
} else if ($user_type === 'guia') {
    $query = "SELECT id, nombre_guia, descripcion, especialidades, precio_hora, contacto_email, contacto_telefono FROM guias_turisticos WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $edit_guia = $result->fetch_assoc(); // El guía solo puede editar su propia información
        $guia_id = $edit_guia['id']; // Asegurarse de que guia_id esté disponible

        // Obtener imágenes del guía
        $stmt_images = $conn->prepare("SELECT id, ruta_imagen, descripcion FROM imagenes_guia WHERE id_guia = ? ORDER BY fecha_subida DESC");
        $stmt_images->bind_param("i", $guia_id);
        $stmt_images->execute();
        $result_images = $stmt_images->get_result();
        while ($row = $result_images->fetch_assoc()) {
            $guia_images[] = $row;
        }
        $stmt_images->close();

        // Obtener servicios del guía
        $stmt_services = $conn->prepare("SELECT id, nombre_servicio, descripcion, precio FROM servicios_guia WHERE id_guia = ? ORDER BY nombre_servicio ASC");
        $stmt_services->bind_param("i", $guia_id);
        $stmt_services->execute();
        $result_services = $stmt_services->get_result();
        while ($row = $result_services->fetch_assoc()) {
            $guia_services[] = $row;
        }
        $stmt_services->close();
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
    <title>Gestionar Guías Turísticos - Admin</title>
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
                            <a class="nav-link active" aria-current="page" href="manage_guias.php">Guías</a>
                        </li>
                        <?php endif; ?>
                        <?php if (in_array($user_type, ['local', 'super_admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_locales.php">
                                Locales
                            </a>
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
                    <h1 class="h2">Gestionar Guías Turísticos</h1>
                </div>

                <?php if ($message): ?>
                    <?= $message ?>
                <?php endif; ?>

                <?php if ($user_type === 'guia'): ?>
                    <?php if (!$edit_guia): ?>
                        <h2>Registrar tu Perfil de Guía</h2>
                        <form action="manage_guias.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="guia_info">
                            <div class="mb-3">
                                <label for="nombre_guia" class="form-label">Tu Nombre como Guía</label>
                                <input type="text" class="form-control" id="nombre_guia" name="nombre_guia" required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="especialidades" class="form-label">Especialidades (separadas por coma)</label>
                                <input type="text" class="form-control" id="especialidades" name="especialidades">
                            </div>
                            <div class="mb-3">
                                <label for="precio_hora" class="form-label">Precio por Hora (€)</label>
                                <input type="number" step="0.01" class="form-control" id="precio_hora" name="precio_hora" required>
                            </div>
                            <div class="mb-3">
                                <label for="contacto_email" class="form-label">Email de Contacto</label>
                                <input type="email" class="form-control" id="contacto_email" name="contacto_email" required>
                            </div>
                            <div class="mb-3">
                                <label for="contacto_telefono" class="form-label">Teléfono de Contacto</label>
                                <input type="text" class="form-control" id="contacto_telefono" name="contacto_telefono">
                            </div>
                            <button type="submit" class="btn btn-primary">Registrar Perfil de Guía</button>
                        </form>
                    <?php else: // Guía ya registrado, mostrar formulario de edición y gestión de imágenes/servicios ?>
                        <h2>Editar Perfil de Guía</h2>
                        <form action="manage_guias.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="guia_info">
                            <input type="hidden" name="guia_id" value="<?= htmlspecialchars($edit_guia['id']) ?>">
                            <div class="mb-3">
                                <label for="nombre_guia" class="form-label">Tu Nombre como Guía</label>
                                <input type="text" class="form-control" id="nombre_guia" name="nombre_guia" value="<?= htmlspecialchars($edit_guia['nombre_guia']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($edit_guia['descripcion']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="especialidades" class="form-label">Especialidades (separadas por coma)</label>
                                <input type="text" class="form-control" id="especialidades" name="especialidades" value="<?= htmlspecialchars($edit_guia['especialidades']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="precio_hora" class="form-label">Precio por Hora (€)</label>
                                <input type="number" step="0.01" class="form-control" id="precio_hora" name="precio_hora" value="<?= htmlspecialchars($edit_guia['precio_hora']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="contacto_email" class="form-label">Email de Contacto</label>
                                <input type="email" class="form-control" id="contacto_email" name="contacto_email" value="<?= htmlspecialchars($edit_guia['contacto_email']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="contacto_telefono" class="form-label">Teléfono de Contacto</label>
                                <input type="text" class="form-control" id="contacto_telefono" name="contacto_telefono" value="<?= htmlspecialchars($edit_guia['contacto_telefono']) ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Actualizar Perfil de Guía</button>
                        </form>

                        <h3 class="mt-5">Gestionar Imágenes</h3>
                        <?php if ($guia_id): ?>
                            <form action="manage_guias.php" method="POST" enctype="multipart/form-data" class="mb-4">
                                <input type="hidden" name="form_type" value="add_edit_image">
                                <input type="hidden" name="image_id" value="">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-5">
                                        <label for="image_file" class="form-label">Archivo de Imagen</label>
                                        <input type="file" class="form-control" id="image_file" name="image_file" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="image_description" class="form-label">Descripción de la Imagen</label>
                                        <input type="text" class="form-control" id="image_description" name="image_description" placeholder="Ej: Guía en la playa de Ureka">
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
                                        <?php if (count($guia_images) > 0): ?>
                                            <?php foreach ($guia_images as $image): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($image['id']) ?></td>
                                                    <td><img src="../assets/img/guias/<?= htmlspecialchars($image['ruta_imagen']) ?>" alt="<?= htmlspecialchars($image['descripcion']) ?>" width="100"></td>
                                                    <td><?= htmlspecialchars($image['descripcion']) ?></td>
                                                    <td>
                                                        <a href="manage_guias.php?action=delete_image&id=<?= htmlspecialchars($image['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar esta imagen?');">Eliminar</a>
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
                            <form action="manage_guias.php" method="POST" class="mb-4">
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
                                        <?php if (count($guia_services) > 0): ?>
                                            <?php foreach ($guia_services as $service): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($service['id']) ?></td>
                                                    <td><?= htmlspecialchars($service['nombre_servicio']) ?></td>
                                                    <td><?= htmlspecialchars($service['descripcion']) ?></td>
                                                    <td><?= htmlspecialchars(number_format($service['precio'], 2)) ?> €</td>
                                                    <td>
                                                        <a href="manage_guias.php?action=delete_service&id=<?= htmlspecialchars($service['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este servicio?');">Eliminar</a>
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
                        <?php else: ?>
                            <div class="alert alert-info">Registra tu perfil de guía para gestionar imágenes y servicios.</div>
                        <?php endif; ?>

                        <h3 class="mt-5">Pedidos Recibidos</h3>
                        <?php if ($guia_id): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID Pedido</th>
                                            <th>Servicio</th>
                                            <th>Turista</th>
                                            <th>Fecha Servicio</th>
                                            <th>Horas</th>
                                            <th>Precio Total</th>
                                            <th>Estado</th>
                                            <th>Itinerario</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($guia_orders) > 0): ?>
                                            <?php foreach ($guia_orders as $order): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($order['id']) ?></td>
                                                    <td><?= htmlspecialchars($order['item_name']) ?></td>
                                                    <td><?= htmlspecialchars($order['turista_nombre']) ?></td>
                                                    <td><?= htmlspecialchars($order['fecha_servicio']) ?></td>
                                                    <td><?= htmlspecialchars($order['cantidad_personas']) ?></td>
                                                    <td><?= htmlspecialchars(number_format($order['precio_total'], 2)) ?> €</td>
                                                    <td>
                                                        <form action="manage_guias.php" method="POST" style="display:inline;">
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
                                                        <?php if ($order['id_itinerario']): ?>
                                                            <a href="../itinerario.php?id=<?= htmlspecialchars($order['id_itinerario']) ?>" target="_blank" class="btn btn-info btn-sm">Ver Itinerario</a>
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
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
                            <div class="alert alert-info">Registra tu perfil de guía para ver los pedidos.</div>
                        <?php endif; ?>

                    <?php endif; // Fin de la lógica para usuario tipo guia ?>

                <?php if ($user_type === 'super_admin'): ?>
                    <h2 class="mt-5">Listado de Guías Turísticos</h2>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Nombre Guía</th>
                                    <th>Especialidades</th>
                                    <th>Precio/Hora</th>
                                    <th>Email Contacto</th>
                                    <th>Teléfono Contacto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($guias) > 0): ?>
                                    <?php foreach ($guias as $guia): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($guia['id']) ?></td>
                                            <td><?= htmlspecialchars($guia['usuario_nombre']) ?></td>
                                            <td><?= htmlspecialchars($guia['nombre_guia']) ?></td>
                                            <td><?= htmlspecialchars($guia['especialidades']) ?></td>
                                            <td><?= htmlspecialchars(number_format($guia['precio_hora'], 2)) ?> €</td>
                                            <td><?= htmlspecialchars($guia['contacto_email']) ?></td>
                                            <td><?= htmlspecialchars($guia['contacto_telefono']) ?></td>
                                            <td>
                                                <a href="manage_guias.php?action=delete&id=<?= htmlspecialchars($guia['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar a este guía?');">Eliminar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8">No hay guías turísticos registrados.</td>
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
