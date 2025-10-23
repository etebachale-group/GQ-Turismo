<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión y es un super_admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'super_admin') {
    header("Location: login.php"); // Redirigir a la página de login si no está autorizado
    exit();
}

include '../includes/db_connect.php';

$message = '';

// Lógica para actualizar tipo de usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_type') {
    $user_id = $_POST['user_id'];
    $new_type = $_POST['new_type'];

    // Validar el nuevo tipo de usuario
    $allowed_types = ['turista', 'agencia', 'guia', 'local', 'super_admin'];
    if (!in_array($new_type, $allowed_types)) {
        $message = "<div class='alert alert-danger'>Tipo de usuario no válido.</div>";
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET tipo_usuario = ? WHERE id = ?");
        $stmt->bind_param("si", $new_type, $user_id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Tipo de usuario actualizado con éxito.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error al actualizar el tipo de usuario: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Lógica para eliminar usuario
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    // Prevenir que el super_admin se elimine a sí mismo
    if ($user_id == $_SESSION['user_id']) {
        $message = "<div class='alert alert-danger'>No puedes eliminar tu propia cuenta de super administrador.</div>";
    } else {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Usuario eliminado con éxito.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error al eliminar el usuario: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Obtener todos los usuarios
$query = "SELECT id, nombre, email, tipo_usuario, fecha_registro FROM usuarios ORDER BY fecha_registro DESC";
$result = $conn->query($query);
$users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();

// Configurar título de página
$page_title = "Gestionar Usuarios";
include 'admin_header.php';
?>

<!-- Page Header -->
<div class="admin-page-header">
    <h1><i class="bi bi-people-fill"></i> Gestionar Usuarios</h1>
    <p>Administra todos los usuarios de la plataforma GQ-Turismo</p>
</div>

<?php if ($message): ?>
    <?= $message ?>
<?php endif; ?>

<!-- Stats Cards -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: var(--space-xl);">
    <?php
    $total_users = count($users);
    $turistas = count(array_filter($users, fn($u) => $u['tipo_usuario'] == 'turista'));
    $agencias = count(array_filter($users, fn($u) => $u['tipo_usuario'] == 'agencia'));
    $guias = count(array_filter($users, fn($u) => $u['tipo_usuario'] == 'guia'));
    $locales = count(array_filter($users, fn($u) => $u['tipo_usuario'] == 'local'));
    $admins = count(array_filter($users, fn($u) => $u['tipo_usuario'] == 'super_admin'));
    ?>
    
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-content">
            <h3><?= $total_users ?></h3>
            <p>Total Usuarios</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="bi bi-person-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?= $turistas ?></h3>
            <p>Turistas</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="bi bi-building"></i>
        </div>
        <div class="stat-content">
            <h3><?= $agencias ?></h3>
            <p>Agencias</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="bi bi-person-badge"></i>
        </div>
        <div class="stat-content">
            <h3><?= $guias ?></h3>
            <p>Guías</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon secondary">
            <i class="bi bi-shop"></i>
        </div>
        <div class="stat-content">
            <h3><?= $locales ?></h3>
            <p>Locales</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="bi bi-shield-check"></i>
        </div>
        <div class="stat-content">
            <h3><?= $admins ?></h3>
            <p>Administradores</p>
        </div>
    </div>
</div>

<!-- Users Table Card -->
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-table"></i> Listado de Usuarios</h2>
        <div class="card-header-actions">
            <input type="text" class="search-input" placeholder="Buscar usuarios..." id="searchUsers">
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Tipo de Usuario</th>
                        <th>Fecha de Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($user['id']) ?></strong></td>
                                <td><?= htmlspecialchars($user['nombre']) ?></td>
                                <td><small><?= htmlspecialchars($user['email']) ?></small></td>
                                <td>
                                    <form action="manage_users.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="update_type">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <select name="new_type" class="form-select form-select-sm" onchange="if(confirm('¿Cambiar el tipo de usuario?')) this.form.submit();" style="width: auto; display: inline-block;">
                                            <option value="turista" <?= ($user['tipo_usuario'] == 'turista') ? 'selected' : '' ?>>👤 Turista</option>
                                            <option value="agencia" <?= ($user['tipo_usuario'] == 'agencia') ? 'selected' : '' ?>>🏢 Agencia</option>
                                            <option value="guia" <?= ($user['tipo_usuario'] == 'guia') ? 'selected' : '' ?>>🧑‍🏫 Guía</option>
                                            <option value="local" <?= ($user['tipo_usuario'] == 'local') ? 'selected' : '' ?>>🏪 Local</option>
                                            <option value="super_admin" <?= ($user['tipo_usuario'] == 'super_admin') ? 'selected' : '' ?>>🛡️ Admin</option>
                                        </select>
                                    </form>
                                </td>
                                <td><small><?= date('d/m/Y', strtotime($user['fecha_registro'])) ?></small></td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="manage_users.php?action=delete&id=<?= $user['id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('⚠️ ¿Estás seguro de eliminar a <?= htmlspecialchars($user['nombre']) ?>? Esta acción no se puede deshacer.');">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled title="No puedes eliminar tu propia cuenta">
                                            <i class="bi bi-shield-lock"></i> Tu cuenta
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding: var(--space-xl);">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: var(--gray-400);"></i>
                                <p style="color: var(--gray-600); margin-top: var(--space-md);">No hay usuarios registrados.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Búsqueda en tabla
document.getElementById('searchUsers')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?php include 'admin_footer.php'; ?>
