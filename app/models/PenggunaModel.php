<?php
require_once __DIR__ . '/../../config/database.php';

class PenggunaModel {
    private $db;

    public function __construct() {
        global $conn;
        $this->db = $conn;
    }

    // Ambil semua pengguna dengan pagination
    public function getAllPengguna($page = 1, $limit = 10, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT id_pengguna, nama, username, role, foto, created_at FROM pengguna WHERE 1=1";
        $params = [];
        
        if ($search !== '') {
            $search = "%{$search}%";
            $query .= " AND (nama LIKE ? OR username LIKE ?)";
            $params[] = $search;
            $params[] = $search;
        }
        
        $query .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Hitung total pengguna
    public function getTotalPengguna($search = '') {
        $query = "SELECT COUNT(*) as total FROM pengguna WHERE 1=1";
        $params = [];
        
        if ($search !== '') {
            $search = "%{$search}%";
            $query .= " AND (nama LIKE ? OR username LIKE ?)";
            $params[] = $search;
            $params[] = $search;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Ambil pengguna berdasarkan ID
    public function getPenggunaById($id) {
        $query = "SELECT id_pengguna, nama, username, role, foto, created_at FROM pengguna WHERE id_pengguna = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tambah pengguna baru
    public function tambahPengguna($data) {
        $query = "INSERT INTO pengguna (nama, username, password, role, foto) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $stmt->execute([
            $data['nama'],
            $data['username'],
            $password,
            $data['role'],
            $data['foto'] ?? 'user.jpg'
        ]);
    }

    // Update pengguna
    public function updatePengguna($id, $data) {
        $query = "UPDATE pengguna SET nama = ?, username = ?, role = ?, foto = ?";
        $params = [$data['nama'], $data['username'], $data['role'], $data['foto'] ?? 'user.jpg'];
        
        // Jika password diupdate
        if (!empty($data['password'])) {
            $query .= ", password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $query .= " WHERE id_pengguna = ?";
        $params[] = $id;
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    // Hapus pengguna
    public function hapusPengguna($id) {
        $query = "DELETE FROM pengguna WHERE id_pengguna = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    // Cek apakah username sudah ada
    public function isUsernameExists($username, $excludeId = null) {
        $query = "SELECT COUNT(*) as total FROM pengguna WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $query .= " AND id_pengguna != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
    }

    // Ambil semua role yang tersedia
    public function getAvailableRoles() {
        return ['Admin', 'Operator'];
    }
}
