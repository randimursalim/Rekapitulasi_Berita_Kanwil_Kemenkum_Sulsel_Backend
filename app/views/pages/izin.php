<?php
// app/views/pages/izin.php
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
            <i class="fas fa-file"></i>
            <span class="text">Manajemen Perizinan Online</span>
        </div>

        <!-- Filter Izin -->
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
        </div>

    </div>

    <!-- Tombol Switch -->
    <div class="btn-container" style="margin: 15px 0; display: flex; gap: 10px;">
        <button id="btnTabMasuk" class="btn-tambah active" onclick="switchTab('masuk')">
            <i class="fas fa-file-arrow-down"></i> Surat Masuk
        </button>
        <button id="btnTabBalasan" class="btn-tambah" onclick="switchTab('balasan')">
            <i class="fas fa-file-circle-check"></i> Surat Balasan
        </button>
        <button id="btnTabDashboard" class="btn-tambah" onclick="switchTab('dashboard')">
            <i class="fas fa-chart-bar"></i> Dashboard
        </button>
    </div>

    <!-- Data Perizinan -->
    <div class="overview izin-page">
        <div class="activity-wrapper" style="margin-top:20px;">
            <div class="activity">
                <div class="activity-data" id="izinResults">
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

<!-- Modal update status -->
<div id="statusModal" class="pdf-modal status-modal">
    <div class="pdf-modal-content status-modal-content">
        <span class="pdf-close" onclick="closeStatusModal()">&times;</span>

        <h3 class="modal-title">Update Status Pengajuan</h3>

        <form id="formUpdateStatus" onsubmit="submitUpdateStatus(event)">
            <input type="hidden" id="statusId" name="id">

            <div class="form-group">
                <label>Identitas Pengaju:</label>
                <input type="text" id="statusNama" disabled>
            </div>

            <div class="form-group">
                <label>Pilih Status Baru:</label>
                <select id="statusSelect" name="status" onchange="toggleKeterangan()">
                    <option value="1">1 - Diterima oleh Pengelola Surat Masuk</option>
                    <option value="2">2 - Ditolak karena tidak memenuhi persyaratan</option>
                    <option value="3">3 - Diterima oleh Kakanwil</option>
                    <option value="4">4 - Ditolak oleh Pimpinan</option>
                    <option value="5">5 - Diterima Kabag TU & Umum</option>
                    <option value="6">6 - Surat Balasan akan dikirim melalui WhatsApp yang terdaftar</option>
                </select>
            </div>

            <div class="form-group" id="keteranganGroup" style="display: none;">
                <label>Alasan<span style="color:red">*</span>:</label>
                <textarea id="statusKeterangan" name="keterangan" rows="3"
                    style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;"
                    placeholder="Tuliskan alasan penolakan..."></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Preview PDF -->
<div id="pdfModal" class="pdf-modal">
    <div class="pdf-modal-content">
        <span class="pdf-close" onclick="closePdfModal()">&times;</span>
        <iframe id="pdfViewer" src="" frameborder="0"> </iframe>
    </div>
</div>

<script>
    window.APP_BASE = "<?= $BASE ?>";
</script>
<script src="<?= $BASE ?>/js/izin.js"></script>