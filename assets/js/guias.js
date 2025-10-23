document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('guias-list')) {
        fetchGuias();
    }
});

function fetchGuias() {
    fetch('api/guias.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            const container = document.getElementById('guias-list');
            const spinner = document.getElementById('loading-spinner');
            
            if(spinner) spinner.style.display = 'none';

            if (data.success && data.data.length > 0) {
                container.innerHTML = ''; // Clear container

                data.data.forEach(guia => {
                    const col = document.createElement('div');
                    col.className = 'col';
                    col.dataset.aos = 'fade-up';

                    col.innerHTML = `
                        <div class="card h-100 shadow-sm">
                            <img src="${guia.imagen_perfil_url}" class="card-img-top" alt="Perfil de ${guia.nombre_guia}" style="height: 200px; object-fit: cover;">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">${guia.nombre_guia}</h5>
                                <p class="card-text text-muted">${guia.descripcion ? guia.descripcion.substring(0, 100) + '...' : 'No hay descripción disponible.'}</p>
                                <ul class="list-unstyled mt-3">
                                    <li><i class="bi bi-star-fill me-2 text-warning"></i>Especialidades: ${guia.especialidades || 'N/A'}</li>
                                    <li><i class="bi bi-cash me-2"></i>Precio/Hora: ${parseFloat(guia.precio_hora).toFixed(2)} €</li>
                                    <li><i class="bi bi-envelope me-2"></i>${guia.contacto_email}</li>
                                    <li><i class="bi bi-phone me-2"></i>${guia.contacto_telefono || 'N/A'}</li>
                                </ul>
                                <div class="mt-auto pt-3">
                                    <a href="detalle_guia.php?id=${guia.id}" class="btn btn-sm btn-outline-primary">Ver Perfil</a>
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(col);
                });
            } else {
                container.innerHTML = '<div class="col-12"><p class="text-center text-warning">No se encontraron guías turísticos.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error al cargar los guías:', error);
            const container = document.getElementById('guias-list');
            const spinner = document.getElementById('loading-spinner');
            if(spinner) spinner.style.display = 'none';
            container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Hubo un error al cargar los guías.</p></div>';
        });
}
