<?php
// app/helpers/SecureFileUpload.php
require_once __DIR__ . '/../../config/database_secure.php';

class SecureFileUpload {
    private $allowedTypes;
    private $maxFileSize;
    private $uploadDir;
    private $isSecure;
    
    public function __construct() {
        $this->allowedTypes = $this->getEnv('ALLOWED_FILE_TYPES', 'image/jpeg,image/png,image/gif');
        $this->maxFileSize = $this->getEnv('MAX_FILE_SIZE', 5242880); // 5MB default
        $this->isSecure = $this->isHttps();
        $this->uploadDir = __DIR__ . '/../../public/storage/uploads/';
    }
    
    private function getEnv($key, $default = null) {
        // Cek environment variable
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        // Cek dari $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        // Cek dari file .env
        $envFile = __DIR__ . '/../../config/env.example';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($envKey, $envValue) = explode('=', $line, 2);
                    if (trim($envKey) === $key) {
                        return trim($envValue);
                    }
                }
            }
        }
        
        return $default;
    }
    
    private function isHttps() {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ||
               isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ||
               isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443;
    }
    
    public function uploadFile($fileInput, $prefix = 'file') {
        // Validasi input
        if (!isset($_FILES[$fileInput])) {
            return ['success' => false, 'message' => 'File tidak ditemukan'];
        }
        
        $file = $_FILES[$fileInput];
        
        // Cek error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => $this->getUploadErrorMessage($file['error'])];
        }
        
        // Validasi file size
        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'message' => 'File terlalu besar. Maksimal ' . $this->formatBytes($this->maxFileSize)];
        }
        
        // Validasi file type
        $validation = $this->validateFileType($file);
        if (!$validation['success']) {
            return $validation;
        }
        
        // Validasi file content
        $contentValidation = $this->validateFileContent($file);
        if (!$contentValidation['success']) {
            return $contentValidation;
        }
        
        // Generate secure filename
        $secureFileName = $this->generateSecureFileName($file, $prefix);
        
        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0700, true); // More restrictive permission
        }
        
        $targetPath = $this->uploadDir . $secureFileName;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Set proper permissions
            chmod($targetPath, 0644);
            
            // Log successful upload
            $this->logUpload($secureFileName, $file['size'], $_SESSION['user']['username'] ?? 'unknown');
            
            return [
                'success' => true,
                'filename' => $secureFileName,
                'path' => 'storage/uploads/' . $secureFileName,
                'size' => $file['size']
            ];
        } else {
            return ['success' => false, 'message' => 'Gagal menyimpan file'];
        }
    }
    
    private function validateFileType($file) {
        // Get MIME type from file
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Allowed MIME types
        $allowedMimes = explode(',', $this->allowedTypes);
        $allowedMimes = array_map('trim', $allowedMimes);
        
        // Allowed extensions
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Validate MIME type
        if (!in_array($mimeType, $allowedMimes)) {
            return ['success' => false, 'message' => 'Tipe file tidak diizinkan. Hanya ' . implode(', ', $allowedMimes)];
        }
        
        // Validate extension
        if (!in_array($extension, $allowedExtensions)) {
            return ['success' => false, 'message' => 'Ekstensi file tidak diizinkan. Hanya ' . implode(', ', $allowedExtensions)];
        }
        
        // Double check MIME type vs extension
        $expectedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        if (isset($expectedMimes[$extension]) && $expectedMimes[$extension] !== $mimeType) {
            return ['success' => false, 'message' => 'File tidak valid - MIME type tidak sesuai dengan ekstensi'];
        }
        
        return ['success' => true];
    }
    
    private function validateFileContent($file) {
        // Check if file is actually an image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'message' => 'File bukan gambar yang valid'];
        }
        
        // Check image dimensions
        $maxWidth = 4096;
        $maxHeight = 4096;
        if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
            return ['success' => false, 'message' => "Gambar terlalu besar. Maksimal {$maxWidth}x{$maxHeight} pixels"];
        }
        
        // Check for malicious content (basic check)
        $fileContent = file_get_contents($file['tmp_name']);
        $dangerousPatterns = [
            '<?php',
            '<script',
            'javascript:',
            'vbscript:',
            'onload=',
            'onerror='
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (stripos($fileContent, $pattern) !== false) {
                return ['success' => false, 'message' => 'File mengandung konten yang tidak aman'];
            }
        }
        
        return ['success' => true];
    }
    
    private function generateSecureFileName($file, $prefix) {
        // Generate random filename
        $randomBytes = random_bytes(16);
        $randomString = bin2hex($randomBytes);
        
        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Create secure filename
        $secureFileName = $prefix . '_' . $randomString . '_' . time() . '.' . $extension;
        
        return $secureFileName;
    }
    
    private function getUploadErrorMessage($errorCode) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (server limit)',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (form limit)',
            UPLOAD_ERR_PARTIAL => 'File hanya ter-upload sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang di-upload',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
            UPLOAD_ERR_CANT_WRITE => 'Tidak bisa menulis file',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension'
        ];
        
        return $messages[$errorCode] ?? 'Error upload tidak diketahui';
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    private function logUpload($filename, $size, $username) {
        $logMessage = date('Y-m-d H:i:s') . " - File uploaded: {$filename} ({$this->formatBytes($size)}) by {$username}";
        error_log($logMessage, 3, __DIR__ . '/../../storage/logs/upload.log');
    }
    
    public function deleteFile($filename) {
        $filePath = $this->uploadDir . $filename;
        
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                $this->logUpload("DELETED: {$filename}", 0, $_SESSION['user']['username'] ?? 'unknown');
                return true;
            }
        }
        
        return false;
    }
}
