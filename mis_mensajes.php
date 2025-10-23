<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}

// Permitir acceso a turistas y proveedores (agencia, guia, local)
$allowed_types = ['turista', 'agencia', 'guia', 'local'];
if (!in_array($_SESSION['user_type'], $allowed_types)) {
    header("Location: index.php");
    exit();
}

include 'includes/db_connect.php';

$current_user_id = $_SESSION['user_id'];
$current_user_type = $_SESSION['user_type'];
$conversations = [];
$message_error = '';

// Get unique conversations (contacts)
if ($conn) {
    $query = "
        SELECT DISTINCT
            CASE 
                WHEN m.sender_id = ? AND m.sender_type = ? THEN m.receiver_id
                ELSE m.sender_id
            END as contact_id,
            CASE 
                WHEN m.sender_id = ? AND m.sender_type = ? THEN m.receiver_type
                ELSE m.sender_type
            END as contact_type,
            CASE 
                WHEN m.sender_id = ? AND m.sender_type = ? THEN 
                    CASE m.receiver_type
                        WHEN 'agencia' THEN (SELECT nombre_agencia FROM agencias WHERE id = m.receiver_id)
                        WHEN 'guia' THEN (SELECT nombre_guia FROM guias_turisticos WHERE id = m.receiver_id)
                        WHEN 'local' THEN (SELECT nombre_local FROM lugares_locales WHERE id = m.receiver_id)
                        ELSE r.nombre
                    END
                ELSE 
                    CASE m.sender_type
                        WHEN 'agencia' THEN (SELECT nombre_agencia FROM agencias WHERE id = m.sender_id)
                        WHEN 'guia' THEN (SELECT nombre_guia FROM guias_turisticos WHERE id = m.sender_id)
                        WHEN 'local' THEN (SELECT nombre_local FROM lugares_locales WHERE id = m.sender_id)
                        ELSE s.nombre
                    END
            END as contact_name,
            MAX(m.timestamp) as last_message_time,
            (SELECT message FROM mensajes m2 
             WHERE ((m2.sender_id = ? AND m2.sender_type = ? AND m2.receiver_id = contact_id AND m2.receiver_type = contact_type)
                 OR (m2.receiver_id = ? AND m2.receiver_type = ? AND m2.sender_id = contact_id AND m2.sender_type = contact_type))
             ORDER BY m2.timestamp DESC LIMIT 1) as last_message,
            SUM(CASE WHEN m.receiver_id = ? AND m.receiver_type = ? AND m.is_read = FALSE THEN 1 ELSE 0 END) as unread_count
        FROM mensajes m
        LEFT JOIN usuarios s ON m.sender_id = s.id
        LEFT JOIN usuarios r ON m.receiver_id = r.id
        WHERE (m.sender_id = ? AND m.sender_type = ?) 
           OR (m.receiver_id = ? AND m.receiver_type = ?)
        GROUP BY contact_id, contact_type
        ORDER BY last_message_time DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isisisisisisisis", 
        $current_user_id, $current_user_type,
        $current_user_id, $current_user_type,
        $current_user_id, $current_user_type,
        $current_user_id, $current_user_type,
        $current_user_id, $current_user_type,
        $current_user_id, $current_user_type,
        $current_user_id, $current_user_type,
        $current_user_id, $current_user_type
    );
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $conversations[] = $row;
    }
    $stmt->close();
    $conn->close();
} else {
    $message_error = "Error de conexión a la base de datos.";
}

require_once 'includes/header.php';
?>

<style>
    .chat-container {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 1.5rem;
        min-height: 600px;
        max-height: 700px;
    }
    
    /* Mobile responsive */
    @media (max-width: 767px) {
        .chat-container {
            grid-template-columns: 1fr;
            gap: 0;
            min-height: calc(100vh - 250px);
            max-height: none;
        }
        
        .conversations-panel {
            display: none;
        }
        
        .conversations-panel.show-mobile {
            display: flex;
            position: fixed;
            top: 72px;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1000;
            border-radius: 0;
        }
        
        .chat-panel {
            min-height: calc(100vh - 250px);
        }
        
        .toggle-conversations-btn {
            display: block !important;
        }
    }
    
    @media (min-width: 768px) {
        .toggle-conversations-btn {
            display: none !important;
        }
    }
    
    .conversations-panel {
        background: white;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .conversations-header {
        padding: 1.25rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .conversations-header h5 {
        margin: 0;
        font-size: 1.125rem;
    }
    
    .conversations-list {
        flex: 1;
        overflow-y: auto;
        padding: 0.5rem;
    }
    
    .conversation-item {
        padding: 1rem;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-bottom: 0.5rem;
        border: 2px solid transparent;
    }
    
    .conversation-item:hover {
        background: #f8f9fa;
    }
    
    .conversation-item.active {
        background: linear-gradient(135deg, #f0f4ff 0%, #e6efff 100%);
        border-color: #667eea;
    }
    
    .conversation-item .contact-name {
        font-weight: 600;
        color: #212529;
        margin-bottom: 0.25rem;
    }
    
    .conversation-item .last-message {
        font-size: 0.875rem;
        color: #6c757d;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin-bottom: 0.25rem;
    }
    
    .conversation-item .time-badge {
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    .conversation-item .unread-badge {
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
    }
    
    .chat-panel {
        background: white;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .chat-header {
        padding: 1.25rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .chat-header h5 {
        margin: 0;
        font-size: 1.125rem;
    }
    
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 50%, #f8f9fa 100%);
    }
    
    .message-bubble {
        max-width: 70%;
        margin-bottom: 1rem;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .message-bubble.sent {
        margin-left: auto;
    }
    
    .message-bubble.received {
        margin-right: auto;
    }
    
    .message-content {
        padding: 0.875rem 1.125rem;
        border-radius: 1rem;
        word-wrap: break-word;
    }
    
    .message-bubble.sent .message-content {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom-right-radius: 0.25rem;
    }
    
    .message-bubble.received .message-content {
        background: white;
        color: #212529;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        border-bottom-left-radius: 0.25rem;
    }
    
    .message-time {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.25rem;
        text-align: right;
    }
    
    .message-bubble.sent .message-time {
        color: #212529;
    }
    
    .chat-input-container {
        padding: 1.25rem;
        background: white;
        border-top: 1px solid #dee2e6;
    }
    
    .chat-input-form {
        display: flex;
        gap: 0.75rem;
    }
    
    .chat-input-form textarea {
        flex: 1;
        border-radius: 10px;
        border: 2px solid #dee2e6;
        padding: 0.75rem;
        resize: none;
        font-size: 0.9375rem;
        transition: border-color 0.2s ease;
    }
    
    .chat-input-form textarea:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }
    
    .chat-input-form button {
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        transition: all 0.2s ease;
    }
    
    .chat-input-form button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .empty-chat {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #6c757d;
    }
    
    .empty-chat i {
        font-size: 5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    @media (max-width: 991px) {
        .chat-container {
            grid-template-columns: 1fr;
            height: auto;
        }
        
        .conversations-panel {
            max-height: 300px;
        }
        
        .chat-panel {
            height: 500px;
        }
    }
</style>

<div class="container py-5">
    <!-- Mobile Toggle Button -->
    <button class="btn btn-primary toggle-conversations-btn mb-3 d-md-none" id="toggleConversations" style="display: none;">
        <i class="bi bi-list me-2"></i>Ver Conversaciones
    </button>
    
    <div class="mb-4">
        <h1 class="display-5 fw-bold mb-2"><i class="bi bi-chat-dots-fill me-2" style="color: #667eea;"></i>Mis Mensajes</h1>
        <p class="text-muted">Conversa con agencias, guías y locales</p>
    </div>

    <?php if ($message_error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $message_error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="chat-container">
        <!-- Conversations List -->
        <div class="conversations-panel">
            <div class="conversations-header">
                <h5><i class="bi bi-chat-left-text-fill me-2"></i>Conversaciones</h5>
            </div>
            <div class="conversations-list" id="conversationsList">
                <?php if (count($conversations) > 0): ?>
                    <?php foreach ($conversations as $index => $conv): ?>
                        <div class="conversation-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                             data-contact-id="<?php echo $conv['contact_id']; ?>"
                             data-contact-type="<?php echo $conv['contact_type']; ?>"
                             data-contact-name="<?php echo htmlspecialchars($conv['contact_name']); ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="contact-name">
                                        <i class="bi bi-<?php 
                                            echo $conv['contact_type'] === 'agencia' ? 'building' : 
                                                ($conv['contact_type'] === 'guia' ? 'person-badge' : 'shop'); 
                                        ?> me-1"></i>
                                        <?php echo htmlspecialchars($conv['contact_name']); ?>
                                    </div>
                                    <div class="last-message">
                                        <?php echo htmlspecialchars(substr($conv['last_message'], 0, 40)) . (strlen($conv['last_message']) > 40 ? '...' : ''); ?>
                                    </div>
                                    <small class="time-badge">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($conv['last_message_time'])); ?>
                                    </small>
                                </div>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted p-4">
                        <i class="bi bi-chat-left-dots" style="font-size: 3rem;"></i>
                        <p class="mt-2">No tienes conversaciones</p>
                        <small>Envía un mensaje a una agencia, guía o local para comenzar</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat Window -->
        <div class="chat-panel">
            <div class="chat-header" id="chatHeader">
                <div>
                    <h5 id="chatContactName">
                        <?php if (count($conversations) > 0): ?>
                            <i class="bi bi-<?php 
                                echo $conversations[0]['contact_type'] === 'agencia' ? 'building' : 
                                    ($conversations[0]['contact_type'] === 'guia' ? 'person-badge' : 'shop'); 
                            ?> me-2"></i><?php echo htmlspecialchars($conversations[0]['contact_name']); ?>
                        <?php else: ?>
                            Selecciona una conversación
                        <?php endif; ?>
                    </h5>
                    <small id="chatContactType" style="opacity: 0.9;">
                        <?php if (count($conversations) > 0): ?>
                            <?php echo ucfirst($conversations[0]['contact_type']); ?>
                        <?php endif; ?>
                    </small>
                </div>
                <button class="btn btn-sm btn-light" onclick="loadMessages()" title="Refrescar">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <?php if (count($conversations) > 0): ?>
                    <!-- Messages will be loaded here via JavaScript -->
                <?php else: ?>
                    <div class="empty-chat">
                        <i class="bi bi-chat-heart"></i>
                        <h4>Bienvenido al Chat</h4>
                        <p>Selecciona una conversación para comenzar a chatear</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="chat-input-container">
                <form class="chat-input-form" id="chatForm">
                    <textarea 
                        id="messageInput" 
                        rows="2" 
                        placeholder="Escribe tu mensaje aquí..."
                        required
                        <?php echo count($conversations) === 0 ? 'disabled' : ''; ?>
                    ></textarea>
                    <button type="submit" <?php echo count($conversations) === 0 ? 'disabled' : ''; ?>>
                        <i class="bi bi-send-fill me-1"></i>
                        Enviar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let currentContactId = <?php echo count($conversations) > 0 ? $conversations[0]['contact_id'] : 'null'; ?>;
let currentContactType = <?php echo count($conversations) > 0 ? "'" . $conversations[0]['contact_type'] . "'" : 'null'; ?>;
let currentContactName = <?php echo count($conversations) > 0 ? "'" . addslashes($conversations[0]['contact_name']) . "'" : 'null'; ?>;
let messageRefreshInterval;

document.addEventListener('DOMContentLoaded', function() {
    if (currentContactId) {
        loadMessages();
        messageRefreshInterval = setInterval(loadMessages, 5000);
    }
    
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.conversation-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            
            currentContactId = this.dataset.contactId;
            currentContactType = this.dataset.contactType;
            currentContactName = this.dataset.contactName;
            
            const iconClass = currentContactType === 'agencia' ? 'building' : 
                             (currentContactType === 'guia' ? 'person-badge' : 'shop');
            document.getElementById('chatContactName').innerHTML = `<i class="bi bi-${iconClass} me-2"></i>` + currentContactName;
            document.getElementById('chatContactType').textContent = currentContactType.charAt(0).toUpperCase() + currentContactType.slice(1);
            
            document.getElementById('messageInput').disabled = false;
            document.querySelector('#chatForm button').disabled = false;
            
            loadMessages();
            
            const unreadBadge = this.querySelector('.unread-badge');
            if (unreadBadge) {
                unreadBadge.remove();
            }
        });
    });
    
    document.getElementById('chatForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    document.getElementById('messageInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // Mobile: Toggle conversations panel
    const toggleBtn = document.getElementById('toggleConversations');
    const conversationsPanel = document.querySelector('.conversations-panel');
    
    if (toggleBtn && conversationsPanel) {
        toggleBtn.addEventListener('click', function() {
            conversationsPanel.classList.toggle('show-mobile');
            if (conversationsPanel.classList.contains('show-mobile')) {
                toggleBtn.innerHTML = '<i class="bi bi-x-lg me-2"></i>Cerrar';
            } else {
                toggleBtn.innerHTML = '<i class="bi bi-list me-2"></i>Ver Conversaciones';
            }
        });
        
        // Close panel when selecting a conversation on mobile
        document.querySelectorAll('.conversation-item').forEach(item => {
            const originalClickHandler = item.onclick;
            item.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    conversationsPanel.classList.remove('show-mobile');
                    toggleBtn.innerHTML = '<i class="bi bi-list me-2"></i>Ver Conversaciones';
                }
            });
        });
    }
});

function loadMessages() {
    if (!currentContactId) return;
    
    fetch(`api/get_conversation.php?contact_id=${currentContactId}&contact_type=${currentContactType}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMessages(data.messages);
            }
        })
        .catch(error => console.error('Error:', error));
}

function displayMessages(messages) {
    const chatMessages = document.getElementById('chatMessages');
    const currentUserId = <?php echo $_SESSION['user_id']; ?>;
    const currentUserType = '<?php echo $_SESSION['user_type']; ?>';
    
    if (messages.length === 0) {
        chatMessages.innerHTML = `
            <div class="empty-chat">
                <i class="bi bi-chat-quote"></i>
                <h5>No hay mensajes aún</h5>
                <p>Envía un mensaje para comenzar la conversación</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    messages.forEach(msg => {
        const isSent = msg.sender_id == currentUserId && msg.sender_type === currentUserType;
        const bubbleClass = isSent ? 'sent' : 'received';
        const time = new Date(msg.timestamp).toLocaleString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        html += `
            <div class="message-bubble ${bubbleClass}">
                <div class="message-content">
                    ${escapeHtml(msg.message).replace(/\n/g, '<br>')}
                </div>
                <div class="message-time">${time}</div>
            </div>
        `;
    });
    
    const shouldScroll = chatMessages.scrollHeight - chatMessages.scrollTop === chatMessages.clientHeight || 
                        chatMessages.scrollTop === 0;
    
    chatMessages.innerHTML = html;
    
    if (shouldScroll) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message || !currentContactId) return;
    
    const data = {
        receiver_id: currentContactId,
        receiver_type: currentContactType,
        message: message
    };
    
    fetch('api/messages.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            loadMessages();
            
            const activeConv = document.querySelector('.conversation-item.active .last-message');
            if (activeConv) {
                activeConv.textContent = message.substring(0, 40) + (message.length > 40 ? '...' : '');
            }
        } else {
            alert('Error al enviar el mensaje: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Hubo un error de conexión al enviar el mensaje');
    });
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

window.addEventListener('beforeunload', function() {
    if (messageRefreshInterval) {
        clearInterval(messageRefreshInterval);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
