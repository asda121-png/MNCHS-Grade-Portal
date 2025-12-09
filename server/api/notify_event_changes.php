<?php
/**
 * Notify all teachers and students of event changes
 * Called when admin adds, updates, or deletes events
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../../includes/config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$eventId = $_POST['event_id'] ?? $_GET['event_id'] ?? null;
$eventTitle = $_POST['event_title'] ?? $_GET['event_title'] ?? 'Event';
$eventType = $_POST['event_type'] ?? $_GET['event_type'] ?? 'update'; // add, update, delete

if (!$action) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing action parameter']);
    exit;
}

if (!$eventTitle) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing event title']);
    exit;
}

/**
 * Notify all users of an event change
 */
function notifyAllUsers($conn, $eventTitle, $changeType, $eventId) {
    $notificationMessages = [
        'add' => "New event added: $eventTitle",
        'update' => "Event updated: $eventTitle",
        'delete' => "Event deleted: $eventTitle"
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
    $allUsers = array_unique($allUsers); // Remove duplicates
    
    if (empty($allUsers)) {
        return ['success' => false, 'message' => 'No users to notify'];
    }
    
    // Create notifications for each user
    $notificationCount = 0;
    $insertStmt = $conn->prepare("
        INSERT INTO notifications (user_id, message, type, reference_id, is_read, created_at)
        VALUES (?, ?, ?, ?, FALSE, NOW())
    ");
    
    if (!$insertStmt) {
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }
    
    $notificationType = 'event_' . $changeType; // event_add, event_update, event_delete
    
    foreach ($allUsers as $userId) {
        if ($insertStmt->bind_param('isis', $userId, $message, $notificationType, $eventId)) {
            if ($insertStmt->execute()) {
                $notificationCount++;
            }
        }
    }
    
    $insertStmt->close();
    
    return [
        'success' => true,
        'message' => "Notified $notificationCount users",
        'count' => $notificationCount
    ];
}

// Handle the notification
$result = notifyAllUsers($conn, $eventTitle, $eventType, $eventId);

echo json_encode($result);
$conn->close();
?>
