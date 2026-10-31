<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$email = $argv[1] ?? 'test@example.com';
$_POST = [
    'action' => 'register',
    'nombre' => 'Turista Test',
    'email' => $email,
    'contrasena' => 'Password123!',
    'tipo_usuario' => 'super_admin'
];

ob_start();
include __DIR__ . '/../../api/auth.php';
ob_end_clean();
