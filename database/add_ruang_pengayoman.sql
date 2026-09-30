-- Script SQL untuk memperbarui kolom nama_ruangan pada tabel `jadwal_peminjaman_ruangan`

-- OPSI 1: Jika kolom `nama_ruangan` di database hosting menggunakan tipe data ENUM
ALTER TABLE `jadwal_peminjaman_ruangan` 
MODIFY COLUMN `nama_ruangan` ENUM(
    'Ruang Rapat Baharuddin Lopa (Kakanwil)',
    'Ruang Rapat Andi Mattalatta (Lantai 1)',
    'Ruang Rapat Hamid Awaluddin (Lantai 2)',
    'Ruang Rapat Bhinneka Tunggal Ika (Lantai 3)',
    'Aula Pancasila (Lantai 3)',
    'Ruang Pengayoman (Ex Musala Lantai 3)'
) NOT NULL;

-- OPSI 2: Jika kolom `nama_ruangan` di database hosting menggunakan tipe data VARCHAR(255)
-- ALTER TABLE `jadwal_peminjaman_ruangan` MODIFY COLUMN `nama_ruangan` VARCHAR(255) NOT NULL;
