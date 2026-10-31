<?php
/**
 * Validación segura de imágenes subidas.
 *
 * Devuelve un nombre de archivo seguro (aleatorio + extensión según el
 * contenido REAL del archivo, no según el nombre que envía el cliente).
 * Si el archivo no es una imagen válida o es demasiado grande devuelve '',
 * de modo que el move_uploaded_file() posterior falla (el destino sería el
 * propio directorio) y se activa el mensaje de error existente.
 */
function safe_image_filename(array $file, int $max_bytes = 5000000): string
{
    if (!isset($file['tmp_name'], $file['size']) || !is_uploaded_file($file['tmp_name'])) {
        return '';
    }
    if ($file['size'] <= 0 || $file['size'] > $max_bytes) {
        return '';
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return '';
    }
    // Segunda comprobación: debe poder leerse como imagen
    if (@getimagesize($file['tmp_name']) === false) {
        return '';
    }

    return bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
}
