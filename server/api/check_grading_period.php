<?php
/**
 * API endpoint to check if grading is currently allowed
 * Returns: { allowed: bool, message: string, current_period: object|null }
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../../includes/config.php';

try {
    // Get current date and time
    $now = new DateTime();
    $current_date = $now->format('Y-m-d');
    
    // Check for active grading period (current date falls within start_date and end_date)
    $stmt = $conn->prepare("
        SELECT id, quarter, start_date, end_date, created_by
        FROM grading_periods
        WHERE start_date <= ? AND end_date >= ?
        LIMIT 1
    ");
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param('ss', $current_date, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $period = $result->fetch_assoc();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'allowed' => true,
            'message' => 'Grading period is currently active. You can enter grades now.',
            'current_period' => [
                'id' => $period['id'],
                'quarter' => $period['quarter'],
                'start_date' => $period['start_date'],
                'end_date' => $period['end_date']
            ]
        ]);
    } else {
        $stmt->close();
        
        // Get next upcoming grading period
        $stmt = $conn->prepare("
            SELECT id, quarter, start_date, end_date
            FROM grading_periods
            WHERE start_date > ?
            ORDER BY start_date ASC
            LIMIT 1
        ");
        
        $stmt->bind_param('s', $current_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $next_period = null;
        $message = 'No active grading period. Grade entry is currently disabled.';
        
        if ($result->num_rows > 0) {
            $next_period = $result->fetch_assoc();
            $message = 'Grade entry will be available starting ' . $next_period['start_date'];
        }
        
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'allowed' => false,
            'message' => $message,
            'next_period' => $next_period
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>
