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

        $nama = trim($_POST['nama'] ?? '');
        $telp = trim($_POST['telp'] ?? '');
        $email = $_POST['email'] ?? '';
        $alamat = $_POST['alamat'] ?? '';
        $tujuan = $_POST['tujuan'] ?? '';
        $layanan = $_POST['layanan'] ?? '';
        $layanan_item = $_POST['layanan_item'] ?? '';
        $entrain = isset($_POST['entrain']) ? 'yes' : 'no';
        $fotoBase64 = $_POST['foto'] ?? '';
        $ttdBase64 = $_POST['ttd'] ?? '';

        // VALIDASI
        $errors = [];
        if (!$nama)
            $errors[] = 'Nama harus diisi';
        if (!$telp)
            $errors[] = 'No Telepon harus diisi';
        if (!$email)
            $errors[] = 'Email harus diisi';
        if (!$alamat)
            $errors[] = 'Alamat harus diisi';
        if (!$tujuan)
            $errors[] = 'Tujuan harus diisi';
        if (!$layanan)
            $errors[] = 'Layanan harus diisi';
        if (!$layanan_item)
            $errors[] = 'Item layanan harus diisi';
        if (!$entrain)
            $errors[] = 'Status ambil antrean harus diisi';
        if (!$fotoBase64)
            $errors[] = 'Foto harus diisi';
        if (!$ttdBase64)
            $errors[] = 'Tanda tangan harus diisi';

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
            'nama' => $nama,
            'telp' => $telp,
            'email' => $email,
            'alamat' => $alamat,
            'tujuan' => $tujuan,
            'layanan' => $layanan,
            'layanan_item' => $layanan_item,
            'entrain' => $entrain,
            'foto' => $fotoFilename,
            'ttd' => $ttdFilename
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

    // export pdf
    public function printTamu()
    {
        require_once __DIR__ . '/../models/TamuModel.php';
        $model = new TamuModel();

        $startDate = $_GET['startDate'] ?? '';
        $endDate = $_GET['endDate'] ?? '';
        $layanan = $_GET['layanan'] ?? '';
        $layanan_item = $_GET['layanan_item'] ?? '';

        $data = $model->getTamuFiltered($startDate, $endDate, $layanan, $layanan_item);

        require_once __DIR__ . '/../views/pages/print-tamu.php';
    }

    // export excel
    public function exportExcel()
    {
        require_once __DIR__ . '/../models/TamuModel.php';

        $model = new TamuModel();

        $startDate = $_GET['startDate'] ?? '';
        $endDate = $_GET['endDate'] ?? '';
        $layanan = $_GET['layanan'] ?? '';
        $layanan_item = $_GET['layanan_item'] ?? '';

        $data = $model->getTamuExportExcel($startDate, $endDate, $layanan, $layanan_item);

        require __DIR__ . '/../views/pages/export-tamu-excel.php';
    }

    // export word
    public function exportWord()
    {
        require_once __DIR__ . '/../models/TamuModel.php';

        $model = new TamuModel();

        $startDate = $_GET['startDate'] ?? '';
        $endDate = $_GET['endDate'] ?? '';
        $layanan = $_GET['layanan'] ?? '';
        $layanan_item = $_GET['layanan_item'] ?? '';

        $data = $model->getTamuExportExcel($startDate, $endDate, $layanan, $layanan_item);

        require __DIR__ . '/../views/pages/export-tamu-word.php';
    }

    // Halaman edit Tamu (edit-tamu.php)
    public function editTamu()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID tamu tidak valid";
            exit;
        }

        $tamu = $this->model->getTamuById($id);
        if (!$tamu) {
            echo "Data tamu tidak ditemukan";
            exit;
        }

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-tamu.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses update tamu
    public function updateTamu()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tamu tidak valid']);
            exit;
        }

        $nama = trim($_POST['nama'] ?? '');
        $telp = trim($_POST['telp'] ?? '');
        $email = $_POST['email'] ?? '';
        $alamat = $_POST['alamat'] ?? '';
        $tujuan = $_POST['tujuan'] ?? '';
        $layanan = $_POST['layanan'] ?? '';
        $layanan_item = $_POST['layanan_item'] ?? '';
        $entrain = isset($_POST['entrain']) ? 'yes' : 'no';
        $fotoBase64 = $_POST['foto'] ?? '';
        $ttdBase64 = $_POST['ttd'] ?? '';

        // VALIDASI
        $errors = [];
        if (!$nama)
            $errors[] = 'Nama harus diisi';
        if (!$telp)
            $errors[] = 'No Telepon harus diisi';
        if (!$email)
            $errors[] = 'Email harus diisi';
        if (!$alamat)
            $errors[] = 'Alamat harus diisi';
        if (!$tujuan)
            $errors[] = 'Tujuan harus diisi';
        if (!$layanan)
            $errors[] = 'Layanan harus diisi';
        if (!$layanan_item)
            $errors[] = 'Item layanan harus diisi';

        if ($errors) {
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }

        // Ambil data tamu saat ini
        $tamu = $this->model->getTamuById($id);
        if (!$tamu) {
            echo json_encode(['success' => false, 'message' => 'Data tamu tidak ditemukan']);
            exit;
        }

        // Proses kamera jika ada foto baru
        $fotoFilename = $tamu['foto'];
        if (!empty($fotoBase64) && strpos($fotoBase64, 'data:image') === 0) {
            $uploadDir = __DIR__ . '/../../public/storage/uploads/foto/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Hapus foto lama jika ada
            if (!empty($tamu['foto']) && file_exists($uploadDir . $tamu['foto'])) {
                unlink($uploadDir . $tamu['foto']);
            }

            // Hapus prefix base64
            $fotoBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64);
            $fotoBinary = base64_decode($fotoBase64);

            if ($fotoBinary !== false) {
                $fotoFilename = 'B' . time() . rand(1000, 9999) . '.jpg';
                file_put_contents($uploadDir . $fotoFilename, $fotoBinary);
            }
        }

        // Proses TTD jika ada TTD baru
        $ttdFilename = $tamu['ttd'];
        if (!empty($ttdBase64) && strpos($ttdBase64, 'data:image') === 0) {
            $uploadDir = __DIR__ . '/../../public/storage/uploads/ttd/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Hapus TTD lama jika ada
            if (!empty($tamu['ttd']) && file_exists($uploadDir . $tamu['ttd'])) {
                unlink($uploadDir . $tamu['ttd']);
            }

            // Hapus prefix base64
            $ttdBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $ttdBase64);
            $ttdBinary = base64_decode($ttdBase64);

            if ($ttdBinary !== false) {
                $ttdFilename = 'A' . time() . rand(1000, 9999) . '.png';
                file_put_contents($uploadDir . $ttdFilename, $ttdBinary);
            }
        }

        // UPDATE DATABASE
        $data = [
            'id' => $id,
            'nama' => $nama,
            'telp' => $telp,
            'email' => $email,
            'alamat' => $alamat,
            'tujuan' => $tujuan,
            'layanan' => $layanan,
            'layanan_item' => $layanan_item,
            'entrain' => $entrain,
            'foto' => $fotoFilename,
            'ttd' => $ttdFilename
        ];

        if ($this->model->updateTamu($data)) {
            echo json_encode(['success' => true, 'message' => 'Data tamu berhasil diperbarui']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data tamu']);
        }
        exit;
    }
}
