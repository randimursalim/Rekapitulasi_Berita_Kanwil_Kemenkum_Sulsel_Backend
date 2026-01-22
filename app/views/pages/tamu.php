<?php
// app/views/pages/tamu.php
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
    <div class="page-header">
        <div class="page-title">
            <i class="fas fa-book"></i>
            <span class="text">Manajemen Buku Tamu</span>
        </div>

        <!-- Filter Tamu -->
        <div class="filter-bar">

            <select id="filterTahun">
                <?php
                $tahunSekarang = date('Y');
                for ($t = $tahunSekarang; $t >= $tahunSekarang - 5; $t--) {
                    echo "<option value='$t'>$t</option>";
                }
                ?>
            </select>

            <select id="filterBulan">
                <option value="all">Semua Bulan</option>
                <?php
                $bulan = [
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember'
                ];
                foreach ($bulan as $i => $b) {
                    echo "<option value='$i'>$b</option>";
                }
                ?>
            </select>

            <button id="btnCari" class="btn-cari">Cari</button>

            <button class="btn-print">Print</button>

        </div>

    </div>

    <!-- Tombol Tambah -->
    <div class="btn-container" style="margin: 15px 0;">
        <button class="btn-tambah" onclick="window.location.href='index.php?page=tambah-tamu'">
            <i class="fas fa-plus"></i> Tambah Tamu
        </button>
    </div>

    <!-- Data Tamu -->
    <div class="overview tamu-page">
        <div class="activity-wrapper" style="margin-top:20px;">
            <div class="activity">
                <div class="activity-data" id="tamuResults">
                    <!-- Data akan dimuat via AJAX -->
                    <div style="text-align: center; padding: 20px;">
                        <p>Memuat data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination" id="pagination">
        <!-- Pagination akan di-generate via JavaScript -->
    </div>
</div>

<!-- Modal Preview Gambar -->
<div id="imagePreviewModal" class="img-modal">
    <div class="img-modal-box">
        <span class="img-modal-close">&times;</span>

        <img class="img-modal-content" id="imgPreview">

        <div class="img-modal-caption" id="imgCaption"></div>
    </div>
</div>

<script src="<?= $BASE ?>/js/tamu.js"></script>