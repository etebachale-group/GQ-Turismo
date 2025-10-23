/**
 * GQ-TURISMO - MOBILE ENHANCEMENTS
 * JavaScript para mejorar la experiencia en dispositivos móviles
 */

(function() {
    'use strict';

    // =============================================
    // MOBILE DETECTION
    // =============================================
    const isMobile = {
        Android: function() {
            return navigator.userAgent.match(/Android/i);
        },
        iOS: function() {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
        },
        any: function() {
            return (isMobile.Android() || isMobile.iOS());
        }
    };

    // =============================================
    // MOBILE MENU TOGGLE
    // =============================================
    const navToggle = document.getElementById('navToggle');
    const navMobile = document.getElementById('navMobile');
    
    if (navToggle && navMobile) {
        navToggle.addEventListener('click', function() {
            navToggle.classList.toggle('active');
            navMobile.classList.toggle('active');
            document.body.style.overflow = navMobile.classList.contains('active') ? 'hidden' : '';
        });

        // Close menu when clicking on a link
        const navLinks = navMobile.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navToggle.classList.remove('active');
                navMobile.classList.remove('active');
                document.body.style.overflow = '';
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !navMobile.contains(e.target)) {
                if (navMobile.classList.contains('active')) {
                    navToggle.classList.remove('active');
                    navMobile.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        });
    }

    // =============================================
    // TOUCH GESTURES
    // =============================================
    let touchStartX = 0;
    let touchEndX = 0;
    
    function handleSwipe() {
        const threshold = 100; // Minimum swipe distance
        
        if (touchEndX < touchStartX - threshold) {
            // Swipe left
            if (navMobile && navMobile.classList.contains('active')) {
                navToggle.classList.remove('active');
                navMobile.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
        
        if (touchEndX > touchStartX + threshold) {
            // Swipe right
            if (navMobile && !navMobile.classList.contains('active') && touchStartX < 30) {
                navToggle.classList.add('active');
                navMobile.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }
    }
    
    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    // =============================================
    // SMOOTH SCROLL WITH OFFSET (for fixed navbar)
    // =============================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#loginModal' && href !== '#registerModal') {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    const offsetTop = target.offsetTop - 80; // Navbar height
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // =============================================
    // LAZY LOADING IMAGES
    // =============================================
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // =============================================
    // VIEWPORT HEIGHT FIX (for mobile browsers)
    // =============================================
    function setVH() {
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }
    
    setVH();
    window.addEventListener('resize', setVH);
    window.addEventListener('orientationchange', setVH);

    // =============================================
    // PREVENT ZOOM ON INPUT FOCUS (iOS)
    // =============================================
    if (isMobile.iOS()) {
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            const fontSize = window.getComputedStyle(input).fontSize;
            if (parseFloat(fontSize) < 16) {
                input.style.fontSize = '16px';
            }
        });
    }

    // =============================================
    // BACK TO TOP BUTTON
    // =============================================
    const createBackToTop = () => {
        const btn = document.createElement('button');
        btn.className = 'back-to-top';
        btn.innerHTML = '<i class="bi bi-arrow-up"></i>';
        btn.setAttribute('aria-label', 'Volver arriba');
        document.body.appendChild(btn);
        
        btn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        });
    };
    
    if (window.innerWidth <= 768) {
        createBackToTop();
    }

    // =============================================
    // PULL TO REFRESH (PWA-like)
    // =============================================
    let pStart = { x: 0, y: 0 };
    let pCurrent = { x: 0, y: 0 };
    let pulling = false;
    
    function pullStart(e) {
        if (window.pageYOffset === 0) {
            const touch = e.touches[0];
            pStart.x = touch.screenX;
            pStart.y = touch.screenY;
        }
    }
    
    function pull(e) {
        if (window.pageYOffset === 0) {
            const touch = e.touches[0];
            pCurrent.x = touch.screenX;
            pCurrent.y = touch.screenY;
            
            const changeY = pStart.y < pCurrent.y ? Math.abs(pStart.y - pCurrent.y) : 0;
            
            if (changeY > 100) {
                pulling = true;
            }
        }
    }
    
    function pullEnd() {
        if (pulling) {
            // Show loading indicator
            const loadingIndicator = document.createElement('div');
            loadingIndicator.className = 'pull-refresh-loading';
            loadingIndicator.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>';
            document.body.insertBefore(loadingIndicator, document.body.firstChild);
            
            // Reload page after animation
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
        pulling = false;
    }
    
    if (isMobile.any()) {
        document.addEventListener('touchstart', pullStart);
        document.addEventListener('touchmove', pull);
        document.addEventListener('touchend', pullEnd);
    }

    // =============================================
    // TOUCH FEEDBACK FOR CARDS
    // =============================================
    const cards = document.querySelectorAll('.card, .btn, .nav-link');
    cards.forEach(card => {
        card.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });
        
        card.addEventListener('touchend', function() {
            this.style.transform = '';
        });
        
        card.addEventListener('touchcancel', function() {
            this.style.transform = '';
        });
    });

    // =============================================
    // OPTIMIZE MODALS FOR MOBILE
    // =============================================
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            // Prevent body scroll when modal is open
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            
            // Focus first input
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput && window.innerWidth <= 768) {
                setTimeout(() => firstInput.focus(), 300);
            }
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            // Restore body scroll
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
        });
    });

    // =============================================
    // OPTIMIZE DROPDOWNS FOR MOBILE
    // =============================================
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        if (window.innerWidth <= 768) {
            dropdown.addEventListener('show.bs.dropdown', function() {
                const menu = this.querySelector('.dropdown-menu');
                if (menu) {
                    // Adjust position to stay in viewport
                    setTimeout(() => {
                        const rect = menu.getBoundingClientRect();
                        if (rect.bottom > window.innerHeight) {
                            menu.style.top = 'auto';
                            menu.style.bottom = '100%';
                        }
                    }, 10);
                }
            });
        }
    });

    // =============================================
    // FORM VALIDATION FEEDBACK
    // =============================================
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                
                // Show first invalid field
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
            form.classList.add('was-validated');
        });
    });

    // =============================================
    // PERFORMANCE: Reduce animations on low-end devices
    // =============================================
    const isLowEndDevice = () => {
        return navigator.hardwareConcurrency <= 2 || navigator.deviceMemory <= 2;
    };
    
    if (isLowEndDevice()) {
        document.documentElement.style.setProperty('--transition-fast', '0ms');
        document.documentElement.style.setProperty('--transition-base', '100ms');
        document.documentElement.style.setProperty('--transition-slow', '200ms');
    }

    // =============================================
    // CONSOLE LOG FOR DEBUGGING
    // =============================================
    console.log('📱 Mobile Enhancements Loaded');
    console.log('Device Type:', isMobile.any() ? 'Mobile' : 'Desktop');
    console.log('Viewport:', window.innerWidth + 'x' + window.innerHeight);
    
})();
