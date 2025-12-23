<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../index.php');
    exit();
}
$student_name = $_SESSION['user_name'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Observed Values | MNCHS Grade Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --primary: #800000; --text: #2d3436; --bg: #f5f6fa; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); margin: 0; }
        .header { background: var(--primary); color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .container { display: flex; min-height: calc(100vh - 70px); }
        .sidebar { width: 250px; background: white; padding: 2rem 1rem; }
        .sidebar a { display: flex; align-items: center; gap: 10px; padding: 12px; color: var(--text); text-decoration: none; border-radius: 8px; margin-bottom: 10px; }
        .sidebar a:hover, .sidebar a.active { background: var(--primary); color: white; }
        .main-content { flex: 1; padding: 2rem; }
        .card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; color: var(--primary); font-weight: 600; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <header class="header">
        <h1>MNCHS Student</h1>
        <span><?php echo htmlspecialchars($student_name); ?></span>
    </header>
    <div class="container">
        <aside class="sidebar">
            <a href="studentdashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="studentgrades.php"><i class="fas fa-star"></i> My Grades</a>
            <a href="studentvalues.php" class="active"><i class="fas fa-heart"></i> Observed Values</a>
        </aside>
        <main class="main-content">
            <div class="card">
                <h2 style="color:var(--primary); margin-bottom:1rem;">Report on Learner's Observed Values</h2>
                <p style="color:#666; margin-bottom:1.5rem;">Core Values and Behavioral Statements</p>
                
                <table>
                    <thead>
                        <tr>
                            <th style="width:20%;">Core Values</th>
                            <th style="width:40%;">Behavior Statements</th>
                            <th class="center">Q1</th>
                            <th class="center">Q2</th>
                            <th class="center">Q3</th>
                            <th class="center">Q4</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Placeholder Data - In a real implementation, fetch from DB -->
                        <tr>
                            <td rowspan="2" style="font-weight:600;">1. Maka-Diyos</td>
                            <td>Expresses one's spiritual beliefs while respecting the spiritual beliefs of others.</td>
                            <td class="center">AO</td><td class="center">AO</td><td class="center">-</td><td class="center">-</td>
                        </tr>
                        <tr>
                            <td>Shows adherence to ethical acts.</td>
                            <td class="center">AO</td><td class="center">SO</td><td class="center">-</td><td class="center">-</td>
                        </tr>
                        <tr>
                            <td style="font-weight:600;">2. Makatao</td>
                            <td>Is sensitive to individual, social, and cultural differences.</td>
                            <td class="center">AO</td><td class="center">AO</td><td class="center">-</td><td class="center">-</td>
                        </tr>
                    </tbody>
                </table>
                <div style="margin-top:2rem; font-size:0.9rem; color:#666;">
                    <strong>Legend:</strong> AO - Always Observed, SO - Sometimes Observed, RO - Rarely Observed, NO - Not Observed
                </div>
            </div>
        </main>
    </div>
</body>
</html>