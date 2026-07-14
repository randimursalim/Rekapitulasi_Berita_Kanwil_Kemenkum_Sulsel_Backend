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

// Fetch one row from skp_akhir_pegawai for this ID_SKP_GLOBAL
$skp_sql = "SELECT * FROM skp_akhir_pegawai WHERE ID_SKP_GLOBAL = ? LIMIT 1";
$stmt = $conn->prepare($skp_sql);
$stmt->bind_param('i', $id_skp_global);
$stmt->execute();
$skp_row = $stmt->get_result()->fetch_assoc();
$stmt->close();
if ($skp_row) {
    $skp_row = array_change_key_case($skp_row, CASE_UPPER);
}

if (!$skp_row) {
    $conn->close();
    die('Data SKP Akhir tidak ditemukan');
}

$nip_pegawai = $skp_row['NIP'];
$nip_atasan = $skp_row['NIP_ATASAN_LANGSUNG'] ?? null;
$tahun = $skp_row['TAHUN'] ?? date('Y');

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

// 3. ATASAN PEJABAT PENILAI KINERJA: Pegawai WHERE JABATAN = (SELECT ATASAN_LANGSUNG FROM Pegawai WHERE JABATAN = (SELECT ATASAN_LANGSUNG FROM Pegawai WHERE NIP = current_user_nip))
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

// 4. EVALUASI KINERJA: SELECT DISTINCT CAPAIAN_KINERJA_ORGANISASI, PREDIKAT_KINERJA_PEGAWAI FROM skp_akhir_pegawai WHERE TAHUN = ? AND NIP = ?
// Fallback to first row (same SKP) if eval query returns nothing or empty
$capaian_organisasi = $skp_row['CAPAIAN_KINERJA_ORGANISASI'] ?? '';
$predikat_pegawai = $skp_row['PREDIKAT_KINERJA_PEGAWAI'] ?? '';
$eval_sql = "SELECT DISTINCT CAPAIAN_KINERJA_ORGANISASI, PREDIKAT_KINERJA_PEGAWAI FROM skp_akhir_pegawai WHERE TAHUN = ? AND NIP = ?";
$st_ev = $conn->prepare($eval_sql);
$st_ev->bind_param('is', $tahun, $nip_pegawai);
$st_ev->execute();
$res_ev = $st_ev->get_result();
if ($res_ev && $res_ev->num_rows > 0) {
    $ev = array_change_key_case($res_ev->fetch_assoc(), CASE_UPPER);
    $v = $ev['CAPAIAN_KINERJA_ORGANISASI'] ?? '';
    $capaian_organisasi = $v !== '' && $v !== null ? $v : $capaian_organisasi;
    $v = $ev['PREDIKAT_KINERJA_PEGAWAI'] ?? '';
    $predikat_pegawai = $v !== '' && $v !== null ? $v : $predikat_pegawai;
}
$st_ev->close();

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

$employee_name = $p($pg, 'NAMA', 'N/A');
$filename = sprintf('Dokumen_Evaluasi_Kinerja_%s_%s.pdf', preg_replace('/[^a-zA-Z0-9\s]/', '', $employee_name), $tahun);
$filename = str_replace(' ', '_', $filename);
$periode_penilaian = "PERIODE PENILAIAN: 1 JANUARI SD 31 DESEMBER TAHUN $tahun";

header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: inline; filename="' . $filename . '"');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dokumen Evaluasi Kinerja Pegawai - PERIODE: AKHIR - <?= htmlspecialchars($tahun) ?></title>
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
    <div class="header-sub">PERIODE: AKHIR</div>
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
            <td class="col-value"><?= htmlspecialchars($p($pg, 'NAMA')) ?></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($p($pg, 'NIP')) ?></td>
        </tr>
        <tr>
            <td>PANGKAT/GOL RUANG</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars(!empty($skp_row['PANGKAT_GOL_RUANG']) ? $skp_row['PANGKAT_GOL_RUANG'] : $pangkat($pg)) ?></td>
        </tr>
        <tr>
            <td>JABATAN</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars(!empty($skp_row['JABATAN']) ? $skp_row['JABATAN'] : $p($pg, 'JABATAN')) ?></td>
        </tr>
        <tr>
            <td>UNIT KERJA</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars(!empty($skp_row['UNIT_KERJA']) ? $skp_row['UNIT_KERJA'] : $p($pg, 'UNIT_KERJA')) ?></td>
        </tr>

        <tr>
            <td rowspan="6" class="blue-bg" style="vertical-align: top; text-align: center; font-weight: normal;">2</td>
            <td colspan="3" class="section-row" style="font-weight: normal;">PEJABAT PENILAI KINERJA</td>
        </tr>
        <tr>
            <td>NAMA</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($p($pn, 'NAMA')) ?></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td style="text-align: center;">:</td>
            <td class="col-value"><?= htmlspecialchars($p($pn, 'NIP')) ?></td>
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
            Makassar, 31 Desember <?= (int)$tahun ?><br>
            <strong>7. Pegawai yang Dinilai</strong><br>
            <br><br>
            <?= htmlspecialchars($p($pg, 'NAMA')) ?><br>
            NIP <?= htmlspecialchars($p($pg, 'NIP')) ?>
        </div>
        <div class="sig-block" style="text-align: right;">
            Makassar, 31 Desember <?= (int)$tahun ?><br>
            <strong>6. Pejabat Penilai Kinerja</strong><br>
            <br><br>
            <?= htmlspecialchars($p($pn, 'NAMA')) ?><br>
            NIP <?= htmlspecialchars($p($pn, 'NIP')) ?>
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
