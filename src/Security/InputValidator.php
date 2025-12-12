<?php
/**
 * Input Validator
 * Handles input validation and sanitization to prevent injection attacks
 */

namespace src\Security;

class InputValidator {
    
    /**
     * Sanitize string input
     */
    public static function sanitizeString($input) {
        if (!is_string($input)) {
            return $input;
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate integer
     */
    public static function validateInteger($input) {
        return filter_var($input, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validate URL
     */
    public static function validateURL($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate phone number (basic validation)
     */
    public static function validatePhoneNumber($phone) {
        return preg_match('/^[0-9\-\+\(\)\s]{7,}$/', $phone) === 1;
    }

    /**
     * Validate password strength
     */
    public static function validatePasswordStrength($password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        return preg_match($pattern, $password) === 1;
    }

    /**
     * Sanitize array of inputs
     */
    public static function sanitizeArray($input) {
        $sanitized = [];
        foreach ($input as $key => $value) {
            $sanitized[self::sanitizeString($key)] = self::sanitizeString($value);
        }
        return $sanitized;
    }

    /**
     * Check for SQL injection patterns
     */
    public static function detectSQLInjection($input) {
        $sql_patterns = [
            '/(\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER)\b)/i',
            '/(-{2}|\/\*|\*\/|xp_|sp_)/i',
            '/(;|\'|"|`)/i'
        ];
        
        foreach ($sql_patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validate input based on expected type
     */
    public static function validateInput($input, $type = 'string') {
        switch ($type) {
            case 'email':
                return self::validateEmail($input);
            case 'integer':
                return self::validateInteger($input);
            case 'url':
                return self::validateURL($input);
            case 'phone':
                return self::validatePhoneNumber($input);
            case 'password':
                return self::validatePasswordStrength($input);
            case 'string':
            default:
                return !self::detectSQLInjection($input);
        }
    }
}
?>
