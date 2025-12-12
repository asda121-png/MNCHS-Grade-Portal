<?php
// Critical: Set headers immediately before ANY output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering FIRST to capture any accidental output
if (!ob_get_level()) {
    ob_start();
}

// Set headers BEFORE session start
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Set custom error handler to log all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("[$errno] $errstr in $errfile:$errline");
    return false;
});

// Set exception handler
set_exception_handler(function($e) {
    error_log('Uncaught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    error_log('Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    ob_end_clean();
    echo json_encode(['error' => 'Server error']);
    exit;
});

session_start();

// Check if user is logged in BEFORE doing anything
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Load config with comprehensive error handling
try {
    require_once '../../includes/config.php';
} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(503);
    error_log('Config loading exception: ' . $e->getMessage());
    echo json_encode(['error' => 'Database initialization failed']);
    exit;
}

// Verify connection was created successfully
if (!isset($conn) || $conn === null) {
    ob_end_clean();
    http_response_code(503);
    error_log('Database connection object not created');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if ($conn->connect_error) {
    ob_end_clean();
    http_response_code(503);
    error_log('Database connection error: ' . $conn->connect_error);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Verify database connection is working with a simple query
$test_query = $conn->query("SELECT 1");
if (!$test_query) {
    $error = $conn->error ?: 'Unknown error';
    ob_end_clean();
    http_response_code(503);
    error_log('Database connection test failed: ' . $error);
    echo json_encode(['error' => 'Database connection test failed']);
    exit;
}

$action = $_GET['action'] ?? '';

// Clear any buffered output that might have been generated during includes
ob_end_clean();

error_log('API Request: action=' . $action . ', user_id=' . $_SESSION['user_id'] . ', user_type=' . ($_SESSION['user_type'] ?? 'NOT_SET'));

// Wrap all operations in try-catch for better error handling
try {
    // Validate action
    if (empty($action)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing action parameter']);
        exit;
    }
    
    // Handle different actions
    switch ($action) {
        case 'add':
            error_log('ACTION: Adding new event');
            addEvent();
            break;
            
        case 'get':
            error_log('ACTION: Getting all events');
            getEvents();
            break;
            
        case 'get_admin_events':
            error_log('ACTION: Getting admin events for user ' . $_SESSION['user_id']);
            getAdminEvents();
            break;
            
        case 'update':
            error_log('ACTION: Updating event');
            updateEvent();
            break;
            
        case 'delete':
            error_log('ACTION: Deleting event');
            deleteEvent();
            break;
            
        default:
            error_log('ACTION: Invalid action - ' . $action);
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action: ' . htmlspecialchars($action)]);
    }
    
} catch (Throwable $e) {
    error_log('FATAL ERROR in API handler: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    error_log('Exception type: ' . get_class($e));
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Ensure clean output
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error processing request',
        'action' => $action
    ]);
}

function addEvent() {
    global $conn;
    
    try {
        error_log('addEvent: Parsing request body');
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            throw new Exception('Failed to parse JSON request body');
        }
        
        if (!isset($data['title']) || empty($data['title'])) {
            throw new Exception('Missing required field: title');
        }
        
        if (!isset($data['start']) || empty($data['start'])) {
            throw new Exception('Missing required field: start date');
        }
        
        $title = trim($data['title']);
        $start = $data['start'];
        $end = $data['end'] ?? $data['start'];
        $userId = $_SESSION['user_id'];
        
        error_log('addEvent: title="' . $title . '", start=' . $start . ', user_id=' . $userId);
        
        $stmt = $conn->prepare("
            INSERT INTO school_events (title, event_date, end_date, event_type, created_by, is_published)
            VALUES (?, ?, ?, 'other', ?, TRUE)
        ");
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        if (!$stmt->bind_param('sssi', $title, $start, $end, $userId)) {
            throw new Exception('Bind param failed: ' . $stmt->error);
        }
        
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $eventId = $conn->insert_id;
        error_log('addEvent: New event created with ID ' . $eventId);
        
        // Notify all users about the new event
        notifyUsers($conn, $title, 'add', $eventId);
        
        echo json_encode([
            'success' => true,
            'id' => $eventId,
            'message' => 'Event added successfully'
        ]);
        
        $stmt->close();
        
    } catch (Exception $e) {
        error_log('addEvent Exception: ' . $e->getMessage());
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getEvents() {
    global $conn;
    
    try {
        error_log('getEvents: Fetching published events');
        
        $stmt = $conn->prepare("
            SELECT id, title, event_date as start, end_date as end, event_type, created_by
            FROM school_events
            WHERE is_published = TRUE
            ORDER BY event_date ASC
        ");
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception('Get result failed: ' . $conn->error);
        }
        
        $events = [];
        
        while ($row = $result->fetch_assoc()) {
            // Determine colors based on event type
            $colors = [
                'holiday' => ['bg' => '#4ecdc4', 'border' => '#45b7aa', 'text' => '#ffffff', 'class' => 'fc-event-holiday'],
                'examination' => ['bg' => '#a29bfe', 'border' => '#9370db', 'text' => '#ffffff', 'class' => 'fc-event-exam'],
                'deadline' => ['bg' => '#ff6b6b', 'border' => '#ff5252', 'text' => '#ffffff', 'class' => 'fc-event-deadline'],
                'celebration' => ['bg' => '#ffd93d', 'border' => '#ffb800', 'text' => '#2d3436', 'class' => 'fc-event-celebration'],
                'meeting' => ['bg' => '#74b9ff', 'border' => '#0984e3', 'text' => '#ffffff', 'class' => 'fc-event-meeting'],
                'other' => ['bg' => '#800000', 'border' => '#800000', 'text' => '#ffffff', 'class' => 'fc-event-other']
            ];
            
            $eventType = $row['event_type'] ?? 'other';
            $color = $colors[$eventType] ?? $colors['other'];
            
            $events[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'start' => $row['start'],
                'end' => $row['end'],
                'backgroundColor' => $color['bg'],
                'borderColor' => $color['border'],
                'textColor' => $color['text'],
                'classNames' => [$color['class']]
            ];
        }
        
        error_log('getEvents: Returning ' . count($events) . ' published events');
        echo json_encode($events);
        $stmt->close();
        
    } catch (Exception $e) {
        error_log('getEvents Exception: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getAdminEvents() {
    global $conn;
    
    try {
        $userId = $_SESSION['user_id'] ?? null;
        $userType = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'unknown';
        
        if ($userId === null) {
            throw new Exception('User ID not set in session');
        }
        
        error_log('getAdminEvents START: userId=' . $userId . ', userType=' . $userType);
        
        // Query based on user type
        $query = '';
        $stmt = null;
        
        if ($userType === 'admin') {
            // Admins can see all events
            $query = "
                SELECT 
                    school_events.id AS event_id,
                    school_events.title,
                    school_events.event_date AS start,
                    school_events.end_date AS end,
                    school_events.event_type,
                    school_events.created_by AS creator_id,
                    school_events.is_published,
                    CONCAT(COALESCE(u.first_name, 'Unknown'), ' ', COALESCE(u.last_name, '')) AS creator_name
                FROM school_events
                LEFT JOIN users u ON school_events.created_by = u.id
                ORDER BY school_events.event_date DESC
            ";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Prepare failed for admin query: ' . $conn->error);
            }
            
            error_log('Admin query prepared successfully');
            
        } else {
            // Regular users see only their created events
            $query = "
                SELECT 
                    school_events.id AS event_id,
                    school_events.title,
                    school_events.event_date AS start,
                    school_events.end_date AS end,
                    school_events.event_type,
                    school_events.created_by AS creator_id,
                    school_events.is_published,
                    CONCAT(COALESCE(u.first_name, 'Unknown'), ' ', COALESCE(u.last_name, '')) AS creator_name
                FROM school_events
                LEFT JOIN users u ON school_events.created_by = u.id
                WHERE school_events.created_by = ?
                ORDER BY school_events.event_date DESC
            ";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Prepare failed for user query: ' . $conn->error);
            }
            
            if (!$stmt->bind_param('i', $userId)) {
                throw new Exception('Bind param failed: ' . $stmt->error);
            }
            
            error_log('User query prepared and bound successfully');
        }
        
        // Execute query
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        error_log('Query executed successfully');
        
        // Get results
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception('Get result failed: ' . $conn->error);
        }
        
        // Fetch all events
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'id' => isset($row['event_id']) ? (int)$row['event_id'] : 0,
                'title' => $row['title'] ?? '',
                'start' => $row['start'] ?? '',
                'end' => $row['end'] ?? '',
                'event_type' => $row['event_type'] ?? 'other',
                'created_by' => isset($row['creator_id']) ? (int)$row['creator_id'] : 0,
                'is_published' => (bool)$row['is_published'],
                'creator_name' => $row['creator_name'] ?? 'Unknown'
            ];
        }
        
        $stmt->close();
        
        error_log('getAdminEvents SUCCESS: Returning ' . count($events) . ' events');
        echo json_encode([
            'success' => true,
            'events' => $events,
            'count' => count($events)
        ]);
        
    } catch (Exception $e) {
        error_log('getAdminEvents EXCEPTION: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        error_log('Stack trace: ' . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to load events',
            'debug' => $e->getMessage()
        ]);
    }
}

function updateEvent() {
    global $conn;
    
    try {
        error_log('updateEvent: Parsing request body');
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            throw new Exception('Failed to parse JSON request body');
        }
        
        if (!isset($data['id']) || !isset($data['title']) || !isset($data['event_date'])) {
            throw new Exception('Missing required fields: id, title, event_date');
        }
        
        $eventId = (int)$data['id'];
        $title = trim($data['title']);
        $eventDate = $data['event_date'];
        $endDate = $data['end_date'] ?? $eventDate;
        $eventType = $data['event_type'] ?? 'other';
        $isPublished = isset($data['is_published']) ? ($data['is_published'] ? 1 : 0) : 1;
        $userId = $_SESSION['user_id'];
        
        error_log('updateEvent: id=' . $eventId . ', title="' . $title . '", user_id=' . $userId);
        
        // Check if event exists and get creator
        $checkStmt = $conn->prepare("SELECT created_by FROM school_events WHERE id = ?");
        if (!$checkStmt) {
            throw new Exception('Prepare check failed: ' . $conn->error);
        }
        
        $checkStmt->bind_param('i', $eventId);
        if (!$checkStmt->execute()) {
            throw new Exception('Execute check failed: ' . $checkStmt->error);
        }
        
        $result = $checkStmt->get_result();
        if ($result->num_rows == 0) {
            throw new Exception('Event not found');
        }
        
        $event = $result->fetch_assoc();
        $checkStmt->close();
        
        // Check authorization
        if ($event['created_by'] != $userId && $_SESSION['user_type'] != 'admin') {
            throw new Exception('Unauthorized - only creator or admin can update');
        }
        
        // Update event
        $updateStmt = $conn->prepare("
            UPDATE school_events 
            SET title = ?, event_date = ?, end_date = ?, event_type = ?, is_published = ?
            WHERE id = ?
        ");
        
        if (!$updateStmt) {
            throw new Exception('Prepare update failed: ' . $conn->error);
        }
        
        if (!$updateStmt->bind_param('sssiii', $title, $eventDate, $endDate, $eventType, $isPublished, $eventId)) {
            throw new Exception('Bind param failed: ' . $updateStmt->error);
        }
        
        if (!$updateStmt->execute()) {
            throw new Exception('Execute update failed: ' . $updateStmt->error);
        }
        
        error_log('updateEvent: Event ID ' . $eventId . ' updated successfully');
        notifyUsers($conn, $title, 'update', $eventId);
        
        echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
        $updateStmt->close();
        
    } catch (Exception $e) {
        error_log('updateEvent Exception: ' . $e->getMessage());
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function deleteEvent() {
    global $conn;
    
    try {
        error_log('deleteEvent: Parsing request body');
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            throw new Exception('Failed to parse JSON request body');
        }
        
        if (!isset($data['id'])) {
            throw new Exception('Missing event ID');
        }
        
        $eventId = (int)$data['id'];
        $userId = $_SESSION['user_id'];
        
        error_log('deleteEvent: id=' . $eventId . ', user_id=' . $userId);
        
        // Check if event exists and get creator
        $checkStmt = $conn->prepare("
            SELECT created_by, title FROM school_events WHERE id = ?
        ");
        if (!$checkStmt) {
            throw new Exception('Prepare check failed: ' . $conn->error);
        }
        
        $checkStmt->bind_param('i', $eventId);
        if (!$checkStmt->execute()) {
            throw new Exception('Execute check failed: ' . $checkStmt->error);
        }
        
        $result = $checkStmt->get_result();
        if ($result->num_rows == 0) {
            throw new Exception('Event not found');
        }
        
        $event = $result->fetch_assoc();
        $eventTitle = $event['title'];
        $checkStmt->close();
        
        // Check authorization
        if ($event['created_by'] != $userId && $_SESSION['user_type'] != 'admin') {
            throw new Exception('Unauthorized - only creator or admin can delete');
        }
        
        // Delete event
        $deleteStmt = $conn->prepare("
            DELETE FROM school_events WHERE id = ?
        ");
        
        if (!$deleteStmt) {
            throw new Exception('Prepare delete failed: ' . $conn->error);
        }
        
        $deleteStmt->bind_param('i', $eventId);
        
        if (!$deleteStmt->execute()) {
            throw new Exception('Execute delete failed: ' . $deleteStmt->error);
        }
        
        error_log('deleteEvent: Event ID ' . $eventId . ' deleted successfully');
        notifyUsers($conn, $eventTitle, 'delete', $eventId);
        
        echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
        $deleteStmt->close();
        
    } catch (Exception $e) {
        error_log('deleteEvent Exception: ' . $e->getMessage());
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Notify all teachers and students about event changes
 */
function notifyUsers($conn, $eventTitle, $changeType, $eventId) {
    try {
        $notificationMessages = [
            'add' => "📅 New event: $eventTitle",
            'update' => "✏️ Event updated: $eventTitle",
            'delete' => "❌ Event deleted: $eventTitle"
        ];
        
        $message = $notificationMessages[$changeType] ?? "Event notification: $eventTitle";
        
        // Get all users (teachers and students)
        $usersQuery = $conn->prepare("SELECT id FROM users WHERE role IN ('teacher', 'student')");
        if (!$usersQuery) {
            return false;
        }
        
        $usersQuery->execute();
        $usersResult = $usersQuery->get_result();
        $allUsers = [];
        
        while ($row = $usersResult->fetch_assoc()) {
            $allUsers[] = $row['id'];
        }
        $usersQuery->close();
        
        if (empty($allUsers)) {
            return true; // No users to notify, but not an error
        }
        
        // Check if notifications table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
        if ($tableCheck->num_rows == 0) {
            // Notifications table doesn't exist, skip notification
            return true;
        }
        
        // Create notifications for each user
        $insertStmt = $conn->prepare("
            INSERT INTO notifications (user_id, message, type, reference_id, is_read, created_at)
            VALUES (?, ?, ?, ?, FALSE, NOW())
        ");
        
        if (!$insertStmt) {
            return false;
        }
        
        $notificationType = 'event_' . $changeType;
        
        foreach ($allUsers as $userId) {
            if (!$insertStmt->bind_param('isis', $userId, $message, $notificationType, $eventId)) {
                $insertStmt->close();
                return false;
            }
            if (!$insertStmt->execute()) {
                $insertStmt->close();
                return false;
            }
        }
        
        $insertStmt->close();
        return true;
    } catch (Exception $e) {
        // Log error but don't fail the main operation
        error_log('Notification error: ' . $e->getMessage());
        return false;
    }
}
?>
