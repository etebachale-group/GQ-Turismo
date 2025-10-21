<?php
header('Content-Type: application/json');
session_start();
include '../includes/db_connect.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Solo guías autenticados pueden actualizar su ubicación
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'guia') {
        $response['message'] = 'Acceso denegado. Solo los guías pueden actualizar su ubicación.';
        echo json_encode($response);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $guia_id = $input['guia_id'] ?? null;
    $latitude = $input['latitude'] ?? null;
    $longitude = $input['longitude'] ?? null;

    // Verificar que el guia_id corresponde al usuario logueado
    $stmt_check_guia = $conn->prepare("SELECT id FROM guias_turisticos WHERE id = ? AND id_usuario = ?");
    $stmt_check_guia->bind_param("ii", $guia_id, $_SESSION['user_id']);
    $stmt_check_guia->execute();
    $stmt_check_guia->store_result();
    if ($stmt_check_guia->num_rows === 0) {
        $response['message'] = 'ID de guía no válido o no autorizado.';
        echo json_encode($response);
        exit();
    }
    $stmt_check_guia->close();

    if ($guia_id === null || $latitude === null || $longitude === null) {
        $response['message'] = 'Datos incompletos para actualizar la ubicación.';
        echo json_encode($response);
        exit();
    }

    $stmt = $conn->prepare("UPDATE guias_turisticos SET current_latitude = ?, current_longitude = ?, last_updated = NOW() WHERE id = ?");
    $stmt->bind_param("ddi", $latitude, $longitude, $guia_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Ubicación actualizada con éxito.';
    } else {
        $response['message'] = 'Error al actualizar la ubicación: ' . $stmt->error;
    }
    $stmt->close();

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $guia_id = $_GET['guia_id'] ?? null;

    if ($guia_id === null) {
        $response['message'] = 'ID de guía no proporcionado.';
        echo json_encode($response);
        exit();
    }

    $stmt = $conn->prepare("SELECT current_latitude, current_longitude, last_updated FROM guias_turisticos WHERE id = ?");
    $stmt->bind_param("i", $guia_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $location_data = $result->fetch_assoc();
        $response['success'] = true;
        $response['location'] = $location_data;
    } else {
        $response['message'] = 'Guía no encontrado o sin datos de ubicación.';
    }
    $stmt->close();

} else {
    $response['message'] = 'Método no permitido.';
}

$conn->close();
echo json_encode($response);
?>