<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../index.php'); 
    exit();
}
// Get teacher name from session
$teacher_name = $_SESSION['user_name'] ?? (isset($_SESSION['first_name']) && isset($_SESSION['last_name']) ? trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : 'Teacher');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Values Entry | MNCHS Grade Portal</title>
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
        .header { background:linear-gradient(90deg,var(--primary),var(--primary-dark)); color:white; padding:1.2rem 2rem; box-shadow:0 4px 15px rgba(128,0,0,0.3); position:sticky; top:0; z-index:1000; display:flex; justify-content:space-between; align-items:center; }
        .header h1 { font-size:1.8rem; font-weight:600; }
        .user-info { font-size:1rem; display:flex; align-items:center; gap:25px; }
        .profile-link { position: relative; color: white; font-size: 1.6rem; text-decoration: none; display: flex; align-items: center; cursor: pointer; }
        .profile-dropdown { position: absolute; top: 100%; right: 0; background-color: white; border: 1px solid rgba(0,0,0,0.15); border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: none; min-width: 160px; z-index: 100; }
        .profile-dropdown.show { display: block; }
        .profile-dropdown a { display: block; padding: 10px 15px; text-decoration: none; color: var(--text); transition: background-color 0.3s ease; }
        .profile-dropdown a:hover { background-color: #f5f6fa; }
        .dropdown-caret { font-size: 0.9rem; margin-left: 8px; }
        .notification-bell { position:relative; color:white; font-size:1.3rem; text-decoration:none; }
        .notification-badge { position:absolute; top:-5px; right:-8px; background-color:#e74c3c; color:white; border-radius:50%; width:18px; height:18px; font-size:0.7rem; font-weight:700; display:none; justify-content:center; align-items:center; border:2px solid var(--primary-dark); }
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
        .page-title { color:var(--primary); font-size:2rem; display:flex; align-items:center; gap:12px; margin-bottom:2rem; }
        .filter-section { background:white; padding:1.5rem 2rem; border-radius:16px; box-shadow:var(--shadow); margin-bottom:2rem; display:flex; gap:1.5rem; align-items:center; flex-wrap:wrap; }
        .filter-group { display:flex; flex-direction:column; }
        .filter-group label { font-weight:600; margin-bottom:8px; color:var(--text-light); font-size:0.9rem; }
        .filter-group select { padding:10px 15px; border:1px solid #ddd; border-radius:8px; font-family:'Poppins',sans-serif; font-size:1rem; background-color:#f9fafb; min-width:200px; }
        .btn { background:var(--primary); color:white; padding:10px 20px; border:none; border-radius:8px; font-weight:600; cursor:pointer; transition:var(--transition); }
        .btn:hover { background:var(--primary-dark); }
        
        /* Table Styles */
        .student-selection-container {
            background: white; padding: 2rem; border-radius: 16px; box-shadow: var(--shadow); margin-bottom: 2rem;
        }
        .student-list { list-style: none; }
        .student-list li {
            padding: 15px 20px; border-bottom: 1px solid #f0f0f0;
            font-weight: 500; cursor: pointer; transition: var(--transition);
            border-radius: 8px;
        }
        .student-list li:last-child { border-bottom: none; }
        .student-list li:hover { background: var(--primary); color: white; transform: translateX(5px); }
        .student-list li i { margin-right: 10px; }
        .search-group { position: relative; }
        .search-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light); }
        #student-search-input {
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            background-color: #f9fafb;
            width: 280px;
            transition: var(--transition);
        }
        #student-search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(128,0,0,0.1); outline: none; }

        .values-table { width:100%; background:white; border-radius:16px; box-shadow:var(--shadow); border-collapse:collapse; overflow:hidden; }
        .values-table th, .values-table td { padding:15px; text-align:left; border-bottom:1px solid #f0f0f0; }
        .values-table thead { background:var(--primary); color:white; font-weight:600; }
        .values-table tbody tr:last-child td { border-bottom:none; }
        .values-table tbody tr:hover { background-color:#f5f6fa; }
        .values-table select { width:100%; padding:8px; border:1px solid #ddd; border-radius:6px; font-family:'Poppins',sans-serif; }
        .student-name-col { font-weight: 600; color: var(--primary-dark); }
        .behavior-col { padding-left: 30px; color: var(--text-light); }
        .values-form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .values-form-header h2 { font-size: 1.5rem; color: var(--primary); }
        .btn-back { background: #6c757d; color: white; }
        .btn-back:hover { background: #5a6268; }


        .form-actions { text-align:right; margin-top:2rem; }
        .btn-save { background:var(--primary); color:white; padding:12px 25px; border:none; border-radius:8px; font-weight:600; font-size:1rem; cursor:pointer; transition:var(--transition); box-shadow:0 4px 15px rgba(128,0,0,0.2); }
        .btn-save:hover { background:var(--primary-dark); transform:translateY(-2px); }

        .legend-box {
            background: white; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem;
            box-shadow: var(--shadow); display: flex; gap: 1.5rem; flex-wrap: wrap; border-left: 5px solid var(--accent);
            font-size: 0.9rem;
        }
        .legend-item span { font-weight: 700; color: var(--primary); margin-right: 5px; }
    </style>
</head>
<body>

    <header class="header">
        <h1>MNCHS Grade Portal</h1>
        <div class="user-info">
            <a href="#" class="notification-bell"><i class="fas fa-bell"></i><span class="notification-badge"></span></a>
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
                <li><a href="teacherdashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="teacherstudent.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="teachermyclasses.php"><i class="fas fa-users"></i> My Classes</a></li>
                <li><a href="teachergradeentry.php"><i class="fas fa-edit"></i> Grade Entry</a></li>
                <li><a href="teachervaluesentry.php" class="active"><i class="fas fa-tasks"></i> Values Entry</a></li>
                <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <h1 class="page-title">Observed Values Entry</h1>
            <div class="filter-section">
                <div class="filter-group">
                    <label for="class-select">Select Class</label>
                    <select id="class-select" name="class">
                        <option value="11-STEM-A">Grade 11 - STEM A (Pre-Calculus)</option>
                        <option value="11-STEM-B">Grade 11 - STEM B (Pre-Calculus)</option>
                        <option value="12-HUMSS-A">Grade 12 - HUMSS A (Practical Research 2)</option>
                        <option value="12-ABM-C">Grade 12 - ABM C (Applied Economics)</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="quarter-select">Select Quarter</label>
                    <select id="quarter-select" name="quarter">
                        <option value="1">1st Quarter</option>
                        <option value="2">2nd Quarter</option>
                        <option value="3">3rd Quarter</option>
                        <option value="4">4th Quarter</option>
                    </select>
                </div>
                <button id="load-students-btn" class="btn" style="align-self: flex-end;"><i class="fas fa-filter"></i> Load Students</button>
            </div>

            <div class="legend-box">
                <div class="legend-item"><span>AO:</span> Always Observed</div>
                <div class="legend-item"><span>SO:</span> Sometimes Observed</div>
                <div class="legend-item"><span>RO:</span> Rarely Observed</div>
                <div class="legend-item"><span>NO:</span> Not Observed</div>
            </div>

            <!-- Student Selection List -->
            <div id="student-selection-container" class="student-selection-container" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <h2 style="font-size: 1.5rem; color: var(--primary); margin: 0;">Select a Student</h2>
                    <div class="search-group">
                        <i class="fas fa-search"></i>
                        <input type="search" id="student-search-input" placeholder="Search student name...">
                    </div>
                </div>
                <ul id="student-list" class="student-list">
                    <!-- Student list will be populated by JavaScript -->
                </ul>
            </div>

            <!-- Values Entry Form (for a single student) -->
            <div id="values-form-container" style="display: none;">
                <div class="values-form-header">
                    <h2 id="student-name-header"></h2>
                    <button id="back-to-list-btn" class="btn btn-back"><i class="fas fa-arrow-left"></i> Back to Student List</button>
                </div>
                <form action="#" method="POST">
                    <table class="values-table">
                        <thead>
                            <tr>
                                <th>Core Value</th>
                                <th>Behavior Statement</th>
                                <th>Rating</th>
                            </tr>
                        </thead>
                        <tbody id="values-tbody">
                            <!-- Values entry rows will be populated by JavaScript -->
                        </tbody>
                    </table>
                    <div class="form-actions">
                        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Values</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Container for the logout modal -->
    <div id="logout-modal-container"></div>
<!-- Link to shared and page-specific scripts -->
<script src="../../assets/js/NotificationManager.js"></script>
<script src="../../assets/js/teacher_shared.js"></script>
<script src="../../assets/js/teachervaluesentry.js"></script>

</body>
</html>