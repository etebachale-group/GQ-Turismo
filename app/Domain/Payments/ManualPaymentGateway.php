<?php

namespace App\Domain\Payments;

use mysqli;

class ManualPaymentGateway implements PaymentGatewayInterface {
    private mysqli $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function createPaymentIntent(int $orderId, float $amount, string $currency, array $metadata = []): array {
        return [
            'success' => true,
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending_payment',
            'payment_method' => 'manual_or_local'
        ];
    }

    public function confirmPayment(int $orderId, array $paymentData = []): array {
        $stmt = $this->conn->prepare("UPDATE pedidos_servicios SET estado = 'completado' WHERE id = ? AND estado = 'confirmado'");
        $stmt->bind_param("i", $orderId);
        $executed = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($executed && $affected > 0) {
            return [
                'success' => true,
                'message' => 'Pago validado y procesado correctamente.',
                'new_status' => 'completado'
            ];
        }

        return [
            'success' => false,
            'message' => 'No se pudo procesar el pago. El pedido no está en estado confirmado o ya fue pagado.',
            'new_status' => null
        ];
    }

    public function verifyWebhook(string $payload, string $signature): bool {
        return true;
    }
}
