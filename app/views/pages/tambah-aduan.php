<?php
// app/views/pages/tambah-aduan.php
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
        <span class="text">Tambah Aduan</span>
    </div>
</div>

<!-- Form Tambah Aduan -->
<div class="activity-wrapper form-wrapper">
    <div class="activity form-activity">
        <div class="form-container">
            <form id="formAduan" class="input-berita-form" action="index.php?page=store-aduan" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="noRegister">No. Register</label>
                    <input type="text" id="noRegister" name="noRegister" placeholder="Masukkan nomor register" required>
                </div>

                <div class="form-group">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" required>
                </div>

                <div class="form-group">
                    <label for="aduan">Aduan</label>
                    <textarea id="aduan" name="aduan" rows="4" placeholder="Masukkan isi aduan" required></textarea>
                </div>

                <div class="form-group">
                    <label for="jenisAduan">Jenis Aduan</label>
                    <input type="text" id="jenisAduan" name="jenisAduan" placeholder="Masukkan jenis aduan" required>
                </div>

                <div class="form-group">
                    <label for="mediaDigunakan">Media Digunakan</label>
                    <input type="text" id="mediaDigunakan" name="mediaDigunakan" placeholder="Masukkan media yang digunakan" required>
                </div>

                <div class="form-group">
                    <label for="tindakLanjut">Tindak Lanjut</label>
                    <textarea id="tindakLanjut" name="tindakLanjut" rows="3" placeholder="Masukkan tindak lanjut (opsional)"></textarea>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan tambahan (opsional)"></textarea>
                </div>

                <!-- Tombol Aksi -->
                <div class="form-actions" style="text-align:center; margin-top:20px;">
                    <button type="submit" class="btn-simpan">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=daftar-aduan'">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= $BASE ?>/js/tambah-aduan.js"></script>

<?php if (isset($_GET['status'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  <?php if ($_GET['status'] == 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Tambah Aduan Sukses!',
      text: 'Aduan berhasil ditambahkan.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      window.location.href = 'index.php?page=daftar-aduan';
    });
  <?php elseif ($_GET['status'] == 'error'): ?>
    Swal.fire({
      icon: 'error',
      title: 'Gagal Menyimpan Data!',
      text: 'Silakan coba lagi atau periksa data yang diinput.',
      showConfirmButton: true
    });
  <?php endif; ?>
});
</script>
<?php endif; ?>

