<div class="overview arsip-page">
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
          <button class="btn-action-aksi edit" onclick="window.location.href='index.php?page=edit-konten'"><i class="uil uil-edit"></i></button>
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

<!-- 🔹 Modal Preview -->
<div class="modal-img" id="imgModal">
  <img id="modalImage" src="" alt="Preview">
</div>

