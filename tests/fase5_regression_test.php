<?php
declare(strict_types=1);

/**
 * Suite de Regresión Automatizada - Fase 5: Experiencia de Usuario Mobile-First y Pantallas Canónicas
 */

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

echo "=== VERIFICACIÓN FASE 5 (UX-01 a UX-07, CMP-05, CMP-06) ===\n\n";

// 1. CMP-05: Navegación inferior móvil (5 accesos y aria-current)
$footer = file_get_contents(__DIR__ . '/../includes/footer.php');
assert_test(strpos($footer, 'class="bottom-nav"') !== false, "CMP-05: footer.php contiene la barra .bottom-nav");
assert_test(strpos($footer, 'index.php') !== false && strpos($footer, 'destinos.php') !== false && strpos($footer, 'crear_itinerario.php') !== false && strpos($footer, 'mis_pedidos.php') !== false, "CMP-05: .bottom-nav contiene los accesos canónicos");
assert_test(strpos($footer, 'aria-current=') !== false, "CMP-05: los enlaces de bottom-nav implementan aria-current");

// 2. CMP-06: Modo sin conexión y control de red
assert_test(strpos($footer, 'id="gqOfflineAlert"') !== false, "CMP-06: footer.php incluye alerta de desconexión #gqOfflineAlert");
assert_test(strpos($footer, "window.addEventListener('offline'") !== false, "CMP-06: listener de evento offline registrado");

// 3. UX-01: Inicio (index.php)
$index = file_get_contents(__DIR__ . '/../index.php');
assert_test(strpos($index, 'destinos.php?region=bioko') !== false, "UX-01: index.php incluye acceso directo 'Explorar Bioko'");
assert_test(strpos($index, 'destinos.php?region=riomuni') !== false, "UX-01: index.php incluye acceso directo 'Explorar Río Muni'");
assert_test(strpos($index, 'Proveedores Verificados') !== false, "UX-01: index.php contiene la franja de confianza con proveedores verificados");
assert_test(strpos($index, 'Pago en Efectivo o Transferencia') !== false, "UX-01: index.php detalla pagos en FCFA (efectivo y transferencia)");
assert_test(strpos($index, 'boton_evisa') !== false, "UX-01: index.php incluye el botón oficial de e-Visa");

// 4. UX-02: Filtros regionales en destinos (destinos.php)
$destinos = file_get_contents(__DIR__ . '/../destinos.php');
assert_test(strpos($destinos, '$region_filtro') !== false, "UX-02: destinos.php procesa el parámetro regional");
assert_test(strpos($destinos, 'region=bioko') !== false && strpos($destinos, 'region=riomuni') !== false, "UX-02: destinos.php ofrece selectores de Bioko y Río Muni");

// 5. UX-04: Checkout (pagar.php)
$pagar = file_get_contents(__DIR__ . '/../pagar.php');
assert_test(strpos($pagar, 'verify_csrf') !== false, "UX-04: pagar.php valida token CSRF");
assert_test(strpos($pagar, 'Cancelación Flexible Garantizada') !== false, "UX-04: pagar.php detalla política de cancelación");
assert_test(strpos($pagar, 'metodo_efectivo') !== false && strpos($pagar, 'metodo_transferencia') !== false, "UX-04: pagar.php implementa efectivo y transferencia bancaria");
assert_test(strpos($pagar, 'INSERT INTO pagos') !== false, "UX-04: registrar pago crea registro en tabla canónica pagos");

// 6. UX-07: Confirmación de Reserva (confirmacion_reserva.php)
assert_test(file_exists(__DIR__ . '/../confirmacion_reserva.php'), "UX-07: Existe confirmacion_reserva.php");
$conf = file_get_contents(__DIR__ . '/../confirmacion_reserva.php');
assert_test(strpos($conf, 'wa.me') !== false, "UX-07: confirmacion_reserva.php incluye botón directo a WhatsApp con mensaje prellenado");
assert_test(strpos($conf, 'window.print()') !== false, "UX-07: confirmacion_reserva.php incluye botón de imprimir comprobante");
assert_test(strpos($conf, 'boton_evisa') !== false, "UX-07: confirmacion_reserva.php incluye recordatorio y botón oficial de e-Visa");

// 7. UX-05: Mi Reserva (mis_pedidos.php)
$pedidos = file_get_contents(__DIR__ . '/../mis_pedidos.php');
assert_test(strpos($pedidos, 'Próximo Paso:') !== false, "UX-05: mis_pedidos.php incluye bloque contextual de Próximo Paso");
assert_test(strpos($pedidos, 'confirmacion_reserva.php') !== false, "UX-05: mis_pedidos.php enlaza al comprobante/voucher oficial");
assert_test(strpos($pedidos, 'wa.me') !== false, "UX-05: mis_pedidos.php incluye contacto directo vía WhatsApp");

// 8. UX-06: Panel de Proveedor (admin/confirmacion_servicios.php)
$admin_conf = file_get_contents(__DIR__ . '/../admin/confirmacion_servicios.php');
assert_test(strpos($admin_conf, "require_once 'admin_header.php'") !== false, "UX-06: confirmacion_servicios.php utiliza admin_header.php");
assert_test(strpos($admin_conf, "require_once 'admin_footer.php'") !== false, "UX-06: confirmacion_servicios.php utiliza admin_footer.php");
assert_test(strpos($admin_conf, 'verify_csrf') !== false, "UX-06: confirmacion_servicios.php verifica token CSRF en POST");
assert_test(strpos($admin_conf, 'cobro_efectivo') !== false, "UX-06: confirmacion_servicios.php soporta registro de cobro en efectivo");

echo "\nResultado Fase 5: $passed pruebas correctas, $failed fallidas.\n";
exit($failed === 0 ? 0 : 1);
