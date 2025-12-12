-- Script untuk menambahkan kolom durasi_kegiatan ke tabel jadwal_peminjaman_ruangan
-- Jalankan script ini di database MySQL

-- Tambahkan kolom durasi_kegiatan (dalam jam, default 2 jam)
ALTER TABLE jadwal_peminjaman_ruangan 
ADD COLUMN durasi_kegiatan DECIMAL(3,1) DEFAULT 1.0 COMMENT 'Durasi kegiatan dalam jam (min 1, max 8)';

-- Update data existing dengan durasi default 2 jam
UPDATE jadwal_peminjaman_ruangan 
SET durasi_kegiatan = 2.0 
WHERE durasi_kegiatan IS NULL;

