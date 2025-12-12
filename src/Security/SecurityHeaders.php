<?php
/**
 * Security Headers Handler
 * Sets up security headers to protect against common web vulnerabilities
 */

namespace src\Security;

class SecurityHeaders {
    
    /**
     * Set all security headers
     */
    public static function setSecurityHeaders() {
        // Prevent clickjacking attacks
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable browser XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Control what information is passed to external sites
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // HTTPS only
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        
        // Control feature access for the document
        header('Permissions-Policy: accelerometer=(), camera=(), microphone=(), geolocation=()');
        
        // Content Security Policy
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdn.tailwindcss.com; style-src \'self\' \'unsafe-inline\' https://cdn.tailwindcss.com; img-src \'self\' data:; font-src \'self\'; connect-src \'self\'');
    }

    /**
     * Set CORS headers
     */
    public static function setCORSHeaders($allowed_origins = ['https://localhost', 'http://localhost']) {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Check if origin is allowed
        if (in_array($origin, $allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Max-Age: 3600');
            header('Access-Control-Allow-Credentials: true');
        }
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /**
     * Validate CSRF token
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>
