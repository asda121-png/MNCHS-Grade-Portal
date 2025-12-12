<?php
/**
 * API Authentication Middleware
 * Handles JWT token validation for API requests
 */

namespace src\Middleware;

use src\Security\JWTHandler;
use src\Security\RateLimiter;

class APIAuthMiddleware {

    /**
     * Verify API token from Authorization header
     */
    public static function verifyAPIToken() {
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? '';

        if (empty($auth_header)) {
            return [
                'success' => false,
                'message' => 'Authorization header missing'
            ];
        }

        // Extract token from "Bearer <token>"
        if (preg_match('/Bearer\s+(\S+)/', $auth_header, $matches)) {
            $token = $matches[1];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid authorization header format'
            ];
        }

        // Verify token
        $payload = \src\Security\JWTHandler::verifyToken($token);

        if (!$payload) {
            return [
                'success' => false,
                'message' => 'Invalid or expired token'
            ];
        }

        return [
            'success' => true,
            'payload' => $payload
        ];
    }

    /**
     * Check rate limit
     */
    public static function checkRateLimit($user_id = null) {
        if (!\src\Security\RateLimiter::isAllowed()) {
            return [
                'success' => false,
                'message' => 'Rate limit exceeded',
                'remaining' => \src\Security\RateLimiter::getRemaining()
            ];
        }

        return [
            'success' => true,
            'remaining' => \src\Security\RateLimiter::getRemaining()
        ];
    }

    /**
     * Verify API key (alternative to JWT)
     */
    public static function verifyAPIKey() {
        $headers = getallheaders();
        $api_key = $headers['X-API-Key'] ?? '';

        if (empty($api_key)) {
            return [
                'success' => false,
                'message' => 'API key missing'
            ];
        }

        // Validate API key format (should be stored in database)
        if (!self::validateAPIKey($api_key)) {
            return [
                'success' => false,
                'message' => 'Invalid API key'
            ];
        }

        return [
            'success' => true
        ];
    }

    /**
     * Validate API key against database
     */
    private static function validateAPIKey($api_key) {
        // This would typically check against a database table of valid API keys
        // For now, return a basic validation
        return strlen($api_key) >= 32;
    }
}
?>
