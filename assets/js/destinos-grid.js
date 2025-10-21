document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('destinos-grid-container')) {
        fetchDestinosGrid();
    }
});

function fetchDestinosGrid() {
    fetch('api/destinos.php?limit=6') // Pide solo 6 para la página principal
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            const container = document.getElementById('destinos-grid-container');
            const spinner = document.getElementById('destinos-loading-spinner');
            
            if(spinner) spinner.style.display = 'none';

            if (data.success && data.data.length > 0) {
                // Limpiar el contenedor (excepto el spinner que ya está oculto)
                container.innerHTML = '';

                data.data.forEach(destino => {
                    const col = document.createElement('div');
                    col.className = 'col-lg-4 col-md-6 mb-4 d-flex align-items-stretch';
                    col.dataset.aos = 'fade-up';

                    col.innerHTML = `
                        <div class="card h-100 shadow-sm w-100">
                            <img src="${destino.imagen}" class="card-img-top" alt="${destino.nombre}">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">${destino.nombre}</h5>
                                <p class="card-text text-muted">${destino.descripcion.substring(0, 90)}...</p>
                                <div class="mt-auto pt-3">
                                    <a href="detalle_destino.php?id=${destino.id}" class="btn btn-sm btn-outline-primary">Más Detalles</a>
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(col);
                });
            } else {
                container.innerHTML = '<div class="col-12"><p class="text-center text-warning">No se encontraron destinos.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error al cargar los destinos:', error);
            const container = document.getElementById('destinos-grid-container');
            const spinner = document.getElementById('destinos-loading-spinner');
            if(spinner) spinner.style.display = 'none';
            container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Hubo un error al cargar los destinos.</p></div>';
        });
}
