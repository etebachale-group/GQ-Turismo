<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$response = ['success' => false, 'data' => []];

// Usamos la conexión $conn que viene de db_connect.php
if ($conn) {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;

    $sql = "SELECT id, nombre, descripcion, imagen, categoria, precio FROM destinos ORDER BY RAND()";

    if ($limit > 0) {
        $sql .= " LIMIT ?";
    }

    $stmt = $conn->prepare($sql);

    if ($limit > 0) {
        $stmt->bind_param('i', $limit);
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
    } else {
        $response['error'] = "Error en la consulta: " . $conn->error;
    }
    $conn->close();
} else {
    $response['error'] = "Error de conexión a la base de datos.";
}

echo json_encode($response);
?>
