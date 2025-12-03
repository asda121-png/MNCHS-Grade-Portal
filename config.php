<?php
// config.php - FIXED ORDER

// 1. Set session settings BEFORE starting session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 only if using HTTPS

// 2. NOW start the session
session_start();

// 3. Database connection
$conn = new mysqli("localhost", "root", "", "gradeportal");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 4. Prevent session fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// 5. Define constants
define('SCHOOL_NAME', 'MNCHS Grade Portal');
?>