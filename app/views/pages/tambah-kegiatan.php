<?php
// app/views/pages/tambah-kegiatan.php
?>

<div class="overview">
    <div class="title">
        <i class="fas fa-plus"></i>
        <span class="text">Tambah Kegiatan</span>
    </div>
</div>

<!-- Form Tambah Kegiatan -->
<div class="activity-wrapper form-wrapper">
    <div class="activity form-activity">
        <div class="form-container">
            <form id="formKegiatan" class="input-berita-form" action="index.php?page=store-kegiatan" method="POST" autocomplete="off">
        <div class="form-group">
            <label for="namaKegiatan">Nama Kegiatan</label>
            <input type="text" id="namaKegiatan" name="namaKegiatan" placeholder="Masukkan nama kegiatan" required>
        </div>

        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" required>
        </div>

        <div class="form-group">
            <label for="jamMulai">Jam Mulai</label>
            <input type="time" id="jamMulai" name="jamMulai" required>
        </div>

        <div class="form-group">
            <label for="jamSelesai">Jam Selesai</label>
            <input type="time" id="jamSelesai" name="jamSelesai" required>
        </div>

        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan kegiatan, misal peserta, tujuan, hasil rapat" required></textarea>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="">-- Pilih Status --</option>
                <option value="Selesai">Selesai</option>
                <option value="Ditunda">Ditunda</option>
                <option value="Dibatalkan">Dibatalkan</option>
                <option value="Belum Dimulai" selected>Belum Dimulai</option>
            </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="form-actions" style="text-align:center; margin-top:20px;">
            <button type="submit" class="btn-simpan">
                <i class="fas fa-save"></i> Simpan
            </button>
            <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=jadwal-kegiatan'">
                <i class="fas fa-times"></i> Batal
            </button>
        </div>
    </form>
        </div>
    </div>
</div>

<script src="/rekap-konten/public/js/tambah-kegiatan.js"></script>

<?php if (isset($_GET['status'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  <?php if ($_GET['status'] == 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Tambah Kegiatan Sukses!',
      text: 'Kegiatan berhasil ditambahkan ke jadwal.',
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
        title: 'Gagal Menyimpan Data!',
        text: 'Silakan coba lagi atau periksa data yang diinput.',
        showConfirmButton: true
      });
    <?php endif; ?>
  <?php endif; ?>
});
</script>
<?php endif; ?>
