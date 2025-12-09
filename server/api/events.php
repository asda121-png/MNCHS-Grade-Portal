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

// Handle different actions
switch ($action) {
    case 'add':
        addEvent();
        break;
    case 'get':
        getEvents();
        break;
    case 'delete':
        deleteEvent();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function addEvent() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['title']) || !isset($data['start'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    $title = $data['title'];
    $start = $data['start'];
    $end = $data['end'] ?? $data['start'];
    $userId = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("
        INSERT INTO school_events (title, event_date, end_date, event_type, created_by, is_published)
        VALUES (?, ?, ?, 'other', ?, TRUE)
    ");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param('sssi', $title, $start, $end, $userId);
    
    if ($stmt->execute()) {
        $eventId = $conn->insert_id;
        
        // Notify all users about the new event
        notifyUsers($conn, $title, 'add', $eventId);
        
        echo json_encode([
            'success' => true,
            'id' => $eventId,
            'message' => 'Event added successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add event: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function getEvents() {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT id, title, event_date as start, end_date as end, event_type, created_by
        FROM school_events
        WHERE is_published = TRUE
        ORDER BY event_date ASC
    ");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
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
    
    echo json_encode($events);
    $stmt->close();
}

function deleteEvent() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing event ID']);
        return;
    }
    
    $eventId = $data['id'];
    $userId = $_SESSION['user_id'];
    
    // Check if user created this event or is admin
    $stmt = $conn->prepare("
        SELECT created_by, title FROM school_events WHERE id = ?
    ");
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    
    if (!$event) {
        http_response_code(404);
        echo json_encode(['error' => 'Event not found']);
        $stmt->close();
        return;
    }
    
    $eventTitle = $event['title'];
    
    // Only allow deletion by creator or admin
    if ($event['created_by'] != $userId && $_SESSION['role'] != 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        $stmt->close();
        return;
    }
    
    $stmt = $conn->prepare("
        DELETE FROM school_events WHERE id = ?
    ");
    $stmt->bind_param('i', $eventId);
    
    if ($stmt->execute()) {
        // Notify all users about the deleted event
        notifyUsers($conn, $eventTitle, 'delete', $eventId);
        
        echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete event']);
    }
    
    $stmt->close();
}

/**
 * Notify all teachers and students about event changes
 */
function notifyUsers($conn, $eventTitle, $changeType, $eventId) {
    $notificationMessages = [
        'add' => "📅 New event: $eventTitle",
        'update' => "✏️ Event updated: $eventTitle",
        'delete' => "❌ Event deleted: $eventTitle"
    ];
    
    $message = $notificationMessages[$changeType] ?? "Event notification: $eventTitle";
    
    // Get all teachers
    $teachersQuery = $conn->prepare("SELECT id FROM users WHERE role = 'teacher'");
    $teachersQuery->execute();
    $teachersResult = $teachersQuery->get_result();
    $teachers = [];
    while ($row = $teachersResult->fetch_assoc()) {
        $teachers[] = $row['id'];
    }
    $teachersQuery->close();
    
    // Get all students
    $studentsQuery = $conn->prepare("SELECT user_id FROM students");
    $studentsQuery->execute();
    $studentsResult = $studentsQuery->get_result();
    $students = [];
    while ($row = $studentsResult->fetch_assoc()) {
        $students[] = $row['user_id'];
    }
    $studentsQuery->close();
    
    // Combine all user IDs
    $allUsers = array_merge($teachers, $students);
    $allUsers = array_unique($allUsers);
    
    if (empty($allUsers)) {
        return false;
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
        if ($insertStmt->bind_param('isis', $userId, $message, $notificationType, $eventId)) {
            $insertStmt->execute();
        }
    }
    
    $insertStmt->close();
    return true;
}
?>
