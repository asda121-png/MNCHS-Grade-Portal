<?php
header('Content-Type: application/json');
require_once '../../includes/config.php';

// Helper to send JSON response
function sendJson($data) {
    echo json_encode($data);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'get_all') {
    $query = "SELECT t.id, t.teacher_id, t.department, t.specialization as subjects, t.assigned_sections,
                     u.first_name, u.last_name, 
                     c.id as class_id, c.class_name, c.section
              FROM teachers t
              JOIN users u ON t.user_id = u.id
              LEFT JOIN classes c ON t.adviser_class_id = c.id
              ORDER BY u.last_name ASC";
    
    $result = $conn->query($query);
    $teachers = [];
    
    while ($row = $result->fetch_assoc()) {
        $teachers[] = [
            'id' => $row['id'],
            'empNo' => $row['teacher_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'dept' => $row['department'],
            'subjects' => $row['subjects'],
            'sections' => $row['assigned_sections'],
            'advisory_id' => $row['class_id'],
            'advisory' => $row['class_id'] ? ($row['class_name'] . ' - ' . $row['section']) : ''
        ];
    }
    
    sendJson(['success' => true, 'teachers' => $teachers]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($action === 'add') {
        $empNo = $conn->real_escape_string($input['empNo']);
        $fullName = trim($input['name']);
        $dept = $conn->real_escape_string($input['dept']);
        $subjects = $conn->real_escape_string($input['subjects']);
        $sections = isset($input['sections']) ? $conn->real_escape_string($input['sections']) : '';
        
        // Resolve Advisory Class ID
        $advisoryId = !empty($input['advisory']) ? getOrCreateClassId($conn, $input['advisory']) : null;
        
        // Split name
        $parts = explode(' ', $fullName);
        $lastName = array_pop($parts);
        $firstName = implode(' ', $parts);
        
        // Generate credentials
        $username = strtolower(str_replace(' ', '', $firstName . $lastName)); // simple username gen
        $email = $username . "@mnchs.edu.ph";
        $password = password_hash('password', PASSWORD_DEFAULT); // Default password
        
        $conn->begin_transaction();
        try {
            // 1. Create User
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, ?, 'teacher', 1)");
            $stmt->bind_param("sssss", $username, $password, $email, $firstName, $lastName);
            $stmt->execute();
            $userId = $conn->insert_id;
            $stmt->close();
            
            // 2. Create Teacher
            $isAdviser = $advisoryId ? 1 : 0;
            $adviserClassVal = $advisoryId ? $advisoryId : null;
            
            $stmt = $conn->prepare("INSERT INTO teachers (user_id, teacher_id, department, specialization, assigned_sections, is_adviser, adviser_class_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssii", $userId, $empNo, $dept, $subjects, $sections, $isAdviser, $adviserClassVal);
            $stmt->execute();
            $teacherId = $conn->insert_id; // Capture the new Teacher ID
            $stmt->close();
            
            // Sync Class Subjects for Dashboard
            syncTeacherClasses($conn, $teacherId, $dept, $subjects, $sections);
            
            $conn->commit();
            sendJson(['success' => true]);
        } catch (Exception $e) {
            $conn->rollback();
            sendJson(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    if ($action === 'update') {
        $id = (int)$input['id'];
        $empNo = $conn->real_escape_string($input['empNo']);
        $fullName = trim($input['name']);
        $dept = $conn->real_escape_string($input['dept']);
        $subjects = $conn->real_escape_string($input['subjects']);
        $sections = isset($input['sections']) ? $conn->real_escape_string($input['sections']) : '';
        
        // Resolve Advisory Class ID
        $advisoryId = !empty($input['advisory']) ? getOrCreateClassId($conn, $input['advisory']) : null;
        
        // Split name
        $parts = explode(' ', $fullName);
        $lastName = array_pop($parts);
        $firstName = implode(' ', $parts);
        
        $conn->begin_transaction();
        try {
            // Get User ID
            $res = $conn->query("SELECT user_id FROM teachers WHERE id = $id");
            $row = $res->fetch_assoc();
            $userId = $row['user_id'];
            
            // 1. Update User
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
            $stmt->bind_param("ssi", $firstName, $lastName, $userId);
            $stmt->execute();
            $stmt->close();
            
            // 2. Update Teacher
            $isAdviser = $advisoryId ? 1 : 0;
            $adviserClassVal = $advisoryId ? $advisoryId : null;
            
            $stmt = $conn->prepare("UPDATE teachers SET teacher_id = ?, department = ?, specialization = ?, assigned_sections = ?, is_adviser = ?, adviser_class_id = ? WHERE id = ?");
            $stmt->bind_param("ssssiii", $empNo, $dept, $subjects, $sections, $isAdviser, $adviserClassVal, $id);
            $stmt->execute();
            $stmt->close();
            
            // Sync Class Subjects for Dashboard
            syncTeacherClasses($conn, $id, $dept, $subjects, $sections);
            
            $conn->commit();
            sendJson(['success' => true]);
        } catch (Exception $e) {
            $conn->rollback();
            sendJson(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    if ($action === 'delete') {
        $id = (int)$input['id'];
        
        $conn->begin_transaction();
        try {
            // Get User ID to delete user (cascade will handle teacher)
            $res = $conn->query("SELECT user_id FROM teachers WHERE id = $id");
            if ($res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $userId = $row['user_id'];
                
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();
            } else {
                // Just delete teacher record if no user found (orphan)
                $conn->query("DELETE FROM teachers WHERE id = $id");
            }
            
            $conn->commit();
            sendJson(['success' => true]);
        } catch (Exception $e) {
            $conn->rollback();
            sendJson(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

// --- Helper Functions ---

function getOrCreateClassId($conn, $classStr) {
    $classStr = trim($classStr);
    if (empty($classStr)) return null;

    // Check if ID is passed directly (numeric)
    if (is_numeric($classStr)) {
        $res = $conn->query("SELECT id FROM classes WHERE id = " . (int)$classStr);
        if ($res && $res->num_rows > 0) return (int)$classStr;
    }

    $escStr = $conn->real_escape_string($classStr);
    // Try to find by section name or combined name
    $query = "SELECT id FROM classes WHERE section = '$escStr' OR CONCAT(class_name, ' ', section) = '$escStr' OR CONCAT(class_name, '-', section) = '$escStr' OR CONCAT(class_name, ' - ', section) = '$escStr' LIMIT 1";
    $res = $conn->query($query);
    if ($res && $res->num_rows > 0) {
        return (int)$res->fetch_assoc()['id'];
    }

    // Create new class
    $className = '';
    $gradeLevel = 0;
    $section = '';
    $academicYear = '2025-2026';

    if (preg_match('/^Grade\s+(\d+)\s+-\s+(.+)$/i', $classStr, $matches)) {
        $gradeLevel = (int)$matches[1];
        $section = $matches[2];
        $className = "Grade " . $gradeLevel;
    } elseif (preg_match('/^(.+?)\s+(\d+)-(.+)$/i', $classStr, $matches)) {
        $gradeLevel = (int)$matches[2];
        $section = $matches[3];
        $className = $matches[1] . " " . $matches[2];
    } else {
        $section = $classStr;
        $className = "Uncategorized";
    }

    $stmt = $conn->prepare("INSERT INTO classes (class_name, grade_level, section, academic_year) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $className, $gradeLevel, $section, $academicYear);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return null;
}

function getOrCreateSubjectId($conn, $subjectName) {
    $subjectName = trim($subjectName);
    if (empty($subjectName)) return null;

    $stmt = $conn->prepare("SELECT id FROM subjects WHERE subject_name = ?");
    $stmt->bind_param("s", $subjectName);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return $row['id'];
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
    $stmt->bind_param("s", $subjectName);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return null;
}

function syncTeacherClasses($conn, $teacherId, $dept, $specialization, $assignedSectionsStr) {
    // 1. Clear existing assignments for this teacher to prevent duplicates
    $conn->query("DELETE FROM class_subjects WHERE teacher_id = $teacherId");

    if (empty($assignedSectionsStr)) return;

    $sections = array_unique(array_map('trim', explode(',', $assignedSectionsStr)));
    
    // Determine if JHS or SHS based on Dept
    $isJHS = strpos($dept, 'Junior High School') !== false || 
             in_array($dept, ['English Department', 'Mathematics Department', 'Science Department', 'Social Studies Department', 'Filipino / Language Department', 'MAPEH Department', 'TLE / TVL Department']);

    foreach ($sections as $sectionName) {
        if (empty($sectionName)) continue;

        // 2. Resolve Class ID
        $classId = getOrCreateClassId($conn, $sectionName);
        if (!$classId) continue;

        // 3. Resolve Subject ID
        $subjectName = $specialization; // Default for SHS (e.g. "Pre-Calculus")
        
        if ($isJHS) {
            // For JHS, derive subject from Department + Grade Level of the class
            $gradeLevel = 0;
            $cRes = $conn->query("SELECT grade_level FROM classes WHERE id = $classId");
            if ($cRes && $row = $cRes->fetch_assoc()) $gradeLevel = $row['grade_level'];

            $prefix = 'Subject';
            if (strpos($dept, 'Math') !== false) $prefix = 'Mathematics';
            elseif (strpos($dept, 'Science') !== false) $prefix = 'Science';
            elseif (strpos($dept, 'English') !== false) $prefix = 'English';
            elseif (strpos($dept, 'Filipino') !== false) $prefix = 'Filipino';
            elseif (strpos($dept, 'Social') !== false) $prefix = 'Araling Panlipunan';
            elseif (strpos($dept, 'MAPEH') !== false) $prefix = 'MAPEH';
            elseif (strpos($dept, 'TLE') !== false) $prefix = 'Technology and Livelihood Education';
            
            $subjectName = $prefix . ' ' . $gradeLevel;
        }

        $subjectId = getOrCreateSubjectId($conn, $subjectName);
        
        // 4. Insert into class_subjects
        if ($classId && $subjectId) {
            $stmt = $conn->prepare("INSERT INTO class_subjects (class_id, subject_id, teacher_id) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $classId, $subjectId, $teacherId);
            $stmt->execute();
            $stmt->close();
        }
    }
}

$conn->close();
?>