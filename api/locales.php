<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
session_start();

$response = ['success' => false, 'data' => []];

if ($conn) {
    $sql = "SELECT id, nombre_local, descripcion, tipo_local, direccion, contacto_email, contacto_telefono FROM lugares_locales ORDER BY nombre_local ASC";
    $result = $conn->query($sql);

    if ($result) {
        $locales = [];
        while ($row = $result->fetch_assoc()) {
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
}

echo json_encode($response);
?>
