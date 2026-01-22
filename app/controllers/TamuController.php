<?php
require_once __DIR__ . '/../models/TamuModel.php';

class TamuController
{
    private $model;

    public function __construct()
    {
        $this->model = new TamuModel();
    }

    // Halaman simtamu user (simtamu.php)
    public function formPublic()
    {
        include __DIR__ . '/../../public/simtamu.php';
    }

    // Halaman daftar Tamu (tamu.php)
    public function daftarTamu()
    {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/tamu.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman tambah Tamu (tambah-tamu.php)
    public function tambahTamu()
    {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/tambah-tamu.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses tambah tamu
    public function storeTamu()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $nama   = trim($_POST['nama'] ?? '');
        $telp   = trim($_POST['telp'] ?? '');
        $email  = $_POST['email'] ?? '';
        $alamat = $_POST['alamat'] ?? '';
        $tujuan = $_POST['tujuan'] ?? '';
        $fotoBase64 = $_POST['foto'] ?? '';
        $ttdBase64 = $_POST['ttd'] ?? '';

        // VALIDASI
        $errors = [];
        if (!$nama)   $errors[] = 'Nama harus diisi';
        if (!$telp)   $errors[] = 'No Telepon harus diisi';
        if (!$email)  $errors[] = 'Email harus diisi';
        if (!$alamat) $errors[] = 'Alamat harus diisi';
        if (!$tujuan) $errors[] = 'Tujuan harus diisi';
        if (!$fotoBase64) $errors[] = 'Foto harus diisi';
        if (!$ttdBase64) $errors[] = 'Tanda tangan harus diisi';

        if ($errors) {
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }

        // Proses kamera
        $fotoBase64 = $_POST['foto'] ?? '';

        $fotoFilename = null;

        if ($fotoBase64) {
            $uploadDir = __DIR__ . '/../../public/storage/uploads/foto/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Hapus prefix base64
            $fotoBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64);
            $fotoBinary = base64_decode($fotoBase64);

            if ($fotoBinary === false) {
                echo json_encode(['success' => false, 'message' => 'Format foto tidak valid']);
                exit;
            }

            // NAMA FILE SESUAI CONTOH DB KAMU
            $fotoFilename = 'B' . time() . rand(1000, 9999) . '.jpg';
            $fotoPath = $uploadDir . $fotoFilename;

            file_put_contents($fotoPath, $fotoBinary);
        }

        // PROSES TTD (BASE64 → PNG)
        $uploadDir = __DIR__ . '/../../public/storage/uploads/ttd/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Hapus prefix base64
        $ttdBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $ttdBase64);
        $ttdBinary = base64_decode($ttdBase64);

        if ($ttdBinary === false) {
            echo json_encode(['success' => false, 'message' => 'Format tanda tangan tidak valid']);
            exit;
        }

        // Nama file sesuai contoh database kamu
        $ttdFilename = 'A' . time() . rand(1000, 9999) . '.png';
        $ttdPath = $uploadDir . $ttdFilename;

        file_put_contents($ttdPath, $ttdBinary);

        // SIMPAN KE DATABASE
        $data = [
            'nama'   => $nama,
            'telp'   => $telp,
            'email'  => $email,
            'alamat' => $alamat,
            'tujuan' => $tujuan,
            'foto'   => $fotoFilename,
            'ttd'    => $ttdFilename
        ];

        if ($this->model->tambahTamu($data)) {
            echo json_encode(['success' => true, 'message' => 'Tamu berhasil ditambahkan']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan tamu']);
        }
        exit;
    }

    // Proses hapus TAMU (AJAX)
    public function hapusTamu()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tamu tidak valid']);
            exit;
        }

        // Ambil data tamu sebelum dihapus
        $tamu = $this->model->getTamuById($id);
        if (!$tamu) {
            echo json_encode(['success' => false, 'message' => 'Data tamu tidak ditemukan']);
            exit;
        }

        // Hapus file FOTO
        if (!empty($tamu['foto'])) {
            $fotoPath = __DIR__ . '/../../public/storage/uploads/foto/' . $tamu['foto'];
            if (file_exists($fotoPath)) {
                unlink($fotoPath);
            }
        }

        // Hapus file TTD
        if (!empty($tamu['ttd'])) {
            $ttdPath = __DIR__ . '/../../public/storage/uploads/ttd/' . $tamu['ttd'];
            if (file_exists($ttdPath)) {
                unlink($ttdPath);
            }
        }

        // Hapus dari database
        if ($this->model->hapusTamu($id)) {
            echo json_encode(['success' => true, 'message' => 'Data tamu berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data tamu']);
        }

        exit;
    }

    public function printTamu()
    {
        require_once __DIR__ . '/../models/TamuModel.php';
        $model = new TamuModel();

        $tahun = $_GET['tahun'] ?? date('Y');
        $bulan = $_GET['bulan'] ?? 'all';

        if ($bulan === 'all') {
            $data = $model->getTamuByTahun($tahun);
            $judulBulan = 'SEMUA BULAN';
        } else {
            $data = $model->getTamuByBulan($tahun, $bulan);
            $judulBulan = strtoupper(strftime('%B', mktime(0, 0, 0, $bulan, 1)));
        }

        require_once __DIR__ . '/../views/pages/print-tamu.php';
    }
}
