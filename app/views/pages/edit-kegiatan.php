<?php
// app/views/pages/edit-kegiatan.php
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
        <span class="text">Edit Kegiatan</span>
    </div>
</div>

<!-- Form Edit Kegiatan -->
<div class="activity-wrapper form-wrapper">
    <div class="activity form-activity">
        <div class="form-container">
            <form id="formKegiatan" class="input-berita-form" action="index.php?page=update-kegiatan" method="POST" autocomplete="off">
        <input type="hidden" name="id" value="<?= htmlspecialchars($kegiatan['id_kegiatan']) ?>">
        
        <div class="form-group">
            <label for="namaKegiatan">Nama Kegiatan</label>
            <input type="text" id="namaKegiatan" name="namaKegiatan" 
                   value="<?= htmlspecialchars($kegiatan['nama_kegiatan']) ?>" 
                   placeholder="Masukkan nama kegiatan" required>
        </div>

        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" 
                   value="<?= htmlspecialchars($kegiatan['tanggal']) ?>" required>
        </div>

        <div class="form-group">
            <label for="jamMulai">Jam Mulai</label>
            <input type="time" id="jamMulai" name="jamMulai" 
                   value="<?= htmlspecialchars($kegiatan['jam_mulai']) ?>" required>
        </div>

        <div class="form-group">
            <label for="jamSelesai">Jam Selesai</label>
            <input type="time" id="jamSelesai" name="jamSelesai" 
                   value="<?= htmlspecialchars($kegiatan['jam_selesai']) ?>" required>
        </div>

        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="3" 
                      placeholder="Masukkan keterangan kegiatan, misal peserta, tujuan, hasil rapat" required><?= htmlspecialchars($kegiatan['keterangan']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="">-- Pilih Status --</option>
                <option value="Selesai" <?= $kegiatan['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                <option value="Ditunda" <?= $kegiatan['status'] == 'Ditunda' ? 'selected' : '' ?>>Ditunda</option>
                <option value="Dibatalkan" <?= $kegiatan['status'] == 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                <option value="Belum Dimulai" <?= $kegiatan['status'] == 'Belum Dimulai' ? 'selected' : '' ?>>Belum Dimulai</option>
            </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="form-actions" style="text-align:center; margin-top:20px;">
            <button type="submit" class="btn-simpan">
                <i class="fas fa-save"></i> Update
            </button>
            <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=jadwal-kegiatan'">
                <i class="fas fa-times"></i> Batal
            </button>
        </div>
    </form>
        </div>
    </div>
</div>

<script src="<?= $BASE ?>/js/edit-kegiatan.js"></script>

<?php if (isset($_GET['status'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  <?php if ($_GET['status'] == 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Update Kegiatan Sukses!',
      text: 'Data kegiatan berhasil diperbarui.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      window.location.href = 'index.php?page=jadwal-kegiatan';
    });
  <?php elseif ($_GET['status'] == 'error'): ?>
    <?php if (isset($_GET['message']) && $_GET['message'] == 'jam'): ?>
      Swal.fire({
        icon: 'error',
        title: 'Validasi Gagal!',
        text: 'Jam selesai harus lebih besar dari jam mulai.',
        showConfirmButton: true
      });
    <?php else: ?>
      Swal.fire({
        icon: 'error',
        title: 'Gagal Memperbarui Data!',
        text: 'Silakan coba lagi atau periksa data yang diinput.',
        showConfirmButton: true
      });
    <?php endif; ?>
  <?php endif; ?>
});
</script><?php endif; ?>
