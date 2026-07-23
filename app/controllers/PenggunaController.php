<?php
require_once __DIR__ . '/../models/PenggunaModel.php';

class PenggunaController {
    private $model;

    public function __construct() {
        $this->model = new PenggunaModel();
    }

    // Halaman daftar pengguna (pengguna.php)
    public function daftarPengguna() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/pengguna.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman tambah pengguna (tambah-pengguna.php)
    public function tambahPengguna() {
        $roles = $this->model->getAvailableRoles();
        
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/tambah-pengguna.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses tambah pengguna
    public function storePengguna() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $nama = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'Operator';

        // Validasi
        $errors = [];
        if (empty($nama)) $errors[] = 'Nama harus diisi';
        if (empty($username)) $errors[] = 'Username harus diisi';
        if (empty($password)) $errors[] = 'Password harus diisi';
        if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter';
        if ($password !== $confirmPassword) $errors[] = 'Password dan konfirmasi password tidak sama';
        if (!in_array($role, ['Admin', 'Operator', 'p3h', 'pegawai'])) $errors[] = 'Role tidak valid';

        // Cek username sudah ada
        if ($this->model->isUsernameExists($username)) {
            $errors[] = 'Username sudah digunakan';
        }

        if (!empty($errors)) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }

        // Handle foto upload
        $foto = 'user.jpg'; // Default foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/Images/users/';
            $storageDir = __DIR__ . '/../../public/storage/uploads/users/';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            if (!is_dir($storageDir)) @mkdir($storageDir, 0755, true);

            $fileExtension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fileName = 'user_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
                $foto = $fileName;
                @copy($uploadPath, $storageDir . $fileName);
            }
        }

        // Simpan data
        $data = [
            'nama' => $nama,
            'username' => $username,
            'password' => $password,
            'role' => $role,
            'foto' => $foto
        ];

        if ($this->model->tambahPengguna($data)) {
            echo json_encode(['success' => true, 'message' => 'Pengguna berhasil ditambahkan']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan pengguna']);
        }
        exit;
    }

    // Halaman edit pengguna (edit-pengguna.php)
    public function editPengguna() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/index.php?page=pengguna');
            exit;
        }

        $pengguna = $this->model->getPenggunaById($id);
        if (!$pengguna) {
            $_SESSION['errors'] = ['Pengguna tidak ditemukan'];
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/index.php?page=pengguna');
            exit;
        }

        $roles = $this->model->getAvailableRoles();
        
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-pengguna.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses update pengguna
    public function updatePengguna() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID pengguna tidak valid']);
            exit;
        }

        $nama = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'Operator';

        // Validasi
        $errors = [];
        if (empty($nama)) $errors[] = 'Nama harus diisi';
        if (empty($username)) $errors[] = 'Username harus diisi';
        if (!in_array($role, ['Admin', 'Operator', 'p3h', 'pegawai'])) $errors[] = 'Role tidak valid';
        
        // Validasi password jika diisi
        if (!empty($password)) {
            if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter';
            if ($password !== $confirmPassword) $errors[] = 'Password dan konfirmasi password tidak sama';
        }

        // Cek username sudah ada (kecuali untuk user yang sama)
        if ($this->model->isUsernameExists($username, $id)) {
            $errors[] = 'Username sudah digunakan';
        }

        if (!empty($errors)) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }

        // Handle foto upload & reset foto
        $penggunaLama = $this->model->getPenggunaById($id);
        $foto = $penggunaLama['foto'] ?? 'user.jpg';
        $resetFoto = $_POST['reset_foto'] ?? '0';

        if ($resetFoto === '1') {
            if (!empty($penggunaLama['foto']) && $penggunaLama['foto'] !== 'user.jpg') {
                require_once __DIR__ . '/../helpers/SecureFileUpload.php';
                $uploadHandler = new SecureFileUpload('users');
                $uploadHandler->deleteFile($penggunaLama['foto']);
                $oldImagesFile = __DIR__ . '/../../public/Images/users/' . $penggunaLama['foto'];
                if (file_exists($oldImagesFile)) {
                    @unlink($oldImagesFile);
                }
            }
            $foto = 'user.jpg';
        } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/Images/users/';
            $storageDir = __DIR__ . '/../../public/storage/uploads/users/';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            if (!is_dir($storageDir)) @mkdir($storageDir, 0755, true);

            $fileExtension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fileName = 'user_' . $id . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
                $foto = $fileName;
                @copy($uploadPath, $storageDir . $fileName);
            }
        }

        // Simpan data
        $data = [
            'nama' => $nama,
            'username' => $username,
            'role' => $role,
            'foto' => $foto
        ];

        // Jika password diisi, update password
        if (!empty($password)) {
            $data['password'] = $password;
        }

        if ($this->model->updatePengguna($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Pengguna berhasil diperbarui']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui pengguna']);
        }
        exit;
    }

    // Proses hapus pengguna (AJAX)
    public function hapusPengguna() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID pengguna tidak valid']);
            exit;
        }

        // Ambil data pengguna sebelum dihapus untuk mendapatkan info foto
        $pengguna = $this->model->getPenggunaById($id);
        if (!$pengguna) {
            echo json_encode(['success' => false, 'message' => 'Pengguna tidak ditemukan']);
            exit;
        }

        // Hapus foto profil jika bukan foto default
        $fileDeleted = true;
        if (!empty($pengguna['foto']) && $pengguna['foto'] !== 'user.jpg') {
            $filePath = __DIR__ . '/../../public/Images/users/' . $pengguna['foto'];
            if (file_exists($filePath)) {
                $fileDeleted = unlink($filePath);
                if (!$fileDeleted) {
                    error_log("[WARNING] Gagal hapus foto profil: " . $filePath);
                    // Tidak exit, tetap lanjut hapus dari database
                }
            }
        }

        // Hapus pengguna dari database
        if ($this->model->hapusPengguna($id)) {
            echo json_encode(['success' => true, 'message' => 'Pengguna berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus pengguna']);
        }
        exit;
    }

    // Halaman edit profil (edit-profil.php)
    public function editProfilPengguna() {
        // nanti bisa pakai data user yang sedang login
        // $profil = $this->model->getProfilByUserId($userId);

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-profil.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses update profil pengguna
    public function updateProfilPengguna() {
        if (ob_get_level() > 0) @ob_clean();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (ob_get_level() > 0) @ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        // Ambil ID user yang sedang login
        $id = $_SESSION['user']['id'] ?? null;
        if (!$id) {
            if (ob_get_level() > 0) @ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User tidak valid / Sesi berakhir. Silakan login ulang.']);
            exit;
        }

        $nama = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $konfirmasi = $_POST['konfirmasi'] ?? '';

        // Validasi
        $errors = [];
        if (empty($nama)) $errors[] = 'Nama harus diisi';
        if (empty($username)) $errors[] = 'Username harus diisi';
        
        // Validasi password jika diisi
        if (!empty($password)) {
            if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter';
            if ($password !== $konfirmasi) $errors[] = 'Password dan konfirmasi password tidak sama';
        }

        // Cek username sudah ada (kecuali untuk user yang sama)
        if ($this->model->isUsernameExists($username, $id)) {
            $errors[] = 'Username sudah digunakan';
        }

        if (!empty($errors)) {
            if (ob_get_level() > 0) @ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }

        // Handle foto upload & reset foto
        $foto = $_SESSION['user']['foto'] ?? 'user.jpg'; // Default foto dari session
        $resetFoto = $_POST['reset_foto'] ?? '0';
        
        if ($resetFoto === '1') {
            // Hapus foto lama dari disk jika bukan foto default
            $fotoLama = $_SESSION['user']['foto'] ?? 'user.jpg';
            if (!empty($fotoLama) && $fotoLama !== 'user.jpg') {
                require_once __DIR__ . '/../helpers/SecureFileUpload.php';
                $uploadHandler = new SecureFileUpload('users');
                $uploadHandler->deleteFile($fotoLama);
                
                $imagesDir = __DIR__ . '/../../public/Images/users/';
                if (file_exists($imagesDir . $fotoLama)) {
                    @unlink($imagesDir . $fotoLama);
                }
            }
            $foto = 'user.jpg';
        } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Cek error khusus batas ukuran PHP
            if ($_FILES['foto']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['foto']['error'] === UPLOAD_ERR_FORM_SIZE) {
                if (ob_get_level() > 0) @ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Ukuran file foto terlalu besar. Maksimal 10MB.']);
                exit;
            } elseif ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                if (ob_get_level() > 0) @ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Gagal mengunggah foto. Kode error: ' . $_FILES['foto']['error']]);
                exit;
            }

            require_once __DIR__ . '/../helpers/SecureFileUpload.php';
            $uploadHandler = new SecureFileUpload('users');
            
            $uploadResult = $uploadHandler->uploadFile('foto', 'user');
            
            if ($uploadResult['success']) {
                $foto = $uploadResult['filename'];
                $srcPath = __DIR__ . '/../../public/storage/uploads/users/' . $foto;
                $imagesDir = __DIR__ . '/../../public/Images/users/';
                if (!is_dir($imagesDir)) {
                    @mkdir($imagesDir, 0755, true);
                }
                
                // Optimized copy & resize if needed
                self::optimizeAndSyncUserAvatar($srcPath, $imagesDir . $foto);

                // Hapus foto lama jika bukan foto default dan beda dengan foto baru
                $fotoLama = $_SESSION['user']['foto'] ?? 'user.jpg';
                if ($fotoLama !== 'user.jpg' && $fotoLama !== $foto) {
                    $uploadHandler->deleteFile($fotoLama);
                    $oldImagesFile = $imagesDir . $fotoLama;
                    if (file_exists($oldImagesFile)) {
                        @unlink($oldImagesFile);
                    }
                }
            } else {
                if (ob_get_level() > 0) @ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Upload foto gagal: ' . $uploadResult['message']]);
                exit;
            }
        }

        // Simpan data
        $data = [
            'nama' => $nama,
            'username' => $username,
            'foto' => $foto,
            'role' => $_SESSION['user']['role'] ?? 'Operator'
        ];

        // Jika password diisi, update password
        if (!empty($password)) {
            $data['password'] = $password;
        }

        if ($this->model->updatePengguna($id, $data)) {
            // Update session dengan data baru
            $_SESSION['user']['nama'] = $nama;
            $_SESSION['user']['username'] = $username;
            $_SESSION['user']['foto'] = $foto;
            
            if (ob_get_level() > 0) @ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Profil berhasil diperbarui']);
        } else {
            if (ob_get_level() > 0) @ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui profil']);
        }
        exit;
    }

    private static function optimizeAndSyncUserAvatar($srcPath, $destPath) {
        if (!file_exists($srcPath)) return;
        @copy($srcPath, $destPath);
        
        if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
            $imgData = @file_get_contents($srcPath);
            if (!$imgData) return;
            
            $srcImg = @imagecreatefromstring($imgData);
            if (!$srcImg) return;
            
            $width = imagesx($srcImg);
            $height = imagesy($srcImg);
            
            $maxSize = 800;
            if ($width > $maxSize || $height > $maxSize) {
                if ($width > $height) {
                    $newWidth = $maxSize;
                    $newHeight = intval($height * ($maxSize / $width));
                } else {
                    $newHeight = $maxSize;
                    $newWidth = intval($width * ($maxSize / $height));
                }
                
                $dstImg = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($dstImg, false);
                imagesavealpha($dstImg, true);
                $transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
                imagefilledrectangle($dstImg, 0, 0, $newWidth, $newHeight, $transparent);
                
                imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                
                $ext = strtolower(pathinfo($srcPath, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg'])) {
                    imagejpeg($dstImg, $srcPath, 85);
                    imagejpeg($dstImg, $destPath, 85);
                } elseif ($ext === 'png') {
                    imagepng($dstImg, $srcPath, 6);
                    imagepng($dstImg, $destPath, 6);
                } elseif ($ext === 'webp' && function_exists('imagewebp')) {
                    imagewebp($dstImg, $srcPath, 85);
                    imagewebp($dstImg, $destPath, 85);
                }
                imagedestroy($dstImg);
            }
            imagedestroy($srcImg);
        }
    }
    // ==== HALAMAN STATISTIK PENGGUNA ====
    public function statistikPengguna() {
        require_once __DIR__ . '/../models/StatistikPenggunaModel.php';
        $statModel = new StatistikPenggunaModel();
        
        $dataStatistik = $statModel->getStatistikSemuaPengguna();
        
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/statistik-pengguna.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }
}
