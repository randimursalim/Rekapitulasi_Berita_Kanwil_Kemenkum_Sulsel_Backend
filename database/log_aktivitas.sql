-- Script untuk membuat tabel log_aktivitas
-- Jalankan script ini di database MySQL

CREATE TABLE IF NOT EXISTS log_aktivitas (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    aktivitas VARCHAR(255) NOT NULL,
    tanggal DATE NOT NULL,
    waktu TIME NOT NULL,
    user VARCHAR(100) NOT NULL,
    status ENUM('Berhasil', 'Gagal', 'Pending') NOT NULL DEFAULT 'Berhasil',
    id_user INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tambahkan foreign key constraint jika tabel pengguna sudah ada
-- ALTER TABLE log_aktivitas
-- ADD CONSTRAINT fk_log_user FOREIGN KEY (id_user) REFERENCES pengguna(id_pengguna) ON DELETE SET NULL;

-- Insert beberapa data contoh
INSERT INTO log_aktivitas (aktivitas, tanggal, waktu, user, status, id_user) VALUES
('Login ke sistem', CURDATE(), CURTIME(), 'Admin', 'Berhasil', 1),
('Menambahkan konten: Berita Terbaru', CURDATE(), CURTIME(), 'Admin', 'Berhasil', 1),
('Mengedit konten: Update Berita', CURDATE(), CURTIME(), 'Admin', 'Berhasil', 1),
('Menghapus konten: Berita Lama', CURDATE(), CURTIME(), 'Admin', 'Berhasil', 1),
('Menambahkan pengguna baru', CURDATE(), CURTIME(), 'Admin', 'Berhasil', 1),
('Mengedit profil pengguna', CURDATE(), CURTIME(), 'Admin', 'Berhasil', 1),
('Menghapus pengguna', CURDATE(), CURTIME(), 'Admin', 'Berhasil', 1),
('Export data ke PDF', CURDATE(), CURTIME(), 'Admin', 'Berhasil', 1),
('Download grafik JPG', CURDATE(), CURTIME(), 'Admin', 'Berhasil', 1),
('Menambahkan kegiatan baru', CURDATE(), CURTIME(), 'Admin', 'Berhasil', 1);
