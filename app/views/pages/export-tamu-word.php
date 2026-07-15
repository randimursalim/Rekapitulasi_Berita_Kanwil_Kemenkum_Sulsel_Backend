<?php
// Auto-detect BASE_URL (SAMA SEPERTI tamu.php)
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

    $BASE = $isLocalhost
        ? (defined('BASE_URL') ? BASE_URL : '/rekap-konten/public')
        : '';
}

$filenameSuffix = 'semua';
if (!empty($startDate)) {
    $filenameSuffix = $startDate;
    if (!empty($endDate)) {
        $filenameSuffix .= '_s_d_' . $endDate;
    }
}
header("Content-Type: application/msword");
header("Content-Disposition: attachment; filename=rekap-pelayanan-$filenameSuffix.doc");

$bulanIndonesia = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

?>

<html>

<head>
    <meta charset="UTF-8">

    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
        }

        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: middle;
        }

        .title {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
        }

        .subtitle {
            font-size: 12pt;
            text-align: center;
        }

        .header th {
            background: #d9d9d9;
            text-align: center;
            font-weight: bold;
        }

        .bulan-row td {
            background: #bfbfbf;
            font-weight: bold;
        }

        .center {
            text-align: center;
        }
    </style>
</head>

<body>

    <table class="main-table">

        <tr>
            <td colspan="7" class="title">
                REKAPITULASI DATA PELAYANAN KEMENTERIAN HUKUM
            </td>
        </tr>

        <tr>
            <td colspan="7" class="subtitle">
                Pada Kantor Wilayah Kementerian Hukum Sulawesi Selatan
            </td>
        </tr>

        <tr>
            <td colspan="7" class="subtitle">
                <?php
                if (!empty($startDate) && !empty($endDate)) {
                    echo "Periode: " . date('d-m-Y', strtotime($startDate)) . " s/d " . date('d-m-Y', strtotime($endDate));
                } elseif (!empty($startDate)) {
                    echo "Periode: Mulai " . date('d-m-Y', strtotime($startDate));
                } elseif (!empty($endDate)) {
                    echo "Periode: s/d " . date('d-m-Y', strtotime($endDate));
                } else {
                    echo "Periode: Semua Tanggal";
                }
                ?>
                <?php if (!empty($layanan)): ?>
                    | Layanan: <?= strtoupper($layanan) ?>
                    <?php if (!empty($layanan_item)): ?>
                        - Item: <?= strtoupper($layanan_item) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>

        <tr>
            <td colspan="7" height="20"></td>
        </tr>

        <tr class="header">

            <th>No</th>

            <th>Layanan Kemenkum</th>

            <th>Jenis Layanan</th>

            <th>Nama Layanan Yang Diterima</th>

            <th>Waktu Pelaksanaan Layanan</th>

            <th>Nama</th>

            <th>Telp</th>

        </tr>

        <?php

        $currentMonth = '';
        $no = 1;

        foreach ($data as $row):

            $bulan = (int) date('n', strtotime($row['tgl']));

            if ($currentMonth != $bulan):

                $currentMonth = $bulan;
                ?>

                <tr class="bulan-row">

                    <td colspan="7">
                        BULAN <?= strtoupper($bulanIndonesia[$bulan]) ?>
                    </td>

                </tr>

            <?php endif; ?>

            <tr>

                <td class="center">
                    <?= $no++ ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['layanan']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['layanan_item']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['tujuan']) ?>
                </td>

                <td>
                    <?= date('j', strtotime($row['tgl'])) . ' ' .
                        $bulanIndonesia[(int) date('n', strtotime($row['tgl']))] . ' ' .
                        date('Y', strtotime($row['tgl'])) ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['nama']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['telp']) ?>
                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</body>

</html>