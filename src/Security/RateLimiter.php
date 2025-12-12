<?php
/**
 * Rate Limiter
 * Prevents abuse by limiting API requests per user/IP
 */

namespace src\Security;

class RateLimiter {
    private static $cache_dir = __DIR__ . '/../../storage/rate_limit';
    private static $requests_limit = 100;
    private static $time_window = 3600; // 1 hour

    /**
     * Initialize cache directory
     */
    private static function initCacheDir() {
        if (!is_dir(self::$cache_dir)) {
            mkdir(self::$cache_dir, 0755, true);
        }
    }

    /**
     * Get identifier for rate limiting
     */
    private static function getIdentifier($identifier = null) {
        if ($identifier) {
            return $identifier;
        }
        
        // Use user ID if authenticated, otherwise use IP
        if (isset($_SESSION['user_id'])) {
            return 'user_' . $_SESSION['user_id'];
        }
        
        return 'ip_' . self::getClientIP();
    }

    /**
     * Get client IP address
     */
    private static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Check if request is allowed
     */
    public static function isAllowed($identifier = null, $limit = null, $window = null) {
        self::initCacheDir();
        
        $id = self::getIdentifier($identifier);
        $limit = $limit ?: self::$requests_limit;
        $window = $window ?: self::$time_window;
        $cache_file = self::$cache_dir . '/' . md5($id) . '.json';
        
        $now = time();
        $requests = [];
        
        // Load existing requests
        if (file_exists($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            if ($data) {
                // Filter out old requests
                $requests = array_filter($data['requests'], function($timestamp) use ($now, $window) {
                    return $timestamp > ($now - $window);
                });
            }
        }
        
        // Check limit
        if (count($requests) >= $limit) {
            return false;
        }
        
        // Add current request
        $requests[] = $now;
        
        // Save updated requests
        file_put_contents($cache_file, json_encode([
            'identifier' => $id,
            'requests' => $requests,
            'created_at' => $now
        ]));
        
        return true;
    }

    /**
     * Get remaining requests
     */
    public static function getRemaining($identifier = null, $limit = null) {
        self::initCacheDir();
        
        $id = self::getIdentifier($identifier);
        $limit = $limit ?: self::$requests_limit;
        $window = self::$time_window;
        $cache_file = self::$cache_dir . '/' . md5($id) . '.json';
        
        $now = time();
        $requests = [];
        
        if (file_exists($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            if ($data) {
                $requests = array_filter($data['requests'], function($timestamp) use ($now, $window) {
                    return $timestamp > ($now - $window);
                });
            }
        }
        
        return max(0, $limit - count($requests));
    }

    /**
     * Reset rate limit for identifier
     */
    public static function reset($identifier = null) {
        self::initCacheDir();
        
        $id = self::getIdentifier($identifier);
        $cache_file = self::$cache_dir . '/' . md5($id) . '.json';
        
        if (file_exists($cache_file)) {
            unlink($cache_file);
        }
        
        return true;
    }
}
?>
