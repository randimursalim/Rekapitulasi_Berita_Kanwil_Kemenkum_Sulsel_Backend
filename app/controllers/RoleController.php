<?php
// app/controllers/RoleController.php
require_once __DIR__ . '/../models/RoleModel.php';

class RoleController {
    private $model;

    public function __construct() {
        $this->model = new RoleModel();
    }

    // Halaman daftar role (role.php)
    public function daftarRole() {
        $roles = $this->model->getAllRoles();
        
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/role.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman tambah role (tambah-role.php)
    public function tambahRole() {
        $availableMenus = $this->model->getAvailableMenus();

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/tambah-role.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses tambah role
    public function storeRole() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $namaRole = trim($_POST['nama_role'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $permissions = $_POST['permissions'] ?? [];

        // Validasi
        $errors = [];
        if (empty($namaRole)) {
            $errors[] = 'Nama role harus diisi';
        } elseif ($this->model->isRoleNameExists($namaRole)) {
            $errors[] = 'Nama role sudah digunakan';
        }

        if (!empty($errors)) {
            if (ob_get_level() > 0) @ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }

        $result = $this->model->tambahRole($namaRole, $deskripsi, $permissions);
        if (ob_get_level() > 0) @ob_clean();
        header('Content-Type: application/json');
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Role berhasil ditambahkan']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan role']);
        }
        exit;
    }

    // Halaman edit role (edit-role.php)
    public function editRole() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/index.php?page=role');
            exit;
        }

        $role = $this->model->getRoleById($id);
        if (!$role) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/index.php?page=role');
            exit;
        }

        $rolePermissions = $this->model->getPermissionsByRoleId($id);
        $availableMenus = $this->model->getAvailableMenus();

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-role.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses update role
    public function updateRole() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $idRole = $_POST['id_role'] ?? null;
        $namaRole = trim($_POST['nama_role'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $permissions = $_POST['permissions'] ?? [];

        if (!$idRole) {
            echo json_encode(['success' => false, 'message' => 'ID Role tidak valid']);
            exit;
        }

        // Validasi
        $errors = [];
        if (empty($namaRole)) {
            $errors[] = 'Nama role harus diisi';
        } elseif ($this->model->isRoleNameExists($namaRole, $idRole)) {
            $errors[] = 'Nama role sudah digunakan';
        }

        if (!empty($errors)) {
            if (ob_get_level() > 0) @ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }

        $result = $this->model->updateRole($idRole, $namaRole, $deskripsi, $permissions);
        if (ob_get_level() > 0) @ob_clean();
        header('Content-Type: application/json');

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Role berhasil diperbarui']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui role']);
        }
        exit;
    }

    // Proses hapus role
    public function hapusRole() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $idRole = $_POST['id'] ?? null;
        if (!$idRole) {
            echo json_encode(['success' => false, 'message' => 'ID Role tidak valid']);
            exit;
        }

        $res = $this->model->hapusRole($idRole);
        if (ob_get_level() > 0) @ob_clean();
        header('Content-Type: application/json');
        echo json_encode($res);
        exit;
    }
}
