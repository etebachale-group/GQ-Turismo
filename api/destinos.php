<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$response = ['success' => false, 'data' => []];

if ($conn) {
    $action = $_GET['action'] ?? null;

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
            $response['error'] = "Error al obtener categorías: " . $conn->error;
        }
    } else { // Default action: fetch destinations with pagination and filter
        $items_per_page = isset($_GET['items_per_page']) ? (int)$_GET['items_per_page'] : 9; // Default to 9 items per page
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

        $sql = "SELECT id, nombre, descripcion, imagen, categoria, precio FROM destinos" . $where_clause . " ORDER BY id DESC";

        // Apply pagination limits
        $sql .= " LIMIT ?, ?"; // Use OFFSET and LIMIT

        $stmt = $conn->prepare($sql);
        
        // Dynamically bind parameters for category and pagination
        if ($category !== 'all') {
            $stmt->bind_param($param_types . 'ii', ...$params, $offset, $items_per_page);
        } else {
            $stmt->bind_param('ii', $offset, $items_per_page);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            $destinos = [];
            while ($row = $result->fetch_assoc()) {
                // Asegurarnos de que la ruta de la imagen es correcta para el frontend
                $row['imagen'] = 'assets/img/destinos/' . $row['imagen'];
                $destinos[] = $row;
            }
            $response['success'] = true;
            $response['data'] = $destinos;
            $response['total_destinos'] = $total_destinos; // Include total_destinos in response
        } else {
            $response['error'] = "Error en la consulta: " . $conn->error;
        }
    }
    $conn->close();
} else {
    $response['error'] = "Error de conexión a la base de datos.";
}

echo json_encode($response);
?>
