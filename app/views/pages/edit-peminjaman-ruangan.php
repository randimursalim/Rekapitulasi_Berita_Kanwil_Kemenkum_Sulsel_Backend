<?php
// app/views/pages/edit-peminjaman-ruangan.php
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
        <span class="text">Edit Peminjaman Ruangan</span>
    </div>
</div>

<!-- Form Edit Peminjaman Ruangan -->
<div class="activity-wrapper form-wrapper">
    <div class="activity form-activity">
        <div class="form-container">
            <form id="formPeminjamanRuangan" class="input-berita-form" action="index.php?page=update-peminjaman-ruangan" method="POST" autocomplete="off">
        <input type="hidden" name="id" value="<?= htmlspecialchars($peminjaman['id']) ?>">
        
        <div class="form-group">
            <label for="namaPeminjam">Nama Peminjam</label>
            <input type="text" id="namaPeminjam" name="namaPeminjam" 
                   value="<?= htmlspecialchars($peminjaman['nama_peminjam']) ?>" 
                   placeholder="Masukkan nama peminjam" required>
        </div>

        <div class="form-group">
            <label for="namaRuangan">Nama Ruangan</label>
            <select id="namaRuangan" name="namaRuangan" required>
                <option value="">-- Pilih Ruangan --</option>
                <option value="Ruang Rapat Baharuddin Lopa (Kakanwil)" <?= $peminjaman['nama_ruangan'] == 'Ruang Rapat Baharuddin Lopa (Kakanwil)' ? 'selected' : '' ?>>Ruang Rapat Baharuddin Lopa (Kakanwil)</option>
                <option value="Ruang Rapat Andi Mattalatta (Lantai 1)" <?= $peminjaman['nama_ruangan'] == 'Ruang Rapat Andi Mattalatta (Lantai 1)' ? 'selected' : '' ?>>Ruang Rapat Andi Mattalatta (Lantai 1)</option>
                <option value="Ruang Rapat Hamid Awaluddin (Lantai 2)" <?= $peminjaman['nama_ruangan'] == 'Ruang Rapat Hamid Awaluddin (Lantai 2)' ? 'selected' : '' ?>>Ruang Rapat Hamid Awaluddin (Lantai 2)</option>
                <option value="Ruang Rapat Bhinneka Tunggal Ika (Lantai 3)" <?= $peminjaman['nama_ruangan'] == 'Ruang Rapat Bhinneka Tunggal Ika (Lantai 3)' ? 'selected' : '' ?>>Ruang Rapat Bhinneka Tunggal Ika (Lantai 3)</option>
                <option value="Aula Pancasila (Lantai 3)" <?= $peminjaman['nama_ruangan'] == 'Aula Pancasila (Lantai 3)' ? 'selected' : '' ?>>Aula Pancasila (Lantai 3)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="kegiatan">Kegiatan</label>
            <input type="text" id="kegiatan" name="kegiatan" 
                   value="<?= htmlspecialchars($peminjaman['kegiatan']) ?>" 
                   placeholder="Masukkan nama kegiatan" required>
        </div>

        <div class="form-group">
            <label for="tanggalKegiatan">Tanggal Kegiatan</label>
            <input type="date" id="tanggalKegiatan" name="tanggalKegiatan" 
                   value="<?= htmlspecialchars($peminjaman['tanggal_kegiatan']) ?>" required>
        </div>

        <div class="form-group">
            <label for="waktuKegiatan">Waktu Kegiatan</label>
            <input type="time" id="waktuKegiatan" name="waktuKegiatan" 
                   value="<?= htmlspecialchars($peminjaman['waktu_kegiatan']) ?>" required>
        </div>

        <div class="form-group">
            <label for="durasiKegiatan">Durasi Kegiatan (jam)</label>
            <input type="number" id="durasiKegiatan" name="durasiKegiatan" 
                   min="1" max="8" value="<?= isset($peminjaman['durasi_kegiatan']) ? htmlspecialchars($peminjaman['durasi_kegiatan']) : '2' ?>" 
                   step="0.5" placeholder="Durasi dalam jam (default: 2 jam)" required>
            <small style="color: var(--text-color); opacity: 0.7;">Durasi minimal 1 jam, maksimal 8 jam</small>
        </div>

        <!-- Tombol Aksi -->
        <div class="form-actions" style="text-align:center; margin-top:20px;">
            <button type="submit" class="btn-simpan">
                <i class="fas fa-save"></i> Update
            </button>
            <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=jadwal-peminjaman-ruangan'">
                <i class="fas fa-times"></i> Batal
            </button>
        </div>
    </form>
        </div>
    </div>
</div>

<script src="<?= $BASE ?>/js/edit-peminjaman-ruangan.js"></script>

<?php if (isset($_GET['status'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  <?php if ($_GET['status'] == 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Update Peminjaman Ruangan Sukses!',
      text: 'Data peminjaman ruangan berhasil diperbarui.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      window.location.href = 'index.php?page=jadwal-peminjaman-ruangan';
    });
  <?php elseif ($_GET['status'] == 'error'): ?>
    Swal.fire({
      icon: 'error',
      title: 'Gagal Memperbarui Data!',
      text: '<?= isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Silakan coba lagi atau periksa data yang diinput.' ?>',
      showConfirmButton: true
    }).then(() => {
      window.history.replaceState({}, document.title, window.location.pathname + '?page=edit-peminjaman-ruangan&id=<?= htmlspecialchars($peminjaman['id']) ?>');
    });
  <?php endif; ?>
});
</script>
<?php endif; ?>


