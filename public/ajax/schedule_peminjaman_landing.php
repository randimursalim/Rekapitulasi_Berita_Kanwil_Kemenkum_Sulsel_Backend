<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once '../../config/database.php';
    
    // Ambil parameter untuk 7 hari ke depan (termasuk hari ini)
    $today = date('Y-m-d');
    $sevenDaysLater = date('Y-m-d', strtotime('+6 days'));
    
    // Query untuk mengambil jadwal peminjaman ruangan 7 hari ke depan
    $query = "
        SELECT 
            id,
            nama_peminjam,
            nama_ruangan,
            kegiatan,
            tanggal_kegiatan,
            waktu_kegiatan,
            COALESCE(durasi_kegiatan, 2) as durasi_kegiatan,
            created_at
        FROM jadwal_peminjaman_ruangan
        WHERE DATE(tanggal_kegiatan) BETWEEN ? AND ?
        ORDER BY tanggal_kegiatan ASC, waktu_kegiatan ASC
        LIMIT 50
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$today, $sevenDaysLater]);
    $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Array warna yang beragam untuk peminjaman ruangan
    $colors = [
        '#6366f1', // Indigo
        '#8b5cf6', // Violet
        '#ec4899', // Pink
        '#ef4444', // Red
        '#f97316', // Orange
        '#eab308', // Yellow
        '#22c55e', // Green
        '#06b6d4', // Cyan
        '#3b82f6', // Blue
        '#84cc16', // Lime
        '#f59e0b', // Amber
        '#10b981', // Emerald
        '#8b5a2b', // Brown
        '#6b7280', // Gray
        '#a855f7'  // Purple
    ];
    
    // Format data untuk landing page
    $formattedData = [];
    foreach ($rawData as $index => $peminjaman) {
        // Pilih warna berdasarkan index (untuk konsistensi)
        $colorIndex = $index % count($colors);
        $selectedColor = $colors[$colorIndex];
        
        // Hitung waktu selesai
        $waktu_parts = explode(':', $peminjaman['waktu_kegiatan']);
        $jam_mulai = intval($waktu_parts[0]);
        $menit_mulai = intval($waktu_parts[1]);
        $durasi = floatval($peminjaman['durasi_kegiatan']);
        
        // Hitung waktu selesai
        $total_menit = ($jam_mulai * 60) + $menit_mulai + ($durasi * 60);
        $jam_selesai = floor($total_menit / 60) % 24;
        $menit_selesai = $total_menit % 60;
        $waktu_selesai = sprintf('%02d:%02d', $jam_selesai, $menit_selesai);
        
        $formattedData[] = [
            'id' => $peminjaman['id'],
            'title' => $peminjaman['kegiatan'],
            'date' => $peminjaman['tanggal_kegiatan'],
            'time' => substr($peminjaman['waktu_kegiatan'], 0, 5) . ' - ' . $waktu_selesai,
            'description' => 'Ruangan: ' . $peminjaman['nama_ruangan'] . ' | Peminjam: ' . $peminjaman['nama_peminjam'],
            'status' => 'Terjadwal',
            'type' => 'room-booking',
            'color' => $selectedColor,
            'ruangan' => $peminjaman['nama_ruangan'],
            'peminjam' => $peminjaman['nama_peminjam']
        ];
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $formattedData,
        'count' => count($formattedData),
        'message' => 'Schedule data loaded successfully',
        'date_range' => [
            'start' => $today,
            'end' => $sevenDaysLater,
            'description' => '7 hari ke depan (termasuk hari ini)'
        ]
    ]);
    
} catch (Exception $e) {
    error_log("[ERROR] Schedule Peminjaman Landing API: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'data' => [],
        'count' => 0,
        'message' => 'Failed to load schedule data: ' . $e->getMessage()
    ]);
}
?>

