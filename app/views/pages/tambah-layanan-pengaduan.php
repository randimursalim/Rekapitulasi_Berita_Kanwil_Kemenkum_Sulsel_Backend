<?php
// app/views/pages/tambah-layanan-pengaduan.php
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
        <span class="text">Tambah Layanan Pengaduan</span>
    </div>
</div>

<!-- Form Tambah Layanan Pengaduan -->
<div class="activity-wrapper form-wrapper">
    <div class="activity form-activity">
        <div class="form-container">
            <form id="formLayananPengaduan" class="input-berita-form" action="index.php?page=store-layanan-pengaduan" method="POST" autocomplete="off">
                <h3 style="margin-bottom: 20px; color: var(--text-color);">Data Pelapor</h3>
                
                <div class="form-group">
                    <label for="noRegisterPengaduan">No. Register Pengaduan <span style="color: red;">*</span></label>
                    <input type="text" id="noRegisterPengaduan" name="noRegisterPengaduan" placeholder="Masukkan nomor register pengaduan" required>
                </div>

                <div class="form-group">
                    <label for="nama">Nama <span style="color: red;">*</span></label>
                    <input type="text" id="nama" name="nama" placeholder="Masukkan nama pelapor" required>
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat <span style="color: red;">*</span></label>
                    <textarea id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat pelapor" required></textarea>
                </div>

                <div class="form-group">
                    <label for="jenisTandaPengenal">Jenis Tanda Pengenal <span style="color: red;">*</span></label>
                    <select id="jenisTandaPengenal" name="jenisTandaPengenal" required>
                        <option value="">-- Pilih Jenis Tanda Pengenal --</option>
                        <option value="KTP">KTP</option>
                        <option value="SIM">SIM</option>
                        <option value="PASPOR">PASPOR</option>
                        <option value="LAINNYA">LAINNYA</option>
                    </select>
                </div>

                <div class="form-group" id="jenisTandaPengenalLainnyaGroup" style="display: none;">
                    <label for="jenisTandaPengenalLainnya">Jenis Tanda Pengenal Lainnya <span style="color: red;">*</span></label>
                    <input type="text" id="jenisTandaPengenalLainnya" name="jenisTandaPengenalLainnya" placeholder="Masukkan jenis tanda pengenal lainnya">
                </div>

                <div class="form-group">
                    <label for="noTandaPengenal">No. Tanda Pengenal <span style="color: red;">*</span></label>
                    <input type="text" id="noTandaPengenal" name="noTandaPengenal" placeholder="Masukkan nomor tanda pengenal" required>
                </div>

                <div class="form-group">
                    <label for="noTelp">No. Telepon</label>
                    <input type="text" id="noTelp" name="noTelp" placeholder="Masukkan nomor telepon (opsional)">
                </div>

                <h3 style="margin: 30px 0 20px 0; color: var(--text-color);">Data Laporan</h3>

                <div class="form-group">
                    <label for="judulLaporan">Judul Laporan <span style="color: red;">*</span></label>
                    <input type="text" id="judulLaporan" name="judulLaporan" placeholder="Masukkan judul laporan" required>
                </div>

                <div class="form-group">
                    <label for="isiLaporan">Isi Laporan <span style="color: red;">*</span></label>
                    <textarea id="isiLaporan" name="isiLaporan" rows="5" placeholder="Masukkan isi laporan" required></textarea>
                </div>

                <div class="form-group">
                    <label for="tanggalKejadian">Tanggal Kejadian <span style="color: red;">*</span></label>
                    <input type="date" id="tanggalKejadian" name="tanggalKejadian" required>
                </div>

                <div class="form-group">
                    <label for="lokasiKejadian">Lokasi Kejadian <span style="color: red;">*</span></label>
                    <input type="text" id="lokasiKejadian" name="lokasiKejadian" placeholder="Masukkan lokasi kejadian" required>
                </div>

                <div class="form-group">
                    <label for="kategoriLaporan">Kategori Laporan <span style="color: red;">*</span></label>
                    <select id="kategoriLaporan" name="kategoriLaporan" required>
                        <option value="">-- Pilih Kategori Laporan --</option>
                        <option value="AHU">AHU</option>
                        <option value="KI">KI</option>
                        <option value="Umum">Umum</option>
                        <option value="Pelayanan Hukum (Peraturan Perundang-undangan dan Pembinaan Hukum / P3H)">Pelayanan Hukum (Peraturan Perundang-undangan dan Pembinaan Hukum / P3H)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="jenisAduan">Jenis Aduan <span style="color: red;">*</span></label>
                    <select id="jenisAduan" name="jenisAduan" required>
                        <option value="">-- Pilih Jenis Aduan --</option>
                        <option value="Suap">Suap</option>
                        <option value="Gratifikasi">Gratifikasi</option>
                        <option value="Pungli">Pungli</option>
                        <option value="Korupsi">Korupsi</option>
                        <option value="Mal Administrasi">Mal Administrasi</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group" id="jenisAduanLainnyaGroup" style="display: none;">
                    <label for="jenisAduanLainnya">Jenis Aduan Lainnya <span style="color: red;">*</span></label>
                    <input type="text" id="jenisAduanLainnya" name="jenisAduanLainnya" placeholder="Masukkan jenis aduan lainnya">
                </div>

                <!-- Tombol Aksi -->
                <div class="form-actions" style="text-align:center; margin-top:20px;">
                    <button type="submit" class="btn-simpan">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=layanan-pengaduan'">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= $BASE ?>/js/tambah-layanan-pengaduan.js"></script>

<?php if (isset($_GET['status'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  <?php if ($_GET['status'] == 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Tambah Layanan Pengaduan Sukses!',
      text: 'Layanan pengaduan berhasil ditambahkan.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      window.location.href = 'index.php?page=layanan-pengaduan';
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

