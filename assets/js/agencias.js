document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('agencias-list')) {
        fetchAgencias();
    }
});

function fetchAgencias() {
    fetch('api/agencias.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            const container = document.getElementById('agencias-list');
            const spinner = document.getElementById('loading-spinner');
            
            if(spinner) spinner.style.display = 'none';

            if (data.success && data.data.length > 0) {
                container.innerHTML = ''; // Clear container

                data.data.forEach(agencia => {
                    const col = document.createElement('div');
                    col.className = 'col';
                    col.dataset.aos = 'fade-up';

                    col.innerHTML = `
                        <div class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">${agencia.nombre_agencia}</h5>
                                <p class="card-text text-muted">${agencia.descripcion ? agencia.descripcion.substring(0, 100) + '...' : 'No hay descripción disponible.'}</p>
                                <ul class="list-unstyled mt-3">
                                    <li><i class="bi bi-envelope me-2"></i>${agencia.contacto_email}</li>
                                    <li><i class="bi bi-phone me-2"></i>${agencia.contacto_telefono || 'N/A'}</li>
                                </ul>
                                <div class="mt-auto pt-3">
                                    <a href="detalle_agencia.php?id=${agencia.id}" class="btn btn-sm btn-outline-primary">Ver Servicios</a>
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(col);
                });
            } else {
                container.innerHTML = '<div class="col-12"><p class="text-center text-warning">No se encontraron agencias.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error al cargar las agencias:', error);
            const container = document.getElementById('agencias-list');
            const spinner = document.getElementById('loading-spinner');
            if(spinner) spinner.style.display = 'none';
            container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Hubo un error al cargar las agencias.</p></div>';
        });
}
