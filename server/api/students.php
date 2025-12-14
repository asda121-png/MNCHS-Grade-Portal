<?php
session_start();

// Check if user is authenticated and is a teacher or admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['teacher', 'admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../../includes/config.php';

// Set response header
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? null;

/**
 * Add a new student to the database
 */
function addStudent($conn) {
    // Get form data
    $lrn = $_POST['lrn'] ?? null;
    $firstName = $_POST['firstName'] ?? null;
    $middleName = $_POST['middleName'] ?? '';
    $lastName = $_POST['lastName'] ?? null;
    $suffix = $_POST['suffix'] ?? '';
    $email = $_POST['email'] ?? null;
    $streetAddress = $_POST['streetAddress'] ?? '';
    $city = $_POST['city'] ?? '';
    $province = $_POST['province'] ?? '';
    $fatherName = $_POST['fatherName'] ?? '';
    $motherName = $_POST['motherName'] ?? '';
    $guardianName = $_POST['guardianName'] ?? '';
    $guardianRelationship = $_POST['guardianRelationship'] ?? '';
    $guardianContact = $_POST['guardianContact'] ?? '';
    $emergencyContact = $_POST['emergencyContact'] ?? '';
    $gradeLevel = $_POST['gradeLevel'] ?? null;
    $section = $_POST['section'] ?? '';
    $status = $_POST['status'] ?? 'Not Enrolled';
    $dateEnrolled = $_POST['dateEnrolled'] ?? null;
    $dateOfBirth = $_POST['dateOfBirth'] ?? null;

    // Validate required fields
    if (!$lrn || !$firstName || !$lastName || !$email || !$gradeLevel) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }

    // Validate LRN format (12 digits)
    if (!preg_match('/^\d{12}$/', $lrn)) {
        echo json_encode(['success' => false, 'error' => 'LRN must be exactly 12 digits']);
        return;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email address']);
        return;
    }

    // Check if LRN already exists
    $stmt = $conn->prepare("SELECT id FROM students WHERE lrn = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        return;
    }
    $stmt->bind_param("s", $lrn);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'LRN already exists']);
        $stmt->close();
        return;
    }
    $stmt->close();

    // Generate unique username and student ID
    $username = 'student_' . $lrn;
    $studentId = 'STD' . date('Ymd') . mt_rand(1000, 9999);

    // Generate a temporary password
    $tempPassword = bin2hex(random_bytes(4)); // Generate 8-character password
    $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT);

    // Build full address
    $fullAddress = trim($streetAddress . (($streetAddress && $city) ? ', ' : '') . $city . (($city && $province) ? ', ' : '') . $province);

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Create user account
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, first_name, last_name, is_active, created_at) VALUES (?, ?, ?, 'student', ?, ?, 1, NOW())");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->bind_param("sssss", $username, $email, $passwordHash, $firstName, $lastName);
        if (!$stmt->execute()) {
            throw new Exception("Error creating user: " . $stmt->error);
        }
        $userId = $stmt->insert_id;
        $stmt->close();

        // 2. Create student record
        $stmt = $conn->prepare("INSERT INTO students (user_id, student_id, lrn, student_name, grade_level, section, guardian_contact, address, date_of_birth, enrollment_date, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $studentName = trim($firstName . ' ' . $middleName . ' ' . $lastName . ' ' . $suffix);
        $enrollmentDate = ($status === 'Enrolled' && $dateEnrolled) ? $dateEnrolled : null;
        
        $stmt->bind_param("isssisssss", $userId, $studentId, $lrn, $studentName, $gradeLevel, $section, $guardianContact, $fullAddress, $dateOfBirth, $enrollmentDate);
        if (!$stmt->execute()) {
            throw new Exception("Error creating student: " . $stmt->error);
        }
        $studentDbId = $stmt->insert_id;
        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Send email notification to student
        require_once __DIR__ . '/send_student_account_email.php';
        $emailSent = sendStudentAccountEmail($email, $username, $tempPassword);

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Student added successfully',
            'studentId' => $studentDbId,
            'username' => $username,
            'tempPassword' => $tempPassword,
            'lrn' => $lrn,
            'emailSent' => $emailSent
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Update an existing student
 */
function updateStudent($conn) {
    $studentId = $_POST['studentId'] ?? null;
    $firstName = $_POST['firstName'] ?? null;
    $middleName = $_POST['middleName'] ?? '';
    $lastName = $_POST['lastName'] ?? null;
    $suffix = $_POST['suffix'] ?? '';
    $email = $_POST['email'] ?? null;
    $streetAddress = $_POST['streetAddress'] ?? '';
    $city = $_POST['city'] ?? '';
    $province = $_POST['province'] ?? '';
    $guardianContact = $_POST['guardianContact'] ?? '';
    $emergencyContact = $_POST['emergencyContact'] ?? '';
    $gradeLevel = $_POST['gradeLevel'] ?? null;
    $section = $_POST['section'] ?? '';
    $status = $_POST['status'] ?? 'Not Enrolled';
    $dateEnrolled = $_POST['dateEnrolled'] ?? null;
    $dateOfBirth = $_POST['dateOfBirth'] ?? null;

    // Validate required fields
    if (!$studentId || !$firstName || !$lastName || !$email || !$gradeLevel) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }

    // Build full address
    $fullAddress = trim($streetAddress . (($streetAddress && $city) ? ', ' : '') . $city . (($city && $province) ? ', ' : '') . $province);
    $studentName = trim($firstName . ' ' . $middleName . ' ' . $lastName . ' ' . $suffix);
    $enrollmentDate = ($status === 'Enrolled' && $dateEnrolled) ? $dateEnrolled : null;

    // Update student record
    $stmt = $conn->prepare("UPDATE students SET student_name = ?, grade_level = ?, section = ?, guardian_contact = ?, address = ?, date_of_birth = ?, enrollment_date = ?, updated_at = NOW() WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        return;
    }
    $stmt->bind_param("ssissssi", $studentName, $gradeLevel, $section, $guardianContact, $fullAddress, $dateOfBirth, $enrollmentDate, $studentId);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Error updating student: ' . $stmt->error]);
        $stmt->close();
        return;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Student updated successfully', 'studentId' => $studentId]);
}

/**
 * Get all students
 */
function getAllStudents($conn) {
    $query = "SELECT id, student_id, lrn, student_name, grade_level, section, guardian_contact, address, date_of_birth, enrollment_date FROM students ORDER BY student_name ASC";
    
    $result = $conn->query($query);
    if (!$result) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        return;
    }

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $result->free();

    echo json_encode(['success' => true, 'students' => $students]);
}

/**
 * Delete a student
 */
function deleteStudent($conn) {
    $studentId = $_POST['studentId'] ?? null;

    if (!$studentId) {
        echo json_encode(['success' => false, 'error' => 'Student ID required']);
        return;
    }

    // Get user_id first
    $stmt = $conn->prepare("SELECT user_id FROM students WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        return;
    }
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Student not found']);
        $stmt->close();
        return;
    }
    $row = $result->fetch_assoc();
    $userId = $row['user_id'];
    $stmt->close();

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete student record (this will cascade delete enrollments)
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->bind_param("i", $studentId);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting student: " . $stmt->error);
        }
        $stmt->close();

        // Delete user account
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->bind_param("i", $userId);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting user: " . $stmt->error);
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Route to appropriate handler
switch ($action) {
    case 'add':
        addStudent($conn);
        break;
    case 'update':
        updateStudent($conn);
        break;
    case 'get_all':
        getAllStudents($conn);
        break;
    case 'delete':
        deleteStudent($conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
?>
