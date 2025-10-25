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
        if (!in_array($role, ['Admin', 'Operator'])) $errors[] = 'Role tidak valid';

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
            $fileExtension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fileName = 'user_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
                $foto = $fileName;
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
            header('Location: index.php?page=pengguna');
            exit;
        }

        $pengguna = $this->model->getPenggunaById($id);
        if (!$pengguna) {
            $_SESSION['errors'] = ['Pengguna tidak ditemukan'];
            header('Location: index.php?page=pengguna');
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
        if (!in_array($role, ['Admin', 'Operator'])) $errors[] = 'Role tidak valid';
        
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

        // Handle foto upload
        $foto = 'user.jpg'; // Default foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/Images/users/';
            $fileExtension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fileName = 'user_' . $id . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
                $foto = $fileName;
            }
        } else {
            // Jika tidak ada foto baru, ambil foto lama dari database
            $penggunaLama = $this->model->getPenggunaById($id);
            $foto = $penggunaLama['foto'] ?? 'user.jpg';
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
        // Suppress semua output sebelum JSON
        ob_clean();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        // Ambil ID user yang sedang login
        $id = $_SESSION['user']['id'] ?? null;
        if (!$id) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User tidak valid']);
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
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }

        // Handle foto upload dengan security
        $foto = $_SESSION['user']['foto'] ?? 'user.jpg'; // Default foto dari session
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            require_once __DIR__ . '/../helpers/SecureFileUpload.php';
            $uploadHandler = new SecureFileUpload();
            
            $uploadResult = $uploadHandler->uploadFile('foto', 'user');
            
            if ($uploadResult['success']) {
                // Hapus foto lama jika bukan foto default
                $fotoLama = $_SESSION['user']['foto'] ?? 'user.jpg';
                if ($fotoLama !== 'user.jpg') {
                    $uploadHandler->deleteFile($fotoLama);
                }
                $foto = $uploadResult['filename'];
            } else {
                // Handle upload error
                ob_clean();
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
            'role' => $_SESSION['user']['role'] // Pertahankan role yang sudah ada
        ];

        // Jika password diisi, update password
        if (!empty($password)) {
            $data['password'] = $password;
        }

        if ($this->model->updatePengguna($id, $data)) {
            // Update session dengan data baru (pertahankan role)
            $_SESSION['user']['nama'] = $nama;
            $_SESSION['user']['username'] = $username;
            $_SESSION['user']['foto'] = $foto;
            // Role tidak perlu diupdate karena sudah ada di session
            
            // Clear output buffer dan set header untuk JSON response
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Profil berhasil diperbarui']);
        } else {
            // Clear output buffer dan set header untuk JSON response
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui profil']);
        }
        exit;
    }
}
