<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
session_start();

$response = ['success' => false, 'message' => 'Invalid request'];

// Check if user is logged in and is a 'turista'
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    $response['message'] = 'Acceso denegado. Solo los turistas pueden gestionar itinerarios.';
    echo json_encode($response);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if ($input && isset($input['action'])) {
    switch ($input['action']) {
        case 'create_itinerary':
            if (isset($input['name'], $input['destinations']) && !empty($input['name']) && count($input['destinations']) > 0) {
                $id_usuario = $_SESSION['user_id'];
                $nombre_itinerario = htmlspecialchars($input['name']);
                $destinations = $input['destinations'];

                $conn->begin_transaction();

                try {
                    // Insert into itineraries table
                    $stmt = $conn->prepare("INSERT INTO itinerarios (id_usuario, nombre_itinerario) VALUES (?, ?)");
                    $stmt->bind_param("is", $id_usuario, $nombre_itinerario);
                    $stmt->execute();
                    $itinerary_id = $stmt->insert_id;
                    $stmt->close();

                    // Insert into itinerario_destinos table
                    $stmt_dest = $conn->prepare("INSERT INTO itinerario_destinos (id_itinerario, id_destino, orden) VALUES (?, ?, ?)");
                    foreach ($destinations as $index => $dest_id) {
                        $order = $index + 1;
                        $stmt_dest->bind_param("iii", $itinerary_id, $dest_id, $order);
                        $stmt_dest->execute();
                    }
                    $stmt_dest->close();

                    $conn->commit();
                    $response['success'] = true;
                    $response['message'] = '¡Itinerario creado con éxito!';

                } catch (Exception $e) {
                    $conn->rollback();
                    $response['message'] = 'Error al crear el itinerario: ' . $e->getMessage();
                }

            } else {
                $response['message'] = 'Datos incompletos para crear el itinerario.';
            }
            break;

        case 'delete_itinerary':
            if (isset($input['id'])) {
                $id_itinerario = (int)$input['id'];
                $id_usuario = $_SESSION['user_id'];

                // We only need to delete from the 'itinerarios' table because of CASCADE DELETE
                $stmt = $conn->prepare("DELETE FROM itinerarios WHERE id = ? AND id_usuario = ?");
                $stmt->bind_param("ii", $id_itinerario, $id_usuario);
                
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $response['success'] = true;
                        $response['message'] = 'Itinerario eliminado con éxito.';
                    } else {
                        $response['message'] = 'No se encontró el itinerario o no tienes permiso para eliminarlo.';
                    }
                } else {
                    $response['message'] = 'Error al eliminar el itinerario.';
                }
                $stmt->close();

            } else {
                $response['message'] = 'ID de itinerario no proporcionado.';
            }
            break;

        default:
            $response['message'] = 'Acción no reconocida.';
            break;
    }
}

echo json_encode($response);
?>
