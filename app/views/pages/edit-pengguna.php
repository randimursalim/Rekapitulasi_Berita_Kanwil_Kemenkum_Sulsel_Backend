<?php
// app/views/pages/edit-pengguna.php
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
        <span class="text">Edit Pengguna</span>
    </div>

    <!-- Form Edit Pengguna -->
    <div class="form-container">
        <form id="formEditPengguna" action="index.php?page=update-pengguna" method="POST" class="input-berita-form" autocomplete="off" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $pengguna['id_pengguna'] ?>">
            
            <?php
            $penggunaFoto = $pengguna['foto'] ?? 'user.jpg';
            $previewSrc = $BASE . '/Images/user.jpg';
            if (!empty($penggunaFoto) && $penggunaFoto !== 'user.jpg') {
                $publicDir = dirname(dirname(dirname(__DIR__))) . '/public';
                $storagePath = $publicDir . '/storage/uploads/users/' . $penggunaFoto;
                $imagesPath  = $publicDir . '/Images/users/' . $penggunaFoto;
                if (file_exists($storagePath)) {
                    $previewSrc = $BASE . '/storage/uploads/users/' . $penggunaFoto;
                } elseif (file_exists($imagesPath)) {
                    $previewSrc = $BASE . '/Images/users/' . $penggunaFoto;
                }
            }
            ?>
            <!-- Upload Foto -->
            <div class="upload-container">
                <img id="previewImage" src="<?= $previewSrc ?>" data-default-src="<?= $BASE ?>/Images/user.jpg" alt="Preview Foto">
                <br>
                <div style="display: flex; gap: 10px; justify-content: center; align-items: center; margin-top: 12px; flex-wrap: wrap;">
                    <label for="foto" style="margin: 0; cursor: pointer;"><i class="fas fa-image"></i> Ubah Foto</label>
                    <input type="file" id="foto" name="foto" accept="image/*">
                    <button type="button" id="btnResetFoto" class="btn-reset-foto" style="background-color: #dc2626; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-size: 0.88rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s;" title="Hapus foto & kembali ke foto default">
                        <i class="fas fa-undo"></i> Reset Foto Default
                    </button>
                </div>
                <input type="hidden" name="reset_foto" id="reset_foto" value="0">
            </div>

            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($pengguna['nama']) ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($pengguna['username']) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password Baru</label>
                <div class="password-input-container">
                    <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diganti">
                    <span class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="password-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm-password">Konfirmasi Password</label>
                <div class="password-input-container">
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Ulangi password baru">
                    <span class="password-toggle" onclick="togglePassword('confirm-password')">
                        <i class="fas fa-eye" id="confirm-password-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="Admin" <?= ($pengguna['role'] === 'Admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="Operator" <?= ($pengguna['role'] === 'Operator') ? 'selected' : '' ?>>Operator</option>
                    <option value="p3h" <?= ($pengguna['role'] === 'p3h') ? 'selected' : '' ?>>Peraturan Perundang-undangan dan Pembinaan Hukum</option>
                    <option value="pegawai" <?= ($pengguna['role'] === 'pegawai') ? 'selected' : '' ?>>Pegawai</option>
                </select>
            </div>

            <div style="text-align:center; margin-top:20px;">
                <button type="submit" class="btn-simpan">
                    <i class="fas fa-save"></i> Update
                </button>
                <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=pengguna'">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </form>
    </div>
    <!-- End Form -->
</div>

<!-- Script preview foto dan validasi -->
<script src="<?= $BASE ?>/js/edit-pengguna.js"></script>
