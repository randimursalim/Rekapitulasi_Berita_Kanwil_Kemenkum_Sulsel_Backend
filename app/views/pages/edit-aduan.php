<?php
// app/views/pages/edit-aduan.php
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
        <i class="fas fa-edit"></i>
        <span class="text">Edit Aduan</span>
    </div>
</div>

<!-- Form Edit Aduan -->
<div class="activity-wrapper form-wrapper">
    <div class="activity form-activity">
        <div class="form-container">
            <form id="formAduan" class="input-berita-form" action="index.php?page=update-aduan" method="POST" autocomplete="off">
                <input type="hidden" name="id" value="<?= htmlspecialchars($aduan['id_aduan']) ?>">
                
                <div class="form-group">
                    <label for="noRegister">No. Register</label>
                    <input type="text" id="noRegister" name="noRegister" 
                           value="<?= htmlspecialchars($aduan['no_register']) ?>" 
                           placeholder="Masukkan nomor register" required>
                </div>

                <div class="form-group">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" 
                           value="<?= htmlspecialchars($aduan['tanggal']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="aduan">Aduan</label>
                    <textarea id="aduan" name="aduan" rows="4" 
                              placeholder="Masukkan isi aduan" required><?= htmlspecialchars($aduan['aduan']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="jenisAduan">Jenis Aduan</label>
                    <input type="text" id="jenisAduan" name="jenisAduan" 
                           value="<?= htmlspecialchars($aduan['jenis_aduan']) ?>" 
                           placeholder="Masukkan jenis aduan" required>
                </div>

                <div class="form-group">
                    <label for="mediaDigunakan">Media Digunakan</label>
                    <input type="text" id="mediaDigunakan" name="mediaDigunakan" 
                           value="<?= htmlspecialchars($aduan['media_digunakan']) ?>" 
                           placeholder="Masukkan media yang digunakan" required>
                </div>

                <div class="form-group">
                    <label for="tindakLanjut">Tindak Lanjut</label>
                    <textarea id="tindakLanjut" name="tindakLanjut" rows="3" 
                              placeholder="Masukkan tindak lanjut (opsional)"><?= htmlspecialchars($aduan['tindak_lanjut'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="3" 
                              placeholder="Masukkan keterangan tambahan (opsional)"><?= htmlspecialchars($aduan['keterangan'] ?? '') ?></textarea>
                </div>

                <!-- Tombol Aksi -->
                <div class="form-actions" style="text-align:center; margin-top:20px;">
                    <button type="submit" class="btn-simpan">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=daftar-aduan'">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= $BASE ?>/js/edit-aduan.js"></script>

<?php if (isset($_GET['status'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  <?php if ($_GET['status'] == 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Update Aduan Sukses!',
      text: 'Data aduan berhasil diperbarui.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      window.location.href = 'index.php?page=daftar-aduan';
    });
  <?php elseif ($_GET['status'] == 'error'): ?>
    Swal.fire({
      icon: 'error',
      title: 'Gagal Memperbarui Data!',
      text: 'Silakan coba lagi atau periksa data yang diinput.',
      showConfirmButton: true
    });
  <?php endif; ?>
});
</script>
<?php endif; ?>

