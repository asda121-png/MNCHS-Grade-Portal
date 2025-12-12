<?php
/**
 * Authentication Middleware
 * Verifies user is authenticated before accessing protected resources
 */

namespace src\Middleware;

class AuthMiddleware {
    
    /**
     * Check if user is authenticated
     */
    public static function authenticate() {
        if (!isset($_SESSION['user_id'])) {
            return [
                'success' => false,
                'message' => 'Not authenticated',
                'redirect' => '/server/auth/login.html'
            ];
        }
        
        return [
            'success' => true
        ];
    }
    
    /**
     * Verify session timeout
     */
    public static function checkSessionTimeout() {
        $sessionTimeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 3600;
        
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > $sessionTimeout) {
                session_destroy();
                return [
                    'success' => false,
                    'message' => 'Session expired',
                    'redirect' => '/server/auth/login.html'
                ];
            }
        }
        
        $_SESSION['last_activity'] = time();
        
        return [
            'success' => true
        ];
    }
}
