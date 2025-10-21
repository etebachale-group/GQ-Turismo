<?php
session_start();

// Verificar si el usuario ha iniciado sesión y tiene el tipo de usuario correcto
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['agencia', 'guia', 'local', 'super_admin'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

$message = '';
$edit_destino = null;

// Lógica para añadir/editar destino
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $categoria = $_POST['categoria'];
    $precio = $_POST['precio'];
    $imagen = $_FILES['imagen']['name'];
    $target_dir = "../assets/img/destinos/";
    $target_file = $target_dir . basename($imagen);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Validar imagen
    if ($imagen) {
        $check = getimagesize($_FILES['imagen']['tmp_name']);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $message = "El archivo no es una imagen.";
            $uploadOk = 0;
        }
        if (file_exists($target_file)) {
            $message = "Lo siento, el archivo ya existe.";
            $uploadOk = 0;
        }
        if ($_FILES['imagen']['size'] > 500000) { // 500KB
            $message = "Lo siento, tu archivo es demasiado grande.";
            $uploadOk = 0;
        }
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $message = "Lo siento, solo se permiten archivos JPG, JPEG, PNG & GIF.";
            $uploadOk = 0;
        }
    }

    if (isset($_POST['destino_id']) && !empty($_POST['destino_id'])) {
        // Editar destino
        $id = $_POST['destino_id'];
        if ($imagen && $uploadOk) {
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
                $stmt = $conn->prepare("UPDATE destinos SET nombre=?, descripcion=?, categoria=?, imagen=?, precio=? WHERE id=?");
                $stmt->bind_param("sssdsi", $nombre, $descripcion, $categoria, $imagen, $precio, $id);
            } else {
                $message = "Error al subir la imagen.";
            }
        } else if (!$imagen) {
            $stmt = $conn->prepare("UPDATE destinos SET nombre=?, descripcion=?, categoria=?, precio=? WHERE id=?");
            $stmt->bind_param("ssdsi", $nombre, $descripcion, $categoria, $precio, $id);
        }
        
        if (isset($stmt) && $stmt->execute()) {
            $message = "Destino actualizado exitosamente.";
        } else if (isset($stmt)) {
            $message = "Error al actualizar destino: " . $stmt->error;
        }
    } else {
        // Añadir destino
        if ($uploadOk) {
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
                $stmt = $conn->prepare("INSERT INTO destinos (nombre, descripcion, categoria, imagen, precio) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssds", $nombre, $descripcion, $categoria, $imagen, $precio);
                if ($stmt->execute()) {
                    $message = "Destino añadido exitosamente.";
                } else {
                    $message = "Error al añadir destino: " . $stmt->error;
                }
            } else {
                $message = "Error al subir la imagen.";
            }
        }
    } else {
        $message = "No se pudo añadir el destino debido a un error en la imagen.";
    }
    if (isset($stmt)) $stmt->close();
}

// Lógica para eliminar destino
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM destinos WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Destino eliminado exitosamente.";
    } else {
        $message = "Error al eliminar destino: " . $stmt->error;
    }
    $stmt->close();
}

// Lógica para cargar destino para edición
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, categoria, imagen, precio FROM destinos WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $edit_destino = $result->fetch_assoc();
    }
    $stmt->close();
}

// Obtener todos los destinos
$destinos = $conn->query("SELECT id, nombre, categoria, precio, imagen FROM destinos ORDER BY id DESC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Destinos - GQ-TURISMO Admin</title>
    <!-- Bootstrap CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
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
                            <a class="nav-link" href="dashboard.php">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="destinos.php">
                                Destinos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reservas.php">
                                Reservas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestionar Destinos</h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-info" role="alert">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <h2><?= $edit_destino ? 'Editar Destino' : 'Añadir Nuevo Destino' ?></h2>
                <form action="destinos.php" method="POST" enctype="multipart/form-data">
                    <?php if ($edit_destino): ?>
                        <input type="hidden" name="destino_id" value="<?= htmlspecialchars($edit_destino['id']) ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= $edit_destino ? htmlspecialchars($edit_destino['nombre']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?= $edit_destino ? htmlspecialchars($edit_destino['descripcion']) : '' ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="categoria" name="categoria" value="<?= $edit_destino ? htmlspecialchars($edit_destino['categoria']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="precio" class="form-label">Precio</label>
                        <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="<?= $edit_destino ? htmlspecialchars($edit_destino['precio']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="imagen" class="form-label">Imagen</label>
                        <input type="file" class="form-control" id="imagen" name="imagen">
                        <?php if ($edit_destino && $edit_destino['imagen']): ?>
                            <small class="form-text text-muted">Imagen actual: <?= htmlspecialchars($edit_destino['imagen']) ?></small>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary"><?= $edit_destino ? 'Actualizar Destino' : 'Añadir Destino' ?></button>
                    <?php if ($edit_destino): ?>
                        <a href="destinos.php" class="btn btn-secondary">Cancelar Edición</a>
                    <?php endif; ?>
                </form>

                <h2 class="mt-5">Listado de Destinos</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Imagen</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($destinos->num_rows > 0): ?>
                                <?php while($row = $destinos->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id']) ?></td>
                                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                                        <td><?= htmlspecialchars($row['categoria']) ?></td>
                                        <td><?= htmlspecialchars(number_format($row['precio'], 2)) ?> €</td>
                                        <td><img src="../assets/img/destinos/<?= htmlspecialchars($row['imagen']) ?>" alt="" width="50"></td>
                                        <td>
                                            <a href="manage_destinos.php?action=edit&id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-warning btn-sm">Editar</a>
                                            <a href="manage_destinos.php?action=delete&id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este destino?');">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No hay destinos registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
