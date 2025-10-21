<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
session_start();

$response = ['success' => false, 'data' => []];

if ($conn) {
    $sql = "SELECT id, nombre_agencia, descripcion, contacto_email, contacto_telefono FROM agencias ORDER BY nombre_agencia ASC";
    $result = $conn->query($sql);

    if ($result) {
        $agencias = [];
        while ($row = $result->fetch_assoc()) {
            $agencias[] = $row;
        }
        $response['success'] = true;
        $response['data'] = $agencias;
    } else {
        $response['error'] = "Error en la consulta: " . $conn->error;
    }
    $conn->close();
} else {
    $response['error'] = "Error de conexión a la base de datos.";
}

echo json_encode($response);
?>
