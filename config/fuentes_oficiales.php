<?php
/**
 * Registro central de enlaces oficiales del trámite de e-Visa.
 * - Las vistas NUNCA escriben estas URLs; usan boton_evisa() o fuente_oficial().
 * - Sin parámetros de seguimiento (?, _ga, _gl, utm_...).
 * - 'verificada_en' se rellena (AAAA-MM-DD) solo tras VI-04. Mientras sea null,
 *   el responsable debe saber que el enlace no está verificado.
 * - 'activo' => false oculta la fuente sin borrarla.
 */

return [

    // Enlace entregado por el responsable (portal de onboarding de VFS).
    'evisa_gnq' => [
        'nombre'        => 'Solicitud de e-Visa de Guinea Ecuatorial (portal oficial)',
        'tipo'          => 'oficial',
        'activo'        => true,
        'verificada_en' => null,
        'responsable'   => 'equipo-contenido',
        'urls' => [
            // La versión en español (/gnq/es/on-boarding) se activa solo tras VI-04.
            'es' => 'https://egonline.vfsevisa.com/gnq/en/on-boarding',
            'en' => 'https://egonline.vfsevisa.com/gnq/en/on-boarding',
            'fr' => 'https://egonline.vfsevisa.com/gnq/en/on-boarding',
            'pt' => 'https://egonline.vfsevisa.com/gnq/en/on-boarding',
        ],
    ],

    // Portal citado en el comunicado de lanzamiento (2023). Respaldo, inactivo
    // hasta comprobarlo en VI-04.
    'evisa_gnq_referencia' => [
        'nombre'        => 'Portal de e-Visa de Guinea Ecuatorial (referencia)',
        'tipo'          => 'oficial',
        'activo'        => false,
        'verificada_en' => null,
        'responsable'   => 'equipo-contenido',
        'urls' => [
            'es' => 'https://equatorialguinea-evisa.com/',
            'en' => 'https://equatorialguinea-evisa.com/',
            'fr' => 'https://equatorialguinea-evisa.com/',
            'pt' => 'https://equatorialguinea-evisa.com/',
        ],
    ],
];
