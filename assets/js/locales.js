document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('locales-list')) {
        fetchLocales();
    }
});

function fetchLocales() {
    fetch('api/locales.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            const container = document.getElementById('locales-list');
            const spinner = document.getElementById('loading-spinner');
            
            if(spinner) spinner.style.display = 'none';

            if (data.success && data.data.length > 0) {
                container.innerHTML = ''; // Clear container

                data.data.forEach(local => {
                    const col = document.createElement('div');
                    col.className = 'col';
                    col.dataset.aos = 'fade-up';

                    col.innerHTML = `
                        <div class="card h-100 shadow-sm">
                            <img src="${local.imagen_perfil_url}" class="card-img-top" alt="Perfil de ${local.nombre_local}" style="height: 200px; object-fit: cover;">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">${local.nombre_local}</h5>
                                <p class="card-text text-muted">${local.descripcion ? local.descripcion.substring(0, 100) + '...' : 'No hay descripción disponible.'}</p>
                                <ul class="list-unstyled mt-3">
                                    <li><i class="bi bi-shop me-2"></i>Tipo: ${local.tipo_local || 'N/A'}</li>
                                    <li><i class="bi bi-geo-alt me-2"></i>Dirección: ${local.direccion || 'N/A'}</li>
                                    <li><i class="bi bi-envelope me-2"></i>${local.contacto_email}</li>
                                    <li><i class="bi bi-phone me-2"></i>${local.contacto_telefono || 'N/A'}</li>
                                </ul>
                                <div class="mt-auto pt-3">
                                    <a href="detalle_local.php?id=${local.id}" class="btn btn-sm btn-outline-primary">Ver Detalles</a>
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(col);
                });
            } else {
                container.innerHTML = '<div class="col-12"><p class="text-center text-warning">No se encontraron lugares o locales.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error al cargar los locales:', error);
            const container = document.getElementById('locales-list');
            const spinner = document.getElementById('loading-spinner');
            if(spinner) spinner.style.display = 'none';
            container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Hubo un error al cargar los locales.</p></div>';
        });
}
