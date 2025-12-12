// Tambah Peminjaman Ruangan Masyarakat Form Management
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formPeminjamanRuangan');
    
    if (!form) return;
    
    // Set minimum date to today
    const tanggalInput = document.getElementById('tanggalKegiatan');
    if (tanggalInput) {
        const today = new Date().toISOString().split('T')[0];
        tanggalInput.setAttribute('min', today);
    }
    
    // Form submission handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(form);
        const data = {
            namaPeminjam: formData.get('namaPeminjam'),
            namaRuangan: formData.get('namaRuangan'),
            kegiatan: formData.get('kegiatan'),
            tanggalKegiatan: formData.get('tanggalKegiatan'),
            waktuKegiatan: formData.get('waktuKegiatan'),
            durasiKegiatan: formData.get('durasiKegiatan')
        };
        
        // Validation
        if (!data.namaPeminjam || !data.namaRuangan || !data.kegiatan || 
            !data.tanggalKegiatan || !data.waktuKegiatan || !data.durasiKegiatan) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: 'Mohon lengkapi semua field yang wajib diisi.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Validate durasi
        const durasi = parseFloat(data.durasiKegiatan);
        if (durasi < 1 || durasi > 8) {
            Swal.fire({
                icon: 'warning',
                title: 'Durasi Tidak Valid',
                text: 'Durasi kegiatan harus antara 1-8 jam.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Confirm submission
        Swal.fire({
            title: 'Konfirmasi Permohonan',
            text: 'Apakah Anda yakin ingin mengirim permohonan peminjaman ruangan ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#667eea',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Kirim',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form
                form.submit();
            }
        });
    });
});

