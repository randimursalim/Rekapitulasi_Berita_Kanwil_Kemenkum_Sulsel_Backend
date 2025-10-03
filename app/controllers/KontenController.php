<?php
require_once __DIR__ . '/../models/KontenModel.php';

class KontenController {
    private $model;

    public function __construct() {
        $this->model = new KontenModel();
    }

    public function index() {
    $model = new KontenModel();
    $statistik = $model->getStatistik();
    $logAktivitas = $model->getLogAktivitas();
    $detailBerita = $model->getDetailBerita();
    $detailMedsos = $model->getDetailMedsos();

    include '../app/views/layouts/header.php';
//      var_dump($detailBerita);
//  var_dump($detailMedsos);
    include '../app/views/pages/dashboard.php';
    include '../app/views/layouts/footer.php';
}

}
