<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

$id_skp_global = $_GET['id_skp_global'] ?? '';
if (empty($id_skp_global)) {
    die('ID SKP Global tidak ditemukan');
}

require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch SKP data from skp_pegawai
$skp_sql = "SELECT * FROM skp_pegawai WHERE id_skp_global = ? ORDER BY TANGGAL_INPUT_SKP ASC";
$stmt = $conn->prepare($skp_sql);
$stmt->bind_param('i', $id_skp_global);
$stmt->execute();
$result = $stmt->get_result();

$skp_data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $skp_data[] = $row;
    }
}
$stmt->close();

if (empty($skp_data)) {
    $conn->close();
    die('Data SKP tidak ditemukan');
}
$first_row = $skp_data[0];

$nip_pegawai = $first_row['NIP'] ?? '';
$nip_atasan = $first_row['NIP_ATASAN_LANGSUNG'] ?? null;
$tahun = $first_row['TAHUN'] ?? date('Y');
$triwulan = $first_row['TRIWULAN'] ?? 1;

// 1. PEGAWAI YANG DINILAI (same as pegawai profile)
$pegawai_detail = null;
$pegawai_sql = "SELECT * FROM Pegawai WHERE NIP = ? LIMIT 1";
$st = $conn->prepare($pegawai_sql);
$st->bind_param('s', $nip_pegawai);
$st->execute();
$res = $st->get_result();
if ($res && $res->num_rows > 0) {
    $pegawai_detail = $res->fetch_assoc();
}
$st->close();

// 2. PEJABAT PENILAI KINERJA (atasan pegawai yang dinilai)
$penilai_detail = null;
if ($nip_atasan) {
    $penilai_sql = "SELECT * FROM Pegawai WHERE NIP = ? LIMIT 1";
    $st = $conn->prepare($penilai_sql);
    $st->bind_param('s', $nip_atasan);
    $st->execute();
    $res = $st->get_result();
    if ($res && $res->num_rows > 0) {
        $penilai_detail = $res->fetch_assoc();
    }
    $st->close();
}

// 3. ATASAN PEJABAT PENILAI KINERJA
$atasan_penilai_detail = null;
$jabatan_penilai = null;
$st1 = $conn->prepare("SELECT ATASAN_LANGSUNG FROM Pegawai WHERE NIP = ? LIMIT 1");
$st1->bind_param('s', $nip_pegawai);
$st1->execute();
$r1 = $st1->get_result();
if ($r1 && $r1->num_rows > 0) {
    $row1 = $r1->fetch_assoc();
    $jabatan_penilai = $row1['ATASAN_LANGSUNG'] ?? null;
}
$st1->close();

if ($jabatan_penilai) {
    $jabatan_atasan_penilai = null;
    $st2 = $conn->prepare("SELECT ATASAN_LANGSUNG FROM Pegawai WHERE JABATAN = ? LIMIT 1");
    $st2->bind_param('s', $jabatan_penilai);
    $st2->execute();
    $r2 = $st2->get_result();
    if ($r2 && $r2->num_rows > 0) {
        $row2 = $r2->fetch_assoc();
        $jabatan_atasan_penilai = $row2['ATASAN_LANGSUNG'] ?? null;
    }
    $st2->close();

    if ($jabatan_atasan_penilai) {
        $st3 = $conn->prepare("SELECT * FROM Pegawai WHERE JABATAN = ? LIMIT 1");
        $st3->bind_param('s', $jabatan_atasan_penilai);
        $st3->execute();
        $r3 = $st3->get_result();
        if ($r3 && $r3->num_rows > 0) {
            $atasan_penilai_detail = $r3->fetch_assoc();
        }
        $st3->close();
    }
}

// 4. EVALUASI KINERJA
$capaian_organisasi = $first_row['CAPAIAN_KINERJA_ORGANISASI'] ?? '';
$predikat_pegawai = $first_row['PREDIKAT_KINERJA_PEGAWAI'] ?? '';

$conn->close();

// Helpers for display
$p = function ($arr, $key, $def = '') {
    if (!$arr || !is_array($arr)) return $def;
    return $arr[$key] ?? $def;
};
$pg = $pegawai_detail;
$pn = $penilai_detail;
$ap = $atasan_penilai_detail;

// PANGKAT/GOL RUANG - try common column names
$pangkat = function ($arr) {
    if (!$arr) return '';
    return $arr['PANGKAT_GOL_RUANG'] ?? $arr['PANGKAT_GOL RUANG'] ?? '';
};

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

$tanggal_evaluasi = $first_row['TANGGAL_EVALUASI_SKP'] ?? null;
if ($tanggal_evaluasi) {
    $tanggal_evaluasi_obj = new DateTime($tanggal_evaluasi);
    $tanggal_evaluasi_formatted = formatTanggalIndonesia($tanggal_evaluasi_obj);
} else {
    $tanggal_evaluasi_formatted = formatTanggalIndonesia(new DateTime());
}

$employee_name = $first_row['NAMA'] ?? 'Unknown';
$filename = sprintf('Dokumen_Evaluasi_Kinerja_%s_TRIWULAN_%s_%s.pdf', preg_replace('/[^a-zA-Z0-9\s]/', '', $employee_name), $triwulan, $tahun);
$filename = str_replace(' ', '_', $filename);

header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: inline; filename="' . $filename . '"');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dokumen Evaluasi Kinerja Pegawai - <?= htmlspecialchars($periode_display) ?> - <?= htmlspecialchars($tahun) ?></title>
    <style>
        body { font-family: 'Bookman Old Style', Arial, sans-serif; font-size: 11px; background: #fff; margin: 0; padding: 15px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #003366; padding: 6px 8px; overflow: visible; }
        table.doc-eval-table .col-label { width: 220px; }
        table.doc-eval-table td.col-value { min-width: 260px; overflow: visible; word-wrap: break-word; }
        .header-title { text-align: center; font-size: 15px; padding: 2px 0; font-weight: bold; }
        .header-sub { text-align: center; font-size: 12px; padding: 0; }
        .section-row { background: #b7d6f6 !important; color: #000 !important; font-weight: bold; }
        .blue-bg { background: #b7d6f6 !important; color: #000 !important; font-weight: bold; }
        .no-border { border: none !important; background: #fff !important; }
        .no-bottom-border { border-bottom: none !important; }
        .no-top-border { border-top: none !important; }
        .sig-block { display: inline-block; vertical-align: top; width: 48%; margin-top: 30px; }
        @media print {
            table, th, td { border: 1px solid #003366 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; overflow: visible !important; }
            table.doc-eval-table .col-value { overflow: visible !important; }
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 15px;">
        <?php
        $garuda_path = 'images/garuda.png';
        if (!file_exists($garuda_path)) {
            $garuda_path = __DIR__ . '/images/garuda.png';
        }
        if (file_exists($garuda_path)) {
            $image_data = file_get_contents($garuda_path);
            $base64 = base64_encode($image_data);
            echo '<img src="data:image/png;base64,' . $base64 . '" alt="Garuda Pancasila" style="height: 100px; width: auto;">';
        }
        ?>
    </div>
    <div class="header-title">DOKUMEN EVALUASI KINERJA PEGAWAI</div>
    <div class="header-sub">PERIODE: <?= htmlspecialchars($periode_display) ?></div>
    <div style="display: flex; justify-content: space-between; margin: 10px 0;">
        <div>KEMENTERIAN HUKUM</div>
        <div style="text-align: right;"><?= htmlspecialchars($periode_penilaian) ?></div>
    </div>

    <table class="doc-eval-table" style="margin-top: 15px;">
        <colgroup>
            <col style="width: 30px;">
            <col class="col-label" style="width: 220px;">
            <col style="width: 20px;">
            <col class="col-value" style="width: auto;">
        </colgroup>
        
        <tr>
            <td rowspan="6" class="blue-bg" style="vertical-align: top; text-align: center; font-weight: normal;">1</td>
            <td colspan="3" class="section-row" style="font-weight: normal;">PEGAWAI YANG DINILAI</td>
        </tr>
        <tr>
            <td>NAMA</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($first_row['NAMA'] ?? '') ?></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($first_row['NIP'] ?? '') ?></td>
        </tr>
        <tr>
            <td>PANGKAT/GOL RUANG</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars(!empty($first_row['PANGKAT_GOL_RUANG']) ? $first_row['PANGKAT_GOL_RUANG'] : $pangkat($pg)) ?></td>
        </tr>
        <tr>
            <td>JABATAN</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars(!empty($first_row['JABATAN']) ? $first_row['JABATAN'] : $p($pg, 'JABATAN')) ?></td>
        </tr>
        <tr>
            <td>UNIT KERJA</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars(!empty($first_row['UNIT_KERJA']) ? $first_row['UNIT_KERJA'] : $p($pg, 'UNIT_KERJA')) ?></td>
        </tr>

        <tr>
            <td rowspan="6" class="blue-bg" style="vertical-align: top; text-align: center; font-weight: normal;">2</td>
            <td colspan="3" class="section-row" style="font-weight: normal;">PEJABAT PENILAI KINERJA</td>
        </tr>
        <tr>
            <td>NAMA</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($p($pn, 'NAMA', $first_row['NAMA_ATASAN_LANGSUNG'] ?? '')) ?></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($p($pn, 'NIP', $first_row['NIP_ATASAN_LANGSUNG'] ?? '')) ?></td>
        </tr>
        <tr>
            <td>PANGKAT/GOL RUANG</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($pangkat($pn)) ?></td>
        </tr>
        <tr>
            <td>JABATAN</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($p($pn, 'JABATAN')) ?></td>
        </tr>
        <tr>
            <td>UNIT KERJA</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($p($pn, 'UNIT_KERJA')) ?></td>
        </tr>

        <tr>
            <td rowspan="6" class="blue-bg" style="vertical-align: top; text-align: center; font-weight: normal;">3</td>
            <td colspan="3" class="section-row" style="font-weight: normal;">ATASAN PEJABAT PENILAI KINERJA</td>
        </tr>
        <tr>
            <td>NAMA</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($p($ap, 'NAMA')) ?></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($p($ap, 'NIP')) ?></td>
        </tr>
        <tr>
            <td>PANGKAT/GOL RUANG</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($pangkat($ap)) ?></td>
        </tr>
        <tr>
            <td>JABATAN</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($p($ap, 'JABATAN')) ?></td>
        </tr>
        <tr>
            <td>UNIT KERJA</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($p($ap, 'UNIT_KERJA')) ?></td>
        </tr>

        <tr>
            <td rowspan="3" class="blue-bg" style="vertical-align: top; text-align: center; font-weight: normal;">4</td>
            <td colspan="3" class="section-row" style="font-weight: normal;">EVALUASI KINERJA</td>
        </tr>
        <tr>
            <td>CAPAIAN KINERJA ORGANISASI</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($capaian_organisasi) ?></td>
        </tr>
        <tr>
            <td>PREDIKAT KINERJA PEGAWAI</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($predikat_pegawai) ?></td>
        </tr>

        <tr>
            <td rowspan="2" class="blue-bg" style="vertical-align: top; text-align: center; font-weight: normal;">5</td>
            <td colspan="3" class="section-row" style="font-weight: normal;">CATATAN REKOMENDASI</td>
        </tr>
        <tr>
            <td style="min-height: 40px;">&nbsp;</td>
            <td style="text-align: center;">&nbsp;</td>
            <td class="col-value">&nbsp;</td>
        </tr>
    </table>

    <div style="margin-top: 40px;">
        <div class="sig-block">
            Makassar, <?= $tanggal_evaluasi_formatted ?><br>
            <strong>7. Pegawai yang Dinilai</strong><br>
            <br><br>
            <?= htmlspecialchars($first_row['NAMA'] ?? '') ?><br>
            NIP <?= htmlspecialchars($first_row['NIP'] ?? '') ?>
        </div>
        <div class="sig-block" style="text-align: right;">
            Makassar, <?= $tanggal_evaluasi_formatted ?><br>
            <strong>6. Pejabat Penilai Kinerja</strong><br>
            <br><br>
            <?= htmlspecialchars($first_row['NAMA_ATASAN_LANGSUNG'] ?? '') ?><br>
            NIP <?= htmlspecialchars($first_row['NIP_ATASAN_LANGSUNG'] ?? '') ?>
        </div>
    </div>
    <script>
        (function() {
            function showPrintPreview() {
                window.print();
            }
            if (document.readyState === 'complete') {
                setTimeout(showPrintPreview, 400);
            } else {
                window.addEventListener('load', function() { setTimeout(showPrintPreview, 400); });
            }
        })();
    </script>
</body>
</html>
