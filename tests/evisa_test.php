<?php
declare(strict_types=1);

/**
 * Ejecutar: php tests/evisa_test.php
 */

require_once __DIR__ . '/../includes/evisa.php';

$fallos = 0;
$ok = 0;

function afirmar(bool $condicion, string $descripcion): void
{
    global $fallos, $ok;
    if ($condicion) {
        $ok++;
        echo "[OK]    {$descripcion}\n";
    } else {
        $fallos++;
        echo "[FALLO] {$descripcion}\n";
    }
}

$fuentes = require __DIR__ . '/../config/fuentes_oficiales.php';

foreach ($fuentes as $id => $fuente) {
    foreach ($fuente['urls'] as $lang => $url) {
        afirmar(
            !preg_match('/[?&](_gl|_ga|_gcl_au|utm_[a-z_]+)=|[?#]/i', $url),
            "[{$id}:{$lang}] sin parámetros de seguimiento"
        );
        afirmar(url_oficial_valida($url), "[{$id}:{$lang}] URL oficial válida");
    }
}

afirmar(!url_oficial_valida('http://egonline.vfsevisa.com/gnq'), 'rechaza http');
afirmar(!url_oficial_valida('https://sitio-falso.com/evisa'), 'rechaza dominio ajeno');
afirmar(!url_oficial_valida('https://egonline.vfsevisa.com/gnq?_ga=1'), 'rechaza parámetros de seguimiento');
afirmar(!url_oficial_valida('https://egonline.vfsevisa.com/gnq#x'), 'rechaza fragmento');
afirmar(fuente_oficial('evisa_gnq', 'es') !== null, 'evisa_gnq resuelve');
afirmar(fuente_oficial('evisa_gnq_referencia', 'es') === null, 'fuente inactiva no se publica');
afirmar(fuente_oficial('no_existe', 'es') === null, 'ID inexistente devuelve null');

$html = boton_evisa('en');
afirmar(str_contains($html, 'target="_blank"'), 'botón abre en pestaña nueva');
afirmar(str_contains($html, 'rel="noopener noreferrer"'), 'botón incluye rel seguro');
afirmar(str_contains($html, 'href="https://egonline.vfsevisa.com/'), 'botón apunta al dominio de la lista blanca');
afirmar(gq_h('<b>') === '&lt;b&gt;', 'gq_h escapa HTML');
afirmar(gq_lang('<img>') === 'es', 'idioma no válido cae a español');

foreach (['es', 'en', 'fr', 'pt'] as $lang) {
    afirmar(str_contains(boton_evisa($lang), 'href="'), "botón disponible en [{$lang}]");
}

echo "\nResultado: {$ok} correctas, {$fallos} fallidas.\n";
exit($fallos === 0 ? 0 : 1);
