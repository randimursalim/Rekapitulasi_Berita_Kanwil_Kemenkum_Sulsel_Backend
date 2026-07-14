<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Get id_skp_global from URL
$id_skp_global = $_GET['id_skp_global'] ?? '';

if (empty($id_skp_global)) {
    die('ID SKP Global tidak ditemukan');
}

// Database connection
require_once '../config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

function render_feedback_with_thumb($text) {
    $thumb_base64 = 'data:image/png;base64,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 whitespace characters are here, wait, I will write the base64 again correctly';
    return str_replace('[THUMB]', '<img src="' . $thumb_base64 . '" alt="Thumbs Up" style="height:60px;vertical-align:middle;filter:brightness(0);">', $text);
}

// Fetch SKP data - Quarterly SKP only (from skp_kuantitatif_awal_tahun_pegawai)
$is_annual = false;
$skp_data = [];

// Query quarterly SKP table only
$skp_sql = "SELECT * FROM skp_kuantitatif_awal_tahun_pegawai WHERE id_skp_global = ? ORDER BY TANGGAL_INPUT_SKP ASC";
$stmt = $conn->prepare($skp_sql);
$stmt->bind_param('i', $id_skp_global);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $skp_data[] = $row;
    }
} else {
    die('Data SKP Triwulan tidak ditemukan');
}
$stmt->close();

$pegawai_detail = null;
if (!empty($skp_data[0]['NIP'])) {
    $nip_pegawai = $skp_data[0]['NIP'];
    $pegawai_sql = "SELECT * FROM Pegawai WHERE NIP = ? LIMIT 1";
    $stmt_pegawai = $conn->prepare($pegawai_sql);
    $stmt_pegawai->bind_param('s', $nip_pegawai);
    $stmt_pegawai->execute();
    $result_pegawai = $stmt_pegawai->get_result();
    if ($result_pegawai && $result_pegawai->num_rows > 0) {
        $pegawai_detail = $result_pegawai->fetch_assoc();
    }
    $stmt_pegawai->close();
}

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

// Fetch Perilaku Kerja data - Quarterly SKP only
$perilaku_sql = "SELECT * FROM skp_perilaku_awal_tahun_pegawai WHERE id_skp_global = ?";
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

// Separate Kinerja Utama and Kinerja Tambahan
$kinerja_utama = [];
$kinerja_tambahan = [];
foreach ($skp_data as $row) {
    $target = trim($row['TARGET'] ?? '');
    $realisasi = trim($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? '');
    $is_not_performed = ($target === '0' && $realisasi === '0');
    
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

$triwulan = $first_row['TRIWULAN'] ?? 1;
$tahun = $first_row['TAHUN'] ?? date('Y');
$periode_awal = '';
$periode_akhir = '';
switch ((int)$triwulan) {
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
$periode_display = 'TRIWULAN ' . $triwulan;

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

$tanggal_evaluasi = $first_row['TANGGAL_EVALUASI_SKP'] ?? null;
if ($tanggal_evaluasi) {
    $tanggal_evaluasi_obj = new DateTime($tanggal_evaluasi);
    $tanggal_evaluasi_formatted = formatTanggalIndonesia($tanggal_evaluasi_obj);
} else {
    $tanggal_evaluasi_formatted = formatTanggalIndonesia(new DateTime());
}

$skp_title = 'Umpan_Balik_Triwulan_Eselon';
$employee_name = $first_row['NAMA'] ?? 'Unknown';
$period = 'TRIWULAN_' . $triwulan;
$filename = sprintf('SKP %s_%s_%s_%s.pdf', 
    $skp_title, 
    preg_replace('/[^a-zA-Z0-9\s]/', '', $employee_name),
    $period, 
    $tahun
);
$filename = str_replace(' ', '_', $filename);

header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: inline; filename="' . $filename . '"');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SKP Umpan Balik Triwulan Eselon - <?= htmlspecialchars($employee_name) ?> - <?= htmlspecialchars($periode_display) ?> - <?= htmlspecialchars($tahun) ?></title>
    <link rel="icon" type="image/png" href="../images/SIAPA.png">
    <style>
        body { font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-size: 11px; background: #fff; margin: 0; padding: 0; }
        table { border-collapse: collapse; width: 100%; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; }
        th, td { border: 1px solid #003366; padding: 6px 8px; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .feedback-cell { font-family: 'Wingdings', 'Bookman Old Style', sans-serif; }
        .header-title { text-align: center; font-size: 15px; padding: 2px 0; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .header-sub { text-align: center; font-size: 12px; padding: 0; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .blue-bg { background: #b7d6f6 !important; color: #000 !important; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .section-row { background: #b7d6f6 !important; color: #000 !important; text-align: left; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .center { text-align: center; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .no-border { border: none !important; background: #fff !important; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .no-bottom-border { border-bottom: none !important; }
        .no-top-border { border-top: none !important; }
        .behavior-title { font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .behavior-desc { font-size: 10px; padding-left: 10px; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .behavior-table th, .behavior-table td { border: 1px solid #003366; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
        .behavior-table th { background: #b7d6f6 !important; color: #000 !important; text-align: center; font-family: 'Bookman Old Style', 'Segoe UI Emoji', 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Symbol', 'Arial', sans-serif; font-weight: normal; }
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
            tr:empty {
                display: none;
            }
            tbody tr {
                break-inside: auto;
            }
            table tbody tr:last-child {
                page-break-after: auto;
            }
            table.table-kinerja-rowspan tbody tr:last-child td:only-child:not([rowspan]) {
                display: none !important;
            }
            tbody tr td {
                min-width: 0;
            }
            td[rowspan]:not([rowspan="1"]):not([rowspan="2"]):not([rowspan="3"]):not([rowspan="4"]):not([rowspan="5"]) {
                page-break-inside: auto !important;
            }
            .no-bottom-border { border-bottom: none !important; }
            table tr:last-child td, table tr:last-child th { border-bottom: none !important; }
        }
    </style>
</head>
<body>
     <div class="header-title">UMPAN BALIK BERKELANJUTAN</div>
     <div class="header-title">PENDEKATAN HASIL KERJA KUANTITATIF</div>
     <div class="header-title">BAGI PEJABAT PIMPINAN TINGGI DAN PIMPINAN UNIT KERJA MANDIRI</div>
     <div class="header-title">PERIODE: TRIWULAN <?= $triwulan ?> TAHUN <?= $tahun ?></div>
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
     <table class="table-kinerja-rowspan">
     <tbody>
         <tr><th colspan="7" class="blue-bg" style="text-align:left;">HASIL KERJA</th></tr>
         <tr>
             <th class="blue-bg center" style="width: 5%;">NO</th>
             <th class="blue-bg center" style="width: 25%;">RENCANA HASIL KERJA</th>
             <th class="blue-bg center" style="width: 20%;">INDIKATOR KINERJA INDIVIDU</th>
             <th class="blue-bg center" style="width: 20%;">PERSPEKTIF</th>
             <th class="blue-bg center" style="width: 10%;">TARGET</th>
             <th class="blue-bg center" style="width: 10%;">REALISASI BERDASARKAN BUKTI DUKUNG</th>
             <th class="blue-bg center" style="width: 10%;">UMPAN BALIK BERKELANJUTAN BERDASARKAN BUKTI DUKUNG</th>
         </tr>
         <tr>
             <td class="blue-bg center small">(1)</td>
             <td class="blue-bg center small">(2)</td>
             <td class="blue-bg center small">(3)</td>
             <td class="blue-bg center small">(4)</td>
             <td class="blue-bg center small">(5)</td>
             <td class="blue-bg center small">(6)</td>
             <td class="blue-bg center small">(7)</td>
         </tr>
         <tr><td colspan="7" class="section-row">A. KINERJA UTAMA</td></tr>
         <?php if (!empty($kinerja_utama)): ?>
             <?php 
             $row_number = 1;
             $prev_perspektif = null;
             $consecutive_count = 1;
             
             $perspektif_rowspan_map = [];
             for ($i = 0; $i < count($kinerja_utama); $i++) {
                 $current_perspektif = $kinerja_utama[$i]['PERSPEKTIF'] ?? '';
                 if ($current_perspektif === $prev_perspektif && $prev_perspektif !== null) {
                     $consecutive_count++;
                 } else {
                     if ($prev_perspektif !== null && $i > 0) {
                         $perspektif_rowspan_map[$i - $consecutive_count] = $consecutive_count;
                     }
                     $consecutive_count = 1;
                 }
                 $prev_perspektif = $current_perspektif;
             }
             if (count($kinerja_utama) > 0) {
                 $perspektif_rowspan_map[count($kinerja_utama) - $consecutive_count] = $consecutive_count;
             }
             
             $prev_rencana = null;
             $consecutive_count_rencana = 1;
             $rencana_rowspan_map = [];
             for ($i = 0; $i < count($kinerja_utama); $i++) {
                 $current_rencana = $kinerja_utama[$i]['RENCANA_HASIL_KERJA'] ?? '';
                 if ($current_rencana === $prev_rencana && $prev_rencana !== null) {
                     $consecutive_count_rencana++;
                 } else {
                     if ($prev_rencana !== null && $i > 0) {
                         $rencana_rowspan_map[$i - $consecutive_count_rencana] = $consecutive_count_rencana;
                     }
                     $consecutive_count_rencana = 1;
                 }
                 $prev_rencana = $current_rencana;
             }
             if (count($kinerja_utama) > 0) {
                 $rencana_rowspan_map[count($kinerja_utama) - $consecutive_count_rencana] = $consecutive_count_rencana;
             }
             
             $row_number = 1;
             $prev_perspektif = null;
             $prev_rencana = null;
             $perspektif_group_remaining = 0;
             $rencana_group_remaining = 0;
             
             foreach ($kinerja_utama as $index => $row): 
                 $current_perspektif = $row['PERSPEKTIF'] ?? '';
                 $current_rencana = $row['RENCANA_HASIL_KERJA'] ?? '';
                 $is_first_in_group_perspektif = ($current_perspektif !== $prev_perspektif);
                 $is_first_in_group_rencana = ($current_rencana !== $prev_rencana);
                 
                 if ($is_first_in_group_perspektif) {
                     $perspektif_group_remaining = $perspektif_rowspan_map[$index] ?? 1;
                 }
                 if ($is_first_in_group_rencana) {
                     $rencana_group_remaining = $rencana_rowspan_map[$index] ?? 1;
                 }
                 
                 $is_last_in_group_perspektif = ($perspektif_group_remaining == 1);
                 $is_last_in_group_rencana = ($rencana_group_remaining == 1);
                 
                 $should_show_perspektif = $is_first_in_group_perspektif;
                 $should_show_rencana = $is_first_in_group_rencana;
                 
                 if ($is_first_in_group_perspektif && $prev_perspektif !== null) {
                     $row_number++;
                 }
                 
                 $perspektif_class = "";
                 if (!$should_show_perspektif) { $perspektif_class .= " no-top-border"; }
                 if (!$is_last_in_group_perspektif) { $perspektif_class .= " no-bottom-border"; }
                 
                 $rencana_class = "";
                 if (!$should_show_rencana) { $rencana_class .= " no-top-border"; }
                 if (!$is_last_in_group_rencana) { $rencana_class .= " no-bottom-border"; }
                 
                 $perspektif_group_remaining--;
                 $rencana_group_remaining--;
             ?>
             <tr>
                 <td class="center <?= $perspektif_class ?>"><?= $should_show_perspektif ? $row_number : '' ?></td>
                 <td class="<?= $rencana_class ?>"><?= $should_show_rencana ? nl2br(htmlspecialchars($current_rencana)) : '' ?></td>
                 <td><?= nl2br(htmlspecialchars($row['INDIKATOR_KINERJA_INDIVIDU'] ?? '')) ?></td>
                 <td class="<?= $perspektif_class ?>"><?= $should_show_perspektif ? nl2br(htmlspecialchars($current_perspektif)) : '' ?></td>
                 <td><?= htmlspecialchars(($row['TARGET'] ?? '') . (!empty($row['SATUAN']) ? ' ' . $row['SATUAN'] : '')) ?></td>
                 <td><?= htmlspecialchars(($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? '') . (!empty($row['SATUAN']) ? ' ' . $row['SATUAN'] : '')) ?></td>
                 <td class="feedback-cell">
                     <?php if (!empty($row['UMPAN_BALIK_STICKER']) && $row['UMPAN_BALIK_STICKER'] == 'C'): ?>
                         <span style="font-family: 'Wingdings', 'Wingdings 2', 'Wingdings 3', 'Arial', sans-serif; font-size: 18px; color: #000000; margin-right: 8px;">C</span>
                     <?php endif; ?>
                     <?= nl2br(htmlspecialchars($row['UMPAN_BALIK_DENGAN_BUKTI_DUKUNG'] ?? '')) ?>
                 </td>
             </tr>
             <?php 
             $prev_perspektif = $current_perspektif;
             $prev_rencana = $current_rencana;
             endforeach; 
             ?>
         <?php else: ?>
             <tr><td colspan="7" class="center">-</td></tr>
         <?php endif; ?>
         <tr><td colspan="7" class="section-row">B. KINERJA TAMBAHAN</td></tr>
         <?php if (!empty($kinerja_tambahan)): ?>
             <?php 
             $row_number = 1;
             $prev_perspektif = null;
             $consecutive_count = 1;
             
             $perspektif_rowspan_map = [];
             for ($i = 0; $i < count($kinerja_tambahan); $i++) {
                 $current_perspektif = $kinerja_tambahan[$i]['PERSPEKTIF'] ?? '';
                 if ($current_perspektif === $prev_perspektif && $prev_perspektif !== null) {
                     $consecutive_count++;
                 } else {
                     if ($prev_perspektif !== null && $i > 0) {
                         $perspektif_rowspan_map[$i - $consecutive_count] = $consecutive_count;
                     }
                     $consecutive_count = 1;
                 }
                 $prev_perspektif = $current_perspektif;
             }
             if (count($kinerja_tambahan) > 0) {
                 $perspektif_rowspan_map[count($kinerja_tambahan) - $consecutive_count] = $consecutive_count;
             }
             
             $prev_rencana = null;
             $consecutive_count_rencana = 1;
             $rencana_rowspan_map = [];
             for ($i = 0; $i < count($kinerja_tambahan); $i++) {
                 $current_rencana = $kinerja_tambahan[$i]['RENCANA_HASIL_KERJA'] ?? '';
                 if ($current_rencana === $prev_rencana && $prev_rencana !== null) {
                     $consecutive_count_rencana++;
                 } else {
                     if ($prev_rencana !== null && $i > 0) {
                         $rencana_rowspan_map[$i - $consecutive_count_rencana] = $consecutive_count_rencana;
                     }
                     $consecutive_count_rencana = 1;
                 }
                 $prev_rencana = $current_rencana;
             }
             if (count($kinerja_tambahan) > 0) {
                 $rencana_rowspan_map[count($kinerja_tambahan) - $consecutive_count_rencana] = $consecutive_count_rencana;
             }
             
             $row_number = 1;
             $prev_perspektif = null;
             $prev_rencana = null;
             $perspektif_group_remaining = 0;
             $rencana_group_remaining = 0;
             
             foreach ($kinerja_tambahan as $index => $row): 
                 $current_perspektif = $row['PERSPEKTIF'] ?? '';
                 $current_rencana = $row['RENCANA_HASIL_KERJA'] ?? '';
                 $is_first_in_group_perspektif = ($current_perspektif !== $prev_perspektif);
                 $is_first_in_group_rencana = ($current_rencana !== $prev_rencana);
                 
                 if ($is_first_in_group_perspektif) {
                     $perspektif_group_remaining = $perspektif_rowspan_map[$index] ?? 1;
                 }
                 if ($is_first_in_group_rencana) {
                     $rencana_group_remaining = $rencana_rowspan_map[$index] ?? 1;
                 }
                 
                 $is_last_in_group_perspektif = ($perspektif_group_remaining == 1);
                 $is_last_in_group_rencana = ($rencana_group_remaining == 1);
                 
                 $should_show_perspektif = $is_first_in_group_perspektif;
                 $should_show_rencana = $is_first_in_group_rencana;
                 
                 if ($is_first_in_group_perspektif && $prev_perspektif !== null) {
                     $row_number++;
                 }
                 
                 $perspektif_class = "";
                 if (!$should_show_perspektif) { $perspektif_class .= " no-top-border"; }
                 if (!$is_last_in_group_perspektif) { $perspektif_class .= " no-bottom-border"; }
                 
                 $rencana_class = "";
                 if (!$should_show_rencana) { $rencana_class .= " no-top-border"; }
                 if (!$is_last_in_group_rencana) { $rencana_class .= " no-bottom-border"; }
                 
                 $perspektif_group_remaining--;
                 $rencana_group_remaining--;
             ?>
             <tr>
                 <td class="center <?= $perspektif_class ?>"><?= $should_show_perspektif ? $row_number : '' ?></td>
                 <td class="<?= $rencana_class ?>"><?= $should_show_rencana ? nl2br(htmlspecialchars($current_rencana)) : '' ?></td>
                 <td><?= nl2br(htmlspecialchars($row['INDIKATOR_KINERJA_INDIVIDU'] ?? '')) ?></td>
                 <td class="<?= $perspektif_class ?>"><?= $should_show_perspektif ? nl2br(htmlspecialchars($current_perspektif)) : '' ?></td>
                 <td><?= htmlspecialchars(($row['TARGET'] ?? '') . (!empty($row['SATUAN']) ? ' ' . $row['SATUAN'] : '')) ?></td>
                 <td><?= htmlspecialchars(($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? '') . (!empty($row['SATUAN']) ? ' ' . $row['SATUAN'] : '')) ?></td>
                 <td class="feedback-cell">
                     <?php if (!empty($row['UMPAN_BALIK_STICKER']) && $row['UMPAN_BALIK_STICKER'] == 'C'): ?>
                         <span style="font-family: 'Wingdings', 'Wingdings 2', 'Wingdings 3', 'Arial', sans-serif; font-size: 18px; color: #000000; margin-right: 8px;">C</span>
                     <?php endif; ?>
                     <?= nl2br(htmlspecialchars($row['UMPAN_BALIK_DENGAN_BUKTI_DUKUNG'] ?? '')) ?>
                 </td>
             </tr>
             <?php 
             $prev_perspektif = $current_perspektif;
             $prev_rencana = $current_rencana;
             endforeach; 
             ?>
         <?php else: ?>
             <tr><td colspan="7" class="center">-</td></tr>
         <?php endif; ?>
     </tbody>
     </table>
     <br>
     <div class="section-row" style="margin-top:20px;">PERILAKU KINERJA</div>
     <table class="behavior-table" style="border: none;">
         <tbody>
             <tr>
                 <th style="width:4%; border: none;">PERILAKU<br>KERJA</th>
                 <th style="width:56%; border: none;">&nbsp;</th>
                 <th style="width:20%; border: none;" class="center blue-bg">EKSPEKTASI KHUSUS PIMPINAN</th>
                 <th style="width:20%; border: none;" class="center blue-bg">UMPAN BALIK BERKELANJUTAN BERDASARKAN BUKTI DUKUNG</th>
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
                     'Menjaga nama baik ASN, Pimpinan, Instansi and Negara',
                     'Menjaga rahasia jabatan and negara'
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
                 <td style="border: none;" class="feedback-cell"> <?= $umpanbalik_isi ?> </td>
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
             Makassar, <?= $tanggal_evaluasi_formatted ?><br>
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
