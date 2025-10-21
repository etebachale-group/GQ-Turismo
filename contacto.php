<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Contacto</h2>
    <p class="text-center text-muted mb-5">¿Tienes alguna pregunta? No dudes en contactarnos.</p>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <form id="contact-form">
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Mensaje</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Enviar Mensaje</button>
            </form>
            <div id="form-message" class="mt-3"></div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
