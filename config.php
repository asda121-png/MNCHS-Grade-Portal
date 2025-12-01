<?php
// config.php
session_start();

$conn = new mysqli("localhost", "root", "", "gradeportal");

if ($conn->connect_error) {
    die("Connection failed. Please check your database.");
}

// Secure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Prevent session fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Define school name
define('SCHOOL_NAME', 'Sunshine High School');
?>