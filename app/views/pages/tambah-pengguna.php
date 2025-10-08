<?php
// app/views/pages/tambah-pengguna.php
?>

<div class="overview">
    <div class="title">
        <i class="uil uil-user-plus"></i>
        <span class="text">Tambah Pengguna</span>
    </div>

    <!-- Form Tambah Pengguna -->
    <div class="form-container">
        <form action="#" method="POST" class="input-berita-form" autocomplete="off" enctype="multipart/form-data">

            <!-- Upload Foto -->
            <div class="upload-container">
                <img id="previewImage" src="<?= $BASE ?>/images/user.jpg" alt="Preview Foto">
                <br>
                <label for="foto"><i class="uil uil-image-plus"></i> Upload Foto</label>
                <input type="file" id="foto" name="foto" accept="image/*">
            </div>

            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            </div>

            <!-- Konfirmasi Password -->
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="Admin">Admin</option>
                    <option value="Operator">User</option>
                </select>
            </div>

            <div style="text-align:center; margin-top:20px;">
                <button type="submit" class="btn-simpan">
                    <i class="uil uil-save"></i> Simpan
                </button>
                <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=pengguna'">
                    <i class="uil uil-times"></i> Batal
                </button>
            </div>
        </form>
    </div>
    <!-- End Form -->
</div>

<!-- Script preview foto -->
<script>
const fotoInput = document.getElementById('foto');
const preview = document.getElementById('previewImage');

if (fotoInput) {
    fotoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.setAttribute('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });
}
</script>
