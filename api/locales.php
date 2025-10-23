<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'data' => []];

if ($conn) {
    $city = $_GET['city'] ?? '';

    $sql = "SELECT l.id, l.nombre_local, l.descripcion, l.tipo_local, l.direccion, l.contacto_email, l.contacto_telefono, l.imagen_perfil FROM lugares_locales l";
    $params = [];
    $param_types = '';

    if (!empty($city)) {
        $sql .= " WHERE l.ciudad LIKE ?";
        $params[] = "%{$city}%";
        $param_types .= 's';
    }

    $sql .= " ORDER BY l.nombre_local ASC";

    $stmt = $conn->prepare($sql);

    if (!empty($city)) {
        $stmt->bind_param($param_types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $locales = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['imagen_perfil'])) {
                $row['imagen_perfil_url'] = 'assets/img/locales/' . $row['imagen_perfil'];
            } else {
                $row['imagen_perfil_url'] = 'assets/img/locales/default.jpg';
            }
            $locales[] = $row;
        }
        $response['success'] = true;
        $response['data'] = $locales;
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
