<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pengguna - KEMENKUM SULSEL</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>
<body>
  <!-- Sidebar -->
  <nav>
    <div class="logo-name">
      <div class="logo-image">
        <img src="images/LOGO KEMENKUM.jpeg" alt="Logo Kemenkum">
      </div>
      <span class="logo_name">KEMENKUM SULSEL</span>
    </div>

    <div class="menu-items">
      <ul class="nav-links">
        <li><a href="index.html"><i class="uil uil-estate"></i><span class="link-name">Dashboard</span></a></li>
        <li><a href="input-konten.html"><i class="uil uil-file-plus"></i><span class="link-name">Input Konten</span></a></li>
        <li><a href="rekap-konten.html"><i class="uil uil-database"></i><span class="link-name">Rekap Konten</span></a></li>
        <li><a href="arsip.html"><i class="uil uil-archive"></i><span class="link-name">Arsip</span></a></li>
        <li><a href="jadwal-kegiatan.html" class="active"><i class="uil uil-schedule"></i><span class="link-name">Jadwal Kegiatan</span></a></li>
        <li><a href="pengguna.html" class="active"><i class="uil uil-users-alt"></i><span class="link-name">Pengguna</span></a></li>
      </ul>

      <ul class="logout-mode">
        <li><a href="#"><i class="uil uil-signout"></i><span class="link-name">Logout</span></a></li>
        <li class="mode">
          <a href="#"><i class="uil uil-moon"></i><span class="link-name">Dark Mode</span></a>
          <div class="mode-toggle"><span class="switch"></span></div>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Dashboard -->
  <section class="dashboard">
    <div class="top">
      <i class="uil uil-bars sidebar-toggle"></i>
       <div class="search-box">
        <i class="uil uil-search"></i>
        <input type="text" placeholder="Cari Pengguna">
      </div>
        <a href="edit-profil.html">
         <img src="images/user.jpg" alt="Profile" class="profile-link">
        </a>
    </div>

    <div class="dash-content">
      <div class="overview">
        <div class="title">
          <i class="uil uil-users-alt"></i>
          <span class="text">Manajemen Pengguna</span>
        </div>

        <!-- Data Pengguna -->
        <div class="activity" style="margin-top:20px;">
          <div class="activity-data">
            <div class="data no">
              <span class="data-title">No</span>
              <span class="data-list">1</span>
              <span class="data-list">2</span>
              <span class="data-list">3</span>
            </div>
            <div class="data name">
              <span class="data-title">Nama</span>
              <span class="data-list">Admin</span>
              <span class="data-list">User Operator</span>
              <span class="data-list">Staff Humas</span>
            </div>
            <div class="data username">
              <span class="data-title">Username</span>
              <span class="data-list">admin01</span>
              <span class="data-list">operator02</span>
              <span class="data-list">humas03</span>
            </div>
            <div class="data role">
              <span class="data-title">Role</span>
              <span class="data-list">Admin</span>
              <span class="data-list">Operator</span>
              <span class="data-list">Staff</span>
            </div>
            <div class="data actions">
              <span class="data-title">Aksi</span>
              <span class="data-list">
                <button class="btn-action-aksi edit" onclick="window.location.href='edit-pengguna.html'"><i class="uil uil-edit"></i></button>
                <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
              </span>
              <span class="data-list">
                <button class="btn-action-aksi edit"><i class="uil uil-edit"></i></button>
                <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
              </span>
              <span class="data-list">
                <button class="btn-action-aksi edit"><i class="uil uil-edit"></i></button>
                <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
              </span>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <button class="active">1</button>
          <button>2</button>
          <button>3</button>
          <button>Next</button>
        </div>

        <!-- Tombol tambah pengguna -->
        <div style="margin-top:20px; text-align:right;">
          <button class="btn-tambah" onclick="window.location.href='tambah-pengguna.html'">
  <i class="uil uil-plus"></i> Tambah Pengguna
</button>

        </div>
      </div>
    </div>
  </section>

  <script src="script.js"></script>
</body>
</html>
