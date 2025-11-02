<?php
// app/views/pages/edit-profil.php
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
        <i class="fas fa-user-circle"></i>
        <span class="text">Edit Profil</span>
    </div>

    <!-- Form Edit Profil -->
    <div class="form-container">
        <form action="index.php?page=update-profil" method="POST" class="input-berita-form" autocomplete="off" enctype="multipart/form-data" id="formEditProfil">
            
            <!-- Upload Foto -->
            <div class="upload-container">
                <img id="previewImage" src="<?= $BASE ?>/Images/<?= !empty($_SESSION['user']['foto']) && $_SESSION['user']['foto'] !== 'user.jpg' ? 'users/' . $_SESSION['user']['foto'] : 'user.jpg' ?>" alt="Preview Foto">
                <br>
                <label for="foto"><i class="fas fa-image"></i> Ganti Foto</label>
                <input type="file" id="foto" name="foto" accept="image/*">
            </div>

            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($_SESSION['user']['nama'] ?? '') ?>" placeholder="Masukkan nama lengkap" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?>" placeholder="Masukkan username" required>
            </div>

            <div class="form-group">
                <label for="password">Password Baru</label>
                <div class="password-input-container">
                    <input type="password" id="password" name="password" placeholder="Masukkan password baru">
                    <span class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="password-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="konfirmasi">Konfirmasi Password</label>
                <div class="password-input-container">
                    <input type="password" id="konfirmasi" name="konfirmasi" placeholder="Ulangi password baru">
                    <span class="password-toggle" onclick="togglePassword('konfirmasi')">
                        <i class="fas fa-eye" id="konfirmasi-eye"></i>
                    </span>
                </div>
            </div>

            <div style="text-align:center; margin-top:20px;">
                <button type="submit" class="btn-simpan"><i class="fas fa-save"></i> Simpan</button>
                <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=dashboard'"><i class="fas fa-times"></i> Batal</button>
            </div>
        </form>
    </div>
    <!-- End Form -->
</div>

<!-- Script preview foto dan password toggle -->
<script src="<?= $BASE ?>/js/edit-profil.js"></script>
