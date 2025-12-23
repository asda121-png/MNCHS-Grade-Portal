<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}
$admin_name = $_SESSION['user_name'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | MNCHS Grade Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="../../assets/images/logo.ico" type="image/x-icon">
    <style>
        :root {
            --primary: #800000; --primary-dark: #660000; --text: #2d3436; --bg: #f5f6fa;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); color: var(--text); min-height: 100vh; }
        .header { background: linear-gradient(90deg, var(--primary), var(--primary-dark)); color: white; padding: 1.2rem 2rem; box-shadow: 0 4px 15px rgba(128, 0, 0, 0.3); position: sticky; top: 0; z-index: 1000; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.8rem; font-weight: 600; display: flex; align-items: center; gap: 12px; }
        .user-info { font-size: 1rem; display: flex; align-items: center; gap: 25px; }
        .container { display: flex; min-height: calc(100vh - 80px); }
        .sidebar { width: 260px; background: white; padding: 2rem 1.5rem; box-shadow: 5px 0 15px rgba(0,0,0,0.05); position: sticky; top: 80px; height: calc(100vh - 80px); overflow-y: auto; }
        .sidebar-logo-container { text-align: center; margin-bottom: 2rem; }
        .sidebar-logo { max-width: 120px; height: auto; }
        .sidebar ul { list-style: none; }
        .sidebar ul li { margin-bottom: 8px; }
        .sidebar ul li a { display: flex; align-items: center; gap: 12px; padding: 14px 18px; color: var(--text); text-decoration: none; border-radius: 12px; font-weight: 500; transition: all 0.3s ease; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background: var(--primary); color: white; transform: translateX(5px); box-shadow: 0 5px 15px rgba(128, 0, 0, 0.2); }
        .main-content { flex: 1; padding: 2.5rem; }
        .report-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .report-card { background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); text-align: center; transition: transform 0.3s ease; }
        .report-card:hover { transform: translateY(-5px); }
        .report-card i { font-size: 3rem; color: var(--primary); margin-bottom: 1rem; }
        .report-card h3 { margin-bottom: 1rem; color: var(--text); }
        .btn-download { background: var(--primary); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600; transition: background 0.3s; }
        .btn-download:hover { background: var(--primary-dark); }
    </style>
</head>
<body>
    <header class="header">
        <h1><i class="fas fa-shield-alt"></i> MNCHS Admin</h1>
        <div class="user-info"><span><?php echo htmlspecialchars($admin_name); ?></span></div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-logo-container"><img src="../../assets/images/logo.png" alt="MNCHS Logo" class="sidebar-logo"></div>
            <ul>
                <li><a href="admindashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="adminteachers.php"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a></li>
                <li><a href="adminreports.php" class="active"><i class="fas fa-file-alt"></i> Reports</a></li>
                <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <h2 style="margin-bottom: 2rem; color: var(--primary);">System Reports</h2>
            <div class="report-grid">
                <div class="report-card">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Student Grade Reports</h3>
                    <p style="margin-bottom: 1.5rem; color: #666;">Download complete grade sheets for all students.</p>
                    <a href="#" class="btn-download"><i class="fas fa-download"></i> Download PDF</a>
                </div>
                <div class="report-card">
                    <i class="fas fa-chalkboard"></i>
                    <h3>Class Summaries</h3>
                    <p style="margin-bottom: 1.5rem; color: #666;">View and download summary reports per section.</p>
                    <a href="#" class="btn-download"><i class="fas fa-download"></i> Download Excel</a>
                </div>
                <div class="report-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Teacher Performance</h3>
                    <p style="margin-bottom: 1.5rem; color: #666;">Analytics on grade submission and class performance.</p>
                    <a href="#" class="btn-download"><i class="fas fa-download"></i> Download Report</a>
                </div>
            </div>
        </main>
    </div>
    <div id="logout-modal-container"></div>
    <script>
        // Simple logout modal loader
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
            });
        }
    </script>
</body>
</html>