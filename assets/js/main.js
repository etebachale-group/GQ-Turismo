document.addEventListener('DOMContentLoaded', function() {
    // Inicializar AOS
    AOS.init({
        duration: 800,
        once: true
    });

    // Solo ejecutar en la página de destinos
    if (document.getElementById('destinos-grid')) {
        fetchDestinos();
    }
});

function fetchDestinos() {
    fetch('api/destinos.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            const grid = document.getElementById('destinos-grid');
            const spinner = document.getElementById('loading-spinner');
            
            // Ocultar el spinner
            if(spinner) spinner.style.display = 'none';

            if (data.success && data.data.length > 0) {
                data.data.forEach((destino, index) => {
                    const col = document.createElement('div');
                    col.className = 'col-lg-4 col-md-6 mb-4';
                    col.setAttribute('data-aos', 'fade-up');
                    col.setAttribute('data-aos-delay', index * 100);
                    
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
                    grid.appendChild(col);
                });

                // Refrescar AOS para que detecte los nuevos elementos
                setTimeout(() => {
                    AOS.refresh();
                }, 100);

            } else {
                grid.innerHTML = '<div class="col-12"><p class="text-center text-warning">No se encontraron destinos. Inténtalo de nuevo más tarde.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error al cargar los destinos:', error);
            const grid = document.getElementById('destinos-grid');
            const spinner = document.getElementById('loading-spinner');
            if(spinner) spinner.style.display = 'none';
            grid.innerHTML = '<div class="col-12"><p class="text-center text-danger">Hubo un error al cargar los destinos. Por favor, revisa la consola para más detalles.</p></div>';
        });
}
