<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../index.php'); // Redirect to main login page
    exit();
}
$student_name = $_SESSION['student_name'] ?? 'Student';
$grade_level  = $_SESSION['grade_level'] ?? '11';
$section      = $_SESSION['section'] ?? 'STEM-A';
$lrn          = $_SESSION['lrn'] ?? 'N/A';
$email        = $_SESSION['email'] ?? '';
$username     = $_SESSION['username'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings | MNCHS Grade Portal</title>
    <link rel="icon" href="../assets/images/logo.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
        .user-info { display: flex; align-items: center; gap: 25px; }
        .menu-icon { display: none; color: white; font-size: 1.5rem; text-decoration: none; margin-right: 1rem; }
        .notification-bell {
            position: relative;
            color: white;
            font-size: 1.3rem;
            text-decoration: none;
            transition: var(--transition);
        }
        .notification-bell:hover { color: var(--accent); }
        .notification-badge {
            position: absolute; top: -5px; right: -8px; background-color: #e74c3c;
            color: white; border-radius: 50%; width: 18px; height: 18px;
            font-size: 0.7rem; font-weight: 700; display: flex;
            justify-content: center; align-items: center;
            border: 2px solid var(--primary-dark);
        }
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
            background-color: white;
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
            background-color: #f5f6fa; /* --bg color */
        }

        .profile-link {
            color: white;
            font-size: 1.6rem;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }
        .profile-link:hover { color: var(--accent); }

        .dropdown-caret {
            font-size: 0.9rem;
            margin-left: 8px;
            transition: var(--transition);
        }

        /* Layout */
         .sidebar-logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .sidebar-logo {
            max-width: 120px;
            height: auto;
        }

        .container { 
            display:flex; 
            min-height:calc(100vh - 80px); 
        }
        .sidebar { 
            width:260px; 
            background:white; 
            padding:2rem 1.5rem; 
            box-shadow:5px 0 15px rgba(0,0,0,0.05); 
            position:sticky; 
            top:80px; 
            height:calc(100vh - 80px); 
            overflow-y:auto; 
        }
        .sidebar ul { list-style-type: none; }
        .sidebar ul li { margin-bottom: 8px; }
        .sidebar ul li a { display:flex; align-items:center; gap:12px; padding:14px 18px; color:var(--text); text-decoration:none; border-radius:12px; font-weight:500; transition:var(--transition); }
        .sidebar ul li a:hover, .sidebar ul li a.active { background:var(--primary); color:white; transform:translateX(5px); box-shadow:0 5px 15px rgba(128,0,0,0.2); }
        .sidebar ul li a i { width:20px; text-align:center; font-size:1.1rem; }

        /* Main Content */
        .main-content { 
            flex:1; 
            padding:2.5rem; 
            padding:2.5rem;
            background: transparent; 
        }
        .page-title { color:var(--primary); font-size:2rem; display:flex; align-items:center; gap:12px; margin-bottom:2rem; max-width: 1000px; }

        /* Form Styling */
        .profile-form {
            background: white; padding: 2rem; border-radius: 16px; box-shadow: var(--shadow); max-width: 1000px;
            border-left: 5px solid var(--accent);
        }
        .form-section { margin-bottom: 2.5rem; }
        .form-section:last-child { margin-bottom: 0; }
        .form-section h3 { color: var(--primary); margin-bottom: 1.5rem; border-bottom: 2px solid #eee; padding-bottom: 0.75rem; font-size: 1.25rem; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; margin-bottom: 8px; color: var(--text-light); font-size: 0.9rem; }
        .form-group input {
            padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif;
            font-size: 1rem; transition: var(--transition); background-color: #f9fafb;
        }
        .form-group input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(128,0,0,0.1); outline: none; }
        .form-group input[readonly] { background-color: #eee; cursor: not-allowed; }

        .form-actions { text-align: right; margin-top: 2rem; }
        .btn-save {
            background: var(--primary); color: white; padding: 12px 25px; border: none; border-radius: 8px;
            font-weight: 600; font-size: 1rem; cursor: pointer; transition: var(--transition);
            box-shadow: 0 4px 15px rgba(128,0,0,0.2);
        }
        .btn-save:hover { background: var(--primary-dark); transform: translateY(-2px); }

        @media (max-width:768px) { 
            .container { flex-direction:column; } 
            .sidebar { width:100%; height:auto; position:relative; top:0; }
            .menu-icon { display: block; }
            .header h1 { font-size: 1.5rem; }
            .header h1 i { font-size: 1.7rem; }
            .page-title { font-size: 1.7rem; }
            .main-content {
                padding: 1.5rem;
            }
        }
    </style>

</head>
<body>

<header class="header"> 
    <h1></i> MNCHS Grade Portal</h1>
    <div class="user-info">
        <a href="#" class="notification-bell">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">3</span>
        </a>
        <div class="profile-link" title="View Profile">
            <i class="fas fa-user-circle"></i><i class="fas fa-caret-down dropdown-caret"></i>
            <div class="profile-dropdown">
                <a href="studentprofile.php">Profile</a>
            </div>
        </div>
        <span><?php echo htmlspecialchars($student_name); ?> (Student)</span>
    </div>
</header>

<div class="container">
    <aside class="sidebar">
        <div class="sidebar-logo-container">
            <img src="../assets/images/logo.png" alt="MNCHS Logo" class="sidebar-logo">
        </div>
        <ul>
            <li><a href="studentdashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="studentgrades.php"><i class="fas fa-clipboard-list"></i> My Grades</a></li>
            <li><a href="studentvalues.php"><i class="fas fa-heart"></i> Observed Values</a></li>
            <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1 class="page-title"></i> Profile Settings</h1>

        <form action="#" method="POST" class="profile-form">
            <div class="form-section">
                <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="" readonly>
                    </div>
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" value="" readonly>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="" readonly>
                    </div>
                    <div class="form-group">
                        <label for="suffix">Suffix (e.g., Jr., III)</label>
                        <input type="text" id="suffix" name="suffix" value="" readonly>
                    </div>
                    <div class="form-group">
                        <label for="lrn">Learner Reference Number (LRN)</label>
                        <input type="text" id="lrn" name="lrn" value="" readonly>
                    </div>
                    <div class="form-group">
                        <label for="grade_level">Grade Level</label>
                        <input type="text" id="grade_level" name="grade_level" value="" readonly>
                    </div>
                    <div class="form-group">
                        <label for="section">Section</label>
                        <input type="text" id="section" name="section" value="" readonly>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-lock"></i> Student Account</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="" readonly>
                    </div>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </form>
    </main>
</div> <!-- End of .container -->

<!-- Container for the logout modal -->
<div id="logout-modal-container"></div>
<!-- Link to the shared JavaScript file -->
<script src="../../assets/js/student_shared.js"></script>
</body>
</html>