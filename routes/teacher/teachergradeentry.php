<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../index.php'); // Redirect to login page
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
    <title>Grade Entry | MNCHS Grade Portal</title>
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
        .header h1 { font-size:1.8rem; font-weight:600; display:flex; align-items:center; gap:12px; }
        .header h1 i { color:var(--accent); font-size:2rem; }
        .user-info { font-size:1rem; display:flex; align-items:center; gap:25px; }
        .menu-icon { display: none; color: white; font-size: 1.5rem; text-decoration: none; margin-right: 1rem; }

        .profile-link {
            position: relative;
            color: white;
            font-size: 1.6rem;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;           
            cursor: pointer;
        }

        .profile-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: none;
            min-width: 160px;
            z-index: 100;
        }

        .profile-dropdown.show {
            display: block;
        }

        .profile-dropdown a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: var(--text);
            transition: background-color 0.3s ease;
            text-align: left;
        }

        .profile-dropdown a:hover { background-color: #f5f6fa; }
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
        .notification-bell { position:relative; color:white; font-size:1.3rem; text-decoration:none; transition:var(--transition); }
        .notification-bell:hover { color:var(--accent); }
        .notification-badge { position:absolute; top:-5px; right:-8px; background-color:#e74c3c; color:white; border-radius:50%; width:18px; height:18px; font-size:0.7rem; font-weight:700; display:none; justify-content:center; align-items:center; border:2px solid var(--primary-dark); }
        .container { display:flex; min-height:calc(100vh - 80px); }
        .sidebar { width:260px; background:white; padding:2rem 1.5rem; box-shadow:5px 0 15px rgba(0,0,0,0.05); position:sticky; top:80px; height:calc(100vh - 80px); overflow-y:auto; }
        .sidebar-logo-container { text-align: center; margin-bottom: 2rem; }
        .sidebar-logo { max-width: 120px; height: auto; }
        .sidebar h2 { color:var(--primary); font-size:1.4rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; }
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
        .grades-table { width:100%; background:white; border-radius:16px; box-shadow:var(--shadow); border-collapse:collapse; overflow:hidden; }
        .grades-table th, .grades-table td { padding:15px; text-align:left; border-bottom:1px solid #f0f0f0; }
        .grades-table thead { background:var(--primary); color:white; font-weight:600; }
        .grades-table tbody tr:last-child td { border-bottom:none; }
        .grades-table tbody tr:hover { background-color:#f5f6fa; }
        .grades-table input[type="number"] { width:70px; padding:8px; border:1px solid #ddd; border-radius:6px; text-align:center; font-family:'Poppins',sans-serif; }
        .grades-table input[type="number"]:focus { border-color:var(--primary); outline:none; box-shadow:0 0 0 2px rgba(128,0,0,0.1); }
        .final-grade { font-weight:600; }
        .remarks.passed { color:#27ae60; font-weight:600; }
        .remarks.failed { color:#e74c3c; font-weight:600; }
        .form-actions { text-align:right; margin-top:2rem; }
        .btn-save { background:var(--primary); color:white; padding:12px 25px; border:none; border-radius:8px; font-weight:600; font-size:1rem; cursor:pointer; transition:var(--transition); box-shadow:0 4px 15px rgba(128,0,0,0.2); }
        .btn-save:hover { background:var(--primary-dark); transform:translateY(-2px); }
        .modal-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); display:flex; justify-content:center; align-items:center; z-index:2000; opacity:0; visibility:hidden; transition:opacity 0.3s ease, visibility 0.3s ease; }
        .modal-overlay.show { opacity:1; visibility:visible; }
        .modal-content { background:white; padding:2.5rem; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.2); text-align:center; max-width:400px; width:90%; transform:scale(0.9); transition:transform 0.3s ease; }
        .modal-overlay.show .modal-content { transform:scale(1); }
        .modal-content h2 { color:var(--primary); margin-bottom:1rem; font-size:1.6rem; }
        .modal-content p { color:var(--text-light); margin-bottom:2rem; font-size:1.1rem; }
        .modal-buttons { display:flex; justify-content:center; gap:1rem; }
        .modal-buttons button, .modal-buttons a { padding:12px 24px; border-radius:8px; font-weight:600; cursor:pointer; text-decoration:none; }
        @media (max-width: 768px) {
            .container { flex-direction:column; }
            .sidebar { width:100%; height:auto; position:relative; top:0; padding:1.5rem; }
            .main-content { padding:1.5rem; }
            .header h1 { font-size:1.5rem; }
            .grades-table { display:block; overflow-x:auto; }
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
                <li><a href="teacherdashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="teachermyclasses.php"><i class="fas fa-users"></i> My Classes</a></li>
                <li><a href="teachergradeentry.php" class="active"><i class="fas fa-edit"></i> Grade Entry</a></li>
                <li><a href="teachervaluesentry.php"><i class="fas fa-tasks"></i> Values Entry</a></li>
                <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <h1 class="page-title">Students Grades</h1>
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

            <form action="#" method="POST">
                <table id="grades-table" class="grades-table">
                    <thead>
                        <tr>
                            <th>LRN</th>
                            <th>Student Name</th>
                            <th>Q1</th>
                            <th>Q2</th>
                            <th>Q3</th>
                            <th>Q4</th>
                            <th>Final Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="students-tbody">
                        <!-- Sample Student Rows -->
                        <tr>
                            <td>123456789012</td>
                            <td>Aguilar, Juan D.</td>
                            <td><input type="number" name="q1[]" min="60" max="100"></td>
                            <td><input type="number" name="q2[]" min="60" max="100"></td>
                            <td><input type="number" name="q3[]" min="60" max="100"></td>
                            <td><input type="number" name="q4[]" min="60" max="100"></td>
                            <td class="final-grade"></td>
                            <td class="remarks"></td>
                        </tr>
                        <tr>
                            <td>123456789013</td>
                            <td>Bautista, Maria C.</td>
                            <td><input type="number" name="q1[]" min="60" max="100"></td>
                            <td><input type="number" name="q2[]" min="60" max="100"></td>
                            <td><input type="number" name="q3[]" min="60" max="100"></td>
                            <td><input type="number" name="q4[]" min="60" max="100"></td>
                            <td class="final-grade"></td>
                            <td class="remarks"></td>
                        </tr>
                        <tr>
                            <td>123456789014</td>
                            <td>Cruz, Pedro S.</td>
                            <td><input type="number" name="q1[]" min="60" max="100"></td>
                            <td><input type="number" name="q2[]" min="60" max="100"></td>
                            <td><input type="number" name="q3[]" min="60" max="100"></td>
                            <td><input type="number" name="q4[]" min="60" max="100"></td>
                            <td class="final-grade"></td>
                            <td class="remarks"></td>
                        </tr>
                         <!-- Add more student rows as needed -->
                    </tbody>
                </table>

                <div class="form-actions">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Grades</button>
                </div>
            </form>
        </main>
    </div>

    <!-- Container for the logout modal -->
    <div id="logout-modal-container"></div>
<!-- Link to shared and page-specific scripts -->
<script src="../../assets/js/NotificationManager.js"></script>
<script src="../../assets/js/GradeEntryLock.js"></script>
<script src="../../assets/js/teacher_shared.js"></script>
<script src="../../assets/js/teachergradeentry.js"></script>

</body>
</html>