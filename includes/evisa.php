<?php
declare(strict_types=1);

/**
 * Botón que redirige al portal oficial de e-Visa.
 *
 * Uso:
 *   require_once __DIR__ . '/includes/evisa.php';
 *   echo boton_evisa($lang);
 *   echo boton_evisa($lang, 'btn btn--outline btn--block');
 */

const GQ_HOSTS_OFICIALES = [
    'egonline.vfsevisa.com',
    'equatorialguinea-evisa.com',
];

const GQ_LANGS = ['es', 'fr', 'pt', 'en'];

const GQ_TEXTOS_BOTON = [
    'es' => [
        'boton'   => 'Solicitar mi e-Visa en el sitio oficial',
        'externo' => 'sitio externo oficial, se abre en otra pestaña',
    ],
    'en' => [
        'boton'   => 'Apply for my e-Visa on the official site',
        'externo' => 'external official site, opens in a new tab',
    ],
    'fr' => [
        'boton'   => 'Demander mon e-Visa sur le site officiel',
        'externo' => 'site officiel externe, s’ouvre dans un nouvel onglet',
    ],
    'pt' => [
        'boton'   => 'Solicitar o meu e-Visa no site oficial',
        'externo' => 'site oficial externo, abre num novo separador',
    ],
];

/** Escapado HTML. Nombre propio para no chocar con otras funciones del proyecto. */
function gq_h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Idioma válido o español por defecto. */
function gq_lang(?string $lang): string
{
    return in_array($lang, GQ_LANGS, true) ? $lang : 'es';
}

/**
 * URL oficial válida: https, host de la lista blanca, sin query ni fragmento.
 */
function url_oficial_valida(string $url): bool
{
    $p = parse_url($url);
    if ($p === false) {
        return false;
    }
    if (($p['scheme'] ?? '') !== 'https') {
        return false;
    }
    if (!in_array($p['host'] ?? '', GQ_HOSTS_OFICIALES, true)) {
        return false;
    }
    return !isset($p['query']) && !isset($p['fragment']);
}

/**
 * URL de una fuente oficial por ID, o null si no existe, está inactiva
 * o no pasa la validación.
 */
function fuente_oficial(string $id, string $lang = 'es'): ?string
{
    static $fuentes = null;
    if ($fuentes === null) {
        $fuentes = require dirname(__DIR__) . '/config/fuentes_oficiales.php';
    }

    if (!isset($fuentes[$id]) || empty($fuentes[$id]['activo'])) {
        return null;
    }

    $lang = gq_lang($lang);
    $url = $fuentes[$id]['urls'][$lang] ?? $fuentes[$id]['urls']['es'] ?? null;

    return ($url !== null && url_oficial_valida($url)) ? $url : null;
}

/**
 * Botón HTML. Si la fuente no es válida, devuelve cadena vacía
 * para no mostrar nunca un enlace roto o no oficial.
 */
function boton_evisa(?string $lang = 'es', string $clase = 'btn btn--primary btn--block'): string
{
    $lang = gq_lang($lang);
    $url = fuente_oficial('evisa_gnq', $lang);

    if ($url === null) {
        return '';
    }

    $textos = GQ_TEXTOS_BOTON[$lang];

    return '<a class="' . gq_h($clase) . '"'
         . ' href="' . gq_h($url) . '"'
         . ' target="_blank" rel="noopener noreferrer">'
         . gq_h($textos['boton'])
         . '<span class="sr-only"> (' . gq_h($textos['externo']) . ')</span>'
         . '</a>';
}
