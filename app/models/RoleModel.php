<?php
// app/models/RoleModel.php
require_once __DIR__ . '/../../config/database.php';

class RoleModel {
    private $db;

    public function __construct() {
        global $conn;
        $this->db = $conn;
    }

    // Ambil daftar semua menu sistem beserta deskripsi dan kategorinya
    public function getAvailableMenus() {
        return [
            'Informasi & Konten' => [
                'dashboard' => 'Dashboard Utama',
                'input-konten' => 'Input Konten Berita',
                'rekap-konten' => 'Rekap Konten Berita',
                'arsip' => 'Manajemen Arsip'
            ],
            'Layanan & Pengaduan' => [
                'tamu' => 'Buku Tamu',
                'izin' => 'Perizinan Magang/Penelitian',
                'layanan-pengaduan' => 'Layanan Pengaduan'
            ],
            'Kegiatan & Fasilitas' => [
                'jadwal-kegiatan' => 'Jadwal Kegiatan',
                'rekap-jadwal-kegiatan' => 'Rekap Jadwal Kegiatan',
                'jadwal-peminjaman-ruangan' => 'Peminjaman Ruangan'
            ],
            'Harmonisasi Hukum' => [
                'harmonisasi' => 'Data Harmonisasi',
                'rekap-harmonisasi' => 'Rekap Harmonisasi'
            ],
            'Pengaturan & Pengguna' => [
                'pengguna' => 'Manajemen Pengguna',
                'statistik-pengguna' => 'Statistik Pengguna',
                'role' => 'Manajemen Role & Hak Akses'
            ]
        ];
    }

    // Ambil semua role dengan total pengguna
    public function getAllRoles() {
        $query = "SELECT r.*, COUNT(p.id_pengguna) AS total_pengguna 
                  FROM roles r 
                  LEFT JOIN pengguna p ON r.nama_role = p.role 
                  GROUP BY r.id_role 
                  ORDER BY r.id_role ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil detail role berdasarkan ID
    public function getRoleById($idRole) {
        $query = "SELECT * FROM roles WHERE id_role = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$idRole]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Ambil list menu_key yang diizinkan untuk id_role tertentu
    public function getPermissionsByRoleId($idRole) {
        $query = "SELECT menu_key FROM role_permissions WHERE id_role = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$idRole]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Ambil list menu_key berdasarkan nama role
    public function getPermissionsByRoleName($roleName) {
        $query = "SELECT rp.menu_key 
                  FROM role_permissions rp
                  JOIN roles r ON rp.id_role = r.id_role
                  WHERE r.nama_role = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$roleName]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Tambah role baru beserta permission-nya
    public function tambahRole($namaRole, $deskripsi, $permissions = []) {
        try {
            $this->db->beginTransaction();

            $query = "INSERT INTO roles (nama_role, deskripsi) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$namaRole, $deskripsi]);
            $idRole = $this->db->lastInsertId();

            if (!empty($permissions)) {
                $permQuery = "INSERT INTO role_permissions (id_role, menu_key) VALUES (?, ?)";
                $permStmt = $this->db->prepare($permQuery);
                foreach ($permissions as $menuKey) {
                    $permStmt->execute([$idRole, $menuKey]);
                }
            }

            $this->db->commit();
            return $idRole;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error tambahRole: " . $e->getMessage());
            return false;
        }
    }

    // Update role beserta permission-nya
    public function updateRole($idRole, $namaRole, $deskripsi, $permissions = []) {
        try {
            $this->db->beginTransaction();

            // Update data role
            $query = "UPDATE roles SET nama_role = ?, deskripsi = ? WHERE id_role = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$namaRole, $deskripsi, $idRole]);

            // Hapus permission lama
            $delQuery = "DELETE FROM role_permissions WHERE id_role = ?";
            $delStmt = $this->db->prepare($delQuery);
            $delStmt->execute([$idRole]);

            // Insert permission baru
            if (!empty($permissions)) {
                $permQuery = "INSERT INTO role_permissions (id_role, menu_key) VALUES (?, ?)";
                $permStmt = $this->db->prepare($permQuery);
                foreach ($permissions as $menuKey) {
                    $permStmt->execute([$idRole, $menuKey]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updateRole: " . $e->getMessage());
            return false;
        }
    }

    // Hapus role
    public function hapusRole($idRole) {
        try {
            // Cek apakah role sedang dipakai oleh pengguna
            $roleData = $this->getRoleById($idRole);
            if ($roleData) {
                $checkQuery = "SELECT COUNT(*) FROM pengguna WHERE role = ?";
                $checkStmt = $this->db->prepare($checkQuery);
                $checkStmt->execute([$roleData['nama_role']]);
                if ($checkStmt->fetchColumn() > 0) {
                    return ['success' => false, 'message' => 'Role tidak dapat dihapus karena sedang digunakan oleh pengguna.'];
                }
            }

            $query = "DELETE FROM roles WHERE id_role = ?";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$idRole]);
            return ['success' => $result, 'message' => $result ? 'Role berhasil dihapus.' : 'Gagal menghapus role.'];
        } catch (Exception $e) {
            error_log("Error hapusRole: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menghapus role: ' . $e->getMessage()];
        }
    }

    // Cek apakah nama role sudah ada
    public function isRoleNameExists($namaRole, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM roles WHERE nama_role = ?";
        $params = [$namaRole];
        if ($excludeId) {
            $query .= " AND id_role != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
