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
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

switch ($action) {
    case 'get_count':
        getNotificationCount();
        break;
    case 'get_all':
        getAllNotifications();
        break;
    case 'mark_read':
        markAsRead();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function getNotificationCount() {
    global $conn, $user_id;
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE user_id = ? AND is_read = FALSE
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'count' => $row['count']
    ]);
}

function getAllNotifications() {
    global $conn, $user_id;
    
    $stmt = $conn->prepare("
        SELECT id, title, message, is_read, created_at 
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 20
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
}

function markAsRead() {
    global $conn, $user_id;
    
    $notification_id = $_POST['notification_id'] ?? null;
    
    if (!$notification_id) {
        // Mark all as read
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = TRUE 
            WHERE user_id = ?
        ");
        $stmt->bind_param('i', $user_id);
    } else {
        // Mark specific as read
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = TRUE 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->bind_param('ii', $notification_id, $user_id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update notification']);
    }
    $stmt->close();
}
?>
