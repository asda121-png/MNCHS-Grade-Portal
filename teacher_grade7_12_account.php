<?php
require_once 'includes/config.php';

// Update teacher_smith to cover grades 7-12
$updateQuery = "UPDATE teachers SET grade_levels = '7,8,9,10,11,12' WHERE user_id = (SELECT id FROM users WHERE username = 'teacher_smith' LIMIT 1)";

if ($conn->query($updateQuery)) {
    $affectedRows = $conn->affected_rows;
    if ($affectedRows > 0) {
        echo "✓ Successfully updated teacher_smith to cover Grades 7-12<br><br>";
    } else {
        echo "No rows were updated. Let me check the teacher record...<br><br>";
    }
} else {
    echo "Error updating teacher: " . $conn->error . "<br><br>";
}

// Retrieve and display the teacher account details
$query = "SELECT u.id, u.username, u.email, u.first_name, u.last_name, t.teacher_id, t.grade_levels, t.is_adviser, c.class_name
          FROM users u 
          JOIN teachers t ON u.id = t.user_id 
          LEFT JOIN classes c ON t.adviser_class_id = c.id
          WHERE u.username = 'teacher_smith'";

$result = $conn->query($query);
$teacher = $result->fetch_assoc();

echo "<!DOCTYPE html>
<html>
<head>
    <title>Teacher Account - Grades 7-12</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; }
        .account { border: 2px solid #28a745; padding: 25px; background: #d4edda; border-radius: 8px; }
        .credential { margin: 15px 0; }
        .label { font-weight: bold; color: #333; display: inline-block; width: 140px; }
        .value { color: #0066cc; font-family: monospace; font-weight: bold; }
        h1 { color: #28a745; text-align: center; }
        .badge { display: inline-block; background: #28a745; color: white; padding: 5px 10px; border-radius: 3px; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>✓ Teacher Account - All Grades (7-12)</h1>
        
        <div class='account'>
            <div class='credential'>
                <span class='label'>Name:</span>
                <span class='value'>" . htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) . "</span>
            </div>
            
            <div class='credential'>
                <span class='label'>Username:</span>
                <span class='value'>" . htmlspecialchars($teacher['username']) . "</span>
            </div>
            
            <div class='credential'>
                <span class='label'>Password:</span>
                <span class='value'>password</span>
            </div>
            
            <div class='credential'>
                <span class='label'>Email:</span>
                <span class='value'>" . htmlspecialchars($teacher['email']) . "</span>
            </div>
            
            <div class='credential'>
                <span class='label'>Teacher ID:</span>
                <span class='value'>" . htmlspecialchars($teacher['teacher_id']) . "</span>
            </div>
            
            <div class='credential'>
                <span class='label'>Grade Levels:</span>
                <span class='badge'>" . htmlspecialchars($teacher['grade_levels']) . "</span>
            </div>
            
            <div class='credential'>
                <span class='label'>Class Adviser:</span>
                <span class='value'>" . htmlspecialchars($teacher['class_name'] ?? 'Yes') . "</span>
            </div>
        </div>
    </div>
</body>
</html>";
?>
