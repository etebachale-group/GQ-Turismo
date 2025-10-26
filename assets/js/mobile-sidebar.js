/**
 * Sidebar Móvil - Sistema de navegación responsive
 * Incluir este archivo en todas las páginas del panel de administración
 */

(function() {
    'use strict';
    
    // Estilos CSS para el sidebar móvil
    const sidebarStyles = `
        <style id="mobile-sidebar-styles">
            /* Botón hamburguesa móvil */
            .mobile-menu-toggle {
                display: none;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 10001;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                width: 50px;
                height: 50px;
                border-radius: 12px;
                color: white;
                font-size: 24px;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                transition: all 0.3s ease;
            }
            
            .mobile-menu-toggle:hover {
                transform: scale(1.05);
            }
            
            .mobile-menu-toggle:active {
                transform: scale(0.95);
            }
            
            /* Overlay */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 9998;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }
            
            .sidebar-overlay.show {
                opacity: 1;
                visibility: visible;
            }
            
            /* Estilos móviles */
            @media (max-width: 992px) {
                .mobile-menu-toggle {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .sidebar {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    bottom: 0 !important;
                    width: 280px !important;
                    max-width: 80vw !important;
                    transform: translateX(-100%) !important;
                    transition: transform 0.3s ease !important;
                    z-index: 9999 !important;
                    overflow-y: auto !important;
                    box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2) !important;
                }
                
                .sidebar.show {
                    transform: translateX(0) !important;
                }
                
                /* Ajustar contenido principal */
                .main-content {
                    margin-left: 0 !important;
                    width: 100% !important;
                }
                
                /* Hacer que el contenedor principal ocupe todo el ancho */
                body.sidebar-open {
                    overflow: hidden;
                }
            }
            
            /* Para dispositivos muy pequeños */
            @media (max-width: 576px) {
                .sidebar {
                    width: 260px !important;
                    max-width: 85vw !important;
                }
                
                .mobile-menu-toggle {
                    width: 45px;
                    height: 45px;
                    font-size: 20px;
                }
            }
        </style>
    `;
    
    // Inyectar estilos
    if (!document.getElementById('mobile-sidebar-styles')) {
        document.head.insertAdjacentHTML('beforeend', sidebarStyles);
    }
    
    // Crear overlay si no existe
    function createOverlay() {
        if (!document.querySelector('.sidebar-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            overlay.id = 'sidebarOverlay';
            document.body.appendChild(overlay);
            return overlay;
        }
        return document.querySelector('.sidebar-overlay');
    }
    
    // Crear botón hamburguesa si no existe
    function createToggleButton() {
        if (!document.querySelector('.mobile-menu-toggle')) {
            const button = document.createElement('button');
            button.className = 'mobile-menu-toggle';
            button.id = 'mobileMenuToggle';
            button.innerHTML = '<i class="bi bi-list"></i>';
            button.setAttribute('aria-label', 'Toggle Menu');
            document.body.appendChild(button);
            return button;
        }
        return document.querySelector('.mobile-menu-toggle');
    }
    
    // Inicializar sidebar móvil
    function initMobileSidebar() {
        const sidebar = document.querySelector('.sidebar') || 
                       document.querySelector('#sidebar') ||
                       document.querySelector('aside');
        
        if (!sidebar) {
            console.warn('No se encontró elemento sidebar');
            return;
        }
        
        const overlay = createOverlay();
        const toggleBtn = createToggleButton();
        
        // Función para abrir sidebar
        function openSidebar() {
            sidebar.classList.add('show');
            overlay.classList.add('show');
            document.body.classList.add('sidebar-open');
        }
        
        // Función para cerrar sidebar
        function closeSidebar() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
        
        // Toggle sidebar
        function toggleSidebar() {
            if (sidebar.classList.contains('show')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }
        
        // Event listeners
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        });
        
        overlay.addEventListener('click', function() {
            closeSidebar();
        });
        
        // Cerrar al hacer click en links del sidebar (solo en móvil)
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    closeSidebar();
                }
            });
        });
        
        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });
        
        // Cerrar al cambiar tamaño de ventana a desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                closeSidebar();
            }
        });
        
        console.log('Sidebar móvil inicializado correctamente');
    }
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileSidebar);
    } else {
        initMobileSidebar();
    }
})();
