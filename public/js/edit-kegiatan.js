// Edit Kegiatan Form Management
document.addEventListener('DOMContentLoaded', function() {
    // Validasi jam
    const jamMulai = document.getElementById('jamMulai');
    const jamSelesai = document.getElementById('jamSelesai');
    
    function validateTime() {
        if (jamMulai.value && jamSelesai.value) {
            if (jamMulai.value >= jamSelesai.value) {
                jamSelesai.setCustomValidity('Jam selesai harus lebih besar dari jam mulai');
                jamSelesai.reportValidity();
            } else {
                jamSelesai.setCustomValidity('');
            }
        }
    }

    jamMulai.addEventListener('change', validateTime);
    jamSelesai.addEventListener('change', validateTime);

    // Validasi form sebelum submit
    document.getElementById('formKegiatan').addEventListener('submit', function(e) {
        validateTime();
        
        if (jamMulai.value >= jamSelesai.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                text: 'Jam selesai harus lebih besar dari jam mulai',
                showConfirmButton: true
            });
            return false;
        }
        
        // Konfirmasi sebelum submit
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Update',
            text: 'Apakah kamu yakin untuk mengupdate kegiatan ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form jika dikonfirmasi
                this.submit();
            }
        });
    });
});
