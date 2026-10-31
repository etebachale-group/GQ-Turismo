<?php

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float|int $amount, string $currency = 'XAF'): string {
        if ($currency === 'XAF') {
            return number_format($amount, 0, ',', ' ') . ' FCFA';
        }
        return number_format($amount, 2, ',', ' ') . ' ' . $currency;
    }
}

if (!function_exists('requerir_rol')) {
    function requerir_rol(array|string $roles, ?string $redirect = null): void {
        \App\Support\Auth::requireRole($roles, $redirect);
    }
}
