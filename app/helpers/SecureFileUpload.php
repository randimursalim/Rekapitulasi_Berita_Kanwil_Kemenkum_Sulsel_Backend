<?php
// app/helpers/SecureFileUpload.php
require_once __DIR__ . '/../../config/database_secure.php';

class SecureFileUpload {
    private $allowedTypes;
    private $maxFileSize;
    private $uploadDir;
    private $isSecure;
    
    public function __construct($subdirectory = '') {
        // Include variasi MIME type untuk JPEG (image/jpeg, image/jpg, image/pjpeg)
        $this->allowedTypes = $this->getEnv('ALLOWED_FILE_TYPES', 'image/jpeg,image/jpg,image/pjpeg,image/png,image/gif,application/pdf,application/x-pdf,application/msword,application/vnd.ms-word,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip');
        // Default 10MB untuk foto (10485760 bytes)
        $this->maxFileSize = $this->getEnv('MAX_FILE_SIZE', 10485760); // 10MB default
        $this->isSecure = $this->isHttps();
        
        // Set upload directory dengan subdirectory jika ada
        $baseDir = __DIR__ . '/../../public/storage/uploads/';
        if (!empty($subdirectory)) {
            // Sanitize subdirectory untuk keamanan
            $subdirectory = preg_replace('/[^a-z0-9_-]/i', '', $subdirectory);
            $this->uploadDir = $baseDir . trim($subdirectory, '/') . '/';
        } else {
            $this->uploadDir = $baseDir;
        }
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
        error_log("[SECURE UPLOAD] Starting upload for input: {$fileInput}");
        error_log("[SECURE UPLOAD] _FILES array keys: " . implode(', ', array_keys($_FILES)));
        
        if (!isset($_FILES[$fileInput])) {
            error_log("[UPLOAD ERROR] File input '{$fileInput}' tidak ditemukan dalam _FILES");
            error_log("[UPLOAD ERROR] Available keys: " . implode(', ', array_keys($_FILES)));
            return ['success' => false, 'message' => 'File tidak ditemukan'];
        }
        
        $file = $_FILES[$fileInput];
        
        // Log file info untuk debugging
        error_log("[UPLOAD DEBUG] File name: " . ($file['name'] ?? 'N/A'));
        error_log("[UPLOAD DEBUG] File size: " . ($file['size'] ?? 'N/A') . " bytes");
        error_log("[UPLOAD DEBUG] File error code: " . ($file['error'] ?? 'N/A'));
        error_log("[UPLOAD DEBUG] File type: " . ($file['type'] ?? 'N/A'));
        error_log("[UPLOAD DEBUG] File tmp_name: " . ($file['tmp_name'] ?? 'N/A'));
        error_log("[UPLOAD DEBUG] Upload dir: " . $this->uploadDir);
        error_log("[UPLOAD DEBUG] Upload dir exists: " . (is_dir($this->uploadDir) ? 'Yes' : 'No'));
        error_log("[UPLOAD DEBUG] Upload dir writable: " . (is_writable($this->uploadDir) ? 'Yes' : 'No'));
        
        // Cek error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = $this->getUploadErrorMessage($file['error']);
            error_log("[UPLOAD ERROR] Upload error: {$errorMsg} (code: {$file['error']})");
            return ['success' => false, 'message' => $errorMsg];
        }
        
        // Cek PHP upload limits
        $uploadMaxFilesize = $this->parseSize(ini_get('upload_max_filesize'));
        $postMaxSize = $this->parseSize(ini_get('post_max_size'));
        $phpMaxSize = min($uploadMaxFilesize, $postMaxSize);
        
        if ($file['size'] > $phpMaxSize) {
            $errorMsg = "File terlalu besar. Maksimal PHP: " . $this->formatBytes($phpMaxSize);
            error_log("[UPLOAD ERROR] {$errorMsg}");
            return ['success' => false, 'message' => $errorMsg];
        }
        
        // Validasi file size (aplikasi limit)
        if ($file['size'] > $this->maxFileSize) {
            $errorMsg = 'File terlalu besar. Maksimal ' . $this->formatBytes($this->maxFileSize);
            error_log("[UPLOAD ERROR] {$errorMsg}");
            return ['success' => false, 'message' => $errorMsg];
        }
        
        // Validasi file type
        $validation = $this->validateFileType($file);
        if (!$validation['success']) {
            error_log("[UPLOAD ERROR] File type validation failed: " . ($validation['message'] ?? 'Unknown'));
            return $validation;
        }
        
        // Validasi file content
        $contentValidation = $this->validateFileContent($file);
        if (!$contentValidation['success']) {
            error_log("[UPLOAD ERROR] File content validation failed: " . ($contentValidation['message'] ?? 'Unknown'));
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
        error_log("[UPLOAD DEBUG] Attempting to move file from: {$file['tmp_name']} to: {$targetPath}");
        error_log("[UPLOAD DEBUG] Source file exists: " . (file_exists($file['tmp_name']) ? 'Yes' : 'No'));
        error_log("[UPLOAD DEBUG] Target directory writable: " . (is_writable($this->uploadDir) ? 'Yes' : 'No'));
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Set proper permissions
            chmod($targetPath, 0644);
            
            // Verify file was saved
            if (file_exists($targetPath)) {
                error_log("[UPLOAD SUCCESS] File verified exists at: {$targetPath}");
            } else {
                error_log("[UPLOAD WARNING] move_uploaded_file returned true but file not found at: {$targetPath}");
            }
            
            // Generate relative path untuk return (dari public/)
            $relativePath = 'storage/uploads/';
            if (strpos($this->uploadDir, '/uploads/') !== false) {
                $subdir = str_replace(__DIR__ . '/../../public/storage/uploads/', '', $this->uploadDir);
                if (!empty($subdir)) {
                    $relativePath .= trim($subdir, '/') . '/';
                }
            }
            $returnPath = $relativePath . $secureFileName;
            
            // Log successful upload
            $this->logUpload($secureFileName, $file['size'], $_SESSION['user']['username'] ?? 'unknown');
            error_log("[UPLOAD SUCCESS] File saved: {$secureFileName} ({$this->formatBytes($file['size'])})");
            error_log("[UPLOAD SUCCESS] Returning path: {$returnPath}");
            
            error_log("[UPLOAD SUCCESS] Full return array: " . json_encode([
                'success' => true,
                'filename' => $secureFileName,
                'path' => $returnPath,
                'size' => $file['size']
            ]));
            
            return [
                'success' => true,
                'filename' => $secureFileName,
                'original_name' => $file['name'], // Simpan nama file asli
                'path' => $returnPath,
                'size' => $file['size']
            ];
        } else {
            $errorMsg = 'Gagal menyimpan file. Periksa permission folder upload.';
            error_log("[UPLOAD ERROR] {$errorMsg} - Target: {$targetPath}");
            error_log("[UPLOAD ERROR] Directory writable: " . (is_writable($this->uploadDir) ? 'Yes' : 'No'));
            error_log("[UPLOAD ERROR] Directory exists: " . (is_dir($this->uploadDir) ? 'Yes' : 'No'));
            error_log("[UPLOAD ERROR] Source file readable: " . (is_readable($file['tmp_name']) ? 'Yes' : 'No'));
            return ['success' => false, 'message' => $errorMsg];
        }
    }
    
    private function validateFileType($file) {
        // Get MIME type from file
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Allowed MIME types - hardcode untuk memastikan PDF dan Word terdeteksi
        $allowedMimes = [
            'image/jpeg', 'image/jpg', 'image/pjpeg', 
            'image/png', 'image/gif',
            'application/pdf', 'application/x-pdf',
            'application/msword', 'application/vnd.ms-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip' // untuk docx yang kadang terdeteksi sebagai zip
        ];
        
        // Also get from env/config if available
        $envAllowedMimes = explode(',', $this->allowedTypes);
        $envAllowedMimes = array_map('trim', $envAllowedMimes);
        $allowedMimes = array_unique(array_merge($allowedMimes, $envAllowedMimes));
        
        // Allowed extensions
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
        
        error_log("[VALIDATE TYPE] File extension: {$extension}");
        error_log("[VALIDATE TYPE] Detected MIME type: {$mimeType}");
        error_log("[VALIDATE TYPE] Allowed MIME types: " . implode(', ', $allowedMimes));
        
        // Validate MIME type
        if (!in_array($mimeType, $allowedMimes)) {
            error_log("[VALIDATE TYPE] MIME type NOT in allowed list!");
            return ['success' => false, 'message' => "Tipe file tidak diizinkan. File Anda: {$mimeType}. Ekstensi: {$extension}. Hanya file PDF, JPG, PNG, DOC, DOCX yang diizinkan."];
        }
        
        // Validate extension
        if (!in_array($extension, $allowedExtensions)) {
            return ['success' => false, 'message' => 'Ekstensi file tidak diizinkan. Hanya ' . implode(', ', $allowedExtensions)];
        }
        
        // Double check MIME type vs extension (lebih fleksibel untuk variasi MIME type)
        $expectedMimes = [
            'jpg' => ['image/jpeg', 'image/jpg', 'image/pjpeg'],
            'jpeg' => ['image/jpeg', 'image/jpg', 'image/pjpeg'],
            'png' => ['image/png', 'image/x-png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf', 'application/x-pdf'],
            'doc' => ['application/msword', 'application/vnd.ms-word'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip']
        ];
        
        if (isset($expectedMimes[$extension])) {
            $allowedMimesForExt = is_array($expectedMimes[$extension]) 
                ? $expectedMimes[$extension] 
                : [$expectedMimes[$extension]];
            
            if (!in_array($mimeType, $allowedMimesForExt)) {
                error_log("[UPLOAD ERROR] MIME type mismatch - Extension: {$extension}, MIME: {$mimeType}, Expected: " . implode(', ', $allowedMimesForExt));
                return ['success' => false, 'message' => 'File tidak valid - MIME type tidak sesuai dengan ekstensi. Ekstensi: ' . $extension . ', MIME: ' . $mimeType];
            }
        }
        
        return ['success' => true];
    }
    
    private function validateFileContent($file) {
        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Get MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // Validate images
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return ['success' => false, 'message' => 'File bukan gambar yang valid'];
            }
            
            // Check image dimensions (meningkatkan limit untuk foto resolusi tinggi)
            $maxWidth = 12000;
            $maxHeight = 12000;
            if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
                error_log("[UPLOAD ERROR] Image dimensions too large: {$imageInfo[0]}x{$imageInfo[1]} pixels (max: {$maxWidth}x{$maxHeight})");
                return ['success' => false, 'message' => "Gambar terlalu besar. Maksimal {$maxWidth}x{$maxHeight} pixels. Ukuran gambar Anda: {$imageInfo[0]}x{$imageInfo[1]} pixels"];
            }
            
            // Log image info untuk debugging
            error_log("[UPLOAD DEBUG] Image dimensions: {$imageInfo[0]}x{$imageInfo[1]} pixels, MIME: {$imageInfo['mime']}");
        }
        
        // Validate PDF
        if ($extension === 'pdf') {
            // Check PDF magic bytes (%PDF)
            $fileHandle = fopen($file['tmp_name'], 'rb');
            $header = fread($fileHandle, 4);
            fclose($fileHandle);
            
            if ($header !== '%PDF') {
                return ['success' => false, 'message' => 'File bukan PDF yang valid'];
            }
        }
        
        // Validate Word documents (doc/docx)
        if (in_array($extension, ['doc', 'docx'])) {
            // For DOC files, check for OLE header (d0 cf 11 e0)
            if ($extension === 'doc') {
                $fileHandle = fopen($file['tmp_name'], 'rb');
                $header = fread($fileHandle, 4);
                fclose($fileHandle);
                
                $oleHeader = "\xd0\xcf\x11\xe0";
                if ($header !== $oleHeader) {
                    return ['success' => false, 'message' => 'File bukan dokumen Word (.doc) yang valid'];
                }
            }
            
            // For DOCX files, check if it's a valid ZIP archive (DOCX is a ZIP file)
            if ($extension === 'docx') {
                $fileHandle = fopen($file['tmp_name'], 'rb');
                $header = fread($fileHandle, 2);
                fclose($fileHandle);
                
                // ZIP files start with PK (50 4B)
                if ($header !== 'PK') {
                    return ['success' => false, 'message' => 'File bukan dokumen Word (.docx) yang valid'];
                }
            }
        }
        
        // Check for malicious content (basic check) - only for text-based files
        // Skip this check for binary files like images, PDFs, and Word docs to avoid false positives
        if (in_array($extension, ['pdf', 'doc', 'docx'])) {
            // For binary files, we rely on MIME type and magic bytes validation above
            return ['success' => true];
        }
        
        // For images, check for embedded scripts (less common but possible)
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
    
    private function parseSize($size) {
        // Parse PHP ini size (e.g., "10M", "5G", "1024K")
        $size = trim($size);
        $last = strtolower($size[strlen($size)-1]);
        $value = (int) $size;
        
        switch($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    private function logUpload($filename, $size, $username) {
        $logMessage = date('Y-m-d H:i:s') . " - File uploaded: {$filename} ({$this->formatBytes($size)}) by {$username}";
        error_log($logMessage, 3, __DIR__ . '/../../storage/logs/upload.log');
    }
    
    public function deleteFile($filename) {
        // Sanitize filename untuk keamanan
        $filename = basename($filename); // Hanya ambil nama file, hapus path
        $filename = str_replace('..', '', $filename); // Prevent directory traversal
        
        // Cek di subdirectory terlebih dahulu
        $filePath = $this->uploadDir . $filename;
        
        error_log("[DELETE FILE] Attempting to delete: {$filename}");
        error_log("[DELETE FILE] Full path: {$filePath}");
        error_log("[DELETE FILE] File exists: " . (file_exists($filePath) ? 'Yes' : 'No'));
        
        if (file_exists($filePath)) {
            if (is_file($filePath)) {
                if (unlink($filePath)) {
                    error_log("[DELETE FILE] ✓ File berhasil dihapus: {$filename}");
                    $this->logUpload("DELETED: {$filename}", 0, $_SESSION['user']['username'] ?? 'unknown');
                    return true;
                } else {
                    error_log("[DELETE FILE] ✗ Gagal menghapus file (unlink failed): {$filename}");
                    return false;
                }
            } else {
                error_log("[DELETE FILE] ✗ Path bukan file: {$filePath}");
                return false;
            }
        } else {
            // Backward compatibility: cek di root uploads jika tidak ditemukan di subdirectory
            $rootDir = __DIR__ . '/../../public/storage/uploads/';
            $rootFilePath = $rootDir . $filename;
            
            if (file_exists($rootFilePath) && is_file($rootFilePath)) {
                error_log("[DELETE FILE] File ditemukan di root (backward compatibility): {$rootFilePath}");
                if (unlink($rootFilePath)) {
                    error_log("[DELETE FILE] ✓ File berhasil dihapus dari root: {$filename}");
                    $this->logUpload("DELETED: {$filename}", 0, $_SESSION['user']['username'] ?? 'unknown');
                    return true;
                } else {
                    error_log("[DELETE FILE] ✗ Gagal menghapus file dari root: {$filename}");
                    return false;
                }
            }
            
            error_log("[DELETE FILE] ✗ File tidak ditemukan: {$filePath}");
            // Return true karena file sudah tidak ada (tidak perlu error)
            return true;
        }
    }
}
