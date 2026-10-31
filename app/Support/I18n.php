<?php
declare(strict_types=1);

namespace App\Support;

/**
 * Gestor central de internacionalización (I18N-01 a I18N-04).
 * Soporta las cuatro lenguas oficiales y relevantes de Guinea Ecuatorial:
 * es (español), en (inglés), fr (francés), pt (portugués).
 */
class I18n {
    public const DEFAULT_LANG = 'es';
    public const SUPPORTED_LANGS = ['es', 'en', 'fr', 'pt'];

    private static array $translations = [];
    private static ?string $currentLang = null;

    /**
     * Obtiene el idioma activo en sesión, cookie o por defecto.
     */
    public static function getLang(): string {
        if (self::$currentLang !== null) {
            return self::$currentLang;
        }

        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['lang'])) {
            $lang = (string)$_SESSION['lang'];
            if (in_array($lang, self::SUPPORTED_LANGS, true)) {
                return self::$currentLang = $lang;
            }
        }

        if (isset($_COOKIE['gq_lang']) && in_array($_COOKIE['gq_lang'], self::SUPPORTED_LANGS, true)) {
            return self::$currentLang = (string)$_COOKIE['gq_lang'];
        }

        return self::$currentLang = self::DEFAULT_LANG;
    }

    /**
     * Establece el idioma activo.
     */
    public static function setLang(string $lang): void {
        if (in_array($lang, self::SUPPORTED_LANGS, true)) {
            self::$currentLang = $lang;
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['lang'] = $lang;
            }
        }
    }

    /**
     * Traduce una clave en el idioma especificado o activo.
     */
    public static function trans(string $key, array $replace = [], ?string $lang = null): string {
        $lang = $lang ?: self::getLang();

        if (!isset(self::$translations[$lang])) {
            $file = dirname(__DIR__, 2) . "/lang/{$lang}.php";
            if (file_exists($file)) {
                self::$translations[$lang] = require $file;
            } else {
                self::$translations[$lang] = [];
            }
        }

        $translation = self::$translations[$lang][$key] ?? null;

        // Fallback a español si falta en otro idioma
        if ($translation === null && $lang !== self::DEFAULT_LANG) {
            if (!isset(self::$translations[self::DEFAULT_LANG])) {
                $fileDef = dirname(__DIR__, 2) . "/lang/" . self::DEFAULT_LANG . ".php";
                self::$translations[self::DEFAULT_LANG] = file_exists($fileDef) ? require $fileDef : [];
            }
            $translation = self::$translations[self::DEFAULT_LANG][$key] ?? $key;
        } elseif ($translation === null) {
            $translation = $key;
        }

        foreach ($replace as $k => $v) {
            $translation = str_replace(':' . $k, (string)$v, $translation);
        }

        return $translation;
    }

    /**
     * Formato de precio local en FCFA (I18N-04): espacio como separador de miles, sin decimales.
     * Ejemplo: 25 000 FCFA
     */
    public static function formatCurrency(float $amount, string $currency = 'XAF'): string {
        if ($currency === 'XAF' || $currency === 'FCFA') {
            return number_format($amount, 0, ',', ' ') . ' FCFA';
        }
        if ($currency === 'EUR') {
            return number_format($amount, 2, ',', ' ') . ' €';
        }
        return number_format($amount, 2, ',', ' ') . ' ' . $currency;
    }

    /**
     * Formato de fecha local DD/MM/AAAA (I18N-04).
     */
    public static function formatDate(string $date): string {
        $timestamp = strtotime($date);
        return $timestamp ? date('d/m/Y', $timestamp) : $date;
    }

    /**
     * Formato de teléfono local (+240) (I18N-04).
     */
    public static function formatPhone(string $phone): string {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '240')) {
            $clean = substr($clean, 3);
        }
        return '+240 ' . chunk_split($clean, 3, ' ');
    }
}
