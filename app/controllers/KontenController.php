<?php
require_once __DIR__ . '/../models/KontenModel.php';

class KontenController {
    private $model;

    public function __construct() {
        $this->model = new KontenModel();
    }

    // === FORM INPUT ===
    public function inputKonten() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/input-konten.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // === SIMPAN DATA KONTEN ===
    public function storeKonten() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?page=input-konten&status=invalid');
        exit;
    }

    // Ambil data utama
    $jenis = $_POST['jenis'] ?? '';
    $judul = $_POST['judul'] ?? '';
    $divisi = $_POST['divisi'] ?? '';

    // Handle upload dokumentasi (opsional)
    $dokumentasi = null;
    if (!empty($_FILES['dokumentasi']['name'])) {
        // Path folder uploads di public supaya bisa diakses browser
        $targetDir = __DIR__ . '/../../public/storage/uploads/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Nama file unik
        $fileName = time() . '_' . basename($_FILES['dokumentasi']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['dokumentasi']['tmp_name'], $targetFile)) {
            // Path relatif untuk akses via browser
            $dokumentasi = 'storage/uploads/' . $fileName;
        }
    }

    // Simpan ke tabel konten
    $idKonten = $this->model->insertKonten($jenis, $judul, $divisi, $dokumentasi);

    if (!$idKonten) {
        header('Location: index.php?page=input-konten&status=error_main');
        exit;
    }

    // Simpan ke tabel detail
    $detailSaved = false;
    if ($jenis === 'berita') {
        $detailSaved = $this->model->insertBerita(
            $idKonten,
            $_POST['tanggalBerita'] ?? null,
            $_POST['linkBerita'] ?? null,
            $_POST['sumberBerita'] ?? null,
            $_POST['jenisBerita'] ?? null,
            $_POST['ringkasan'] ?? null
        );
    } else {
        $detailSaved = $this->model->insertMedsos(
            $idKonten,
            $_POST['tanggalPost'] ?? null,
            $_POST['linkPost'] ?? null,
            $_POST['caption'] ?? null
        );
    }

    // === CEK HASIL ===
    if ($detailSaved) {
        // ✅ jika sukses simpan semua
        header('Location: index.php?page=input-konten&status=success');
        exit;
    } else {
        // ❌ jika gagal, hapus data utama agar tidak nyangkut di DB
        $this->model->deleteKonten($idKonten);
        header('Location: index.php?page=input-konten&status=error_detail');
        exit;
    }
}


    // === REKAP ===
    public function rekapKonten() {
        $detailBerita = $this->model->getDetailBerita();
        $detailMedsos = $this->model->getDetailMedsos();

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/rekap-konten.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // === EDIT ===
    public function editKonten() {
        $id = $_GET['id'] ?? null;
        $konten = $id ? $this->model->getKontenById($id) : null;

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-konten.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // === ARSIP ===
public function arsip() {
    // Ambil data dari model
    $detailBerita = $this->model->getDetailBerita();
    $detailMedsos  = $this->model->getDetailMedsos();

    // Pastikan variabel berupa array (menghindari null)
    if (!is_array($detailBerita)) $detailBerita = [];
    if (!is_array($detailMedsos)) $detailMedsos = [];

    include __DIR__ . '/../views/layouts/header.php';
    include __DIR__ . '/../views/pages/arsip.php';
    include __DIR__ . '/../views/layouts/footer.php';
}

}
