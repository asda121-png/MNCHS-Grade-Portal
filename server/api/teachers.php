<?php
session_start();
header('Content-Type: application/json');

// Allow CORS for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../includes/config.php';

// Get action from query string
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_classes':
        getClasses();
        break;
    case 'get_departments':
        getDepartments();
        break;
    case 'get_specializations':
        getSpecializations();
        break;
    case 'update_teacher':
        updateTeacher();
        break;
    case 'add_teacher':
        addTeacher();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

/**
 * Get all classes for adviser dropdown
 */
function getClasses() {
    global $conn;
    
    try {
        // Get unique classes by grade_level and section using MIN(id) for the id
        $query = "SELECT MIN(id) as id, class_name as name, section, grade_level 
                  FROM classes 
                  GROUP BY grade_level, section, class_name
                  ORDER BY grade_level ASC, section ASC";
        
        $result = $conn->query($query);
        
        if (!$result) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
            return;
        }
        
        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $classes[] = [
                'id' => $row['id'],
                'name' => $row['name'] ?: 'Grade ' . $row['grade_level'],
                'section' => $row['section'],
                'grade_level' => $row['grade_level']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $classes
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Get departments list
 */
function getDepartments() {
    $departments = [
        ['id' => 'junior_high', 'name' => 'Junior High School'],
        ['id' => 'senior_high', 'name' => 'Senior High School'],
        ['id' => 'science', 'name' => 'Science Department'],
        ['id' => 'mathematics', 'name' => 'Mathematics Department'],
        ['id' => 'english', 'name' => 'English Department'],
        ['id' => 'filipino', 'name' => 'Filipino Department'],
        ['id' => 'social_studies', 'name' => 'Social Studies Department'],
        ['id' => 'mapeh', 'name' => 'MAPEH Department'],
        ['id' => 'tle', 'name' => 'TLE Department'],
        ['id' => 'values', 'name' => 'Values Education Department']
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $departments
    ]);
}

/**
 * Get specializations list
 */
function getSpecializations() {
    $specializations = [
        ['id' => 'english', 'name' => 'English'],
        ['id' => 'filipino', 'name' => 'Filipino'],
        ['id' => 'mathematics', 'name' => 'Mathematics'],
        ['id' => 'science', 'name' => 'Science'],
        ['id' => 'biology', 'name' => 'Biology'],
        ['id' => 'chemistry', 'name' => 'Chemistry'],
        ['id' => 'physics', 'name' => 'Physics'],
        ['id' => 'social_studies', 'name' => 'Social Studies'],
        ['id' => 'araling_panlipunan', 'name' => 'Araling Panlipunan'],
        ['id' => 'mapeh', 'name' => 'MAPEH'],
        ['id' => 'music', 'name' => 'Music'],
        ['id' => 'arts', 'name' => 'Arts'],
        ['id' => 'pe', 'name' => 'Physical Education'],
        ['id' => 'health', 'name' => 'Health'],
        ['id' => 'tle', 'name' => 'TLE'],
        ['id' => 'ict', 'name' => 'ICT'],
        ['id' => 'esp', 'name' => 'Edukasyon sa Pagpapakatao'],
        ['id' => 'values', 'name' => 'Values Education']
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $specializations
    ]);
}

/**
 * Update teacher details
 */
function updateTeacher() {
    global $conn;
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        return;
    }
    
    $teacherId = isset($input['teacher_id']) ? (int)$input['teacher_id'] : 0;
    $isAdviser = isset($input['is_adviser']) ? (int)$input['is_adviser'] : 0;
    $adviserClassId = isset($input['adviser_class_id']) && $input['adviser_class_id'] !== '' ? (int)$input['adviser_class_id'] : null;
    
    if ($teacherId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid teacher ID']);
        return;
    }
    
    try {
        // If setting as adviser, first clear any existing adviser for this class
        if ($isAdviser && $adviserClassId) {
            $clearStmt = $conn->prepare("UPDATE teachers SET is_adviser = 0, adviser_class_id = NULL WHERE adviser_class_id = ? AND id != ?");
            $clearStmt->bind_param("ii", $adviserClassId, $teacherId);
            $clearStmt->execute();
            $clearStmt->close();
        }
        
        // Update the teacher
        if ($isAdviser && $adviserClassId) {
            $stmt = $conn->prepare("UPDATE teachers SET is_adviser = ?, adviser_class_id = ? WHERE id = ?");
            $stmt->bind_param("iii", $isAdviser, $adviserClassId, $teacherId);
        } else {
            $stmt = $conn->prepare("UPDATE teachers SET is_adviser = 0, adviser_class_id = NULL WHERE id = ?");
            $stmt->bind_param("i", $teacherId);
        }
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Teacher updated successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update teacher: ' . $stmt->error]);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

/**
 * Add a new teacher
 */
function addTeacher() {
    global $conn;
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        return;
    }
    
    $employeeId = isset($input['employee_id']) ? trim($input['employee_id']) : '';
    $firstName = isset($input['first_name']) ? trim($input['first_name']) : '';
    $lastName = isset($input['last_name']) ? trim($input['last_name']) : '';
    $middleName = isset($input['middle_name']) ? trim($input['middle_name']) : '';
    $suffix = isset($input['suffix']) ? trim($input['suffix']) : '';
    $email = isset($input['email']) ? trim($input['email']) : '';
    $phone = isset($input['phone']) ? trim($input['phone']) : '';
    $department = isset($input['department']) ? trim($input['department']) : '';
    $specialization = isset($input['specialization']) ? trim($input['specialization']) : '';
    $isAdviser = isset($input['is_adviser']) ? (int)$input['is_adviser'] : 0;
    $adviserClassId = isset($input['adviser_class_id']) && $input['adviser_class_id'] !== '' ? (int)$input['adviser_class_id'] : null;
    
    // Validate required fields
    if (empty($employeeId) || empty($firstName) || empty($lastName) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Employee ID, First Name, Last Name, and Email are required']);
        return;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Generate a temporary password
        $tempPassword = password_hash('teacher123', PASSWORD_DEFAULT);
        
        // Create user account first
        $userStmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, user_type, is_active) VALUES (?, ?, ?, ?, ?, 'teacher', 1)");
        $username = strtolower($firstName . '.' . $lastName);
        $userStmt->bind_param("sssss", $username, $email, $tempPassword, $firstName, $lastName);
        
        if (!$userStmt->execute()) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to create user account: ' . $userStmt->error]);
            return;
        }
        
        $userId = $conn->insert_id;
        $userStmt->close();
        
        // Create teacher record
        $teacherStmt = $conn->prepare("INSERT INTO teachers (user_id, teacher_id, department, specialization, is_adviser, adviser_class_id, hire_date) VALUES (?, ?, ?, ?, ?, ?, CURDATE())");
        $teacherStmt->bind_param("isssis", $userId, $employeeId, $department, $specialization, $isAdviser, $adviserClassId);
        
        if (!$teacherStmt->execute()) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to create teacher record: ' . $teacherStmt->error]);
            return;
        }
        
        $teacherStmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Teacher added successfully',
            'user_id' => $userId
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
