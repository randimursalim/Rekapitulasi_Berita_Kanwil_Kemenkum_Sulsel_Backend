<?php
// app/views/pages/input-konten.php
?>
<div class="overview">
    <div class="title">
        <i class="fas fa-plus-circle"></i>
        <span class="text">Input Konten</span>
    </div>
</div>

<!-- Form Input Konten -->
<div class="activity-wrapper form-wrapper">
    <div class="activity form-activity">
        <div class="form-container">
            <form action="index.php?page=store-konten" method="POST" class="input-berita-form" autocomplete="off" enctype="multipart/form-data">

            <!-- Pilih Jenis Konten -->
            <div class="form-group">
                <label for="jenis">Jenis Konten</label>
                <select id="jenis" name="jenis" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="berita">Berita</option>
                    <option value="instagram">Instagram</option>
                    <option value="youtube">YouTube</option>
                    <option value="tiktok">TikTok</option>
                    <option value="twitter">Twitter (X)</option>
                    <option value="facebook">Facebook</option>
                </select>
            </div>

            <!-- Judul -->
            <div class="form-group">
                <label for="judul">Judul Konten</label>
                <input type="text" id="judul" name="judul" placeholder="Masukkan judul konten" required />
            </div>

            <!-- Form khusus Berita -->
            <div id="form-berita" style="display: none;">
                <div class="form-group">
                    <label for="tanggalBerita">Tanggal Berita</label>
                    <input type="date" id="tanggalBerita" name="tanggalBerita" />
                </div>

                <div class="form-group">
                    <label for="linkBerita">Link Berita</label>
                    <input type="url" id="linkBerita" name="linkBerita" placeholder="https://contoh.com/berita" />
                </div>

                <div class="form-group">
                    <label for="sumberBerita">Sumber / Media Berita</label>
                    <input type="text" id="sumberBerita" name="sumberBerita" placeholder="Masukkan nama media/sumber" />
                </div>

                <div class="form-group">
                    <label for="jenisBerita">Jenis Berita</label>
                    <select id="jenisBerita" name="jenisBerita" required>
                        <option value="">-- Pilih Jenis Berita --</option>
                        <option value="media_online">Media Online</option>
                        <option value="surat_kabar">Surat Kabar</option>
                        <option value="website_kanwil">Website Kanwil</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ringkasan">Ringkasan Berita</label>
                    <textarea id="ringkasan" name="ringkasan" rows="4" placeholder="Tuliskan ringkasan berita..."></textarea>
                </div>
            </div>

            <!-- Form khusus Media Sosial -->
            <div id="form-medsos" style="display: none;">
                <div class="form-group">
                    <label for="tanggalPost">Tanggal Posting</label>
                    <input type="date" id="tanggalPost" name="tanggalPost" />
                </div>

                <div class="form-group">
                    <label for="linkPost">Link Postingan</label>
                    <input type="url" id="linkPost" name="linkPost" placeholder="https://instagram.com/..." />
                </div>

                <div class="form-group">
                    <label for="caption">Deskripsi / Caption</label>
                    <textarea id="caption" name="caption" rows="4" placeholder="Tuliskan deskripsi atau caption..."></textarea>
                </div>
            </div>

            <!-- Dokumentasi -->
            <div class="form-group">
                <label for="dokumentasi">Dokumentasi (Opsional)</label>
                <input type="file" id="dokumentasi" name="dokumentasi" accept="image/*" />
            </div>

            <!-- Divisi / Bagian -->
            <div class="form-group">
                <label for="divisi">Divisi / Bagian</label>
                <select id="divisi" name="divisi" required>
                    <option value="">-- Pilih Divisi --</option>
                    <option value="ppu">Peraturan Perundang-undangan dan Pembinaan Hukum</option>
                    <option value="pelayanan">Pelayanan Hukum</option>
                    <option value="umum">Umum</option>
                </select>
            </div>

            <!-- Tombol -->
            <div class="form-actions" style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn-simpan">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=dashboard'">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
            </form>
        </div>
    </div>
</div>
<script src="/rekap-konten/public/js/input-konten.js"></script>

<?php if (isset($_GET['status'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  <?php if ($_GET['status'] == 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Input Konten Sukses!',
      showConfirmButton: false,
      timer: 2000
    });
  <?php elseif ($_GET['status'] == 'error'): ?>
    Swal.fire({
      icon: 'error',
      title: 'Gagal Menyimpan Data!',
      text: 'Silakan coba lagi.',
      showConfirmButton: true
    });
  <?php endif; ?>
});
</script>
<?php endif; ?>
