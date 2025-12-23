<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../index.php');
    exit();
}
require_once '../../includes/config.php';

$user_id = $_SESSION['user_id'];
$student_name = $_SESSION['user_name'] ?? 'Student';

// Fetch Student ID
$stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$student_data = $res->fetch_assoc();
$stmt->close();

$student_id = $student_data['id'] ?? 0;

// Fetch Grades
$grades = [];
if ($student_id) {
    $query = "SELECT s.subject_name, 
                     CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                     g.q1, g.q2, g.q3, g.q4
              FROM class_enrollments ce
              JOIN class_subjects cs ON ce.class_id = cs.class_id
              JOIN subjects s ON cs.subject_id = s.id
              JOIN teachers t ON cs.teacher_id = t.id
              JOIN users u ON t.user_id = u.id
              LEFT JOIN grades g ON g.class_subject_id = cs.id AND g.student_id = ce.student_id
              WHERE ce.student_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Grades | MNCHS Grade Portal</title>
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
        .grade-cell { text-align: center; font-weight: 500; }
        .final-grade { font-weight: 700; color: var(--primary); }
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
            <a href="studentgrades.php" class="active"><i class="fas fa-star"></i> My Grades</a>
            <a href="studentvalues.php"><i class="fas fa-heart"></i> Observed Values</a>
        </aside>
        <main class="main-content">
            <div class="card">
                <h2 style="color:var(--primary); margin-bottom:1.5rem;">Academic Grades</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th class="grade-cell">Q1</th>
                            <th class="grade-cell">Q2</th>
                            <th class="grade-cell">Q3</th>
                            <th class="grade-cell">Q4</th>
                            <th class="grade-cell">Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $g): 
                            $final = ($g['q1'] && $g['q2'] && $g['q3'] && $g['q4']) ? round(($g['q1']+$g['q2']+$g['q3']+$g['q4'])/4, 2) : '';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($g['subject_name']); ?></td>
                            <td style="font-size:0.9rem; color:#666;"><?php echo htmlspecialchars($g['teacher_name']); ?></td>
                            <td class="grade-cell"><?php echo $g['q1'] ?? '-'; ?></td>
                            <td class="grade-cell"><?php echo $g['q2'] ?? '-'; ?></td>
                            <td class="grade-cell"><?php echo $g['q3'] ?? '-'; ?></td>
                            <td class="grade-cell"><?php echo $g['q4'] ?? '-'; ?></td>
                            <td class="grade-cell final-grade"><?php echo $final ?: '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>