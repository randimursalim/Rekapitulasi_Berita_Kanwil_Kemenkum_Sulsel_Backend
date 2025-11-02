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
<script src="<?= $BASE ?>/js/rekap.js"></script>
<script src="<?= $BASE ?>/js/modal-dashboard.js"></script>
<script src="<?= $BASE ?>/js/filter-activity.js"></script>
<script src="<?= $BASE ?>/js/form-kegiatan.js"></script>

<script>
// Data dari PHP - Pass to global scope
window.detailBerita = <?= json_encode($detailBerita ?? []) ?>;
window.detailMedsos = <?= json_encode($detailMedsos ?? []) ?>;
</script>
