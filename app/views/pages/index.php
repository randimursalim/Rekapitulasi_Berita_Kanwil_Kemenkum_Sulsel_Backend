<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - KEMENKUM SULSEL</title>
  <link rel="stylesheet" href="style.css" />
  <link
    rel="stylesheet"
    href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"
  />
</head>
<body>
  <!-- Sidebar -->
  <nav>
    <div class="logo-name">
      <div class="logo-image">
        <img src="images/LOGO KEMENKUM.jpeg" alt="Logo Kemenkum" />
      </div>
      <span class="logo_name">KEMENKUM SULSEL</span>
    </div>

    <div class="menu-items">
      <ul class="nav-links">
        <li>
          <a href="index.html" class="active">
            <i class="fas fa-home"></i>
            <span class="link-name">Dashboard</span>
          </a>
        </li>
        <li>
          <a href="input-konten.html">
            <i class="fas fa-plus-circle"></i>
            <span class="link-name">Input Konten</span>
          </a>
        </li>
        <li>
          <a href="rekap-konten.html">
            <i class="fas fa-database"></i>
            <span class="link-name">Rekap Konten</span>
          </a>
        </li>
        <li>
          <a href="arsip.html">
            <i class="fas fa-archive"></i>
            <span class="link-name">Arsip</span>
          </a>
        </li>
        <li>
          <a href="jadwal-kegiatan.html">
            <i class="fas fa-calendar-alt"></i>
            <span class="link-name">Jadwal Kegiatan</span>
          </a>
        </li>
        <li>
          <a href="pengguna.html">
            <i class="fas fa-users"></i>
            <span class="link-name">Pengguna</span>
          </a>
        </li>
      </ul>

      <ul class="logout-mode">
        <li>
          <a href="#">
            <i class="fas fa-sign-out-alt"></i>
            <span class="link-name">Logout</span>
          </a>
        </li>
        <li class="mode">
          <a href="#">
            <i class="fas fa-moon"></i>
            <span class="link-name">Dark Mode</span>
          </a>
          <div class="mode-toggle"><span class="switch"></span></div>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Dashboard -->
  <section class="dashboard">
    <div class="top">
      <i class="fas fa-bars sidebar-toggle"></i>
      <a href="edit-profil.html">
        <img src="images/user.jpg" alt="Profile" class="profile-link" />
      </a>
    </div>

    <div class="dash-content">
      <!-- Statistik -->
      <div class="overview">
        <div class="title">
          <i class="fas fa-tachometer-alt"></i>
          <span class="text">Dashboard</span>
        </div>

        <div class="boxes">
          <div class="box box1" onclick="showDetail('berita')" data-tooltip="Klik untuk lihat rincian">
            <i class="fas fa-newspaper"></i>
            <span class="text">Total Berita</span>
            <span class="number">80</span>
          </div>

          <div class="box box2" onclick="showDetail('medsos')" data-tooltip="Klik untuk lihat rincian">
            <i class="fas fa-share-alt"></i>
            <span class="text">Postingan Medsos</span>
            <span class="number">34</span>
          </div>

          <div class="box box3">
            <i class="fas fa-archive"></i>
            <span class="text">Total Arsip</span>
            <span class="number">114</span>
          </div>
        </div>

        <!-- Modal Detail -->
        <div id="detailModal" class="modal">
          <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle">Detail</h3>
            <ul id="modalList"></ul>
          </div>
        </div>
      </div>

      <!-- Log Aktivitas -->
      <div class="activity">
        <div class="title">
          <i class="fas fa-history"></i>
          <span class="text">Log Aktivitas</span>
        </div>

        <div class="activity-data">
          <div class="data activity-log">
            <span class="data-title">Aktivitas</span>
            <span class="data-list">Admin menambahkan berita baru</span>
            <span class="data-list">Operator mengedit postingan Instagram</span>
            <span class="data-list">Humas menghapus draft berita</span>
            <span class="data-list">Admin login ke sistem</span>
            <span class="data-list">Admin logout dari sistem</span>
          </div>

          <div class="data date">
            <span class="data-title">Tanggal</span>
            <span class="data-list">2025-09-08</span>
            <span class="data-list">2025-09-07</span>
            <span class="data-list">2025-09-06</span>
            <span class="data-list">2025-09-06</span>
            <span class="data-list">2025-09-10</span>
          </div>

          <div class="data time">
            <span class="data-title">Waktu</span>
            <span class="data-list">08:45</span>
            <span class="data-list">10:15</span>
            <span class="data-list">11:20</span>
            <span class="data-list">08:00</span>
            <span class="data-list">17:30</span>
          </div>

          <div class="data user">
            <span class="data-title">User</span>
            <span class="data-list">Admin</span>
            <span class="data-list">Operator</span>
            <span class="data-list">Humas</span>
            <span class="data-list">Admin</span>
            <span class="data-list">Admin</span>
          </div>

          <div class="data status">
            <span class="data-title">Status</span>
            <span class="data-list status-tambah">Tambah</span>
            <span class="data-list status-edit">Edit</span>
            <span class="data-list status-hapus">Hapus</span>
            <span class="data-list status-login">Login</span>
            <span class="data-list status-logout">Logout</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script src="script.js"></script>
</body>
</html>
