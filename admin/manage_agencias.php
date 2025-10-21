<?php
session_start();

// Verificar si el usuario ha iniciado sesión y tiene el tipo de usuario correcto
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['agencia', 'super_admin'])) {
    header("Location: login.php"); // Redirigir a la página de login si no está autorizado
    exit();
}

include '../includes/db_connect.php';

$message = '';
$edit_agency = null;
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$agency_id = null;

// Obtener el ID de la agencia del usuario actual si es una agencia
if ($user_type === 'agencia') {
    $stmt = $conn->prepare("SELECT id FROM agencias WHERE id_usuario = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $agency_id = $result->fetch_assoc()['id'];
    }
    $stmt->close();
}

// Lógica para añadir/editar información de agencia
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'agency_info') {
    $nombre_agencia = $_POST['nombre_agencia'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $contacto_email = $_POST['contacto_email'] ?? '';
    $contacto_telefono = $_POST['contacto_telefono'] ?? '';
    $posted_agency_id = $_POST['agency_id'] ?? null;

    if (empty($nombre_agencia) || empty($contacto_email)) {
        $message = "<div class='alert alert-danger'>El nombre de la agencia y el email de contacto son obligatorios.</div>";
    } else {
        if ($posted_agency_id) {
            // Editar agencia existente (solo si el usuario es super_admin o es su propia agencia)
            if ($user_type === 'super_admin' || ($user_type === 'agencia' && $posted_agency_id == $agency_id)) {
                $stmt = $conn->prepare("UPDATE agencias SET nombre_agencia = ?, descripcion = ?, contacto_email = ?, contacto_telefono = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $nombre_agencia, $descripcion, $contacto_email, $contacto_telefono, $posted_agency_id);
                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success'>Información de la agencia actualizada con éxito.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error al actualizar la agencia: " . $stmt->error . "</div>";
                }
                $stmt->close();
            } else {
                $message = "<div class='alert alert-danger'>No tienes permiso para editar esta agencia.</div>";
            }
        } else {
            // Añadir nueva agencia (solo si el usuario es agencia y no tiene ya una registrada)
            if ($user_type === 'agencia') {
                $check_stmt = $conn->prepare("SELECT id FROM agencias WHERE id_usuario = ?");
                $check_stmt->bind_param("i", $user_id);
                $check_stmt->execute();
                $check_stmt->store_result();
                if ($check_stmt->num_rows > 0) {
                    $message = "<div class='alert alert-warning'>Ya tienes una agencia registrada. Edita la existente.</div>";
                } else {
                    $stmt = $conn->prepare("INSERT INTO agencias (id_usuario, nombre_agencia, descripcion, contacto_email, contacto_telefono) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("issss", $user_id, $nombre_agencia, $descripcion, $contacto_email, $contacto_telefono);
                    if ($stmt->execute()) {
                        $message = "<div class='alert alert-success'>Agencia registrada con éxito.</div>";
                        $agency_id = $stmt->insert_id; // Actualizar agency_id para la sesión actual
                    } else {
                        $message = "<div class='alert alert-danger'>Error al registrar la agencia: " . $stmt->error . "</div>";
                    }
                    $stmt->close();
                }
                $check_stmt->close();
            } else {
                $message = "<div class='alert alert-danger'>Solo las agencias pueden registrar nuevas agencias.</div>";
            }
        }
    }
}

// Lógica para añadir/editar servicio de agencia
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'add_edit_service') {
    $service_name = $_POST['service_name'] ?? '';
    $service_description = $_POST['service_description'] ?? '';
    $service_price = $_POST['service_price'] ?? 0.00;
    $service_id = $_POST['service_id'] ?? null;

    if (empty($service_name) || empty($service_price)) {
        $message = "<div class='alert alert-danger'>El nombre y el precio del servicio son obligatorios.</div>";
    } else if ($agency_id) {
        if ($service_id) {
            // Editar servicio existente
            $stmt = $conn->prepare("UPDATE servicios_agencia SET nombre_servicio = ?, descripcion = ?, precio = ? WHERE id = ? AND id_agencia = ?");
            $stmt->bind_param("ssdii", $service_name, $service_description, $service_price, $service_id, $agency_id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Servicio actualizado con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al actualizar el servicio: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            // Añadir nuevo servicio
            $stmt = $conn->prepare("INSERT INTO servicios_agencia (id_agencia, nombre_servicio, descripcion, precio) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issd", $agency_id, $service_name, $service_description, $service_price);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Servicio añadido con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al añadir el servicio: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } else {
        $message = "<div class='alert alert-danger'>Primero debes registrar tu agencia para añadir servicios.</div>";
    }
}

// Lógica para eliminar servicio de agencia
if (isset($_GET['action']) && $_GET['action'] == 'delete_service' && isset($_GET['id'])) {
    $service_to_delete_id = $_GET['id'];
    if ($user_type === 'super_admin') {
        $stmt = $conn->prepare("DELETE FROM servicios_agencia WHERE id = ?");
        $stmt->bind_param("i", $service_to_delete_id);
    } else if ($user_type === 'agencia' && $agency_id) {
        $stmt = $conn->prepare("DELETE FROM servicios_agencia WHERE id = ? AND id_agencia = ?");
        $stmt->bind_param("ii", $service_to_delete_id, $agency_id);
    }
    else {
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

// Lógica para añadir/editar menú de agencia
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'add_edit_menu') {
    $menu_name = $_POST['menu_name'] ?? '';
    $menu_description = $_POST['menu_description'] ?? '';
    $menu_price = $_POST['menu_price'] ?? 0.00;
    $menu_id = $_POST['menu_id'] ?? null;

    if (empty($menu_name) || empty($menu_price)) {
        $message = "<div class='alert alert-danger'>El nombre y el precio del menú son obligatorios.</div>";
    } else if ($agency_id) {
        if ($menu_id) {
            // Editar menú existente
            $stmt = $conn->prepare("UPDATE menus_agencia SET nombre_menu = ?, descripcion = ?, precio_total = ? WHERE id = ? AND id_agencia = ?");
            $stmt->bind_param("ssdii", $menu_name, $menu_description, $menu_price, $menu_id, $agency_id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Menú actualizado con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al actualizar el menú: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            // Añadir nuevo menú
            $stmt = $conn->prepare("INSERT INTO menus_agencia (id_agencia, nombre_menu, descripcion, precio_total) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issd", $agency_id, $menu_name, $menu_description, $menu_price);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Menú añadido con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al añadir el menú: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } else {
        $message = "<div class='alert alert-danger'>Primero debes registrar tu agencia para añadir menús.</div>";
    }
}

// Lógica para eliminar menú de agencia
if (isset($_GET['action']) && $_GET['action'] == 'delete_menu' && isset($_GET['id'])) {
    $menu_to_delete_id = $_GET['id'];
    if ($user_type === 'super_admin') {
        $stmt = $conn->prepare("DELETE FROM menus_agencia WHERE id = ?");
        $stmt->bind_param("i", $menu_to_delete_id);
    } else if ($user_type === 'agencia' && $agency_id) {
        $stmt = $conn->prepare("DELETE FROM menus_agencia WHERE id = ? AND id_agencia = ?");
        $stmt->bind_param("ii", $menu_to_delete_id, $agency_id);
    }
    else {
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

// Obtener datos de la agencia para edición o listado
$agencies = [];
$agency_services = [];
$agency_menus = [];
$agency_orders = []; // Inicializar para evitar warnings

if ($user_type === 'super_admin') {
    $query = "SELECT a.id, a.nombre_agencia, a.descripcion, a.contacto_email, a.contacto_telefono, u.nombre as usuario_nombre FROM agencias a JOIN usuarios u ON a.id_usuario = u.id ORDER BY a.nombre_agencia ASC";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $agencies[] = $row;
        }
    }
} else if ($user_type === 'agencia') {
    $query = "SELECT id, nombre_agencia, descripcion, contacto_email, contacto_telefono FROM agencias WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $edit_agency = $result->fetch_assoc(); // La agencia solo puede editar su propia información
        $agency_id = $edit_agency['id']; // Asegurarse de que agency_id esté disponible

        // Obtener servicios de la agencia
        $stmt_services = $conn->prepare("SELECT id, nombre_servicio, descripcion, precio FROM servicios_agencia WHERE id_agencia = ? ORDER BY nombre_servicio ASC");
        $stmt_services->bind_param("i", $agency_id);
        $stmt_services->execute();
        $result_services = $stmt_services->get_result();
        while ($row = $result_services->fetch_assoc()) {
            $agency_services[] = $row;
        }
        $stmt_services->close();

        // Obtener menús de la agencia
        $stmt_menus = $conn->prepare("SELECT id, nombre_menu, descripcion, precio_total FROM menus_agencia WHERE id_agencia = ? ORDER BY nombre_menu ASC");
        $stmt_menus->bind_param("i", $agency_id);
        $stmt_menus->execute();
        $result_menus = $stmt_menus->get_result();
        while ($row = $result_menus->fetch_assoc()) {
            $agency_menus[] = $row;
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
    <title>Gestionar Agencias - Admin</title>
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
                            <a class="nav-link active" aria-current="page" href="manage_agencias.php">Agencias</a>
                        </li>
                        <?php endif; ?>
                        <?php if (in_array($user_type, ['guia', 'super_admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_guias.php">Guías</a>
                        </a>
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
                    <h1 class="h2">Gestionar Agencias</h1>
                </div>

                <?php if ($message): ?>
                    <?= $message ?>
                <?php endif; ?>

                <?php if ($user_type === 'agencia'): ?>
                    <?php if (!$edit_agency): ?>
                        <h2>Registrar tu Agencia</h2>
                        <form action="manage_agencias.php" method="POST">
                            <input type="hidden" name="form_type" value="agency_info">
                            <div class="mb-3">
                                <label for="nombre_agencia" class="form-label">Nombre de la Agencia</label>
                                <input type="text" class="form-control" id="nombre_agencia" name="nombre_agencia" required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="contacto_email" class="form-label">Email de Contacto</label>
                                <input type="email" class="form-control" id="contacto_email" name="contacto_email" required>
                            </div>
                            <div class="mb-3">
                                <label for="contacto_telefono" class="form-label">Teléfono de Contacto</label>
                                <input type="text" class="form-control" id="contacto_telefono" name="contacto_telefono">
                            </div>
                            <button type="submit" class="btn btn-primary">Registrar Agencia</button>
                        </form>
                    <?php else: // Agencia ya registrada, mostrar formulario de edición y gestión de servicios/menús ?>
                        <h2>Editar Información de Agencia</h2>
                        <form action="manage_agencias.php" method="POST">
                            <input type="hidden" name="form_type" value="agency_info">
                            <input type="hidden" name="agency_id" value="<?= htmlspecialchars($edit_agency['id']) ?>">
                            <div class="mb-3">
                                <label for="nombre_agencia" class="form-label">Nombre de la Agencia</label>
                                <input type="text" class="form-control" id="nombre_agencia" name="nombre_agencia" value="<?= htmlspecialchars($edit_agency['nombre_agencia']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($edit_agency['descripcion']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="contacto_email" class="form-label">Email de Contacto</label>
                                <input type="email" class="form-control" id="contacto_email" name="contacto_email" value="<?= htmlspecialchars($edit_agency['contacto_email']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="contacto_telefono" class="form-label">Teléfono de Contacto</label>
                                <input type="text" class="form-control" id="contacto_telefono" name="contacto_telefono" value="<?= htmlspecialchars($edit_agency['contacto_telefono']) ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Actualizar Agencia</button>
                        </form>

                        <h3 class="mt-5">Gestionar Servicios</h3>
                        <?php if ($agency_id): ?>
                            <form action="manage_agencias.php" method="POST" class="mb-4">
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
                                        <?php if (count($agency_services) > 0): ?>
                                            <?php foreach ($agency_services as $service): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($service['id']) ?></td>
                                                    <td><?= htmlspecialchars($service['nombre_servicio']) ?></td>
                                                    <td><?= htmlspecialchars($service['descripcion']) ?></td>
                                                    <td><?= htmlspecialchars(number_format($service['precio'], 2)) ?> €</td>
                                                    <td>
                                                        <!-- Implementar edición de servicio si es necesario -->
                                                        <a href="manage_agencias.php?action=delete_service&id=<?= htmlspecialchars($service['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este servicio?');">Eliminar</a>
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

                            <h3 class="mt-5">Gestionar Menús/Paquetes</h3>
                            <form action="manage_agencias.php" method="POST" class="mb-4">
                                <input type="hidden" name="form_type" value="add_edit_menu">
                                <input type="hidden" name="menu_id" value="">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="menu_name" placeholder="Nombre del Menú/Paquete" required>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="menu_description" placeholder="Descripción del Menú/Paquete">
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
                                        <?php if (count($agency_menus) > 0): ?>
                                            <?php foreach ($agency_menus as $menu): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($menu['id']) ?></td>
                                                    <td><?= htmlspecialchars($menu['nombre_menu']) ?></td>
                                                    <td><?= htmlspecialchars($menu['descripcion']) ?></td>
                                                    <td><?= htmlspecialchars(number_format($menu['precio_total'], 2)) ?> €</td>
                                                    <td>
                                                        <!-- Implementar edición de menú si es necesario -->
                                                        <a href="manage_agencias.php?action=delete_menu&id=<?= htmlspecialchars($menu['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este menú?');">Eliminar</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5">No hay menús/paquetes registrados.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">Registra tu agencia para gestionar servicios y menús.</div>
                        <?php endif; ?>

                        <h3 class="mt-5">Pedidos Recibidos</h3>
                        <?php if ($agency_id): ?>
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
                                        <?php if (count($agency_orders) > 0): ?>
                                            <?php foreach ($agency_orders as $order): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($order['id']) ?></td>
                                                    <td><?= htmlspecialchars($order['item_name']) ?></td>
                                                    <td><?= htmlspecialchars($order['tipo_item']) ?></td>
                                                    <td><?= htmlspecialchars($order['turista_nombre']) ?></td>
                                                    <td><?= htmlspecialchars($order['fecha_servicio']) ?></td>
                                                    <td><?= htmlspecialchars($order['cantidad_personas']) ?></td>
                                                    <td><?= htmlspecialchars(number_format($order['precio_total'], 2)) ?> €</td>
                                                    <td>
                                                        <form action="manage_agencias.php" method="POST" style="display:inline;">
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
                            <div class="alert alert-info">Registra tu agencia para ver los pedidos.</div>
                        <?php endif; ?>

                    <?php endif; // Fin de la lógica para usuario tipo agencia ?>

                    <?php endif; // Fin de la lógica para usuario tipo agencia ?>

                <?php if ($user_type === 'super_admin'): ?>
                    <h2 class="mt-5">Listado de Agencias</h2>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Nombre Agencia</th>
                                    <th>Email Contacto</th>
                                    <th>Teléfono Contacto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($agencies) > 0): ?>
                                    <?php foreach ($agencies as $agency): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($agency['id']) ?></td>
                                            <td><?= htmlspecialchars($agency['usuario_nombre']) ?></td>
                                            <td><?= htmlspecialchars($agency['nombre_agencia']) ?></td>
                                            <td><?= htmlspecialchars($agency['contacto_email']) ?></td>
                                            <td><?= htmlspecialchars($agency['contacto_telefono']) ?></td>
                                            <td>
                                                <a href="manage_agencias.php?action=delete&id=<?= htmlspecialchars($agency['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar esta agencia?');">Eliminar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">No hay agencias registradas.</td>
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
