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
            echo "Akses tidak valid.";
            return;
        }

        // Ambil data utama
        $jenis = $_POST['jenis'] ?? '';
        $judul = $_POST['judul'] ?? '';
        $divisi = $_POST['divisi'] ?? '';

        // Handle upload dokumentasi (opsional)
        $dokumentasi = null;
        if (!empty($_FILES['dokumentasi']['name'])) {
            $targetDir = __DIR__ . '/../../storage/uploads/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $fileName = time() . '_' . basename($_FILES['dokumentasi']['name']);
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($_FILES['dokumentasi']['tmp_name'], $targetFile)) {
                $dokumentasi = 'storage/uploads/' . $fileName;
            }
        }

        // Simpan ke tabel konten
        $idKonten = $this->model->insertKonten($jenis, $judul, $divisi, $dokumentasi);

        if (!$idKonten) {
            echo "❌ Gagal menyimpan data konten utama!";
            return;
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

        if ($detailSaved) {
            header('Location: index.php?page=rekap-konten&status=success');
            exit;
        } else {
            // Jika gagal simpan detail, hapus konten utama
            $this->model->deleteKonten($idKonten);
            echo "❌ Gagal menyimpan detail konten!";
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
       // $arsip = $this->model->getArsip();

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/arsip.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }
}
