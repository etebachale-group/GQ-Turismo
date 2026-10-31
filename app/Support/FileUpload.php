<?php

namespace App\Support;

class FileUpload {
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB

    /**
     * Procesa y valida una subida de imagen de forma estricta.
     * @return array [success => bool, filename => string|null, error => string|null]
     */
    public static function uploadImage(array $file, string $destinationDir): array {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'filename' => null, 'error' => 'Parámetro de archivo inválido.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'filename' => null, 'error' => 'Error en la subida: código ' . $file['error']];
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['success' => false, 'filename' => null, 'error' => 'El archivo supera el tamaño máximo de 5MB.'];
        }

        // 1. Validar tipo MIME real por contenido
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mime, self::ALLOWED_MIME_TYPES)) {
            return ['success' => false, 'filename' => null, 'error' => 'Tipo de archivo no permitido. Solo se aceptan JPG, PNG y WEBP.'];
        }

        // 2. Validar con getimagesize
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'filename' => null, 'error' => 'El archivo no es una imagen válida o está dañado.'];
        }

        // 3. Generar nombre seguro con UUID
        $extension = self::ALLOWED_MIME_TYPES[$mime];
        $safeName = bin2hex(random_bytes(16)) . '.' . $extension;

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $targetPath = rtrim($destinationDir, '/\\') . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'filename' => null, 'error' => 'No se pudo guardar el archivo en el servidor.'];
        }

        return ['success' => true, 'filename' => $safeName, 'error' => null];
    }
}
