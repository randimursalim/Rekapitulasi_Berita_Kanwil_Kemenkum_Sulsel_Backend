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
    
    // Query untuk mengambil kegiatan 7 hari ke depan
    $query = "
        SELECT 
            id_kegiatan,
            nama_kegiatan,
            tanggal,
            jam_mulai,
            jam_selesai,
            keterangan,
            status,
            created_at
        FROM kegiatan
        WHERE DATE(tanggal) BETWEEN ? AND ?
        ORDER BY tanggal ASC, jam_mulai ASC
        LIMIT 50
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$today, $sevenDaysLater]);
    $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug logging
    error_log("Schedule Landing - Date Range: $today to $sevenDaysLater");
    error_log("Schedule Landing - Found " . count($rawData) . " raw activities");
    
    // Deduplicate by ID at database level
    $data = [];
    $seenIds = [];
    foreach ($rawData as $kegiatan) {
        if (!in_array($kegiatan['id_kegiatan'], $seenIds)) {
            $seenIds[] = $kegiatan['id_kegiatan'];
            $data[] = $kegiatan;
        } else {
            error_log("Duplicate activity found in database: " . $kegiatan['nama_kegiatan'] . " (ID: " . $kegiatan['id_kegiatan'] . ")");
        }
    }
    
    error_log("Schedule Landing - After deduplication: " . count($data) . " unique activities");
    
    // Array warna yang beragam untuk kegiatan
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
    foreach ($data as $index => $kegiatan) {
        // Pilih warna berdasarkan index (untuk konsistensi)
        $colorIndex = $index % count($colors);
        $selectedColor = $colors[$colorIndex];
        
        // Tentukan jenis kegiatan berdasarkan nama
        $type = 'meeting'; // Default
        $namaKegiatan = strtolower($kegiatan['nama_kegiatan']);
        if (strpos($namaKegiatan, 'rapat') !== false) {
            $type = 'meeting';
        } elseif (strpos($namaKegiatan, 'kunjungan') !== false) {
            $type = 'visit';
        } elseif (strpos($namaKegiatan, 'sosialisasi') !== false) {
            $type = 'socialization';
        } elseif (strpos($namaKegiatan, 'evaluasi') !== false) {
            $type = 'evaluation';
        } elseif (strpos($namaKegiatan, 'penyuluhan') !== false) {
            $type = 'counseling';
        } elseif (strpos($namaKegiatan, 'upacara') !== false) {
            $type = 'ceremony';
        }
        
        $formattedData[] = [
            'id' => $kegiatan['id_kegiatan'],
            'title' => $kegiatan['nama_kegiatan'],
            'date' => $kegiatan['tanggal'],
            'time' => $kegiatan['jam_mulai'] . ' - ' . $kegiatan['jam_selesai'],
            'description' => $kegiatan['keterangan'] ?: 'Tidak ada keterangan',
            'status' => $kegiatan['status'],
            'type' => $type,
            'color' => $selectedColor
        ];
    }
    
    // Jika tidak ada data 7 hari ke depan, ambil beberapa kegiatan terbaru
    if (empty($formattedData)) {
        $fallbackQuery = "
            SELECT 
                id_kegiatan,
                nama_kegiatan,
                tanggal,
                jam_mulai,
                jam_selesai,
                keterangan,
                status,
                created_at
            FROM kegiatan
            ORDER BY tanggal DESC, jam_mulai ASC
            LIMIT 25
        ";
        
        $stmt = $conn->prepare($fallbackQuery);
        $stmt->execute();
        $fallbackData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($fallbackData as $index => $kegiatan) {
            // Pilih warna berdasarkan index (untuk konsistensi)
            $colorIndex = $index % count($colors);
            $selectedColor = $colors[$colorIndex];
            
            // Tentukan jenis kegiatan berdasarkan nama
            $type = 'meeting'; // Default
            $namaKegiatan = strtolower($kegiatan['nama_kegiatan']);
            if (strpos($namaKegiatan, 'rapat') !== false) {
                $type = 'meeting';
            } elseif (strpos($namaKegiatan, 'kunjungan') !== false) {
                $type = 'visit';
            } elseif (strpos($namaKegiatan, 'sosialisasi') !== false) {
                $type = 'socialization';
            } elseif (strpos($namaKegiatan, 'evaluasi') !== false) {
                $type = 'evaluation';
            } elseif (strpos($namaKegiatan, 'penyuluhan') !== false) {
                $type = 'counseling';
            } elseif (strpos($namaKegiatan, 'upacara') !== false) {
                $type = 'ceremony';
            }
            
            $formattedData[] = [
                'id' => $kegiatan['id_kegiatan'],
                'title' => $kegiatan['nama_kegiatan'],
                'date' => $kegiatan['tanggal'],
                'time' => $kegiatan['jam_mulai'] . ' - ' . $kegiatan['jam_selesai'],
                'description' => $kegiatan['keterangan'] ?: 'Tidak ada keterangan',
                'status' => $kegiatan['status'],
                'type' => $type,
                'color' => $selectedColor
            ];
        }
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
    error_log("[ERROR] Schedule Landing API: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'data' => [],
        'count' => 0,
        'message' => 'Failed to load schedule data: ' . $e->getMessage()
    ]);
}
?>
