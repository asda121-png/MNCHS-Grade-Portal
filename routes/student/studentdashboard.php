<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../index.php');
    exit();
}
require_once '../../includes/config.php';

$user_id = $_SESSION['user_id'];
$student_name = $_SESSION['user_name'] ?? 'Student';

// Fetch Student Details
$stmt = $conn->prepare("SELECT s.id, s.lrn, s.grade_level, s.section, c.class_name 
                        FROM students s 
                        LEFT JOIN class_enrollments ce ON s.id = ce.student_id 
                        LEFT JOIN classes c ON ce.class_id = c.id 
                        WHERE s.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

$grade_section = ($student['grade_level'] ?? '') . ' - ' . ($student['section'] ?? '');

// --- Securely load API Key from .env file for Calendar ---
$google_api_key = '';
$dotenv_path = __DIR__ . '/../../.env';
if (file_exists($dotenv_path)) {
    $lines = file($dotenv_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        if (trim($name) === 'GOOGLE_CALENDAR_API_KEY') {
            $value = trim($value);
            if (substr($value, 0, 1) == '"' && substr($value, -1) == '"') {
                $google_api_key = substr($value, 1, -1);
            } else { $google_api_key = $value; }
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | MNCHS Grade Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link rel="icon" href="../../assets/images/logo.ico" type="image/x-icon">
    <style>
        :root { --primary: #800000; --text: #2d3436; --bg: #f5f6fa; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); margin: 0; }
        .header { background: var(--primary); color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .container { display: flex; min-height: calc(100vh - 70px); }
        .sidebar { width: 250px; background: white; padding: 2rem 1rem; box-shadow: 2px 0 5px rgba(0,0,0,0.05); }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar li { margin-bottom: 10px; }
        .sidebar a { display: flex; align-items: center; gap: 10px; padding: 12px; color: var(--text); text-decoration: none; border-radius: 8px; transition: 0.3s; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background: var(--primary); color: white; }
        .main-content { flex: 1; padding: 2rem; }
        .welcome-card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
        #calendar-container { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .student-info-badge { background: #f0f0f0; padding: 5px 12px; border-radius: 20px; font-size: 0.9rem; color: #555; font-weight: 500; }
    </style>
</head>
<body>
    <header class="header">
        <div style="display:flex; align-items:center; gap:10px;">
            <img src="../../assets/images/logo.png" alt="Logo" style="height:40px;">
            <h1 style="font-size:1.2rem; margin:0;">MNCHS Student</h1>
        </div>
        <div style="display:flex; align-items:center; gap:15px;">
            <span><?php echo htmlspecialchars($student_name); ?></span>
            <a href="#" id="logout-link" style="color:white;"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="studentdashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="studentgrades.php"><i class="fas fa-star"></i> My Grades</a></li>
                <li><a href="studentvalues.php"><i class="fas fa-heart"></i> Observed Values</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <div class="welcome-card">
                <div>
                    <h2>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>
                    <p style="color:#666;">Here is your school schedule and events.</p>
                </div>
                <div style="text-align:right;">
                    <div class="student-info-badge"><i class="fas fa-id-card"></i> LRN: <?php echo htmlspecialchars($student['lrn'] ?? 'N/A'); ?></div>
                    <div class="student-info-badge" style="margin-top:5px;"><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($grade_section); ?></div>
                </div>
            </div>

            <div id="calendar-container">
                <h3 style="margin-bottom:1.5rem; color:var(--primary);">School Calendar</h3>
                <div id="calendar"></div>
            </div>
        </main>
    </div>

    <div id="logout-modal-container"></div>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/google-calendar@5.11.3/main.global.min.js'></script>
    <script>window.GOOGLE_API_KEY = '<?php echo htmlspecialchars($google_api_key); ?>';</script>
    <script src="../../assets/js/NotificationManager.js"></script>
    <script src="../../assets/js/studentdashboard.js"></script>
    <script>
        // Logout Modal Logic
        const logoutLink = document.getElementById("logout-link");
        const modalContainer = document.getElementById("logout-modal-container");
        if (logoutLink && modalContainer) {
            fetch("../../components/logout_modal.html")
            .then(r => r.text())
            .then(html => {
                modalContainer.innerHTML = html;
                const modal = document.getElementById("logout-modal");
                logoutLink.onclick = (e) => { e.preventDefault(); modal.classList.add("show"); };
                document.getElementById("cancel-logout").onclick = () => modal.classList.remove("show");
                document.getElementById("confirm-logout").onclick = () => window.location.href = '../../logout.php';
            });
        }
    </script>
</body>
</html>