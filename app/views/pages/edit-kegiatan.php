<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Kegiatan - KEMENKUM SULSEL</title>
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
        <li><a href="pengguna.html"><i class="uil uil-users-alt"></i><span class="link-name">Pengguna</span></a></li>
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
      <a href="edit-profil.html">
        <img src="images/user.jpg" alt="Profile" class="profile-link">
      </a>
    </div>

    <div class="dash-content">
      <div class="overview">
        <div class="title">
          <i class="uil uil-edit"></i>
          <span class="text">Edit Kegiatan</span>
        </div>

        <!-- Form Edit Kegiatan -->
        <form action="update-kegiatan.php" method="post" class="input-berita-form" autocomplete="off">
          <!-- Hidden ID -->
          <input type="hidden" id="idKegiatan" name="idKegiatan" value="1">

          <div class="form-group">
            <label for="namaKegiatan">Nama Kegiatan</label>
            <input type="text" id="namaKegiatan" name="namaKegiatan" value="Rapat Koordinasi" required>
          </div>

          <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="2025-09-25" required>
          </div>

          <div class="form-group">
            <label for="jamMulai">Jam Mulai</label>
            <input type="time" id="jamMulai" name="jamMulai" value="09:00" required>
          </div>

          <div class="form-group">
            <label for="jamSelesai">Jam Selesai</label>
            <input type="time" id="jamSelesai" name="jamSelesai" value="11:00" required>
          </div>

          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
              <option value="Belum Dimulai" selected>Belum Dimulai</option>
              <option value="Selesai">Selesai</option>
              <option value="Ditunda">Ditunda</option>
              <option value="Dibatalkan">Dibatalkan</option>
            </select>
          </div>

          <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="5" placeholder="Masukkan detail kegiatan (misal: kegiatan dihadiri oleh..., membahas tentang...)" required>Rapat ini membahas koordinasi pelaksanaan kegiatan bulanan, dihadiri oleh seluruh staf humas.</textarea>
          </div>

          <!-- Tombol Aksi -->
          <div style="text-align:center; margin-top:20px;" class="form-actions">
            <button type="submit" class="btn-simpan">
              <i class="uil uil-save"></i> Update
            </button>
            <button type="button" class="btn-batal" onclick="window.location.href='jadwal-kegiatan.html'">
              <i class="uil uil-times"></i> Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>

  <script src="script.js"></script>
</body>
</html>
