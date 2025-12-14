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

// Check if user is authenticated and is an admin
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Admin access required to add grading periods']);
    http_response_code(403);
    exit;
}

// Verify user role from database
$userId = $_SESSION['user_id'];
$roleStmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$roleStmt->bind_param("i", $userId);
$roleStmt->execute();
$roleResult = $roleStmt->get_result();
$roleRow = $roleResult->fetch_assoc();
$roleStmt->close();

if (!$roleRow || $roleRow['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Admin access required to add grading periods']);
    http_response_code(403);
    exit;
}

// Get action from query string
$action = isset($_GET['action']) ? $_GET['action'] : '';

/**
 * Notify admin users about grading period addition
 */
function notifyAdminsAboutGradingPeriod($quarter, $startDate, $endDate) {
    global $conn;
    
    try {
        $quarterNames = ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];
        $quarterName = isset($quarterNames[$quarter - 1]) ? $quarterNames[$quarter - 1] : "Quarter $quarter";
        $formattedStart = date('F j, Y', strtotime($startDate));
        $formattedEnd = date('F j, Y', strtotime($endDate));
        
        $notificationTitle = "Grading Period Added: $quarterName";
        $notificationMessage = "$quarterName has been set from $formattedStart to $formattedEnd";
        
        // Get all admins
        $adminQuery = "SELECT id FROM users WHERE role = 'admin'";
        $adminResult = $conn->query($adminQuery);
        
        $notifiedCount = 0;
        
        if ($adminResult && $adminResult->num_rows > 0) {
            while ($admin = $adminResult->fetch_assoc()) {
                $userId = $admin['id'];
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES (?, ?, ?, 'grading_period', 0, NOW())");
                if ($stmt) {
                    $stmt->bind_param("iss", $userId, $notificationTitle, $notificationMessage);
                    if ($stmt->execute()) {
                        $notifiedCount++;
                    }
                    $stmt->close();
                }
            }
        }
        
        error_log("Sent grading period notifications to $notifiedCount admins");
        return $notifiedCount;
        
    } catch (Exception $e) {
        error_log("Error sending grading period notifications: " . $e->getMessage());
        return false;
    }
}

switch ($action) {
    case 'add_grading_period':
        addGradingPeriod();
        break;
    case 'get_all':
        getAllGradingPeriods();
        break;
    case 'update':
        updateGradingPeriod();
        break;
    case 'delete':
        deleteGradingPeriod();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

/**
 * Add a new grading period
 */
function addGradingPeriod() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
        return;
    }
    
    $quarter = isset($input['quarter']) ? (int)$input['quarter'] : 0;
    $startDate = isset($input['start_date']) ? trim($input['start_date']) : '';
    $endDate = isset($input['end_date']) ? trim($input['end_date']) : '';
    
    // Validate inputs
    if ($quarter < 1 || $quarter > 4) {
        echo json_encode(['success' => false, 'error' => 'Invalid quarter. Must be 1-4']);
        http_response_code(400);
        return;
    }
    
    if (empty($startDate) || empty($endDate)) {
        echo json_encode(['success' => false, 'error' => 'Start date and end date are required']);
        http_response_code(400);
        return;
    }
    
    // Validate dates
    if (strtotime($startDate) === false || strtotime($endDate) === false) {
        echo json_encode(['success' => false, 'error' => 'Invalid date format']);
        http_response_code(400);
        return;
    }
    
    if (strtotime($startDate) >= strtotime($endDate)) {
        echo json_encode(['success' => false, 'error' => 'Start date must be before end date']);
        http_response_code(400);
        return;
    }
    
    try {
        // Check if grading period already exists for this quarter
        $checkStmt = $conn->prepare("SELECT id FROM grading_periods WHERE quarter = ?");
        $checkStmt->bind_param("i", $quarter);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'Grading period for this quarter already exists']);
            http_response_code(400);
            $checkStmt->close();
            return;
        }
        $checkStmt->close();
        
        // Insert the grading period
        $stmt = $conn->prepare("INSERT INTO grading_periods (quarter, start_date, end_date, created_by, created_at) VALUES (?, ?, ?, ?, NOW())");
        
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
            http_response_code(500);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $stmt->bind_param("issi", $quarter, $startDate, $endDate, $userId);
        
        if ($stmt->execute()) {
            $newId = $conn->insert_id;
            
            // Notify admins about the new grading period
            notifyAdminsAboutGradingPeriod($quarter, $startDate, $endDate);
            
            echo json_encode([
                'success' => true,
                'message' => 'Grading period added successfully',
                'id' => $newId
            ]);
            http_response_code(200);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add grading period: ' . $stmt->error]);
            http_response_code(500);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        http_response_code(500);
    }
}

/**
 * Get all grading periods
 */
function getAllGradingPeriods() {
    global $conn;
    
    try {
        $result = $conn->query("SELECT id, quarter, start_date, end_date, created_at FROM grading_periods ORDER BY quarter ASC");
        
        if (!$result) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
            http_response_code(500);
            return;
        }
        
        $periods = [];
        while ($row = $result->fetch_assoc()) {
            $periods[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'periods' => $periods
        ]);
        http_response_code(200);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        http_response_code(500);
    }
}

/**
 * Update a grading period
 */
function updateGradingPeriod() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
        http_response_code(400);
        return;
    }
    
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    $startDate = isset($input['start_date']) ? trim($input['start_date']) : '';
    $endDate = isset($input['end_date']) ? trim($input['end_date']) : '';
    
    if ($id <= 0 || empty($startDate) || empty($endDate)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        http_response_code(400);
        return;
    }
    
    if (strtotime($startDate) >= strtotime($endDate)) {
        echo json_encode(['success' => false, 'error' => 'Start date must be before end date']);
        http_response_code(400);
        return;
    }
    
    try {
        $stmt = $conn->prepare("UPDATE grading_periods SET start_date = ?, end_date = ? WHERE id = ?");
        $stmt->bind_param("ssi", $startDate, $endDate, $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Grading period updated successfully'
            ]);
            http_response_code(200);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update grading period']);
            http_response_code(500);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        http_response_code(500);
    }
}

/**
 * Delete a grading period
 */
function deleteGradingPeriod() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
        http_response_code(400);
        return;
    }
    
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        http_response_code(400);
        return;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM grading_periods WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Grading period deleted successfully'
            ]);
            http_response_code(200);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete grading period']);
            http_response_code(500);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        http_response_code(500);
    }
}
?>
