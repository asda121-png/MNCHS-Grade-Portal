<?php
session_start();
require_once 'includes/config.php';

// Get current user
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Not Logged In';
$userRole = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'Unknown';

echo "<h1>Notification System Test</h1>";
echo "<p>Current User: <strong>$username</strong> (ID: $userId, Role: $userRole)</p>";

if (!$userId) {
    echo "<p style='color: red;'>You are not logged in. Please log in first.</p>";
    exit;
}

// Check notifications count
$stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$unreadCount = $row['unread_count'];

echo "<h2>Unread Notifications: <strong style='color: #e74c3c;'>$unreadCount</strong></h2>";

// List all notifications for this user
echo "<h3>Your Recent Notifications:</h3>";
$stmt = $conn->prepare("SELECT id, title, message, type, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 30");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='color: orange;'>No notifications found.</p>";
} else {
    echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Message</th><th>Type</th><th>Status</th><th>Created</th></tr>";
    while ($notif = $result->fetch_assoc()) {
        $status = $notif['is_read'] ? '✓ Read' : '⚠ Unread';
        echo "<tr>";
        echo "<td>{$notif['id']}</td>";
        echo "<td>" . htmlspecialchars($notif['title']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($notif['message'], 0, 50)) . "...</td>";
        echo "<td><strong>{$notif['type']}</strong></td>";
        echo "<td>$status</td>";
        echo "<td>" . date('m/d/Y H:i', strtotime($notif['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check all school events
echo "<h3>All School Events:</h3>";
$result = $conn->query("SELECT id, title, event_type, event_date, is_published, created_at FROM school_events ORDER BY event_date DESC LIMIT 20");

if ($result->num_rows === 0) {
    echo "<p style='color: orange;'>No events found.</p>";
} else {
    echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Type</th><th>Date</th><th>Published</th><th>Created</th></tr>";
    while ($event = $result->fetch_assoc()) {
        $published = $event['is_published'] ? '✓ Yes' : '✗ No';
        echo "<tr>";
        echo "<td>{$event['id']}</td>";
        echo "<td>" . htmlspecialchars($event['title']) . "</td>";
        echo "<td>{$event['event_type']}</td>";
        echo "<td>" . date('m/d/Y', strtotime($event['event_date'])) . "</td>";
        echo "<td>$published</td>";
        echo "<td>" . date('m/d/Y H:i', strtotime($event['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check notification counts by type
echo "<h3>Notification Summary:</h3>";
$result = $conn->query("SELECT type, COUNT(*) as count FROM notifications WHERE user_id = $userId GROUP BY type");
if ($result->num_rows > 0) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row['type']) . ": " . $row['count'] . "</li>";
    }
    echo "</ul>";
}

$stmt->close();
$conn->close();
?>
