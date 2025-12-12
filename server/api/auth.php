<?php
/**
 * API Authentication Endpoint
 * Handles JWT token generation and validation
 */

header('Content-Type: application/json');
session_start();

require_once '../../includes/config.php';
require_once '../../src/Security/JWTHandler.php';
require_once '../../src/Security/InputValidator.php';
require_once '../../src/Security/SecurityHeaders.php';

use src\Security\JWTHandler;
use src\Security\InputValidator;
use src\Security\SecurityHeaders;

// Set security headers
SecurityHeaders::setSecurityHeaders();

try {
    $request_method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    // Handle preflight requests
    if ($request_method === 'OPTIONS') {
        SecurityHeaders::setCORSHeaders();
        http_response_code(200);
        exit;
    }

    // Generate token from session
    if ($request_method === 'POST' && $action === 'generate_token') {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }

        $token = JWTHandler::generateToken([
            'user_id' => $_SESSION['user_id'],
            'user_type' => $_SESSION['user_type'],
            'username' => $_SESSION['username'] ?? 'Unknown'
        ]);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'token' => $token,
            'expires_in' => 86400,
            'token_type' => 'Bearer'
        ]);
        exit;
    }

    // Verify token
    if ($request_method === 'POST' && $action === 'verify_token') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['token'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Token not provided']);
            exit;
        }

        $token = $data['token'];
        $payload = JWTHandler::verifyToken($token);

        if (!$payload) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
            exit;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'payload' => $payload
        ]);
        exit;
    }

    // Refresh token
    if ($request_method === 'POST' && $action === 'refresh_token') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['token'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Token not provided']);
            exit;
        }

        $token = JWTHandler::refreshToken($data['token']);

        if (!$token) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'token' => $token,
            'expires_in' => 86400
        ]);
        exit;
    }

    // Invalid action
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
}
?>
