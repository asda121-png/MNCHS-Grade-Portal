<?php
error_log("[DEBUG] student_register_api.php called");
// CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
require_once '../../includes/config.php';
require_once '../../src/Utilities/APIResponse.php';
use src\Utilities\APIResponse;

// Handle AJAX registration POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lrn'])) {
    // Collect and sanitize input
    $lrn = trim($_POST['lrn'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $parent_name = trim($_POST['parent_name'] ?? ''); // Added parent name
    $grade_level = trim($_POST['grade_level'] ?? '');
    $section = trim($_POST['section'] ?? '');
    $strand = trim($_POST['strand'] ?? '');

    // Generate username and password
    $username = strtolower(
        preg_replace('/[^a-z0-9]/', '', $first_name . $last_name)
    );
    $username .= rand(100, 999); // Add random digits for uniqueness
    $password = bin2hex(random_bytes(4)); // 8-char random password

    // Validation
    if (!preg_match('/^\d{12}$/', $lrn)) {
        APIResponse::error('LRN must be exactly 12 digits.');
    }
    if (empty($first_name) || empty($last_name) || empty($grade_level) || empty($section) || empty($email) || empty($parent_name)) {
        APIResponse::error('Please fill in all required fields.');
    }
    if (($grade_level === '11' || $grade_level === '12') && $strand === '') {
        APIResponse::error('Strand is required for Grade 11/12.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        APIResponse::error('Invalid email address.');
    }

    // Start transaction for atomic operations
    $conn->begin_transaction();

    try {
        // Check for duplicate email (case and whitespace insensitive)
        $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(TRIM(email)) = LOWER(TRIM(?))");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $rowCount = $result->num_rows;
        error_log("[DEBUG] Checking email: '" . $email . "' | Row count: $rowCount | DB: " . $conn->query("SELECT DATABASE() AS db")->fetch_assoc()['db']);
        if ($rowCount > 0) {
            throw new Exception('An account with this email already exists.');
        }
        $stmt->close();

        $stmt = $conn->prepare("SELECT id FROM students WHERE lrn = ?");
        $stmt->bind_param('s', $lrn);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('An account with this LRN already exists.');
        }
        $stmt->close();

        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, first_name, last_name, is_active) VALUES (?, ?, ?, 'student', ?, ?, 1)");
        $stmt->bind_param('sssss', $username, $email, $password_hash, $first_name, $last_name);
        if (!$stmt->execute()) {
            throw new Exception('Failed to create user account: ' . $stmt->error);
        }
        $user_id = $stmt->insert_id;
        $stmt->close();

        // Insert into students table
        $student_name = trim($first_name . ' ' . $middle_name . ' ' . $last_name . ' ' . $suffix);
        $stmt = $conn->prepare("INSERT INTO students (user_id, student_id, lrn, student_name, grade_level, section, guardian_contact) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $student_id = $lrn; // Use LRN as student_id for now
        $stmt->bind_param('isssiss', $user_id, $student_id, $lrn, $student_name, $grade_level, $section, $parent_name);
        if (!$stmt->execute()) {
            throw new Exception('Failed to create student record: ' . $stmt->error);
        }
        $stmt->close();

        // If all good, commit the transaction
        $conn->commit();

        // Send email with credentials
        require_once __DIR__ . '/send_student_account_email.php';
        $emailSent = sendStudentAccountEmail($email, $username, $password);

        APIResponse::success([
            'username' => $username,
            'password' => $password, // Note: Only show this for debugging if needed.
            'emailSent' => $emailSent
        ], 'Registration successful!');

    } catch (Exception $e) {
        $conn->rollback(); // Rollback on any error
        error_log("Registration Error: " . $e->getMessage());
        APIResponse::error($e->getMessage(), 409); // 409 Conflict for duplicate data
    }
} else {
    APIResponse::error('Invalid request method or missing LRN.', 405);
}
?>
