/* =============================================
   ADMIN MOBILE ENHANCEMENTS - GQ-Turismo
   Mejoras de UX para dispositivos móviles
   ============================================= */

document.addEventListener('DOMContentLoaded', function() {
    
    // =============================================
    // CONVERTIR TABLAS A CARDS EN MÓVIL
    // =============================================
    function makeTablesResponsive() {
        const tables = document.querySelectorAll('.table-responsive table');
        
        tables.forEach(table => {
            if (window.innerWidth <= 576) {
                // Agregar data-labels a las celdas
                const headers = table.querySelectorAll('thead th');
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    cells.forEach((cell, index) => {
                        if (headers[index]) {
                            cell.setAttribute('data-label', headers[index].textContent.trim());
                        }
                    });
                });
                
                table.classList.add('table-as-cards');
            } else {
                table.classList.remove('table-as-cards');
            }
        });
    }
    
    makeTablesResponsive();
    window.addEventListener('resize', makeTablesResponsive);
    
    // =============================================
    // BOTONES DE ACCIÓN EN STACK PARA MÓVIL
    // =============================================
    function stackActionButtons() {
        const actionCells = document.querySelectorAll('td:last-child, th:last-child');
        
        actionCells.forEach(cell => {
            const buttons = cell.querySelectorAll('.btn');
            if (buttons.length > 1 && window.innerWidth <= 576) {
                const wrapper = document.createElement('div');
                wrapper.className = 'action-buttons';
                
                cell.innerHTML = '';
                buttons.forEach(btn => {
                    wrapper.appendChild(btn.cloneNode(true));
                });
                cell.appendChild(wrapper);
            }
        });
    }
    
    if (window.innerWidth <= 576) {
        stackActionButtons();
    }
    
    // =============================================
    // MODALES FULLSCREEN EN MÓVIL
    // =============================================
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (window.innerWidth <= 576) {
            const dialog = modal.querySelector('.modal-dialog');
            if (dialog) {
                dialog.classList.add('modal-fullscreen-sm-down');
            }
        }
    });
    
    // =============================================
    // MEJORAR INPUTS PARA TÁCTIL
    // =============================================
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        // Agregar padding táctil
        if (window.innerWidth <= 767) {
            input.style.minHeight = '44px';
            input.style.fontSize = '16px'; // Prevenir zoom en iOS
        }
        
        // Focus visual mejorado
        input.addEventListener('focus', function() {
            this.style.borderColor = 'var(--primary)';
            this.style.boxShadow = '0 0 0 0.2rem rgba(102, 126, 234, 0.25)';
        });
        
        input.addEventListener('blur', function() {
            this.style.borderColor = '';
            this.style.boxShadow = '';
        });
    });
    
    // =============================================
    // DROPDOWN MEJORADO PARA MÓVIL
    // =============================================
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('show.bs.dropdown', function() {
            if (window.innerWidth <= 767) {
                const menu = this.querySelector('.dropdown-menu');
                if (menu) {
                    menu.style.width = '100%';
                    menu.style.maxWidth = 'calc(100vw - 20px)';
                }
            }
        });
    });
    
    // =============================================
    // SCROLL TO TOP EN FORMULARIOS LARGOS
    // =============================================
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Si hay errores de validación, scroll al primer campo con error
            const invalidField = form.querySelector(':invalid');
            if (invalidField) {
                e.preventDefault();
                invalidField.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                invalidField.focus();
            }
        });
    });
    
    // =============================================
    // PREVIEW DE IMÁGENES OPTIMIZADO
    // =============================================
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    let preview = input.parentElement.querySelector('.image-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'image-preview mt-2';
                        preview.style.maxWidth = window.innerWidth <= 576 ? '150px' : '200px';
                        preview.style.maxHeight = window.innerWidth <= 576 ? '150px' : '200px';
                        preview.style.borderRadius = '8px';
                        preview.style.objectFit = 'cover';
                        input.parentElement.appendChild(preview);
                    }
                    preview.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // =============================================
    // TABS SCROLL HORIZONTAL EN MÓVIL
    // =============================================
    const tabContainers = document.querySelectorAll('.nav-tabs');
    tabContainers.forEach(tabs => {
        if (window.innerWidth <= 767) {
            tabs.style.flexWrap = 'nowrap';
            tabs.style.overflowX = 'auto';
            tabs.style.webkitOverflowScrolling = 'touch';
            tabs.style.scrollbarWidth = 'thin';
            
            // Scroll al tab activo
            const activeTab = tabs.querySelector('.nav-link.active');
            if (activeTab) {
                setTimeout(() => {
                    activeTab.scrollIntoView({ 
                        behavior: 'smooth', 
                        inline: 'center',
                        block: 'nearest'
                    });
                }, 100);
            }
        }
    });
    
    // =============================================
    // GALERÍA DE IMÁGENES RESPONSIVE
    // =============================================
    const galleries = document.querySelectorAll('.gallery-grid');
    galleries.forEach(gallery => {
        if (window.innerWidth <= 576) {
            gallery.style.gridTemplateColumns = 'repeat(2, 1fr)';
            gallery.style.gap = '0.5rem';
        } else if (window.innerWidth <= 768) {
            gallery.style.gridTemplateColumns = 'repeat(3, 1fr)';
        }
    });
    
    // =============================================
    // CONFIRMACIONES MEJORADAS
    // =============================================
    const deleteButtons = document.querySelectorAll('[data-confirm], .btn-danger');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || '¿Estás seguro de realizar esta acción?';
            const confirmed = confirm(message);
            
            if (!confirmed) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            
            // Feedback visual
            if (this.tagName === 'BUTTON') {
                this.disabled = true;
                const originalHTML = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Procesando...';
                
                // Si es un enlace, permitir la navegación después del delay
                if (this.tagName === 'A') {
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.disabled = false;
                    }, 1000);
                }
            }
        });
    });
    
    // =============================================
    // ALERT AUTO-DISMISS CON PROGRESS BAR
    // =============================================
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        // Agregar progress bar
        const progressBar = document.createElement('div');
        progressBar.style.cssText = `
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: currentColor;
            opacity: 0.5;
            width: 100%;
            transition: width 5s linear;
        `;
        alert.style.position = 'relative';
        alert.style.overflow = 'hidden';
        alert.appendChild(progressBar);
        
        setTimeout(() => {
            progressBar.style.width = '0%';
        }, 10);
        
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
    
    // =============================================
    // STATS CARDS ANIMACIÓN AL SCROLL
    // =============================================
    const statCards = document.querySelectorAll('.stat-card');
    
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(20px)';
                entry.target.style.transition = 'all 0.5s ease';
                
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 100);
                
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    statCards.forEach(card => {
        observer.observe(card);
    });
    
    // =============================================
    // PAGINACIÓN COMPACTA EN MÓVIL
    // =============================================
    const pagination = document.querySelector('.pagination');
    if (pagination && window.innerWidth <= 576) {
        const items = pagination.querySelectorAll('.page-item');
        items.forEach((item, index) => {
            const isFirst = index === 0;
            const isLast = index === items.length - 1;
            const isActive = item.classList.contains('active');
            const isDisabled = item.classList.contains('disabled');
            const isNearActive = Math.abs(index - Array.from(items).findIndex(i => i.classList.contains('active'))) <= 1;
            
            if (!isFirst && !isLast && !isActive && !isDisabled && !isNearActive) {
                item.style.display = 'none';
            }
        });
    }
    
    // =============================================
    // BÚSQUEDA EN TABLA EN TIEMPO REAL
    // =============================================
    const searchInputs = document.querySelectorAll('[data-table-search]');
    searchInputs.forEach(input => {
        input.addEventListener('input', debounce(function() {
            const searchTerm = this.value.toLowerCase();
            const tableId = this.getAttribute('data-table-search');
            const table = document.getElementById(tableId);
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                let visibleCount = 0;
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    const isVisible = text.includes(searchTerm);
                    row.style.display = isVisible ? '' : 'none';
                    if (isVisible) visibleCount++;
                });
                
                // Mostrar mensaje si no hay resultados
                let noResultsRow = table.querySelector('.no-results-row');
                if (visibleCount === 0 && searchTerm !== '') {
                    if (!noResultsRow) {
                        noResultsRow = document.createElement('tr');
                        noResultsRow.className = 'no-results-row';
                        noResultsRow.innerHTML = `<td colspan="100" class="text-center py-4 text-muted">
                            <i class="bi bi-search me-2"></i>No se encontraron resultados para "${searchTerm}"
                        </td>`;
                        table.querySelector('tbody').appendChild(noResultsRow);
                    }
                } else if (noResultsRow) {
                    noResultsRow.remove();
                }
            }
        }, 300));
    });
    
    // =============================================
    // FORMULARIOS: GUARDAR BORRADOR EN LOCALSTORAGE
    // =============================================
    const formsWithDraft = document.querySelectorAll('form[data-draft]');
    formsWithDraft.forEach(form => {
        const draftKey = `draft_${form.getAttribute('data-draft')}`;
        
        // Cargar borrador al cargar la página
        const savedDraft = localStorage.getItem(draftKey);
        if (savedDraft) {
            try {
                const data = JSON.parse(savedDraft);
                Object.keys(data).forEach(key => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field) {
                        field.value = data[key];
                    }
                });
                
                // Mostrar notificación
                const notification = document.createElement('div');
                notification.className = 'alert alert-info alert-dismissible fade show';
                notification.innerHTML = `
                    <i class="bi bi-info-circle me-2"></i>
                    Se ha restaurado un borrador guardado.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                form.insertBefore(notification, form.firstChild);
            } catch(e) {
                console.error('Error al cargar borrador:', e);
            }
        }
        
        // Guardar borrador mientras se escribe
        const saveHandler = debounce(function() {
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            localStorage.setItem(draftKey, JSON.stringify(data));
        }, 1000);
        
        form.addEventListener('input', saveHandler);
        
        // Limpiar borrador al enviar
        form.addEventListener('submit', function() {
            localStorage.removeItem(draftKey);
        });
    });
    
    // =============================================
    // UTILIDAD: DEBOUNCE
    // =============================================
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // =============================================
    // COPIAR AL PORTAPAPELES
    // =============================================
    const copyButtons = document.querySelectorAll('[data-copy]');
    copyButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const textToCopy = this.getAttribute('data-copy');
            navigator.clipboard.writeText(textToCopy).then(() => {
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check"></i> Copiado';
                setTimeout(() => {
                    this.innerHTML = originalHTML;
                }, 2000);
            });
        });
    });
    
    // =============================================
    // TOOLTIPS TOUCH-FRIENDLY
    // =============================================
    if ('ontouchstart' in window) {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => {
            el.addEventListener('touchstart', function(e) {
                e.preventDefault();
                const tooltip = bootstrap.Tooltip.getInstance(el);
                if (tooltip) {
                    tooltip.show();
                    setTimeout(() => tooltip.hide(), 3000);
                }
            });
        });
    }
    
});
