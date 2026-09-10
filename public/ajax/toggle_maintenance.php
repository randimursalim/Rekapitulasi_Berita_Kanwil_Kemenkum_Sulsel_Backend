<?php
// public/ajax/toggle_maintenance.php
// Set custom session path agar sama dengan index.php
$sessionPath = __DIR__ . '/../../storage/sessions';
if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0755, true);
}
if (is_writable($sessionPath)) {
    ini_set('session.save_path', $sessionPath);
}

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../../app/helpers/maintenance_helper.php';

// Proteksi hanya role Admin yang bisa mengubah status Maintenance Mode
if (!MaintenanceHelper::isAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak. Hanya Admin yang dapat mengelola Mode Maintenance.'
    ]);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'status';

if ($action === 'toggle' || $action === 'enable' || $action === 'disable') {
    $currentStatus = MaintenanceHelper::isMaintenanceMode();
    $newStatus = ($action === 'toggle') ? !$currentStatus : ($action === 'enable');
    
    MaintenanceHelper::setMaintenanceMode($newStatus);
    
    echo json_encode([
        'success' => true,
        'maintenance' => $newStatus,
        'message' => $newStatus ? 'Mode Maintenance berhasil DIAKTIFKAN.' : 'Mode Maintenance berhasil DINONAKTIFKAN.'
    ]);
    exit;
}

// Get Current Status
$status = MaintenanceHelper::isMaintenanceMode();
echo json_encode([
    'success' => true,
    'maintenance' => $status
]);
