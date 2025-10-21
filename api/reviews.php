<?php
header('Content-Type: application/json');
session_start();
include '../includes/db_connect.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
        $response['message'] = 'Solo los turistas autenticados pueden enviar valoraciones.';
        echo json_encode($response);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $reviewer_id = $_SESSION['user_id'];
    $provider_id = $input['provider_id'] ?? null;
    $provider_type = $input['provider_type'] ?? null;
    $rating = $input['rating'] ?? null;
    $comment = $input['comment'] ?? null;

    if (!$provider_id || !$provider_type || !$rating) {
        $response['message'] = 'Datos incompletos para enviar la valoración.';
        echo json_encode($response);
        exit();
    }

    if ($rating < 1 || $rating > 5) {
        $response['message'] = 'La valoración debe ser entre 1 y 5 estrellas.';
        echo json_encode($response);
        exit();
    }

    // Check if the tourist has already reviewed this provider
    $stmt_check = $conn->prepare("SELECT id FROM valoraciones WHERE reviewer_id = ? AND provider_id = ? AND provider_type = ?");
    $stmt_check->bind_param("iis", $reviewer_id, $provider_id, $provider_type);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $response['message'] = 'Ya has enviado una valoración para este proveedor.';
        echo json_encode($response);
        exit();
    }
    $stmt_check->close();

    $stmt = $conn->prepare("INSERT INTO valoraciones (reviewer_id, provider_id, provider_type, rating, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisds", $reviewer_id, $provider_id, $provider_type, $rating, $comment);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Valoración enviada con éxito.';
    } else {
        $response['message'] = 'Error al enviar la valoración: ' . $stmt->error;
    }
    $stmt->close();

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $provider_id = $_GET['provider_id'] ?? null;
    $provider_type = $_GET['provider_type'] ?? null;

    if (!$provider_id || !$provider_type) {
        $response['message'] = 'Datos incompletos para obtener valoraciones.';
        echo json_encode($response);
        exit();
    }

    $reviews = [];
    $average_rating = 0;
    $total_reviews = 0;

    // Get all reviews for the provider
    $stmt_reviews = $conn->prepare("SELECT v.*, u.nombre as reviewer_name FROM valoraciones v JOIN usuarios u ON v.reviewer_id = u.id WHERE v.provider_id = ? AND v.provider_type = ? ORDER BY v.timestamp DESC");
    $stmt_reviews->bind_param("is", $provider_id, $provider_type);
    $stmt_reviews->execute();
    $result_reviews = $stmt_reviews->get_result();

    while ($row = $result_reviews->fetch_assoc()) {
        $reviews[] = $row;
    }
    $stmt_reviews->close();

    // Calculate average rating
    if (count($reviews) > 0) {
        $total_rating = 0;
        foreach ($reviews as $review) {
            $total_rating += $review['rating'];
        }
        $average_rating = $total_rating / count($reviews);
        $total_reviews = count($reviews);
    }

    $response['success'] = true;
    $response['reviews'] = $reviews;
    $response['average_rating'] = round($average_rating, 1);
    $response['total_reviews'] = $total_reviews;

} else {
    $response['message'] = 'Método no permitido.';
}

$conn->close();
echo json_encode($response);
?>