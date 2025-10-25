// Tambah Pengguna Form Management
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formTambahPengguna');
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
        const confirmPassword = document.getElementById('confirm_password').value;
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
        
        if (!password) {
            Swal.fire('Error!', 'Password harus diisi', 'error');
            return;
        }
        
        if (password.length < 6) {
            Swal.fire('Error!', 'Password minimal 6 karakter', 'error');
            return;
        }
        
        if (password !== confirmPassword) {
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
            text: 'Apakah Anda yakin ingin menambahkan pengguna baru?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, tambahkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX submission
                const formData = new FormData(form);
                
                fetch('index.php?page=store-pengguna', {
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
                        text: 'Terjadi kesalahan saat menambahkan pengguna',
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
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
