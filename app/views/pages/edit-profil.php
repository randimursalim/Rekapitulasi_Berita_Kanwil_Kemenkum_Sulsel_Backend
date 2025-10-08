<?php
// app/views/pages/edit-profil.php
?>

<div class="overview">
    <div class="title">
        <i class="uil uil-user-circle"></i>
        <span class="text">Edit Profil</span>
    </div>

    <!-- Form Edit Profil -->
    <div class="form-container">
        <form action="#" method="POST" class="input-berita-form" autocomplete="off" enctype="multipart/form-data">
            
            <!-- Upload Foto -->
            <div class="upload-container">
                <img id="previewImage" src="<?= $BASE ?>/images/user.jpg" alt="Preview Foto">
                <br>
                <label for="foto"><i class="uil uil-image-plus"></i> Ganti Foto</label>
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
                <label for="password">Password Baru</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password baru">
            </div>

            <div class="form-group">
                <label for="konfirmasi">Konfirmasi Password</label>
                <input type="password" id="konfirmasi" name="konfirmasi" placeholder="Ulangi password baru">
            </div>

            <div style="text-align:center; margin-top:20px;">
                <button type="submit" class="btn-simpan"><i class="uil uil-save"></i> Simpan</button>
                <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=dashboard'"><i class="uil uil-times"></i> Batal</button>
            </div>
        </form>
    </div>
    <!-- End Form -->
</div>

<!-- Script preview foto -->
<script>
const fotoInput = document.getElementById('foto');
const previewImage = document.getElementById('previewImage');

if (fotoInput) {
    fotoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
            }
            reader.readAsDataURL(file);
        } else {
            previewImage.src = '<?= $BASE ?>/images/user.jpg';
        }
    });
}
</script>
