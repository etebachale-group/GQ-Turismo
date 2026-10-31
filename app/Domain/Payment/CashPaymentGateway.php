<?php
declare(strict_types=1);

namespace App\Domain\Payment;

use mysqli;
use RuntimeException;

/**
 * Pasarela de pago en efectivo al proveedor (M-10).
 * Registra el acuerdo de cobro y permite al proveedor certificar el abono en mano.
 */
class CashPaymentGateway implements PaymentGatewayInterface {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function obtenerMetodo(): string {
        return 'efectivo';
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
                'mensaje' => 'Datos de pedido o monto no válidos.',
                'estado' => 'fallido',
            ];
        }

        $codigoRef = 'GQ-EF-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        $stmt = $this->db->prepare("INSERT INTO pagos (id_pedido, id_turista, monto, moneda, metodo, estado, codigo_referencia, idempotency_key, notas) VALUES (?, ?, ?, ?, 'efectivo', 'pendiente', ?, ?, 'Acuerdo de pago en efectivo el día del servicio')");
        $stmt->bind_param("iidsss", $idPedido, $idTurista, $monto, $moneda, $codigoRef, $idempotencyKey);
        
        if (!$stmt->execute()) {
            return [
                'exito' => false,
                'id_transaccion' => '',
                'mensaje' => 'No se pudo registrar el pago: ' . $this->db->error,
                'estado' => 'fallido',
            ];
        }

        $pagoId = (string)$stmt->insert_id;
        $stmt->close();

        return [
            'exito' => true,
            'id_transaccion' => $codigoRef,
            'id_pago' => $pagoId,
            'mensaje' => 'Acuerdo de pago en efectivo registrado con éxito.',
            'estado' => 'pendiente',
        ];
    }

    public function verificarWebhook(string $payload, string $firma): bool {
        // Efectivo no usa webhooks de pasarelas remotas
        return true;
    }
}
