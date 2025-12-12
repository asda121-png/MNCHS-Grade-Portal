<?php
/**
 * Teacher Management API
 * Handles CRUD operations for teacher data including adviser role assignments
 */

header('Content-Type: application/json');

// Start session
session_start();

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Include database configuration
require_once '../../config/database.php';

try {
    $request_method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    if ($request_method === 'POST' && $action === 'update_teacher') {
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['teacher_id'])) {
            throw new Exception('Invalid request data');
        }

        $teacher_id = (int)$data['teacher_id'];
        $is_adviser = isset($data['is_adviser']) ? (int)$data['is_adviser'] : 0;
        $adviser_class_id = $data['adviser_class_id'] ?? null;

        // Validate adviser class if teacher is being set as adviser
        if ($is_adviser && $adviser_class_id) {
            // Check if another teacher is already adviser for this class
            $check_query = "SELECT id FROM teachers WHERE adviser_class_id = ? AND is_adviser = 1 AND id != ?";
            $stmt = $db->prepare($check_query);
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $db->error);
            }
            $stmt->bind_param('ii', $adviser_class_id, $teacher_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception('Another teacher is already assigned as adviser to this class');
            }
            $stmt->close();

            // Validate class exists
            $class_check = "SELECT class_id FROM classes WHERE class_id = ?";
            $stmt = $db->prepare($class_check);
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $db->error);
            }
            $stmt->bind_param('i', $adviser_class_id);
            $stmt->execute();
            $class_result = $stmt->get_result();
            
            if ($class_result->num_rows === 0) {
                throw new Exception('Invalid class ID');
            }
            $stmt->close();
        }

        // Remove adviser assignment if not adviser
        if (!$is_adviser) {
            $adviser_class_id = null;
        }

        // Update teacher record
        $update_query = "UPDATE teachers SET is_adviser = ?, adviser_class_id = ? WHERE id = ?";
        $stmt = $db->prepare($update_query);
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $db->error);
        }
        
        // Handle NULL value for adviser_class_id
        $adviser_class_id_param = $adviser_class_id ?? null;
        if ($adviser_class_id === null) {
            $stmt->bind_param('iii', $is_adviser, $adviser_class_id_param, $teacher_id);
        } else {
            $stmt->bind_param('iii', $is_adviser, $adviser_class_id, $teacher_id);
        }
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update teacher: ' . $stmt->error);
        }
        
        $stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Teacher role updated successfully',
            'data' => [
                'teacher_id' => $teacher_id,
                'is_adviser' => $is_adviser,
                'adviser_class_id' => $adviser_class_id
            ]
        ]);

    } else if ($request_method === 'GET' && $action === 'get_classes') {
        // Get available classes for adviser assignment
        $query = "SELECT class_id, class_name, section FROM classes ORDER BY class_name ASC";
        $result = $db->query($query);

        if (!$result) {
            throw new Exception('Database query error: ' . $db->error);
        }

        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $classes[] = [
                'id' => $row['class_id'],
                'name' => $row['class_name'],
                'section' => $row['section']
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $classes
        ]);

    } else if ($request_method === 'GET' && $action === 'get_teacher_role') {
        // Get current role info for a teacher
        $teacher_id = (int)($_GET['teacher_id'] ?? 0);

        if (!$teacher_id) {
            throw new Exception('Teacher ID is required');
        }

        $query = "
            SELECT 
                t.id,
                t.is_adviser,
                t.adviser_class_id,
                c.class_name,
                c.section
            FROM teachers t
            LEFT JOIN classes c ON t.adviser_class_id = c.class_id
            WHERE t.id = ?
        ";

        $stmt = $db->prepare($query);
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $db->error);
        }

        $stmt->bind_param('i', $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Teacher not found');
        }

        $teacher = $result->fetch_assoc();
        $stmt->close();

        echo json_encode([
            'success' => true,
            'data' => [
                'teacher_id' => $teacher['id'],
                'is_adviser' => (bool)$teacher['is_adviser'],
                'adviser_class_id' => $teacher['adviser_class_id'],
                'adviser_class_name' => $teacher['class_name'] ?? null,
                'adviser_section' => $teacher['section'] ?? null
            ]
        ]);

    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$db->close();
?>
