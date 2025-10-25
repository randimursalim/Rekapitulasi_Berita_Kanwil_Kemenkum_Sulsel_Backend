<?php
// app/views/pages/tambah-pengguna.php
?>

<div class="overview">
    <div class="title">
        <i class="fas fa-user-plus"></i>
        <span class="text">Tambah Pengguna</span>
    </div>

    <!-- Form Tambah Pengguna -->
    <div class="form-container">
        <form id="formTambahPengguna" action="index.php?page=store-pengguna" method="POST" class="input-berita-form" autocomplete="off" enctype="multipart/form-data">

            <!-- Upload Foto -->
            <div class="upload-container">
                    <img id="previewImage" src="<?= $BASE ?>/Images/user.jpg" alt="Preview Foto">
                <br>
                <label for="foto"><i class="fas fa-image"></i> Upload Foto</label>
                <input type="file" id="foto" name="foto" accept="image/*">
            </div>

            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" 
                       value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-container">
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    <span class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="password-eye"></i>
                    </span>
                </div>
            </div>

            <!-- Konfirmasi Password -->
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <div class="password-input-container">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                    <span class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye" id="confirm_password-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="Admin" <?= (($_POST['role'] ?? '') === 'Admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="Operator" <?= (($_POST['role'] ?? '') === 'Operator') ? 'selected' : '' ?>>Operator</option>
                </select>
            </div>

            <div style="text-align:center; margin-top:20px;">
                <button type="submit" class="btn-simpan">
                    <i class="fas fa-save"></i> Simpan
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
<script src="/rekap-konten/public/js/tambah-pengguna.js"></script>
