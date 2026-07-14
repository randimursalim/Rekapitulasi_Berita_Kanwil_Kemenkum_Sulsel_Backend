<?php
// Sidebar component for consistent navigation across all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Get user data for sidebar
$user_data = [
    'nama' => $_SESSION['nama'] ?? 'Nama User',
    'nip' => $_SESSION['nip'] ?? 'NIP User',
    'jabatan' => $_SESSION['jabatan'] ?? 'Jabatan User'
];

// Determine if user is atasan
$is_atasan = (isset($_SESSION['atasan']) && $_SESSION['atasan'] === 'YA');

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
$is_manager_view = isset($_GET['manager']) && $_GET['manager'] == '1';

// Logo path (relative to page that includes this sidebar - all are in project root)
$sidebar_logo = 'images/SIAPA.png';

// Inline SVG icons (white outline style for dark blue sidebar)
$icon_doc = '<svg class="nav-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>';
$icon_doc_plus = '<svg class="nav-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><line x1="12" y1="15" x2="12" y2="19"/><line x1="10" y1="17" x2="14" y2="17"/></svg>';
$icon_doc_check = '<svg class="nav-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 15l2 2 4-4"/></svg>';
$icon_doc_pen = '<svg class="nav-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M11 14l2.5-2.5 4 4-2.5 2.5"/><path d="M13 12l2-2"/></svg>';
$icon_logout = '<svg class="nav-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>';
?>

<!-- Page loading overlay (first in body so it covers the page while loading) -->
<div class="page-load-overlay" id="page-load-overlay" aria-hidden="true">
    <div class="page-load-spinner"></div>
</div>

<!-- Mobile Menu Toggle -->
<button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Toggle Menu">
    ☰
</button>

<div class="sidebar" id="sidebar">
    <!-- Top: Logo + Brand -->
    <div class="sidebar-brand">
        <img src="<?= htmlspecialchars($sidebar_logo) ?>" alt="SI-APA" class="sidebar-logo">
    </div>
    <hr class="sidebar-sep">

    <!-- User info -->
    <div class="sidebar-user">
        <div class="sidebar-user-value"><?= htmlspecialchars($user_data['nama'] ?? 'Nama User') ?></div>
        <div class="sidebar-user-value"><?= htmlspecialchars($user_data['nip'] ?? 'NIP User') ?></div>
        <div class="sidebar-user-value"><?= htmlspecialchars($user_data['jabatan'] ?? 'Jabatan User') ?></div>
    </div>
    <hr class="sidebar-sep">

    <!-- SKP Section -->
    <div class="nav-section">
        <div class="nav-section-title">SKP</div>
        <a href="skploginpage.php" class="nav-item <?= ($current_page == 'skploginpage.php' && !$is_manager_view) ? 'active' : '' ?>">
            <span class="nav-icon"><?= $icon_doc ?></span>
            <span class="nav-text">SKP Triwulan</span>
        </a>
        <a href="skp_kuantitatif.php" class="nav-item <?= ($current_page == 'skp_kuantitatif.php' && !$is_manager_view) ? 'active' : '' ?>">
            <span class="nav-icon"><?= $icon_doc ?></span>
            <span class="nav-text">SKP Kuantitatif</span>
        </a>
        <a href="skp_akhir.php" class="nav-item <?= $current_page == 'skp_akhir.php' ? 'active' : '' ?>">
            <span class="nav-icon"><?= $icon_doc ?></span>
            <span class="nav-text">SKP Tahunan</span>
        </a>
        <?php
        $is_eselon = (isset($_SESSION['eselon']) && $_SESSION['eselon'] === 'YA');
        $skpbaru_link = $is_eselon ? 'skpbaru_eselon.php' : 'skpbaru.php';
        $skpbaru_active = ($current_page == 'skpbaru.php' || $current_page == 'skpbaru_eselon.php') ? 'active' : '';
        ?>
        <a href="<?= $skpbaru_link ?>" class="nav-item <?= $skpbaru_active ?>">
            <span class="nav-icon"><?= $icon_doc_plus ?></span>
            <span class="nav-text">SKP Baru</span>
        </a>
        <?php if ($is_atasan): ?>
        <a href="skploginpage.php?manager=1" class="nav-item <?= ($current_page == 'skploginpage.php' && $is_manager_view) ? 'active' : '' ?>">
            <span class="nav-icon"><?= $icon_doc_check ?></span>
            <span class="nav-text">Evaluasi SKP Triwulan</span>
        </a>
        <a href="skp_kuantitatif.php?manager=1" class="nav-item <?= ($current_page == 'skp_kuantitatif.php' && $is_manager_view) ? 'active' : '' ?>">
            <span class="nav-icon"><?= $icon_doc_check ?></span>
            <span class="nav-text">Evaluasi SKP Kuantitatif</span>
        </a>
        <a href="skp_akhir_evaluasi.php" class="nav-item <?= $current_page == 'skp_akhir_evaluasi.php' ? 'active' : '' ?>">
            <span class="nav-icon"><?= $icon_doc_check ?></span>
            <span class="nav-text">Evaluasi SKP Tahunan</span>
        </a>
        <?php endif; ?>
    </div>

    <!-- Lampiran Section -->
    <div class="nav-section">
        <div class="nav-section-title">LAMPIRAN</div>
        <a href="lampiran_list.php" class="nav-item <?= $current_page == 'lampiran_list.php' ? 'active' : '' ?>">
            <span class="nav-icon"><?= $icon_doc_check ?></span>
            <span class="nav-text">Lampiran</span>
        </a>
        <a href="skp_lampiran.php" class="nav-item <?= $current_page == 'skp_lampiran.php' ? 'active' : '' ?>">
            <span class="nav-icon"><?= $icon_doc_pen ?></span>
            <span class="nav-text">Lampiran Baru</span>
        </a>
        <?php if ($is_atasan): ?>
        <a href="lampiran_evaluasi.php" class="nav-item <?= $current_page == 'lampiran_evaluasi.php' ? 'active' : '' ?>">
            <span class="nav-icon"><?= $icon_doc_check ?></span>
            <span class="nav-text">Evaluasi Lampiran</span>
        </a>
        <?php endif; ?>
    </div>
    <hr class="sidebar-sep">

    <!-- Logout -->
    <a href="login.php" class="nav-item nav-item-logout">
        <span class="nav-icon"><?= $icon_logout ?></span>
        <span class="nav-text">Keluar</span>
    </a>
</div>

<script>
// Mobile menu toggle functionality
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('mobile-open');
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.querySelector('.mobile-menu-toggle');

    if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('mobile-open')) {
        if (!sidebar.contains(event.target) && event.target !== menuToggle) {
            sidebar.classList.remove('mobile-open');
        }
    }
});

// Close mobile menu when clicking on a nav item
document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.remove('mobile-open');
                }
            }
        });
    });

    // Mark page as loaded so loading overlay fades out
    document.body.classList.add('page-loaded');
});

// Page loading overlay: show when navigating to another page
(function() {
    function isSameOrigin(href) {
        if (!href || href === '#' || href.startsWith('javascript:')) return false;
        try {
            return new URL(href, window.location.origin).origin === window.location.origin;
        } catch (e) {
            return false;
        }
    }
    document.addEventListener('click', function(e) {
        var a = e.target.closest('a[href]');
        if (a && a.href && isSameOrigin(a.href)) {
            var t = (a.getAttribute('target') || '').toLowerCase();
            if (!t || t === '_self') {
                document.body.classList.add('page-navigating');
            }
        }
    }, true);
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (form && form.tagName === 'FORM' && form.method.toLowerCase() === 'get') return;
        if (form && form.getAttribute('target') === '_blank') return;
        document.body.classList.add('page-navigating');
    }, true);
})();
</script>
