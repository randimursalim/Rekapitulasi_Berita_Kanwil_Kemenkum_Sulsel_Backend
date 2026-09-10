-- Table: roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id_role` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_role` VARCHAR(50) NOT NULL UNIQUE,
  `deskripsi` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: role_permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_role` INT NOT NULL,
  `menu_key` VARCHAR(50) NOT NULL,
  FOREIGN KEY (`id_role`) REFERENCES `roles`(`id_role`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update column role in pengguna table to support custom dynamic roles
ALTER TABLE `pengguna` MODIFY COLUMN `role` VARCHAR(100) NOT NULL DEFAULT 'Operator';

-- Insert Default Roles if not exists
INSERT IGNORE INTO `roles` (`id_role`, `nama_role`, `deskripsi`) VALUES
(1, 'Admin', 'Administrator dengan akses penuh ke seluruh menu dan sistem'),
(2, 'Operator', 'Operator dengan akses input, rekap, arsip, kegiatan, peminjaman, dan harmonisasi'),
(3, 'p3h', 'Petugas P3H dengan akses kegiatan, peminjaman, dan harmonisasi'),
(4, 'pegawai', 'Pegawai umum dengan akses peminjaman ruangan');

-- Insert Default Permissions for Admin (Access to all menus)
INSERT IGNORE INTO `role_permissions` (`id_role`, `menu_key`) VALUES
(1, 'dashboard'),
(1, 'input-konten'),
(1, 'rekap-konten'),
(1, 'tamu'),
(1, 'izin'),
(1, 'arsip'),
(1, 'layanan-pengaduan'),
(1, 'jadwal-kegiatan'),
(1, 'rekap-jadwal-kegiatan'),
(1, 'jadwal-peminjaman-ruangan'),
(1, 'harmonisasi'),
(1, 'rekap-harmonisasi'),
(1, 'pengguna'),
(1, 'statistik-pengguna'),
(1, 'role');

-- Insert Default Permissions for Operator
INSERT IGNORE INTO `role_permissions` (`id_role`, `menu_key`) VALUES
(2, 'dashboard'),
(2, 'input-konten'),
(2, 'rekap-konten'),
(2, 'arsip'),
(2, 'layanan-pengaduan'),
(2, 'jadwal-kegiatan'),
(2, 'rekap-jadwal-kegiatan'),
(2, 'jadwal-peminjaman-ruangan'),
(2, 'harmonisasi'),
(2, 'rekap-harmonisasi');

-- Insert Default Permissions for p3h
INSERT IGNORE INTO `role_permissions` (`id_role`, `menu_key`) VALUES
(3, 'jadwal-kegiatan'),
(3, 'rekap-jadwal-kegiatan'),
(3, 'jadwal-peminjaman-ruangan'),
(3, 'harmonisasi'),
(3, 'rekap-harmonisasi');

-- Insert Default Permissions for pegawai
INSERT IGNORE INTO `role_permissions` (`id_role`, `menu_key`) VALUES
(4, 'jadwal-peminjaman-ruangan');
