<?php
// app/views/pages/role.php
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
        <i class="fas fa-user-shield"></i>
        <span class="text">Manajemen Role & Hak Akses</span>
    </div>

    <!-- Tombol Tambah Role -->
    <div class="btn-container" style="margin: 15px 0;">
        <button class="btn-tambah" onclick="window.location.href='index.php?page=tambah-role'">
            <i class="fas fa-plus"></i> Tambah Role Baru
        </button>
    </div>

    <!-- Data Role Table -->
    <div class="activity-wrapper" style="margin-top:20px;">
        <div class="activity">
            <div class="activity-data">
                <div class="role-table-wrapper">
                    <table class="role-table">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">No</th>
                                <th style="width: 200px;">Nama Role</th>
                                <th>Deskripsi</th>
                                <th style="width: 150px; text-align: center;">Jumlah Pengguna</th>
                                <th style="width: 150px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($roles)): ?>
                                <?php foreach ($roles as $index => $r): ?>
                                    <tr>
                                        <td style="text-align: center;"><?= $index + 1 ?></td>
                                        <td style="font-weight: 600;">
                                            <span class="badge-role-tag">
                                                <i class="fas fa-user-tag"></i><?= htmlspecialchars($r['nama_role']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($r['deskripsi'] ?? '-') ?></td>
                                        <td style="text-align: center;">
                                            <span class="badge-user-count">
                                                <?= $r['total_pengguna'] ?> Pengguna
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <button title="Edit Role" class="btn-edit" onclick="window.location.href='index.php?page=edit-role&id=<?= $r['id_role'] ?>'" style="margin-right: 5px; padding: 6px 12px; background: #f59e0b; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if (!in_array($r['nama_role'], ['Admin'])): ?>
                                                <button title="Hapus Role" class="btn-hapus" onclick="konfirmasiHapusRole(<?= $r['id_role'] ?>, '<?= htmlspecialchars($r['nama_role'], ENT_QUOTES) ?>')" style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px;">Belum ada data role.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function konfirmasiHapusRole(id, namaRole) {
    Swal.fire({
        title: 'Hapus Role?',
        text: `Apakah Anda yakin ingin menghapus role "${namaRole}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmColor: '#d33',
        cancelColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('index.php?page=hapus-role', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(id)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Terhapus!', data.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error!', 'Terjadi kesalahan pada server.', 'error');
            });
        }
    });
}
</script>
