// Edit Aduan Form Management
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formAduan');
    
    if (!form) return;
    
    // Konfirmasi sebelum submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Konfirmasi Update',
            text: 'Apakah kamu yakin untuk mengupdate aduan ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form jika dikonfirmasi
                form.submit();
            }
        });
    });
});

