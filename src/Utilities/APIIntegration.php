<?php
/**
 * API Integration Helper
 * Provides utilities for external API integrations
 */

namespace src\Utilities;

class APIIntegration {
    
    private static $timeout = 10;
    private static $verify_ssl = true;

    /**
     * Make GET request to external API
     */
    public static function get($url, $headers = [], $params = []) {
        return self::request('GET', $url, null, $headers, $params);
    }

    /**
     * Make POST request to external API
     */
    public static function post($url, $data = [], $headers = []) {
        return self::request('POST', $url, $data, $headers);
    }

    /**
     * Make PUT request to external API
     */
    public static function put($url, $data = [], $headers = []) {
        return self::request('PUT', $url, $data, $headers);
    }

    /**
     * Make DELETE request to external API
     */
    public static function delete($url, $headers = []) {
        return self::request('DELETE', $url, null, $headers);
    }

    /**
     * Generic request handler using cURL
     */
    private static function request($method, $url, $data = null, $headers = [], $params = []) {
        // Add query parameters to URL
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::$timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => self::$verify_ssl,
            CURLOPT_USERAGENT => 'MNCHS-Grade-Portal/1.0',
        ]);

        // Add headers
        $default_headers = ['Content-Type: application/json'];
        $headers = array_merge($default_headers, $headers);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        // Add body for POST/PUT requests
        if ($data && ($method === 'POST' || $method === 'PUT')) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $http_code
            ];
        }

        return [
            'success' => true,
            'data' => json_decode($response, true),
            'http_code' => $http_code,
            'raw_response' => $response
        ];
    }

    /**
     * Webhook handler - receive data from external services
     */
    public static function handleWebhook($action, $callback) {
        $method = $_SERVER['REQUEST_METHOD'];
        $headers = getallheaders();
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);

        // Verify webhook signature if provided
        if (isset($headers['X-Webhook-Signature'])) {
            $signature = $headers['X-Webhook-Signature'];
            $secret = getenv('WEBHOOK_SECRET') ?: 'webhook_secret';

            $computed_signature = hash_hmac('sha256', $body, $secret);
            if ($signature !== $computed_signature) {
                return [
                    'success' => false,
                    'message' => 'Invalid webhook signature'
                ];
            }
        }

        // Call the callback function
        if (is_callable($callback)) {
            return $callback($method, $headers, $data);
        }

        return [
            'success' => false,
            'message' => 'Invalid callback'
        ];
    }

    /**
     * Send webhook to external service
     */
    public static function sendWebhook($url, $data, $secret = null) {
        $payload = json_encode($data);
        
        $headers = [
            'Content-Type: application/json',
            'X-Webhook-Event: grade-portal-event'
        ];

        // Add signature if secret is provided
        if ($secret) {
            $signature = hash_hmac('sha256', $payload, $secret);
            $headers[] = 'X-Webhook-Signature: ' . $signature;
        }

        return self::request('POST', $url, $data, $headers);
    }
}
?>
