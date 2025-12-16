// Edit Konten Form Management
(function() {
    // Cegah eksekusi ganda jika inline script sudah dijalankan
    if (window.editKontenFormInitialized) return;
    
    function initEditKontenForm() {
        // Set flag untuk mencegah eksekusi ganda
        if (window.editKontenFormInitialized) return;
        window.editKontenFormInitialized = true;
        
        const jenisSelect = document.getElementById('jenis');
        const formBerita = document.getElementById('form-berita');
        const formMedsos = document.getElementById('form-medsos');
        const editForm = document.getElementById('editKontenForm');

        if (!jenisSelect) return;

        // Fungsi untuk menampilkan form sesuai jenis
        function toggleForm() {
            const value = jenisSelect.value;
            if (value === 'berita') {
                if (formBerita) formBerita.style.display = 'block';
                if (formMedsos) formMedsos.style.display = 'none';
            } else if (['instagram','youtube','tiktok','twitter','facebook'].includes(value)) {
                if (formBerita) formBerita.style.display = 'none';
                if (formMedsos) formMedsos.style.display = 'block';
            } else {
                if (formBerita) formBerita.style.display = 'none';
                if (formMedsos) formMedsos.style.display = 'none';
            }
        }

        // Jalankan saat halaman load (untuk edit, menampilkan sesuai data lama)
        toggleForm();

        // Jalankan saat user mengganti pilihan
        jenisSelect.addEventListener('change', toggleForm);

        // Konfirmasi sebelum submit form
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Update Konten?',
                        text: "Apakah kamu yakin untuk mengupdate konten ini?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, update!',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if(result.isConfirmed){
                            // Submit form jika konfirmasi
                            this.submit();
                        }
                    });
                } else {
                    // Fallback jika SweetAlert tidak tersedia
                    if (confirm('Apakah kamu yakin untuk mengupdate konten ini?')) {
                        this.submit();
                    }
                }
            });
        }
    }

    // Jalankan segera jika DOM sudah ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditKontenForm);
    } else {
        // DOM sudah ready, jalankan langsung
        initEditKontenForm();
    }
})();
