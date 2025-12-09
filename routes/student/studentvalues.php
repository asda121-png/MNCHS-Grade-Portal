<?php
session_start();
// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../index.php'); // Redirect to main login page
    exit();
}

$student_name = $_SESSION['student_name'] ?? 'Juan Dela Cruz';
$grade_level  = $_SESSION['grade_level'] ?? '11';
$section      = $_SESSION['section'] ?? 'STEM-A';
$student_id   = $_SESSION['user_id'];
$school_year  = "2025–2026";

// Include database config
require_once '../../includes/config.php';

// Fetch student's observed values from database
$values = [];

$stmt = $conn->prepare("
    SELECT 
        sv.id,
        sv.aspect_name AS value_category,
        sv.rating AS behavior_statement,
        sv.comments AS quarter_1,   -- or you can keep as 'comments'
        sv.recorded_date AS quarter_2, -- example mapping if needed
        sv.created_at AS quarter_3,     -- just placeholders if you need quarters
        sv.updated_at AS quarter_4      -- remove if not needed
    FROM student_values sv
    WHERE sv.student_id = ?
    ORDER BY sv.aspect_name ASC, sv.id ASC
");

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
    $values[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Observed Values | MNCHS Grade Portal</title>    <link rel="icon" href="../../assets/images/logo.ico" type="image/x-icon">
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
            font-size: 0.7rem; font-weight: 700; display: none;
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
        .container { display:flex; min-height:calc(100vh - 80px); }
        .sidebar { width:260px; background:white; padding:2rem 1.5rem; box-shadow:5px 0 15px rgba(0,0,0,0.05); position:sticky; top:80px; height:calc(100vh - 80px); overflow-y:auto; }
        
          .sidebar-logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .sidebar-logo {
            max-width: 120px;
            height: auto;
        }

        .sidebar ul { list-style-type: none; }
        .sidebar ul li { margin-bottom: 8px; }
        .sidebar ul li a { display:flex; align-items:center; gap:12px; padding:14px 18px; color:var(--text); text-decoration:none; border-radius:12px; font-weight:500; transition:var(--transition); }
        .sidebar ul li a:hover, .sidebar ul li a.active { background:var(--primary); color:white; transform:translateX(5px); box-shadow:0 5px 15px rgba(128,0,0,0.2); }
        .sidebar ul li a i { width:20px; text-align:center; font-size:1.1rem; }

        /* Main Content */
        .main-content { flex:1; padding:2.5rem; }
        .page-title { color:var(--primary); font-size:2rem; display:flex; align-items:center; gap:12px; margin-bottom:.5rem; }
        .subtitle { color:var(--text-light); font-size:1.1rem; margin-bottom:2rem; }

        /* Table Styling */
        table { width:100%; border-collapse:collapse; background:white; border-radius:16px; overflow:hidden; box-shadow:var(--shadow); }
        th { background:linear-gradient(90deg,var(--primary),var(--primary-dark)); color:white; padding:1.2rem; text-align:left; vertical-align: middle; }
        td { padding:1.1rem 1.2rem; border-bottom:1px solid #eee; vertical-align: top; }
        tr:hover { background:#f8f9fa; }
        
        /* Specific Column Styles */
        .core-value { font-weight: 700; color: var(--primary); width: 20%; background-color: #fffcfc; }
        .behavior { width: 50%; }
        .grade-col { text-align: center; font-weight: 600; width: 7.5%; }
        
        /* Legend Box */
        .legend-box {
            background: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;
            box-shadow: var(--shadow); display: flex; gap: 2rem; flex-wrap: wrap; border-left: 5px solid var(--accent);
        }
        .legend-item span { font-weight: 700; color: var(--primary); margin-right: 5px; }
        .legend-item { color: var(--text-light); font-size: 0.9rem; }

        @media (max-width:768px) { 
            .container { flex-direction:column; } 
            .sidebar { width:100%; height:auto; position:relative; top:0; display: none; }
            .sidebar.show { display: block; }
            table { display: block; overflow-x: auto; }
            .menu-icon { display: block; }
            .header h1 { font-size: 1.5rem; }
            .header h1 i { font-size: 1.7rem; }
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
            <span class="notification-badge">3</span>
        </a>
        <div class="profile-link" title="View Profile">
            <i class="fas fa-user-circle"></i><i class="fas fa-caret-down dropdown-caret"></i>
            <div class="profile-dropdown">
                <a href="studentprofile.php">Profile</a>
            </div>
        </div>
        <span><?php echo htmlspecialchars($student_name); ?></span>
    </div>
</header>

<div class="container">
    <aside class="sidebar">
        <div class="sidebar-logo-container">
<img src="../../assets/images/logo.png" alt="MNCHS Logo" class="sidebar-logo">
        </div>
        <ul>
            <li><a href="studentdashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="studentgrades.php"><i class="fas fa-clipboard-list"></i> My Grades</a></li>
            <li><a href="studentvalues.php" class="active"><i class="fas fa-heart"></i> Observed Values</a></li>
            <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1 class="page-title"></i> Learner's Observed Values</h1>
        <p class="subtitle">Grade <?php echo htmlspecialchars($grade_level); ?> - <?php echo htmlspecialchars($section); ?> • School Year <?php echo $school_year; ?></p>

        <div class="legend-box">
            <div class="legend-item"><span>AO</span> Always Observed</div>
            <div class="legend-item"><span>SO</span> Sometimes Observed</div>
            <div class="legend-item"><span>RO</span> Rarely Observed</div>
            <div class="legend-item"><span>NO</span> Not Observed</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Core Values</th>
                    <th>Behavior Statements</th>
                    <th class="grade-col">Q1</th>
                    <th class="grade-col">Q2</th>
                    <th class="grade-col">Q3</th>
                    <th class="grade-col">Q4</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($values) > 0): ?>
                    <?php 
                    $lastCategory = null;
                    $categoryCount = [];
                    
                    // Count occurrences of each category
                    foreach ($values as $value) {
                        $cat = $value['value_category'];
                        $categoryCount[$cat] = ($categoryCount[$cat] ?? 0) + 1;
                    }
                    
                    foreach ($values as $idx => $value): 
                        $category = $value['value_category'];
                        $showRowspan = ($lastCategory !== $category);
                        $rowspanValue = $categoryCount[$category] ?? 1;
                    ?>
                    <tr>
                        <?php if ($showRowspan): ?>
                            <td rowspan="<?php echo $rowspanValue; ?>" class="core-value"><?php echo htmlspecialchars($category); ?></td>
                        <?php endif; ?>
                        <td class="behavior"><?php echo htmlspecialchars($value['behavior_statement']); ?></td>
                        <td class="grade-col"><?php echo $value['quarter_1'] ?? '—'; ?></td>
                        <td class="grade-col"><?php echo $value['quarter_2'] ?? '—'; ?></td>
                        <td class="grade-col"><?php echo $value['quarter_3'] ?? '—'; ?></td>
                        <td class="grade-col"><?php echo $value['quarter_4'] ?? '—'; ?></td>
                    </tr>
                    <?php 
                        $lastCategory = $category;
                    endforeach; 
                    ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; color: #636e72; padding: 2rem;">No observed values recorded yet. Check back later!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
<!-- Container for the logout modal -->
<div id="logout-modal-container"></div>

<!-- Notification System -->
<script src="../../assets/js/NotificationManager.js"></script>

<!-- Link to the shared JavaScript file -->
<script src="../../assets/js/student_shared.js"></script>
</body>
</html>