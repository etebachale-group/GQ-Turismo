document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('carousel-destinos-container')) {
        fetchDestinosCarousel();
    }
});

function fetchDestinosCarousel() {
    fetch('api/destinos.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            const container = document.getElementById('carousel-destinos-container');
            const spinner = document.getElementById('carousel-loading-spinner');
            
            if(spinner) spinner.style.display = 'none';

            if (data.success && data.data.length > 0) {
                // Clear container
                container.innerHTML = '';

                // Chunk destinations into groups of 3 for the carousel
                const chunkSize = 3;
                for (let i = 0; i < data.data.length; i += chunkSize) {
                    const chunk = data.data.slice(i, i + chunkSize);
                    
                    const carouselItem = document.createElement('div');
                    carouselItem.className = `carousel-item ${i === 0 ? 'active' : ''}`;
                    
                    const row = document.createElement('div');
                    row.className = 'row';

                    chunk.forEach(destino => {
                        const col = document.createElement('div');
                        col.className = 'col-lg-4 col-md-6 mb-4';
                        col.innerHTML = `
                            <div class="card h-100 shadow-sm">
                                <img src="${destino.imagen}" class="card-img-top" alt="${destino.nombre}">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">${destino.nombre}</h5>
                                    <p class="card-text text-muted">${destino.descripcion.substring(0, 90)}...</p>
                                    <div class="mt-auto">
                                        <a href="detalle_destino.php?id=${destino.id}" class="btn btn-sm btn-outline-primary">Más Detalles</a>
                                    </div>
                                </div>
                            </div>
                        `;
                        row.appendChild(col);
                    });

                    carouselItem.appendChild(row);
                    container.appendChild(carouselItem);
                }
            } else {
                container.innerHTML = '<div class="col-12"><p class="text-center text-warning">No se encontraron destinos para el carrusel.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error al cargar los destinos para el carrusel:', error);
            const container = document.getElementById('carousel-destinos-container');
            const spinner = document.getElementById('carousel-loading-spinner');
            if(spinner) spinner.style.display = 'none';
            container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Hubo un error al cargar los destinos.</p></div>';
        });
}
