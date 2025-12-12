<?php
// app/views/pages/tambah-harmonisasi.php
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
        <i class="fas fa-plus"></i>
        <span class="text">Tambah Data Harmonisasi</span>
    </div>
</div>

<!-- Form Tambah Harmonisasi -->
<div class="activity-wrapper form-wrapper">
    <div class="activity form-activity">
        <div class="form-container">
            <form id="formHarmonisasi" class="input-berita-form" action="index.php?page=store-harmonisasi" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="judul_rancangan">Judul Rancangan <span style="color: red;">*</span></label>
                    <input type="text" id="judul_rancangan" name="judul_rancangan" placeholder="Masukkan judul rancangan" required>
                </div>

                <div class="form-group">
                    <label for="pemrakarsa">Pemrakarsa <span style="color: red;">*</span></label>
                    <input type="text" id="pemrakarsa" name="pemrakarsa" placeholder="Masukkan pemrakarsa" required>
                </div>

                <div class="form-group">
                    <label for="pemerintah_daerah">Pemerintah Daerah <span style="color: red;">*</span></label>
                    <input type="text" id="pemerintah_daerah" name="pemerintah_daerah" placeholder="Masukkan pemerintah daerah" required>
                </div>

                <div class="form-group">
                    <label for="tanggal_rapat">Tanggal Rapat <span style="color: red;">*</span></label>
                    <input type="date" id="tanggal_rapat" name="tanggal_rapat" required>
                </div>

                <div class="form-group">
                    <label for="pemegang_draf">Pemegang Draf <span style="color: red;">*</span></label>
                    <input type="text" id="pemegang_draf" name="pemegang_draf" placeholder="Masukkan pemegang draf" required>
                </div>

                <div class="form-group">
                    <label for="status">Status <span style="color: red;">*</span></label>
                    <select id="status" name="status" required>
                        <option value="Diterima" selected>Diterima</option>
                        <option value="Dikembalikan">Dikembalikan</option>
                    </select>
                </div>

                <div class="form-group" id="alasanGroup" style="display: none;">
                    <label for="alasan_pengembalian_draf">Alasan Pengembalian Draf</label>
                    <textarea id="alasan_pengembalian_draf" name="alasan_pengembalian_draf" rows="4" placeholder="Tuliskan alasan pengembalian draf..."></textarea>
                </div>

                <div class="form-actions" style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn-simpan">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=harmonisasi'">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= $BASE ?>/js/tambah-harmonisasi.js"></script>

<?php if (isset($_GET['status'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  <?php if ($_GET['status'] == 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Tambah Data Harmonisasi Sukses!',
      text: '<?= isset($_GET['message']) ? htmlspecialchars(urldecode($_GET['message'])) : 'Data harmonisasi berhasil ditambahkan.' ?>',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      window.location.href = 'index.php?page=harmonisasi';
    });
  <?php elseif ($_GET['status'] == 'error'): ?>
    Swal.fire({
      icon: 'error',
      title: 'Gagal Menambahkan Data!',
      text: '<?= isset($_GET['message']) ? htmlspecialchars(urldecode($_GET['message'])) : 'Terjadi kesalahan saat menambahkan data harmonisasi. Silakan coba lagi.' ?>',
      showConfirmButton: true
    });
  <?php endif; ?>
});
</script>
<?php endif; ?>

