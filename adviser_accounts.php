<?php
require_once 'includes/config.php';

// Query for teacher advisers
$query = "SELECT u.id, u.username, u.email, u.first_name, u.last_name, t.teacher_id, 
          t.is_adviser, t.adviser_class_id, c.class_name, c.grade_level, c.section
          FROM users u 
          JOIN teachers t ON u.id = t.user_id 
          LEFT JOIN classes c ON t.adviser_class_id = c.id
          WHERE u.role = 'teacher' 
          ORDER BY u.created_at ASC";

$result = $conn->query($query);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Teacher Adviser Accounts</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .account { border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: #f9f9f9; border-radius: 5px; }
        .adviser-account { background: #d4edda; border-color: #28a745; border-left: 4px solid #28a745; }
        .credential { margin: 8px 0; font-family: monospace; }
        .label { font-weight: bold; color: #333; }
        .value { color: #0066cc; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .adviser { background-color: #d4edda; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Teacher Adviser Accounts</h1>";

$adviserCount = 0;
$allTeachers = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $allTeachers[] = $row;
        if ($row['is_adviser']) {
            $adviserCount++;
        }
    }
    
    if ($adviserCount > 0) {
        echo "<div class='account adviser-account'>
                <h2>✓ Adviser Accounts Found: " . $adviserCount . "</h2>";
        
        foreach ($allTeachers as $teacher) {
            if ($teacher['is_adviser']) {
                echo "
                <div style='margin: 15px 0; padding: 12px; background: white; border-radius: 3px; border-left: 4px solid #28a745;'>
                    <div style='font-size: 18px; font-weight: bold; color: #28a745;'>✓ CLASS ADVISER</div>
                    <div class='credential'>
                        <span class='label'>Teacher:</span> <span class='value'>" . htmlspecialchars($teacher['first_name'] . " " . $teacher['last_name']) . "</span>
                    </div>
                    <div class='credential'>
                        <span class='label'>Username:</span> <span class='value'>" . htmlspecialchars($teacher['username']) . "</span>
                    </div>
                    <div class='credential'>
                        <span class='label'>Email:</span> <span class='value'>" . htmlspecialchars($teacher['email']) . "</span>
                    </div>
                    <div class='credential'>
                        <span class='label'>Teacher ID:</span> <span class='value'>" . htmlspecialchars($teacher['teacher_id']) . "</span>
                    </div>
                    <div class='credential'>
                        <span class='label'>Advised Class:</span> <span class='value'>" . htmlspecialchars($teacher['class_name'] ?? 'Not assigned') . "</span>
                    </div>
                </div>";
            }
        }
        
        echo "</div>";
    } else {
        echo "<div class='account' style='background: #fff3cd; border-color: #ffc107;'>
                <h2>⚠ No Adviser Accounts Found</h2>
                <p>No teachers are currently configured as class advisers.</p>
                <p>The database has " . count($allTeachers) . " teacher(s) total.</p>";
        
        echo "<h3>Available Teachers (to be set as adviser):</h3>";
        echo "<table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Teacher ID</th>
                    </tr>
                </thead>
                <tbody>";
        
        foreach ($allTeachers as $teacher) {
            echo "<tr>
                    <td>" . htmlspecialchars($teacher['username']) . "</td>
                    <td>" . htmlspecialchars($teacher['first_name'] . " " . $teacher['last_name']) . "</td>
                    <td>" . htmlspecialchars($teacher['email']) . "</td>
                    <td>" . htmlspecialchars($teacher['teacher_id']) . "</td>
                  </tr>";
        }
        
        echo "</tbody></table>";
        echo "</div>";
    }
} else {
    echo "<p>No teachers found in database.</p>";
}

echo "
        <div class='account' style='background: #f0f7ff; border-color: #0066cc;'>
            <h3>📋 About Class Advisers:</h3>
            <ul>
                <li>A class adviser is a teacher responsible for a specific class/section</li>
                <li>Each class can have only one adviser</li>
                <li>An adviser typically teaches 1 section in the system</li>
                <li>Login with the adviser account's credentials to access adviser functions</li>
            </ul>
        </div>

        <div class='account' style='background: #f9f9f9; border-left: 4px solid #666;'>
            <h3>💡 Default Login:</h3>
            <div class='credential'>
                <span class='label'>Password:</span> <span class='value'>password</span>
            </div>
            <p style='color: #666;'>Use this password for any teacher account.</p>
        </div>
    </div>
</body>
</html>";
?>
