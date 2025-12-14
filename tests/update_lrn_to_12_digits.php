<?php
/**
 * Update LRN to 12-digit format
 * Converts existing 8-digit LRN (YYYY + 4-digit counter) to 12-digit LRN (YYYY + 8-digit counter)
 */

require_once "../includes/config.php";

echo "<h2>LRN Update to 12-Digit Format</h2>";

try {
    // Get all students with 8-digit LRN
    $query = "SELECT id, lrn FROM students WHERE lrn IS NOT NULL AND LENGTH(lrn) = 8 ORDER BY id ASC";
    $result = $conn->query($query);
    
    if (!$result) {
        echo "<p style='color: red;'>Error fetching students: " . $conn->error . "</p>";
        exit;
    }
    
    $updated = 0;
    $failed = 0;
    
    echo "<p>Converting 8-digit LRN to 12-digit format...</p>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='margin: 20px 0;'>";
    echo "<tr><th>Student ID</th><th>Old LRN (8-digit)</th><th>New LRN (12-digit)</th><th>Status</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $studentId = $row['id'];
        $oldLrn = $row['lrn'];
        
        // Convert format: 20250001 -> 202500000001
        // Extract year (first 4 digits) and counter (last 4 digits)
        $year = substr($oldLrn, 0, 4);
        $counter = substr($oldLrn, 4, 4);
        
        // Create 12-digit LRN: year + 8-digit padded counter
        $newLrn = $year . str_pad($counter, 8, '0', STR_PAD_LEFT);
        
        // Update the student record
        $updateQuery = "UPDATE students SET lrn = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        
        if (!$updateStmt) {
            echo "<tr><td>{$studentId}</td><td>{$oldLrn}</td><td>{$newLrn}</td><td style='color: red;'>Prepare Error</td></tr>";
            $failed++;
            continue;
        }
        
        $updateStmt->bind_param("si", $newLrn, $studentId);
        
        if ($updateStmt->execute()) {
            echo "<tr><td>{$studentId}</td><td>{$oldLrn}</td><td>{$newLrn}</td><td style='color: green;'>✓ Updated</td></tr>";
            $updated++;
        } else {
            echo "<tr><td>{$studentId}</td><td>{$oldLrn}</td><td>{$newLrn}</td><td style='color: red;'>✗ Failed</td></tr>";
            $failed++;
        }
        
        $updateStmt->close();
    }
    
    echo "</table>";
    
    echo "<h3>Summary:</h3>";
    echo "<p><strong>Successfully updated:</strong> {$updated} students</p>";
    if ($failed > 0) {
        echo "<p style='color: red;'><strong>Failed:</strong> {$failed} students</p>";
    }
    
    // Verify the changes
    echo "<h3>Verification:</h3>";
    $verifyQuery = "SELECT 
                        COUNT(*) as total_students,
                        SUM(CASE WHEN LENGTH(lrn) = 12 THEN 1 ELSE 0 END) as twelve_digit_count,
                        SUM(CASE WHEN LENGTH(lrn) = 8 THEN 1 ELSE 0 END) as eight_digit_count
                   FROM students";
    
    $verifyResult = $conn->query($verifyQuery);
    $verifyRow = $verifyResult->fetch_assoc();
    
    echo "<p>Total Students: " . $verifyRow['total_students'] . "</p>";
    echo "<p>12-Digit LRN: " . ($verifyRow['twelve_digit_count'] ?? 0) . "</p>";
    echo "<p>8-Digit LRN: " . ($verifyRow['eight_digit_count'] ?? 0) . "</p>";
    
    if ($verifyRow['eight_digit_count'] == 0) {
        echo "<p style='color: green;'><strong>✓ All LRN values successfully converted to 12-digit format!</strong></p>";
    }
    
    $result->free();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

?>
