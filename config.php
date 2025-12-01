<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gradeportal");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Real-time polling interval (ms)
define('POLL_INTERVAL', 5000);
?>