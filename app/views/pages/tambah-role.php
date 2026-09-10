<?php
// app/views/pages/tambah-role.php
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
        <i class="fas fa-shield-alt"></i>
        <span class="text">Tambah Role & Hak Akses Baru</span>
    </div>

    <div class="role-container">
        <form id="formTambahRole" action="index.php?page=store-role" method="POST" autocomplete="off">
            <div class="role-form-group">
                <label for="nama_role">Nama Role / Divisi</label>
                <input type="text" id="nama_role" name="nama_role" class="role-form-control" placeholder="Contoh: Staf Humas, Subbag Umum, Perancang" required>
            </div>

            <div class="role-form-group" style="margin-bottom: 25px;">
                <label for="deskripsi">Deskripsi / Keterangan</label>
                <input type="text" id="deskripsi" name="deskripsi" class="role-form-control" placeholder="Deskripsi singkat mengenai peran atau tugas role ini">
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
                                    <label class="permission-item">
                                        <input type="checkbox" name="permissions[]" value="<?= $menuKey ?>" class="perm-checkbox">
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
                    <i class="fas fa-save"></i> Simpan Role
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

document.getElementById('formTambahRole').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('index.php?page=store-role', {
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
