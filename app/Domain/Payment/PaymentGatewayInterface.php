<?php
declare(strict_types=1);

namespace App\Domain\Payment;

/**
 * Interfaz canónica para pasarelas y métodos de pago (M-10, M-11).
 */
interface PaymentGatewayInterface {
    /**
     * Procesa una solicitud de pago.
     * @param array $datos ['id_pedido', 'id_turista', 'monto', 'moneda', 'idempotency_key', ...]
     * @return array ['exito' => bool, 'id_transaccion' => string, 'mensaje' => string, 'estado' => string]
     */
    public function procesarPago(array $datos): array;

    /**
     * Verifica la autenticidad de un webhook entrante con firma criptográfica.
     * @param string $payload
     * @param string $firma
     * @return bool
     */
    public function verificarWebhook(string $payload, string $firma): bool;

    /**
     * Obtiene el identificador del método ('efectivo', 'transferencia', 'tarjeta').
     */
    public function obtenerMetodo(): string;
}
