<?php
// app/models/UserModel.php
require_once __DIR__ . '/../../config/database.php';

class UserModel
{
    private $db;

    public function __construct()
    {
        global $conn;
        $this->db = $conn;
    }

    // Ambil user berdasarkan username
    public function getUserByUsername($username) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM pengguna WHERE username = ?");
            $stmt->execute([$username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    // Verifikasi password
    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }

    // Update last login (optional)
    public function updateLastLogin($userId) {
        try {
            $stmt = $this->db->prepare("UPDATE pengguna SET last_login = NOW() WHERE id_pengguna = ?");
            $stmt->execute([$userId]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Cek apakah user ada
    public function userExists($username) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM pengguna WHERE username = ?");
            $stmt->execute([$username]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
