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
            <div id="form-berita" style="display:none;">
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
            <div id="form-medsos" style="display:none;">
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
                <?php if (!empty($konten['dokumentasi'])): ?>
                    <p style="margin-top: 5px; font-size: 12px; color: #666;">
                        Dokumentasi saat ini: <a href="<?= htmlspecialchars($konten['dokumentasi']) ?>" target="_blank">Lihat</a>
                    </p>
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
            <div style="text-align:center; margin-top:20px;" class="form-buttons">
                <button type="submit" class="btn-simpan"><i class="fas fa-save"></i> Update</button>
                <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=arsip'"><i class="fas fa-times"></i> Batal</button>
            </div>
        </form>
        </div>
    </div>
</div>


<script src="<?= $BASE ?>/js/edit-konten.js"></script>
</body>
</html>
