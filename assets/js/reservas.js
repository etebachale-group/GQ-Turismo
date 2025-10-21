document.addEventListener('DOMContentLoaded', function () {
    const reservaForm = document.getElementById('reserva-form');
    const reservaMessage = document.getElementById('reserva-message');

    if (reservaForm) {
        reservaForm.addEventListener('submit', function (e) {
            e.preventDefault();

            reservaMessage.innerHTML = '<div class="alert alert-info">Procesando tu reserva...</div>';

            const formData = new FormData(reservaForm);

            fetch('api/reservas.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    reservaMessage.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    reservaForm.reset();
                } else {
                    reservaMessage.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(error => {
                reservaMessage.innerHTML = '<div class="alert alert-danger">Ocurrió un error al procesar tu reserva.</div>';
                console.error('Error:', error);
            });
        });
    }
});
