<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jadwal Kegiatan - KEMENKUM SULSEL</title>
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
      <div class="search-box">
        <i class="uil uil-search"></i>
        <input type="text" placeholder="Cari Kegiatan">
      </div>
      <a href="edit-profil.html">
        <img src="images/user.jpg" alt="Profile" class="profile-link">
      </a>
    </div>

    <div class="dash-content">
      <div class="overview">
        <div class="title">
          <i class="uil uil-schedule"></i>
          <span class="text">Jadwal Kegiatan</span>
        </div>

        <!-- Tombol Tambah -->
        <div class="btn-container" style="margin: 15px 0;">
          <button class="btn-tambah" onclick="window.location.href='tambah-kegiatan.html'">
            <i class="uil uil-plus"></i> Tambah Kegiatan
          </button>
        </div>

        <!-- Tabel Jadwal Kegiatan -->
        <div class="activity" style="margin-top: 20px;">
          <div class="activity-data">

            <div class="data no">
              <span class="data-title">No</span>
              <span class="data-list">1</span>
              <span class="data-list">2</span>
              <span class="data-list">3</span>
              <span class="data-list">4</span>
            </div>

            <div class="data kegiatan">
              <span class="data-title">Nama Kegiatan</span>
              <span class="data-list">Rapat Koordinasi Bulanan</span>
              <span class="data-list">Sosialisasi Layanan Hukum</span>
              <span class="data-list">Workshop Digitalisasi</span>
              <span class="data-list">Rapat Sosialisasi</span>
            </div>

            <div class="data tanggal">
              <span class="data-title">Tanggal</span>
              <span class="data-list">2025-09-30</span>
              <span class="data-list">2025-10-05</span>
              <span class="data-list">2025-10-10</span>
              <span class="data-list">2025-10-11</span>
            </div>

            <div class="data waktu">
              <span class="data-title">Waktu</span>
              <span class="data-list">09.00-10.00</span>
              <span class="data-list">13.00-14.00</span>
              <span class="data-list">10.00-11.00</span>
              <span class="data-list">18.00-19.00</span>
            </div>

            <div class="data keterangan">
              <span class="data-title">Keterangan</span>
              <span class="data-list">Dihadiri oleh Kakanwil dan Kabag Humas. Membahas rencana kerja bulan depan.</span>
              <span class="data-list">Sosialisasi kepada ASN baru tentang layanan hukum dan inovasi digital.</span>
              <span class="data-list">Pelatihan penggunaan aplikasi digital rekap konten.</span>
              <span class="data-list">Koordinasi kegiatan publikasi internal antar subbagian.</span>
            </div>

            <div class="data status">
              <span class="data-title">Status</span>
              <span class="data-list status-selesai">Selesai</span>
              <span class="data-list status-ditunda">Ditunda</span>
              <span class="data-list status-dibatalkan">Dibatalkan</span>
              <span class="data-list status-belum">Belum Dimulai</span>
            </div>

            <div class="data actions">
              <span class="data-title">Aksi</span>
              <span class="data-list">
                <button class="btn-action-aksi view" onclick="showKeterangan(0)"><i class="uil uil-eye"></i></button>
                <button class="btn-action-aksi edit" onclick="window.location.href='edit-kegiatan.html'"><i class="uil uil-edit"></i></button>
                <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
              </span>
              <span class="data-list">
                <button class="btn-action-aksi view" onclick="showKeterangan(1)"><i class="uil uil-eye"></i></button>
                <button class="btn-action-aksi edit"><i class="uil uil-edit"></i></button>
                <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
              </span>
              <span class="data-list">
                <button class="btn-action-aksi view" onclick="showKeterangan(2)"><i class="uil uil-eye"></i></button>
                <button class="btn-action-aksi edit"><i class="uil uil-edit"></i></button>
                <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
              </span>
              <span class="data-list">
                <button class="btn-action-aksi view" onclick="showKeterangan(3)"><i class="uil uil-eye"></i></button>
                <button class="btn-action-aksi edit"><i class="uil uil-edit"></i></button>
                <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
              </span>
            </div>

          </div>
        </div>

        <!-- Modal Keterangan -->
        <div id="keteranganModal" class="modal">
          <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Detail Keterangan Kegiatan</h3>
            <p id="modalText"></p>
          </div>
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <button class="active">1</button>
          <button>2</button>
          <button>3</button>
          <button>Next</button>
        </div>

      </div>
    </div>
  </section>

  <script src="script.js"></script>
  <script>
    const keteranganList = [
      "Kegiatan ini dihadiri oleh Kakanwil, Kabag Humas, dan seluruh kasubbag. Membahas rencana kerja bulan depan, evaluasi program, serta strategi peningkatan publikasi.",
      "Sosialisasi kepada ASN baru tentang layanan hukum dan inovasi digital.",
      "Pelatihan penggunaan aplikasi digital rekap konten.",
      "Koordinasi kegiatan publikasi internal antar subbagian."
    ];

    function showKeterangan(index) {
      document.getElementById("modalText").textContent = keteranganList[index];
      document.getElementById("keteranganModal").style.display = "block";
    }

    function closeModal() {
      document.getElementById("keteranganModal").style.display = "none";
    }

    // Tutup modal jika klik di luar konten
    window.onclick = function(event) {
      const modal = document.getElementById("keteranganModal");
      if (event.target === modal) {
        modal.style.display = "none";
      }
    }
  </script>
</body>
</html>
