<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$id_destino = $_GET['id_destino'] ?? null;
$tipo = $_GET['tipo'] ?? 'guia'; // guia, agencia, local

if (!$id_destino) {
    echo json_encode(['success' => false, 'message' => 'ID de destino requerido']);
    exit();
}

$response = ['success' => true, 'proveedores' => []];

try {
    switch ($tipo) {
        case 'guia':
            $stmt = $conn->prepare("
                SELECT DISTINCT gt.id, gt.nombre_guia, gt.descripcion, gt.tarifa_por_dia,
                       gt.contacto_telefono, gt.especialidad, gt.imagen, gt.calificacion,
                       gd.tarifa_especial, gd.descripcion_servicio
                FROM guias_turisticos gt
                JOIN guia_destinos gd ON gt.id = gd.id_guia
                WHERE gd.id_destino = ? AND gd.disponible = 1
                ORDER BY gt.calificacion DESC, gt.nombre_guia ASC
            ");
            $stmt->bind_param("i", $id_destino);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $row['tarifa'] = $row['tarifa_especial'] ?? $row['tarifa_por_dia'];
                $response['proveedores'][] = $row;
            }
            $stmt->close();
            break;
            
        case 'agencia':
            $stmt = $conn->prepare("
                SELECT DISTINCT a.id, a.nombre_agencia, a.descripcion, a.contacto_email,
                       a.contacto_telefono, a.direccion, a.imagen, a.calificacion,
                       ad.tarifa_especial, ad.descripcion_servicio
                FROM agencias a
                JOIN agencia_destinos ad ON a.id = ad.id_agencia
                WHERE ad.id_destino = ? AND ad.disponible = 1
                ORDER BY a.calificacion DESC, a.nombre_agencia ASC
            ");
            $stmt->bind_param("i", $id_destino);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $response['proveedores'][] = $row;
            }
            $stmt->close();
            break;
            
        case 'local':
            $stmt = $conn->prepare("
                SELECT DISTINCT ll.id, ll.nombre_local, ll.tipo_local, ll.descripcion,
                       ll.direccion, ll.contacto_telefono, ll.horario, ll.imagen, ll.calificacion,
                       ld.tarifa_especial, ld.descripcion_servicio
                FROM lugares_locales ll
                JOIN local_destinos ld ON ll.id = ld.id_local
                WHERE ld.id_destino = ? AND ld.disponible = 1
                ORDER BY ll.calificacion DESC, ll.nombre_local ASC
            ");
            $stmt->bind_param("i", $id_destino);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $response['proveedores'][] = $row;
            }
            $stmt->close();
            break;
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Error al obtener proveedores: ' . $e->getMessage()
    ];
}

echo json_encode($response);
$conn->close();
?>
