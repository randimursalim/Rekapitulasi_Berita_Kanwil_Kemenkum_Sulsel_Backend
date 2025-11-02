<?php
// app/views/layouts/header.php

// Set basic security headers (safe approach)
if (!headers_sent()) {
    // Basic security headers that are safe
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // HTTPS Security Headers (only if HTTPS)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    
    // Remove server signature
    if (function_exists('header_remove')) {
        header_remove('Server');
        header_remove('X-Powered-By');
    }
}

// Auto-detect BASE_URL untuk localhost vs hosting
// Deteksi environment (sederhana, tanpa require config)
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$serverName = $_SERVER['SERVER_NAME'] ?? '';
$httpHost = $_SERVER['HTTP_HOST'] ?? '';

// Cek apakah localhost
$isLocalhost = (
    strpos($serverName, 'localhost') !== false ||
    strpos($serverName, '127.0.0.1') !== false ||
    strpos($httpHost, 'localhost') !== false ||
    strpos($requestUri, '/rekap-konten/public') !== false ||
    strpos($scriptName, '/rekap-konten/public') !== false
);

// Set BASE_URL berdasarkan environment
if ($isLocalhost) {
    // Localhost: gunakan BASE_URL dari config jika sudah defined, atau default
    $BASE = defined('BASE_URL') ? BASE_URL : '/rekap-konten/public';
} else {
    // Production hosting: kosong (handled by .htaccess)
    $BASE = '';
}
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; // Halaman default

function is_active($pageName) {
    global $currentPage;
    return $currentPage === $pageName ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= ucfirst($currentPage) ?> - KEMENKUM SULSEL</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/jpeg" href="<?= $BASE ?>/Images/LOGO KEMENKUM.jpeg">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="<?= $BASE ?>/css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <!-- Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- Custom Scripts -->
  <script src="<?= $BASE ?>/js/console-suppress.js"></script>
  <script src="<?= $BASE ?>/js/live-search.js"></script>
  <script src="<?= $BASE ?>/js/session-timeout.js"></script>
</head>
<body>
  <nav>
    <div class="logo-name">
      <div class="logo-image">
        <img src="<?= $BASE ?>/Images/LOGO KEMENKUM.jpeg" alt="Logo Kemenkum" />
      </div>
      <span class="logo_name">KEMENKUM SULSEL</span>
    </div>

    <div class="menu-items">
      <ul class="nav-links">
        <li><a href="<?= $BASE ?>/index.php?page=dashboard" class="<?= is_active('dashboard') ?>"><i class="fas fa-home"></i><span class="link-name">Dashboard</span></a></li>
        <li><a href="<?= $BASE ?>/index.php?page=input-konten" class="<?= is_active('input-konten') ?>"><i class="fas fa-plus-circle"></i><span class="link-name">Input Konten</span></a></li>
        <li><a href="<?= $BASE ?>/index.php?page=rekap-konten" class="<?= is_active('rekap-konten') ?>"><i class="fas fa-database"></i><span class="link-name">Rekap Konten</span></a></li>
        <li><a href="<?= $BASE ?>/index.php?page=arsip" class="<?= is_active('arsip') ?>"><i class="fas fa-archive"></i><span class="link-name">Arsip</span></a></li>
        <li><a href="<?= $BASE ?>/index.php?page=jadwal-kegiatan" class="<?= is_active('jadwal-kegiatan') ?>"><i class="fas fa-calendar-alt"></i><span class="link-name">Jadwal Kegiatan</span></a></li>
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'Admin'): ?>
        <li><a href="<?= $BASE ?>/index.php?page=pengguna" class="<?= is_active('pengguna') ?>"><i class="fas fa-users"></i><span class="link-name">Pengguna</span></a></li>
        <?php endif; ?>
      </ul>

      <ul class="logout-mode">
        <li><a href="<?= $BASE ?>/index.php?page=logout"><i class="fas fa-sign-out-alt"></i><span class="link-name">Logout</span></a></li>
        <li class="mode">
          <a href="#"><i class="fas fa-moon"></i><span class="link-name">Dark Mode</span></a>
          <div class="mode-toggle"><span class="switch"></span></div>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Konten utama -->
  <section class="dashboard">
    <div class="top">
      <i class="fas fa-bars sidebar-toggle"></i>

      <?php
      // Tampilkan search box di halaman tertentu
      $showSearch = in_array($currentPage, ['arsip', 'jadwal-kegiatan', 'pengguna']);
      ?>

      <?php if ($showSearch): ?>
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text"
                 class="live-search"
                 data-page="<?= $currentPage ?>"
                 placeholder="Cari..." />
        </div>
      <?php endif; ?>

      <div class="profile-info">
        <span class="user-info">
          <?= isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['nama']) : 'User' ?>
          <small>(<?= isset($_SESSION['user']) ? $_SESSION['user']['role'] : 'Guest' ?>)</small>
        </span>
        <a href="<?= $BASE ?>/index.php?page=edit-profil">
            <img src="<?= $BASE ?>/Images/<?= !empty($_SESSION['user']['foto']) && $_SESSION['user']['foto'] !== 'user.jpg' ? 'users/' . $_SESSION['user']['foto'] : 'user.jpg' ?>" alt="Profile" class="profile-link" />
        </a>
      </div>
    </div>

    <div class="dash-content">
