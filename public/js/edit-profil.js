// Edit Profil Form Management
document.addEventListener('DOMContentLoaded', function() {
    const fotoInput = document.getElementById('foto');
    const previewImage = document.getElementById('previewImage');
    const form = document.getElementById('formEditProfil');

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
                // Reset ke foto default jika tidak ada file
                previewImage.src = previewImage.getAttribute('data-default-src') || previewImage.src;
            }
        });
    }

    // Handle form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nama = document.getElementById('nama').value.trim();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const konfirmasi = document.getElementById('konfirmasi').value;
            
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
            
            if (password && password !== konfirmasi) {
                Swal.fire('Error!', 'Password dan konfirmasi password tidak sama', 'error');
                return;
            }
            
            // Konfirmasi sebelum submit
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin ingin memperbarui profil?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, perbarui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // AJAX submission
                    const formData = new FormData(form);
                    
                    fetch('index.php?page=update-profil', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        // Cek apakah response adalah JSON
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            return response.json();
                        } else {
                            // Jika bukan JSON, ambil text response
                            return response.text().then(text => {
                                throw new Error('Response bukan JSON: ' + text);
                            });
                        }
                    })
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = 'index.php?page=dashboard';
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
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat memperbarui profil: ' + error.message,
                            showConfirmButton: true
                        });
                    });
                }
            });
        });
    }
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
