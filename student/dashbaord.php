

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | MNCHS Grade Portal</title>

    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
            background: linear-gradient(135deg, #f5f7fa 0%, #c, #c3cfe2 100%);
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

        .user-info {
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .user-info a:hover {
            color: #ffed4e;
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

        .stat-card.gold i { color: var(--accent); }
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

        .quick-links {
            margin-top: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .link-card {
            background: white;
            padding: 1.8rem;
            border-radius: 14px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            text-decoration: none;
            color: var(--text);
        }

        .link-card:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 25px rgba(128, 0, 0, 0.15);
        }

        .link-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .link-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .link-card p {
            color: var(--text-light);
            font-size: 0.95rem;
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
    </style>
</head>
<body>

    <header class="header">
        <h1><i class="fas fa-graduation-cap"></i> MNCHS Grade Portal</h1>
        <div class="user-info">
            <span><i class="fas fa-user"></i> </span>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <h2><i class="fas fa-tachometer-alt"></i> Menu</h2>
            <ul>
                <li><a href="dashbaord.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="grades.html"><i class="fas fa-clipboard-list"></i> My Grades</a></li>
                <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Class Schedule</a></li>
                <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="welcome-card">
                <h2>Welcome back, </h2>
                <p>You are currently enrolled in <strong>Grade </strong></p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-book-open"></i>
                    <h3>Subjects Enrolled</h3>
                    <div class="value">8</div>
                </div>
                <div class="stat-card gold">
                    <i class="fas fa-trophy"></i>
                    <h3>General Average</h3>
                    <div class="value">92.4</div>
                </div>
                <div class="stat-card blue">
                    <i class="fas fa-tasks"></i>
                    <h3>Pending Requirements</h3>
                    <div class="value">2</div>
                </div>
                <div class="stat-card green">
                    <i class="fas fa-calendar-check"></i>
                    <h3>Attendance Rate</h3>
                    <div class="value">98%</div>
                </div>
            </div>

            <div class="quick-links">
                <a href="grades.php" class="link-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>View My Grades</h3>
                    <p>Check your latest grades and performance</p>
                </a>
                <a href="schedule.php" class="link-card">
                    <i class="fas fa-clock"></i>
                    <h3>Class Schedule</h3>
                    <p>See your daily class timetable</p>
                </a>
                <a href="profile.php" class="link-card">
                    <i class="fas fa-user-edit"></i>
                    <h3>Update Profile</h3>
                    <p>Manage your account and password</p>
                </a>
            </div>
        </main>
    </div>

</body>
</html>