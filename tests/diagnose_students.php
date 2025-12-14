<?php
/**
 * Database Diagnostic Script
 * Checks students table status and helps populate data
 */

require_once "../includes/config.php";

echo "<h2>Database Diagnostic Report</h2>";

try {
    // Check 1: How many students exist?
    echo "<h3>1. Student Count:</h3>";
    $count_result = $conn->query("SELECT COUNT(*) as total FROM students");
    $count_row = $count_result->fetch_assoc();
    echo "<p>Total Students: <strong>" . $count_row['total'] . "</strong></p>";
    
    if ($count_row['total'] == 0) {
        echo "<p style='color: red;'>⚠️ No students found! Need to create sample students first.</p>";
    }
    
    // Check 2: Show all students data
    echo "<h3>2. All Student Records:</h3>";
    $students_result = $conn->query("
        SELECT s.id, s.user_id, s.student_id, s.lrn, s.student_name, u.username, u.first_name, u.last_name, u.email
        FROM students s
        INNER JOIN users u ON s.user_id = u.id
    ");
    
    if ($students_result && $students_result->num_rows > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
        echo "<tr><th>ID</th><th>User ID</th><th>Student ID</th><th>LRN</th><th>Student Name</th><th>Username</th><th>Email</th></tr>";
        
        while ($row = $students_result->fetch_assoc()) {
            $lrn = $row['lrn'] ?? "<span style='color: red;'>NULL</span>";
            $name = $row['student_name'] ?? "<span style='color: red;'>NULL</span>";
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['user_id']}</td>";
            echo "<td>{$row['student_id']}</td>";
            echo "<td>{$lrn}</td>";
            echo "<td>{$name}</td>";
            echo "<td>{$row['username']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>ℹ️ No students linked to users.</p>";
    }
    
    // Check 3: How many users are students?
    echo "<h3>3. Student Users Count:</h3>";
    $student_users = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'student'");
    $student_users_row = $student_users->fetch_assoc();
    echo "<p>Total Student Users: <strong>" . $student_users_row['total'] . "</strong></p>";
    
    echo "<h3>4. Sample Student Users:</h3>";
    $sample_users = $conn->query("
        SELECT id, username, email, first_name, last_name 
        FROM users 
        WHERE role = 'student' 
        LIMIT 5
    ");
    
    if ($sample_users && $sample_users->num_rows > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>User ID</th><th>Username</th><th>Email</th><th>Name</th></tr>";
        while ($row = $sample_users->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['username']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>{$row['first_name']} {$row['last_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check 4: Offer to create sample student
    if ($count_row['total'] == 0 && $student_users_row['total'] > 0) {
        echo "<h3 style='color: blue;'>Solution: Create Student Records</h3>";
        echo "<p>There are student users but no student records. Click button below to create them:</p>";
        echo "<form method='POST'>";
        echo "<input type='hidden' name='action' value='create_student_records'>";
        echo "<button type='submit' style='padding: 10px 20px; background-color: #800000; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;'>Create Student Records Now</button>";
        echo "</form>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Error: " . $e->getMessage() . "</h3>";
}

// Handle student creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create_student_records') {
    echo "<h3>Creating Student Records...</h3>";
    
    $student_users = $conn->query("
        SELECT id, username, email, first_name, last_name 
        FROM users 
        WHERE role = 'student' 
        AND id NOT IN (SELECT user_id FROM students)
    ");
    
    $created = 0;
    $current_year = date('Y');
    $counter = 1;
    
    while ($user = $student_users->fetch_assoc()) {
        $lrn = $current_year . str_pad($counter, 8, '0', STR_PAD_LEFT);
        $student_name = trim($user['first_name'] . ' ' . $user['last_name']);
        $student_id = 'STU-' . $user['id'];
        
        $insert_stmt = $conn->prepare("
            INSERT INTO students (user_id, student_id, lrn, student_name, grade_level, section)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $grade = 10;
        $section = 'A';
        
        $insert_stmt->bind_param("issss", $user['id'], $student_id, $lrn, $student_name, $grade, $section);
        
        if ($insert_stmt->execute()) {
            echo "✓ Created: {$student_name} (LRN: {$lrn})<br>";
            $created++;
        } else {
            echo "✗ Error creating {$student_name}: " . $conn->error . "<br>";
        }
        $insert_stmt->close();
        $counter++;
    }
    
    echo "<h3 style='color: green;'>✓ Created $created student records</h3>";
    echo "<p><a href='populate_student_lrn.php'>Now populate LRN data</a></p>";
}

$conn->close();
?>
