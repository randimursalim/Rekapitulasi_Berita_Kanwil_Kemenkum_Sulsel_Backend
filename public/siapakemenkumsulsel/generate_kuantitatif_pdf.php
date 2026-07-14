<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Get id_skp_global from URL
$id_skp_global = $_GET['id_skp_global'] ?? '';

if (empty($id_skp_global)) {
    die('ID SKP Global tidak ditemukan');
}

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

function render_feedback_with_thumb($text) {
    $thumb_base64 = 'data:image/png;base64,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';
    return str_replace('[THUMB]', '<img src="' . $thumb_base64 . '" alt="Thumbs Up" style="height:60px;vertical-align:middle;filter:brightness(0);">', $text);
}

// Fetch SKP data - check skp_akhir_pegawai first (annual SKP)
$is_annual = false;
$skp_data = [];

// Check annual SKP table first
$annual_sql = "SELECT * FROM skp_akhir_pegawai WHERE ID_SKP_GLOBAL = ? ORDER BY ID_SKP ASC";
$annual_stmt = $conn->prepare($annual_sql);
$annual_stmt->bind_param('i', $id_skp_global);
$annual_stmt->execute();
$annual_result = $annual_stmt->get_result();

if ($annual_result && $annual_result->num_rows > 0) {
    // Data found in annual table
    while ($row = $annual_result->fetch_assoc()) {
        $skp_data[] = $row;
    }
    $is_annual = true;
}
$annual_stmt->close();

// If no annual data found, check quarterly SKP table
if (empty($skp_data)) {
    $skp_sql = "SELECT * FROM skp_pegawai WHERE id_skp_global = ? ORDER BY TANGGAL_INPUT_SKP ASC";
    $stmt = $conn->prepare($skp_sql);
    $stmt->bind_param('i', $id_skp_global);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $skp_data[] = $row;
        }
    }
    $stmt->close();
}

// Fetch Pegawai (dinilai) detail
$pegawai_detail = null;
// Data is now loaded directly from SKP tables (first_row)


// Fetch Penilai (atasan) detail
$penilai_detail = null;
if (!empty($skp_data[0]['NIP_ATASAN_LANGSUNG'])) {
    $nip_atasan = $skp_data[0]['NIP_ATASAN_LANGSUNG'];
    $penilai_sql = "SELECT * FROM Pegawai WHERE NIP = ? LIMIT 1";
    $stmt_penilai = $conn->prepare($penilai_sql);
    $stmt_penilai->bind_param('s', $nip_atasan);
    $stmt_penilai->execute();
    $result_penilai = $stmt_penilai->get_result();
    if ($result_penilai && $result_penilai->num_rows > 0) {
        $penilai_detail = $result_penilai->fetch_assoc();
    }
    $stmt_penilai->close();
}

// Fetch Perilaku Kerja data
if ($is_annual) {
    $perilaku_sql = "SELECT * FROM skp_akhir_perilaku_pegawai WHERE ID_SKP_GLOBAL = ?";
} else {
    $perilaku_sql = "SELECT * FROM skp_perilaku_pegawai WHERE id_skp_global = ?";
}
$perilaku_stmt = $conn->prepare($perilaku_sql);
$perilaku_stmt->bind_param('i', $id_skp_global);
$perilaku_stmt->execute();
$perilaku_result = $perilaku_stmt->get_result();
$perilaku_data = $perilaku_result ? $perilaku_result->fetch_assoc() : null;

$perilaku_stmt->close();
$conn->close();

if (empty($skp_data)) {
    die('Data SKP tidak ditemukan');
}
$first_row = $skp_data[0];

// Separate Kinerja Utama and Kinerja Tambahan using JENIS_KINERJA field
// Exclude activities where TARGET = 0 and REALISASI = 0 (not performed)
$kinerja_utama = [];
$kinerja_tambahan = [];
foreach ($skp_data as $row) {
    // Check if activity was not performed
    $target = trim($row['TARGET'] ?? '');
    $realisasi = trim($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? '');
    $is_not_performed = ($target === '0' && $realisasi === '0');
    
    // Skip activities that were not performed
    if ($is_not_performed) {
        continue;
    }
    
    if (isset($row['JENIS_KINERJA'])) {
        if ($row['JENIS_KINERJA'] === 'kinerja utama') {
            $kinerja_utama[] = $row;
        } elseif ($row['JENIS_KINERJA'] === 'kinerja tambahan') {
            $kinerja_tambahan[] = $row;
        }
    }
}

$triwulan = $is_annual ? null : ($first_row['TRIWULAN'] ?? 1);
$tahun = $first_row['TAHUN'] ?? date('Y');
$periode_awal = '';
$periode_akhir = '';
$bulan_map = [1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL', 5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS', 9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'];
switch ((int)($triwulan ?? 0)) {
    case 1:
        $periode_awal = '01 JANUARI';
        $periode_akhir = '31 MARET';
        break;
    case 2:
        $periode_awal = '01 APRIL';
        $periode_akhir = '30 JUNI';
        break;
    case 3:
        $periode_awal = '01 JULI';
        $periode_akhir = '30 SEPTEMBER';
        break;
    case 4:
        $periode_awal = '01 OKTOBER';
        $periode_akhir = '31 DESEMBER';
        break;
    default:
        $periode_awal = '01 JANUARI';
        $periode_akhir = '31 DESEMBER';
}
$periode_penilaian = "PERIODE PENILAIAN: $periode_awal SD $periode_akhir TAHUN $tahun";
// Set period display text
$periode_display = $is_annual ? 'AKHIR' : 'TRIWULAN ' . ($triwulan ?? 1);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title></title>
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
                page-break-inside: auto;
                page-break-after: auto;
                orphans: 1;
                widows: 1;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            td, th {
                page-break-inside: avoid;
            }
            td[rowspan], th[rowspan] {
                page-break-inside: auto !important;
                page-break-before: auto !important;
                page-break-after: auto !important;
            }
            td[rowspan="1"], th[rowspan="1"] {
                page-break-inside: avoid;
            }
            table {
                page-break-inside: auto;
            }
            tbody {
                page-break-inside: auto;
            }
            /* Ensure rowspan cells don't create orphaned cells */
            tr:empty {
                display: none;
            }
            /* Prevent duplicate rendering of rowspan cells across page breaks */
            tbody tr {
                break-inside: auto;
            }
            /* Ensure table structure is maintained properly */
            table tbody tr:last-child {
                page-break-after: auto;
            }
            /* Prevent extra cells from appearing after rowspan breaks */
            tbody tr:last-child td:only-child:not([rowspan]) {
                display: none !important;
            }
            /* Ensure proper cell count in each row */
            tbody tr td {
                min-width: 0;
            }
            /* Force page breaks for large rowspan groups */
            td[rowspan]:not([rowspan="1"]):not([rowspan="2"]):not([rowspan="3"]):not([rowspan="4"]):not([rowspan="5"]) {
                page-break-inside: auto !important;
            }
            .no-bottom-border { border-bottom: none !important; }
            table tr:last-child td, table tr:last-child th { border-bottom: none !important; }
        }
    </style>
</head>
<body>
     <div class="header-title">SASARAN KINERJA</div>
    <div class="header-title">PENDEKATAN HASIL KERJA KUANTITATIF</div>
    <div class="header-title">BAGI PEJABAT ADMINISTRASI DAN PEJABAT FUNGSIONAL</div>
    <div class="header-title">PERIODE: <?= htmlspecialchars($periode_display) ?><?= $is_annual ? '' : ' TAHUN ' . htmlspecialchars($tahun) ?></div>
    <br>
    <div style="display: flex; justify-content: space-between; align-items: center; margin: 10px 0;">
        <div style="width: 50%;">KEMENTERIAN HUKUM</div>
        <div style="width: 50%; text-align: right;">PERIODE PENILAIAN: <?= htmlspecialchars($periode_penilaian ?? '') ?></div>
    </div>
    <table>
        <tr>
            <th colspan="5" class="blue-bg center">PEGAWAI YANG DINILAI</th>
            <th colspan="5" class="blue-bg center">PEJABAT PENILAI KINERJA</th>
        </tr>
        <tr>
            <td class="blue-bg">NAMA</td>
            <td colspan="4"><?= htmlspecialchars($first_row['NAMA'] ?? '') ?></td>
            <td class="blue-bg">NAMA</td>
            <td colspan="4"><?= htmlspecialchars($first_row['NAMA_ATASAN_LANGSUNG'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="blue-bg">NIP</td>
            <td colspan="4"><?= htmlspecialchars($first_row['NIP'] ?? '') ?></td>
            <td class="blue-bg">NIP</td>
            <td colspan="4"><?= htmlspecialchars($first_row['NIP_ATASAN_LANGSUNG'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="blue-bg">PANGKAT/GOL RUANG</td>
            <td colspan="4"><?= htmlspecialchars($pegawai_detail['PANGKAT_GOL_RUANG'] ?? $first_row['PANGKAT_GOL_RUANG'] ?? '') ?></td>
            <td class="blue-bg">PANGKAT/GOL RUANG</td>
            <td colspan="4"><?= htmlspecialchars($penilai_detail['PANGKAT_GOL_RUANG'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="blue-bg">JABATAN</td>
            <td colspan="4"><?= htmlspecialchars($pegawai_detail['JABATAN'] ?? $first_row['JABATAN'] ?? '') ?></td>
            <td class="blue-bg">JABATAN</td>
            <td colspan="4"><?= htmlspecialchars($penilai_detail['JABATAN'] ?? '') ?></td>
            </tr>
            <tr>
            <td class="blue-bg">UNIT KERJA</td>
            <td colspan="4"><?= htmlspecialchars($pegawai_detail['UNIT_KERJA'] ?? $first_row['UNIT_KERJA'] ?? '') ?></td>
            <td class="blue-bg">UNIT KERJA</td>
            <td colspan="4"><?= htmlspecialchars($penilai_detail['UNIT_KERJA'] ?? '') ?></td>
            </tr>
        </table>
    <br>
    <table>
    <!-- Remove <thead> and put all header rows in <tbody> -->
    <tbody>
        <tr><th colspan="6" class="blue-bg" style="text-align:left;">HASIL KERJA</th></tr>
        <tr>
            <th class="blue-bg center">NO</th>
            <th class="blue-bg center">RENCANA HASIL KERJA PIMPINAN YANG DIINTERVENSI</th>
            <th class="blue-bg center">RENCANA HASIL KERJA</th>
            <th class="blue-bg center">ASPEK</th>
            <th class="blue-bg center">INDIKATOR KINERJA INDIVIDU</th>
            <th class="blue-bg center">TARGET</th>
        </tr>
        <tr>
            <td class="blue-bg center small">(1)</td>
            <td class="blue-bg center small">(2)</td>
            <td class="blue-bg center small">(3)</td>
            <td class="blue-bg center small"></td>
            <td class="blue-bg center small"></td>
            <td class="blue-bg center small">(4)</td>
        </tr>
        <tr><td colspan="6" class="section-row">A. KINERJA UTAMA</td></tr>
        <?php if (!empty($kinerja_utama)): ?>
            <?php 
            // Group rows by RHK_PIMPINAN_INTERV and RENCANA_HASIL_KERJA
            $grouped_utama = [];
            foreach ($kinerja_utama as $row) {
                $key = ($row['RHK_PIMPINAN_INTERV'] ?? '') . '|' . ($row['RENCANA_HASIL_KERJA'] ?? '');
                if (!isset($grouped_utama[$key])) {
                    $grouped_utama[$key] = [];
                }
                $grouped_utama[$key][] = $row;
            }
            
            $row_number = 1;
            foreach ($grouped_utama as $group_key => $group_rows): 
                $first_row = $group_rows[0];
                $row_count = count($group_rows);
            ?>
            <tr>
                <td class="center <?= $row_count > 10 ? 'large-rowspan' : '' ?>" rowspan="<?= $row_count ?>"> <?= $row_number ?> </td>
                <td class="<?= $row_count > 10 ? 'large-rowspan' : '' ?>" rowspan="<?= $row_count ?>"><?= nl2br(htmlspecialchars($first_row['RHK_PIMPINAN_INTERV'] ?? '')) ?></td>
                <td class="<?= $row_count > 10 ? 'large-rowspan' : '' ?>" rowspan="<?= $row_count ?>"><?= nl2br(htmlspecialchars($first_row['RENCANA_HASIL_KERJA'] ?? '')) ?></td>
                <td><?= htmlspecialchars($first_row['ASPEK'] ?? '') ?></td>
                <td><?= nl2br(htmlspecialchars($first_row['INDIKATOR_KINERJA_INDIVIDU'] ?? '')) ?></td>
                <td><?= htmlspecialchars(($first_row['TARGET'] ?? '') . (!empty($first_row['SATUAN']) ? ' ' . $first_row['SATUAN'] : '')) ?></td>
            </tr>
            <?php 
            // Add additional rows for the same group
            for ($i = 1; $i < $row_count; $i++): 
                $current_row = $group_rows[$i];
            ?>
            <tr>
                <td><?= htmlspecialchars($current_row['ASPEK'] ?? '') ?></td>
                <td><?= nl2br(htmlspecialchars($current_row['INDIKATOR_KINERJA_INDIVIDU'] ?? '')) ?></td>
                <td><?= htmlspecialchars(($current_row['TARGET'] ?? '') . (!empty($current_row['SATUAN']) ? ' ' . $current_row['SATUAN'] : '')) ?></td>
            </tr>
            <?php 
            endfor;
            $row_number++;
            endforeach; 
            ?>
        <?php else: ?>
            <tr><td colspan="6" class="center">-</td></tr>
        <?php endif; ?>
        <tr><td colspan="6" class="section-row">B. KINERJA TAMBAHAN</td></tr>
        <?php if (!empty($kinerja_tambahan)): ?>
            <?php 
            // Group rows by RHK_PIMPINAN_INTERV and RENCANA_HASIL_KERJA
            $grouped_tambahan = [];
            foreach ($kinerja_tambahan as $row) {
                $key = ($row['RHK_PIMPINAN_INTERV'] ?? '') . '|' . ($row['RENCANA_HASIL_KERJA'] ?? '');
                if (!isset($grouped_tambahan[$key])) {
                    $grouped_tambahan[$key] = [];
                }
                $grouped_tambahan[$key][] = $row;
            }
            
            $row_number = 1;
            foreach ($grouped_tambahan as $group_key => $group_rows): 
                $first_row = $group_rows[0];
                $row_count = count($group_rows);
            ?>
            <tr>
                <td class="center <?= $row_count > 10 ? 'large-rowspan' : '' ?>" rowspan="<?= $row_count ?>"> <?= $row_number ?> </td>
                <td class="<?= $row_count > 10 ? 'large-rowspan' : '' ?>" rowspan="<?= $row_count ?>"><?= nl2br(htmlspecialchars($first_row['RHK_PIMPINAN_INTERV'] ?? '')) ?></td>
                <td class="<?= $row_count > 10 ? 'large-rowspan' : '' ?>" rowspan="<?= $row_count ?>"><?= nl2br(htmlspecialchars($first_row['RENCANA_HASIL_KERJA'] ?? '')) ?></td>
                <td><?= htmlspecialchars($first_row['ASPEK'] ?? '') ?></td>
                <td><?= nl2br(htmlspecialchars($first_row['INDIKATOR_KINERJA_INDIVIDU'] ?? '')) ?></td>
                <td><?= htmlspecialchars(($first_row['TARGET'] ?? '') . (!empty($first_row['SATUAN']) ? ' ' . $first_row['SATUAN'] : '')) ?></td>
            </tr>
            <?php 
            // Add additional rows for the same group
            for ($i = 1; $i < $row_count; $i++): 
                $current_row = $group_rows[$i];
            ?>
            <tr>
                <td><?= htmlspecialchars($current_row['ASPEK'] ?? '') ?></td>
                <td><?= nl2br(htmlspecialchars($current_row['INDIKATOR_KINERJA_INDIVIDU'] ?? '')) ?></td>
                <td><?= htmlspecialchars(($current_row['TARGET'] ?? '') . (!empty($current_row['SATUAN']) ? ' ' . $current_row['SATUAN'] : '')) ?></td>
            </tr>
            <?php 
            endfor;
            $row_number++;
            endforeach; 
            ?>
        <?php else: ?>
            <tr><td colspan="6" class="center">-</td></tr>
        <?php endif; ?>
    </tbody>
    </table>
    <br>
    <div class="section-row" style="margin-top:20px;">PERILAKU KINERJA</div>
    <table class="behavior-table" style="border: none;">
        <!-- Remove <thead> and put all header rows in <tbody> -->
        <tbody>
            <tr>
                <th style="width:4%; border: none;">PERILAKU<br>KERJA</th>
                <th style="width:76%; border: none;">&nbsp;</th>
                <th style="width:20%; border: none;" class="center blue-bg">EKSPEKTASI KHUSUS PIMPINAN</th>
            </tr>
            <?php
            $perilaku_nama = [
                'Berorientasi Pelayanan',
                'Akuntabel',
                'Kompeten',
                'Harmonis',
                'Loyal',
                'Adaptif',
                'Kolaboratif'
            ];
            $perilaku_desc = [
                [
                    'Memahami dan memenuhi kebutuhan masyarakat',
                    'Ramah, cekatan, solutif, dan dapat diandalkan',
                    'Melakukan perbaikan tiada henti'
                ],
                [
                    'Melaksanakan tugas dengan jujur, bertanggungjawab, cermat, disiplin dan berintegritas tinggi',
                    'Menggunakan kekayaan dan barang milik negara secara bertanggungjawab, efektif dan efisien',
                    'Tidak menyalahgunakan kewenangan jabatan'
                ],
                [
                    'Meningkatkan kompetensi diri untuk menjawab tantangan yang selalu berubah',
                    'Membantu orang lain belajar',
                    'Melaksanakan tugas dengan kualitas terbaik'
                ],
                [
                    'Menghargai setiap orang apapun latar belakangnya',
                    'Suka menolong orang lain',
                    'Membangun lingkungan kerja yang kondusif'
                ],
                [
                    'Memegang teguh ideologi Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, setia pada Negara Kesatuan Republik Indonesia serta pemerintahan yang sah',
                    'Menjaga nama baik ASN, Pimpinan, Instansi dan Negara',
                    'Menjaga rahasia jabatan dan negara'
                ],
                [
                    'Cepat menyesuaikan diri menghadapi perubahan',
                    'Terus berinovasi dan mengembangkan kreativitas',
                    'Bertindak proaktif'
                ],
                [
                    'Memberi kesempatan kepada berbagai pihak untuk berkontribusi',
                    'Terbuka dalam bekerjasama untuk menghasilkan nilai tambah',
                    'Menggerakan pemanfaatan berbagai sumber daya untuk tujuan bersama'
                ]
            ];
            $ekspektasi_keys = [
                'EKSPEKTASI_PIMPINAN_BERORIENTASI_PELAYANAN',
                'EKSPEKTASI_PIMPINAN_AKUNTABEL',
                'EKSPEKTASI_PIMPINAN_KOMPETEN',
                'EKSPEKTASI_PIMPINAN_HARMONIS',
                'EKSPEKTASI_PIMPINAN_LOYAL',
                'EKSPEKTASI_PIMPINAN_ADAPTIF',
                'EKSPEKTASI_PIMPINAN_KOLABORATIF'
            ];
            $umpanbalik_keys = [
                'UMPAN_BALIK_BERORIENTASI_PELAYANAN',
                'UMPAN_BALIK_AKUNTABEL',
                'UMPAN_BALIK_KOMPETEN',
                'UMPAN_BALIK_HARMONIS',
                'UMPAN_BALIK_LOYAL',
                'UMPAN_BALIK_ADAPTIF',
                'UMPAN_BALIK_KOLABORATIF'
            ];
            for ($i = 0; $i < 7; $i++):
                $desc_count = count($perilaku_desc[$i]);
                $ekspektasi = isset($perilaku_data[$ekspektasi_keys[$i]]) ? preg_split('/\r?\n/', $perilaku_data[$ekspektasi_keys[$i]]) : [];
                $umpanbalik = isset($perilaku_data[$umpanbalik_keys[$i]]) ? preg_split('/\r?\n/', $perilaku_data[$umpanbalik_keys[$i]]) : [];
                $ekspektasi_isi = htmlspecialchars(implode('<br>', array_map('trim', $ekspektasi)));
                $umpanbalik_isi = htmlspecialchars(implode('<br>', array_map('trim', $umpanbalik)));
                $desc_block = '<span style="font-weight:bold;">' . $perilaku_nama[$i] . '</span><br>';
                foreach ($perilaku_desc[$i] as $desc) {
                    $desc_block .= htmlspecialchars($desc) . '<br>';
                }
            ?>
            <tr>
                <td class="blue-bg center" style="font-weight:bold; font-size:13px; border: none;"> <?= $i+1 ?> </td>
                <td style="border: none;"><?= $desc_block ?></td>
                <td style="border: none;"> <?= $ekspektasi_isi ?> </td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>
    <div class="no-print" style="margin-top: 20px; text-align: center;">
    <button onclick="window.print()" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
        🖨️ Print / Save as PDF
    </button>
</div>
<style>
@media print {
    .no-print { display: none !important; }
}
</style>
<script>
window.onload = function() {
    setTimeout(function() {
        window.print();
    }, 500);
};
</script>
    <br><br>
    <div class="signature-section" style="width:100%; display:flex; justify-content:space-between; align-items:center; margin-top:40px; font-size:12px;">
        <div style="width:48%; text-align:center;">
            PNS yang dinilai,<br><br><br><br>
            <?= htmlspecialchars($first_row['NAMA'] ?? '') ?><br>
            NIP <?= htmlspecialchars($first_row['NIP'] ?? '') ?>
        </div>
        <div style="width:48%; text-align:center;">
            Makassar, <?= date('d F Y') ?><br>
            Pejabat Penilai,<br><br><br><br>
            <?= htmlspecialchars($first_row['NAMA_ATASAN_LANGSUNG'] ?? '') ?><br>
            NIP <?= htmlspecialchars($first_row['NIP_ATASAN_LANGSUNG'] ?? '') ?>
        </div>
    </div>
    <style>
    @media print {
        .signature-section {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    }
    </style>
</body>
</html>
