<?php
/**
 * Example: Secure API Endpoint Implementation
 * Shows how to implement API security, authentication, and validation
 */

header('Content-Type: application/json');
session_start();

require_once '../../includes/config.php';
require_once '../../src/Security/JWTHandler.php';
require_once '../../src/Security/InputValidator.php';
require_once '../../src/Security/SecurityHeaders.php';
require_once '../../src/Security/RateLimiter.php';
require_once '../../src/Middleware/APIAuthMiddleware.php';
require_once '../../src/Utilities/APIResponse.php';

use src\Security\JWTHandler;
use src\Security\InputValidator;
use src\Security\SecurityHeaders;
use src\Security\RateLimiter;
use src\Middleware\APIAuthMiddleware;
use src\Utilities\APIResponse;

// 1. SET SECURITY HEADERS
SecurityHeaders::setSecurityHeaders();
SecurityHeaders::setCORSHeaders(['https://localhost', 'http://localhost']);

// 2. CHECK RATE LIMITING
if (!RateLimiter::isAllowed()) {
    APIResponse::error('Rate limit exceeded. Maximum 100 requests per hour.', 429);
}

try {
    $request_method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    // 3. VERIFY AUTHENTICATION
    // You can use either session-based auth or JWT token auth
    
    // Option A: Session-based authentication (for traditional web apps)
    if (!isset($_SESSION['user_id']) && !$_GET['skip_auth']) {
        // Option B: JWT token authentication (for APIs)
        $auth_result = APIAuthMiddleware::verifyAPIToken();
        if (!$auth_result['success']) {
            APIResponse::unauthorized($auth_result['message']);
        }
        $user_payload = $auth_result['payload'];
        $user_id = $user_payload['user_id'];
        $user_type = $user_payload['user_type'];
    } else {
        $user_id = $_SESSION['user_id'] ?? null;
        $user_type = $_SESSION['user_type'] ?? null;
    }

    // 4. VALIDATE INPUT
    if ($request_method === 'POST' && $action === 'create_grade') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = [];
        
        // Validate student ID
        if (empty($data['student_id']) || !InputValidator::validateInteger($data['student_id'])) {
            $errors['student_id'] = 'Valid student ID is required';
        }
        
        // Validate grade
        if (empty($data['grade']) || !InputValidator::validateInteger($data['grade']) || 
            $data['grade'] < 0 || $data['grade'] > 100) {
            $errors['grade'] = 'Grade must be between 0 and 100';
        }
        
        // Validate subject (sanitize string)
        if (empty($data['subject'])) {
            $errors['subject'] = 'Subject is required';
        } else {
            $subject = InputValidator::sanitizeString($data['subject']);
        }
        
        // Check for SQL injection attempts
        if (InputValidator::detectSQLInjection($data['subject'] ?? '')) {
            $errors['subject'] = 'Invalid characters detected';
        }
        
        if (!empty($errors)) {
            APIResponse::validationError($errors);
        }

        // 5. PROCESS REQUEST
        try {
            global $db;
            
            $student_id = (int)$data['student_id'];
            $grade = (int)$data['grade'];
            
            // Insert grade into database
            $query = "INSERT INTO grades (student_id, grade, subject, teacher_id, created_at) 
                      VALUES (?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($query);
            
            if (!$stmt) {
                throw new Exception('Database error: ' . $db->error);
            }
            
            $stmt->bind_param('iisi', $student_id, $grade, $subject, $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to insert grade: ' . $stmt->error);
            }
            
            $grade_id = $db->insert_id;
            
            APIResponse::success(
                ['id' => $grade_id, 'student_id' => $student_id, 'grade' => $grade],
                'Grade created successfully',
                201
            );
            
        } catch (Exception $e) {
            APIResponse::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    // 6. GET GRADES ENDPOINT
    if ($request_method === 'GET' && $action === 'get_grades') {
        try {
            global $db;
            
            // Get pagination parameters
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $per_page = isset($_GET['per_page']) ? min(100, (int)$_GET['per_page']) : 10;
            $offset = ($page - 1) * $per_page;
            
            // Build query
            $query = "SELECT id, student_id, grade, subject, created_at FROM grades 
                     WHERE teacher_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
            
            $stmt = $db->prepare($query);
            if (!$stmt) {
                throw new Exception('Database error: ' . $db->error);
            }
            
            $stmt->bind_param('iii', $user_id, $per_page, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $grades = [];
            while ($row = $result->fetch_assoc()) {
                $grades[] = $row;
            }
            
            // Get total count
            $count_query = "SELECT COUNT(*) as total FROM grades WHERE teacher_id = ?";
            $count_stmt = $db->prepare($count_query);
            $count_stmt->bind_param('i', $user_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_row = $count_result->fetch_assoc();
            $total = $count_row['total'];
            
            APIResponse::paginated($grades, $total, $page, $per_page, 'Grades retrieved successfully');
            
        } catch (Exception $e) {
            APIResponse::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    // 7. UPDATE GRADE ENDPOINT
    if ($request_method === 'PUT' && $action === 'update_grade') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = [];
        
        if (empty($data['id'])) {
            $errors['id'] = 'Grade ID is required';
        }
        
        if (!empty($data['grade']) && (!InputValidator::validateInteger($data['grade']) || 
            $data['grade'] < 0 || $data['grade'] > 100)) {
            $errors['grade'] = 'Grade must be between 0 and 100';
        }
        
        if (!empty($errors)) {
            APIResponse::validationError($errors);
        }

        try {
            global $db;
            
            $grade_id = (int)$data['id'];
            $grade = (int)$data['grade'];
            
            $query = "UPDATE grades SET grade = ? WHERE id = ? AND teacher_id = ?";
            $stmt = $db->prepare($query);
            
            if (!$stmt) {
                throw new Exception('Database error: ' . $db->error);
            }
            
            $stmt->bind_param('iii', $grade, $grade_id, $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update grade: ' . $stmt->error);
            }
            
            if ($stmt->affected_rows === 0) {
                APIResponse::notFound('Grade not found or you do not have permission');
            }
            
            APIResponse::success(['id' => $grade_id, 'grade' => $grade], 'Grade updated successfully');
            
        } catch (Exception $e) {
            APIResponse::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    // 8. DELETE GRADE ENDPOINT
    if ($request_method === 'DELETE' && $action === 'delete_grade') {
        $grade_id = (int)($_GET['id'] ?? 0);
        
        if ($grade_id <= 0) {
            APIResponse::validationError(['id' => 'Valid grade ID is required']);
        }

        try {
            global $db;
            
            $query = "DELETE FROM grades WHERE id = ? AND teacher_id = ?";
            $stmt = $db->prepare($query);
            
            if (!$stmt) {
                throw new Exception('Database error: ' . $db->error);
            }
            
            $stmt->bind_param('ii', $grade_id, $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to delete grade: ' . $stmt->error);
            }
            
            if ($stmt->affected_rows === 0) {
                APIResponse::notFound('Grade not found or you do not have permission');
            }
            
            APIResponse::success([], 'Grade deleted successfully');
            
        } catch (Exception $e) {
            APIResponse::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    // Invalid action
    APIResponse::error('Invalid action', 400);

} catch (Exception $e) {
    APIResponse::error('Server error', 500);
}
?>
