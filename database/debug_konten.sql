-- Script sederhana untuk test log aktivitas
-- Jalankan script ini di database MySQL

-- Cek apakah tabel konten ada
SELECT 'Checking konten table...' as status;

-- Cek struktur tabel konten
DESCRIBE konten;

-- Cek data di tabel konten
SELECT COUNT(*) as total_konten FROM konten;

-- Cek beberapa data konten
SELECT id_konten, judul, tanggal_input FROM konten ORDER BY tanggal_input DESC LIMIT 5;
