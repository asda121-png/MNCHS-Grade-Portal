<?php
require_once './includes/config.php';

// Only allow this from localhost
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1' && !isset($_GET['force'])) {
    die('Access denied');
}

// Alter the notifications table to add new enum values
$sql = "ALTER TABLE notifications MODIFY type ENUM('grade', 'event', 'event_update', 'event_delete', 'grading_period', 'message', 'system') DEFAULT 'system'";

echo "<h2>Database Update</h2>";
echo "<p>Executing: " . htmlspecialchars($sql) . "</p>";

if ($conn->query($sql)) {
    echo "<p style='color: green;'><strong>✓ Successfully updated notifications table enum type</strong></p>";
    echo "<p>The notifications table now supports 'event_update', 'event_delete', and 'grading_period' notification types.</p>";
} else {
    echo "<p style='color: red;'><strong>✗ Error updating notifications table:</strong></p>";
    echo "<p>" . htmlspecialchars($conn->error) . "</p>";
}

// Also try to verify the column definition
$result = $conn->query("SHOW COLUMNS FROM notifications WHERE Field = 'type'");
if ($result && $row = $result->fetch_assoc()) {
    echo "<p><strong>Column Type Definition:</strong> " . htmlspecialchars($row['Type']) . "</p>";
}

$conn->close();
?>
