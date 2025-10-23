<!-- Mobile Responsive Configuration - Global Mobile UX Fixes -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

<style>
/* Global Mobile Responsive Styles */
* {
    box-sizing: border-box;
}

html, body {
    overflow-x: hidden;
    max-width: 100vw;
}

/* Container and Layout Fixes */
@media (max-width: 991.98px) {
    .container-fluid {
        padding-left: 15px !important;
        padding-right: 15px !important;
    }
    
    .card {
        margin-bottom: 1rem;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 1rem;
    }
    
    .table {
        min-width: 600px;
        font-size: 0.875rem;
    }
    
    .table th,
    .table td {
        padding: 0.5rem !important;
        white-space: nowrap;
    }
    
    /* Form Controls */
    .form-control,
    .form-select,
    .btn {
        font-size: 16px !important; /* Prevents zoom on iOS */
    }
    
    /* Button Groups */
    .btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .btn-group .btn {
        flex: 1 1 auto;
        min-width: 100px;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
    
    /* Cards */
    .card-body {
        padding: 1rem !important;
    }
    
    /* Modals */
    .modal-dialog {
        margin: 0.5rem !important;
        max-width: calc(100% - 1rem) !important;
    }
    
    .modal-body {
        padding: 1rem !important;
    }
    
    /* Badges and Labels */
    .badge {
        font-size: 0.75rem !important;
        padding: 0.35em 0.65em !important;
    }
    
    /* Navigation */
    .navbar {
        padding: 0.5rem 1rem !important;
    }
    
    /* Stats Cards */
    .stats-card {
        margin-bottom: 1rem;
    }
    
    .stats-card .display-4 {
        font-size: 2rem !important;
    }
    
    /* Flex utilities */
    .d-flex.flex-wrap {
        gap: 0.5rem;
    }
    
    /* Images */
    img {
        max-width: 100%;
        height: auto;
    }
    
    /* Prevent horizontal scroll */
    .row {
        margin-left: -10px !important;
        margin-right: -10px !important;
    }
    
    .row > * {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }
}

@media (max-width: 575.98px) {
    /* Extra small devices */
    h1, .h1 { font-size: 1.75rem !important; }
    h2, .h2 { font-size: 1.5rem !important; }
    h3, .h3 { font-size: 1.25rem !important; }
    h4, .h4 { font-size: 1.1rem !important; }
    h5, .h5 { font-size: 1rem !important; }
    
    .btn-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }
    
    .table {
        font-size: 0.75rem !important;
    }
    
    /* Stack columns */
    .col-auto {
        width: 100% !important;
        margin-bottom: 0.5rem;
    }
}

/* Touch-friendly improvements */
@media (hover: none) and (pointer: coarse) {
    .btn,
    a,
    button,
    input[type="button"],
    input[type="submit"] {
        min-height: 44px;
        min-width: 44px;
    }
    
    .form-check-input {
        width: 20px;
        height: 20px;
    }
}
</style>
