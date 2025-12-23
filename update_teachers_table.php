<?php
require_once 'includes/config.php';

// Add assigned_sections column to teachers table
$sql = "ALTER TABLE `teachers` ADD COLUMN `assigned_sections` TEXT DEFAULT NULL AFTER `specialization`";

echo "<h2>Database Update: Add assigned_sections to teachers table</h2>";

if ($conn->query($sql)) {
    echo "<p style='color: green;'><strong>✓ Successfully added 'assigned_sections' column to teachers table.</strong></p>";
} else {
    // Check if error is because column already exists
    if (strpos($conn->error, "Duplicate column name") !== false) {
        echo "<p style='color: orange;'><strong>⚠ Column 'assigned_sections' already exists.</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>✗ Error updating table: " . htmlspecialchars($conn->error) . "</strong></p>";
    }
}

// Verify structure
$result = $conn->query("SHOW COLUMNS FROM teachers");
if ($result) {
    echo "<h3>Current Teachers Table Structure:</h3><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row['Field']) . " (" . htmlspecialchars($row['Type']) . ")</li>";
    }
    echo "</ul>";
}

$conn->close();
?>