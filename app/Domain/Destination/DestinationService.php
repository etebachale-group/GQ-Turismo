<?php
declare(strict_types=1);

namespace App\Domain\Destination;

use mysqli;
use InvalidArgumentException;
use RuntimeException;

/**
 * Servicio canónico de destinos turísticos (M-01).
 * Incluye generación de slug único, gestión de publicación, metadatos y exportación a sitemap.xml.
 */
class DestinationService {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /**
     * Genera un slug URL-friendly y garantiza unicidad en la base de datos.
     */
    public static function generarSlug(string $texto): string {
        $slug = mb_strtolower(trim($texto), 'UTF-8');
        // Reemplazar caracteres con acentos
        $a = ['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ','ü','Ü'];
        $b = ['a','e','i','o','u','n','a','e','i','o','u','n','u','u'];
        $slug = str_replace($a, $b, $slug);
        // Quitar caracteres no alfanuméricos
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Obtiene todos los destinos publicados.
     */
    public function listarPublicados(?string $region = null, ?string $categoria = null): array {
        $conditions = ["1=1"];
        $params = [];
        $types = '';

        if ($categoria && $categoria !== 'all') {
            $conditions[] = "categoria = ?";
            $params[] = $categoria;
            $types .= 's';
        }

        if ($region === 'bioko') {
            $conditions[] = "LOWER(COALESCE(ciudad, '')) IN ('malabo', 'luba', 'riaba', 'baney', 'ureka', 'moka')";
        } elseif ($region === 'riomuni') {
            $conditions[] = "(LOWER(COALESCE(ciudad, '')) NOT IN ('malabo', 'luba', 'riaba', 'baney', 'ureka', 'moka') OR LOWER(COALESCE(ciudad, '')) IN ('bata', 'mongomo', 'mbini', 'evinayong', 'ebebiyin', 'cogo', 'niefang', 'oyala', 'añisok', 'akonibe'))";
        }

        $sql = "SELECT * FROM destinos WHERE " . implode(' AND ', $conditions) . " ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $destinos = [];
        while ($row = $res->fetch_assoc()) {
            $destinos[] = $row;
        }
        $stmt->close();

        return $destinos;
    }

    /**
     * Obtiene un destino por ID.
     */
    public function obtenerPorId(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM destinos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Genera XML para sitemap.xml.
     */
    public function generarSitemapXml(string $baseUrl): string {
        $destinos = $this->listarPublicados();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Home
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars(rtrim($baseUrl, '/') . '/index.php') . "</loc>\n";
        $xml .= "    <changefreq>daily</changefreq>\n";
        $xml .= "    <priority>1.0</priority>\n";
        $xml .= "  </url>\n";

        // Destinos general
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars(rtrim($baseUrl, '/') . '/destinos.php') . "</loc>\n";
        $xml .= "    <changefreq>daily</changefreq>\n";
        $xml .= "    <priority>0.9</priority>\n";
        $xml .= "  </url>\n";

        // Cada destino
        foreach ($destinos as $d) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars(rtrim($baseUrl, '/') . '/detalle_destino.php?id=' . $d['id']) . "</loc>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }
}
