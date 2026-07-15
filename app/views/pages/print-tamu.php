<?php
// Auto-detect BASE_URL (SAMA SEPERTI tamu.php)
if (!isset($BASE)) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $httpHost   = $_SERVER['HTTP_HOST'] ?? '';

    $isLocalhost = (
        strpos($serverName, 'localhost') !== false ||
        strpos($serverName, '127.0.0.1') !== false ||
        strpos($httpHost, 'localhost') !== false ||
        strpos($requestUri, '/rekap-konten/public') !== false ||
        strpos($scriptName, '/rekap-konten/public') !== false
    );

    $BASE = $isLocalhost
        ? (defined('BASE_URL') ? BASE_URL : '/rekap-konten/public')
        : '';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Buku Tamu - KEMENKUM SULSEL</title>
    <link rel="icon" type="image/png" href="<?= $BASE ?>/Images/lamacca.png">
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12px;
        }
        .kop {
            text-align: center;
            margin-bottom: 10px;
        }
        .kop img {
            width: 35px;
            height: auto;
        }
        .judul {
            text-align: center;
            font-weight: bold;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        table th {
            background: #eee;
        }
        .ttd img, .foto img {
            max-height: 40px;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
        }

        @media print {
            body { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="kop">
    <img src="<?= $BASE ?>/Images/LOGO KEMENKUM.jpeg">
    <div><strong>KANTOR WILAYAH KEMENTERIAN HUKUM</strong></div>
    <div>SULAWESI SELATAN</div>
</div>

<div class="judul">
    BUKU TAMU<br>
    <?php if (!empty($startDate) && !empty($endDate)): ?>
        PERIODE: <?= date('d-m-Y', strtotime($startDate)) ?> s/d <?= date('d-m-Y', strtotime($endDate)) ?>
    <?php elseif (!empty($startDate)): ?>
        PERIODE: MULAI <?= date('d-m-Y', strtotime($startDate)) ?>
    <?php elseif (!empty($endDate)): ?>
        PERIODE: S/D <?= date('d-m-Y', strtotime($endDate)) ?>
    <?php else: ?>
        PERIODE: SEMUA TANGGAL
    <?php endif; ?>
    <?php if (!empty($layanan)): ?>
        <br>LAYANAN: <?= strtoupper($layanan) ?>
        <?php if (!empty($layanan_item)): ?>
            - ITEM: <?= strtoupper($layanan_item) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<table>
    <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Nama</th>
        <th>Telp</th>
        <th>Email</th>
        <th>Alamat</th>
        <th>Layanan</th>
        <th>Item Layanan</th>
        <th>Maksud/Tujuan</th>
        <th>TTD</th>
        <th>Foto</th>
    </tr>

    <?php foreach ($data as $i => $t): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= date('d-m-Y', strtotime($t['tgl'])) ?><br><?= $t['jam'] ?></td>
        <td><?= htmlspecialchars($t['nama']) ?></td>
        <td><?= htmlspecialchars($t['telp']) ?></td>
        <td><?= htmlspecialchars($t['email']) ?></td>
        <td><?= htmlspecialchars($t['alamat']) ?></td>
        <td><?= htmlspecialchars($t['layanan']) ?></td>
        <td><?= htmlspecialchars($t['layanan_item']) ?></td>
        <td><?= htmlspecialchars($t['tujuan']) ?></td>
        <td class="ttd">
            <?php if ($t['ttd']): ?>
                <img src="<?= $BASE ?>/storage/uploads/ttd/<?= $t['ttd'] ?>">
            <?php endif; ?>
        </td>
        <td class="foto">
            <?php if ($t['foto']): ?>
                <img src="<?= $BASE ?>/storage/uploads/foto/<?= $t['foto'] ?>">
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<div class="footer">
    Makassar, <?= date('d F Y') ?><br><br><br>
    Kepala Kantor Wilayah
</div>

</body>
</html>