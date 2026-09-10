<?php
// app/views/pages/edit-role.php
if (!isset($BASE)) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    
    $isLocalhost = (
        strpos($serverName, 'localhost') !== false ||
        strpos($serverName, '127.0.0.1') !== false ||
        strpos($httpHost, 'localhost') !== false ||
        strpos($requestUri, '/rekap-konten/public') !== false ||
        strpos($scriptName, '/rekap-konten/public') !== false
    );
    
    $BASE = $isLocalhost ? 
        (defined('BASE_URL') ? BASE_URL : '/rekap-konten/public') : 
        '';
}
?>

<link rel="stylesheet" href="<?= $BASE ?>/css/role.css?v=<?= time() ?>">

<div class="overview">
    <div class="title">
        <i class="fas fa-edit"></i>
        <span class="text">Edit Role & Hak Akses</span>
    </div>

    <div class="role-container">
        <form id="formEditRole" action="index.php?page=update-role" method="POST" autocomplete="off">
            <input type="hidden" name="id_role" value="<?= $role['id_role'] ?>">

            <div class="role-form-group">
                <label for="nama_role">Nama Role / Divisi</label>
                <input type="text" id="nama_role" name="nama_role" class="role-form-control" value="<?= htmlspecialchars($role['nama_role']) ?>" placeholder="Contoh: Staf Humas, Subbag Umum, Perancang" required <?= ($role['nama_role'] === 'Admin') ? 'readonly' : '' ?>>
                <?php if ($role['nama_role'] === 'Admin'): ?>
                    <small style="color: #64748b; margin-top: 4px; display: block;">* Nama role Admin tidak dapat diubah.</small>
                <?php endif; ?>
            </div>

            <div class="role-form-group" style="margin-bottom: 25px;">
                <label for="deskripsi">Deskripsi / Keterangan</label>
                <input type="text" id="deskripsi" name="deskripsi" class="role-form-control" value="<?= htmlspecialchars($role['deskripsi'] ?? '') ?>" placeholder="Deskripsi singkat mengenai peran atau tugas role ini">
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color, #eee); margin: 25px 0;">

            <div class="permissions-header">
                <h3 class="permissions-title">
                    <i class="fas fa-list-check" style="color: #0E4BF1;"></i> Pilih Hak Akses Menu
                </h3>
                <div>
                    <button type="button" class="btn-perm-toggle" onclick="toggleAllPermissions(true)" style="margin-right: 5px;">Pilih Semua</button>
                    <button type="button" class="btn-perm-toggle" onclick="toggleAllPermissions(false)">Hapus Semua</button>
                </div>
            </div>

            <div class="permissions-grid">
                <?php if (!empty($availableMenus)): ?>
                    <?php foreach ($availableMenus as $kategori => $menus): ?>
                        <div class="permission-card">
                            <h4>
                                <?= htmlspecialchars($kategori) ?>
                            </h4>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php foreach ($menus as $menuKey => $menuLabel): ?>
                                    <?php $isPermissionChecked = in_array($menuKey, $rolePermissions); ?>
                                    <label class="permission-item">
                                        <input type="checkbox" name="permissions[]" value="<?= $menuKey ?>" class="perm-checkbox" <?= $isPermissionChecked ? 'checked' : '' ?>>
                                        <span><?= htmlspecialchars($menuLabel) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div style="text-align:center; margin-top:30px;">
                <button type="submit" class="btn-simpan" style="padding: 10px 24px; background: #0E4BF1; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 10px;">
                    <i class="fas fa-save"></i> Perbarui Role
                </button>
                <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=role'" style="padding: 10px 24px; background: #64748b; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAllPermissions(checked) {
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = checked);
}

document.getElementById('formEditRole').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('index.php?page=update-role', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Berhasil!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#0E4BF1'
            }).then(() => {
                window.location.href = 'index.php?page=role';
            });
        } else {
            Swal.fire('Gagal!', data.message, 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
    });
});
</script>
