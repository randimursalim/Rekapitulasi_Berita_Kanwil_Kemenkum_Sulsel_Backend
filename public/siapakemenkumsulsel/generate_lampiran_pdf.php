<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get parameters
$tahun = $_GET['tahun'] ?? '';
$nip = $_GET['nip'] ?? '';

if (empty($tahun) || empty($nip)) {
    die("Parameter tahun dan nip harus diisi");
}

// Get lampiran data
$sql = "SELECT * FROM skp_lampiran WHERE nip = ? AND tahun = ? ORDER BY kategori_lampiran, id_lampiran";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $nip, $tahun);
$stmt->execute();
$result = $stmt->get_result();

$lampiran_data = [];
$dukungan_data = [];
$skema_data = [];
$konsekuensi_data = [];

while ($row = $result->fetch_assoc()) {
    $lampiran_data[] = $row;
    switch ($row['kategori_lampiran']) {
        case 'DUKUNGAN SUMBER DAYA':
            $dukungan_data[] = $row;
            break;
        case 'SKEMA PERTANGGUNGJAWABAN':
            $skema_data[] = $row;
            break;
        case 'KONSEKUENSI':
            $konsekuensi_data[] = $row;
            break;
    }
}

$stmt->close();
$conn->close();

if (empty($lampiran_data)) {
    die("Data lampiran tidak ditemukan");
}

$first_record = $lampiran_data[0];
$nama_pegawai = $first_record['nama'];
$nip_pegawai = $first_record['nip'];
$nama_atasan = $first_record['nama_atasan_langsung'];
$nip_atasan = $first_record['nip_atasan_langsung'];

// Get evaluation date from database
// Helper function to convert English month names to Indonesian
function formatTanggalIndonesia($date) {
    $bulan = [
        'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
        'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
        'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
        'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
    ];
    $date_formatted = $date->format('d F Y');
    foreach ($bulan as $en => $id) {
        $date_formatted = str_replace($en, $id, $date_formatted);
    }
    return $date_formatted;
}

$tanggal_evaluasi = $first_record['tanggal_evaluasi_lampiran'] ?? null;
if ($tanggal_evaluasi) {
    // Format date from database (assuming it's in YYYY-MM-DD or datetime format)
    $tanggal_evaluasi_obj = new DateTime($tanggal_evaluasi);
    $tanggal_evaluasi_formatted = formatTanggalIndonesia($tanggal_evaluasi_obj);
} else {
    // Fallback to current date if not set
    $tanggal_evaluasi_formatted = formatTanggalIndonesia(new DateTime());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lampiran Sasaran Kinerja Pegawai - <?= htmlspecialchars($nama_pegawai) ?> - <?= htmlspecialchars($tahun) ?></title>
    <link rel="icon" type="image/png" href="images/SIAPA.png">
    <style>
        body { font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-size: 11px; background: #fff; margin: 0; padding: 0; }
        table { border-collapse: collapse; width: 100%; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; }
        th, td { border: 1px solid #003366; padding: 6px 8px; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .feedback-cell { font-family: 'Wingdings', 'Bookman Old Style', sans-serif; }
        .header-title { text-align: center; font-size: 15px; padding: 2px 0; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .header-sub { text-align: center; font-size: 12px; padding: 0; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .blue-bg { background: #b7d6f6 !important; color: #000 !important; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .section-row { background: #b7d6f6 !important; color: #000 !important; text-align: left; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .subsection-row { background: #eaf3fb; color: #003366; text-align: left; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .center { text-align: center; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .no-border { border: none !important; background: #fff !important; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .no-bottom-border { border-bottom: none !important; }
        .no-top-border { border-top: none !important; }
        .behavior-title { font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .behavior-desc { font-size: 10px; padding-left: 10px; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .behavior-table th, .behavior-table td { border: 1px solid #003366; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .behavior-table th { background: #b7d6f6 !important; color: #000 !important; text-align: center; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .behavior-table td.blue-bg { background: #b7d6f6 !important; color: #000 !important; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .behavior-table td { background: #fff; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .small { font-size: 10px; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        table tr:last-child td, table tr:last-child th { border-bottom: none !important; }
        @media print {
            table, th, td {
                border: 1px solid #003366 !important;
                border-collapse: separate !important;
                border-spacing: 0 !important;
                font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif !important;
                font-weight: normal !important;
            }
            tr {
                page-break-inside: avoid;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-bottom-border { border-bottom: none !important; }
            table tr:last-child td, table tr:last-child th { border-bottom: none !important; }
        }
        @media print {
            .no-print { display: none !important; }
        }
        @media print {
            .signature-section {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
            🖨️ Print / Save as PDF
        </button>
    </div>
    
    <div class="header-title">LAMPIRAN SASARAN KINERJA PEGAWAI</div>
    <br>
    <div style="display: flex; justify-content: space-between; align-items: center; margin: 10px 0;">
        <div style="width: 50%;">KEMENTERIAN HUKUM</div>
        <div style="width: 50%; text-align: right;">PERIODE PENILAIAN: 1 JANUARI SD 31 DESEMBER TAHUN <?= htmlspecialchars($tahun) ?></div>
    </div>
    
    <table>
        <tr><td colspan="2" class="section-row">DUKUNGAN SUMBER DAYA</td></tr>
        <?php 
        $dukungan_count = count($dukungan_data);
        for ($i = 1; $i <= $dukungan_count; $i++): 
            $content = htmlspecialchars($dukungan_data[$i-1]['isi_lampiran']);
        ?>
        <tr>
            <td class="blue-bg center" style="width: 40px;"><?= $i ?></td>
            <td><?= $content ?></td>
        </tr>
        <?php endfor; ?>
    </table>
    <br>
    
    <table>
        <tr><td colspan="2" class="section-row">SKEMA PERTANGGUNGJAWABAN</td></tr>
        <?php 
        $skema_count = count($skema_data);
        for ($i = 1; $i <= $skema_count; $i++): 
            $content = htmlspecialchars($skema_data[$i-1]['isi_lampiran']);
        ?>
        <tr>
            <td class="blue-bg center" style="width: 40px;"><?= $i ?></td>
            <td><?= $content ?></td>
        </tr>
        <?php endfor; ?>
    </table>
    <br>
    
    <table>
        <tr><td colspan="2" class="section-row">KONSEKUENSI</td></tr>
        <?php 
        $konsekuensi_count = count($konsekuensi_data);
        for ($i = 1; $i <= $konsekuensi_count; $i++): 
            $content = htmlspecialchars($konsekuensi_data[$i-1]['isi_lampiran']);
        ?>
        <tr>
            <td class="blue-bg center" style="width: 40px;"><?= $i ?></td>
            <td><?= $content ?></td>
        </tr>
        <?php endfor; ?>
    </table>
    
    <div class="signature-section" style="width:100%; display:flex; justify-content:space-between; align-items:center; margin-top:40px; font-size:12px;">
        <div style="width:48%; text-align:center;">
            PNS yang dinilai,<br><br><br><br>
            <?= htmlspecialchars($nama_pegawai) ?><br>
            NIP <?= htmlspecialchars($nip_pegawai) ?>
        </div>
        <div style="width:48%; text-align:center;">
            Makassar, <?= $tanggal_evaluasi_formatted ?><br>
            Pejabat Penilai,<br><br><br><br>
            <?= htmlspecialchars($nama_atasan) ?><br>
            NIP <?= htmlspecialchars($nip_atasan) ?>
        </div>
    </div>
    
    <script>
    window.onload = function() {
        setTimeout(function() {
            window.print();
        }, 500);
    };
    </script>
</body>
</html>
