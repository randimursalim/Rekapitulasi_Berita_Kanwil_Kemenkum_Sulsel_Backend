<!-- Judul -->
<div class="title">
  <i class="uil uil-chart"></i>
  <span class="text">Rekap Konten</span>
</div>

<!-- Filter -->
<div class="form-container">
  <div class="filters" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;">
    <button class="filter-btn" data-filter="daily">Harian</button>
    <button class="filter-btn" data-filter="weekly">Mingguan</button>
    <button class="filter-btn" data-filter="monthly">Bulanan</button>
    <button class="filter-btn" data-filter="yearly">Tahunan</button>
    <input type="date" id="start-date"> 
    <span>-</span>
    <input type="date" id="end-date">
    <button id="apply-range">Terapkan</button>

    <!-- Tambahan filter jenis konten -->
    <select id="filterJenis" class="filter-select">
      <option value="all">Semua</option>
      <option value="berita">Berita</option>
      <option value="media_online">Berita - Media Online</option>
      <option value="surat_kabar">Berita - Surat Kabar</option>
      <option value="website_kanwil">Berita - Website Kanwil</option>
      <option value="facebook">Facebook</option>
      <option value="tiktok">Tiktok</option>
      <option value="twitter">Twitter (X)</option>
      <option value="youtube">Youtube</option>
    </select>
  </div>
</div>

<!-- Grafik -->
<div class="activity">
  <div class="activity-data" style="flex-direction: column; width: 100%;">
    <div class="form-container" style="width:100%; max-width:900px; margin:auto;">
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
</div>

<!-- TABEL REKAP -->
<div class="table-container" style="max-width:1000px; margin:30px auto;">
  <!-- Judul tabel dinamis -->
  <h3 id="tableTitle" style="text-align:center; margin-bottom:15px;">
    REKAP PUBLIKASI DAN GLORIFIKASI BULAN APRIL TAHUN 2023
  </h3>

  <!-- Filter Tabel -->
  <div class="filters" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; justify-content:center;">
    <select id="filterBulan" class="filter-select">
      <option value="januari">Januari</option>
      <option value="februari">Februari</option>
      <option value="maret">Maret</option>
      <option value="april" selected>April</option>
      <option value="mei">Mei</option>
      <option value="juni">Juni</option>
    </select>
    <select id="filterTahun" class="filter-select">
      <option value="2023" selected>2023</option>
      <option value="2024">2024</option>
      <option value="2025">2025</option>
    </select>
    <button id="applyFilter" class="filter-btn">Terapkan</button>
  </div>

  <!-- Dummy Tabel -->
  <table id="rekapTable">
    <thead>
      <tr>
        <th>No</th>
        <th>Bulan</th>
        <th>Media Online/Cetak</th>
        <th>Website Kanwil Sulsel</th>
        <th>Website SIPP</th>
        <th>Instagram</th>
        <th>Twitter (X)</th>
        <th>Youtube</th>
        <th>Facebook</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1.</td>
        <td>April</td>
        <td>314 Rilis Berita</td>
        <td class="highlight">48 Berita</td>
        <td>-</td>
        <td>24 Postingan</td>
        <td class="highlight">133 Twit</td>
        <td>4 Video</td>
        <td>67 Postingan</td>
      </tr>
    </tbody>
  </table>

  <!-- Tombol download -->
  <div style="text-align:center; margin-top:20px;">
    <button id="downloadTablePDF" class="btn-simpan">Download PDF</button>
    <button id="downloadTableWord" class="btn-simpan">Download Word</button>
  </div>
</div>
