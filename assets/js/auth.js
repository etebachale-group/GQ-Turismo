document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (loginForm) {
        loginForm.addEventListener('submit', handleAuthFormSubmit);
    }

    if (registerForm) {
        registerForm.addEventListener('submit', handleAuthFormSubmit);
    }
});

function handleAuthFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const alertBox = document.getElementById(`${form.id.replace('Form', '')}-alert`);

    // Limpiar alerta previa
    alertBox.classList.add('d-none');
    alertBox.textContent = '';

    fetch('api/auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alertBox.className = 'alert alert-success';
            alertBox.textContent = data.message;
            alertBox.classList.remove('d-none');

            // Recargar la página o redirigir si se especifica
            setTimeout(() => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.reload();
                }
            }, 1500);

        } else {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = data.message || 'Ocurrió un error.';
            alertBox.classList.remove('d-none');
        }
    })
    .catch(error => {
        console.error('Error en la petición de autenticación:', error);
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = 'Error de conexión. Inténtalo de nuevo.';
        alertBox.classList.remove('d-none');
    });
}
