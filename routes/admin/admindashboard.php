<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../index.php'); // Redirect to login page
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | MNCHS Grade Portal</title>
    <link rel="icon" href="../assets/images/logo.ico" type="image/x-icon">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text);
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            color: white; padding: 1.2rem 2rem;
            box-shadow: 0 4px 15px rgba(128, 0, 0, 0.3);
            position: sticky; top: 0; z-index: 1000;
            display: flex; justify-content: space-between; align-items: center;
        }
        .header h1 { font-size: 1.8rem; font-weight: 600; }
        .user-info { display: flex; align-items: center; gap: 25px; }
        .notification-bell { position: relative; color: white; font-size: 1.3rem; text-decoration: none; }
        .notification-badge { position: absolute; top: -5px; right: -8px; background-color: #e74c3c; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; font-weight: 700; display: flex; justify-content: center; align-items: center; border: 2px solid var(--primary-dark); }
        .container { display: flex; min-height: calc(100vh - 77px); }
        .sidebar {
            width: 260px; background: white; padding: 2rem 1.5rem;
            box-shadow: 5px 0 15px rgba(0,0,0,0.05);
            position: sticky; top: 77px; /* Height of header */
            height: calc(100vh - 77px);
            overflow-y: auto;
        }
        .sidebar-logo-container { text-align: center; margin-bottom: 2rem; }
        .sidebar-logo { max-width: 120px; height: auto; }
        .sidebar ul { list-style: none; }
        .sidebar ul li { margin-bottom: 8px; }
        .sidebar ul li a {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 18px; color: var(--text); text-decoration: none;
            border-radius: 12px; font-weight: 500; transition: var(--transition);
        }
        .sidebar ul li a:hover, .sidebar ul li a.active {
            background: var(--primary); color: white;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(128, 0, 0, 0.2);
        }
        .sidebar ul li a i { font-size: 1.1rem; width: 20px; text-align: center; }
        .main-content { flex: 1; padding: 2.5rem; background: transparent; }
        .welcome-card { background: white; padding: 2rem; border-radius: 16px; box-shadow: var(--shadow); margin-bottom: 2rem; }
        .welcome-card h2 { color: var(--primary); margin-bottom: 0.5rem; font-size: 1.8rem; }
        .welcome-card p { color: var(--text-light); font-size: 1.1rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.8rem; }
        .stat-card {
            background: white; padding: 1.8rem; border-radius: 16px;
            box-shadow: var(--shadow); display: flex; align-items: center; gap: 1.5rem;
            transition: var(--transition); border: 1px solid #eee;
        }
        .stat-card:hover { transform: translateY(-8px); box-shadow: 0 12px 25px rgba(128, 0, 0, 0.1); }
        .stat-card .icon-container {
            padding: 1rem; border-radius: 50%; font-size: 1.8rem;
            display: flex; align-items: center; justify-content: center;
        }
        .stat-card .icon-container.blue { background-color: #eaf5ff; color: #3498db; }
        .stat-card .icon-container.green { background-color: #e8f8f0; color: #27ae60; }
        .stat-card .icon-container.yellow { background-color: #fff8e1; color: #f1c40f; }
        .stat-card .icon-container.red { background-color: #feeaed; color: #e74c3c; }
        .stat-card .value { font-size: 2rem; font-weight: 700; color: var(--text); }
        .stat-card .label { color: var(--text-light); }
        .recent-users-table { margin-top: 2.5rem; background: white; border-radius: 16px; box-shadow: var(--shadow); overflow: hidden; }
        .recent-users-table h2 { padding: 1.5rem; font-size: 1.3rem; border-bottom: 1px solid #eee; }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem 1.5rem; text-align: left; border-bottom: 1px solid #f0f0f0; }
        thead th { background-color: #f9fafb; color: var(--text-light); text-transform: uppercase; font-size: 0.8rem; }
        tbody tr:hover { background-color: #f5f6fa; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-weight: 600; font-size: 0.8rem; }
        /* Calendar Styles */
        #calendar-container {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: var(--shadow);
            margin-top: 2.5rem;
        }
        .fc .fc-button-primary { background-color: var(--primary); border-color: var(--primary); }
        .fc .fc-button-primary:hover { background-color: var(--primary-dark); border-color: var(--primary-dark); }
        .fc .fc-daygrid-day.fc-day-today { background-color: rgba(255, 215, 0, 0.2); }
        .fc-event { cursor: pointer; }
        .status-badge.active { background-color: #e8f8f0; color: #27ae60; }
        .status-badge.pending { background-color: #fff8e1; color: #f39c12; }
        .action-link { color: var(--primary); text-decoration: none; font-weight: 600; }
        .action-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <!-- Reusable Header -->
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo-container"><img src="../assets/images/logo.png" alt="MNCHS Logo" class="sidebar-logo"></div>
            <ul>
                <li><a href="admindashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="adminstudents.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="adminteachers.php"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                <li><a href="adminreports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="welcome-card">
                <h2>Admin Dashboard</h2>
                <p>Welcome back! Here's an overview of the portal's status.</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon-container blue"><i class="fas fa-user-graduate"></i></div>
                    <div>
                        <div class="value">1,250</div>
                        <div class="label">Total Students</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="icon-container green"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div>
                        <div class="value">85</div>
                        <div class="label">Total Teachers</div>
                    </div>
                </div>
            </div>

            <!-- Calendar Section -->
            <div id="calendar-container">
                <div id="calendar"></div>
            </div>

            <!-- Recent Users Table -->
            <div class="recent-users-table">
                <h2>Recent Portal Activity</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John Doe</td>
                                <td>john.doe@example.com</td>
                                <td>Student</td>
                                <td><span class="status-badge active">Active</span></td>
                                <td><a href="#" class="action-link">Edit</a></td>
                            </tr>
                            <tr>
                                <td>Jane Smith</td>
                                <td>jane.smith@example.com</td>
                                <td>Teacher</td>
                                <td><span class="status-badge active">Active</span></td>
                                <td><a href="#" class="action-link">Edit</a></td>
                            </tr>
                            <tr>
                                <td>Peter Jones</td>
                                <td>peter.jones@example.com</td>
                                <td>Parent</td>
                                <td><span class="status-badge pending">Pending</span></td>
                                <td><a href="#" class="action-link">Approve</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Container for the logout modal -->
    <div id="logout-modal-container"></div>

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/google-calendar@5.11.3/main.global.min.js'></script>

    <!-- Link to the external JavaScript file -->
    <script src="../../assets/js/admindashboard.js"></script>
</body>
</html>