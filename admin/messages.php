<?php
session_start();

// Verificar si el usuario ha iniciado sesión y tiene el tipo de usuario correcto
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['agencia', 'guia', 'local', 'super_admin'])) {
    header("Location: login.php"); // Redirigir a la página de login si no está autorizado
    exit();
}

include '../includes/db_connect.php';

$current_user_id = $_SESSION['user_id'];
$current_user_type = $_SESSION['user_type'];
$messages = [];
$message_error = '';

// Fetch messages for the current user
if ($conn) {
    // Get messages where current user is sender or receiver
    $stmt = $conn->prepare("
        SELECT m.*, 
               s.nombre as sender_name, 
               r.nombre as receiver_name
        FROM mensajes m
        LEFT JOIN usuarios s ON m.sender_id = s.id
        LEFT JOIN usuarios r ON m.receiver_id = r.id
        WHERE (m.sender_id = ? AND m.sender_type = ?) 
           OR (m.receiver_id = ? AND m.receiver_type = ?)
        ORDER BY m.timestamp DESC
    ");
    $stmt->bind_param("isis", $current_user_id, $current_user_type, $current_user_id, $current_user_type);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();

    // Mark messages received by the current user as read
    $stmt_mark_read = $conn->prepare("UPDATE mensajes SET is_read = TRUE WHERE receiver_id = ? AND receiver_type = ? AND is_read = FALSE");
    $stmt_mark_read->bind_param("is", $current_user_id, $current_user_type);
    $stmt_mark_read->execute();
    $stmt_mark_read->close();

    $conn->close();
} else {
    $message_error = "Error de conexión a la base de datos.";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Mensajes - Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Mis Mensajes</h1>
                </div>

                <?php if ($message_error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $message_error ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-12">
                        <?php if (count($messages) > 0): ?>
                            <div class="list-group">
                                <?php foreach ($messages as $msg): ?>
                                    <div class="list-group-item list-group-item-action <?= $msg['is_read'] ? '' : 'list-group-item-light fw-bold' ">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1">
                                                <?php 
                                                    $display_name = '';
                                                    if ($msg['sender_id'] == $current_user_id && $msg['sender_type'] == $current_user_type) {
                                                        echo 'Para: ' . htmlspecialchars($msg['receiver_name'] ?? $msg['receiver_type']);
                                                    } else {
                                                        echo 'De: ' . htmlspecialchars($msg['sender_name'] ?? $msg['sender_type']);
                                                    }
                                                ?>
                                            </h5>
                                            <small><?= date('d/m/Y H:i', strtotime($msg['timestamp'])) ?></small>
                                        </div>
                                        <p class="mb-1"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                        <small class="text-muted">
                                            <?php if ($msg['sender_id'] == $current_user_id && $msg['sender_type'] == $current_user_type): ?>
                                                Enviado por ti a <?= htmlspecialchars($msg['receiver_type']) ?>
                                            <?php else: ?>
                                                Recibido de <?= htmlspecialchars($msg['sender_type']) ?>
                                            <?php endif; ?>
                                        </small>
                                        <!-- Reply button - will open a modal -->
                                        <button type="button" class="btn btn-sm btn-outline-primary float-end" data-bs-toggle="modal" data-bs-target="#replyMessageModal" 
                                                data-receiver-id="<?= ($msg['sender_id'] == $current_user_id && $msg['sender_type'] == $current_user_type) ? htmlspecialchars($msg['receiver_id']) : htmlspecialchars($msg['sender_id']) ?>" 
                                                data-receiver-type="<?= ($msg['sender_id'] == $current_user_id && $msg['sender_type'] == $current_user_type) ? htmlspecialchars($msg['receiver_type']) : htmlspecialchars($msg['sender_type']) ?>"
                                                data-receiver-name="<?= ($msg['sender_id'] == $current_user_id && $msg['sender_type'] == $current_user_type) ? htmlspecialchars($msg['receiver_name'] ?? $msg['receiver_type']) : htmlspecialchars($msg['sender_name'] ?? $msg['sender_type']) ?>">
                                            Responder
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center">No tienes mensajes.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Reply Message Modal -->
    <div class="modal fade" id="replyMessageModal" tabindex="-1" aria-labelledby="replyMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replyMessageModalLabel">Responder a <span id="modalReplyReceiverName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="replyMessageForm">
                        <input type="hidden" id="replyReceiverId" name="receiver_id">
                        <input type="hidden" id="replyReceiverType" name="receiver_type">
                        <div class="mb-3">
                            <label for="replyMessageContent" class="form-label">Tu Respuesta</label>
                            <textarea class="form-control" id="replyMessageContent" name="message_content" rows="5" required></textarea>
                        </div>
                        <div id="replyMessageResponse" class="mt-3"></div>
                        <button type="submit" class="btn btn-primary w-100">Enviar Respuesta</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const replyMessageModal = document.getElementById('replyMessageModal');
            const modalReplyReceiverName = document.getElementById('modalReplyReceiverName');
            const replyReceiverId = document.getElementById('replyReceiverId');
            const replyReceiverType = document.getElementById('replyReceiverType');
            const replyMessageContent = document.getElementById('replyMessageContent');
            const replyMessageForm = document.getElementById('replyMessageForm');
            const replyMessageResponse = document.getElementById('replyMessageResponse');

            if (replyMessageModal) {
                replyMessageModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    modalReplyReceiverName.textContent = button.dataset.receiverName;
                    replyReceiverId.value = button.dataset.receiverId;
                    replyReceiverType.value = button.dataset.receiverType;
                    replyMessageContent.value = ''; // Clear previous message
                    replyMessageResponse.innerHTML = ''; // Clear previous response
                });

                replyMessageForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    replyMessageResponse.innerHTML = '<div class="alert alert-info">Enviando respuesta...</div>';

                    const formData = {
                        receiver_id: replyReceiverId.value,
                        receiver_type: replyReceiverType.value,
                        message: replyMessageContent.value
                    };

                    fetch('../api/messages.php', { // Note the path to the API endpoint
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            replyMessageResponse.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                            replyMessageForm.reset();
                            // Optionally close modal after a short delay and refresh page
                            setTimeout(() => {
                                const modal = bootstrap.Modal.getInstance(replyMessageModal);
                                modal.hide();
                                location.reload(); // Reload to show new message and updated read status
                            }, 2000);
                        } else {
                            replyMessageResponse.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        replyMessageResponse.innerHTML = '<div class="alert alert-danger">Hubo un error de conexión. Por favor, inténtalo de nuevo más tarde.</div>';
                    });
                });
            }
        });
    </script>
</body>
</html>