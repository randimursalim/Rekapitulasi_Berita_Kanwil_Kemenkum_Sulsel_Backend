<?php
require_once __DIR__ . '/../models/LayananPengaduanModel.php';

class LayananPengaduanController {
    private $model;

    public function __construct() {
        $this->model = new LayananPengaduanModel();
    }

    // Halaman daftar-layanan-pengaduan.php
    public function daftarLayananPengaduan() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/daftar-layanan-pengaduan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman tambah-layanan-pengaduan.php (untuk admin)
    public function tambahLayananPengaduan() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/tambah-layanan-pengaduan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman tambah-layanan-pengaduan-masyarakat.php (untuk masyarakat, tanpa login)
    public function tambahLayananPengaduanMasyarakat() {
        include __DIR__ . '/../views/pages/tambah-layanan-pengaduan-masyarakat.php';
    }

    // Halaman tracking pengaduan (untuk masyarakat, tanpa login)
    public function trackingLayananPengaduan() {
        include __DIR__ . '/../views/pages/tracking-layanan-pengaduan.php';
    }

    // Proses tracking pengaduan berdasarkan nomor register
    public function trackingResult() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $noRegister = trim($_POST['no_register'] ?? '');
            
            if (empty($noRegister)) {
                header('Location: index.php?page=tracking-layanan-pengaduan&status=error&message=' . urlencode('Nomor register tidak boleh kosong.'));
                exit;
            }
            
            // Cari pengaduan berdasarkan nomor register
            $pengaduan = $this->model->getLayananPengaduanByNoRegister($noRegister);
            
            if ($pengaduan) {
                // Redirect ke halaman tracking dengan data
                header('Location: index.php?page=tracking-layanan-pengaduan&no_register=' . urlencode($noRegister) . '&found=1');
            } else {
                header('Location: index.php?page=tracking-layanan-pengaduan&status=error&message=' . urlencode('Nomor register tidak ditemukan. Pastikan nomor register yang Anda masukkan benar.'));
            }
            exit;
        }
    }

    // Helper function untuk error message upload
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File melebihi ukuran maksimal yang diizinkan oleh server (upload_max_filesize)';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File melebihi ukuran maksimal yang diizinkan oleh form (MAX_FILE_SIZE)';
            case UPLOAD_ERR_PARTIAL:
                return 'File hanya ter-upload sebagian';
            case UPLOAD_ERR_NO_FILE:
                return 'Tidak ada file yang diupload';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Folder temporary tidak ditemukan';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Gagal menulis file ke disk';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload dihentikan oleh extension PHP';
            default:
                return 'Error tidak diketahui: ' . $errorCode;
        }
    }

    // Proses tambah layanan pengaduan untuk masyarakat (tanpa login, tanpa tindak_lanjut dan keterangan)
    public function storeLayananPengaduanMasyarakat() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Generate nomor register otomatis
            $noRegister = $this->model->generateNoRegister();
            
            // Untuk masyarakat, tindak_lanjut selalu 'belum diproses' dan keterangan null
            $data = [
                'no_register_pengaduan' => $noRegister,
                'nama' => $_POST['nama'] ?? '',
                'alamat' => $_POST['alamat'] ?? '',
                'jenis_tanda_pengenal' => $_POST['jenisTandaPengenal'] ?? '',
                'jenis_tanda_pengenal_lainnya' => ($_POST['jenisTandaPengenal'] ?? '') === 'LAINNYA' ? ($_POST['jenisTandaPengenalLainnya'] ?? '') : null,
                'no_tanda_pengenal' => $_POST['noTandaPengenal'] ?? '',
                'no_telp' => $_POST['noTelp'] ?? '',
                'judul_laporan' => $_POST['judulLaporan'] ?? '',
                'isi_laporan' => $_POST['isiLaporan'] ?? '',
                'tanggal_kejadian' => $_POST['tanggalKejadian'] ?? '',
                'lokasi_kejadian' => $_POST['lokasiKejadian'] ?? '',
                'kategori_laporan' => $_POST['kategoriLaporan'] ?? '',
                'jenis_aduan' => $_POST['jenisAduan'] ?? '',
                'jenis_aduan_lainnya' => ($_POST['jenisAduan'] ?? '') === 'Lainnya' ? ($_POST['jenisAduanLainnya'] ?? '') : null,
                'tindak_lanjut' => 'belum diproses', // Default untuk masyarakat
                'keterangan' => null // Masyarakat tidak bisa isi keterangan
            ];

            // Validasi data wajib
            if (empty($data['no_register_pengaduan']) || empty($data['nama']) || 
                empty($data['alamat']) || empty($data['jenis_tanda_pengenal']) || 
                empty($data['no_tanda_pengenal']) || empty($data['judul_laporan']) || 
                empty($data['isi_laporan']) || empty($data['tanggal_kejadian']) || 
                empty($data['lokasi_kejadian']) || empty($data['kategori_laporan']) || 
                empty($data['jenis_aduan'])) {
                header('Location: index.php?page=tambah-layanan-pengaduan-masyarakat&status=error');
                exit;
            }

            // Validasi jika pilihan "LAINNYA" atau "Lainnya" harus diisi field lainnya
            if ($data['jenis_tanda_pengenal'] === 'LAINNYA' && empty($data['jenis_tanda_pengenal_lainnya'])) {
                header('Location: index.php?page=tambah-layanan-pengaduan-masyarakat&status=error');
                exit;
            }
            if ($data['jenis_aduan'] === 'Lainnya' && empty($data['jenis_aduan_lainnya'])) {
                header('Location: index.php?page=tambah-layanan-pengaduan-masyarakat&status=error');
                exit;
            }

            try {
                if ($this->model->tambahLayananPengaduan($data)) {
                    // Redirect dengan nomor register untuk ditampilkan ke user
                    header('Location: index.php?page=tambah-layanan-pengaduan-masyarakat&status=success&no_register=' . urlencode($noRegister));
                } else {
                    error_log("[ERROR] Store Layanan Pengaduan Masyarakat: Failed to insert data");
                    header('Location: index.php?page=tambah-layanan-pengaduan-masyarakat&status=error');
                }
            } catch (Exception $e) {
                error_log("[ERROR] Store Layanan Pengaduan Masyarakat: " . $e->getMessage());
                header('Location: index.php?page=tambah-layanan-pengaduan-masyarakat&status=error');
            }
            exit;
        }
    }

    // Proses tambah layanan pengaduan (untuk admin)
    public function storeLayananPengaduan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Generate nomor register otomatis
            $noRegister = $this->model->generateNoRegister();
            
            // Handle upload file keterangan (bisa teks + file sekaligus)
            $keteranganText = trim($_POST['keterangan'] ?? '');
            $keterangan = $keteranganText;
            
            // Debug: log input
            error_log("[LAYANAN PENGADUAN] ========== START UPLOAD PROCESS ==========");
            error_log("[LAYANAN PENGADUAN] Keterangan text: " . $keteranganText);
            error_log("[LAYANAN PENGADUAN] REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
            error_log("[LAYANAN PENGADUAN] Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'N/A'));
            error_log("[LAYANAN PENGADUAN] _FILES array keys: " . (empty($_FILES) ? 'EMPTY' : implode(', ', array_keys($_FILES))));
            error_log("[LAYANAN PENGADUAN] _FILES full: " . print_r($_FILES, true));
            
            // Cek apakah file diupload (gunakan cara yang sama seperti KontenController)
            if (!empty($_FILES['keteranganFile']['name'])) {
                error_log("[LAYANAN PENGADUAN] File detected: " . $_FILES['keteranganFile']['name']);
                error_log("[LAYANAN PENGADUAN] File error code: " . $_FILES['keteranganFile']['error']);
                error_log("[LAYANAN PENGADUAN] File size: " . $_FILES['keteranganFile']['size'] . " bytes");
                
                require_once __DIR__ . '/../helpers/SecureFileUpload.php';
                $uploadHandler = new SecureFileUpload('layanan-pengaduan');
                $uploadResult = $uploadHandler->uploadFile('keteranganFile', 'keterangan');
                
                error_log("[LAYANAN PENGADUAN] Upload result: " . json_encode($uploadResult));
                
                if ($uploadResult['success'] && !empty($uploadResult['path'])) {
                    // Gabungkan teks dan file dengan format: "TEXT\nFILE: path_file|original_name"
                    $fileInfo = $uploadResult['path'];
                    if (!empty($uploadResult['original_name'])) {
                        $fileInfo .= '|' . $uploadResult['original_name'];
                    }
                    
                    if (!empty($keteranganText)) {
                        $keterangan = $keteranganText . "\nFILE: " . $fileInfo;
                    } else {
                        $keterangan = "FILE: " . $fileInfo;
                    }
                    error_log("[LAYANAN PENGADUAN] ✓ SUCCESS: Final keterangan (with file): " . $keterangan);
                } else {
                    // Jika upload gagal, gunakan teks saja (jika ada)
                    $errorMsg = $uploadResult['message'] ?? 'Upload file gagal';
                    error_log("[LAYANAN PENGADUAN] ✗ UPLOAD FAILED: " . $errorMsg);
                    // Jika tidak ada teks juga, redirect dengan error
                    if (empty($keteranganText)) {
                        header('Location: index.php?page=tambah-layanan-pengaduan&status=error&message=' . urlencode($errorMsg));
                        exit;
                    }
                }
            } else {
                error_log("[LAYANAN PENGADUAN] No file uploaded (empty name or not set)");
            }
            
            error_log("[LAYANAN PENGADUAN] Final keterangan to save: " . ($keterangan ?: 'EMPTY'));
            error_log("[LAYANAN PENGADUAN] Final keterangan length: " . strlen($keterangan));
            error_log("[LAYANAN PENGADUAN] ========== END UPLOAD PROCESS ==========");
            
            $data = [
                'no_register_pengaduan' => $noRegister,
                'nama' => $_POST['nama'] ?? '',
                'alamat' => $_POST['alamat'] ?? '',
                'jenis_tanda_pengenal' => $_POST['jenisTandaPengenal'] ?? '',
                'jenis_tanda_pengenal_lainnya' => ($_POST['jenisTandaPengenal'] ?? '') === 'LAINNYA' ? ($_POST['jenisTandaPengenalLainnya'] ?? '') : null,
                'no_tanda_pengenal' => $_POST['noTandaPengenal'] ?? '',
                'no_telp' => $_POST['noTelp'] ?? '',
                'judul_laporan' => $_POST['judulLaporan'] ?? '',
                'isi_laporan' => $_POST['isiLaporan'] ?? '',
                'tanggal_kejadian' => $_POST['tanggalKejadian'] ?? '',
                'lokasi_kejadian' => $_POST['lokasiKejadian'] ?? '',
                'kategori_laporan' => $_POST['kategoriLaporan'] ?? '',
                'jenis_aduan' => $_POST['jenisAduan'] ?? '',
                'jenis_aduan_lainnya' => ($_POST['jenisAduan'] ?? '') === 'Lainnya' ? ($_POST['jenisAduanLainnya'] ?? '') : null,
                'tindak_lanjut' => $_POST['tindakLanjut'] ?? 'belum diproses',
                'keterangan' => $keterangan
            ];

            // Validasi data wajib
            if (empty($data['no_register_pengaduan']) || empty($data['nama']) || 
                empty($data['alamat']) || empty($data['jenis_tanda_pengenal']) || 
                empty($data['no_tanda_pengenal']) || empty($data['judul_laporan']) || 
                empty($data['isi_laporan']) || empty($data['tanggal_kejadian']) || 
                empty($data['lokasi_kejadian']) || empty($data['kategori_laporan']) || 
                empty($data['jenis_aduan'])) {
                header('Location: index.php?page=tambah-layanan-pengaduan&status=error');
                exit;
            }

            // Validasi jika pilihan "LAINNYA" atau "Lainnya" harus diisi field lainnya
            if ($data['jenis_tanda_pengenal'] === 'LAINNYA' && empty($data['jenis_tanda_pengenal_lainnya'])) {
                header('Location: index.php?page=tambah-layanan-pengaduan&status=error');
                exit;
            }
            if ($data['jenis_aduan'] === 'Lainnya' && empty($data['jenis_aduan_lainnya'])) {
                header('Location: index.php?page=tambah-layanan-pengaduan&status=error');
                exit;
            }

            try {
                if ($this->model->tambahLayananPengaduan($data)) {
                    // Tambahkan log aktivitas
                    require_once __DIR__ . '/../models/HomeModel.php';
                    $homeModel = new HomeModel();
                    $homeModel->addLogAktivitas("Menambahkan layanan pengaduan: " . $data['no_register_pengaduan']);
                    
                    header('Location: index.php?page=tambah-layanan-pengaduan&status=success');
                } else {
                    error_log("[ERROR] Store Layanan Pengaduan: Failed to insert data");
                    header('Location: index.php?page=tambah-layanan-pengaduan&status=error');
                }
            } catch (Exception $e) {
                error_log("[ERROR] Store Layanan Pengaduan: " . $e->getMessage());
                header('Location: index.php?page=tambah-layanan-pengaduan&status=error');
            }
            exit;
        }
    }

    // Halaman edit-layanan-pengaduan.php
    public function editLayananPengaduan() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: index.php?page=layanan-pengaduan');
            exit;
        }

        $layananPengaduan = $this->model->getLayananPengaduanById($id);
        
        if (!$layananPengaduan) {
            header('Location: index.php?page=layanan-pengaduan');
            exit;
        }

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-layanan-pengaduan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses update layanan pengaduan
    public function updateLayananPengaduan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                header('Location: index.php?page=layanan-pengaduan');
                exit;
            }

            // Handle upload file keterangan (bisa teks + file sekaligus)
            $keteranganText = trim($_POST['keterangan'] ?? '');
            $layananLama = $this->model->getLayananPengaduanById($id);
            $keteranganLama = $layananLama['keterangan'] ?? '';
            
            // Parse keterangan lama untuk ekstrak teks dan file
            $keteranganLamaText = '';
            $keteranganLamaFile = '';
            if (!empty($keteranganLama)) {
                if (strpos($keteranganLama, 'FILE:') !== false) {
                    // Ada file di keterangan lama
                    $parts = explode('FILE:', $keteranganLama, 2);
                    $keteranganLamaText = trim($parts[0]);
                    $keteranganLamaFile = trim($parts[1] ?? '');
                } else if (strpos($keteranganLama, 'storage/uploads/') !== false || preg_match('/\.(pdf|jpg|jpeg|png|doc|docx)$/i', $keteranganLama)) {
                    // Keterangan lama hanya file (format lama)
                    $keteranganLamaFile = $keteranganLama;
                } else {
                    // Keterangan lama hanya teks
                    $keteranganLamaText = $keteranganLama;
                }
            }
            
            $keterangan = $keteranganText;
            
            // Debug: log input
            error_log("[LAYANAN PENGADUAN UPDATE] ========== START UPDATE UPLOAD PROCESS ==========");
            error_log("[LAYANAN PENGADUAN UPDATE] Keterangan text: " . $keteranganText);
            error_log("[LAYANAN PENGADUAN UPDATE] REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
            error_log("[LAYANAN PENGADUAN UPDATE] Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'N/A'));
            error_log("[LAYANAN PENGADUAN UPDATE] _FILES array keys: " . (empty($_FILES) ? 'EMPTY' : implode(', ', array_keys($_FILES))));
            error_log("[LAYANAN PENGADUAN UPDATE] _FILES full: " . print_r($_FILES, true));
            
            // Jika ada file baru diupload (gunakan cara yang sama seperti KontenController)
            if (!empty($_FILES['keteranganFile']['name'])) {
                require_once __DIR__ . '/../helpers/SecureFileUpload.php';
                $uploadHandler = new SecureFileUpload('layanan-pengaduan');
                $uploadResult = $uploadHandler->uploadFile('keteranganFile', 'keterangan');
                
                error_log("[LAYANAN PENGADUAN UPDATE] Upload result: " . json_encode($uploadResult));
                
                if ($uploadResult['success']) {
                    // Hapus file lama jika ada (SEBELUM menyimpan file baru)
                    if (!empty($keteranganLamaFile)) {
                        error_log("[LAYANAN PENGADUAN UPDATE] File lama ditemukan: " . $keteranganLamaFile);
                        
                        // Parse untuk dapatkan secure filename saja
                        // Format bisa: "storage/uploads/filename" atau "storage/uploads/filename|original_name"
                        $oldFileParts = explode('|', $keteranganLamaFile);
                        $oldPath = trim($oldFileParts[0]);
                        $oldSecureFileName = basename($oldPath);
                        
                        error_log("[LAYANAN PENGADUAN UPDATE] Attempting to delete old file: " . $oldSecureFileName);
                        
                        $deleteResult = $uploadHandler->deleteFile($oldSecureFileName);
                        
                        if ($deleteResult) {
                            error_log("[LAYANAN PENGADUAN UPDATE] ✓ File lama berhasil dihapus: " . $oldSecureFileName);
                        } else {
                            error_log("[LAYANAN PENGADUAN UPDATE] ✗ Gagal menghapus file lama: " . $oldSecureFileName);
                            // Lanjutkan proses meskipun delete gagal (file mungkin sudah tidak ada)
                        }
                    } else {
                        error_log("[LAYANAN PENGADUAN UPDATE] Tidak ada file lama untuk dihapus");
                    }
                    
                    // Gabungkan teks dan file dengan format: "TEXT\nFILE: path_file|original_name"
                    $fileInfo = $uploadResult['path'];
                    if (!empty($uploadResult['original_name'])) {
                        $fileInfo .= '|' . $uploadResult['original_name'];
                    }
                    
                    if (!empty($keteranganText)) {
                        $keterangan = $keteranganText . "\nFILE: " . $fileInfo;
                    } else {
                        $keterangan = "FILE: " . $fileInfo;
                    }
                } else {
                    // Jika upload gagal, gunakan teks baru + file lama (jika ada)
                    if (!empty($keteranganLamaFile)) {
                        if (!empty($keteranganText)) {
                            $keterangan = $keteranganText . "\nFILE: " . $keteranganLamaFile;
                        } else {
                            $keterangan = "FILE: " . $keteranganLamaFile;
                        }
                    }
                    // Jika tidak ada file lama dan tidak ada teks, tetap gunakan teks yang diinput
                }
            } else {
                // Jika tidak ada file baru, gabungkan teks baru dengan file lama (jika ada)
                error_log("[LAYANAN PENGADUAN UPDATE] Tidak ada file baru diupload");
                if (!empty($keteranganLamaFile)) {
                    error_log("[LAYANAN PENGADUAN UPDATE] Mempertahankan file lama: " . $keteranganLamaFile);
                    if (!empty($keteranganText)) {
                        $keterangan = $keteranganText . "\nFILE: " . $keteranganLamaFile;
                    } else {
                        $keterangan = "FILE: " . $keteranganLamaFile;
                    }
                } else {
                    error_log("[LAYANAN PENGADUAN UPDATE] Tidak ada file lama, hanya menggunakan teks");
                }
                // Jika tidak ada file lama, gunakan teks saja
            }
            
            error_log("[LAYANAN PENGADUAN UPDATE] Final keterangan to save: " . ($keterangan ?: 'EMPTY'));
            
            $data = [
                'no_register_pengaduan' => $_POST['noRegisterPengaduan'] ?? '',
                'nama' => $_POST['nama'] ?? '',
                'alamat' => $_POST['alamat'] ?? '',
                'jenis_tanda_pengenal' => $_POST['jenisTandaPengenal'] ?? '',
                'jenis_tanda_pengenal_lainnya' => ($_POST['jenisTandaPengenal'] ?? '') === 'LAINNYA' ? ($_POST['jenisTandaPengenalLainnya'] ?? '') : null,
                'no_tanda_pengenal' => $_POST['noTandaPengenal'] ?? '',
                'no_telp' => $_POST['noTelp'] ?? '',
                'judul_laporan' => $_POST['judulLaporan'] ?? '',
                'isi_laporan' => $_POST['isiLaporan'] ?? '',
                'tanggal_kejadian' => $_POST['tanggalKejadian'] ?? '',
                'lokasi_kejadian' => $_POST['lokasiKejadian'] ?? '',
                'kategori_laporan' => $_POST['kategoriLaporan'] ?? '',
                'jenis_aduan' => $_POST['jenisAduan'] ?? '',
                'jenis_aduan_lainnya' => ($_POST['jenisAduan'] ?? '') === 'Lainnya' ? ($_POST['jenisAduanLainnya'] ?? '') : null,
                'tindak_lanjut' => $_POST['tindakLanjut'] ?? 'belum diproses',
                'keterangan' => $keterangan
            ];

            // Validasi data wajib
            if (empty($data['no_register_pengaduan']) || empty($data['nama']) || 
                empty($data['alamat']) || empty($data['jenis_tanda_pengenal']) || 
                empty($data['no_tanda_pengenal']) || empty($data['judul_laporan']) || 
                empty($data['isi_laporan']) || empty($data['tanggal_kejadian']) || 
                empty($data['lokasi_kejadian']) || empty($data['kategori_laporan']) || 
                empty($data['jenis_aduan'])) {
                header('Location: index.php?page=edit-layanan-pengaduan&id=' . $id . '&status=error');
                exit;
            }

            // Validasi jika pilihan "LAINNYA" atau "Lainnya" harus diisi field lainnya
            if ($data['jenis_tanda_pengenal'] === 'LAINNYA' && empty($data['jenis_tanda_pengenal_lainnya'])) {
                header('Location: index.php?page=edit-layanan-pengaduan&id=' . $id . '&status=error');
                exit;
            }
            if ($data['jenis_aduan'] === 'Lainnya' && empty($data['jenis_aduan_lainnya'])) {
                header('Location: index.php?page=edit-layanan-pengaduan&id=' . $id . '&status=error');
                exit;
            }

            try {
                if ($this->model->updateLayananPengaduan($id, $data)) {
                    // Tambahkan log aktivitas
                    require_once __DIR__ . '/../models/HomeModel.php';
                    $homeModel = new HomeModel();
                    $homeModel->addLogAktivitas("Mengedit layanan pengaduan: " . $data['no_register_pengaduan']);
                    
                    header('Location: index.php?page=edit-layanan-pengaduan&id=' . $id . '&status=success');
                } else {
                    error_log("[ERROR] Update Layanan Pengaduan: Failed to update data");
                    header('Location: index.php?page=edit-layanan-pengaduan&id=' . $id . '&status=error');
                }
            } catch (Exception $e) {
                error_log("[ERROR] Update Layanan Pengaduan: " . $e->getMessage());
                header('Location: index.php?page=edit-layanan-pengaduan&id=' . $id . '&status=error');
            }
            exit;
        }
    }

    // Hapus layanan pengaduan (AJAX)
    public function hapusLayananPengaduan() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
                exit;
            }

            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID layanan pengaduan tidak valid']);
                exit;
            }

            // Validasi ID
            $id = (int) $id;
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID layanan pengaduan tidak valid']);
                exit;
            }

            // Cek apakah layanan pengaduan ada
            $layananPengaduan = $this->model->getLayananPengaduanById($id);
            if (!$layananPengaduan) {
                echo json_encode(['success' => false, 'message' => 'Layanan pengaduan tidak ditemukan']);
                exit;
            }

            // Hapus file keterangan jika ada
            $keterangan = $layananPengaduan['keterangan'] ?? '';
            if (!empty($keterangan)) {
                // Parse keterangan untuk ekstrak file path
                $filePath = '';
                if (strpos($keterangan, 'FILE:') !== false) {
                    $parts = explode('FILE:', $keterangan, 2);
                    $filePath = trim($parts[1] ?? '');
                } else if (strpos($keterangan, 'storage/uploads/') !== false || preg_match('/\.(pdf|jpg|jpeg|png|doc|docx)$/i', $keterangan)) {
                    $filePath = $keterangan;
                }
                
                if (!empty($filePath)) {
                    // Parse untuk dapatkan secure filename saja (format: "path|original_name" atau hanya "path")
                    $fileParts = explode('|', $filePath);
                    $securePath = trim($fileParts[0]);
                    $secureFileName = basename($securePath);
                    
                    error_log("[LAYANAN PENGADUAN DELETE] Attempting to delete file: " . $secureFileName);
                    
                    require_once __DIR__ . '/../helpers/SecureFileUpload.php';
                    $uploadHandler = new SecureFileUpload('layanan-pengaduan');
                    $deleteResult = $uploadHandler->deleteFile($secureFileName);
                    
                    if ($deleteResult) {
                        error_log("[LAYANAN PENGADUAN DELETE] ✓ File berhasil dihapus: " . $secureFileName);
                    } else {
                        error_log("[LAYANAN PENGADUAN DELETE] ✗ Gagal menghapus file: " . $secureFileName);
                        // Lanjutkan proses meskipun delete gagal
                    }
                }
            }

            // Hapus layanan pengaduan
            $result = $this->model->hapusLayananPengaduan($id);
            
            if ($result) {
                // Tambahkan log aktivitas
                require_once __DIR__ . '/../models/HomeModel.php';
                $homeModel = new HomeModel();
                $homeModel->addLogAktivitas("Menghapus layanan pengaduan: " . $layananPengaduan['no_register_pengaduan']);
                
                echo json_encode(['success' => true, 'message' => 'Layanan pengaduan berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus layanan pengaduan dari database']);
            }
            
        } catch (Exception $e) {
            error_log("[ERROR] Delete Layanan Pengaduan Controller: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server']);
        }
    }

    // Download file keterangan
    public function downloadKeterangan() {
        $filePath = $_GET['file'] ?? '';
        
        if (empty($filePath)) {
            header('HTTP/1.0 404 Not Found');
            die('File not found');
        }
        
        // Parse path dan original name (format: "storage/uploads/filename|original_name")
        $parts = explode('|', $filePath, 2);
        $securePath = $parts[0];
        $originalName = $parts[1] ?? basename($securePath);
        
        // Sanitize path untuk keamanan
        $securePath = str_replace('..', '', $securePath);
        $securePath = ltrim($securePath, '/');
        
        // Full path file
        $fullPath = __DIR__ . '/../../public/' . $securePath;
        
        // Cek apakah file ada
        if (!file_exists($fullPath)) {
            header('HTTP/1.0 404 Not Found');
            die('File not found');
        }
        
        // Deteksi MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fullPath);
        finfo_close($finfo);
        
        // Set headers untuk download
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . addslashes($originalName) . '"');
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Output file
        readfile($fullPath);
        exit;
    }
}

