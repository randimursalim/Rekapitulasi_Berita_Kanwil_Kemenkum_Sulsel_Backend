<?php
// app/views/pages/edit-pengguna.php
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
            
            <!-- Upload Foto -->
            <div class="upload-container">
                <img id="previewImage" src="<?= $BASE ?>/Images/<?= !empty($pengguna['foto']) && $pengguna['foto'] !== 'user.jpg' ? 'users/' . $pengguna['foto'] : 'user.jpg' ?>" alt="Preview Foto">
                <br>
                <label for="foto"><i class="fas fa-image"></i> Ubah Foto</label>
                <input type="file" id="foto" name="foto" accept="image/*">
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
<script src="/rekap-konten/public/js/edit-pengguna.js"></script>
