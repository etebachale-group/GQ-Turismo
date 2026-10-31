<?php
declare(strict_types=1);

/**
 * Suite de Regresión Automatizada - Fase 7: Módulos Núcleo (Core Domain Modernization)
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';

use App\Domain\Booking\BookingStateMachine;
use App\Domain\Booking\PriceCalculator;
use App\Domain\Payment\PaymentGatewayInterface;
use App\Domain\Payment\CashPaymentGateway;
use App\Domain\Payment\BankTransferGateway;
use App\Domain\Payment\CardPaymentGateway;
use App\Domain\Messaging\ChatService;
use App\Domain\Destination\DestinationService;

$passed = 0;
$failed = 0;

function assert_test(bool $cond, string $desc): void {
    global $passed, $failed;
    if ($cond) {
        echo "[OK]   $desc\n";
        $passed++;
    } else {
        echo "[FAIL] $desc\n";
        $failed++;
    }
}

echo "=== VERIFICACIÓN FASE 7 (M-01 a M-16: MÓDULOS NÚCLEO) ===\n\n";

// --- 1. M-08 / Sección 3.5: Máquina de estados de reservas (BookingStateMachine) ---
echo "[M-08] Máquina de Estados de Reservas:\n";
assert_test(BookingStateMachine::esTransicionValida('borrador', 'pendiente_confirmacion'), "Transición permitida: borrador -> pendiente_confirmacion");
assert_test(BookingStateMachine::esTransicionValida('pendiente_confirmacion', 'confirmado'), "Transición permitida: pendiente_confirmacion -> confirmado");
assert_test(BookingStateMachine::esTransicionValida('confirmado', 'pagado'), "Transición permitida: confirmado -> pagado");
assert_test(BookingStateMachine::esTransicionValida('pagado', 'en_curso'), "Transición permitida: pagado -> en_curso");
assert_test(BookingStateMachine::esTransicionValida('en_curso', 'completado'), "Transición permitida: en_curso -> completado");
assert_test(BookingStateMachine::esTransicionValida('confirmado', 'cancelado'), "Transición permitida: confirmado -> cancelado");
assert_test(BookingStateMachine::esTransicionValida('pagado', 'reembolsado'), "Transición permitida: pagado -> reembolsado");

$exLanzada = false;
try {
    BookingStateMachine::transicionar('borrador', 'pagado');
} catch (\DomainException $e) {
    $exLanzada = true;
}
assert_test($exLanzada, "Transición inválida directa (borrador -> pagado) lanza DomainException");

$exCancelado = false;
try {
    BookingStateMachine::transicionar('cancelado', 'confirmado');
} catch (\DomainException $e) {
    $exCancelado = true;
}
assert_test($exCancelado, "Estado terminal (cancelado -> confirmado) lanza DomainException");

// --- 2. M-10 y M-11: Pasarelas de Pago Canónicas (PaymentGateways) ---
echo "\n[M-10 / M-11] Pasarelas de Pago y Webhooks:\n";
$cashGw = new CashPaymentGateway($conn);
$bankGw = new BankTransferGateway($conn);
$cardGw = new CardPaymentGateway($conn, 'secret_hmac_test_key_123');

assert_test($cashGw instanceof PaymentGatewayInterface, "CashPaymentGateway implementa PaymentGatewayInterface");
assert_test($bankGw instanceof PaymentGatewayInterface, "BankTransferGateway implementa PaymentGatewayInterface");
assert_test($cardGw instanceof PaymentGatewayInterface, "CardPaymentGateway implementa PaymentGatewayInterface");

// Probar pago en efectivo
$resCash = $cashGw->procesarPago([
    'id_pedido' => 999901,
    'id_turista' => 1,
    'monto' => 35000.00,
    'moneda' => 'XAF'
]);
assert_test($resCash['exito'] === true, "CashPaymentGateway procesa solicitud con éxito");
assert_test(strpos($resCash['id_transaccion'], 'GQ-EF-') === 0, "CashPaymentGateway genera código GQ-EF-...");

// Probar transferencia bancaria
$resBank = $bankGw->procesarPago([
    'id_pedido' => 999902,
    'id_turista' => 1,
    'monto' => 45000.00,
    'moneda' => 'XAF'
]);
assert_test($resBank['exito'] === true, "BankTransferGateway procesa solicitud con éxito");
assert_test(strpos($resBank['id_transaccion'], 'GQ-TB-') === 0, "BankTransferGateway genera código GQ-TB-...");

// Probar tarjeta e idempotencia
$idempotencyKey = 'idem_' . bin2hex(random_bytes(8));
$resCard1 = $cardGw->procesarPago([
    'id_pedido' => 999903,
    'id_turista' => 1,
    'monto' => 50000.00,
    'moneda' => 'XAF',
    'idempotency_key' => $idempotencyKey
]);
$resCard2 = $cardGw->procesarPago([
    'id_pedido' => 999903,
    'id_turista' => 1,
    'monto' => 50000.00,
    'moneda' => 'XAF',
    'idempotency_key' => $idempotencyKey
]);
assert_test($resCard1['exito'] === true, "CardPaymentGateway procesa transacción correctamente");
assert_test($resCard1['id_transaccion'] === $resCard2['id_transaccion'], "Idempotencia: doble envío con misma clave devuelve misma transacción");

// Verificación criptográfica de Webhook HMAC
$payload = json_encode(['evento' => 'pago.confirmado', 'monto' => 50000]);
$firmaValida = hash_hmac('sha256', $payload, 'secret_hmac_test_key_123');
assert_test($cardGw->verificarWebhook($payload, $firmaValida), "CardPaymentGateway valida firma HMAC de webhook válida");
assert_test(!$cardGw->verificarWebhook($payload, 'firma_invalida_falsificada'), "CardPaymentGateway rechaza webhook con firma manipulada");

// --- 3. M-12: Chat Canónico por usuarios.id (ChatService) ---
echo "\n[M-12] Chat Canónico (Identidad por usuarios.id):\n";
$chatService = new ChatService($conn);

// Obtener o crear conversación entre usuario 1 y usuario 2
$convId = $chatService->obtenerOCrearConversacion(1, 2, 999901);
assert_test($convId > 0, "ChatService crea u obtiene conversación canónica");

$convId2 = $chatService->obtenerOCrearConversacion(1, 2, 999901);
assert_test($convId === $convId2, "ChatService es idempotente al buscar conversación existente entre usuarios");

// Enviar mensaje
$msg = $chatService->enviarMensaje($convId, 1, "Hola, mensaje de verificación de fase 7");
assert_test($msg['id'] > 0, "ChatService envía y persiste mensaje con id_conversacion");

// Obtener mensajes
$mensajes = $chatService->obtenerMensajes($convId, 2);
assert_test(count($mensajes) > 0, "Receptor obtiene los mensajes de la conversación");

// Acceso denegado a tercero
$exTercero = false;
try {
    $chatService->obtenerMensajes($convId, 99999);
} catch (\RuntimeException $e) {
    $exTercero = true;
}
assert_test($exTercero, "Usuario ajeno a la conversación recibe excepción de acceso denegado");

// --- 4. M-01: Servicio de Destinos y Sitemap (DestinationService) ---
echo "\n[M-01] Destinos y Sitemap XML:\n";
$destService = new DestinationService($conn);
$slug1 = DestinationService::generarSlug('Parque Nacional de Monte Alén');
assert_test($slug1 === 'parque-nacional-de-monte-alen', "Slug de destino sin acentos ni espacios: 'parque-nacional-de-monte-alen'");

$slug2 = DestinationService::generarSlug('¡Cascadas de Moka & Lago de Ilachi!');
assert_test($slug2 === 'cascadas-de-moka-lago-de-ilachi', "Slug limpia símbolos especiales correctamente");

$sitemap = $destService->generarSitemapXml('http://localhost/GQ-Turismo');
assert_test(strpos($sitemap, '<?xml version="1.0" encoding="UTF-8"?>') !== false, "Sitemap contiene cabecera XML estándar");
assert_test(strpos($sitemap, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">') !== false, "Sitemap contiene namespace oficial sitemaps.org");
assert_test(strpos($sitemap, 'detalle_destino.php?id=') !== false, "Sitemap contiene enlaces indexables a los destinos");

// Limpiar datos de prueba de pagos creados para tests
$conn->query("DELETE FROM pagos WHERE id_pedido IN (999901, 999902, 999903)");

echo "\nResultado Fase 7: $passed pruebas correctas, $failed fallidas.\n";
exit($failed === 0 ? 0 : 1);
