<div class="overview">
    <div class="title">
        <i class="uil uil-edit"></i>
        <span class="text">Edit Konten</span>
    </div>

    <div class="form-container">
        <!-- FORM EDIT KONTEN -->
        <form id="editKontenForm" action="#" method="POST" class="input-berita-form" autocomplete="off" enctype="multipart/form-data">
            <!-- Jenis Konten -->
            <div class="form-group">
                <label for="jenis">Jenis Konten</label>
                <select id="jenis" name="jenis" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="berita" selected>Berita</option>
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
                <input type="text" id="judul" name="judul" value="Judul Lama" required>
            </div>

            <!-- Form Berita -->
            <div id="form-berita" style="display:none;">
                <div class="form-group">
                    <label for="tanggalBerita">Tanggal Berita</label>
                    <input type="date" id="tanggalBerita" name="tanggalBerita" value="2025-09-01">
                </div>
                <div class="form-group">
                    <label for="linkBerita">Link Berita</label>
                    <input type="url" id="linkBerita" name="linkBerita" value="https://contoh.com/berita">
                </div>
                <div class="form-group">
                    <label for="sumberBerita">Sumber / Media Berita</label>
                    <input type="text" id="sumberBerita" name="sumberBerita" value="Media Lama">
                </div>
                <div class="form-group">
                    <label for="jenisBerita">Jenis Berita</label>
                    <select id="jenisBerita" name="jenisBerita">
                        <option value="">-- Pilih Jenis Berita --</option>
                        <option value="media_online" selected>Media Online</option>
                        <option value="surat_kabar">Surat Kabar</option>
                        <option value="website_kanwil">Website Kanwil</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ringkasan">Ringkasan Berita</label>
                    <textarea id="ringkasan" name="ringkasan" rows="4">Ringkasan lama berita...</textarea>
                </div>
            </div>

            <!-- Form Media Sosial -->
            <div id="form-medsos" style="display:none;">
                <div class="form-group">
                    <label for="tanggalPost">Tanggal Posting</label>
                    <input type="date" id="tanggalPost" name="tanggalPost">
                </div>
                <div class="form-group">
                    <label for="linkPost">Link Postingan</label>
                    <input type="url" id="linkPost" name="linkPost">
                </div>
                <div class="form-group">
                    <label for="caption">Deskripsi / Caption</label>
                    <textarea id="caption" name="caption" rows="4"></textarea>
                </div>
            </div>

            <!-- Dokumentasi -->
            <div class="form-group">
                <label for="dokumentasi">Dokumentasi (Opsional)</label>
                <input type="file" id="dokumentasi" name="dokumentasi" accept="image/*">
            </div>

            <!-- Divisi -->
            <div class="form-group">
                <label for="divisi">Divisi / Bagian</label>
                <select id="divisi" name="divisi">
                    <option value="">-- Pilih Divisi --</option>
                    <option value="ppu">Peraturan Perundang-undangan dan Pembinaan Hukum</option>
                    <option value="pelayanan">Pelayanan Hukum</option>
                    <option value="umum">Umum</option>
                </select>
            </div>

            <!-- Buttons -->
            <div style="text-align:center; margin-top:20px;" class="form-buttons">
                <button type="submit" class="btn-simpan"><i class="uil uil-save"></i> Update</button>
                <button type="button" class="btn-batal" onclick="window.location.href='rekap-konten.php'"><i class="uil uil-times"></i> Batal</button>
            </div>
        </form>
    </div>
</div>


  <script>
    // Toggle form sesuai pilihan (konsisten dengan input-konten.html)
    document.addEventListener('DOMContentLoaded', function () {
    const jenisSelect = document.getElementById('jenis');
    const formBerita = document.getElementById('form-berita');
    const formMedsos = document.getElementById('form-medsos');

    if (!jenisSelect) return;

    // Fungsi untuk menampilkan form sesuai jenis
    function toggleForm() {
        const value = jenisSelect.value;
        if (value === 'berita') {
            formBerita.style.display = 'block';
            formMedsos.style.display = 'none';
        } else if (['instagram','youtube','tiktok','twitter','facebook'].includes(value)) {
            formBerita.style.display = 'none';
            formMedsos.style.display = 'block';
        } else {
            formBerita.style.display = 'none';
            formMedsos.style.display = 'none';
        }
    }

    // Jalankan saat halaman load (untuk edit, menampilkan sesuai data lama)
    toggleForm();

    // Jalankan saat user mengganti pilihan
    jenisSelect.addEventListener('change', toggleForm);
});

  </script>
</body>
</html>
