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
        <form id="formEditPengguna" action="index.php?page=update-pengguna" method="POST" class="input-berita-form" autocomplete="off" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $pengguna['id_pengguna'] ?>">
            
            <!-- Upload Foto -->
            <div class="upload-container">
                <img id="previewImage" src="<?= $BASE ?>/Images/<?= !empty($pengguna['foto']) ? $pengguna['foto'] : 'user.jpg' ?>" alt="Preview Foto">
                <br>
                <label for="foto"><i class="uil uil-image-plus"></i> Ubah Foto</label>
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
                        <i class="uil uil-eye" id="password-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm-password">Konfirmasi Password</label>
                <div class="password-input-container">
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Ulangi password baru">
                    <span class="password-toggle" onclick="togglePassword('confirm-password')">
                        <i class="uil uil-eye" id="confirm-password-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="Admin" <?= ($pengguna['role'] === 'Admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="Operator" <?= ($pengguna['role'] === 'Operator') ? 'selected' : '' ?>>User</option>
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

<!-- Script preview foto dan validasi -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEditPengguna');
    const fotoInput = document.getElementById('foto');
    const preview = document.getElementById('previewImage');
    
    // Preview foto
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
    
    // Validasi form
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const nama = document.getElementById('nama').value.trim();
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        const role = document.getElementById('role').value;
        
        // Validasi client-side
        if (!nama) {
            Swal.fire('Error!', 'Nama harus diisi', 'error');
            return;
        }
        
        if (!username) {
            Swal.fire('Error!', 'Username harus diisi', 'error');
            return;
        }
        
        if (password && password.length < 6) {
            Swal.fire('Error!', 'Password minimal 6 karakter', 'error');
            return;
        }
        
        if (password && password !== confirmPassword) {
            Swal.fire('Error!', 'Password dan konfirmasi password tidak sama', 'error');
            return;
        }
        
        if (!role) {
            Swal.fire('Error!', 'Role harus dipilih', 'error');
            return;
        }
        
        // Submit form
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin mengupdate data pengguna?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, update!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX submission
                const formData = new FormData(form);
                
                fetch('index.php?page=update-pengguna', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = 'index.php?page=pengguna';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message,
                            showConfirmButton: true
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat mengupdate pengguna',
                        showConfirmButton: true
                    });
                });
            }
        });
    });
});

// Function untuk toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const eyeIcon = document.getElementById(inputId + '-eye');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.classList.remove('uil-eye');
        eyeIcon.classList.add('uil-eye-slash');
    } else {
        input.type = 'password';
        eyeIcon.classList.remove('uil-eye-slash');
        eyeIcon.classList.add('uil-eye');
    }
}
</script>
