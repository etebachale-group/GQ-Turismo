<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    // Simple validation
    if (empty($name) || empty($email) || empty($message)) {
        http_response_code(400);
        echo json_encode(['message' => 'Por favor, rellena todos los campos.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['message' => 'Por favor, introduce un correo electrónico válido.']);
        exit;
    }

    // Simulate sending an email
    // In a real application, you would use a library like PHPMailer to send an email.
    // For example:
    // $to = 'your-email@example.com';
    // $subject = "New contact from {$name}";
    // $body = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";
    // $headers = "From: {$email}";
    // mail($to, $subject, $body, $headers);

    http_response_code(200);
    echo json_encode(['message' => '¡Gracias por tu mensaje! Nos pondremos en contacto contigo pronto.']);
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido']);
}
?>
