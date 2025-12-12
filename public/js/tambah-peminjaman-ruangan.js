// Form Tambah Peminjaman Ruangan
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('formPeminjamanRuangan');
  
  if (!form) return;

  form.addEventListener('submit', function(e) {
    e.preventDefault();

    // Validasi form
    const namaPeminjam = document.getElementById('namaPeminjam').value.trim();
    const namaRuangan = document.getElementById('namaRuangan').value;
    const kegiatan = document.getElementById('kegiatan').value.trim();
    const tanggalKegiatan = document.getElementById('tanggalKegiatan').value;
    const waktuKegiatan = document.getElementById('waktuKegiatan').value;

    if (!namaPeminjam || !namaRuangan || !kegiatan || !tanggalKegiatan || !waktuKegiatan) {
      Swal.fire({
        icon: 'warning',
        title: 'Data Tidak Lengkap',
        text: 'Silakan lengkapi semua field yang wajib diisi.'
      });
      return;
    }

    // Konfirmasi sebelum submit
    Swal.fire({
      title: 'Simpan Data?',
      text: 'Apakah Anda yakin ingin menyimpan data peminjaman ruangan ini?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, Simpan!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });
});


