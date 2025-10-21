<?php
require_once __DIR__ . '/../../app/models/HomeModel.php';

header('Content-Type: application/json');

try {
    $homeModel = new HomeModel();
    $statistik = $homeModel->getStatistik();
    
    // Format data sesuai dengan yang dibutuhkan dashboard
    $response = [
        'success' => true,
        'data' => [
            'total_berita' => $statistik['total_berita'],
            'total_medsos' => $statistik['total_medsos'],
            'total_arsip' => $statistik['total_arsip']
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'Gagal mengambil data statistik',
        'message' => $e->getMessage()
    ];
    
    echo json_encode($response);
}
?>
