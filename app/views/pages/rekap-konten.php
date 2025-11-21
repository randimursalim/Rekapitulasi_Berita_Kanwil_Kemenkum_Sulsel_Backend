<div class="overview rekap-page">
  <!-- Judul -->
  <div class="title">
    <i class="fas fa-chart-bar"></i>
    <span class="text">Rekap Konten</span>
  </div>

<!-- Filter Chart -->
<div class="filters" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:20px;">
  <button class="filter-btn" data-filter="daily">Harian</button>
  <button class="filter-btn" data-filter="weekly">Mingguan</button>
  <button class="filter-btn active" data-filter="monthly">Bulanan</button>
  <button class="filter-btn" data-filter="yearly">Tahunan</button>
  
  <label for="start-date">Tanggal:</label>
  <input type="date" id="start-date"> 
  <span>-</span>
  <input type="date" id="end-date">
  <button id="apply-range">Terapkan</button>
  <button id="reset-filter">Reset</button>

  <label for="filterJenis">Jenis Konten:</label>
  <select id="filterJenis" class="filter-select">
    <option value="all">Semua</option>
    <option value="berita">Berita</option>
    <option value="media_online">Berita - Media Online</option>
    <option value="surat_kabar">Berita - Surat Kabar</option>
    <option value="website_kanwil">Berita - Website Kanwil</option>
    <option value="medsos">Media Sosial</option>
    <option value="facebook">Facebook</option>
    <option value="instagram">Instagram</option>
    <option value="tiktok">Tiktok</option>
    <option value="twitter">Twitter (X)</option>
    <option value="youtube">Youtube</option>
  </select>
</div>

<!-- Grafik -->
<div class="chart-container" style="width: 100%; padding: 20px 0;">
  <div class="chart-wrapper" style="width:100%; max-width:900px; margin:auto;">
    <h3 style="text-align:center; margin-bottom:20px;">Jumlah Konten</h3>
    <canvas id="rekapChart"></canvas>
    <div style="text-align:center; margin-top:20px;">
      <button id="downloadJPG" class="btn-simpan">Download JPG</button>
      <button id="downloadPDF" class="btn-simpan">Download PDF</button>
    </div>
    <div id="totalBerita" style="margin-top:20px; font-weight:bold; font-size:16px; text-align:center;">
      Total Konten: 0
    </div>
  </div>
</div>

<!-- TABEL REKAP -->
<div class="table-container" style="max-width:1000px; margin:30px auto;">
  <!-- Judul tabel dinamis -->
  <h3 id="tableTitle" style="text-align:center; margin-bottom:15px;">
    REKAP PUBLIKASI DAN GLORIFIKASI
  </h3>

  <!-- Filter Tabel -->
  <div class="filters" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; justify-content:center;">
    <select id="filterBulan" class="filter-select">
      <option value="">-- Pilih Bulan --</option>
    </select>
    <select id="filterTahun" class="filter-select">
      <option value="">-- Pilih Tahun --</option>
    </select>
    <button id="applyFilter">Terapkan</button>
  </div>

  <!-- Tabel Rekap -->
  <table id="rekapTable" style="width: 100%; table-layout: fixed;">
    <thead>
      <tr>
        <th style="width: 5%;">No</th>
        <th style="width: 12%;">Bulan</th>
        <th style="width: 12%;">Media Online/Cetak</th>
        <th style="width: 12%;">Website Kanwil</th>
        <th style="width: 10%;">Website SIPP</th>
        <th style="width: 10%;">Instagram</th>
        <th style="width: 10%;">TikTok</th>
        <th style="width: 10%;">Twitter (X)</th>
        <th style="width: 10%;">Youtube</th>
        <th style="width: 9%;">Facebook</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1.</td>
        <td id="bulanTabel">-</td>
        <td id="mediaOnline">0 Rilis Berita</td>
        <td id="websiteKanwil">0 Berita</td>
        <td>-</td>
        <td id="instagram">0 Postingan</td>
        <td id="tiktok">0 Video</td>
        <td id="twitter">0 Twit</td>
        <td id="youtube">0 Video</td>
        <td id="facebook">0 Postingan</td>
      </tr>
    </tbody>
  </table>

  <!-- Tombol download -->
  <div style="text-align:center; margin-top:20px;">
    <button id="downloadTablePDF" class="btn-simpan">Download PDF</button>
    <button id="downloadTableWord" class="btn-simpan">Download Word</button>
  </div>
</div>

<!-- Pencarian Kata Kunci -->
<div class="search-section" style="margin: 30px 0; padding: 20px; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 8px;">
  <h3 style="margin-bottom: 15px; color: var(--text-color); text-align: center;">Pencarian Kata Kunci</h3>
  
  <!-- Input Kata Kunci -->
  <div style="margin-bottom: 15px;">
    <label for="keywordInput" style="display: block; margin-bottom: 5px; color: var(--text-color);">Kata Kunci:</label>
    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
      <input type="text" id="keywordInput" placeholder="Masukkan kata kunci..." class="search-input" style="flex: 1; min-width: 200px; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px; background-color: var(--panel-color); color: var(--text-color);">
      <button id="addKeywordBtn" class="btn-simpan" style="padding: 8px 15px;">
        <i class="fas fa-plus"></i> Tambah Kata Kunci
      </button>
    </div>
    <small style="color: var(--text-color); opacity: 0.7;">Tekan Enter atau klik tombol untuk menambahkan kata kunci (opsional)</small>
  </div>

  <!-- Daftar Kata Kunci -->
  <div id="keywordsList" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; min-height: 30px;">
    <!-- Kata kunci akan ditambahkan di sini -->
  </div>

  <!-- Filter Pencarian -->
  <div class="filters" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:15px;">
    <label for="searchStartDate">Tanggal:</label>
    <input type="date" id="searchStartDate" class="search-input" style="background-color: var(--panel-color); color: var(--text-color); border: 1px solid var(--border-color); border-radius: 4px; padding: 6px;">
    <span>-</span>
    <input type="date" id="searchEndDate" class="search-input" style="background-color: var(--panel-color); color: var(--text-color); border: 1px solid var(--border-color); border-radius: 4px; padding: 6px;">
    
    <label for="searchFilterJenis">Jenis Konten:</label>
    <select id="searchFilterJenis" class="filter-select">
      <option value="all">Semua</option>
      <option value="berita">Berita</option>
      <option value="medsos">Media Sosial</option>
    </select>

    <label for="searchFilterKategori">Kategori/Platform:</label>
    <select id="searchFilterKategori" class="filter-select">
      <option value="all">Semua</option>
      <option value="media_online">Media Online</option>
      <option value="surat_kabar">Surat Kabar</option>
      <option value="website_kanwil">Website Kanwil</option>
      <option value="facebook">Facebook</option>
      <option value="instagram">Instagram</option>
      <option value="tiktok">TikTok</option>
      <option value="twitter">Twitter</option>
      <option value="youtube">YouTube</option>
    </select>

    <button id="searchBtn" class="btn-simpan">
      <i class="fas fa-search"></i> Cari
    </button>
    <button id="resetSearchBtn" class="btn-batal">
      <i class="fas fa-times"></i> Reset
    </button>
  </div>
</div>

<!-- Hasil Pencarian -->
<div id="searchResultsSection" style="display: none; margin: 30px 0;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
    <h3 style="color: var(--text-color);">Hasil Pencarian</h3>
    <div>
      <button id="downloadSearchWord" class="btn-simpan" style="margin-right: 10px;">
        <i class="fas fa-file-word"></i> Download Word
      </button>
      <button id="downloadSearchExcel" class="btn-simpan">
        <i class="fas fa-file-excel"></i> Download Excel
      </button>
    </div>
  </div>

  <!-- Hasil Pencarian (hanya menampilkan jumlah data) -->
  <div style="margin-top: 20px; display: flex; justify-content: center; width: 100%;">
    <div id="searchResults" style="display: block !important; width: 100%; max-width: 600px; margin: 0 auto;">
      <!-- Data akan dimuat via AJAX -->
      <div style="text-align: center; padding: 20px; width: 100%; background: var(--panel-color); color: var(--text-color);">
        <p style="color: var(--text-color);">Klik tombol "Cari" untuk menampilkan hasil pencarian</p>
      </div>
    </div>
  </div>
</div>
</div>
