<?php
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
        <span class="text">Edit Konten</span>
    </div>
</div>

<!-- Form Edit Konten -->
<div class="activity-wrapper form-wrapper">
    <div class="activity form-activity">
        <div class="form-container">
        <!-- FORM EDIT KONTEN -->
        <form id="editKontenForm" action="index.php?page=update-konten" method="POST" class="input-berita-form" autocomplete="off" enctype="multipart/form-data">
            <!-- Hidden field untuk ID -->
            <input type="hidden" name="id_konten" value="<?= htmlspecialchars($konten['id_konten'] ?? '') ?>">
            
            <!-- Jenis Konten -->
            <div class="form-group">
                <label for="jenis">Jenis Konten</label>
                <select id="jenis" name="jenis" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="berita" <?= ($konten['jenis'] ?? '') === 'berita' ? 'selected' : '' ?>>Berita</option>
                    <option value="instagram" <?= ($konten['jenis'] ?? '') === 'instagram' ? 'selected' : '' ?>>Instagram</option>
                    <option value="youtube" <?= ($konten['jenis'] ?? '') === 'youtube' ? 'selected' : '' ?>>YouTube</option>
                    <option value="tiktok" <?= ($konten['jenis'] ?? '') === 'tiktok' ? 'selected' : '' ?>>TikTok</option>
                    <option value="twitter" <?= ($konten['jenis'] ?? '') === 'twitter' ? 'selected' : '' ?>>Twitter (X)</option>
                    <option value="facebook" <?= ($konten['jenis'] ?? '') === 'facebook' ? 'selected' : '' ?>>Facebook</option>
                </select>
            </div>

            <!-- Judul -->
            <div class="form-group">
                <label for="judul">Judul Konten</label>
                <input type="text" id="judul" name="judul" value="<?= htmlspecialchars($konten['judul'] ?? '') ?>" required>
            </div>

            <!-- Form Berita -->
            <div id="form-berita" style="display:<?= ($konten['jenis'] ?? '') === 'berita' ? 'block' : 'none' ?>;">
                <div class="form-group">
                    <label for="tanggalBerita">Tanggal Berita</label>
                    <input type="date" id="tanggalBerita" name="tanggalBerita" value="<?= htmlspecialchars($konten['tanggal_berita'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="linkBerita">Link Berita</label>
                    <input type="url" id="linkBerita" name="linkBerita" value="<?= htmlspecialchars($konten['link_berita'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="sumberBerita">Sumber / Media Berita</label>
                    <input type="text" id="sumberBerita" name="sumberBerita" value="<?= htmlspecialchars($konten['sumber_berita'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="jenisBerita">Jenis Berita</label>
                    <select id="jenisBerita" name="jenisBerita">
                        <option value="">-- Pilih Jenis Berita --</option>
                        <option value="media_online" <?= ($konten['jenis_berita'] ?? '') === 'media_online' ? 'selected' : '' ?>>Media Online</option>
                        <option value="surat_kabar" <?= ($konten['jenis_berita'] ?? '') === 'surat_kabar' ? 'selected' : '' ?>>Surat Kabar</option>
                        <option value="website_kanwil" <?= ($konten['jenis_berita'] ?? '') === 'website_kanwil' ? 'selected' : '' ?>>Website Kanwil</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ringkasan">Ringkasan Berita</label>
                    <textarea id="ringkasan" name="ringkasan" rows="4"><?= htmlspecialchars($konten['ringkasan'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Form Media Sosial -->
            <div id="form-medsos" style="display:<?= in_array($konten['jenis'] ?? '', ['instagram','youtube','tiktok','twitter','facebook']) ? 'block' : 'none' ?>;">
                <div class="form-group">
                    <label for="tanggalPost">Tanggal Posting</label>
                    <input type="date" id="tanggalPost" name="tanggalPost" value="<?= htmlspecialchars($konten['tanggal_post'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="linkPost">Link Postingan</label>
                    <input type="url" id="linkPost" name="linkPost" value="<?= htmlspecialchars($konten['link_post'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="caption">Deskripsi / Caption</label>
                    <textarea id="caption" name="caption" rows="4"><?= htmlspecialchars($konten['caption'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Dokumentasi -->
            <div class="form-group">
                <label for="dokumentasi">Dokumentasi (Opsional)</label>
                <input type="file" id="dokumentasi" name="dokumentasi" accept="image/*">
                <?php 
                // Debug: cek apakah dokumentasi ada
                $hasDokumentasi = !empty($konten['dokumentasi']);
                if ($hasDokumentasi): 
                    // Generate full URL untuk gambar
                    $dokumentasiPath = $konten['dokumentasi'];
                    $dokumentasiUrl = $dokumentasiPath;
                    
                    // Jika bukan full URL, proses path
                    if (!preg_match('/^https?:\/\//', $dokumentasiUrl)) {
                        // Jika absolute path (dimulai dengan /) - kompatibel PHP 7.4
                        if (substr($dokumentasiUrl, 0, 1) === '/') {
                            // Di hosting, absolute path biasanya tidak perlu BASE
                            // Tapi kita tetap coba dengan BASE jika diperlukan
                            if (!empty($BASE)) {
                                $dokumentasiUrl = $BASE . $dokumentasiUrl;
                            }
                        } else {
                            // Relative path, tambahkan BASE
                            $dokumentasiUrl = $BASE . '/' . ltrim($dokumentasiPath, '/');
                        }
                    }
                ?>
                    <div style="margin-top: 10px;">
                        <p style="margin-bottom: 5px; font-size: 12px; color: #666;">
                            Dokumentasi saat ini:
                        </p>
                        <img src="<?= htmlspecialchars($dokumentasiUrl) ?>" 
                             alt="Preview Dokumentasi" 
                             data-original-path="<?= htmlspecialchars($dokumentasiPath) ?>"
                             style="max-width: 300px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px; padding: 5px; background: #f9f9f9; display: block;"
                             onerror="if(typeof getImageUrl !== 'undefined' && this.dataset.originalPath) { var newSrc = getImageUrl(this.dataset.originalPath); if(newSrc) { this.src = newSrc; } else { this.style.display='none'; } } else { this.style.display='none'; }">
                        <br>
                        <a href="<?= htmlspecialchars($dokumentasiUrl) ?>" target="_blank" style="font-size: 12px; color: #0E4BF1; text-decoration: none; margin-top: 5px; display: inline-block;">
                            <i class="fas fa-external-link-alt"></i> Buka di tab baru
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Divisi -->
            <div class="form-group">
                <label for="divisi">Divisi / Bagian</label>
                <select id="divisi" name="divisi">
                    <option value="">-- Pilih Divisi --</option>
                    <option value="ppu" <?= ($konten['divisi'] ?? '') === 'ppu' ? 'selected' : '' ?>>Peraturan Perundang-undangan dan Pembinaan Hukum</option>
                    <option value="pelayanan" <?= ($konten['divisi'] ?? '') === 'pelayanan' ? 'selected' : '' ?>>Pelayanan Hukum</option>
                    <option value="umum" <?= ($konten['divisi'] ?? '') === 'umum' ? 'selected' : '' ?>>Umum</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="form-actions" style="text-align: center; margin-top: 30px; margin-bottom: 20px; padding: 20px 0; clear: both; display: block;">
                <button type="submit" class="btn-simpan" style="display: inline-block; margin: 0 10px;">
                    <i class="fas fa-save"></i> Update
                </button>
                <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=arsip'" style="display: inline-block; margin: 0 10px;">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </form>
        </div>
    </div>
</div>

<!-- Inline script untuk toggle form saat user mengubah pilihan -->
<script>
(function() {
    // Cegah eksekusi ganda
    if (window.editKontenFormInitialized) return;
    
    function initEditKontenForm() {
        // Set flag untuk mencegah eksekusi ganda
        if (window.editKontenFormInitialized) return;
        window.editKontenFormInitialized = true;
        
        const jenisSelect = document.getElementById('jenis');
        const formBerita = document.getElementById('form-berita');
        const formMedsos = document.getElementById('form-medsos');
        const editForm = document.getElementById('editKontenForm');
        const formActions = document.querySelector('.form-actions');
        const dokumentasiImg = document.querySelector('#dokumentasi + div img');

        if (!jenisSelect) {
            // Retry jika elemen belum tersedia
            setTimeout(initEditKontenForm, 100);
            return;
        }

        // Pastikan tombol terlihat
        if (formActions) {
            formActions.style.display = 'block';
            formActions.style.visibility = 'visible';
            formActions.style.opacity = '1';
        }

        // Perbaiki path gambar dokumentasi jika gagal dimuat
        if (dokumentasiImg && typeof getImageUrl !== 'undefined') {
            const originalPath = dokumentasiImg.dataset.originalPath;
            if (originalPath) {
                dokumentasiImg.onerror = function() {
                    const newSrc = getImageUrl(originalPath);
                    if (newSrc && newSrc !== this.src) {
                        this.src = newSrc;
                    } else {
                        console.error('Gagal memuat gambar dokumentasi:', originalPath);
                    }
                };
            }
        }

        // Fungsi untuk menampilkan form sesuai jenis
        function toggleForm() {
            const value = jenisSelect.value;
            if (value === 'berita') {
                if (formBerita) formBerita.style.display = 'block';
                if (formMedsos) formMedsos.style.display = 'none';
            } else if (['instagram','youtube','tiktok','twitter','facebook'].includes(value)) {
                if (formBerita) formBerita.style.display = 'none';
                if (formMedsos) formMedsos.style.display = 'block';
            } else {
                if (formBerita) formBerita.style.display = 'none';
                if (formMedsos) formMedsos.style.display = 'none';
            }
        }

        // Jalankan saat user mengganti pilihan (form sudah ditampilkan oleh PHP)
        jenisSelect.addEventListener('change', toggleForm);

        // Konfirmasi sebelum submit form
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Update Konten?',
                        text: "Apakah kamu yakin untuk mengupdate konten ini?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, update!',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if(result.isConfirmed){
                            // Submit form jika konfirmasi
                            this.submit();
                        }
                    });
                } else {
                    // Fallback jika SweetAlert tidak tersedia
                    if (confirm('Apakah kamu yakin untuk mengupdate konten ini?')) {
                        this.submit();
                    }
                }
            });
        }
    }

    // Jalankan saat DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditKontenForm);
    } else {
        // DOM sudah ready, jalankan langsung
        initEditKontenForm();
    }
    
    // Backup: jalankan setelah window load
    window.addEventListener('load', function() {
        setTimeout(initEditKontenForm, 100);
    });
})();
</script>
