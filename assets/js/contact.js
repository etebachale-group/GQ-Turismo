document.addEventListener('DOMContentLoaded', function () {
    const contactForm = document.getElementById('contact-form');
    const formMessage = document.getElementById('form-message');

    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();

            formMessage.innerHTML = '<div class="alert alert-info">Enviando...</div>';

            fetch('api/contact.php', {
                method: 'POST',
                body: new FormData(contactForm)
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    formMessage.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    contactForm.reset();
                } else {
                    formMessage.innerHTML = `<div class="alert alert-danger">Hubo un error al enviar el mensaje.</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                formMessage.innerHTML = '<div class="alert alert-danger">Hubo un error de conexión. Por favor, inténtalo de nuevo más tarde.</div>';
            });
        });
    }
});
