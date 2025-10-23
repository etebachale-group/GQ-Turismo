<!-- Mobile Sidebar Configuration - Include this in all admin pages -->
<style>
/* Mobile Sidebar Styles */
@media (max-width: 991.98px) {
    .sidebar {
        position: fixed;
        top: 0;
        left: -280px;
        width: 280px;
        height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transition: left 0.3s ease;
        z-index: 1050;
        overflow-y: auto;
    }
    
    .sidebar.active {
        left: 0;
    }
    
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
    }
    
    .sidebar-overlay.active {
        display: block;
    }
    
    .mobile-menu-toggle {
        display: block !important;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1000;
        background: #667eea;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 8px;
        cursor: pointer;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }
    
    .mobile-menu-toggle:hover {
        background: #5568d3;
    }
    
    .main-content {
        margin-left: 0 !important;
        padding-top: 60px;
    }
    
    .navbar {
        margin-left: 0 !important;
    }
}

@media (min-width: 992px) {
    .mobile-menu-toggle {
        display: none !important;
    }
    
    .sidebar-overlay {
        display: none !important;
    }
}
</style>

<!-- Mobile Menu Toggle Button -->
<button class="mobile-menu-toggle" id="mobileMenuToggle" style="display: none;">
    <i class="bi bi-list" style="font-size: 24px;"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    
    if (!sidebar || !sidebarOverlay || !mobileMenuToggle) return;
    
    // Toggle sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    }
    
    // Close sidebar
    function closeSidebar() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Event listeners
    mobileMenuToggle.addEventListener('click', toggleSidebar);
    sidebarOverlay.addEventListener('click', closeSidebar);
    
    // Close sidebar when clicking on a link
    const sidebarLinks = sidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                setTimeout(closeSidebar, 300);
            }
        });
    });
    
    // Close sidebar on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            closeSidebar();
        }
    });
});
</script>
