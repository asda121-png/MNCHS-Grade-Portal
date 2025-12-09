<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../index.php'); // Redirect to main login page
    exit();
}
$student_name = $_SESSION['student_name'] ?? 'Juan Dela Cruz';
$grade_level  = $_SESSION['grade_level'] ?? '11';
$section      = $_SESSION['section'] ?? 'STEM-A';
$student_id   = $_SESSION['user_id'];
$school_year  = "2025–2026";

// Include database config
require_once '../../includes/config.php';

// Fetch student's grades from database
$grades = [];
$stmt = $conn->prepare("
    SELECT 
        s.id as subject_id,
        s.subject_name,
        g.quarter_1,
        g.quarter_2,
        g.quarter_3,
        g.quarter_4
    FROM grades g
    JOIN subjects s ON g.class_subject_id = s.id
    WHERE g.student_id = ?
    ORDER BY s.id ASC
");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $grades[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades | MNCHS Grade Portal</title>    
    <link rel="icon" href="../../assets/images/logo.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #800000; --primary-dark: #660000; --accent: #FFD700;
            --text: #2d3436; --text-light: #636e72; --shadow: 0 8px 25px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%); color:var(--text); min-height:100vh; }
        .header { background:linear-gradient(90deg,var(--primary),var(--primary-dark)); color:white; padding:1.2rem 2rem; box-shadow:0 4px 15px rgba(128,0,0,0.3); position:sticky; top:0; z-index:1000; display:flex; justify-content:space-between; align-items:center; }
        .header h1 { font-size:1.8rem; font-weight:600; display:flex; align-items:center; gap:12px; }
        .header h1 i { color:var(--accent); font-size:2rem; }
        .user-info { display: flex; align-items: center; gap: 25px; }
        .notification-bell {
            position: relative;
            color: white;
            font-size: 1.3rem;
            text-decoration: none;
            transition: var(--transition);
        }
        .notification-bell:hover { color: var(--accent); }
        .notification-badge {
            position: absolute; top: -5px; right: -8px; background-color: #e74c3c;
            color: white; border-radius: 50%; width: 18px; height: 18px;
            font-size: 0.7rem; font-weight: 700; display: none;
            justify-content: center; align-items: center;
            border: 2px solid var(--primary-dark);
        }
        .profile-link {
            position: relative;
            color: white;
            font-size: 1.6rem;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            cursor: pointer; /* Add cursor style to indicate it's clickable */
        }

        .profile-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: none; /* Hidden by default */
            min-width: 160px;
            z-index: 100;
        }

        .profile-dropdown.show {
            display: block; /* Show when the class 'show' is added */
        }

        .profile-dropdown a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: var(--text);
            transition: background-color 0.3s ease;
            text-align: left;
        }

        .profile-dropdown a:hover {
            background-color: #f5f6fa; /* --bg color */
        }

        .profile-link {
            color: white;
            font-size: 1.6rem;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }
        .profile-link:hover { color: var(--accent); }

        .dropdown-caret {
            font-size: 0.9rem;
            margin-left: 8px;
            transition: var(--transition);
        }

        .container { display:flex; min-height:calc(100vh - 80px); }
        .sidebar { width:260px; background:white; padding:2rem 1.5rem; box-shadow:5px 0 15px rgba(0,0,0,0.05); position:sticky; top:80px; height:calc(100vh - 80px); overflow-y:auto; }
         .sidebar-logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .sidebar-logo {
            max-width: 120px;
            height: auto;
        }

        .sidebar ul li a { display:flex; align-items:center; gap:12px; padding:14px 18px; color:var(--text); text-decoration:none; border-radius:12px; font-weight:500; transition:var(--transition); }
        .sidebar ul { list-style-type: none; }
        .sidebar ul li { margin-bottom: 8px; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background:var(--primary); color:white; transform:translateX(5px); box-shadow:0 5px 15px rgba(128,0,0,0.2); }
        .sidebar ul li a i { width:20px; text-align:center; font-size:1.1rem; }
        .main-content { flex:1; padding:2.5rem; }
        .page-title { color:var(--primary); font-size:2rem; display:flex; align-items:center; gap:12px; margin-bottom:.5rem; }
        .subtitle { color:var(--text-light); font-size:1.1rem; }
        .content-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2rem; }
        .school-year-selector { display:flex; align-items:center; gap:10px; }
        .school-year-selector label { font-weight:500; color:var(--text-light); font-size:0.95rem; }
        .school-year-selector select {
            padding:8px 12px; border:1px solid #ddd; border-radius:8px; font-family:'Poppins',sans-serif;
            font-size:0.95rem; background-color:white; box-shadow:var(--shadow); cursor:pointer;
            -webkit-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23636e72' class='bi bi-chevron-down' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center; padding-right: 30px;
        }
        .grades-summary { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1.8rem; margin-bottom:2.5rem; }
        .summary-card { background:white; padding:1.8rem; border-radius:16px; text-align:center; box-shadow:var(--shadow); border-left:5px solid var(--primary); transition:var(--transition); position:relative; overflow:hidden; }
        .summary-card::before { content:''; position:absolute; top:-50%; right:-50%; width:200px; height:200px; background:rgba(128,0,0,0.03); border-radius:50%; transition:var(--transition); }
        .summary-card:hover { transform:translateY(-8px); box-shadow:0 12px 35px rgba(128,0,0,0.15); }
        .summary-card:hover::before { top:-20%; right:-20%; }
        .summary-card .icon { font-size:2rem; color:var(--primary); margin-bottom:0.8rem; display:block; }
        .summary-card h3 { font-size:0.85rem; color:var(--text-light); margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px; font-weight:600; }
        .summary-card .value { font-size:2.8rem; font-weight:700; color:var(--primary); line-height:1; }
        table { width:100%; border-collapse:collapse; background:white; border-radius:16px; overflow:hidden; box-shadow:var(--shadow); }
        th { background:linear-gradient(90deg,var(--primary),var(--primary-dark)); color:white; padding:1.2rem; text-align:left; }
        td { padding:1.1rem 1.2rem; border-bottom:1px solid #eee; }
        tr:hover { background:#f8f9fa; }
        .grade.excellent { color:#27ae60; font-weight:600; }
        .grade.good      { color:#2980b9; font-weight:600; }
        .grade.fair      { color:#f39c12; font-weight:600; }
        .grade.poor      { color:#c0392b; font-weight:600; }
        .final-grade     { color:var(--primary); font-weight:700; }
        .grade-col       { text-align: center; }
        .remarks { padding:6px 12px; border-radius:30px; background:#e8f5e8; color:#27ae60; font-weight:600; font-size:.9rem; text-transform:uppercase; }
        .remarks.failed { background:#fdeaea; color:#c0392b; }
        @media (max-width:768px) { .container{flex-direction:column;} .sidebar{width:100%;height:auto;position:relative;} }
    </style>
</head>
<body>

<header class="header">
    <h1></i> MNCHS Grade Portal</h1>
    <div class="user-info">
        <a href="#" class="notification-bell">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">3</span>
        </a>
        <div class="profile-link" title="View Profile">
            <i class="fas fa-user-circle"></i><i class="fas fa-caret-down dropdown-caret"></i>
            <div class="profile-dropdown">
                <a href="studentprofile.php">Profile</a>
            </div>
        </div>
        <span><?php echo htmlspecialchars($student_name); ?></span>
    </div>
</header>

<div class="container">
    <aside class="sidebar">
        <div class="sidebar-logo-container">
<img src="../../assets/images/logo.png" alt="MNCHS Logo" class="sidebar-logo">
        </div>
        <ul>
            <li><a href="studentdashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="studentgrades.php" class="active"><i class="fas fa-clipboard-list"></i> My Grades</a></li>
            <li><a href="studentvalues.php"><i class="fas fa-heart"></i> Observed Values</a></li>
            <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="content-header">
            <div>
                <h1 class="page-title">My Grades</h1>
                <p class="subtitle">Grade <?php echo htmlspecialchars($grade_level); ?> - <?php echo htmlspecialchars($section); ?></p>
            </div>
            <div class="school-year-selector">
                <label for="school-year">School Year:</label>
                <select id="school-year" name="school-year">
                    <option value="2025-2026" selected><?php echo $school_year; ?></option>
                    <option value="2024-2025">2024–2025</option>
                </select>
            </div>
        </div>

        <div class="grades-summary">
            <div class="summary-card"><h3>Total Subjects</h3><div class="value"><?php echo count($grades); ?></div></div>
            <div class="summary-card"><h3>Subjects Passed</h3><div class="value"><?php echo count(array_filter($grades, function($g) { $avg = (($g['quarter_1'] ?? 0) + ($g['quarter_2'] ?? 0) + ($g['quarter_3'] ?? 0) + ($g['quarter_4'] ?? 0)) / 4; return $avg >= 75; })); ?></div></div>
            <div class="summary-card"><h3>Subjects Failed</h3><div class="value"><?php echo count(array_filter($grades, function($g) { $avg = (($g['quarter_1'] ?? 0) + ($g['quarter_2'] ?? 0) + ($g['quarter_3'] ?? 0) + ($g['quarter_4'] ?? 0)) / 4; return $avg < 75; })); ?></div></div>
            <div class="summary-card"><h3>General Weighted Average</h3><div class="value"><?php echo count($grades) > 0 ? number_format(array_sum(array_map(function($g) { return (($g['quarter_1'] ?? 0) + ($g['quarter_2'] ?? 0) + ($g['quarter_3'] ?? 0) + ($g['quarter_4'] ?? 0)) / 4; }, $grades)) / count($grades), 1) : '—'; ?></div></div>
            <div class="summary-card"><h3>Academic Status</h3><div class="value"><?php 
                if (count($grades) === 0) echo '—';
                else {
                    $gwa = array_sum(array_map(function($g) { return (($g['quarter_1'] ?? 0) + ($g['quarter_2'] ?? 0) + ($g['quarter_3'] ?? 0) + ($g['quarter_4'] ?? 0)) / 4; }, $grades)) / count($grades);
                    $failed = count(array_filter($grades, function($g) { $avg = (($g['quarter_1'] ?? 0) + ($g['quarter_2'] ?? 0) + ($g['quarter_3'] ?? 0) + ($g['quarter_4'] ?? 0)) / 4; return $avg < 75; }));
                    if ($failed > 0) echo 'Failed';
                    elseif ($gwa >= 90) echo 'With Honors';
                    else echo 'Regular';
                }
            ?></div></div>
        </div>

        <table>
            <thead>
                <tr><th>Subject</th><th class="grade-col">1st Quarter</th><th class="grade-col">2nd Quarter</th><th class="grade-col">3rd Quarter</th><th class="grade-col">4th Quarter</th><th class="grade-col">Final Grade</th><th class="grade-col">Remarks</th></tr>
            </thead>
            <tbody>
                <?php if (count($grades) > 0): ?>
                    <?php foreach ($grades as $grade): 
                        $q1 = $grade['quarter_1'] ?? '—';
                        $q2 = $grade['quarter_2'] ?? '—';
                        $q3 = $grade['quarter_3'] ?? '—';
                        $q4 = $grade['quarter_4'] ?? '—';
                        
                        // Calculate final grade (average of quarters)
                        $finalGrade = '—';
                        $passed = false;
                        if ($q1 !== '—' && $q2 !== '—' && $q3 !== '—' && $q4 !== '—') {
                            $finalGrade = round(($q1 + $q2 + $q3 + $q4) / 4, 0);
                            $passed = $finalGrade >= 75;
                        }
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($grade['subject_name']); ?></strong></td>
                        <td class="grade-col"><?php echo $q1; ?></td>
                        <td class="grade-col"><?php echo $q2; ?></td>
                        <td class="grade-col"><?php echo $q3; ?></td>
                        <td class="grade-col"><?php echo $q4; ?></td>
                        <td class="final-grade grade-col"><?php echo $finalGrade; ?></td>
                        <td class="grade-col"><span class="remarks<?php echo !$passed && $finalGrade !== '—' ? ' failed' : ''; ?>"><?php echo $finalGrade === '—' ? '—' : ($passed ? 'passed' : 'failed'); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align: center; color: #636e72; padding: 2rem;">No grades available yet. Check back later!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>

<!-- Container for the logout modal -->
<div id="logout-modal-container"></div>

<!-- Notification System -->
<script src="../../assets/js/NotificationManager.js"></script>

<!-- Link to the shared JavaScript file -->
<script src="../../assets/js/student_shared.js"></script>
</body>
</html>