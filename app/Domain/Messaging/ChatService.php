<?php
declare(strict_types=1);

namespace App\Domain\Messaging;

use mysqli;
use InvalidArgumentException;
use RuntimeException;

/**
 * Servicio canónico de mensajería y chat (M-12).
 * 
 * Regla de identidad:
 * Todas las operaciones se realizan exclusivamente con identificadores de 'usuarios.id'.
 * Ninguna consulta compara usuarios.id con proveedores.id ni agencias.id.
 */
class ChatService {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /**
     * Obtiene o crea una conversación directa entre dos usuarios.
     */
    public function obtenerOCrearConversacion(int $usuario1Id, int $usuario2Id, ?int $pedidoId = null): int {
        if ($usuario1Id <= 0 || $usuario2Id <= 0 || $usuario1Id === $usuario2Id) {
            throw new InvalidArgumentException("Los identificadores de usuario deben ser válidos y distintos.");
        }

        // Buscar si ya existe una conversación con ambos participantes
        $sql = "
            SELECT c.id 
            FROM conversaciones c
            JOIN conversacion_participantes cp1 ON c.id = cp1.id_conversacion AND cp1.id_usuario = ?
            JOIN conversacion_participantes cp2 ON c.id = cp2.id_conversacion AND cp2.id_usuario = ?
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $usuario1Id, $usuario2Id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if ($row) {
            return (int)$row['id'];
        }

        // Crear nueva conversación
        $stmt_c = $this->db->prepare("INSERT INTO conversaciones (id_pedido, titulo) VALUES (?, ?)");
        $titulo = "Conversación entre #$usuario1Id y #$usuario2Id";
        $stmt_c->bind_param("is", $pedidoId, $titulo);
        if (!$stmt_c->execute()) {
            throw new RuntimeException("Error al crear conversación: " . $this->db->error);
        }
        $convId = $stmt_c->insert_id;
        $stmt_c->close();

        // Asociar participantes con su usuario_id canónico
        $stmt_p1 = $this->db->prepare("INSERT INTO conversacion_participantes (id_conversacion, id_usuario, rol) VALUES (?, ?, 'participante')");
        $stmt_p1->bind_param("ii", $convId, $usuario1Id);
        $stmt_p1->execute();
        $stmt_p1->close();

        $stmt_p2 = $this->db->prepare("INSERT INTO conversacion_participantes (id_conversacion, id_usuario, rol) VALUES (?, ?, 'participante')");
        $stmt_p2->bind_param("ii", $convId, $usuario2Id);
        $stmt_p2->execute();
        $stmt_p2->close();

        return (int)$convId;
    }

    /**
     * Envía un mensaje en la conversación.
     */
    public function enviarMensaje(int $conversacionId, int $emisorUsuarioId, string $texto): array {
        $texto = trim($texto);
        if ($texto === '') {
            throw new InvalidArgumentException("El mensaje no puede estar vacío.");
        }

        // Verificar que el emisor pertenezca a la conversación
        if (!$this->esParticipante($conversacionId, $emisorUsuarioId)) {
            throw new RuntimeException("El usuario #$emisorUsuarioId no pertenece a la conversación #$conversacionId.");
        }

        // Obtener el receptor en la conversación
        $stmt_rec = $this->db->prepare("SELECT id_usuario FROM conversacion_participantes WHERE id_conversacion = ? AND id_usuario != ? LIMIT 1");
        $stmt_rec->bind_param("ii", $conversacionId, $emisorUsuarioId);
        $stmt_rec->execute();
        $recRow = $stmt_rec->get_result()->fetch_assoc();
        $receptorId = $recRow ? (int)$recRow['id_usuario'] : $emisorUsuarioId;
        $stmt_rec->close();

        // Insertar en tabla mensajes con id_conversacion y IDs canónicos de usuarios
        $sql = "INSERT INTO mensajes (id_conversacion, sender_id, receiver_id, id_emisor, id_receptor, message, timestamp, is_read, leido) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 0, 0)";
        $stmt_m = $this->db->prepare($sql);
        $stmt_m->bind_param("iiiiis", $conversacionId, $emisorUsuarioId, $receptorId, $emisorUsuarioId, $receptorId, $texto);
        $stmt_m->execute();
        $mensajeId = $stmt_m->insert_id;
        $stmt_m->close();

        // Actualizar timestamp de la conversación
        $this->db->query("UPDATE conversaciones SET actualizado_en = NOW() WHERE id = $conversacionId");

        return [
            'id' => $mensajeId,
            'id_conversacion' => $conversacionId,
            'emisor_id' => $emisorUsuarioId,
            'receptor_id' => $receptorId,
            'mensaje' => $texto,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Obtiene los mensajes de una conversación.
     */
    public function obtenerMensajes(int $conversacionId, int $usuarioId, int $limite = 50): array {
        if (!$this->esParticipante($conversacionId, $usuarioId)) {
            throw new RuntimeException("Acceso denegado a los mensajes de la conversación.");
        }

        $sql = "
            SELECT m.id, m.id_conversacion, m.message, m.timestamp, m.is_read,
                   m.id_emisor, COALESCE(u.nombre, 'Usuario') as emisor_nombre
            FROM mensajes m
            LEFT JOIN usuarios u ON m.id_emisor = u.id
            WHERE m.id_conversacion = ?
            ORDER BY m.timestamp ASC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $conversacionId, $limite);
        $stmt->execute();
        $res = $stmt->get_result();
        $mensajes = [];
        while ($row = $res->fetch_assoc()) {
            $mensajes[] = $row;
        }
        $stmt->close();

        // Marcar como leídos los recibidos por el usuario
        $this->marcarLeidos($conversacionId, $usuarioId);

        return $mensajes;
    }

    /**
     * Marca mensajes de la conversación como leídos para un usuario.
     */
    public function marcarLeidos(int $conversacionId, int $usuarioId): void {
        $stmt = $this->db->prepare("UPDATE mensajes SET is_read = 1, leido = 1, fecha_lectura = NOW() WHERE id_conversacion = ? AND id_receptor = ? AND is_read = 0");
        $stmt->bind_param("ii", $conversacionId, $usuarioId);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Comprueba si un usuario es participante de la conversación.
     */
    public function esParticipante(int $conversacionId, int $usuarioId): bool {
        $stmt = $this->db->prepare("SELECT id FROM conversacion_participantes WHERE id_conversacion = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $conversacionId, $usuarioId);
        $stmt->execute();
        $es = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $es;
    }
}
