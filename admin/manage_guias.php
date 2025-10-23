<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
$guia_city = null; // Inicializar $guia_city

// Obtener el ID del guía del usuario actual si es un guía
if ($user_type === 'guia') {
    $stmt = $conn->prepare("SELECT id, ciudad_operacion FROM guias_turisticos WHERE id_usuario = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $guia_data = $result->fetch_assoc();
        $guia_id = $guia_data['id'];
        $guia_city = $guia_data['ciudad_operacion'];
    }
    $stmt->close();
}

// Lógica para actualizar estado de pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'update_order_status') {
    $order_id = $_POST['order_id'] ?? null;
    $new_status = $_POST['new_status'] ?? '';
    
    if ($order_id && $new_status) {
        if ($user_type === 'guia' && $guia_id) {
            $stmt_update = $conn->prepare("UPDATE pedidos_servicios SET estado = ? WHERE id = ? AND tipo_proveedor = 'guia' AND id_proveedor = ?");
            $stmt_update->bind_param("sii", $new_status, $order_id, $guia_id);
            if ($stmt_update->execute()) {
                $message = "<div class='alert alert-success'>Estado del pedido actualizado con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al actualizar el estado del pedido: " . $stmt_update->error . "</div>";
            }
            $stmt_update->close();
        }
    } else {
        $message = "<div class='alert alert-danger'>Datos incompletos para actualizar el estado.</div>";
    }
}

// Lógica para añadir/eliminar destinos ofrecidos por el guía
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'manage_guia_destinos') {
    $action = $_POST['action'] ?? '';
    $destino_id = $_POST['destino_id'] ?? null;

    if ($guia_id && $destino_id) {
        if ($action === 'add') {
            // Verificar si el destino ya está ofrecido
            $stmt_check = $conn->prepare("SELECT id FROM guias_destinos WHERE id_guia = ? AND id_destino = ?");
            $stmt_check->bind_param("ii", $guia_id, $destino_id);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $message = "<div class='alert alert-warning'>Este destino ya está ofrecido por ti.</div>";
            } else {
                // Verificar que el destino pertenece a la ciudad del guía
                $stmt_check_city = $conn->prepare("SELECT id FROM destinos WHERE id = ? AND ciudad = ?");
                $stmt_check_city->bind_param("is", $destino_id, $guia_city);
                $stmt_check_city->execute();
                $stmt_check_city->store_result();
                if ($stmt_check_city->num_rows > 0) {
                    $stmt_add = $conn->prepare("INSERT INTO guias_destinos (id_guia, id_destino) VALUES (?, ?)");
                    $stmt_add->bind_param("ii", $guia_id, $destino_id);
                    if ($stmt_add->execute()) {
                        $message = "<div class='alert alert-success'>Destino añadido con éxito.</div>";
                    } else {
                        $message = "<div class='alert alert-danger'>Error al añadir destino: " . $stmt_add->error . "</div>";
                    }
                    $stmt_add->close();
                } else {
                    $message = "<div class='alert alert-danger'>Este destino no pertenece a tu ciudad de operación.</div>";
                }
                $stmt_check_city->close();
            }
            $stmt_check->close();
        } elseif ($action === 'remove') {
            $stmt_remove = $conn->prepare("DELETE FROM guias_destinos WHERE id_guia = ? AND id_destino = ?");
            $stmt_remove->bind_param("ii", $guia_id, $destino_id);
            if ($stmt_remove->execute()) {
                $message = "<div class='alert alert-success'>Destino eliminado con éxito.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error al eliminar destino: " . $stmt_remove->error . "</div>";
            }
            $stmt_remove->close();
        }
    } else {
        $message = "<div class='alert alert-danger'>Datos incompletos para gestionar destinos.</div>";
    }
}

// Obtener destinos disponibles en la ciudad del guía
$available_destinos = [];
if ($guia_city) {
    $stmt_available_destinos = $conn->prepare("SELECT id, nombre FROM destinos WHERE ciudad = ? ORDER BY nombre ASC");
    $stmt_available_destinos->bind_param("s", $guia_city);
    $stmt_available_destinos->execute();
    $result_available_destinos = $stmt_available_destinos->get_result();
    while ($row = $result_available_destinos->fetch_assoc()) {
        $available_destinos[] = $row;
    }
    $stmt_available_destinos->close();
}

// Obtener destinos que el guía ya ofrece
$guia_destinos_ofrecidos = [];
if ($guia_id) {
    $stmt_guia_destinos = $conn->prepare("
        SELECT gd.id_destino, d.nombre, d.ciudad
        FROM guias_destinos gd
        JOIN destinos d ON gd.id_destino = d.id
        WHERE gd.id_guia = ?
        ORDER BY d.nombre ASC
    ");
    $stmt_guia_destinos->bind_param("i", $guia_id);
    $stmt_guia_destinos->execute();
    $result_guia_destinos = $stmt_guia_destinos->get_result();
    while ($row = $result_guia_destinos->fetch_assoc()) {
        $guia_destinos_ofrecidos[] = $row;
    }
    $stmt_guia_destinos->close();
}

// Lógica para añadir/editar información de guía
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'guia_info') {
    $nombre_guia = $_POST['nombre_guia'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $especialidades = $_POST['especialidades'] ?? '';
    $precio_hora = $_POST['precio_hora'] ?? 0.00;
    $contacto_email = $_POST['contacto_email'] ?? '';
    $contacto_telefono = $_POST['contacto_telefono'] ?? '';
    $ciudad_operacion = $_POST['ciudad_operacion'] ?? '';
    $posted_guia_id = $_POST['guia_id'] ?? null;

    if (empty($nombre_guia) || empty($contacto_email) || empty($precio_hora) || empty($ciudad_operacion)) {
        $message = "<div class='alert alert-danger'>El nombre del guía, el email de contacto, el precio por hora y la ciudad de operación son obligatorios.</div>";
    } else {
        if ($posted_guia_id) {
            // Editar guía existente
            if ($user_type === 'super_admin' || ($user_type === 'guia' && $posted_guia_id == $guia_id)) {
                $profile_image_name = '';
                error_log("DEBUG: POST request received for guia_info form.");
                error_log("DEBUG: FILES array: " . print_r($_FILES, true));

                if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] == UPLOAD_ERR_OK) {
                    error_log("DEBUG: imagen_perfil file detected.");
                    $target_dir = "../assets/img/guias/";
                    if (!is_dir($target_dir)) {
                        error_log("DEBUG: Target directory does not exist. Attempting to create: " . $target_dir);
                        if (!mkdir($target_dir, 0777, true)) {
                            error_log("ERROR: Failed to create directory: " . $target_dir);
                            $message = "<div class='alert alert-danger'>Error: No se pudo crear el directorio de imágenes.</div>";
                            // Exit or handle error appropriately
                        }
                    }
                    $profile_image_name = uniqid() . "_" . basename($_FILES['imagen_perfil']['name']);
                    $target_file = $target_dir . $profile_image_name;
                    error_log("DEBUG: Attempting to move uploaded file to: " . $target_file);
                    if (!move_uploaded_file($_FILES['imagen_perfil']['tmp_name'], $target_file)) {
                        $message = "<div class='alert alert-danger'>Error al subir la imagen de perfil.</div>";
                        $profile_image_name = ''; // Reset if upload fails
                        error_log("ERROR: Failed to move uploaded file. Check permissions for " . $target_dir);
                    } else {
                        error_log("DEBUG: File moved successfully: " . $profile_image_name);
                    }
                } else if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] != UPLOAD_ERR_NO_FILE) {
                    error_log("ERROR: File upload error for imagen_perfil: " . $_FILES['imagen_perfil']['error']);
                    $message = "<div class='alert alert-danger'>Error en la subida del archivo: Código " . $_FILES['imagen_perfil']['error'] . "</div>";
                }

                $sql = "UPDATE guias_turisticos SET nombre_guia = ?, descripcion = ?, especialidades = ?, precio_hora = ?, contacto_email = ?, contacto_telefono = ?, ciudad_operacion = ?" . (!empty($profile_image_name) ? ", imagen_perfil = ?" : "") . " WHERE id = ?";
                error_log("DEBUG: SQL query: " . $sql);
                error_log("DEBUG: profile_image_name: " . $profile_image_name);
                
                if (!empty($profile_image_name)) {
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssdssssi", $nombre_guia, $descripcion, $especialidades, $precio_hora, $contacto_email, $contacto_telefono, $ciudad_operacion, $profile_image_name, $posted_guia_id);
                    error_log("DEBUG: bind_param types: sssdssssi, params: " . $nombre_guia . ", " . $descripcion . ", " . $especialidades . ", " . $precio_hora . ", " . $contacto_email . ", " . $contacto_telefono . ", " . $ciudad_operacion . ", " . $profile_image_name . ", " . $posted_guia_id);
                } else {
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssdsssi", $nombre_guia, $descripcion, $especialidades, $precio_hora, $contacto_email, $contacto_telefono, $ciudad_operacion, $posted_guia_id);
                    error_log("DEBUG: bind_param types: sssdsssi, params: " . $nombre_guia . ", " . $descripcion . ", " . $especialidades . ", " . $precio_hora . ", " . $contacto_email . ", " . $contacto_telefono . ", " . $ciudad_operacion . ", " . $posted_guia_id);
                }

                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success'>Información del guía actualizada con éxito.</div>";
                    error_log("DEBUG: Guia updated successfully.");
                } else {
                    $message = "<div class='alert alert-danger'>Error al actualizar el guía: " . $stmt->error . "</div>";
                    error_log("ERROR: Failed to update guia: " . $stmt->error);
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
                    $stmt = $conn->prepare("INSERT INTO guias_turisticos (id_usuario, nombre_guia, descripcion, especialidades, precio_hora, contacto_email, contacto_telefono, ciudad_operacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssdsss", $user_id, $nombre_guia, $descripcion, $especialidades, $precio_hora, $contacto_email, $contacto_telefono, $ciudad_operacion);
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
$guia_orders = [];

if ($user_type === 'super_admin') {
    $query = "SELECT g.id, g.nombre_guia, g.descripcion, g.especialidades, g.precio_hora, g.contacto_email, g.contacto_telefono, u.nombre as usuario_nombre FROM guias_turisticos g JOIN usuarios u ON g.id_usuario = u.id ORDER BY g.nombre_guia ASC";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $guias[] = $row;
        }
    }
} else if ($user_type === 'guia') {
    $query = "SELECT id, nombre_guia, descripcion, especialidades, precio_hora, contacto_email, contacto_telefono, ciudad_operacion, imagen_perfil FROM guias_turisticos WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $edit_guia = $result->fetch_assoc(); // El guía solo puede editar su propia información
        $guia_id = $edit_guia['id']; // Asegurarse de que guia_id esté disponible
        $guia_city = $edit_guia['ciudad_operacion']; // Actualizar guia_city

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

        // Obtener pedidos del guía
        $stmt_orders = $conn->prepare("
            SELECT ps.id, 
                   sg.nombre_servicio AS item_name,
                   ps.tipo_item, 
                   u.nombre as turista_nombre, 
                   ps.fecha_servicio, 
                   ps.cantidad_personas, 
                   ps.precio_total, 
                   ps.estado,
                   ps.id_itinerario,
                   ps.fecha_solicitud
            FROM pedidos_servicios ps
            JOIN usuarios u ON ps.id_turista = u.id
            LEFT JOIN servicios_guia sg ON ps.id_servicio_o_menu = sg.id AND ps.tipo_item = 'servicio'
            WHERE ps.tipo_proveedor = 'guia' AND ps.id_proveedor = ?
            ORDER BY ps.fecha_solicitud DESC
        ");
        $stmt_orders->bind_param("i", $guia_id);
        $stmt_orders->execute();
        $result_orders = $stmt_orders->get_result();
        while ($row = $result_orders->fetch_assoc()) {
            $guia_orders[] = $row;
        }
        $stmt_orders->close();

        // Obtener datos de ingresos y estadísticas para el guía
        $total_income_guia = 0;
        $completed_orders_count_guia = 0;
        $income_by_service_guia = [];

        if ($guia_id) {
            // Total de ingresos y pedidos completados
            $stmt_income_summary = $conn->prepare("SELECT SUM(precio_total) as total_income, COUNT(*) as completed_count FROM pedidos_servicios WHERE tipo_proveedor = 'guia' AND id_proveedor = ? AND estado = 'completado'");
            $stmt_income_summary->bind_param("i", $guia_id);
            $stmt_income_summary->execute();
            $result_income_summary = $stmt_income_summary->get_result()->fetch_assoc();
            $total_income_guia = $result_income_summary['total_income'] ?? 0;
            $completed_orders_count_guia = $result_income_summary['completed_count'] ?? 0;
            $stmt_income_summary->close();

            // Ingresos por servicio
            $stmt_income_service = $conn->prepare("
                SELECT sg.nombre_servicio AS item_name, SUM(ps.precio_total) as total_item_income 
                FROM pedidos_servicios ps 
                JOIN servicios_guia sg ON ps.id_servicio_o_menu = sg.id
                WHERE ps.tipo_proveedor = 'guia' AND ps.id_proveedor = ? AND ps.tipo_item = 'servicio' AND ps.estado = 'completado' 
                GROUP BY sg.nombre_servicio 
                ORDER BY total_item_income DESC
            ");
            $stmt_income_service->bind_param("i", $guia_id);
            $stmt_income_service->execute();
            $result_income_service = $stmt_income_service->get_result();
            while ($row = $result_income_service->fetch_assoc()) {
                $income_by_service_guia[] = $row;
            }
            $stmt_income_service->close();
        }
    }
    $stmt->close();
}

$conn->close();

$page_title = "Gestionar Guías Turísticos";
include 'admin_header.php';
?>

<div class="admin-page-header">
    <h1>Gestionar Guías Turísticos</h1>
    <p>Administra perfiles de guías, servicios, imágenes y pedidos</p>
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
                                <label for="ciudad_operacion" class="form-label">Ciudad de Operación</label>
                                <input type="text" class="form-control" id="ciudad_operacion" name="ciudad_operacion" placeholder="Ej: Malabo" required>
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
                                <label for="ciudad_operacion" class="form-label">Ciudad de Operación</label>
                                <input type="text" class="form-control" id="ciudad_operacion" name="ciudad_operacion" value="<?= htmlspecialchars($edit_guia['ciudad_operacion'] ?? '') ?>" placeholder="Ej: Malabo" required>
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
                            <div class="mb-3">
                                <label for="imagen_perfil" class="form-label"><i class="bi bi-person-circle me-2"></i>Imagen de Perfil</label>
                                <input type="file" class="form-control" id="imagen_perfil" name="imagen_perfil">
                                <?php if ($edit_guia && isset($edit_guia['imagen_perfil']) && !empty($edit_guia['imagen_perfil'])): ?>
                                    <small class="text-muted">Imagen actual: <a href="../assets/img/guias/<?= htmlspecialchars($edit_guia['imagen_perfil']) ?>" target="_blank"><?= htmlspecialchars($edit_guia['imagen_perfil']) ?></a></small>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Actualizar Perfil de Guía</button>
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

                        <h3 class="mt-5">Actualizar Ubicación</h3>
                        <?php if ($guia_id): ?>
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Mi Ubicación Actual</h5>
                                    <form id="updateLocationForm">
                                        <input type="hidden" id="guiaId" value="<?= htmlspecialchars($guia_id) ?>">
                                        <div class="mb-3">
                                            <label for="latitude" class="form-label">Latitud</label>
                                            <input type="text" class="form-control" id="latitude" name="latitude" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label for="longitude" class="form-label">Longitud</label>
                                            <input type="text" class="form-control" id="longitude" name="longitude" readonly>
                                        </div>
                                        <button type="button" class="btn btn-primary" id="getLocationBtn"><i class="bi bi-geo-alt-fill"></i> Obtener Mi Ubicación</button>
                                        <button type="submit" class="btn btn-success ms-2">Guardar Ubicación</button>
                                        <div id="locationMessage" class="mt-3"></div>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">Registra tu perfil de guía para actualizar tu ubicación.</div>
                        <?php endif; ?>

                        <h3 class="mt-5">Gestionar Destinos Ofrecidos</h3>
                        <?php if ($guia_id && $guia_city): ?>
                            <form action="manage_guias.php" method="POST" class="mb-4">
                                <input type="hidden" name="form_type" value="manage_guia_destinos">
                                <input type="hidden" name="action" value="add">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-9">
                                        <label for="destino_id" class="form-label">Seleccionar Destino de <?= htmlspecialchars($guia_city) ?></label>
                                        <select class="form-select" id="destino_id" name="destino_id" required>
                                            <option value="">-- Selecciona un destino --</option>
                                            <?php foreach ($available_destinos as $destino): ?>
                                                <option value="<?= htmlspecialchars($destino['id']) ?>"><?= htmlspecialchars($destino['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-success w-100">Añadir Destino</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID Destino</th>
                                            <th>Nombre</th>
                                            <th>Ciudad</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($guia_destinos_ofrecidos) > 0): ?>
                                            <?php foreach ($guia_destinos_ofrecidos as $destino): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($destino['id_destino']) ?></td>
                                                    <td><?= htmlspecialchars($destino['nombre']) ?></td>
                                                    <td><?= htmlspecialchars($destino['ciudad']) ?></td>
                                                    <td>
                                                        <form action="manage_guias.php" method="POST" style="display:inline;">
                                                            <input type="hidden" name="form_type" value="manage_guia_destinos">
                                                            <input type="hidden" name="action" value="remove">
                                                            <input type="hidden" name="destino_id" value="<?= htmlspecialchars($destino['id_destino']) ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres dejar de ofrecer este destino?');">Eliminar</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4">No ofreces ningún destino actualmente.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">Registra tu perfil de guía y especifica tu ciudad de operación para gestionar destinos.</div>
                        <?php endif; ?>

                        <h3 class="mt-5">Ingresos y Estadísticas</h3>
                        <?php if ($guia_id): ?>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card text-white bg-success h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Ingresos Totales Completados</h5>
                                            <p class="card-text display-4"><?= number_format($total_income_guia, 2) ?> €</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card text-white bg-info h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Pedidos Completados</h5>
                                            <p class="card-text display-4"><?= $completed_orders_count_guia ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h4>Ingresos por Servicio</h4>
                            <?php if (count($income_by_service_guia) > 0): ?>
                                <ul class="list-group mb-3">
                                    <?php foreach ($income_by_service_guia as $item): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?= htmlspecialchars($item['item_name']) ?>
                                            <span class="badge bg-primary rounded-pill"><?= number_format($item['total_item_income'], 2) ?> €</span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">No hay ingresos por servicios completados.</p>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">Registra tu perfil de guía para ver tus ingresos y estadísticas.</div>
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
                <?php endif; ?>

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const getLocationBtn = document.getElementById('getLocationBtn');
            const updateLocationForm = document.getElementById('updateLocationForm');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const guiaIdInput = document.getElementById('guiaId');
            const locationMessage = document.getElementById('locationMessage');

            if (getLocationBtn) {
                getLocationBtn.addEventListener('click', function() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(showPosition, showError);
                    } else {
                        locationMessage.innerHTML = '<div class="alert alert-danger">La geolocalización no es soportada por este navegador.</div>';
                    }
                });
            }

            function showPosition(position) {
                latitudeInput.value = position.coords.latitude;
                longitudeInput.value = position.coords.longitude;
                locationMessage.innerHTML = '<div class="alert alert-success">Ubicación obtenida con éxito. Ahora puedes guardarla.</div>';
            }

            function showError(error) {
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        locationMessage.innerHTML = '<div class="alert alert-danger">Usuario denegó la solicitud de geolocalización.</div>';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        locationMessage.innerHTML = '<div class="alert alert-danger">Información de ubicación no disponible.</div>';
                        break;
                    case error.TIMEOUT:
                        locationMessage.innerHTML = '<div class="alert alert-danger">La solicitud para obtener la ubicación ha caducado.</div>';
                        break;
                    case error.UNKNOWN_ERROR:
                        locationMessage.innerHTML = '<div class="alert alert-danger">Ha ocurrido un error desconocido.</div>';
                        break;
                }
            }

            if (updateLocationForm) {
                updateLocationForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    locationMessage.innerHTML = '<div class="alert alert-info">Guardando ubicación...</div>';

                    const formData = {
                        guia_id: guiaIdInput.value,
                        latitude: latitudeInput.value,
                        longitude: longitudeInput.value
                    };

                    fetch('../api/location.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            locationMessage.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        } else {
                            locationMessage.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        locationMessage.innerHTML = '<div class="alert alert-danger">Hubo un error de conexión. Por favor, inténtalo de nuevo más tarde.</div>';
                    });
                });
            }
        });
    </script>

<?php include 'admin_footer.php'; ?>
