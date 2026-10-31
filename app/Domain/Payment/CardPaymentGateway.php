<?php
declare(strict_types=1);

namespace App\Domain\Payment;

use mysqli;

/**
 * Pasarela de tarjeta con clave de idempotencia y validación criptográfica de webhook (M-11).
 */
class CardPaymentGateway implements PaymentGatewayInterface {
    private mysqli $db;
    private string $webhookSecret;

    public function __construct(mysqli $db, string $webhookSecret = 'gq_turismo_secret_webhook_key') {
        $this->db = $db;
        $this->webhookSecret = $webhookSecret;
    }

    public function obtenerMetodo(): string {
        return 'tarjeta';
    }

    public function procesarPago(array $datos): array {
        $idPedido = (int)($datos['id_pedido'] ?? 0);
        $idTurista = (int)($datos['id_turista'] ?? 0);
        $monto = (float)($datos['monto'] ?? 0.0);
        $moneda = $datos['moneda'] ?? 'XAF';
        $idempotencyKey = $datos['idempotency_key'] ?? bin2hex(random_bytes(16));

        if ($idPedido <= 0 || $monto <= 0) {
            return [
                'exito' => false,
                'id_transaccion' => '',
                'mensaje' => 'Parámetros de tarjeta inválidos.',
                'estado' => 'fallido',
            ];
        }

        // Idempotencia: Verificar si ya existe un registro con esta idempotency_key
        $check = $this->db->prepare("SELECT id, codigo_referencia, estado FROM pagos WHERE idempotency_key = ?");
        $check->bind_param("s", $idempotencyKey);
        $check->execute();
        $existente = $check->get_result()->fetch_assoc();
        $check->close();

        if ($existente) {
            return [
                'exito' => true,
                'id_transaccion' => $existente['codigo_referencia'],
                'id_pago' => (string)$existente['id'],
                'mensaje' => 'Pago recuperado por clave de idempotencia.',
                'estado' => $existente['estado'],
            ];
        }

        $codigoRef = 'GQ-CC-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        $stmt = $this->db->prepare("INSERT INTO pagos (id_pedido, id_turista, monto, moneda, metodo, estado, codigo_referencia, idempotency_key, notas) VALUES (?, ?, ?, ?, 'tarjeta', 'pendiente', ?, ?, 'Pago con tarjeta procesado')");
        $stmt->bind_param("iidsss", $idPedido, $idTurista, $monto, $moneda, $codigoRef, $idempotencyKey);
        
        if (!$stmt->execute()) {
            return [
                'exito' => false,
                'id_transaccion' => '',
                'mensaje' => 'Error al registrar la transacción de tarjeta.',
                'estado' => 'fallido',
            ];
        }

        $pagoId = (string)$stmt->insert_id;
        $stmt->close();

        return [
            'exito' => true,
            'id_transaccion' => $codigoRef,
            'id_pago' => $pagoId,
            'mensaje' => 'Transacción de tarjeta inicializada.',
            'estado' => 'pendiente',
        ];
    }

    /**
     * Valida la firma HMAC-SHA256 del webhook.
     */
    public function verificarWebhook(string $payload, string $firma): bool {
        if (empty($payload) || empty($firma)) {
            return false;
        }

        $calculada = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($calculada, $firma);
    }
}
