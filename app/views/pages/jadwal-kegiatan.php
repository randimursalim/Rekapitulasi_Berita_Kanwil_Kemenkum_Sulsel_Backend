<?php
// app/views/pages/jadwal-kegiatan.php
?>

<div class="overview">
    <div class="title">
        <i class="fas fa-calendar-alt"></i>
        <span class="text">Jadwal Kegiatan</span>
    </div>

    <!-- Tombol Tambah -->
    <div class="btn-container" style="margin: 15px 0;">
        <button class="btn-tambah" onclick="window.location.href='index.php?page=tambah-kegiatan'">
            <i class="fas fa-plus"></i> Tambah Kegiatan
        </button>
    </div>

    <!-- Filter -->
    <div class="filters" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom: 20px;">
        <label for="startDate">Tanggal:</label>
        <input type="date" id="startDate">
        <span>-</span>
        <input type="date" id="endDate">
        <button id="filterBtn">Terapkan</button>
        <button id="resetBtn">Reset</button>
    </div>

    <!-- Tabel Jadwal Kegiatan -->
    <div class="activity-wrapper" style="margin-top: 20px;">
        <div class="activity">
            <div class="activity-data" id="kegiatanResults">
                <!-- Data akan dimuat via AJAX -->
                <div style="text-align: center; padding: 20px;">
                    <p>Memuat data...</p>
            </div>
            </div>
        </div>
    </div>

    <!-- Modal Keterangan -->
    <div id="keteranganModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Detail Keterangan Kegiatan</h3>
            <div id="modalText" style="white-space: pre-wrap; max-height: 400px; overflow-y: auto; padding: 15px; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 8px; margin: 15px 0; color: var(--text-color); line-height: 1.6; font-size: 14px;"></div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination" id="pagination">
        <!-- Pagination akan di-generate via JavaScript -->
    </div>
</div>

<script src="/rekap-konten/public/js/jadwal-kegiatan.js"></script>
