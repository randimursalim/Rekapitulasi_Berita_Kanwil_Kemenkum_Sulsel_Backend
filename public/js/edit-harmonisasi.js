// Edit Harmonisasi Form Management
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formHarmonisasi');
    const statusSelect = document.getElementById('status');
    const alasanGroup = document.getElementById('alasanGroup');
    const alasanTextarea = document.getElementById('alasan_pengembalian_draf');
    
    // Toggle alasan group berdasarkan status
    if (statusSelect && alasanGroup) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'Dikembalikan') {
                alasanGroup.style.display = 'block';
            } else {
                alasanGroup.style.display = 'none';
            }
        });
    }

    // Validasi form sebelum submit
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validasi jika status "Dikembalikan" tapi alasan kosong (opsional, tapi bisa diisi warning)
            if (statusSelect && statusSelect.value === 'Dikembalikan' && alasanTextarea && !alasanTextarea.value.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Status "Dikembalikan" dipilih, namun alasan pengembalian draf belum diisi. Apakah Anda yakin ingin melanjutkan?',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Konfirmasi final sebelum submit
                        Swal.fire({
                            title: 'Konfirmasi Update',
                            text: 'Apakah kamu yakin untuk mengupdate data harmonisasi ini?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, Update!',
                            cancelButtonText: 'Batal'
                        }).then((result2) => {
                            if (result2.isConfirmed) {
                                form.submit();
                            }
                        });
                    }
                });
            } else {
                // Konfirmasi sebelum submit
                Swal.fire({
                    title: 'Konfirmasi Update',
                    text: 'Apakah kamu yakin untuk mengupdate data harmonisasi ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Update!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    }
});

