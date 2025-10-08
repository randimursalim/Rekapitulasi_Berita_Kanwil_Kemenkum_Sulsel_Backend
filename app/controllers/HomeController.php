<?php
// app/controllers/HomeController.php
require_once __DIR__ . '/../models/HomeModel.php';

class HomeController {
    private $model;

    public function __construct() {
        // Buat instance KontenModel sekali saja
        $this->model = new HomeModel();
    }

    public function index() {
        // Ambil data dari model
        $statistik     = $this->model->getStatistik();
        $logAktivitas  = $this->model->getLogAktivitas();
        $detailBerita  = $this->model->getDetailBerita();
        $detailMedsos  = $this->model->getDetailMedsos();

        // Include view header, halaman dashboard, dan footer
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/dashboard.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }
}
