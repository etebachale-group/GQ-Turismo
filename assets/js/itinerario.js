document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('itinerary-form');
    const itineraryList = document.getElementById('itineraries-list');

    // Logic for itinerary creation form
    if (form) {
        const destinationCards = form.querySelectorAll('.destination-card');
        const selectedList = document.getElementById('selected-destinations');
        const feedbackBox = document.getElementById('itinerary-feedback');
        let selectedDestinations = [];

        destinationCards.forEach(card => {
            const addButton = card.querySelector('.add-to-itinerary');
            const destinationId = card.dataset.id;
            const destinationName = card.querySelector('.card-title').textContent;

            addButton.addEventListener('click', () => {
                if (selectedDestinations.length >= 5 && !isSelected(destinationId)) {
                    showFeedback('No puedes seleccionar más de 5 destinos.', 'danger', feedbackBox);
                    return;
                }
                toggleSelection(card, destinationId, destinationName);
            });
        });

        function toggleSelection(card, id, name) {
            if (isSelected(id)) {
                selectedDestinations = selectedDestinations.filter(dest => dest.id !== id);
                card.classList.remove('selected');
                const addButton = card.querySelector('.add-to-itinerary');
                addButton.textContent = 'Agregar';
                addButton.classList.replace('btn-danger', 'btn-outline-primary');
            } else {
                selectedDestinations.push({ id, name });
                card.classList.add('selected');
                const addButton = card.querySelector('.add-to-itinerary');
                addButton.textContent = 'Quitar';
                addButton.classList.replace('btn-outline-primary', 'btn-danger');
            }
            updateSelectedList();
        }

        function isSelected(id) {
            return selectedDestinations.some(dest => dest.id === id);
        }

        function updateSelectedList() {
            selectedList.innerHTML = '';
            if (selectedDestinations.length === 0) {
                selectedList.innerHTML = '<li class="list-group-item text-muted">Aún no has seleccionado destinos.</li>';
                return;
            }
            selectedDestinations.forEach((dest, index) => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.innerHTML = `<span>${index + 1}. ${dest.name}</span><button type="button" class="btn btn-sm btn-outline-danger remove-from-list" data-id="${dest.id}">&times;</button>`;
                selectedList.appendChild(li);
            });
            document.querySelectorAll('.remove-from-list').forEach(button => {
                button.addEventListener('click', (e) => {
                    const idToRemove = e.target.dataset.id;
                    const cardToDeselect = form.querySelector(`.destination-card[data-id="${idToRemove}"]`);
                    toggleSelection(cardToDeselect, idToRemove, '');
                });
            });
        }

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const itineraryName = document.getElementById('itinerary-name').value.trim();
            if (!itineraryName) {
                showFeedback('Por favor, dale un nombre a tu itinerario.', 'danger', feedbackBox);
                return;
            }
            if (selectedDestinations.length === 0) {
                showFeedback('Debes seleccionar al menos un destino.', 'danger', feedbackBox);
                return;
            }
            const data = {
                action: 'create_itinerary',
                name: itineraryName,
                destinations: selectedDestinations.map(d => d.id)
            };
            fetch('api/itinerarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showFeedback(result.message, 'success', feedbackBox);
                    form.reset();
                    selectedDestinations = [];
                    destinationCards.forEach(card => card.classList.remove('selected'));
                    updateSelectedList();
                    setTimeout(() => { window.location.href = 'itinerario.html'; }, 2000);
                } else {
                    showFeedback(result.message || 'Ocurrió un error.', 'danger', feedbackBox);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showFeedback('Error de conexión. Inténtalo de nuevo.', 'danger', feedbackBox);
            });
        });
        updateSelectedList();
    }

    // Logic for itinerary list page
    if (itineraryList) {
        const deleteButtons = itineraryList.querySelectorAll('.delete-itinerary');
        const feedbackBox = document.getElementById('itinerary-delete-feedback');

        deleteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const itineraryId = e.target.dataset.id;
                if (confirm('¿Estás seguro de que quieres eliminar este itinerario?')) {
                    deleteItinerary(itineraryId, e.target);
                }
            });
        });

        function deleteItinerary(id, button) {
            const data = { action: 'delete_itinerary', id: id };

            fetch('api/itinerarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showFeedback(result.message, 'success', feedbackBox);
                    button.closest('.list-group-item').remove();
                } else {
                    showFeedback(result.message || 'Ocurrió un error.', 'danger', feedbackBox);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showFeedback('Error de conexión. Inténtalo de nuevo.', 'danger', feedbackBox);
            });
        }
    }

    function showFeedback(message, type, element) {
        element.className = `alert alert-${type}`;
        element.textContent = message;
        setTimeout(() => {
            element.textContent = '';
            element.className = '';
        }, 4000);
    }
});
