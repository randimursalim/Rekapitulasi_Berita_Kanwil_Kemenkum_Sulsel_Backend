<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - KEMENKUM SULSEL</title>
  <link rel="stylesheet" href="/rekap-konten/public/css/style.css" />
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
</head>
<body>

<!-- Sidebar -->
<nav>
  <div class="logo-name">
    <div class="logo-image">
      <img src="/rekap-konten/public/images/LOGO KEMENKUM.jpeg" alt="Logo Kemenkum" />
    </div>
    <span class="logo_name">KEMENKUM SULSEL</span>
  </div>

  <div class="menu-items">
    <ul class="nav-links">
      <li>
        <a href="index.php" class="active">
          <i class="uil uil-estate"></i>
          <span class="link-name">Dashboard</span>
        </a>
      </li>
      <li>
        <a href="input-konten.php">
          <i class="uil uil-file-plus"></i>
          <span class="link-name">Input Konten</span>
        </a>
      </li>
      <li>
        <a href="rekap-konten.php">
          <i class="uil uil-database"></i>
          <span class="link-name">Rekap Konten</span>
        </a>
      </li>
      <li>
        <a href="arsip.php">
          <i class="uil uil-archive"></i>
          <span class="link-name">Arsip</span>
        </a>
      </li>
      <li>
        <a href="jadwal-kegiatan.php">
          <i class="uil uil-schedule"></i>
          <span class="link-name">Jadwal Kegiatan</span>
        </a>
      </li>
      <li>
        <a href="pengguna.php">
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

<!-- Mulai isi utama -->
<section class="dashboard">
  <div class="top">
    <i class="uil uil-bars sidebar-toggle"></i>
    <a href="edit-profil.php">
      <img src="images/user.jpg" alt="Profile" class="profile-link" />
    </a>
  </div>

  <div class="dash-content">
