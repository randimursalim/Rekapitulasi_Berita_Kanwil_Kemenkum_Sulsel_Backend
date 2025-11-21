<?php
// app/views/pages/edit-layanan-pengaduan.php
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
        <span class="text">Edit Layanan Pengaduan</span>
    </div>
</div>

<!-- Form Edit Layanan Pengaduan -->
<div class="activity-wrapper form-wrapper">
    <div class="activity form-activity">
        <div class="form-container">
            <form id="formLayananPengaduan" class="input-berita-form" action="index.php?page=update-layanan-pengaduan" method="POST" autocomplete="off">
                <input type="hidden" name="id" value="<?= htmlspecialchars($layananPengaduan['id']) ?>">
                
                <h3 style="margin-bottom: 20px; color: var(--text-color);">Data Pelapor</h3>
                
                <div class="form-group">
                    <label for="noRegisterPengaduan">No. Register Pengaduan <span style="color: red;">*</span></label>
                    <input type="text" id="noRegisterPengaduan" name="noRegisterPengaduan" 
                           value="<?= htmlspecialchars($layananPengaduan['no_register_pengaduan']) ?>" 
                           placeholder="Masukkan nomor register pengaduan" required>
                </div>

                <div class="form-group">
                    <label for="nama">Nama <span style="color: red;">*</span></label>
                    <input type="text" id="nama" name="nama" 
                           value="<?= htmlspecialchars($layananPengaduan['nama']) ?>" 
                           placeholder="Masukkan nama pelapor" required>
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat <span style="color: red;">*</span></label>
                    <textarea id="alamat" name="alamat" rows="3" 
                              placeholder="Masukkan alamat pelapor" required><?= htmlspecialchars($layananPengaduan['alamat']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="jenisTandaPengenal">Jenis Tanda Pengenal <span style="color: red;">*</span></label>
                    <select id="jenisTandaPengenal" name="jenisTandaPengenal" required>
                        <option value="">-- Pilih Jenis Tanda Pengenal --</option>
                        <option value="KTP" <?= $layananPengaduan['jenis_tanda_pengenal'] === 'KTP' ? 'selected' : '' ?>>KTP</option>
                        <option value="SIM" <?= $layananPengaduan['jenis_tanda_pengenal'] === 'SIM' ? 'selected' : '' ?>>SIM</option>
                        <option value="PASPOR" <?= $layananPengaduan['jenis_tanda_pengenal'] === 'PASPOR' ? 'selected' : '' ?>>PASPOR</option>
                        <option value="LAINNYA" <?= $layananPengaduan['jenis_tanda_pengenal'] === 'LAINNYA' ? 'selected' : '' ?>>LAINNYA</option>
                    </select>
                </div>

                <div class="form-group" id="jenisTandaPengenalLainnyaGroup" style="display: <?= $layananPengaduan['jenis_tanda_pengenal'] === 'LAINNYA' ? 'block' : 'none' ?>;">
                    <label for="jenisTandaPengenalLainnya">Jenis Tanda Pengenal Lainnya <span style="color: red;">*</span></label>
                    <input type="text" id="jenisTandaPengenalLainnya" name="jenisTandaPengenalLainnya" 
                           value="<?= htmlspecialchars($layananPengaduan['jenis_tanda_pengenal_lainnya'] ?? '') ?>" 
                           placeholder="Masukkan jenis tanda pengenal lainnya" <?= $layananPengaduan['jenis_tanda_pengenal'] === 'LAINNYA' ? 'required' : '' ?>>
                </div>

                <div class="form-group">
                    <label for="noTandaPengenal">No. Tanda Pengenal <span style="color: red;">*</span></label>
                    <input type="text" id="noTandaPengenal" name="noTandaPengenal" 
                           value="<?= htmlspecialchars($layananPengaduan['no_tanda_pengenal']) ?>" 
                           placeholder="Masukkan nomor tanda pengenal" required>
                </div>

                <div class="form-group">
                    <label for="noTelp">No. Telepon</label>
                    <input type="text" id="noTelp" name="noTelp" 
                           value="<?= htmlspecialchars($layananPengaduan['no_telp'] ?? '') ?>" 
                           placeholder="Masukkan nomor telepon (opsional)">
                </div>

                <h3 style="margin: 30px 0 20px 0; color: var(--text-color);">Data Laporan</h3>

                <div class="form-group">
                    <label for="judulLaporan">Judul Laporan <span style="color: red;">*</span></label>
                    <input type="text" id="judulLaporan" name="judulLaporan" 
                           value="<?= htmlspecialchars($layananPengaduan['judul_laporan']) ?>" 
                           placeholder="Masukkan judul laporan" required>
                </div>

                <div class="form-group">
                    <label for="isiLaporan">Isi Laporan <span style="color: red;">*</span></label>
                    <textarea id="isiLaporan" name="isiLaporan" rows="5" 
                              placeholder="Masukkan isi laporan" required><?= htmlspecialchars($layananPengaduan['isi_laporan']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="tanggalKejadian">Tanggal Kejadian <span style="color: red;">*</span></label>
                    <input type="date" id="tanggalKejadian" name="tanggalKejadian" 
                           value="<?= htmlspecialchars($layananPengaduan['tanggal_kejadian']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="lokasiKejadian">Lokasi Kejadian <span style="color: red;">*</span></label>
                    <input type="text" id="lokasiKejadian" name="lokasiKejadian" 
                           value="<?= htmlspecialchars($layananPengaduan['lokasi_kejadian']) ?>" 
                           placeholder="Masukkan lokasi kejadian" required>
                </div>

                <div class="form-group">
                    <label for="kategoriLaporan">Kategori Laporan <span style="color: red;">*</span></label>
                    <select id="kategoriLaporan" name="kategoriLaporan" required>
                        <option value="">-- Pilih Kategori Laporan --</option>
                        <option value="AHU" <?= $layananPengaduan['kategori_laporan'] === 'AHU' ? 'selected' : '' ?>>AHU</option>
                        <option value="KI" <?= $layananPengaduan['kategori_laporan'] === 'KI' ? 'selected' : '' ?>>KI</option>
                        <option value="Umum" <?= $layananPengaduan['kategori_laporan'] === 'Umum' ? 'selected' : '' ?>>Umum</option>
                        <option value="Pelayanan Hukum (Peraturan Perundang-undangan dan Pembinaan Hukum / P3H)" <?= $layananPengaduan['kategori_laporan'] === 'Pelayanan Hukum (Peraturan Perundang-undangan dan Pembinaan Hukum / P3H)' ? 'selected' : '' ?>>Pelayanan Hukum (Peraturan Perundang-undangan dan Pembinaan Hukum / P3H)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="jenisAduan">Jenis Aduan <span style="color: red;">*</span></label>
                    <select id="jenisAduan" name="jenisAduan" required>
                        <option value="">-- Pilih Jenis Aduan --</option>
                        <option value="Suap" <?= $layananPengaduan['jenis_aduan'] === 'Suap' ? 'selected' : '' ?>>Suap</option>
                        <option value="Gratifikasi" <?= $layananPengaduan['jenis_aduan'] === 'Gratifikasi' ? 'selected' : '' ?>>Gratifikasi</option>
                        <option value="Pungli" <?= $layananPengaduan['jenis_aduan'] === 'Pungli' ? 'selected' : '' ?>>Pungli</option>
                        <option value="Korupsi" <?= $layananPengaduan['jenis_aduan'] === 'Korupsi' ? 'selected' : '' ?>>Korupsi</option>
                        <option value="Mal Administrasi" <?= $layananPengaduan['jenis_aduan'] === 'Mal Administrasi' ? 'selected' : '' ?>>Mal Administrasi</option>
                        <option value="Lainnya" <?= $layananPengaduan['jenis_aduan'] === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>

                <div class="form-group" id="jenisAduanLainnyaGroup" style="display: <?= $layananPengaduan['jenis_aduan'] === 'Lainnya' ? 'block' : 'none' ?>;">
                    <label for="jenisAduanLainnya">Jenis Aduan Lainnya <span style="color: red;">*</span></label>
                    <input type="text" id="jenisAduanLainnya" name="jenisAduanLainnya" 
                           value="<?= htmlspecialchars($layananPengaduan['jenis_aduan_lainnya'] ?? '') ?>" 
                           placeholder="Masukkan jenis aduan lainnya" <?= $layananPengaduan['jenis_aduan'] === 'Lainnya' ? 'required' : '' ?>>
                </div>

                <!-- Tombol Aksi -->
                <div class="form-actions" style="text-align:center; margin-top:20px;">
                    <button type="submit" class="btn-simpan">
                        <i class="fas fa-save"></i> Update
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
<script src="<?= $BASE ?>/js/edit-layanan-pengaduan.js"></script>

<?php if (isset($_GET['status'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  <?php if ($_GET['status'] == 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Update Layanan Pengaduan Sukses!',
      text: 'Data layanan pengaduan berhasil diperbarui.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      window.location.href = 'index.php?page=layanan-pengaduan';
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

