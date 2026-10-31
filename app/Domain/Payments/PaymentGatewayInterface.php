<?php

namespace App\Domain\Payments;

interface PaymentGatewayInterface {
    /**
     * Inicia una sesión o intento de pago para una reserva/pedido.
     */
    public function createPaymentIntent(int $orderId, float $amount, string $currency, array $metadata = []): array;

    /**
     * Confirma o procesa el pago.
     */
    public function confirmPayment(int $orderId, array $paymentData = []): array;

    /**
     * Valida webhooks entrantes para evitar fraudes o duplicados.
     */
    public function verifyWebhook(string $payload, string $signature): bool;
}
