<?php
session_start();
require_once '../../includes/config.php';

// Handle AJAX registration POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lrn'])) {
    header('Content-Type: application/json');
    $response = ["success" => false, "message" => "Unknown error."];

    // Collect and sanitize input
    $lrn = trim($_POST['lrn'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $grade_level = trim($_POST['grade_level'] ?? '');
    $section = trim($_POST['section'] ?? '');
    $strand = trim($_POST['strand'] ?? '');
    $adviser_name = trim($_POST['adviser_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (!preg_match('/^\d{12}$/', $lrn)) {
        $response['message'] = 'LRN must be exactly 12 digits.';
        echo json_encode($response); exit;
    }
    if ($first_name === '' || $last_name === '' || $grade_level === '' || $section === '' || $username === '' || $email === '' || $password === '' || $adviser_name === '') {
        $response['message'] = 'Please fill in all required fields.';
        echo json_encode($response); exit;
    }
    if (($grade_level === '11' || $grade_level === '12') && $strand === '') {
        $response['message'] = 'Strand is required for Grade 11/12.';
        echo json_encode($response); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address.';
        echo json_encode($response); exit;
    }
    if ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match.';
        echo json_encode($response); exit;
    }
    if (strlen($password) < 6) {
        $response['message'] = 'Password must be at least 6 characters.';
        echo json_encode($response); exit;
    }

    // Check for duplicate LRN, username, or email
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $response['message'] = 'Username or email already exists.';
        echo json_encode($response); exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT id FROM students WHERE lrn = ?");
    $stmt->bind_param('s', $lrn);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $response['message'] = 'LRN already exists.';
        echo json_encode($response); exit;
    }
    $stmt->close();

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert into users table
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, first_name, last_name, is_active) VALUES (?, ?, ?, 'student', ?, ?, 1)");
    $stmt->bind_param('sssss', $username, $email, $password_hash, $first_name, $last_name);
    if (!$stmt->execute()) {
        $response['message'] = 'Failed to create user: ' . $stmt->error;
        echo json_encode($response); exit;
    }
    $user_id = $stmt->insert_id;
    $stmt->close();

    // Insert into students table
    $student_name = trim($first_name . ' ' . $middle_name . ' ' . $last_name . ' ' . $suffix);
    $stmt = $conn->prepare("INSERT INTO students (user_id, student_id, lrn, student_name, grade_level, section) VALUES (?, ?, ?, ?, ?, ?)");
    $student_id = $lrn; // Use LRN as student_id for now
    $stmt->bind_param('isssis', $user_id, $student_id, $lrn, $student_name, $grade_level, $section);
    if (!$stmt->execute()) {
        $response['message'] = 'Failed to create student: ' . $stmt->error;
        // Rollback user
        $conn->query("DELETE FROM users WHERE id = " . intval($user_id));
        echo json_encode($response); exit;
    }
    $stmt->close();

    $response['success'] = true;
    $response['message'] = 'Registration successful!';
    echo json_encode($response); exit;
}
