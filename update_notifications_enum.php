<?php
require_once './includes/config.php';

// Alter the notifications table to add new enum values
$sql = "ALTER TABLE notifications MODIFY type ENUM('grade', 'event', 'event_update', 'event_delete', 'message', 'system') DEFAULT 'system'";

if ($conn->query($sql)) {
    echo "Successfully updated notifications table enum type\n";
} else {
    echo "Error updating notifications table: " . $conn->error . "\n";
}

$conn->close();
?>
