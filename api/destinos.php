<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';

$response = ['success' => false, 'message' => '', 'data' => []];

// Verificar método de petición
$method = $_SERVER['REQUEST_METHOD'];

if ($conn) {
    $action = $_GET['action'] ?? null;

    // CREAR/ACTUALIZAR destino (POST/PUT)
    if ($method === 'POST' || $method === 'PUT') {
        // Verificar autenticación
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['super_admin'])) {
            $response['message'] = 'No autorizado';
            echo json_encode($response);
            exit();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $destino_id = $data['id'] ?? null;
        $nombre = $data['nombre'] ?? '';
        $descripcion = $data['descripcion'] ?? '';
        $categoria = $data['categoria'] ?? '';
        $precio = $data['precio'] ?? 0.00;
        $ciudad = $data['ciudad'] ?? '';
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        if (empty($nombre) || empty($descripcion) || empty($categoria)) {
            $response['message'] = 'Campos requeridos faltantes';
            echo json_encode($response);
            exit();
        }

        if ($destino_id) {
            // Actualizar
            $sql = "UPDATE destinos SET nombre = ?, descripcion = ?, categoria = ?, precio = ?, ciudad = ?, latitude = ?, longitude = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssdssi", $nombre, $descripcion, $categoria, $precio, $ciudad, $latitude, $longitude, $destino_id);
        } else {
            // Crear
            $sql = "INSERT INTO destinos (nombre, descripcion, categoria, precio, ciudad, latitude, longitude, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, 'default.jpg')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssdss", $nombre, $descripcion, $categoria, $precio, $ciudad, $latitude, $longitude);
        }

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = $destino_id ? 'Destino actualizado exitosamente' : 'Destino creado exitosamente';
            $response['data'] = ['id' => $destino_id ?? $conn->insert_id];
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        $stmt->close();
    }
    
    // ELIMINAR destino (DELETE)
    else if ($method === 'DELETE') {
        // Verificar autenticación
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['super_admin'])) {
            $response['message'] = 'No autorizado';
            echo json_encode($response);
            exit();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $destino_id = $data['id'] ?? null;

        if (!$destino_id) {
            $response['message'] = 'ID de destino requerido';
            echo json_encode($response);
            exit();
        }

        // Eliminar imágenes de galería
        $stmt_gallery = $conn->prepare("SELECT ruta_imagen FROM imagenes_destino WHERE id_destino = ?");
        $stmt_gallery->bind_param("i", $destino_id);
        $stmt_gallery->execute();
        $result_gallery = $stmt_gallery->get_result();
        while ($gallery_img = $result_gallery->fetch_assoc()) {
            $gallery_file_path = "../assets/img/destinos/" . $gallery_img['ruta_imagen'];
            if (file_exists($gallery_file_path)) {
                @unlink($gallery_file_path);
            }
        }
        $stmt_gallery->close();

        // Eliminar registros relacionados
        $conn->query("DELETE FROM imagenes_destino WHERE id_destino = $destino_id");
        $conn->query("DELETE FROM itinerario_destinos WHERE id_destino = $destino_id");

        // Eliminar destino
        $stmt = $conn->prepare("DELETE FROM destinos WHERE id = ?");
        $stmt->bind_param("i", $destino_id);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Destino eliminado exitosamente';
        } else {
            $response['message'] = 'Error al eliminar: ' . $stmt->error;
        }
        $stmt->close();
    }
    
    // OBTENER destinos (GET)
    else if ($method === 'GET') {
        if ($action === 'get_categories') {
            $sql_categories = "SELECT DISTINCT categoria FROM destinos ORDER BY categoria ASC";
            $result_categories = $conn->query($sql_categories);
            if ($result_categories) {
                $categories = [];
                while ($row = $result_categories->fetch_assoc()) {
                    $categories[] = $row['categoria'];
                }
                $response['success'] = true;
                $response['data'] = $categories;
            } else {
                $response['message'] = "Error al obtener categorías: " . $conn->error;
            }
        } 
        else if ($action === 'get_all_destinos') {
            $city = $_GET['city'] ?? '';

            $sql = "SELECT id, nombre, descripcion, COALESCE(imagen, '') as imagen, categoria, precio, ciudad FROM destinos";
            $params = [];
            $param_types = '';

            if (!empty($city)) {
                $sql .= " WHERE ciudad LIKE ?";
                $params[] = "%{$city}%";
                $param_types .= 's';
            }

            $sql .= " ORDER BY nombre ASC";

            $stmt = $conn->prepare($sql);

            if (!empty($city)) {
                $stmt->bind_param($param_types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                $destinos = [];
                while ($row = $result->fetch_assoc()) {
                    if (preg_match('/\.(jpeg|jpg|png|gif|webp)$/i', $row['imagen'])) {
                        $row['imagen'] = 'assets/img/destinos/' . $row['imagen'];
                    } else {
                        $row['imagen'] = 'assets/img/destinos/default.jpg';
                    }
                    $destinos[] = $row;
                }
                $response['success'] = true;
                $response['data'] = $destinos;
            } else {
                $response['message'] = "Error en la consulta: " . $conn->error;
            }
        }
        else if ($action === 'get_one') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM destinos WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $response['success'] = true;
                    $response['data'] = $row;
                } else {
                    $response['message'] = 'Destino no encontrado';
                }
                $stmt->close();
            }
        }
        else { // Default action: fetch destinations with pagination and filter
            $items_per_page = isset($_GET['items_per_page']) ? (int)$_GET['items_per_page'] : 9;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $items_per_page;

            $category = $_GET['category'] ?? 'all';

            $where_clause = '';
            $params = [];
            $param_types = '';

            if ($category !== 'all') {
                $where_clause = " WHERE categoria = ?";
                $params[] = $category;
                $param_types .= 's';
            }

            // First, get total number of destinations for pagination info
            $count_sql = "SELECT COUNT(*) as total FROM destinos" . $where_clause;
            $count_stmt = $conn->prepare($count_sql);
            if ($category !== 'all') {
                $count_stmt->bind_param($param_types, ...$params);
            }
            $count_stmt->execute();
            $total_destinos = $count_stmt->get_result()->fetch_assoc()['total'];
            $count_stmt->close();

            $sql = "SELECT id, nombre, descripcion, imagen, categoria, precio, ciudad FROM destinos" . $where_clause . " ORDER BY id DESC";

            // Apply pagination limits
            $sql .= " LIMIT ?, ?";

            $stmt = $conn->prepare($sql);
            
            // Dynamically bind parameters for category and pagination
            $bind_params = $params;
            $bind_params[] = $offset;
            $bind_params[] = $items_per_page;

            if ($category !== 'all') {
                $stmt->bind_param($param_types . 'ii', ...$bind_params);
            } else {
                $stmt->bind_param('ii', ...$bind_params);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                $destinos = [];
                while ($row = $result->fetch_assoc()) {
                    $row['imagen'] = 'assets/img/destinos/' . ($row['imagen'] ?? 'default.jpg');
                    $destinos[] = $row;
                }
                $response['success'] = true;
                $response['data'] = $destinos;
                $response['total_destinos'] = $total_destinos;
                $response['current_page'] = $page;
                $response['total_pages'] = ceil($total_destinos / $items_per_page);
            } else {
                $response['message'] = "Error en la consulta: " . $conn->error;
            }
        }
    }

    $conn->close();
} else {
    $response['message'] = "Error de conexión a la base de datos.";
}

echo json_encode($response);
?>
