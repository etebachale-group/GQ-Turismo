    </main>
    
</div>

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin Mobile Enhancements -->
<script src="<?= $base_url ?>assets/js/admin-mobile.js"></script>

<!-- Admin Navigation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle (Top Nav)
    const navToggle = document.getElementById('navToggle');
    const navMobile = document.getElementById('navMobile');
    
    if (navToggle && navMobile) {
        navToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            navMobile.classList.toggle('active');
            document.body.style.overflow = navMobile.classList.contains('active') ? 'hidden' : '';
        });
        
        navMobile.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navToggle.classList.remove('active');
                navMobile.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
    }
    
    // Sidebar Toggle (Mobile)
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    console.log('Sidebar elements:', { sidebarToggle, adminSidebar, sidebarOverlay });
    console.log('Window width:', window.innerWidth);
    console.log('Is touch device:', 'ontouchstart' in window);
    
    if (sidebarToggle && adminSidebar && sidebarOverlay) {
        // Función para toggle
        function toggleSidebarFunc(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            console.log('Toggle sidebar triggered!');
            const isOpen = adminSidebar.classList.contains('show');
            console.log('Current state:', isOpen ? 'OPEN' : 'CLOSED');
            
            adminSidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
            document.body.style.overflow = adminSidebar.classList.contains('show') ? 'hidden' : '';
            
            console.log('New state:', adminSidebar.classList.contains('show') ? 'OPEN' : 'CLOSED');
            console.log('Classes:', adminSidebar.className);
        }
        
        // Click event
        sidebarToggle.addEventListener('click', toggleSidebarFunc);
        
        // Touch event para móviles
        sidebarToggle.addEventListener('touchend', function(e) {
            console.log('Touch event on toggle button');
            toggleSidebarFunc(e);
        });
        
        // Close on overlay click
        function closeSidebar(e) {
            if (e) {
                e.preventDefault();
            }
            console.log('Closing sidebar via overlay');
            adminSidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.style.overflow = '';
        }
        
        sidebarOverlay.addEventListener('click', closeSidebar);
        sidebarOverlay.addEventListener('touchend', closeSidebar);
        
        // Close sidebar when clicking a link on mobile
        adminSidebar.querySelectorAll('.admin-sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 991) {
                    console.log('Closing sidebar via link click');
                    closeSidebar();
                }
            });
        });
    } else {
        console.error('Missing sidebar elements!', {
            hasToggle: !!sidebarToggle,
            hasSidebar: !!adminSidebar,
            hasOverlay: !!sidebarOverlay
        });
    }
    
    // Auto-hide/show sidebar toggle button based on scroll (mobile)
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
        if (window.innerWidth <= 991) {
            const currentScroll = window.pageYOffset;
            const sidebarBtn = document.getElementById('sidebarToggle');
            
            if (sidebarBtn) {
                if (currentScroll > lastScroll && currentScroll > 100) {
                    // Scrolling down
                    sidebarBtn.style.transform = 'translateY(100px)';
                } else {
                    // Scrolling up
                    sidebarBtn.style.transform = 'translateY(0)';
                }
            }
            
            lastScroll = currentScroll;
        }
    });
    
    // Confirmation for delete actions
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || '¿Estás seguro de que quieres eliminar este elemento?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Form validation enhancement
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // DataTable-like search functionality (simple)
    const searchInputs = document.querySelectorAll('[data-table-search]');
    searchInputs.forEach(input => {
        const tableId = input.getAttribute('data-table-search');
        const table = document.getElementById(tableId);
        
        if (table) {
            input.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    });
});
</script>

<!-- Additional page-specific scripts can be added here -->
<?php if (isset($additional_scripts)): ?>
    <?= $additional_scripts ?>
<?php endif; ?>

</body>
</html>
