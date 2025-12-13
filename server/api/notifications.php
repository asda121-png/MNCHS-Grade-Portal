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

// Get action from query string
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Get current user ID from session
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

switch ($action) {
    case 'get_count':
        getUnreadCount($userId);
        break;
    case 'get_all':
        getAllNotifications($userId);
        break;
    case 'get_unread':
        getUnreadNotifications($userId);
        break;
    case 'mark_read':
        markAsRead($userId);
        break;
    case 'mark_all_read':
        markAllAsRead($userId);
        break;
    case 'delete':
        deleteNotification($userId);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

/**
 * Get unread notification count for user
 */
function getUnreadCount($userId) {
    global $conn;
    
    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated', 'count' => 0]);
        return;
    }
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'count' => (int)$row['count']
        ]);
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage(), 'count' => 0]);
    }
}

/**
 * Get all notifications for user
 */
function getAllNotifications($userId) {
    global $conn;
    
    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated', 'notifications' => []]);
        return;
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT id, title, message, type, is_read, created_at 
            FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'message' => $row['message'],
                'type' => $row['type'],
                'is_read' => (bool)$row['is_read'],
                'created_at' => $row['created_at'],
                'time_ago' => timeAgo($row['created_at'])
            ];
        }
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications
        ]);
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage(), 'notifications' => []]);
    }
}

/**
 * Get unread notifications for user
 */
function getUnreadNotifications($userId) {
    global $conn;
    
    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated', 'notifications' => []]);
        return;
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT id, title, message, type, is_read, created_at 
            FROM notifications 
            WHERE user_id = ? AND is_read = 0
            ORDER BY created_at DESC 
            LIMIT 20
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'message' => $row['message'],
                'type' => $row['type'],
                'is_read' => false,
                'created_at' => $row['created_at'],
                'time_ago' => timeAgo($row['created_at'])
            ];
        }
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications
        ]);
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage(), 'notifications' => []]);
    }
}

/**
 * Mark notification as read
 */
function markAsRead($userId) {
    global $conn;
    
    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $notificationId = isset($input['id']) ? (int)$input['id'] : 0;
    
    if ($notificationId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notificationId, $userId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to mark notification as read']);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Mark all notifications as read for user
 */
function markAllAsRead($userId) {
    global $conn;
    
    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'All notifications marked as read',
                'affected' => $stmt->affected_rows
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to mark notifications as read']);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Delete notification
 */
function deleteNotification($userId) {
    global $conn;
    
    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $notificationId = isset($input['id']) ? (int)$input['id'] : 0;
    
    if ($notificationId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notificationId, $userId);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Notification deleted']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Notification not found']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete notification']);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Helper function to convert timestamp to time ago format
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>
