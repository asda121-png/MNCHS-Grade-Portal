<?php require '../database/config.php'; if ($_SESSION['role'] !== 'student') header('Location: ../index.php'); ?>
<h1>Student Dashboard (Coming Soon)</h1>
<a href="../index.php">← Back</a>