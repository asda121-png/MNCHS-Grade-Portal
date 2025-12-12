<?php
/**
 * JWT (JSON Web Token) Handler
 * Handles creation, validation, and management of JWT tokens for API authentication
 */

namespace src\Security;

class JWTHandler {
    private static $secret_key = 'MNCHS_Grade_Portal_Secret_2024'; // Should be in .env file
    private static $algorithm = 'HS256';
    private static $expiration = 86400; // 24 hours

    /**
     * Generate a JWT token
     * 
     * @param array $payload The data to encode in the token
     * @return string The encoded JWT token
     */
    public static function generateToken($payload) {
        // Get secret from environment variable or use default
        $secret = getenv('JWT_SECRET') ?: self::$secret_key;
        
        // Create header
        $header = [
            'alg' => self::$algorithm,
            'typ' => 'JWT'
        ];

        // Add expiration and issued at time
        $payload['iat'] = time();
        $payload['exp'] = time() + self::$expiration;

        // Encode header and payload
        $header_encoded = self::base64url_encode(json_encode($header));
        $payload_encoded = self::base64url_encode(json_encode($payload));

        // Create signature
        $signature = self::base64url_encode(
            hash_hmac('sha256', "$header_encoded.$payload_encoded", $secret, true)
        );

        // Return the complete token
        return "$header_encoded.$payload_encoded.$signature";
    }

    /**
     * Verify and decode a JWT token
     * 
     * @param string $token The JWT token to verify
     * @return array|false Returns decoded payload if valid, false otherwise
     */
    public static function verifyToken($token) {
        $secret = getenv('JWT_SECRET') ?: self::$secret_key;
        
        // Split the token into parts
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }

        list($header_encoded, $payload_encoded, $signature) = $parts;

        // Verify signature
        $expected_signature = self::base64url_encode(
            hash_hmac('sha256', "$header_encoded.$payload_encoded", $secret, true)
        );

        if ($signature !== $expected_signature) {
            return false;
        }

        // Decode payload
        $payload = json_decode(self::base64url_decode($payload_encoded), true);

        if (!$payload) {
            return false;
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    /**
     * Base64 URL safe encode
     */
    private static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL safe decode
     */
    private static function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    /**
     * Refresh a token
     */
    public static function refreshToken($token) {
        $payload = self::verifyToken($token);
        if (!$payload) {
            return false;
        }

        // Remove old expiration data
        unset($payload['exp']);
        unset($payload['iat']);

        // Generate new token
        return self::generateToken($payload);
    }
}
?>
