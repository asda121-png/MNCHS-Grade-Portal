<?php
require_once 'includes/config.php';

// Get the first teacher (teacher_smith) and find a class to assign
$teacherQuery = "SELECT t.id FROM teachers t WHERE t.user_id = (SELECT id FROM users WHERE username = 'teacher_smith' LIMIT 1)";
$teacherResult = $conn->query($teacherQuery);
$teacherRow = $teacherResult->fetch_assoc();
$teacherId = $teacherRow['id'] ?? null;

// Get the first available class
$classQuery = "SELECT id, class_name, grade_level, section FROM classes LIMIT 1";
$classResult = $conn->query($classQuery);
$classRow = $classResult->fetch_assoc();
$classId = $classRow['id'] ?? null;

if ($teacherId && $classId) {
    // Update the teacher to be an adviser
    $updateQuery = "UPDATE teachers SET is_adviser = 1, adviser_class_id = " . $classId . " WHERE id = " . $teacherId;
    
    if ($conn->query($updateQuery)) {
        echo "✓ Successfully set teacher_smith as class adviser for: " . htmlspecialchars($classRow['class_name']);
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Could not find teacher or class to assign";
}

// Show the updated adviser
$adviserQuery = "SELECT u.username, u.email, u.first_name, u.last_name, t.teacher_id, c.class_name 
                 FROM users u 
                 JOIN teachers t ON u.id = t.user_id 
                 LEFT JOIN classes c ON t.adviser_class_id = c.id
                 WHERE u.username = 'teacher_smith'";
                 
$adviserResult = $conn->query($adviserQuery);
$adviser = $adviserResult->fetch_assoc();

echo "<br><br>";
echo "Adviser Account Details:<br>";
echo "Username: " . $adviser['username'] . "<br>";
echo "Name: " . $adviser['first_name'] . " " . $adviser['last_name'] . "<br>";
echo "Email: " . $adviser['email'] . "<br>";
echo "Class: " . $adviser['class_name'] . "<br>";
echo "Password: password<br>";
?>
