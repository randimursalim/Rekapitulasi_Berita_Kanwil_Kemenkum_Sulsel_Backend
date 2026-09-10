<?php
// app/helpers/maintenance_helper.php

class MaintenanceHelper {
    private static $flagFile = __DIR__ . '/../../storage/maintenance.flag';

    public static function isMaintenanceMode() {
        // Cek file flag di storage
        if (file_exists(self::$flagFile)) {
            return true;
        }

        // Cek database tb_setting jika file flag tidak ada
        try {
            global $conn;
            if (!$conn) {
                $dbFile = __DIR__ . '/../../config/database.php';
                if (file_exists($dbFile)) {
                    require_once $dbFile;
                }
            }
            if (isset($conn) && $conn) {
                $stmt = $conn->prepare("SELECT setting_value FROM tb_setting WHERE setting_key = 'maintenance_mode'");
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && $row['setting_value'] === '1') {
                    return true;
                }
            }
        } catch (Exception $e) {
            // Silence exception
        }

        return false;
    }

    public static function setMaintenanceMode($enable = true) {
        $storageDir = dirname(self::$flagFile);
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }

        if ($enable) {
            @file_put_contents(self::$flagFile, date('Y-m-d H:i:s'));
        } else {
            if (file_exists(self::$flagFile)) {
                @unlink(self::$flagFile);
            }
        }

        // Update database setting
        try {
            global $conn;
            if (!$conn) {
                $dbFile = __DIR__ . '/../../config/database.php';
                if (file_exists($dbFile)) {
                    require_once $dbFile;
                }
            }
            if (isset($conn) && $conn) {
                $val = $enable ? '1' : '0';
                $stmt = $conn->prepare("INSERT INTO tb_setting (setting_key, setting_value) VALUES ('maintenance_mode', :val) ON DUPLICATE KEY UPDATE setting_value = :val2");
                $stmt->execute([':val' => $val, ':val2' => $val]);
            }
        } catch (Exception $e) {
            // Silence exception
        }

        return true;
    }

    public static function isAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            $sessionPath = __DIR__ . '/../../storage/sessions';
            if (!is_dir($sessionPath)) {
                @mkdir($sessionPath, 0755, true);
            }
            if (is_writable($sessionPath)) {
                ini_set('session.save_path', $sessionPath);
            } else {
                error_reporting(E_ALL & ~E_WARNING);
            }
            @session_start();
        }
        $role = $_SESSION['user']['role'] ?? '';
        return isset($_SESSION['user']) && (strcasecmp($role, 'Admin') === 0 || strcasecmp($role, 'Administrator') === 0);
    }
}

