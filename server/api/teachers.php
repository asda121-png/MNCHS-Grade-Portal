<?php
// --- CORS HEADERS FOR CROSS-ORIGIN SESSION SHARING ---
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
set_exception_handler(function($e) {
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
});


// --- CORS HEADERS ---
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
header('Content-Type: application/json');

// Use the fully-qualified name to avoid conflicts and for clarity
require_once '../../includes/config.php';
require_once '../../src/Utilities/APIResponse.php';
require_once __DIR__ . '/send_account_email.php'; // For sending credentials

// Import the APIResponse class to use it directly
use src\Utilities\APIResponse;

session_start();

// Security check: only admins can perform this action
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    // Use the imported class
    APIResponse::unauthorized('Admin access required.');
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST' && $action === 'add_teacher') {
    add_teacher($conn);
} else {
    APIResponse::error('Invalid request', 400);
}

function add_teacher(mysqli $conn): void {
    $input = json_decode(file_get_contents('php://input'), true);

    // --- Basic Input Validation ---
    $required_fields = ['employee_id', 'first_name', 'last_name', 'email', 'department', 'specialization', 'role'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || trim($input[$field]) === '') {
            APIResponse::error("Missing required field: $field", 400);
            return;
        }
    }

    // --- Sanitize Inputs ---
    $employee_id = trim($input['employee_id']);
    $first_name = trim($input['first_name']);
    $last_name = trim($input['last_name']);
    $middle_name = isset($input['middle_name']) ? trim($input['middle_name']) : null;
    $suffix = isset($input['suffix']) ? trim($input['suffix']) : null;
    $email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        APIResponse::error('Invalid email format.', 400);
        return;
    }
    $phone = isset($input['phone']) ? preg_replace('/[^0-9]/', '', $input['phone']) : null;
    $department = trim($input['department']);
    $specialization = trim($input['specialization']);
    $is_adviser = ($input['role'] === 'adviser') ? 1 : 0;
    $adviser_class_id = ($is_adviser && !empty($input['adviser_class_id'])) ? (int)$input['adviser_class_id'] : null;

    // --- Generate unique username and temporary password ---
    $base_username = strtolower(preg_replace('/[^a-z0-9]/', '', substr($first_name, 0, 1) . $last_name));
    $username = $base_username . rand(100, 999); // Add random digits for uniqueness
    $password = bin2hex(random_bytes(8)); // Generate a random 16-char password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // --- Database Transaction ---
    $conn->begin_transaction();

    try {
        // 1. Create user account
        $user_sql = "INSERT INTO users (username, password, email, first_name, last_name, middle_name, suffix, user_type, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 'teacher', 1)";
        $user_stmt = $conn->prepare($user_sql);
        if (!$user_stmt) {
            throw new Exception("User prepare failed: " . $conn->error);
        }
        $user_stmt->bind_param('sssssss', $username, $hashed_password, $email, $first_name, $last_name, $middle_name, $suffix);
        $user_stmt->execute();
        $user_id = $conn->insert_id;
        $user_stmt->close();

        // 2. Create teacher record
        $teacher_sql = "INSERT INTO teachers (user_id, teacher_id, department, specialization, hire_date, is_adviser, adviser_class_id) VALUES (?, ?, ?, ?, CURDATE(), ?, ?)";
        $teacher_stmt = $conn->prepare($teacher_sql);
        if (!$teacher_stmt) {
            throw new Exception("Teacher prepare failed: " . $conn->error);
        }
        $teacher_stmt->bind_param('isssii', $user_id, $employee_id, $department, $specialization, $is_adviser, $adviser_class_id);
        $teacher_stmt->execute();
        $teacher_stmt->close();

        // Commit transaction
        $conn->commit();

        // Send an email to the teacher with their login credentials
        $emailSent = sendAccountEmail($email, $first_name, $username, $password, 'Teacher');

        APIResponse::success([
            'message' => 'Teacher added successfully!',
            'teacher_id' => $employee_id,
            'user_id' => $user_id,
            'username' => $username,
            'email_sent' => $emailSent,
            'temp_password' => $password // Keep for admin to see in alert, but don't rely on it.
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        // Check for duplicate entry
        if ($conn->errno === 1062) {
            APIResponse::error('A teacher with this Employee ID or Email already exists.', 409);
        } else {
            APIResponse::error('Database error: ' . $e->getMessage(), 500);
        }
    }
}

?>