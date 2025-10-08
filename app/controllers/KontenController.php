<?php
require_once __DIR__ . '/../models/KontenModel.php';

class KontenController {
    private $model;

    public function __construct() {
        // hanya 1 kali buat instance model
        //$this->model = new KontenModel();
    }

    // Halaman input-konten.php
    public function inputKonten() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/input-konten.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman rekap-konten.php
    public function rekapKonten() {
        //$statistik = $this->model->getStatistik(); // contoh ambil data
       // $detailBerita = $this->model->getDetailBerita();
        //$detailMedsos = $this->model->getDetailMedsos();

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/rekap-konten.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman edit-konten.php
    public function editKonten() {
        // ambil data konten tertentu misal dari $_GET['id']
        $id = $_GET['id'] ?? null;
      //  $konten = $id ? $this->model->getKontenById($id) : null;

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-konten.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman arsip.php
    public function arsip() {
       // $arsip = $this->model->getArsip(); // contoh data arsip

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/arsip.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }
}
