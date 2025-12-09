<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../../includes/config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add_grading_period':
        addGradingPeriod();
        break;
    case 'get_active_periods':
        getActivePeriods();
        break;
    case 'get_current_quarter':
        getCurrentQuarter();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function addGradingPeriod() {
    global $conn;
    
    // Debug logging
    error_log('Grading Period Add - User ID: ' . $_SESSION['user_id'] . ', User Type: ' . ($_SESSION['user_type'] ?? 'NOT SET'));
    
    // Only admins can add grading periods
    if ($_SESSION['user_type'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized - Admin access required']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['quarter']) || !isset($data['start_date']) || !isset($data['end_date'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    $quarter = (int)$data['quarter'];
    $startDate = $data['start_date'];
    $endDate = $data['end_date'];
    $userId = $_SESSION['user_id'];
    
    // Validate quarter
    if ($quarter < 1 || $quarter > 4) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid quarter']);
        return;
    }
    
    // Check if grading period already exists for this quarter
    $stmt = $conn->prepare("
        SELECT id FROM grading_periods 
        WHERE quarter = ? AND YEAR(start_date) = YEAR(CURDATE())
    ");
    $stmt->bind_param('i', $quarter);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Grading period for Q' . $quarter . ' already exists']);
        $stmt->close();
        return;
    }
    $stmt->close();
    
    $stmt = $conn->prepare("
        INSERT INTO grading_periods (quarter, start_date, end_date, created_by)
        VALUES (?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param('issi', $quarter, $startDate, $endDate, $userId);
    
    if ($stmt->execute()) {
        $gpId = $conn->insert_id;
        
        // Also create calendar events for this grading period
        $quarterNames = ['1st', '2nd', '3rd', '4th'];
        $quarterName = $quarterNames[$quarter - 1];
        
        // Create start event
        $startEventTitle = "✓✓ Q$quarter: Start Grades Submission";
        $stmt2 = $conn->prepare("
            INSERT INTO school_events (title, event_date, end_date, event_type, created_by, is_published)
            VALUES (?, ?, ?, 'deadline', ?, TRUE)
        ");
        $stmt2->bind_param('sssi', $startEventTitle, $startDate, $startDate, $userId);
        $stmt2->execute();
        $stmt2->close();
        
        // Create end event
        $endEventTitle = "✓✓ Q$quarter: End Grades Submission";
        $stmt3 = $conn->prepare("
            INSERT INTO school_events (title, event_date, end_date, event_type, created_by, is_published)
            VALUES (?, ?, ?, 'deadline', ?, TRUE)
        ");
        $stmt3->bind_param('sssi', $endEventTitle, $endDate, $endDate, $userId);
        $stmt3->execute();
        $stmt3->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Grading period added successfully',
            'id' => $gpId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add grading period']);
    }
    
    $stmt->close();
}

function getActivePeriods() {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT id, quarter, start_date, end_date, created_by
        FROM grading_periods
        WHERE YEAR(start_date) = YEAR(CURDATE())
        ORDER BY quarter ASC
    ");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
        return;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $periods = [];
    
    while ($row = $result->fetch_assoc()) {
        $periods[] = [
            'id' => $row['id'],
            'quarter' => $row['quarter'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date']
        ];
    }
    
    echo json_encode($periods);
    $stmt->close();
}

function getCurrentQuarter() {
    global $conn;
    
    $today = date('Y-m-d');
    
    $stmt = $conn->prepare("
        SELECT quarter
        FROM grading_periods
        WHERE start_date <= ? AND end_date >= ?
        AND YEAR(start_date) = YEAR(CURDATE())
        LIMIT 1
    ");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
        return;
    }
    
    $stmt->bind_param('ss', $today, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'current_quarter' => $row['quarter']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No active grading period',
            'current_quarter' => null
        ]);
    }
    
    $stmt->close();
}
?>
