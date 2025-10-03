<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Arsip - KEMENKUM SULSEL</title>

  <!-- 🔹 Styles -->
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
  
  <style>
    /* Pastikan baris sejajar */
    .data-list {
      display: flex;
      align-items: center;
      height: 60px;
    }
  </style>
</head>
<body>
  <!-- 🔹 Sidebar -->
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
        <li><a href="arsip.html" class="active"><i class="uil uil-archive"></i><span class="link-name">Arsip</span></a></li>
        <li><a href="jadwal-kegiatan.html"><i class="uil uil-schedule"></i><span class="link-name">Jadwal Kegiatan</span></a></li>
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

  <!-- 🔹 Dashboard -->
  <section class="dashboard">
    <div class="top">
      <i class="uil uil-bars sidebar-toggle"></i>
      <div class="search-box">
        <i class="uil uil-search"></i>
        <input type="text" placeholder="Cari Konten">
      </div>
      <a href="edit-profil.html">
        <img src="images/user.jpg" alt="Profile" class="profile-link">
      </a>
    </div>

    <div class="dash-content">
      <div class="overview">
        <div class="title">
          <i class="uil uil-archive"></i>
          <span class="text">Arsip Konten</span>
        </div>

        <!-- 🔹 Filter -->
        <div class="filters" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
          <label for="startDate">Tanggal:</label>
          <input type="date" id="startDate">
          <span>-</span>
          <input type="date" id="endDate">
          <button id="filterBtn">Terapkan</button>
          <button id="resetBtn">Reset</button>

          <label for="filterJenis">Jenis Konten:</label>
          <select id="filterJenis" class="filter-select">
            <option value="all">Semua</option>
            <option value="berita">Berita</option>
            <option value="medsos">Media Sosial</option>
          </select>

          <label for="filterKategori">Kategori/Platform:</label>
          <select id="filterKategori" class="filter-select">
            <option value="all">Semua</option>
            <option value="media_online">Media Online</option>
            <option value="surat_kabar">Surat Kabar</option>
            <option value="website_kanwil">Website Kanwil</option>
            <option value="facebook">Facebook</option>
            <option value="instagram">Instagram</option>
            <option value="tiktok">TikTok</option>
            <option value="twitter">X (Twitter)</option>
            <option value="youtube">YouTube</option>
          </select>
        </div>

        <!-- 🔹 Arsip -->
        <div class="activity" style="margin-top:20px;">
          <div class="activity-data">
            <!-- No -->
            <div class="data no">
              <span class="data-title">No</span>
              <span class="data-list">1</span>
              <span class="data-list">2</span>
              <span class="data-list">3</span>
            </div>

            <!-- Judul -->
            <div class="data title-news">
              <span class="data-title">Judul</span>
              <span class="data-list">Dirjen AHU Pastikan Dukungan Layanan Publik di Sulsel Makin Optimal</span>
              <span class="data-list">Proses Pengajuan Permohonan Harmonisasi Ramperda & Ramperkada</span>
              <span class="data-list">Berita di Media Online</span>
            </div>

            <!-- Jenis -->
            <div class="data jenis">
              <span class="data-title">Jenis</span>
              <span class="data-list">Berita</span>
              <span class="data-list">Medsos</span>
              <span class="data-list">Berita</span>
            </div>

            <!-- Kategori -->
            <div class="data kategori">
              <span class="data-title">Kategori/Platform</span>
              <span class="data-list">Media Online</span>
              <span class="data-list">Twitter (X)</span>
              <span class="data-list">Media Online</span>
            </div>

            <!-- Tanggal -->
            <div class="data date">
              <span class="data-title">Tanggal</span>
              <span class="data-list">2025-09-27</span>
              <span class="data-list">2025-09-26</span>
              <span class="data-list">2025-08-15</span>
            </div>

            <!-- Dokumentasi -->
            <div class="data dokumentasi">
              <span class="data-title">Dokumentasi</span>
              <span class="data-list">
                <img src="images/Dirjen AHU Pastikan Dukungan Layanan Publik di Sulsel Makin Optimal.jpeg" alt="Foto 1">
              </span>
              <span class="data-list">
                <img src="images/Proses Pengajuan Permohonan Harmonisasi Ramperda dan Ramperkada 1.jpeg" alt="Foto 2">
              </span>
              <span class="data-list">
                <img src="images/dok3.jpg" alt="Foto 3">
              </span>
            </div>

            <!-- Aksi -->
            <div class="data actions">
              <span class="data-title">Aksi</span>

              <!-- Row 1 -->
              <span class="data-list">
                <button class="btn-action-aksi view"
                        onclick="window.open('https://www.sulselsatu.com/2025/09/27/news/dirjen-ahu-pastikan-dukungan-layanan-publik-di-sulsel-makin-optimal.html', '_blank')">
                  <i class="uil uil-eye"></i>
                </button>
                <button class="btn-action-aksi edit" onclick="window.location.href='edit-konten.html'"><i class="uil uil-edit"></i></button>
                <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
              </span>

              <!-- Row 2 -->
              <span class="data-list">
                <button class="btn-action-aksi view"
                        onclick="window.open('https://x.com/kemenkumsulsel/status/1971462462320119813', '_blank')">
                  <i class="uil uil-eye"></i>
                </button>
                <button class="btn-action-aksi edit"><i class="uil uil-edit"></i></button>
                <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
              </span>

              <!-- Row 3 -->
              <span class="data-list">
                <button class="btn-action-aksi view"
                        onclick="window.open('https://www.sulselsatu.com/2025/09/27/news/dirjen-ahu-pastikan-dukungan-layanan-publik-di-sulsel-makin-optimal.html', '_blank')">
                  <i class="uil uil-eye"></i>
                </button>
                <button class="btn-action-aksi edit"><i class="uil uil-edit"></i></button>
                <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
              </span>
            </div>
          </div>
        </div>

        <!-- 🔹 Pagination -->
        <div class="pagination">
          <button class="active">1</button>
          <button>2</button>
          <button>3</button>
          <button>Next</button>
        </div>
      </div>
    </div>
  </section>

  <!-- 🔹 Modal Preview -->
  <div class="modal-img" id="imgModal">
    <img id="modalImage" src="" alt="Preview">
  </div>

  <!-- 🔹 Scripts -->
  <script src="script.js"></script>
  <script>
    // Filter logic
    const filterBtn = document.getElementById("filterBtn");
    const resetBtn = document.getElementById("resetBtn");
    const startDateInput = document.getElementById("startDate");
    const endDateInput = document.getElementById("endDate");
    const filterJenis = document.getElementById("filterJenis");
    const filterKategori = document.getElementById("filterKategori");

    function applyFilters() {
      const startDate = startDateInput.value ? new Date(startDateInput.value) : null;
      const endDate = endDateInput.value ? new Date(endDateInput.value) : null;
      const jenis = filterJenis.value.toLowerCase();
      const kategori = filterKategori.value.toLowerCase();

      const totalRows = document.querySelectorAll(".activity-data .data.no .data-list").length;

      for (let i = 0; i < totalRows; i++) {
        const dateText = document.querySelectorAll(".activity-data .data.date .data-list")[i].innerText;
        const jenisText = document.querySelectorAll(".activity-data .data.jenis .data-list")[i].innerText.toLowerCase();
        const kategoriText = document.querySelectorAll(".activity-data .data.kategori .data-list")[i].innerText.toLowerCase();
        const dateVal = new Date(dateText);

        const matchDate = (!startDate || !endDate) || (dateVal >= startDate && dateVal <= endDate);
        const matchJenis = (jenis === "all" || jenisText.includes(jenis));
        const matchKategori = (kategori === "all" || kategoriText.includes(kategori));

        const visible = matchDate && matchJenis && matchKategori;

        document.querySelectorAll(".activity-data .data").forEach(col => {
          col.children[i + 1].style.display = visible ? "" : "none";
        });
      }
    }

    filterBtn.addEventListener("click", applyFilters);
    filterJenis.addEventListener("change", applyFilters);
    filterKategori.addEventListener("change", applyFilters);

    resetBtn.addEventListener("click", () => {
      startDateInput.value = "";
      endDateInput.value = "";
      filterJenis.value = "all";
      filterKategori.value = "all";
      document.querySelectorAll(".activity-data .data .data-list").forEach(el => el.style.display = "");
    });
  </script>
</body>
</html>
