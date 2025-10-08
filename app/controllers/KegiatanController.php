<?php
require_once __DIR__ . '/../models/KegiatanModel.php';

class KegiatanController {
    private $model;

    public function __construct() {
        // inisialisasi model
      //  $this->model = new KegiatanModel();
    }

    // Halaman jadwal-kegiatan.php
    public function jadwalKegiatan() {
        // nanti bisa ambil data jadwal
        // $jadwal = $this->model->getAllJadwal();

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/jadwal-kegiatan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman tambah-kegiatan.php
    public function tambahKegiatan() {
        // nanti bisa dipakai form tambah
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/tambah-kegiatan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman edit-kegiatan.php
    public function editKegiatan() {
        $id = $_GET['id'] ?? null;
        // $kegiatan = $id ? $this->model->getKegiatanById($id) : null;

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-kegiatan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }
}
