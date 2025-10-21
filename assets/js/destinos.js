document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('destinos-grid')) {
        // Initial fetch for the first page and 'all' category
        fetchAllDestinos(1, 'all');
        fetchAndRenderFilters(); // Fetch categories and render filter buttons
    }
});

const ITEMS_PER_PAGE = 9; // Define how many items per page
let currentCategory = 'all'; // Keep track of the currently selected category

function fetchAllDestinos(page = 1, category = 'all') {
    const container = document.getElementById('destinos-grid');
    const spinner = document.getElementById('loading-spinner');
    const paginationContainer = document.getElementById('destinos-pagination');

    currentCategory = category; // Update current category

    if (spinner) spinner.style.display = 'block'; // Show spinner
    if (container) container.innerHTML = ''; // Clear previous content

    let apiUrl = `api/destinos.php?page=${page}&items_per_page=${ITEMS_PER_PAGE}`;
    if (category !== 'all') {
        apiUrl += `&category=${category}`;
    }

    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (spinner) spinner.style.display = 'none'; // Hide spinner

            if (data.success && data.data.length > 0) {
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
                renderPagination(data.total_destinos, page);
            } else {
                container.innerHTML = '<div class="col-12"><p class="text-center text-warning">No se encontraron destinos.</p></div>';
                if (paginationContainer) paginationContainer.innerHTML = ''; // Clear pagination if no results
            }
        })
        .catch(error => {
            console.error('Error al cargar los destinos:', error);
            if (spinner) spinner.style.display = 'none';
            if (container) container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Hubo un error al cargar los destinos.</p></div>';
            if (paginationContainer) paginationContainer.innerHTML = '';
        });
}

function renderPagination(totalItems, currentPage) {
    const paginationContainer = document.getElementById('destinos-pagination');
    if (!paginationContainer) return;

    paginationContainer.innerHTML = ''; // Clear previous pagination
    const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);

    if (totalPages <= 1) return; // No need for pagination if only one page

    const ul = document.createElement('ul');
    ul.className = 'pagination justify-content-center';

    // Previous button
    let liPrev = document.createElement('li');
    liPrev.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    liPrev.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}">Anterior</a>`;
    ul.appendChild(liPrev);

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        let li = document.createElement('li');
        li.className = `page-item ${currentPage === i ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
        ul.appendChild(li);
    }

    // Next button
    let liNext = document.createElement('li');
    liNext.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    liNext.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}">Siguiente</a>`;
    ul.appendChild(liNext);

    paginationContainer.appendChild(ul);

    // Add event listeners to page links
    ul.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const newPage = parseInt(this.dataset.page);
            if (!isNaN(newPage) && newPage >= 1 && newPage <= totalPages) {
                fetchAllDestinos(newPage, currentCategory); // Pass currentCategory
            }
        });
    });
}

// Function to fetch categories and render filter buttons
function fetchAndRenderFilters() {
    fetch('api/destinos.php?action=get_categories') // New API action to get unique categories
        .then(response => response.json())
        .then(data => {
            const filterContainer = document.getElementById('destinos-filters');
            if (!filterContainer) return;

            filterContainer.innerHTML = ''; // Clear previous filters

            // "All" button
            const allBtn = document.createElement('button');
            allBtn.className = `btn btn-outline-primary me-2 mb-2 ${currentCategory === 'all' ? 'active' : ''}`;
            allBtn.textContent = 'Todos';
            allBtn.addEventListener('click', () => {
                currentCategory = 'all';
                fetchAllDestinos(1, 'all');
                updateFilterButtons();
            });
            filterContainer.appendChild(allBtn);

            if (data.success && data.data.length > 0) {
                data.data.forEach(category => {
                    const btn = document.createElement('button');
                    btn.className = `btn btn-outline-primary me-2 mb-2 ${currentCategory === category ? 'active' : ''}`;
                    btn.textContent = category;
                    btn.addEventListener('click', () => {
                        currentCategory = category;
                        fetchAllDestinos(1, category);
                        updateFilterButtons();
                    });
                    filterContainer.appendChild(btn);
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar las categorías:', error);
        });
}

// Function to update active state of filter buttons
function updateFilterButtons() {
    document.querySelectorAll('#destinos-filters .btn').forEach(btn => {
        if (btn.textContent === currentCategory || (currentCategory === 'all' && btn.textContent === 'Todos')) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}
