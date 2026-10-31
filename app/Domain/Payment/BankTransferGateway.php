<?php
declare(strict_types=1);

namespace App\Domain\Payment;

use mysqli;

/**
 * Pasarela de transferencia bancaria con referencia y validación de comprobante (M-10).
 */
class BankTransferGateway implements PaymentGatewayInterface {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function obtenerMetodo(): string {
        return 'transferencia';
    }

    public function procesarPago(array $datos): array {
        $idPedido = (int)($datos['id_pedido'] ?? 0);
        $idTurista = (int)($datos['id_turista'] ?? 0);
        $monto = (float)($datos['monto'] ?? 0.0);
        $moneda = $datos['moneda'] ?? 'XAF';
        $comprobante = $datos['comprobante_ruta'] ?? null;
        $idempotencyKey = $datos['idempotency_key'] ?? bin2hex(random_bytes(16));

        if ($idPedido <= 0 || $monto <= 0) {
            return [
                'exito' => false,
                'id_transaccion' => '',
                'mensaje' => 'Datos de pedido o monto no válidos.',
                'estado' => 'fallido',
            ];
        }

        $codigoRef = 'GQ-TB-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $estado = !empty($comprobante) ? 'verificando' : 'pendiente';

        $stmt = $this->db->prepare("INSERT INTO pagos (id_pedido, id_turista, monto, moneda, metodo, estado, codigo_referencia, comprobante_ruta, idempotency_key, notas) VALUES (?, ?, ?, ?, 'transferencia', ?, ?, ?, ?, 'Transferencia bancaria local')");
        $stmt->bind_param("iidsssss", $idPedido, $idTurista, $monto, $moneda, $estado, $codigoRef, $comprobante, $idempotencyKey);
        
        if (!$stmt->execute()) {
            return [
                'exito' => false,
                'id_transaccion' => '',
                'mensaje' => 'No se pudo registrar la transferencia: ' . $this->db->error,
                'estado' => 'fallido',
            ];
        }

        $pagoId = (string)$stmt->insert_id;
        $stmt->close();

        return [
            'exito' => true,
            'id_transaccion' => $codigoRef,
            'id_pago' => $pagoId,
            'mensaje' => 'Transferencia bancaria iniciada con éxito. Por favor adjunta o conserva tu justificante.',
            'estado' => $estado,
        ];
    }

    public function verificarWebhook(string $payload, string $firma): bool {
        return true;
    }
}
