<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'data' => []];

if ($conn) {
    $city = $_GET['city'] ?? '';

    $sql = "SELECT g.id, g.nombre_guia, g.descripcion, g.especialidades, g.precio_hora, g.contacto_email, g.contacto_telefono, g.imagen_perfil FROM guias_turisticos g";
    $params = [];
    $param_types = '';

    if (!empty($city)) {
        $sql .= " WHERE g.ciudad_operacion LIKE ?";
        $params[] = "%{$city}%";
        $param_types .= 's';
    }

    $sql .= " ORDER BY g.nombre_guia ASC";

    $stmt = $conn->prepare($sql);

    if (!empty($city)) {
        $stmt->bind_param($param_types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $guias = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['imagen_perfil'])) {
                $row['imagen_perfil_url'] = 'assets/img/guias/' . $row['imagen_perfil'];
            } else {
                $row['imagen_perfil_url'] = 'assets/img/guias/default.jpg';
            }
            $guias[] = $row;
        }
        $response['success'] = true;
        $response['data'] = $guias;
    } else {
        $response['error'] = "Error en la consulta: " . $conn->error;
    }
    $conn->close();
} else {
    $response['error'] = "Error de conexión a la base de datos.";
    error_log("Database connection error.");
}

echo json_encode($response);
?>
