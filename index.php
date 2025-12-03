<?php
// index.php
require_once '../database/config.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= ucfirst($role) ?> Portal - <?= SCHOOL_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .role-card { min-height: 200px; display: flex; align-items: center; justify-content: center; }
    .icon { font-size: 3rem; }
  </style>
</head>
<body class="bg-light">
  <nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container-fluid">
      <span class="navbar-brand mb-0 h1"><?= SCHOOL_NAME ?> Grade Portal</span>
      <div class="d-flex align-items-center text-white">
        <span class="me-3">Welcome, <strong><?= htmlspecialchars($full_name) ?></strong></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container">
    <div class="row g-4">
      <?php if ($role === 'admin'): ?>
        <div class="col-md-4">
          <div class="card role-card text-center bg-info text-white">
            <div class="card-body">
              <div class="icon mb-3">Admin</div>
              <h5>Admin Dashboard</h5>
              <a href="admin/dashboard.php" class="btn btn-light mt-2">Go →</a>
            </div>
          </div>
        </div>

      <?php elseif ($role === 'teacher'): ?>
        <div class="col-md-4">
          <div class="card role-card text-center bg-success text-white">
            <div class="card-body">
              <div class="icon mb-3">Teacher</div>
              <h5>Teacher Portal</h5>
              <a href="teacher/dashboard.php" class="btn btn-light mt-2">Go →</a>
            </div>
          </div>
        </div>

      <?php elseif ($role === 'student'): ?>
        <div class="col-md-4">
          <div class="card role-card text-center bg-warning text-dark">
            <div class="card-body">
              <div class="icon mb-3">Student</div>
              <h5>My Grades & Attendance</h5>
              <a href="student/dashboard.php" class="btn btn-dark mt-2">Go →</a>
            </div>
          </div>
        </div>

      <?php elseif ($role === 'parent'): ?>
        <div class="col-md-4">
          <div class="card role-card text-center bg-secondary text-white">
            <div class="card-body">
              <div class="icon mb-3">Parent</div>
              <h5>Child's Progress</h5>
              <a href="parent/dashboard.php" class="btn btn-light mt-2">Go →</a>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>