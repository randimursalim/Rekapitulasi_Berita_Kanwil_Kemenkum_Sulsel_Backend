</div> <!-- end dash-content -->
</section> <!-- end dashboard -->

<?php
// Auto-detect BASE_URL jika belum tersedia (jika footer.php dipanggil tanpa header.php)
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

<script src="<?= $BASE ?>/js/script.js"></script>
<?php if (isset($_GET['page']) && $_GET['page'] === 'rekap-konten'): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="<?= $BASE ?>/js/rekap.js"></script>
<?php endif; ?>
<?php if (isset($_GET['page']) && $_GET['page'] === 'rekap-harmonisasi'): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="<?= $BASE ?>/js/rekap-harmonisasi.js"></script>
<?php endif; ?>
<?php if (isset($_GET['page']) && $_GET['page'] === 'jadwal-peminjaman-ruangan'): ?>
<script src="<?= $BASE ?>/js/jadwal-peminjaman-ruangan.js"></script>
<?php endif; ?>
<?php if (isset($_GET['page']) && $_GET['page'] === 'tambah-peminjaman-ruangan'): ?>
<script src="<?= $BASE ?>/js/tambah-peminjaman-ruangan.js"></script>
<?php endif; ?>
<?php if (isset($_GET['page']) && $_GET['page'] === 'edit-peminjaman-ruangan'): ?>
<script src="<?= $BASE ?>/js/edit-peminjaman-ruangan.js"></script>
<?php endif; ?>
<?php if (isset($_GET['page']) && $_GET['page'] === 'harmonisasi'): ?>
<script src="<?= $BASE ?>/js/daftar-harmonisasi.js?v=1.0.2"></script>
<?php endif; ?>
<?php if (isset($_GET['page']) && $_GET['page'] === 'edit-konten'): ?>
<script src="<?= $BASE ?>/js/edit-konten.js?v=1.0.4" onerror="console.warn('edit-konten.js not found, using inline script')"></script>
<script>
// Backup script untuk edit-konten jika file eksternal tidak ter-load
(function() {
    if (window.editKontenFormInitialized) return;
    
    function initEditKontenForm() {
        if (window.editKontenFormInitialized) return;
        window.editKontenFormInitialized = true;
        
        const jenisSelect = document.getElementById('jenis');
        const formBerita = document.getElementById('form-berita');
        const formMedsos = document.getElementById('form-medsos');
        const editForm = document.getElementById('editKontenForm');

        if (!jenisSelect) {
            setTimeout(initEditKontenForm, 100);
            return;
        }

        function toggleForm() {
            const value = jenisSelect.value;
            if (value === 'berita') {
                if (formBerita) {
                    formBerita.style.display = 'block';
                }
                if (formMedsos) formMedsos.style.display = 'none';
            } else if (['instagram','youtube','tiktok','twitter','facebook'].includes(value)) {
                if (formBerita) formBerita.style.display = 'none';
                if (formMedsos) {
                    formMedsos.style.display = 'block';
                }
            } else {
                if (formBerita) formBerita.style.display = 'none';
                if (formMedsos) formMedsos.style.display = 'none';
            }
        }

        toggleForm();
        setTimeout(toggleForm, 50);
        jenisSelect.addEventListener('change', toggleForm);

        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Update Konten?',
                        text: "Apakah kamu yakin untuk mengupdate konten ini?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, update!',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if(result.isConfirmed) this.submit();
                    });
                } else {
                    if (confirm('Apakah kamu yakin untuk mengupdate konten ini?')) {
                        this.submit();
                    }
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditKontenForm);
    } else {
        initEditKontenForm();
        setTimeout(initEditKontenForm, 10);
    }
    window.addEventListener('load', function() {
        setTimeout(initEditKontenForm, 100);
    });
})();
</script>
<?php endif; ?>
<script src="<?= $BASE ?>/js/modal-dashboard.js"></script>
<script src="<?= $BASE ?>/js/filter-activity.js"></script>
<script src="<?= $BASE ?>/js/form-kegiatan.js"></script>

<script>
// Data dari PHP - Pass to global scope
window.detailBerita = <?= json_encode($detailBerita ?? []) ?>;
window.detailMedsos = <?= json_encode($detailMedsos ?? []) ?>;
</script>

</body>
</html>
