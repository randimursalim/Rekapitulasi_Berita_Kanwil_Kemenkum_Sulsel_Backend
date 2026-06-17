<?php
header('Content-Type: application/json');

$layanan = $_GET['layanan'] ?? '';

$data = [

    'adm' => [
        'Surat masuk',
        'Pengaduan masyarakat',
        'Penerbitan surat izin penelitian',
        'Penerbitan surat izin magang',
        'Lainnya'
    ],

    'ahu' => [
        'Notariat',
        'Kewarganegaraan/Pewarganegaraan',
        'Apostille',
        'Legalisasi',
        'Badan Hukum',
        'PPNS',
        'Wasit',
        'Partai Politik',
        'Fidusia',
        'Lainnya'
    ],

    'ki' => [
        'Desain industri',
        'Hak Cipta',
        'Indikasi Geografis',
        'Merek',
        'Paten',
        'KI Komunal',
        'DTLST',
        'Rahasia Dagang',
        'Lainnya'
    ],

    'p3h' => [
        'Bantuan Hukum',
        'Konsultasi Hukum',
        'Posbankum',
        'JDIH',
        'Harmonisasi',
        'Lainnya'
    ],

    'priority' => [
        'Surat masuk',
        'Pengaduan masyarakat',
        'Penerbitan surat izin penelitian',
        'Penerbitan surat izin magang',

        'Notariat',
        'Kewarganegaraan/Pewarganegaraan',
        'Apostille',
        'Legalisasi',
        'Badan Hukum',
        'PPNS',
        'Wasit',
        'Partai Politik',
        'Fidusia',

        'Desain industri',
        'Hak Cipta',
        'Indikasi Geografis',
        'Merek',
        'Paten',
        'KI Komunal',
        'DTLST',
        'Rahasia Dagang',

        'Bantuan Hukum',
        'Konsultasi Hukum',
        'Posbankum',
        'JDIH',
        'Harmonisasi',

        'Lainnya'
    ]

];

echo json_encode([
    'success' => true,
    'items' => $data[$layanan] ?? []
]);