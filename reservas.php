<?php
include 'includes/header.php';

// Proteger la página: si el usuario no está logueado o no es un turista, redirigir al inicio
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    header('Location: index.php');
    exit;
}

// Obtener destinos para el formulario
$query = "SELECT id, nombre FROM destinos ORDER BY nombre ASC";
$result = $conn->query($query);
$destinos = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $destinos[] = $row;
    }
}
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Crear Reserva</h2>
    <p class="text-center text-muted mb-5">Completa el formulario para reservar tu próxima aventura.</p>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <form id="reserva-form">
                <div class="mb-3">
                    <label for="destino" class="form-label">Destino</label>
                    <select class="form-select" id="destino" name="id_destino" required>
                        <option value="" selected disabled>Selecciona un destino</option>
                        <?php foreach ($destinos as $destino): ?>
                            <option value="<?php echo htmlspecialchars($destino['id']); ?>">
                                <?php echo htmlspecialchars($destino['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="fecha" class="form-label">Fecha de Viaje</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" required>
                </div>
                <div class="mb-3">
                    <label for="personas" class="form-label">Número de Personas</label>
                    <input type="number" class="form-control" id="personas" name="personas" min="1" value="1" required>
                </div>
                <button type="submit" class="btn btn-primary">Confirmar Reserva</button>
            </form>
            <div id="reserva-message" class="mt-3"></div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
