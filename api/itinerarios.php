<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';

$response = ['success' => false, 'message' => ''];

// Verificar autenticación
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    $response['message'] = 'No autorizado';
    echo json_encode($response);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'];

// CREAR/ACTUALIZAR ITINERARIO
if ($method === 'POST') {
    $itinerario_id = $_POST['itinerario_id'] ?? null;
    $nombre_itinerario = $_POST['nombre_itinerario'] ?? '';
    $estado = $_POST['estado'] ?? 'planificacion';
    $fecha_inicio = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
    $fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;
    $presupuesto_estimado = $_POST['presupuesto_estimado'] ?? 0;
    $ciudad = $_POST['ciudad'] ?? '';
    $notas = $_POST['notas'] ?? '';
    $destinos = json_decode($_POST['destinos'] ?? '[]', true);
    $servicios = json_decode($_POST['servicios'] ?? '{}', true);

    if (empty($nombre_itinerario)) {
        $response['message'] = 'El nombre del itinerario es obligatorio';
        echo json_encode($response);
        exit;
    }

    if (empty($destinos) || !is_array($destinos)) {
        $response['message'] = 'Debes seleccionar al menos un destino';
        echo json_encode($response);
        exit;
    }

    // Calcular precio total de destinos
    $precio_total = 0;
    if (count($destinos) > 0) {
        $placeholders = str_repeat('?,', count($destinos) - 1) . '?';
        $stmt_precio = $conn->prepare("SELECT SUM(precio) as total FROM destinos WHERE id IN ($placeholders)");
        $stmt_precio->bind_param(str_repeat('i', count($destinos)), ...$destinos);
        $stmt_precio->execute();
        $result_precio = $stmt_precio->get_result();
        if ($row = $result_precio->fetch_assoc()) {
            $precio_total = $row['total'] ?? 0;
        }
        $stmt_precio->close();
    }

    $conn->begin_transaction();

    try {
        if ($itinerario_id) {
            // ACTUALIZAR
            $sql = "UPDATE itinerarios SET
                    nombre_itinerario = ?,
                    estado = ?,
                    fecha_inicio = ?,
                    fecha_fin = ?,
                    presupuesto_estimado = ?,
                    ciudad = ?,
                    notas = ?,
                    precio_total = ?
                    WHERE id = ? AND id_usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssdssdii", $nombre_itinerario, $estado, $fecha_inicio, $fecha_fin,
                            $presupuesto_estimado, $ciudad, $notas, $precio_total, $itinerario_id, $user_id);
            $stmt->execute();
            $stmt->close();

            // Eliminar destinos y servicios anteriores
            $conn->query("DELETE FROM itinerario_destinos WHERE id_itinerario = $itinerario_id");
            $conn->query("DELETE FROM itinerario_servicios WHERE id_itinerario = $itinerario_id");

            $response['message'] = 'Itinerario actualizado exitosamente';
        } else {
            // CREAR
            $sql = "INSERT INTO itinerarios (id_usuario, nombre_itinerario, estado, fecha_inicio, fecha_fin,
                    presupuesto_estimado, ciudad, notas, precio_total)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssdssd", $user_id, $nombre_itinerario, $estado, $fecha_inicio, $fecha_fin,
                            $presupuesto_estimado, $ciudad, $notas, $precio_total);
            $stmt->execute();
            $itinerario_id = $conn->insert_id;
            $stmt->close();

            $response['message'] = 'Itinerario creado exitosamente';
        }

        // Insertar destinos con orden
        if (!empty($destinos) && is_array($destinos)) {
            $stmt_destinos = $conn->prepare("INSERT INTO itinerario_destinos (id_itinerario, id_destino, orden) VALUES (?, ?, ?)");
            foreach ($destinos as $orden => $destino_id) {
                $orden_num = $orden + 1;
                $stmt_destinos->bind_param("iii", $itinerario_id, $destino_id, $orden_num);
                $stmt_destinos->execute();
            }
            $stmt_destinos->close();
        }

        // Guardar servicios seleccionados (opcional)
        if (!empty($servicios)) {
            $stmt_servicios = $conn->prepare("INSERT INTO itinerario_servicios (id_itinerario, tipo_servicio, id_servicio) VALUES (?, ?, ?)");
            
            // Guías
            if (!empty($servicios['guias']) && is_array($servicios['guias'])) {
                foreach ($servicios['guias'] as $guia_id) {
                    $tipo = 'guia';
                    $stmt_servicios->bind_param("isi", $itinerario_id, $tipo, $guia_id);
                    $stmt_servicios->execute();
                }
            }

            // Agencias
            if (!empty($servicios['agencias']) && is_array($servicios['agencias'])) {
                foreach ($servicios['agencias'] as $agencia_id) {
                    $tipo = 'agencia';
                    $stmt_servicios->bind_param("isi", $itinerario_id, $tipo, $agencia_id);
                    $stmt_servicios->execute();
                }
            }

            // Locales
            if (!empty($servicios['locales']) && is_array($servicios['locales'])) {
                foreach ($servicios['locales'] as $local_id) {
                    $tipo = 'local';
                    $stmt_servicios->bind_param("isi", $itinerario_id, $tipo, $local_id);
                    $stmt_servicios->execute();
                }
            }
            
            $stmt_servicios->close();
        }

        $conn->commit();
        $response['success'] = true;
        $response['data'] = ['id' => $itinerario_id];

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Error al guardar el itinerario: ' . $e->getMessage();
    }
}

// ELIMINAR ITINERARIO
else if ($method === 'DELETE') {
    $action = $_GET['action'] ?? '';
    $id = $_GET['id'] ?? null;

    if ($action === 'delete' && $id) {
        $id = (int)$id;

        // Verificar que el itinerario pertenece al usuario
        $stmt_check = $conn->prepare("SELECT id FROM itinerarios WHERE id = ? AND id_usuario = ?");
        $stmt_check->bind_param("ii", $id, $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows === 0) {
            $response['message'] = 'Itinerario no encontrado o no tienes permiso';
            echo json_encode($response);
            exit;
        }
        $stmt_check->close();

        $conn->begin_transaction();

        try {
            // Eliminar registros relacionados (las foreign keys con CASCADE lo hacen automáticamente)
            $conn->query("DELETE FROM itinerario_destinos WHERE id_itinerario = $id");
            $conn->query("DELETE FROM itinerario_servicios WHERE id_itinerario = $id");

            // Eliminar itinerario
            $stmt_delete = $conn->prepare("DELETE FROM itinerarios WHERE id = ? AND id_usuario = ?");
            $stmt_delete->bind_param("ii", $id, $user_id);
            $stmt_delete->execute();
            $stmt_delete->close();

            $conn->commit();
            $response['success'] = true;
            $response['message'] = 'Itinerario eliminado exitosamente';

        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Error al eliminar el itinerario: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Parámetros inválidos';
    }
}

// OBTENER ITINERARIOS
else if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'list') {
        $sql = "SELECT i.*,
                COUNT(DISTINCT id_dest.id_destino) as total_destinos
                FROM itinerarios i
                LEFT JOIN itinerario_destinos id_dest ON i.id = id_dest.id_itinerario
                WHERE i.id_usuario = ?
                GROUP BY i.id
                ORDER BY i.fecha_creacion DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $itinerarios = [];
        while ($row = $result->fetch_assoc()) {
            $itinerarios[] = $row;
        }
        $stmt->close();

        $response['success'] = true;
        $response['data'] = $itinerarios;
    }
    else if ($action === 'get_one') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM itinerarios WHERE id = ? AND id_usuario = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                // Obtener destinos
                $stmt_dest = $conn->prepare("SELECT id_destino FROM itinerario_destinos WHERE id_itinerario = ? ORDER BY orden ASC");
                $stmt_dest->bind_param("i", $id);
                $stmt_dest->execute();
                $result_dest = $stmt_dest->get_result();
                $destinos = [];
                while ($row_dest = $result_dest->fetch_assoc()) {
                    $destinos[] = $row_dest['id_destino'];
                }
                $row['destinos'] = $destinos;
                $stmt_dest->close();
                
                // Obtener servicios
                $stmt_serv = $conn->prepare("SELECT tipo_servicio, id_servicio FROM itinerario_servicios WHERE id_itinerario = ?");
                $stmt_serv->bind_param("i", $id);
                $stmt_serv->execute();
                $result_serv = $stmt_serv->get_result();
                $servicios = ['guias' => [], 'agencias' => [], 'locales' => []];
                while ($row_serv = $result_serv->fetch_assoc()) {
                    $key = $row_serv['tipo_servicio'] . 's';
                    // Corregir para locales
                    if ($key === 'locals') $key = 'locales';
                    $servicios[$key][] = $row_serv['id_servicio'];
                }
                $row['servicios'] = $servicios;
                $stmt_serv->close();

                $response['success'] = true;
                $response['data'] = $row;
            } else {
                $response['message'] = 'Itinerario no encontrado';
            }
            $stmt->close();
        }
    }
}

else {
    $response['message'] = 'Método no permitido';
}

echo json_encode($response);
$conn->close();
?>
