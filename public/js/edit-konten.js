// Edit Konten Form Management
document.addEventListener('DOMContentLoaded', function () {
    const jenisSelect = document.getElementById('jenis');
    const formBerita = document.getElementById('form-berita');
    const formMedsos = document.getElementById('form-medsos');
    const editForm = document.getElementById('editKontenForm');

    if (!jenisSelect) return;

    // Fungsi untuk menampilkan form sesuai jenis
    function toggleForm() {
        const value = jenisSelect.value;
        if (value === 'berita') {
            formBerita.style.display = 'block';
            formMedsos.style.display = 'none';
        } else if (['instagram','youtube','tiktok','twitter','facebook'].includes(value)) {
            formBerita.style.display = 'none';
            formMedsos.style.display = 'block';
        } else {
            formBerita.style.display = 'none';
            formMedsos.style.display = 'none';
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
        });
    }
});
