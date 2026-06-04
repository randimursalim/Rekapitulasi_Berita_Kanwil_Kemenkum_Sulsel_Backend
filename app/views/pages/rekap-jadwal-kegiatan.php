<div class="overview rekap-page">
  <!-- Judul -->
  <div class="title">
    <i class="fas fa-chart-pie"></i>
    <span class="text">Rekap Jadwal Kegiatan</span>
  </div>

  <!-- Filter Utama (Untuk Grafik & Tabel) -->
  <div class="filters" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:20px; padding: 15px; background: var(--panel-color); border: 1px solid var(--border-color); border-radius: 8px;">
    
    <label for="filterBulan">Bulan:</label>
    <select id="filterBulan" class="filter-select">
      <option value="all">Semua Bulan</option>
      <!-- Option akan diisi oleh JS -->
    </select>

    <label for="filterTahun">Tahun:</label>
    <select id="filterTahun" class="filter-select">
      <!-- Option akan diisi oleh JS -->
    </select>

    <label for="filterPimti">Kehadiran Pimti:</label>
    <select id="filterPimti" class="filter-select">
      <option value="all">Semua</option>
      <option value="kakanwil">Kakanwil</option>
      <option value="kadiv_p3h">Kadiv P3H</option>
      <option value="kadiv_yankum">Kadiv Yankum</option>
    </select>

    <label for="filterStatus">Status:</label>
    <select id="filterStatus" class="filter-select">
      <option value="all">Semua</option>
      <option value="Selesai">Selesai</option>
      <option value="Sedang Berlangsung">Sedang Berlangsung</option>
      <option value="Belum Dimulai">Belum Dimulai</option>
      <option value="Ditunda">Ditunda</option>
      <option value="Dibatalkan">Dibatalkan</option>
    </select>
    
    <label for="keywordInput">Cari Kata Kunci:</label>
    <input type="text" id="keywordInput" placeholder="Cari nama/ket kegiatan..." class="search-input" style="flex: 1; min-width: 150px; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px; background-color: var(--panel-color); color: var(--text-color);">

    <button id="applyFilter" class="btn-simpan">
      <i class="fas fa-search"></i> Terapkan
    </button>
    <button id="resetFilter" class="btn-batal">
      <i class="fas fa-sync-alt"></i> Reset
    </button>
  </div>

  <!-- Grafik -->
  <div class="chart-container" style="width: 100%; padding: 20px; background: var(--panel-color); border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 30px;">
    <div class="chart-wrapper" style="width:100%; max-width:900px; margin:auto;">
      <h3 style="text-align:center; margin-bottom:20px; color: var(--text-color);">Jumlah Kegiatan</h3>
      <canvas id="rekapChart"></canvas>
      <div id="totalKegiatan" style="margin-top:20px; font-weight:bold; font-size:16px; text-align:center; color: var(--text-color);">
        Total Kegiatan: 0
      </div>
    </div>
  </div>

  <!-- TABEL REKAP -->
  <div class="table-container" style="width:100%; padding: 20px; background: var(--panel-color); border: 1px solid var(--border-color); border-radius: 8px; overflow-x: auto;">
    <!-- Judul tabel dinamis -->
    <h3 id="tableTitle" style="text-align:center; margin-bottom:20px; color: var(--text-color);">
      REKAPITULASI JADWAL KEGIATAN
    </h3>

    <!-- Tombol download -->
    <div style="text-align:right; margin-bottom:15px;">
      <button id="downloadTableWord" class="btn-simpan btn-word">
        <i class="fas fa-file-word"></i> Download Word
      </button>
      <button id="downloadTableExcel" class="btn-simpan btn-excel">
        <i class="fas fa-file-excel"></i> Download Excel
      </button>
      <button id="downloadTablePDF" class="btn-batal btn-pdf">
        <i class="fas fa-file-pdf"></i> Download PDF
      </button>
    </div>

    <!-- Hasil Rekapitulasi (hanya menampilkan jumlah data) -->
    <div id="searchResultsSection" style="margin: 30px 0;">
      <div style="display: flex; justify-content: center; width: 100%;">
        <div id="searchResults" style="display: block !important; width: 100%; max-width: 600px; margin: 0 auto;">
          <!-- Data akan dimuat via AJAX -->
          <div style="text-align: center; padding: 40px; background: var(--panel-color); border: 1px solid var(--border-color); border-radius: 8px; width: 100%; color: var(--text-color);">
            <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: var(--primary-color);"></i>
            <p style="margin-top: 15px;">Memuat data...</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Load scripts -->
<!-- Gunakan library Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Library untuk export Word -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html-docx-js/0.3.1/html-docx.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<!-- Library untuk export Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<!-- Library untuk export PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script src="<?= $BASE ?>/js/rekap-jadwal-kegiatan.js?v=<?= time() ?>"></script>
