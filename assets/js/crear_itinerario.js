document.addEventListener('DOMContentLoaded', () => {
    const itineraryTabs = document.getElementById('itinerary-tabs');
    if (!itineraryTabs) return;

    const nextStepButtons = document.querySelectorAll('.next-step');
    const prevStepButtons = document.querySelectorAll('.prev-step');
    const tabPanes = document.querySelectorAll('.tab-pane');
    const tabs = document.querySelectorAll('#itinerary-tabs .nav-link');

    let currentStep = 0;

    const updateTabs = () => {
        tabs.forEach((tab, index) => {
            if (index === currentStep) {
                tab.classList.add('active');
                tab.setAttribute('aria-selected', 'true');
                tabPanes[index].classList.add('show', 'active');
            } else {
                tab.classList.remove('active');
                tab.setAttribute('aria-selected', 'false');
                tabPanes[index].classList.remove('show', 'active');
            }
        });
    };

    nextStepButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (currentStep < tabs.length - 1) {
                currentStep++;
                updateTabs();
            }
        });
    });

    prevStepButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (currentStep > 0) {
                currentStep--;
                updateTabs();
            }
        });
    });

    const itinerary = {
        city: '',
        startDate: '',
        endDate: '',
        destinations: [],
        accommodation: null,
        guide: null,
        name: ''
    };

    const destinosContainer = document.querySelector('#step2 .row');
    const guiasContainer = document.querySelector('#step4 .row');
    const localesContainer = document.querySelector('#step3 .row');

    const loadDestinos = (city) => {
        destinosContainer.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p>Cargando destinos...</p></div>';
        fetch(`api/destinos.php?action=get_all_destinos&city=${encodeURIComponent(city)}`)
            .then(response => response.json())
            .then(data => {
                destinosContainer.innerHTML = '';
                if (data.success && data.data.length > 0) {
                    data.data.forEach(destino => {
                        const col = document.createElement('div');
                        col.className = 'col';
                        col.innerHTML = `
                            <div class="card h-100 destination-card">
                                <img src="${destino.imagen}" class="card-img-top" alt="${destino.nombre}">
                                <div class="card-body">
                                    <h5 class="card-title">${destino.nombre}</h5>
                                    <p class="card-text small">${(destino.descripcion || '').substring(0, 100)}...</p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <button type="button" class="btn btn-sm btn-outline-primary w-100 add-to-itinerary-item" data-item-type="destino" data-item-id="${destino.id}" data-item-name="${destino.nombre}">Agregar</button>
                                </div>
                            </div>
                        `;
                        destinosContainer.appendChild(col);
                    });
                } else {
                    destinosContainer.innerHTML = '<p class="col-12">No se encontraron destinos para la ciudad seleccionada.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching destinos:', error);
                destinosContainer.innerHTML = '<p class="col-12">Error al cargar los destinos.</p>';
            });
    };

    const loadGuias = (city) => {
        guiasContainer.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p>Cargando guías...</p></div>';
        fetch(`api/guias.php?city=${encodeURIComponent(city)}`)
            .then(response => response.json())
            .then(data => {
                guiasContainer.innerHTML = '';
                if (data.success && data.data.length > 0) {
                    data.data.forEach(guia => {
                        const col = document.createElement('div');
                        col.className = 'col';
                        col.innerHTML = `
                            <div class="card h-100">
                                <img src="${guia.imagen_perfil_url}" class="card-img-top" alt="${guia.nombre_guia}">
                                <div class="card-body">
                                    <h5 class="card-title">${guia.nombre_guia}</h5>
                                    <p class="card-text small">${(guia.descripcion || '').substring(0, 100)}...</p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <button type="button" class="btn btn-sm btn-outline-primary w-100 add-to-itinerary-item" data-item-type="guia" data-item-id="${guia.id}" data-item-name="${guia.nombre_guia}">Agregar</button>
                                </div>
                            </div>
                        `;
                        guiasContainer.appendChild(col);
                    });
                } else {
                    guiasContainer.innerHTML = '<p class="col-12">No se encontraron guías para la ciudad seleccionada.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching guias:', error);
                guiasContainer.innerHTML = '<p class="col-12">Error al cargar los guías.</p>';
            });
    };

    const loadLocales = (city) => {
        localesContainer.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p>Cargando alojamientos...</p></div>';
        fetch(`api/locales.php?city=${encodeURIComponent(city)}`)
            .then(response => response.json())
            .then(data => {
                localesContainer.innerHTML = '';
                if (data.success && data.data.length > 0) {
                    data.data.forEach(local => {
                        const col = document.createElement('div');
                        col.className = 'col';
                        col.innerHTML = `
                            <div class="card h-100">
                                <img src="${local.imagen_perfil_url}" class="card-img-top" alt="${local.nombre_local}">
                                <div class="card-body">
                                    <h5 class="card-title">${local.nombre_local}</h5>
                                    <p class="card-text small">${(local.descripcion || '').substring(0, 100)}...</p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <button type="button" class="btn btn-sm btn-outline-primary w-100 add-to-itinerary-item" data-item-type="alojamiento" data-item-id="${local.id}" data-item-name="${local.nombre_local}">Agregar</button>
                                </div>
                            </div>
                        `;
                        localesContainer.appendChild(col);
                    });
                } else {
                    localesContainer.innerHTML = '<p class="col-12">No se encontraron alojamientos para la ciudad seleccionada.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching locales:', error);
                localesContainer.innerHTML = '<p class="col-12">Error al cargar los alojamientos.</p>';
            });
    };

    const formStep1 = document.getElementById('form-step1');
    formStep1.addEventListener('submit', (e) => e.preventDefault());

    document.querySelector('#step1 .next-step').addEventListener('click', () => {
        const cityInput = document.getElementById('itinerary-city').value;
        const startDateInput = document.getElementById('itinerary-start-date').value;
        const endDateInput = document.getElementById('itinerary-end-date').value;

        if (cityInput && startDateInput && endDateInput) {
            if (itinerary.city !== cityInput) {
                itinerary.city = cityInput;
                // Reset selections and clear containers if city changes
                itinerary.destinations = [];
                itinerary.accommodation = null;
                itinerary.guide = null;
                updateReviewList('review-destinos', []);
                updateReviewList('review-alojamiento', []);
                updateReviewList('review-guia', []);
                
                destinosContainer.innerHTML = '';
                localesContainer.innerHTML = '';
                guiasContainer.innerHTML = '';
            }
            itinerary.startDate = startDateInput;
            itinerary.endDate = endDateInput;
        } else {
            alert('Por favor, completa todos los campos.');
            return; // Stop propagation
        }
    });

    document.getElementById('step2-tab').addEventListener('shown.bs.tab', () => {
        if (itinerary.city && destinosContainer.children.length === 0) {
            loadDestinos(itinerary.city);
        }
    });

    document.getElementById('step3-tab').addEventListener('shown.bs.tab', () => {
        if (itinerary.city && localesContainer.children.length === 0) {
            loadLocales(itinerary.city);
        }
    });

    document.getElementById('step4-tab').addEventListener('shown.bs.tab', () => {
        if (itinerary.city && guiasContainer.children.length === 0) {
            loadGuias(itinerary.city);
        }
    });

    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('add-to-itinerary-item')) {
            const itemType = e.target.dataset.itemType;
            const itemId = e.target.dataset.itemId;
            const itemName = e.target.dataset.itemName;

            // Logic for adding/removing items from itinerary object
            if (itemType === 'destino') {
                const destination = { id: itemId, name: itemName };
                const index = itinerary.destinations.findIndex(d => d.id === itemId);
                if (index > -1) {
                    itinerary.destinations.splice(index, 1);
                    e.target.textContent = 'Agregar';
                    e.target.classList.remove('btn-success');
                    e.target.classList.add('btn-outline-primary');
                } else {
                    itinerary.destinations.push(destination);
                    e.target.textContent = 'Agregado';
                    e.target.classList.remove('btn-outline-primary');
                    e.target.classList.add('btn-success');
                }
                updateReviewList('review-destinos', itinerary.destinations);
            } else if (itemType === 'alojamiento') {
                if (itinerary.accommodation && itinerary.accommodation.id === itemId) {
                    itinerary.accommodation = null;
                    e.target.textContent = 'Agregar';
                    e.target.classList.remove('btn-success');
                    e.target.classList.add('btn-outline-primary');
                } else {
                    if (itinerary.accommodation) {
                        const prevButton = document.querySelector(`.add-to-itinerary-item[data-item-type="alojamiento"][data-item-id="${itinerary.accommodation.id}"]`);
                        if (prevButton) {
                            prevButton.textContent = 'Agregar';
                            prevButton.classList.remove('btn-success');
                            prevButton.classList.add('btn-outline-primary');
                        }
                    }
                    itinerary.accommodation = { id: itemId, name: itemName };
                    e.target.textContent = 'Agregado';
                    e.target.classList.remove('btn-outline-primary');
                    e.target.classList.add('btn-success');
                }
                updateReviewList('review-alojamiento', itinerary.accommodation ? [itinerary.accommodation] : []);
            } else if (itemType === 'guia') {
                if (itinerary.guide && itinerary.guide.id === itemId) {
                    itinerary.guide = null;
                    e.target.textContent = 'Agregar';
                    e.target.classList.remove('btn-success');
                    e.target.classList.add('btn-outline-primary');
                } else {
                    if (itinerary.guide) {
                        const prevButton = document.querySelector(`.add-to-itinerary-item[data-item-type="guia"][data-item-id="${itinerary.guide.id}"]`);
                        if (prevButton) {
                            prevButton.textContent = 'Agregar';
                            prevButton.classList.remove('btn-success');
                            prevButton.classList.add('btn-outline-primary');
                        }
                    }
                    itinerary.guide = { id: itemId, name: itemName };
                    e.target.textContent = 'Agregado';
                    e.target.classList.remove('btn-outline-primary');
                    e.target.classList.add('btn-success');
                }
                updateReviewList('review-guia', itinerary.guide ? [itinerary.guide] : []);
            }
        }
    });
    
    // Step 5: Review and Save
    const finalItineraryForm = document.getElementById('final-itinerary-form');
    finalItineraryForm.addEventListener('submit', (e) => {
        e.preventDefault();
        itinerary.name = document.getElementById('final-itinerary-name').value;
        const feedbackElement = document.getElementById('final-itinerary-feedback');

        if (!itinerary.name) {
            feedbackElement.innerHTML = '<div class="alert alert-danger">Por favor, dale un nombre a tu itinerario.</div>';
            return;
        }

        const formData = new FormData();
        formData.append('nombre_itinerario', itinerary.name);
        formData.append('ciudad', itinerary.city);
        formData.append('fecha_inicio', itinerary.startDate);
        formData.append('fecha_fin', itinerary.endDate);
        formData.append('destinos', JSON.stringify(itinerary.destinations.map(d => d.id)));
        if (itinerary.accommodation) {
            formData.append('alojamiento_id', itinerary.accommodation.id);
        }
        if (itinerary.guide) {
            formData.append('guia_id', itinerary.guide.id);
        }

        feedbackElement.innerHTML = '<div class="alert alert-info">Guardando tu itinerario...</div>';

        fetch('api/itinerarios.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                feedbackElement.innerHTML = `<div class="alert alert-success">¡Itinerario guardado con éxito! Redirigiendo...</div>`;
                setTimeout(() => {
                    window.location.href = 'itinerario.php?id=' + data.itinerary_id;
                }, 2000);
            } else {
                feedbackElement.innerHTML = `<div class="alert alert-danger">Error al guardar: ${data.error || 'Error desconocido.'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error saving itinerary:', error);
            feedbackElement.innerHTML = '<div class="alert alert-danger">Ocurrió un error de red. Por favor, intenta de nuevo.</div>';
        });
    });
    
    // Populate review section when moving to step 5
    document.getElementById('step5-tab').addEventListener('shown.bs.tab', () => {
        document.getElementById('final-itinerary-city').value = itinerary.city;
        document.getElementById('final-itinerary-dates').value = `${itinerary.startDate} al ${itinerary.endDate}`;
        updateReviewList('review-destinos', itinerary.destinations);
        updateReviewList('review-alojamiento', itinerary.accommodation ? [itinerary.accommodation] : []);
        updateReviewList('review-guia', itinerary.guide ? [itinerary.guide] : []);
    });


    function updateReviewList(listId, items) {
        const listElement = document.getElementById(listId);
        listElement.innerHTML = '';
        if (!items || items.length === 0) {
            listElement.innerHTML = '<li class="list-group-item text-muted">No has seleccionado nada aún.</li>';
            return;
        }
        items.forEach(item => {
            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.textContent = item.name;
            listElement.appendChild(li);
        });
    }
});

