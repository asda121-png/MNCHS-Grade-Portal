<?php
/**
 * API Response Handler
 * Standardizes API responses across all endpoints
 */

namespace src\Utilities;

class APIResponse {
    
    /**
     * Send success response
     */
    public static function success($data = [], $message = 'Success', $status_code = 200) {
        http_response_code($status_code);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ]);
        exit;
    }

    /**
     * Send error response
     */
    public static function error($message = 'Error', $status_code = 400, $errors = []) {
        http_response_code($status_code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => time()
        ]);
        exit;
    }

    /**
     * Send paginated response
     */
    public static function paginated($data = [], $total = 0, $page = 1, $per_page = 10, $message = 'Success') {
        $total_pages = ceil($total / $per_page);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => $total_pages
            ],
            'timestamp' => time()
        ]);
        exit;
    }

    /**
     * Send validation error response
     */
    public static function validationError($errors = []) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors,
            'timestamp' => time()
        ]);
        exit;
    }

    /**
     * Send unauthorized response
     */
    public static function unauthorized($message = 'Unauthorized') {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'timestamp' => time()
        ]);
        exit;
    }

    /**
     * Send forbidden response
     */
    public static function forbidden($message = 'Forbidden') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'timestamp' => time()
        ]);
        exit;
    }

    /**
     * Send not found response
     */
    public static function notFound($message = 'Resource not found') {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'timestamp' => time()
        ]);
        exit;
    }
}
?>
