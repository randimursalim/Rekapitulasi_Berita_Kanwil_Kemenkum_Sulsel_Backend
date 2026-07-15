<?php
// app/views/pages/edit-tamu.php
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
        <i class="fas fa-user-edit"></i>
        <span class="text">Edit Tamu</span>
    </div>

    <!-- Form Edit Tamu -->
    <div class="form-container">
        <form id="formEditTamu" action="index.php?page=update-tamu" method="POST" class="input-berita-form"
            autocomplete="off" enctype="multipart/form-data">
            
            <input type="hidden" name="id" value="<?= htmlspecialchars($tamu['id']) ?>">

            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Nama Lengkap"
                    value="<?= htmlspecialchars($tamu['nama'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="telp">Telepon/WA</label>
                <input type="text" id="telp" name="telp" placeholder="08123"
                    value="<?= htmlspecialchars($tamu['telp'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" placeholder="abc@gmail.com"
                    value="<?= htmlspecialchars($tamu['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat</label>
                <input type="text" id="alamat" name="alamat" placeholder="Alamat"
                    value="<?= htmlspecialchars($tamu['alamat'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Pilih Layanan</label>
                <select name="layanan" id="edit_layanan" required>
                    <option value="">--Pilih Layanan--</option>
                    <option value="adm" <?= ($tamu['layanan'] === 'adm') ? 'selected' : '' ?>>ADMINISTRASI</option>
                    <option value="ahu" <?= ($tamu['layanan'] === 'ahu') ? 'selected' : '' ?>>AHU</option>
                    <option value="ki" <?= ($tamu['layanan'] === 'ki') ? 'selected' : '' ?>>KI</option>
                    <option value="p3h" <?= ($tamu['layanan'] === 'p3h') ? 'selected' : '' ?>>P3H</option>
                    <option value="priority" <?= ($tamu['layanan'] === 'priority') ? 'selected' : '' ?>>PRIORITY</option>
                </select>
            </div>

            <div class="form-group">
                <label>Pilih Item Layanan</label>
                <!-- We pass current selected value in data attribute for JS to prepopulate it -->
                <select name="layanan_item" id="layanan_item" data-selected="<?= htmlspecialchars($tamu['layanan_item'] ?? '') ?>" required disabled>
                    <option value="">-- Pilih Item Layanan --</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tujuan">Maksud/Tujuan Bertamu</label>
                <input type="text" id="tujuan" name="tujuan" placeholder="Maksud/Tujuan Bertamu"
                    value="<?= htmlspecialchars($tamu['tujuan'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <div class="switch-wrapper">
                    <div class="switch-text">
                        <label class="switch-label">Ambil Antrean</label>
                        <small>Aktifkan jika ingin mengambil nomor antrean</small>
                    </div>

                    <label class="switch">
                        <input type="checkbox" name="entrain" value="yes" <?= ($tamu['entrain'] === 'yes') ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Foto (Kamera)</label>
                
                <?php if (!empty($tamu['foto'])): ?>
                    <div style="margin-bottom: 10px;">
                        <p style="font-size: 0.9em; margin-bottom: 5px; color: var(--text-color);">Foto saat ini:</p>
                        <img src="<?= $BASE ?>/storage/uploads/foto/<?= htmlspecialchars($tamu['foto']) ?>" style="max-width:200px; border-radius: 8px; border: 1px solid var(--border-color);">
                    </div>
                <?php endif; ?>

                <p style="font-size: 0.9em; margin-bottom: 5px; color: var(--text-color);">Ambil foto baru (opsional):</p>
                <!-- Video webcam -->
                <div class="camera-wrapper">
                    <video id="video" width="400" height="300" autoplay muted playsinline></video>
                    <canvas id="canvas" width="400" height="300" style="display:none;"></canvas>
                </div>

                <!-- Preview foto -->
                <img id="previewFoto" style="margin-top:10px; max-width:200px; display:none;">

                <!-- Hidden input base64 -->
                <input type="hidden" name="foto" id="foto">

                <button type="button" id="captureFoto" class="btn-clear" style="margin-top:10px;">
                    Ambil Foto Baru
                </button>
            </div>

            <div class="form-group">
                <label for="signature">Tanda Tangan</label>

                <?php if (!empty($tamu['ttd'])): ?>
                    <div style="margin-bottom: 10px;">
                        <p style="font-size: 0.9em; margin-bottom: 5px; color: var(--text-color);">Tanda tangan saat ini:</p>
                        <img src="<?= $BASE ?>/storage/uploads/ttd/<?= htmlspecialchars($tamu['ttd']) ?>" style="max-height:100px; background-color:#fff; border-radius: 8px; border: 1px solid var(--border-color); padding: 5px;">
                    </div>
                <?php endif; ?>

                <p style="font-size: 0.9em; margin-bottom: 5px; color: var(--text-color);">Buat tanda tangan baru (opsional):</p>
                <!-- Canvas untuk tanda tangan -->
                <div class="signature-wrapper">
                    <canvas id="signature-pad" width="400" height="200"></canvas>
                </div>

                <!-- Hidden input untuk simpan base64 tanda tangan -->
                <input type="hidden" name="ttd" id="ttd">

                <!-- Tombol clear -->
                <button type="button" id="clear-signature" class="btn-clear" style="margin-top:10px;">
                    Clear
                </button>
            </div>

            <div style="text-align:center; margin-top:20px;">
                <button type="submit" class="btn-simpan">
                    <i class="fas fa-save"></i> Perbarui
                </button>
                <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=tamu'">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </form>
    </div>
    <!-- End Form -->
</div>

<!-- Script preview foto dan validasi -->
<script src="<?= $BASE ?>/js/edit-tamu.js?v=1.0.1"></script>
