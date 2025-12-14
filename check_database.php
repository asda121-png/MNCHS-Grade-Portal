<?php
require_once 'includes/config.php';

// Get all teacher accounts with their details
echo "<h2>All Teacher Accounts in Database:</h2>";
echo "<pre>";

$query = "SELECT u.id, u.username, u.email, u.password, u.first_name, u.last_name, u.created_at, t.teacher_id, t.grade_levels 
          FROM users u 
          LEFT JOIN teachers t ON u.id = t.user_id 
          WHERE u.role = 'teacher'
          ORDER BY u.created_at ASC";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " teacher(s):\n\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "=== TEACHER ACCOUNT ===\n";
        echo "Username: " . $row['username'] . "\n";
        echo "Email: " . $row['email'] . "\n";
        echo "Name: " . $row['first_name'] . " " . $row['last_name'] . "\n";
        echo "Teacher ID: " . ($row['teacher_id'] ?? 'N/A') . "\n";
        echo "Grade Levels: " . ($row['grade_levels'] ?? 'N/A') . "\n";
        echo "Created: " . $row['created_at'] . "\n";
        echo "Password Hash: " . substr($row['password'], 0, 20) . "... (hashed)\n";
        echo "\n";
    }
} else {
    echo "No teachers found in database!\n";
    
    // Check if users table is empty
    $user_count = "SELECT COUNT(*) as total FROM users";
    $count_result = $conn->query($user_count);
    $count_row = $count_result->fetch_assoc();
    echo "\nTotal users in database: " . $count_row['total'] . "\n";
    
    if ($count_row['total'] == 0) {
        echo "\nNo test data found. Please run database seeding first.\n";
    }
}

echo "</pre>";

// Check database structure
echo "<h3>Database Connection Status:</h3>";
echo "<p>Database: " . $conn->select_db('mnchs_grade_portal') ? "Connected ✓" : "Connection failed";
echo "</p>";
?>
