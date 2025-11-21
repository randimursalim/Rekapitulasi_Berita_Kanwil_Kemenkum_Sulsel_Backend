<?php
// app/views/pages/daftar-aduan.php
// Auto-detect BASE_URL jika belum tersedia
if (!isset($BASE)) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    
    $isLocalhost = (
        strpos($serverName, 'localhost') !== false ||
        strpos($serverName, '127.0.0.1') !== false ||
        strpos($httpHost, 'localhost') !== false ||
        strpos($requestUri, '/rekap-konten/public') !== false ||
        strpos($scriptName, '/rekap-konten/public') !== false
    );
    
    $BASE = $isLocalhost ? 
        (defined('BASE_URL') ? BASE_URL : '/rekap-konten/public') : 
        '';
}
?>

<div class="overview">
    <div class="title">
        <i class="fas fa-exclamation-triangle"></i>
        <span class="text">Daftar Aduan</span>
    </div>

    <!-- Tombol Tambah -->
    <div class="btn-container" style="margin: 15px 0;">
        <button class="btn-tambah" onclick="window.location.href='index.php?page=tambah-aduan'">
            <i class="fas fa-plus"></i> Tambah Aduan
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

    <!-- Tabel Daftar Aduan -->
    <div class="activity-wrapper" style="margin-top: 20px;">
        <div class="activity">
            <div class="activity-data" id="aduanResults">
                <!-- Data akan dimuat via AJAX -->
                <div style="text-align: center; padding: 20px;">
                    <p>Memuat data...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Detail Aduan</h3>
            <div id="modalContent" style="white-space: pre-wrap; max-height: 400px; overflow-y: auto; padding: 15px; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 8px; margin: 15px 0; color: var(--text-color); line-height: 1.6; font-size: 14px;"></div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination" id="pagination">
        <!-- Pagination akan di-generate via JavaScript -->
    </div>
</div>

<script src="<?= $BASE ?>/js/daftar-aduan.js"></script>

