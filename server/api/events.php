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

switch ($action) {
    case 'get':
        getEvents();
        break;
    case 'add':
        addEvent();
        break;
    case 'update':
        updateEvent();
        break;
    case 'delete':
        deleteEvent();
        break;
    case 'get_admin_events':
    case 'getAll':
        echo json_encode(getAllEventsForAdmin());
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

/**
 * Send notifications to all teachers and students about an event
 */
function notifyUsersAboutEvent($eventId, $title, $eventDate, $eventType) {
    global $conn;
    
    // Format the notification message
    $formattedDate = date('F j, Y', strtotime($eventDate));
    $typeLabel = ucfirst($eventType);
    $notificationTitle = "New School Event: $title";
    $notificationMessage = "A new $typeLabel has been scheduled for $formattedDate: $title";
    
    try {
        // Get all teachers
        $teacherQuery = "SELECT id FROM users WHERE user_type = 'teacher'";
        $teacherResult = $conn->query($teacherQuery);
        
        // Get all students
        $studentQuery = "SELECT id FROM users WHERE user_type = 'student'";
        $studentResult = $conn->query($studentQuery);
        
        // Prepare notification insert statement
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES (?, ?, ?, 'event', 0, NOW())");
        
        if (!$stmt) {
            error_log("Failed to prepare notification statement: " . $conn->error);
            return false;
        }
        
        $notifiedCount = 0;
        
        // Notify all teachers
        if ($teacherResult && $teacherResult->num_rows > 0) {
            while ($teacher = $teacherResult->fetch_assoc()) {
                $userId = $teacher['id'];
                $stmt->bind_param("iss", $userId, $notificationTitle, $notificationMessage);
                if ($stmt->execute()) {
                    $notifiedCount++;
                }
            }
        }
        
        // Notify all students
        if ($studentResult && $studentResult->num_rows > 0) {
            while ($student = $studentResult->fetch_assoc()) {
                $userId = $student['id'];
                $stmt->bind_param("iss", $userId, $notificationTitle, $notificationMessage);
                if ($stmt->execute()) {
                    $notifiedCount++;
                }
            }
        }
        
        $stmt->close();
        
        error_log("Sent event notifications to $notifiedCount users for event: $title");
        return $notifiedCount;
        
    } catch (Exception $e) {
        error_log("Error sending event notifications: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all events for calendar display
 */
function getEvents() {
    global $conn;
    
    try {
        $query = "SELECT 
                    id,
                    title,
                    event_date as start,
                    COALESCE(end_date, event_date) as end,
                    event_type as type,
                    description,
                    is_published
                  FROM school_events 
                  WHERE is_published = 1
                  ORDER BY event_date ASC";
        
        $result = $conn->query($query);
        
        if (!$result) {
            echo json_encode(['error' => 'Database error: ' . $conn->error]);
            return;
        }
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            // Format for FullCalendar
            $event = [
                'id' => $row['id'],
                'title' => $row['title'],
                'start' => $row['start'],
                'end' => $row['end'],
                'extendedProps' => [
                    'type' => $row['type'],
                    'description' => $row['description'],
                    'is_published' => $row['is_published']
                ]
            ];
            
            // Set color based on event type
            switch ($row['type']) {
                case 'holiday':
                    $event['backgroundColor'] = '#4ecdc4';
                    $event['borderColor'] = '#45b7aa';
                    break;
                case 'deadline':
                    $event['backgroundColor'] = '#ff6b6b';
                    $event['borderColor'] = '#ff5252';
                    break;
                case 'examination':
                    $event['backgroundColor'] = '#9b59b6';
                    $event['borderColor'] = '#8e44ad';
                    break;
                case 'celebration':
                    $event['backgroundColor'] = '#27ae60';
                    $event['borderColor'] = '#219a52';
                    break;
                case 'meeting':
                    $event['backgroundColor'] = '#3498db';
                    $event['borderColor'] = '#2980b9';
                    break;
                default:
                    $event['backgroundColor'] = '#ffd93d';
                    $event['borderColor'] = '#ffb800';
                    $event['textColor'] = '#2d3436';
            }
            
            $events[] = $event;
        }
        
        echo json_encode($events);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error fetching events: ' . $e->getMessage()]);
    }
}

/**
 * Get all events for admin management (including unpublished)
 */
function getAllEventsForAdmin() {
    global $conn;
    
    try {
        $query = "SELECT 
                    e.id,
                    e.title,
                    e.event_date,
                    e.end_date,
                    e.event_type,
                    e.description,
                    e.is_published,
                    e.created_at,
                    CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                  FROM school_events e
                  LEFT JOIN users u ON e.created_by = u.id
                  ORDER BY e.event_date DESC";
        
        $result = $conn->query($query);
        
        if (!$result) {
            return ['error' => 'Database error: ' . $conn->error];
        }
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        return ['success' => true, 'events' => $events];
        
    } catch (Exception $e) {
        return ['error' => 'Error fetching events: ' . $e->getMessage()];
    }
}

/**
 * Add a new event
 */
function addEvent() {
    global $conn;
    
    // Check if user is logged in - if not, use a default admin ID
    $createdBy = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
        return;
    }
    
    $title = isset($input['title']) ? trim($input['title']) : '';
    $startDate = isset($input['start']) ? $input['start'] : '';
    $endDate = isset($input['end']) ? $input['end'] : $startDate;
    $eventType = isset($input['type']) ? $input['type'] : 'other';
    $customType = isset($input['custom_type']) ? trim($input['custom_type']) : '';
    $description = isset($input['description']) ? trim($input['description']) : '';
    $isPublished = isset($input['published']) ? (int)$input['published'] : 1;
    
    // If event type is "other" and custom type is provided, store it in description
    if ($eventType === 'other' && !empty($customType)) {
        $description = "Custom Type: " . $customType . ($description ? "\n" . $description : "");
    }
    
    // Validate required fields
    if (empty($title) || empty($startDate)) {
        echo json_encode(['success' => false, 'error' => 'Title and start date are required']);
        return;
    }
    
    // Validate event type
    $validTypes = ['holiday', 'examination', 'deadline', 'celebration', 'meeting', 'other'];
    if (!in_array($eventType, $validTypes)) {
        $eventType = 'other';
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO school_events (title, event_date, end_date, event_type, description, is_published, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => 'Database prepare error: ' . $conn->error]);
            return;
        }
        
        $stmt->bind_param("sssssii", $title, $startDate, $endDate, $eventType, $description, $isPublished, $createdBy);
        
        if ($stmt->execute()) {
            $newEventId = $conn->insert_id;
            
            // Send notifications to all teachers and students if event is published
            $notifiedCount = 0;
            if ($isPublished) {
                $notifiedCount = notifyUsersAboutEvent($newEventId, $title, $startDate, $eventType);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Event added successfully',
                'id' => $newEventId,
                'notifications_sent' => $notifiedCount
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add event: ' . $stmt->error]);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error adding event: ' . $e->getMessage()]);
    }
}

/**
 * Update an existing event
 */
function updateEvent() {
    global $conn;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        return;
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
        return;
    }
    
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    $title = isset($input['title']) ? trim($input['title']) : '';
    $startDate = isset($input['start']) ? $input['start'] : '';
    $endDate = isset($input['end']) ? $input['end'] : $startDate;
    $eventType = isset($input['type']) ? $input['type'] : 'other';
    $description = isset($input['description']) ? trim($input['description']) : '';
    $isPublished = isset($input['published']) ? (int)$input['published'] : 1;
    
    // Validate required fields
    if ($id <= 0 || empty($title) || empty($startDate)) {
        echo json_encode(['success' => false, 'error' => 'ID, title and start date are required']);
        return;
    }
    
    // Validate event type
    $validTypes = ['holiday', 'examination', 'deadline', 'celebration', 'meeting', 'other'];
    if (!in_array($eventType, $validTypes)) {
        $eventType = 'other';
    }
    
    try {
        $stmt = $conn->prepare("UPDATE school_events SET title = ?, event_date = ?, end_date = ?, event_type = ?, description = ?, is_published = ? WHERE id = ?");
        
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => 'Database prepare error: ' . $conn->error]);
            return;
        }
        
        $stmt->bind_param("sssssii", $title, $startDate, $endDate, $eventType, $description, $isPublished, $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'No event found with that ID or no changes made']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update event: ' . $stmt->error]);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error updating event: ' . $e->getMessage()]);
    }
}

/**
 * Delete an event
 */
function deleteEvent() {
    global $conn;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        return;
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
        return;
    }
    
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Valid event ID is required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM school_events WHERE id = ?");
        
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => 'Database prepare error: ' . $conn->error]);
            return;
        }
        
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'No event found with that ID']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete event: ' . $stmt->error]);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error deleting event: ' . $e->getMessage()]);
    }
}

?>
