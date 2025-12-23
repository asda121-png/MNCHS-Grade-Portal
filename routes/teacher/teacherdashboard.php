<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../index.php');
    exit();
}
require_once '../../includes/config.php';

$user_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['user_name'] ?? (isset($_SESSION['first_name']) && isset($_SESSION['last_name']) ? trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : 'Teacher');

// Fetch Teacher Details
$stmt = $conn->prepare("SELECT id, is_adviser, adviser_class_id FROM teachers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

$teacher_id = $teacher['id'] ?? 0;
$is_adviser = $teacher['is_adviser'] ?? 0;
$adviser_class_id = $teacher['adviser_class_id'] ?? 0;

// Fetch Advisory Class Info
$advisory_class = null;
if ($is_adviser && $adviser_class_id) {
    $stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
    $stmt->bind_param("i", $adviser_class_id);
    $stmt->execute();
    $advisory_class = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fetch Subject Loads (Classes taught)
$subject_loads = [];
$query = "SELECT cs.id as class_subject_id, c.class_name, c.section, s.subject_name, c.grade_level 
          FROM class_subjects cs 
          JOIN classes c ON cs.class_id = c.id 
          JOIN subjects s ON cs.subject_id = s.id 
          WHERE cs.teacher_id = ?";
$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $subject_loads[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard | MNCHS Grade Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="../../assets/images/logo.ico" type="image/x-icon">
    <style>
        :root {
            --primary: #800000; --primary-dark: #660000; --accent: #FFD700;
            --text: #2d3436; --text-light: #636e72; --shadow: 0 8px 25px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%); color:var(--text); min-height:100vh; }
        
        /* Header */
        .header { background:linear-gradient(90deg,var(--primary),var(--primary-dark)); color:white; padding:1.2rem 2rem; box-shadow:0 4px 15px rgba(128,0,0,0.3); position:sticky; top:0; z-index:1000; display:flex; justify-content:space-between; align-items:center; }
        .header h1 { font-size:1.8rem; font-weight:600; display:flex; align-items:center; gap:12px; }
        .header h1 i { color:var(--accent); font-size:2rem; }
        .user-info { font-size:1rem; display:flex; align-items:center; gap:25px; }
        .menu-icon { display: none; color: white; font-size: 1.5rem; text-decoration: none; margin-right: 1rem; }

        .profile-link { position: relative; color: white; font-size: 1.6rem; text-decoration: none; transition: var(--transition); display: flex; align-items: center; cursor: pointer; }
        .profile-link:hover { color: var(--accent); }
        .profile-dropdown { position: absolute; top: 100%; right: 0; background-color: white; border: 1px solid rgba(0, 0, 0, 0.15); border-radius: 4px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); display: none; min-width: 160px; z-index: 100; }
        .profile-dropdown.show { display: block; }
        .profile-dropdown a { display: block; padding: 10px 15px; text-decoration: none; color: var(--text); transition: background-color 0.3s ease; text-align: left; }
        .profile-dropdown a:hover { background-color: #f5f6fa; }
        .dropdown-caret { font-size: 0.9rem; margin-left: 8px; transition: var(--transition); }

        .notification-bell { position:relative; color:white; font-size:1.3rem; text-decoration:none; transition:var(--transition); }
        .notification-bell:hover { color:var(--accent); }
        .notification-badge { position:absolute; top:-5px; right:-8px; background-color:#e74c3c; color:white; border-radius:50%; width:18px; height:18px; font-size:0.7rem; font-weight:700; display:none; justify-content:center; align-items:center; border:2px solid var(--primary-dark); }

        /* Layout */
        .container { display:flex; min-height:calc(100vh - 80px); }
        .sidebar { width:260px; background:white; padding:2rem 1.5rem; box-shadow:5px 0 15px rgba(0,0,0,0.05); position:sticky; top:80px; height:calc(100vh - 80px); overflow-y:auto; }
        .sidebar-logo-container { text-align: center; margin-bottom: 2rem; }
        .sidebar-logo { max-width: 120px; height: auto; }
        .sidebar ul { list-style:none; }
        .sidebar ul li { margin-bottom:8px; }
        .sidebar ul li a { display:flex; align-items:center; gap:12px; padding:14px 18px; color:var(--text); text-decoration:none; border-radius:12px; font-weight:500; transition:var(--transition); }
        .sidebar ul li a:hover, .sidebar ul li a.active { background:var(--primary); color:white; transform:translateX(5px); box-shadow:0 5px 15px rgba(128,0,0,0.2); }
        .sidebar ul li a i { font-size:1.1rem; width:20px; text-align:center; }
        
        .main-content { flex:1; padding:2.5rem; background:transparent; }

        /* Dashboard Specific Styles */
        .welcome-section { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s; border-left: 5px solid var(--primary); }
        .card:hover { transform: translateY(-5px); }
        .card h3 { margin: 0 0 0.5rem 0; color: var(--primary); }
        .card p { margin: 0; color: #666; font-size: 0.9rem; }
        .btn { display: inline-block; padding: 8px 16px; background: var(--primary); color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem; margin-top: 1rem; }
        .btn:hover { background: #600000; }
        .section-title { margin-bottom: 1rem; font-weight: 600; color: #444; display: flex; align-items: center; gap: 10px; }
        .badge { background: #e0e0e0; color: #333; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; }

        /* Responsive */
        @media (max-width: 768px) {
            .container { flex-direction:column; }
            .sidebar { width:100%; height:auto; position:relative; top:0; padding:1.5rem; }
            .main-content { padding:1.5rem; }
            .header h1 { font-size:1.5rem; }
            .menu-icon { display: block; }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="#" class="menu-icon"><i class="fas fa-bars"></i></a>
        <h1></i> MNCHS Grade Portal</h1>
        <div class="user-info">
            <a href="#" class="notification-bell">
                <i class="fas fa-bell"></i>
                <span class="notification-badge"></span>
            </a>
            <div class="profile-link" title="View Profile">
                <i class="fas fa-user-circle"></i><i class="fas fa-caret-down dropdown-caret"></i>
                <div class="profile-dropdown">
                    <a href="teacherprofile.php">Profile</a>
                </div>
            </div>
            <span><?php echo htmlspecialchars($teacher_name); ?></span>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-logo-container"><img src="../../assets/images/logo.png" alt="MNCHS Logo" class="sidebar-logo"></div>
            <ul>
                <li><a href="teacherdashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="teacherstudent.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="teachermyclasses.php"><i class="fas fa-users"></i> My Classes</a></li>
                <li><a href="teachergradeentry.php"><i class="fas fa-edit"></i> Grade Entry</a></li>
                <li><a href="teachervaluesentry.php"><i class="fas fa-tasks"></i> Values Entry</a></li>
                <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
        <div class="welcome-section">
            <div>
                <h2>Welcome back, <?php echo htmlspecialchars($teacher_name); ?>!</h2>
                <p>Manage your classes and student grades.</p>
            </div>
            <?php if ($is_adviser): ?>
                <div style="text-align:right;">
                    <span style="background:#d4edda; color:#155724; padding:5px 10px; border-radius:20px; font-size:0.9rem; font-weight:600;">
                        <i class="fas fa-check-circle"></i> Class Adviser
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Advisory Section -->
        <?php if ($is_adviser && $advisory_class): ?>
            <h3 class="section-title"><i class="fas fa-user-tie"></i> Advisory Class</h3>
            <div class="card" style="border-left-color: #27ae60; margin-bottom: 2rem;">
                <h3><?php echo htmlspecialchars($advisory_class['class_name']); ?></h3>
                <p>Grade <?php echo htmlspecialchars($advisory_class['grade_level']); ?> - Section <?php echo htmlspecialchars($advisory_class['section']); ?></p>
                <div style="margin-top:10px;">
                    <a href="teacher_advisory.php" class="btn" style="background:#27ae60;">Manage Advisory Class</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Subject Loads Section -->
        <h3 class="section-title"><i class="fas fa-book"></i> Subject Loads (My Classes)</h3>
        <div class="grid">
            <?php if (count($subject_loads) > 0): ?>
                <?php foreach ($subject_loads as $subject): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                        <p><strong>Class:</strong> <?php echo htmlspecialchars($subject['class_name']); ?></p>
                        <p><strong>Section:</strong> <?php echo htmlspecialchars($subject['section']); ?></p>
                        <a href="teacher_grading.php?class_subject_id=<?php echo $subject['class_subject_id']; ?>" class="btn">Encode Grades</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:#666;">No subject loads assigned yet.</p>
            <?php endif; ?>
        </div>
        </main>
    </div>

    <div id="logout-modal-container"></div>
    <script src="../../assets/js/NotificationManager.js"></script>
    <script src="../../assets/js/teacher_shared.js"></script>
</body>
</html>