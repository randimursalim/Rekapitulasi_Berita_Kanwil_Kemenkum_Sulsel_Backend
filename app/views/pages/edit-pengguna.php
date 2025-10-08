<?php
// app/views/pages/edit-pengguna.php
?>

<div class="overview">
    <div class="title">
        <i class="uil uil-edit"></i>
        <span class="text">Edit Pengguna</span>
    </div>

    <!-- Form Edit Pengguna -->
    <div class="form-container">
        <form action="#" method="POST" class="input-berita-form" autocomplete="off" enctype="multipart/form-data">
            
            <!-- Upload Foto -->
            <div class="upload-container">
                <img id="previewImage" src="<?= $BASE ?>/images/user.jpg" alt="Preview Foto">
                <br>
                <label for="foto"><i class="uil uil-image-plus"></i> Ubah Foto</label>
                <input type="file" id="foto" name="foto" accept="image/*">
            </div>

            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="Admin" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="admin01" required>
            </div>

            <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diganti">
            </div>

            <div class="form-group">
                <label for="confirm-password">Konfirmasi Password</label>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Ulangi password baru">
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="Admin" selected>Admin</option>
                    <option value="Operator">User</option>
                </select>
            </div>

            <div style="text-align:center; margin-top:20px;">
                <button type="submit" class="btn-simpan">
                    <i class="uil uil-save"></i> Update
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
