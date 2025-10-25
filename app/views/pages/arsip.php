<?php
// app/views/pages/arsip.php
?>

<div class="overview arsip-page">
  <div class="title">
    <i class="fas fa-archive"></i>
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
      <option value="twitter">Twitter</option>
      <option value="youtube">YouTube</option>
    </select>
  </div>

  <!-- 🔹 Arsip -->
  <div class="activity-wrapper" style="margin-top:20px;">
    <div class="activity">
      <div class="activity-data" id="searchResults">
        <!-- Data akan dimuat via AJAX -->
        <div style="text-align: center; padding: 20px;">
          <p>Memuat data...</p>
        </div>
      </div>
    </div>
  </div>

  <!-- 🔹 Pagination -->
  <div class="pagination" id="pagination">
    <!-- Pagination akan di-generate via JavaScript -->
  </div>
</div>

<!-- 🔹 Modal Preview -->
<div class="modal-img" id="imgModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); text-align:center; padding-top:5%;">
  <img id="modalImage" src="" alt="Preview" style="max-width:90%; max-height:80%;">
</div>

<script src="/rekap-konten/public/js/arsip.js"></script>
