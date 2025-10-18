-- Script untuk menambahkan kolom user ke tabel konten
-- Jalankan script ini di database MySQL

-- Tambahkan kolom id_user ke tabel konten
ALTER TABLE konten 
ADD COLUMN id_user INT,
ADD CONSTRAINT fk_konten_user FOREIGN KEY (id_user) REFERENCES pengguna(id_pengguna) ON DELETE SET NULL;

-- Update data existing dengan user default (Admin)
UPDATE konten SET id_user = 1 WHERE id_user IS NULL;

-- Tambahkan kolom created_by untuk menyimpan nama user yang membuat konten
ALTER TABLE konten 
ADD COLUMN created_by VARCHAR(100) DEFAULT 'Admin';

-- Update data existing
UPDATE konten SET created_by = 'Admin' WHERE created_by IS NULL;
