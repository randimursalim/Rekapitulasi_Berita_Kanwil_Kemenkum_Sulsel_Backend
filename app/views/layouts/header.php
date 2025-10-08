<?php
// app/views/layouts/header.php
$BASE = defined('BASE_URL') ? BASE_URL : '/rekap-konten/public';
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; // default halaman dashboard

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
  <link rel="stylesheet" href="<?= $BASE ?>/css/style.css" />
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
  
</head>
<body>
<nav>
  <div class="logo-name">
    <div class="logo-image">
      <img src="<?= $BASE ?>/images/LOGO KEMENKUM.jpeg" alt="Logo Kemenkum" />
    </div>
    <span class="logo_name">KEMENKUM SULSEL</span>
  </div>

  <div class="menu-items">
    <ul class="nav-links">
      <li>
        <a href="<?= $BASE ?>/index.php?page=dashboard" class="<?= is_active('dashboard') ?>">
          <i class="uil uil-estate"></i>
          <span class="link-name">Dashboard</span>
        </a>
      </li>
      <li>
        <a href="<?= $BASE ?>/index.php?page=input-konten" class="<?= is_active('input-konten') ?>">
          <i class="uil uil-file-plus"></i>
          <span class="link-name">Input Konten</span>
        </a>
      </li>
      <li>
        <a href="<?= $BASE ?>/index.php?page=rekap-konten" class="<?= is_active('rekap-konten') ?>">
          <i class="uil uil-database"></i>
          <span class="link-name">Rekap Konten</span>
        </a>
      </li>
      <li>
        <a href="<?= $BASE ?>/index.php?page=arsip" class="<?= is_active('arsip') ?>">
          <i class="uil uil-archive"></i>
          <span class="link-name">Arsip</span>
        </a>
      </li>
      <li>
        <a href="<?= $BASE ?>/index.php?page=jadwal-kegiatan" class="<?= is_active('jadwal-kegiatan') ?>">
          <i class="uil uil-schedule"></i>
          <span class="link-name">Jadwal Kegiatan</span>
        </a>
      </li>
      <li>
        <a href="<?= $BASE ?>/index.php?page=pengguna" class="<?= is_active('pengguna') ?>">
          <i class="uil uil-users-alt"></i>
          <span class="link-name">Pengguna</span>
        </a>
      </li>
    </ul>

    <ul class="logout-mode">
      <li>
        <a href="#">
          <i class="uil uil-signout"></i>
          <span class="link-name">Logout</span>
        </a>
      </li>
      <li class="mode">
        <a href="#">
          <i class="uil uil-moon"></i>
          <span class="link-name">Dark Mode</span>
        </a>
        <div class="mode-toggle"><span class="switch"></span></div>
      </li>
    </ul>
  </div>
</nav>

<!-- mulai konten -->
<section class="dashboard">
  <div class="top">
    <i class="uil uil-bars sidebar-toggle"></i>
    <a href="<?= $BASE ?>/index.php?page=edit-profil">
      <img src="<?= $BASE ?>/images/user.jpg" alt="Profile" class="profile-link" />
    </a>
  </div>

  <div class="dash-content">
