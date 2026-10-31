<?php
require_once __DIR__ . '/../../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

$_SERVER['HTTP_ACCEPT'] = 'application/json';
$action = $argv[1] ?? 'unauthenticated';

if ($action === 'unauthenticated') {
    $_SESSION = [];
    \App\Support\Auth::requireRole(['super_admin']);
} elseif ($action === 'unauthorized') {
    $_SESSION['user_id'] = 123;
    $_SESSION['user_type'] = 'turista';
    \App\Support\Auth::requireRole(['super_admin']);
}
