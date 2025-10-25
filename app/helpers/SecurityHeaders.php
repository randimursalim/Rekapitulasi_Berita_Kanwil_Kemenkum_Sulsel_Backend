<?php
// app/helpers/SecurityHeaders.php

class SecurityHeaders {
    
    public static function setSecurityHeaders() {
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' cdnjs.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' cdnjs.cloudflare.com fonts.googleapis.com; " .
               "font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com; " .
               "img-src 'self' data:; " .
               "connect-src 'self';";
        header("Content-Security-Policy: {$csp}");
        
        // Remove server signature
        header_remove('Server');
        header_remove('X-Powered-By');
        
        // Additional security headers
        header('X-Permitted-Cross-Domain-Policies: none');
        header('Cross-Origin-Embedder-Policy: require-corp');
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-origin');
    }
    
    public static function setHttpsHeaders() {
        // HTTPS Security Headers
        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }
    
    private static function isHttps() {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ||
               isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ||
               isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443;
    }
    
    public static function setCorsHeaders() {
        // CORS Headers (if needed)
        header('Access-Control-Allow-Origin: ' . self::getAllowedOrigin());
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 86400');
    }
    
    private static function getAllowedOrigin() {
        $allowedOrigins = [
            'http://localhost',
            'https://localhost',
            'http://127.0.0.1',
            'https://127.0.0.1'
        ];
        
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array($origin, $allowedOrigins)) {
            return $origin;
        }
        
        return 'null';
    }
    
    public static function setCacheHeaders($maxAge = 3600) {
        // Cache Control Headers
        header("Cache-Control: public, max-age={$maxAge}");
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
    }
    
    public static function setNoCacheHeaders() {
        // No Cache Headers
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}
