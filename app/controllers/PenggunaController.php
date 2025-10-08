<?php
require_once __DIR__ . '/../models/PenggunaModel.php';

class PenggunaController {
    private $model;

    public function __construct() {
        // inisialisasi model
        // $this->model = new PenggunaModel();
    }

    // Halaman daftar pengguna (pengguna.php)
    public function daftarPengguna() {
        // nanti bisa ambil data pengguna dari model
        // $pengguna = $this->model->getAllPengguna();

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/pengguna.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman tambah pengguna (tambah-pengguna.php)
    public function tambahPengguna() {
        // nanti bisa digunakan untuk form tambah pengguna baru

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/tambah-pengguna.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman edit pengguna (edit-pengguna.php)
    public function editPengguna() {
        $id = $_GET['id'] ?? null;
        // $pengguna = $id ? $this->model->getPenggunaById($id) : null;

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-pengguna.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman edit profil (edit-profil.php)
    public function editProfilPengguna() {
        // nanti bisa pakai data user yang sedang login
        // $profil = $this->model->getProfilByUserId($userId);

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-profil.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }
}
