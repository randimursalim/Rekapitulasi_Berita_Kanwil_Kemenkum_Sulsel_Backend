<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        overflow-y: scroll; /* reserve scrollbar space so sidebar doesn't shift between pages */
    }
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
        display: flex;
        min-height: 100vh;
        transition: all 0.3s ease;
    }

    .sidebar {
        width: 260px;
        background-color: #0D2052;
        color: white;
        padding: 20px 16px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 1000;
        overflow-y: auto;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
        font-family: Arial, sans-serif;
    }

    /* Brand block: logo + SI-APA + subtitle */
    .sidebar-brand {
        margin-bottom: 12px;
        text-align: center;
    }
    .sidebar-logo {
        width: 180px;
        height: auto;
        display: block;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 5px;
    }
    .sidebar-app-name {
        font-size: 1.4rem;
        font-weight: bold;
        color: #fff;
        margin: 0 0 2px 0;
        letter-spacing: 0.02em;
    }
    .sidebar-subtitle {
        font-size: 0.7rem;
        color: rgba(255,255,255,0.95);
        margin: 0;
        line-height: 1.3;
        letter-spacing: 0.02em;
    }
    .sidebar-sep {
        border: none;
        border-top: 1px solid rgba(255,255,255,0.4);
        margin: 14px 0;
    }

    /* User info: values only */
    .sidebar-user {
        font-size: 0.85rem;
        line-height: 1.5;
    }
    .sidebar-user-value {
        color: #fff;
        font-style: italic;
        margin-bottom: 2px;
    }
    .sidebar-user-value:first-child { margin-top: 0; }

    /* Nav sections */
    .sidebar-nav {
        margin-bottom: 0;
    }
    .nav-section {
        margin-bottom: 16px;
    }
    .nav-section-title {
        color: #fff;
        font-weight: bold;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 10px;
        padding: 0;
        background: transparent;
        border: none;
        border-left: none;
    }
    .nav-item {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        color: #fff;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        letter-spacing: 0.02em;
        transition: background 0.2s, color 0.2s;
        border-radius: 6px;
        margin-bottom: 2px;
    }
    .nav-item:hover {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        text-decoration: none;
    }
    .sidebar .nav-item.active {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        font-weight: 600;
    }
    .nav-item .nav-icon {
        margin-right: 12px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .nav-icon-svg {
        width: 20px;
        height: 20px;
        color: #fff;
    }
    .nav-item .nav-text {
        font-size: 0.9rem;
        line-height: 1.3;
    }
    .nav-item-logout {
        margin-top: 4px;
    }

    .main-content {
        flex: 1;
        background-color: white;
        border: 2px solid #0D2052;
        border-left: none;
        padding: 30px;
        margin-left: 260px;
        min-height: 100vh;
    }

    /* Interactive elements */
    .data-table tr {
        transition: all 0.2s ease;
    }
    
    .data-table tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .btn, .submit-btn, .view-details-btn, .download-pdf-btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .btn:hover, .submit-btn:hover, .view-details-btn:hover, .download-pdf-btn:hover {
        transform: translateY(-2px) scale(1.02);
    }

    /* Mobile Menu Toggle Button */
    .mobile-menu-toggle {
        display: none;
        position: fixed;
        top: 15px;
        right: 15px;
        z-index: 1100;
        background: #0D2052;
        color: white;
        border: none;
        padding: 12px 15px;
        border-radius: 8px;
        cursor: pointer;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        font-size: 24px;
        line-height: 1;
    }
    
    .mobile-menu-toggle:active {
        transform: scale(0.95);
    }
    
    /* Tablet and Mobile Styles */
    @media (max-width: 768px) {
        body {
            flex-direction: column;
        }
        
        .mobile-menu-toggle {
            display: block;
        }
        
        .sidebar {
            width: 280px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: -280px;
            padding: 70px 16px 20px;
            flex-direction: column;
            justify-content: flex-start;
            transition: left 0.3s ease;
            overflow-y: auto;
            z-index: 1050;
        }
        
        .sidebar.mobile-open {
            left: 0;
            box-shadow: 4px 0 15px rgba(0,0,0,0.3);
        }
        
        .sidebar-brand {
            margin-bottom: 12px;
        }
        .sidebar-logo {
            width: 120px;
            margin-left: auto;
            margin-right: auto;
        }
        .sidebar-nav {
            display: block;
            width: 100%;
        }
        
        .nav-item {
            padding: 15px 20px;
            font-size: 15px;
        }
        
        .main-content {
            margin-left: 0;
            padding: 70px 15px 20px;
            border-left: 2px solid #0D2052;
            border-top: none;
            width: 100%;
        }
        
        /* Page title */
        .page-title {
            font-size: 20px;
            margin-bottom: 20px;
        }
        
        /* Tables responsive - horizontal scroll */
        .data-table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            font-size: 12px;
        }
        
        .data-table th,
        .data-table td {
            padding: 10px 8px;
            font-size: 12px;
        }
        
        /* Buttons adjustment */
        .view-details-btn, .submit-evaluasi-btn, .download-pdf-btn, 
        .revisi-skp-btn, .evaluate-btn, .edit-btn {
            padding: 8px 12px;
            font-size: 11px;
            min-width: 90px;
            white-space: normal;
        }
        
        /* Form fields - prevent zoom on iOS */
        input[type="text"], input[type="number"], input[type="password"],
        select, textarea, .dropdown {
            font-size: 16px !important;
        }
        
        /* Filter sections */
        .filter-section, .generate-section {
            padding: 15px;
        }
        
        .filter-row, .generate-form {
            flex-direction: column;
            gap: 10px;
        }
        
        .filter-group, .form-group {
            width: 100%;
            min-width: 100%;
        }
        
        /* Grouped tables */
        .year-group {
            margin-left: 0;
            padding-left: 10px;
        }
        
        .name-header, .year-header {
            padding: 12px;
            font-size: 13px;
        }
        
        .group-header .group-info {
            font-size: 11px;
        }
        
        /* Status badges */
        .status-badge {
            padding: 5px 8px;
            font-size: 10px;
        }
        
        .name-badge, .year-badge {
            padding: 4px 8px;
            font-size: 11px;
        }
        
        /* Info grids */
        .info-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        
        /* Pagination */
        .pagination-controls {
            flex-wrap: wrap;
            font-size: 12px;
        }
    }
    
    @media (max-width: 480px) {
        .mobile-menu-toggle {
            top: 10px;
            right: 10px;
            padding: 10px 12px;
            font-size: 20px;
        }
        
        .main-content {
            padding: 60px 10px 15px;
        }
        
        .page-title {
            font-size: 18px;
        }
        
        .data-table {
            font-size: 11px;
        }
        
        .data-table th,
        .data-table td {
            padding: 8px 6px;
            font-size: 11px;
        }
        
        .view-details-btn, .submit-evaluasi-btn, .download-pdf-btn {
            padding: 6px 10px;
            font-size: 10px;
            min-width: 80px;
        }
        
        .filter-section, .generate-section {
            padding: 10px;
        }
        
        .name-badge, .year-badge {
            font-size: 10px;
        }
    }
    
    @media (min-width: 769px) {
        .sidebar {
            position: fixed;
        }
        
        .mobile-menu-toggle {
            display: none;
        }
    }

    /* Page loading overlay - smooth transition between pages */
    .page-load-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.92);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        opacity: 1;
        pointer-events: none;
        transition: opacity 0.35s ease;
    }
    body:not(.page-loaded) .page-load-overlay {
        pointer-events: auto;
    }
    body.page-navigating .page-load-overlay {
        opacity: 1;
        pointer-events: auto;
    }
    body.page-loaded .page-load-overlay {
        opacity: 0;
        pointer-events: none;
    }
    .page-load-spinner {
        width: 48px;
        height: 48px;
        border: 4px solid rgba(13, 32, 82, 0.15);
        border-top-color: #0D2052;
        border-radius: 50%;
        animation: page-load-spin 0.8s linear infinite;
    }
    @keyframes page-load-spin {
        to { transform: rotate(360deg); }
    }
</style>
