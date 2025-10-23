/**
 * GQ-TURISMO - Mobile App Experience
 * Progressive Web App (PWA) Functionality
 * Version: 1.0
 */

(function() {
    'use strict';

    // =============================================
    // 1. MOBILE BOTTOM NAVIGATION
    // =============================================
    
    function initMobileBottomNav() {
        if (window.innerWidth <= 768) {
            // Crear bottom navigation si no existe
            if (!document.querySelector('.mobile-bottom-nav')) {
                const currentPage = window.location.pathname.split('/').pop() || 'index.php';
                
                const bottomNav = document.createElement('div');
                bottomNav.className = 'mobile-bottom-nav';
                bottomNav.innerHTML = `
                    <a href="index.php" class="mobile-nav-item ${currentPage === 'index.php' ? 'active' : ''}">
                        <i class="bi bi-house-fill"></i>
                        <span>Inicio</span>
                    </a>
                    <a href="destinos.php" class="mobile-nav-item ${currentPage === 'destinos.php' ? 'active' : ''}">
                        <i class="bi bi-compass-fill"></i>
                        <span>Destinos</span>
                    </a>
                    <a href="crear_itinerario.php" class="mobile-nav-item ${currentPage === 'crear_itinerario.php' ? 'active' : ''}">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>Itinerario</span>
                    </a>
                    <a href="mis_pedidos.php" class="mobile-nav-item ${currentPage === 'mis_pedidos.php' ? 'active' : ''}">
                        <i class="bi bi-cart-fill"></i>
                        <span>Pedidos</span>
                    </a>
                    <a href="about.php" class="mobile-nav-item ${currentPage === 'about.php' ? 'active' : ''}">
                        <i class="bi bi-person-fill"></i>
                        <span>Perfil</span>
                    </a>
                `;
                
                document.body.appendChild(bottomNav);
                
                // Añadir clase al body para ajustar padding
                document.body.classList.add('has-mobile-nav');
            }
        }
    }

    // =============================================
    // 2. PULL TO REFRESH
    // =============================================
    
    let touchStartY = 0;
    let pullRefreshIndicator = null;
    
    function initPullToRefresh() {
        if (window.innerWidth <= 768 && 'ontouchstart' in window) {
            // Crear indicador si no existe
            if (!pullRefreshIndicator) {
                pullRefreshIndicator = document.createElement('div');
                pullRefreshIndicator.className = 'pull-refresh-indicator';
                pullRefreshIndicator.innerHTML = `
                    <div class="spinner"></div>
                    <span>Actualizando...</span>
                `;
                document.body.appendChild(pullRefreshIndicator);
            }
            
            document.addEventListener('touchstart', function(e) {
                if (window.scrollY === 0) {
                    touchStartY = e.touches[0].clientY;
                }
            }, { passive: true });
            
            document.addEventListener('touchmove', function(e) {
                if (window.scrollY === 0) {
                    const touchY = e.touches[0].clientY;
                    const pullDistance = touchY - touchStartY;
                    
                    if (pullDistance > 80) {
                        pullRefreshIndicator.classList.add('show');
                    }
                }
            }, { passive: true });
            
            document.addEventListener('touchend', function(e) {
                if (pullRefreshIndicator.classList.contains('show')) {
                    // Recargar página
                    setTimeout(() => {
                        location.reload();
                    }, 300);
                }
                touchStartY = 0;
            }, { passive: true });
        }
    }

    // =============================================
    // 3. SMOOTH SCROLL & PAGE TRANSITIONS
    // =============================================
    
    function initPageTransitions() {
        // Añadir animación de entrada a la página
        document.body.classList.add('page-transition-enter');
        
        setTimeout(() => {
            document.body.classList.remove('page-transition-enter');
        }, 300);
        
        // Interceptar clicks en enlaces para animación de salida
        document.querySelectorAll('a:not([target="_blank"])').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Solo para enlaces internos
                if (href && href.startsWith('/') || href.includes('.php')) {
                    e.preventDefault();
                    
                    document.body.classList.add('page-transition-exit');
                    
                    setTimeout(() => {
                        window.location.href = href;
                    }, 300);
                }
            });
        });
    }

    // =============================================
    // 4. TOUCH RIPPLE EFFECT
    // =============================================
    
    function initRippleEffect() {
        document.querySelectorAll('.btn, .card, .ripple').forEach(element => {
            element.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                ripple.classList.add('ripple-effect');
                
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    }

    // =============================================
    // 5. OFFLINE DETECTION
    // =============================================
    
    function initOfflineDetection() {
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                document.body.appendChild(container);
            }
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideUp 0.3s ease-out forwards';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        window.addEventListener('online', () => {
            showToast('Conexión restaurada', 'success');
        });
        
        window.addEventListener('offline', () => {
            showToast('Sin conexión a internet', 'warning');
        });
    }

    // =============================================
    // 6. LAZY LOADING DE IMÁGENES
    // =============================================
    
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        const src = img.getAttribute('data-src');
                        
                        if (src) {
                            img.src = src;
                            img.classList.remove('skeleton-image');
                            img.classList.add('loaded');
                            observer.unobserve(img);
                        }
                    }
                });
            }, {
                rootMargin: '50px'
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        } else {
            // Fallback para navegadores sin IntersectionObserver
            document.querySelectorAll('img[data-src]').forEach(img => {
                img.src = img.getAttribute('data-src');
            });
        }
    }

    // =============================================
    // 7. INFINITE SCROLL
    // =============================================
    
    function initInfiniteScroll(containerSelector, loadMoreCallback) {
        const container = document.querySelector(containerSelector);
        
        if (!container || typeof loadMoreCallback !== 'function') return;
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    loadMoreCallback();
                }
            });
        }, {
            rootMargin: '200px'
        });
        
        const sentinel = document.createElement('div');
        sentinel.className = 'infinite-scroll-sentinel';
        container.appendChild(sentinel);
        
        observer.observe(sentinel);
    }

    // =============================================
    // 8. SWIPEABLE CARDS
    // =============================================
    
    function initSwipeableCards() {
        let startX = 0;
        let currentX = 0;
        let isDragging = false;
        
        document.querySelectorAll('.swipeable-card').forEach(card => {
            card.addEventListener('touchstart', function(e) {
                startX = e.touches[0].clientX;
                isDragging = true;
                this.classList.add('swiping');
            }, { passive: true });
            
            card.addEventListener('touchmove', function(e) {
                if (!isDragging) return;
                
                currentX = e.touches[0].clientX;
                const deltaX = currentX - startX;
                this.style.transform = `translateX(${deltaX}px)`;
            }, { passive: true });
            
            card.addEventListener('touchend', function(e) {
                if (!isDragging) return;
                
                const deltaX = currentX - startX;
                isDragging = false;
                this.classList.remove('swiping');
                
                // Si desliza más de 100px, hacer acción
                if (Math.abs(deltaX) > 100) {
                    if (deltaX > 0) {
                        // Deslizar derecha
                        this.style.transform = 'translateX(100%)';
                        setTimeout(() => this.remove(), 300);
                    } else {
                        // Deslizar izquierda
                        this.style.transform = 'translateX(-100%)';
                        setTimeout(() => this.remove(), 300);
                    }
                } else {
                    // Volver a posición original
                    this.style.transform = 'translateX(0)';
                }
            }, { passive: true });
        });
    }

    // =============================================
    // 9. HAPTIC FEEDBACK (si disponible)
    // =============================================
    
    function triggerHaptic(type = 'light') {
        if ('vibrate' in navigator) {
            switch(type) {
                case 'light':
                    navigator.vibrate(10);
                    break;
                case 'medium':
                    navigator.vibrate(20);
                    break;
                case 'heavy':
                    navigator.vibrate(30);
                    break;
                case 'success':
                    navigator.vibrate([10, 50, 10]);
                    break;
                case 'error':
                    navigator.vibrate([20, 50, 20, 50, 20]);
                    break;
            }
        }
    }

    // =============================================
    // 10. INSTALL PWA PROMPT
    // =============================================
    
    let deferredPrompt;
    
    function initPWAInstall() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Mostrar botón de instalación
            const installBtn = document.getElementById('installBtn');
            if (installBtn) {
                installBtn.style.display = 'block';
                
                installBtn.addEventListener('click', async () => {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const { outcome } = await deferredPrompt.userChoice;
                        
                        if (outcome === 'accepted') {
                            triggerHaptic('success');
                        }
                        
                        deferredPrompt = null;
                        installBtn.style.display = 'none';
                    }
                });
            }
        });
    }

    // =============================================
    // 11. FORM VALIDATION MEJORADA
    // =============================================
    
    function initFormValidation() {
        document.querySelectorAll('form').forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateInput(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid')) {
                        validateInput(this);
                    }
                });
            });
            
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!validateInput(input)) {
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    triggerHaptic('error');
                } else {
                    triggerHaptic('success');
                }
            });
        });
    }
    
    function validateInput(input) {
        const value = input.value.trim();
        let isValid = true;
        let errorMessage = '';
        
        // Validación requerido
        if (input.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'Este campo es requerido';
        }
        
        // Validación email
        if (input.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Email inválido';
            }
        }
        
        // Validación teléfono
        if (input.type === 'tel' && value) {
            const phoneRegex = /^[0-9+\s()-]+$/;
            if (!phoneRegex.test(value)) {
                isValid = false;
                errorMessage = 'Teléfono inválido';
            }
        }
        
        // Actualizar UI
        const feedbackEl = input.nextElementSibling;
        
        if (isValid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            if (feedbackEl && feedbackEl.classList.contains('invalid-feedback')) {
                feedbackEl.style.display = 'none';
            }
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            
            if (feedbackEl && feedbackEl.classList.contains('invalid-feedback')) {
                feedbackEl.textContent = errorMessage;
                feedbackEl.style.display = 'block';
            } else {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = errorMessage;
                input.parentNode.insertBefore(feedback, input.nextSibling);
            }
            
            triggerHaptic('light');
        }
        
        return isValid;
    }

    // =============================================
    // INICIALIZACIÓN
    // =============================================
    
    function init() {
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }
        
        // Inicializar funcionalidades
        initMobileBottomNav();
        initPullToRefresh();
        initPageTransitions();
        initRippleEffect();
        initOfflineDetection();
        initLazyLoading();
        initSwipeableCards();
        initPWAInstall();
        initFormValidation();
        
        console.log('🚀 GQ-Turismo Mobile App initialized');
    }
    
    // Ejecutar inicialización
    init();
    
    // Exponer funciones útiles globalmente
    window.GQTurismo = {
        triggerHaptic,
        initInfiniteScroll
    };

})();
