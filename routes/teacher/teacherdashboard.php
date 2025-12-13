<?php
session_start();
// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    // In a real scenario, you would redirect to a login page
    // header('Location: ../login.php');
    // exit();
}

// Get teacher name from session
$teacher_name = $_SESSION['user_name'] ?? (isset($_SESSION['first_name']) && isset($_SESSION['last_name']) ? trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : 'Teacher');

// --- Securely load API Key from .env file ---
$google_api_key = '';
$dotenv_path = __DIR__ . '/../../.env';

if (file_exists($dotenv_path)) {
    $lines = file($dotenv_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        list($name, $value) = explode('=', $line, 2);
        if (trim($name) === 'GOOGLE_CALENDAR_API_KEY') {
            $value = trim($value);
            // Remove quotes if they exist
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
    <title>Teacher Dashboard | MNCHS Grade Portal</title>

    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link rel="icon" href="../../assets/images/logo.ico" type="image/x-icon">

    <style>
        :root {
            --primary: #800000;      /* Maroon */
            --primary-dark: #660000;
            --accent: #FFD700;       /* Gold */
            --text: #2d3436;
            --text-light: #636e72;
            --bg: #f5f6fa;
            --white: #ffffff;
            --shadow: 0 8px 25px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 1.2rem 2rem;
            box-shadow: 0 4px 15px rgba(128, 0, 0, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header h1 i {
            color: var(--accent);
            font-size: 2rem;
        }

        .menu-icon { display: none; color: white; font-size: 1.5rem; text-decoration: none; margin-right: 1rem; }

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
            background-color: var(--white);
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
            background-color: var(--bg);
        }
        .profile-link:hover { color: var(--accent); }
        .dropdown-caret { font-size: 0.9rem; margin-left: 8px; transition: var(--transition); }

        .user-menu { position: relative; cursor: pointer; }
        .user-menu-content { display: none; position: absolute; right: 0; background-color: white; min-width: 160px; box-shadow: var(--shadow); z-index: 1; border-radius: 8px; overflow: hidden; }
        .user-menu-content a { color: var(--text); padding: 12px 16px; text-decoration: none; display: flex; align-items: center; gap: 10px; font-size: 0.95rem; }
        .user-menu-content a:hover { background-color: #f1f1f1; }
        .user-menu-content a i { color: var(--primary); }
        .user-menu:hover .user-menu-content { display: block; }
        .user-name { display: flex; align-items: center; gap: 10px; }
        .user-name i {
            background-color: var(--accent); color: var(--primary);
            border-radius: 50%; width: 36px; height: 36px;
            display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }
        .user-info {
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .notification-bell {
            position: relative;
            color: white;
            font-size: 1.3rem;
            text-decoration: none;
            transition: var(--transition);
        }
        .notification-bell:hover {
            color: var(--accent);
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            font-weight: 700;
            display: none;
            justify-content: center;
            align-items: center;
            border: 2px solid var(--primary-dark);
        }

        .container {
            display: flex;
            min-height: calc(100vh - 80px);
        }

        .sidebar {
            width: 260px;
            background: white;
            padding: 2rem 1.5rem;
            box-shadow: 5px 0 15px rgba(0,0,0,0.05);
            position: sticky;
            top: 80px;
            height: calc(100vh - 80px);
            overflow-y: auto;
        }
        .sidebar-logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .sidebar-logo { max-width: 120px; height: auto; }

        .sidebar h2 {
            color: var(--primary);
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin-bottom: 8px;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            color: var(--text);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            transition: var(--transition);
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: var(--primary);
            color: white;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(128, 0, 0, 0.2);
        }

        .sidebar ul li a i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            padding: 2.5rem;
            background: transparent;
        }

        .welcome-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .welcome-card h2 {
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }

        .welcome-card p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.8rem;
            margin-top: 1rem;
        }

        .stat-card {
            background: white;
            padding: 1.8rem;
            border-radius: 16px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            border: 1px solid #eee;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(128, 0, 0, 0.12);
        }

        .stat-card i {
            font-size: 2.8rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .stat-card.blue i { color: #3498db; }
        .stat-card.green i { color: #27ae60; }

        .stat-card h3 {
            font-size: 1.1rem;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .container { flex-direction: column; }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 1.5rem;
            }
            .main-content { padding: 1.5rem; }
            .header h1 { font-size: 1.5rem; }
        }
        @media (max-width:768px) { .menu-icon { display: block; } }
    </style>
    <style>
        /* Calendar Styles */
        #calendar-container {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: var(--shadow);
            margin-top: 2rem;
        }
        .fc .fc-button-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .fc .fc-button-primary:hover { background-color: var(--primary-dark); border-color: var(--primary-dark); }
        .fc .fc-daygrid-day.fc-day-today { background-color: rgba(255, 215, 0, 0.2); }
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
        <aside class="sidebar"><div class="sidebar-logo-container"><img src="../../assets/images/logo.png" alt="MNCHS Logo" class="sidebar-logo"></div>
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
            <div class="welcome-card">
                <h2>Welcome to your dashboard, <?php echo htmlspecialchars(explode(' ', $teacher_name)[0]); ?>!</h2>
                <p>Here's a summary of your activities for the School Year 2025-2026.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-chalkboard"></i>
                    <h3>Classes Handled</h3>
                    <div class="value">4</div>
                </div>
                <div class="stat-card blue">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Total Students</h3>
                    <div class="value">162</div>
                </div>
            </div>

            <!-- Calendar Section -->
            <div id="calendar-container">
                <div id="calendar"></div>
            </div>
        </main>
    </div>

    <!-- Container for the logout modal -->
    <div id="logout-modal-container"></div>

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/google-calendar@5.11.3/main.global.min.js'></script>

<!-- Pass PHP variables to JavaScript -->
<script>
    window.GOOGLE_API_KEY = '<?php echo htmlspecialchars($google_api_key); ?>';
</script>

<!-- Notification System -->
<script src="../../assets/js/NotificationManager.js"></script>

<!-- Link to the external JavaScript file -->
<script src="../../assets/js/teacherdashboard.js"></script>
</body>
</html>
