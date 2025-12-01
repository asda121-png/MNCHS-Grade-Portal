<?php
// index.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

switch ($_SESSION['role']) {
    case 'admin':
        header("Location: admin/dashboard.php");
        break;
    case 'teacher':
        header("Location: teacher/dashboard.php");
        break;
    case 'student':
        header("Location: student/dashboard.php");
        break;
    case 'parent':
        header("Location: parent/dashboard.php");
        break;
    default:
        header("Location: login.php");
}
exit();
?>