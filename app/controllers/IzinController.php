<?php
require_once __DIR__ . '/../models/IzinModel.php';
require_once __DIR__ . '/../helpers/message_helper.php';
require_once __DIR__ . '/../helpers/fonnte_helper.php';
require_once __DIR__ . '/../../config/fonnte.php';

class IzinController
{
    private $model;

    public function __construct()
    {
        $this->model = new IzinModel();
    }

    // Halaman simanis user (simanis.php)
    public function formPublic()
    {
        include __DIR__ . '/../../public/simanis.php';
    }

    // Halaman Tracking surat (simanis-tracking.php)
    public function trackingSurat()
    {
        require __DIR__ . '/../../public/simanis-tracking.php';
    }

    // Halaman daftar perizinan (izin.php)
    public function daftarIzin()
    {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/izin.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman tambah Izin (tambah-izin.php)
    public function tambahIzin()
    {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/tambah-izin.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses tambah perizinan
    public function storeIzin()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $nik = trim($_POST['nik'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $tlp = trim($_POST['tlp'] ?? '');
        $jenis_surat = trim($_POST['jenis_surat'] ?? '');

        // ===== VALIDASI =====
        $errors = [];
        if (!$nik)
            $errors[] = 'NIK harus diisi';
        if (!$nama)
            $errors[] = 'Nama harus diisi';
        if (!$tlp)
            $errors[] = 'No Telepon/WA harus diisi';
        if (!$jenis_surat)
            $errors[] = 'Jenis Surat harus diisi';

        if ($errors) {
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }

        // VALIDASI FILE WAJIB
        if (!isset($_FILES['lampiran']) || $_FILES['lampiran']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'success' => false,
                'message' => 'File lampiran wajib diupload'
            ]);
            exit;
        }

        // ===== PREFIX =====
        switch ($jenis_surat) {
            case 'magang':
                $prefix = 'SPM';
                break;
            case 'penelitian':
                $prefix = 'SPP';
                break;
            case 'lainnya':
                $prefix = 'SPL';
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Jenis surat tidak valid']);
                exit;
        }

        // ===== ID + FILE NAME (OPSI B) =====
        $year = date('Y');
        $random = substr(bin2hex(random_bytes(5)), 0, 10);

        $id = $prefix . $year . $random;   // contoh: SPM2026abc123
        $fileName = $id . '.pdf';

        // ===== UPLOAD FILE =====
        $filePathDB = null;

        $tmp = $_FILES['lampiran']['tmp_name'];
        $size = $_FILES['lampiran']['size'];

        // Max 2MB
        if ($size > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 2MB']);
            exit;
        }

        // MIME check
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);

        if ($mime !== 'application/pdf') {
            echo json_encode(['success' => false, 'message' => 'File harus PDF']);
            exit;
        }

        // Folder tahun
        $uploadDir = __DIR__ . "/../../public/storage/uploads/simanis/surat_masuk/$year/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move file
        if (!move_uploaded_file($tmp, $uploadDir . $fileName)) {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file']);
            exit;
        }

        // Path untuk DB
        $filePathDB = "storage/uploads/simanis/surat_masuk/$year/$fileName";

        // ===== DATA DB =====
        $data = [
            'id' => $id,
            'nik' => $nik,
            'nama' => $nama,
            'tlp' => $tlp,
            'jenis_surat' => $jenis_surat,
            'file' => $filePathDB,
            'keterangan' => null,
            'file_balasan' => null,
            'status' => 1
        ];

        if ($this->model->tambahIzin($data)) {
            echo json_encode([
                'success' => true,
                'message' => 'Pengajuan perizinan berhasil dikirim',
                'id' => $id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data']);
        }
    }

    // Update Status Manual (Surat Masuk)
    public function updateStatus()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $id = $_POST['id'] ?? '';
        $status = $_POST['status'] ?? '';
        $keterangan = trim($_POST['keterangan'] ?? ''); // Ambil keterangan

        if (!$id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            exit;
        }

        if (!in_array($status, ['1', '2', '3', '4', '5', '6'])) {
            echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
            exit;
        }

        // Validasi Keterangan Wajib jika Status 2 atau 4
        if (($status == '2' || $status == '4') && empty($keterangan)) {
            echo json_encode(['success' => false, 'message' => 'Alasan penolakan wajib diisi']);
            exit;
        }

        // Jika status BUKAN 2 atau 4, set keterangan jadi NULL (biar bersih jika sebelumnya ditolak lalu diterima)
        if ($status != '2' && $status != '4') {
            $keterangan = null;
        }

        // Panggil model dengan 3 parameter
        if ($this->model->updateStatusIzin($id, $status, $keterangan)) {
            echo json_encode(['success' => true, 'message' => 'Status berhasil diubah']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengubah status']);
        }
        exit;
    }

    public function uploadBalasan()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $id = $_POST['id'] ?? '';

        if (!$id || empty($_FILES['file_balasan'])) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            exit;
        }

        // 1. Cek File Error
        if ($_FILES['file_balasan']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Upload error code: ' . $_FILES['file_balasan']['error']]);
            exit;
        }

        // 2. Validasi Tipe & Ukuran
        $tmp = $_FILES['file_balasan']['tmp_name'];
        $size = $_FILES['file_balasan']['size'];

        if ($size > 2 * 1024 * 1024) { // 2MB
            echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 2MB']);
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);

        if ($mime !== 'application/pdf') {
            echo json_encode(['success' => false, 'message' => 'File harus format PDF']);
            exit;
        }

        // 3. Persiapkan Folder & Nama File
        $year = date('Y');
        // Folder tujuan: public/storage/uploads/simanis/surat_balasan/$year/
        $uploadDir = __DIR__ . "/../../public/storage/uploads/simanis/surat_balasan/$year/";

        // Buat folder jika belum ada (rekursif)
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Nama file: ID_balasan.pdf
        $fileName = $id . '_balasan.pdf';
        $destination = $uploadDir . $fileName;

        // 4. Pindahkan File
        if (move_uploaded_file($tmp, $destination)) {

            // Path relatif untuk disimpan di database
            $filePathDB = "storage/uploads/simanis/surat_balasan/$year/$fileName";

            // 5. Update Database (Panggil Model)
            if ($this->model->updateFileBalasan($id, $filePathDB)) {
                echo json_encode(['success' => true, 'message' => 'File berhasil diupload']);
            } else {
                // Jika DB gagal, hapus file yang sudah terupload agar tidak sampah
                unlink($destination);
                echo json_encode(['success' => false, 'message' => 'Gagal update database']);
            }

        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memindahkan file ke server']);
        }
        exit;
    }

    // fungsi kirim balasan
    public function kirimBalasan()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $id = $_POST['id'] ?? '';

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            exit;
        }

        // 🔹 1. Ambil data dari DB
        $izin = $this->model->getIzinById($id);

        if (!$izin) {
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
            exit;
        }

        if (empty($izin['tlp'])) {
            echo json_encode(['success' => false, 'message' => 'Nomor HP tidak tersedia']);
            exit;
        }

        if (empty($izin['file_balasan'])) {
            echo json_encode(['success' => false, 'message' => 'File balasan belum tersedia']);
            exit;
        }

        // 🔹 2. Format nomor HP(Indonesia)
        $phone = preg_replace('/\D/', '', $izin['tlp']);

        // Jika diawali 0 → ubah ke 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        // Jika diawali 8 → anggap belum pakai kode negara
        elseif (substr($phone, 0, 1) === '8') {
            $phone = '62' . $phone;
        }
        // Jika sudah 62 → biarkan
        elseif (substr($phone, 0, 2) === '62') {
            // valid
        } else {
            echo json_encode(['success' => false, 'message' => 'Format nomor tidak valid']);
            exit;
        }

        // 🔹 3. Generate link file
        $baseUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        $fileLink = $baseUrl . BASE_URL . '/pdf-viewer.php?file=' . urlencode(ltrim($izin['file_balasan'], '/'));

        // 🔹 4. Generate pesan
        $message = buildWaMessage($izin['nama'], $fileLink);

        // 🔥 5. KIRIM VIA FONNTE
        $result = sendFonnteMessage($phone, $message);

        if ($result['success']) {
            $this->model->updateWaSuccess($id, $result['response']);

            echo json_encode([
                'success' => true,
                'message' => 'Pesan WhatsApp berhasil dikirim'
            ]);
        } else {
            $this->model->updateWaFailed($id, $result['response'] ?? $result['error']);

            echo json_encode([
                'success' => false,
                'message' => 'Gagal mengirim WhatsApp'
            ]);
        }

        exit;
    }

    // Proses hapus Perizinan (AJAX)
    public function hapusIzin()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID perizinan tidak valid']);
            exit;
        }

        // Ambil data perizinan sebelum dihapus
        $izin = $this->model->getIzinById($id);
        if (!$izin) {
            echo json_encode(['success' => false, 'message' => 'Data Perizinan tidak ditemukan']);
            exit;
        }

        // Hapus dari database
        if ($this->model->hapusIzin($id)) {
            echo json_encode(['success' => true, 'message' => 'Data perizinan berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data perizinan']);
        }

        exit;
    }
}