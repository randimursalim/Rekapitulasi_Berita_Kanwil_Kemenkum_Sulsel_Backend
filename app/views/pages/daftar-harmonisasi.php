<?php
// app/views/pages/daftar-harmonisasi.php
// $BASE sudah didefinisikan di header.php, tidak perlu didefinisikan lagi
// Jika header.php belum di-include, gunakan fallback
if (!isset($BASE)) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    
    $isLocalhost = (
        strpos($serverName, 'localhost') !== false ||
        strpos($serverName, '127.0.0.1') !== false ||
        strpos($httpHost, 'localhost') !== false ||
        strpos($httpHost, '127.0.0.1') !== false ||
        strpos($requestUri, '/rekap-konten/public') !== false ||
        strpos($scriptName, '/rekap-konten/public') !== false ||
        (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && strpos($_SERVER['HTTP_X_FORWARDED_HOST'], 'localhost') !== false)
    );
    
    $BASE = $isLocalhost ? '/rekap-konten/public' : '';
    
    // Fallback: jika BASE kosong tapi script ada di subdirectory, deteksi otomatis
    if (empty($BASE) && strpos($scriptName, '/public/') !== false) {
        $pathParts = explode('/public/', $scriptName);
        if (count($pathParts) > 1) {
            $BASE = $pathParts[0] . '/public';
        }
    }
    
    // Pastikan BASE selalu dimulai dengan / jika tidak kosong
    if (!empty($BASE) && $BASE[0] !== '/') {
        $BASE = '/' . $BASE;
    }
    
    // Pastikan BASE tidak diakhiri dengan /
    $BASE = rtrim($BASE, '/');
}
?>

<div class="overview harmonisasi-page">
    <div class="title">
        <i class="fas fa-balance-scale"></i>
        <span class="text">Data Harmonisasi</span>
    </div>

    <!-- Tombol Tambah -->
    <div class="btn-container" style="margin: 15px 0;">
        <button class="btn-tambah" onclick="window.location.href='index.php?page=tambah-harmonisasi'">
            <i class="fas fa-plus"></i> Tambah Data Harmonisasi
        </button>
    </div>

    <!-- Filter -->
    <div class="filters" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom: 20px;">
        <label for="startDate">Tanggal Rapat:</label>
        <input type="date" id="startDate">
        <span>-</span>
        <input type="date" id="endDate">
        <label for="statusFilter">Status:</label>
        <select id="statusFilter" class="filter-select">
            <option value="">Semua Status</option>
            <option value="Diterima">Diterima</option>
            <option value="Dikembalikan">Dikembalikan</option>
        </select>
        <button id="filterBtn">Terapkan</button>
        <button id="resetBtn">Reset</button>
    </div>

    <!-- Tabel Harmonisasi -->
    <div class="activity-wrapper" style="margin-top: 20px;">
        <div class="activity">
            <div class="activity-data" id="harmonisasiResults">
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
            <h3>Detail Data Harmonisasi</h3>
            <div id="modalContent" style="max-height: 500px; overflow-y: auto; padding: 15px; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 8px; margin: 15px 0; color: var(--text-color); line-height: 1.6; font-size: 14px; white-space: pre-wrap;"></div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination" id="pagination">
        <!-- Pagination akan di-generate via JavaScript -->
    </div>
</div>

<?php if (isset($_GET['status'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  <?php if ($_GET['status'] == 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: '<?= isset($_GET['message']) ? htmlspecialchars(urldecode($_GET['message'])) : 'Operasi berhasil!' ?>',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      // Remove status parameter from URL untuk mencegah notifikasi muncul berulang saat refresh
      const newUrl = window.location.pathname + '?page=harmonisasi';
      window.history.replaceState({}, document.title, newUrl);
    });
  <?php elseif ($_GET['status'] == 'error'): ?>
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: '<?= isset($_GET['message']) ? htmlspecialchars(urldecode($_GET['message'])) : 'Terjadi kesalahan. Silakan coba lagi.' ?>',
      showConfirmButton: true
    }).then(() => {
      // Remove status parameter from URL untuk mencegah notifikasi muncul berulang saat refresh
      const newUrl = window.location.pathname + '?page=harmonisasi';
      window.history.replaceState({}, document.title, newUrl);
    });
  <?php endif; ?>
});
</script>
<?php endif; ?>

