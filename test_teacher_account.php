<?php
session_start();
require_once 'includes/config.php';

// Query for teachers with grades 7-12
$query = "SELECT u.id, u.username, u.email, u.first_name, u.last_name, t.teacher_id, t.grade_levels FROM users u 
          JOIN teachers t ON u.id = t.user_id 
          WHERE u.role = 'teacher' 
          ORDER BY u.created_at ASC
          LIMIT 10";

$result = $conn->query($query);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Teacher Test Accounts</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .password { background-color: #fff3cd; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Teacher Test Accounts (Grades 7-12)</h1>";

if ($result && $result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Name</th>
                <th>Teacher ID</th>
                <th>Grade Levels</th>
            </tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['username']) . "</td>
                <td>" . htmlspecialchars($row['email']) . "</td>
                <td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>
                <td>" . htmlspecialchars($row['teacher_id']) . "</td>
                <td>" . htmlspecialchars($row['grade_levels']) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No teachers found in the database.</p>";
}

// Check if database is populated
$count_query = "SELECT COUNT(*) as total FROM teachers";
$count_result = $conn->query($count_query);
$count_row = $count_result->fetch_assoc();

echo "<hr>";
echo "<p><strong>Total Teachers in Database:</strong> " . $count_row['total'] . "</p>";
echo "<p><em>Note: Default test password is usually 'password' for test accounts.</em></p>";
echo "</body></html>";
?>
