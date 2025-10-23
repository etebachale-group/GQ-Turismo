<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'data' => []];

if ($conn) {
    $sql = "SELECT id, nombre_agencia, descripcion, contacto_email, contacto_telefono, imagen_perfil FROM agencias ORDER BY nombre_agencia ASC";
    $result = $conn->query($sql);

    if ($result) {
        $agencias = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['imagen_perfil'])) {
                $row['imagen_perfil_url'] = 'assets/img/agencias/' . $row['imagen_perfil'];
            } else {
                $row['imagen_perfil_url'] = 'assets/img/agencias/default.jpg'; // Placeholder si no hay imagen
            }
            $agencias[] = $row;
        }
        $response['success'] = true;
        $response['data'] = $agencias;
    } else {
        $response['error'] = "Error en la consulta: " . $conn->error . " SQL: " . $sql;
    }
    $conn->close();
} else {
    $response['error'] = "Error de conexión a la base de datos.";
}

echo json_encode($response);
?>
