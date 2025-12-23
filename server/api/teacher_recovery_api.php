<?php
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once 'send_account_email.php'; // Reusing the existing email function

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$employeeNumber = trim($_POST['employee_number'] ?? '');
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');

if (empty($employeeNumber) || empty($fullName) || empty($email)) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit;
}

try {
    // 1. Verify Teacher Exists
    // We check if the teacher_id matches AND if the name matches the user record
    // Note: Names in DB are split into first_name and last_name. We compare against CONCAT.
    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.first_name, u.last_name 
        FROM teachers t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.teacher_id = ? 
        AND (
            LOWER(CONCAT(u.first_name, ' ', u.last_name)) = LOWER(?) 
            OR LOWER(CONCAT(u.first_name, ' ', u.last_name)) = LOWER(?)
        )
    ");
    
    // Try exact match and trimmed match
    $stmt->bind_param("sss", $employeeNumber, $fullName, $fullName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'No teacher account found matching these details.']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    $userId = $user['id'];
    $username = $user['username'];
    $firstName = $user['first_name'];
    
    // 2. Generate New Password
    $newPassword = bin2hex(random_bytes(4)); // 8 character random password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Start transaction to ensure password is only changed if email is sent
    $conn->begin_transaction();

    // 3. Update User Password
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateStmt->bind_param("si", $hashedPassword, $userId);
    
    if ($updateStmt->execute()) {
        // 4. Send Email
        $emailSent = sendAccountEmail($email, $firstName, $username, $newPassword, 'Teacher');
        
        if ($emailSent) {
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Credentials sent to email.']);
        } else {
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => 'Account verified but failed to send email. Please contact admin to check SMTP settings.']);
        }
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Database error updating account.']);
    }
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>