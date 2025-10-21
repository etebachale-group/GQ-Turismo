</main>

<footer class="bg-dark text-white text-center p-4 mt-auto">
    <div class="container">
        <p>&copy; <?php echo date("Y"); ?> Eteba Chale Group. Todos los derechos reservados.</p>
        <p>Promoviendo el turismo en Guinea Ecuatorial.</p>
    </div>
</footer>

<?php if (!isset($_SESSION['user_id'])): ?>
<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Iniciar Sesión</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="loginForm">
          <input type="hidden" name="action" value="login">
          <div id="login-alert" class="alert alert-danger d-none"></div>
          <div class="mb-3">
            <label for="loginEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="loginEmail" name="email" required>
          </div>
          <div class="mb-3">
            <label for="loginPassword" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="loginPassword" name="contrasena" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
      </div>
      <div class="modal-footer">
        <span>¿No tienes cuenta? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Regístrate</a></span>
      </div>
    </div>
  </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registerModalLabel">Crear Cuenta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="registerForm">
           <input type="hidden" name="action" value="register">
           <div id="register-alert" class="alert d-none"></div>
          <div class="mb-3">
            <label for="registerName" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="registerName" name="nombre" required>
          </div>
          <div class="mb-3">
            <label for="registerEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="registerEmail" name="email" required>
          </div>
          <div class="mb-3">
            <label for="registerPassword" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="registerPassword" name="contrasena" required>
          </div>
          <div class="mb-3">
            <label for="registerUserType" class="form-label">Tipo de Cuenta</label>
            <select class="form-select" id="registerUserType" name="tipo_usuario" required>
              <option value="turista" selected>Turista</option>
              <option value="agencia">Agencia de Vuelos</option>
              <option value="guia">Guía Turístico</option>
              <option value="local">Lugar/Local</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100">Registrarse</button>
        </form>
      </div>
       <div class="modal-footer">
        <span>¿Ya tienes cuenta? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Inicia Sesión</a></span>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<!-- AOS JS -->
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>

<!-- Custom JS -->
<script src="assets/js/main.js"></script>
<script src="assets/js/map.js"></script>
<script src="assets/js/auth.js"></script>
<script src="assets/js/itinerario.js"></script>
<script src="assets/js/contact.js"></script>
<script src="assets/js/reservas.js"></script>
<script src="assets/js/destinos-grid.js"></script>
<script src="assets/js/destinos.js"></script>
<script src="assets/js/agencias.js"></script>
<script src="assets/js/guias.js"></script>
<script src="assets/js/locales.js"></script>
<script src="assets/js/scroll.js"></script>

</body>
</html>
