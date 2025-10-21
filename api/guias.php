<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
session_start();

$response = ['success' => false, 'data' => []];

if ($conn) {
    $sql = "SELECT id, nombre_guia, descripcion, especialidades, precio_hora, contacto_email, contacto_telefono FROM guias_turisticos ORDER BY nombre_guia ASC";
    $result = $conn->query($sql);

    if ($result) {
        $guias = [];
        while ($row = $result->fetch_assoc()) {
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
}

echo json_encode($response);
?>
