<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión y es un super_admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'super_admin') {
    header("Location: login.php"); // Redirigir a la página de login si no está autorizado
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
                $message = "<div class='alert alert-success'>Publicidad guardada con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al guardar publicidad: " . $stmt->error . "</div>";
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
        $message = "<div class='alert alert-success'>Publicidad eliminada con éxito.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error al eliminar publicidad: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Lógica para añadir/editar carousel
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'carousel') {
    $nombre = $_POST['nombre'] ?? '';
    $enlace = $_POST['enlace'] ?? '';
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
                $stmt = $conn->prepare("UPDATE carouseles SET nombre = ?, ruta_imagen = ?, enlace = ?, orden = ?, activo = ? WHERE id = ?");
                $stmt->bind_param("sssiii", $nombre, $imagen_name, $enlace, $orden, $activo, $carousel_id);
            } else {
                $stmt = $conn->prepare("UPDATE carouseles SET nombre = ?, enlace = ?, orden = ?, activo = ? WHERE id = ?");
                $stmt->bind_param("ssiii", $nombre, $enlace, $orden, $activo, $carousel_id);
            }
        } else {
            // Añadir carousel
            if ($imagen_name) {
                $stmt = $conn->prepare("INSERT INTO carouseles (nombre, ruta_imagen, enlace, orden, activo) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssii", $nombre, $imagen_name, $enlace, $orden, $activo);
            } else {
                $message = "<div class='alert alert-danger'>La imagen es obligatoria para un nuevo carousel.</div>";
            }
        }

        if (isset($stmt)) {
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Carousel guardado con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al guardar carousel: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

// Lógica para eliminar carousel
if (isset($_GET['action']) && $_GET['action'] == 'delete_carousel' && isset($_GET['id'])) {
    $carousel_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM carouseles WHERE id = ?");
    $stmt->bind_param("i", $carousel_id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Carousel eliminado con éxito.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error al eliminar carousel: " . $stmt->error . "</div>";
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
$result_carouseles = $conn->query("SELECT * FROM carouseles ORDER BY orden ASC");
if ($result_carouseles) {
    while ($row = $result_carouseles->fetch_assoc()) {
        $carouseles[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Publicidad y Carouseles - Admin</title>
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
                    <h1 class="h2">Gestionar Publicidad y Carouseles</h1>
                </div>

                <?php if ($message): ?>
                    <?= $message ?>
                <?php endif; ?>

                <h2 class="mt-5">Gestionar Publicidades</h2>
                <form action="manage_publicidad_carousel.php" method="POST" enctype="multipart/form-data" class="mb-4">
                    <input type="hidden" name="form_type" value="publicidad">
                    <input type="hidden" name="publicidad_id" value="">
                    <div class="mb-3">
                        <label for="publicidad_titulo" class="form-label">Título</label>
                        <input type="text" class="form-control" id="publicidad_titulo" name="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="publicidad_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="publicidad_descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="publicidad_imagen" class="form-label">Imagen</label>
                        <input type="file" class="form-control" id="publicidad_imagen" name="imagen">
                    </div>
                    <div class="mb-3">
                        <label for="publicidad_enlace" class="form-label">Enlace (URL)</label>
                        <input type="url" class="form-control" id="publicidad_enlace" name="enlace">
                    </div>
                    <div class="mb-3">
                        <label for="publicidad_fecha_inicio" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="publicidad_fecha_inicio" name="fecha_inicio">
                    </div>
                    <div class="mb-3">
                        <label for="publicidad_fecha_fin" class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" id="publicidad_fecha_fin" name="fecha_fin">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="publicidad_activo" name="activo" value="1" checked>
                        <label class="form-check-label" for="publicidad_activo">Activo</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Añadir Publicidad</button>
                </form>

                <div class="table-responsive mt-4">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Imagen</th>
                                <th>Enlace</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($publicidades) > 0): ?>
                                <?php foreach ($publicidades as $pub): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($pub['id']) ?></td>
                                        <td><?= htmlspecialchars($pub['titulo']) ?></td>
                                        <td><img src="../assets/img/publicidad/<?= htmlspecialchars($pub['imagen']) ?>" alt="" width="50"></td>
                                        <td><a href="<?= htmlspecialchars($pub['enlace']) ?>" target="_blank">Ver</a></td>
                                        <td><?= $pub['activo'] ? 'Sí' : 'No' ?></td>
                                        <td>
                                            <!-- Editar Publicidad (implementar formulario de edición) -->
                                            <a href="manage_publicidad_carousel.php?action=delete_publicidad&id=<?= htmlspecialchars($pub['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar esta publicidad?');">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No hay publicidades registradas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <h2 class="mt-5">Gestionar Carouseles</h2>
                <form action="manage_publicidad_carousel.php" method="POST" enctype="multipart/form-data" class="mb-4">
                    <input type="hidden" name="form_type" value="carousel">
                    <input type="hidden" name="carousel_id" value="">
                    <div class="mb-3">
                        <label for="carousel_nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="carousel_nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="carousel_imagen" class="form-label">Imagen</label>
                        <input type="file" class="form-control" id="carousel_imagen" name="imagen">
                    </div>
                    <div class="mb-3">
                        <label for="carousel_enlace" class="form-label">Enlace (URL)</label>
                        <input type="url" class="form-control" id="carousel_enlace" name="enlace">
                    </div>
                    <div class="mb-3">
                        <label for="carousel_orden" class="form-label">Orden</label>
                        <input type="number" class="form-control" id="carousel_orden" name="orden" value="0">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="carousel_activo" name="activo" value="1" checked>
                        <label class="form-check-label" for="carousel_activo">Activo</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Añadir Carousel</button>
                </form>

                <div class="table-responsive mt-4">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Imagen</th>
                                <th>Enlace</th>
                                <th>Orden</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($carouseles) > 0): ?>
                                <?php foreach ($carouseles as $car): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($car['id']) ?></td>
                                        <td><?= htmlspecialchars($car['nombre']) ?></td>
                                        <td><img src="../assets/img/carouseles/<?= htmlspecialchars($car['ruta_imagen']) ?>" alt="" width="50"></td>
                                        <td><a href="<?= htmlspecialchars($car['enlace']) ?>" target="_blank">Ver</a></td>
                                        <td><?= htmlspecialchars($car['orden']) ?></td>
                                        <td><?= $car['activo'] ? 'Sí' : 'No' ?></td>
                                        <td>
                                            <!-- Editar Carousel (implementar formulario de edición) -->
                                            <a href="manage_publicidad_carousel.php?action=delete_carousel&id=<?= htmlspecialchars($car['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este carousel?');">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">No hay carouseles registrados.</td>
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
