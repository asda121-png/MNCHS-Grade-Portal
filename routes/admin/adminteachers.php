<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}
$admin_name = $_SESSION['user_name'] ?? 'Administrator';

require_once '../../includes/config.php';

// Fetch Classes for Dropdown
$classes = [];
$class_query = "SELECT id, class_name, section FROM classes ORDER BY class_name, section";
$class_res = $conn->query($class_query);
if ($class_res) {
    while($row = $class_res->fetch_assoc()) {
        $classes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers | MNCHS Grade Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="../../assets/images/logo.ico" type="image/x-icon">
    <style>
        :root {
            --primary: #800000; --primary-dark: #660000; --accent: #FFD700;
            --text: #2d3436; --bg: #f5f6fa; --white: #ffffff;
            --shadow: 0 8px 25px rgba(0,0,0,0.08); --transition: all 0.3s ease;
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
        .sidebar ul li a { display: flex; align-items: center; gap: 12px; padding: 14px 18px; color: var(--text); text-decoration: none; border-radius: 12px; font-weight: 500; transition: var(--transition); }
        .sidebar ul li a:hover, .sidebar ul li a.active { background: var(--primary); color: white; transform: translateX(5px); box-shadow: 0 5px 15px rgba(128, 0, 0, 0.2); }
        .main-content { flex: 1; padding: 2.5rem; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .btn { padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: var(--transition); display: flex; align-items: center; gap: 8px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .content-box { background: white; padding: 2rem; border-radius: 16px; box-shadow: var(--shadow); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { font-weight: 600; color: var(--primary); }
        
        /* Modal */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: none; justify-content: center; align-items: center; z-index: 2000; }
        .modal-content { background: white; padding: 2rem; border-radius: 16px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; }
        .checkbox-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
        }
        .sections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 10px;
            max-height: 300px;
            overflow-y: auto;
        }
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
                <li><a href="adminteachers.php" class="active"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a></li>
                <li><a href="adminreports.php"><i class="fas fa-file-alt"></i> Reports</a></li>
                <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <div class="page-header">
                <h2>Manage Teachers</h2>
                <button id="add-teacher-btn" class="btn btn-primary"><i class="fas fa-plus"></i> Add Teacher</button>
            </div>

            <div class="content-box">
                <table>
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Advisory Class</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="teachers-table-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add/Edit Teacher Modal -->
    <div id="teacher-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Add Teacher Profile</h3>
                <button id="close-teacher-modal" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
            </div>
            <form id="teacher-form">
                <input type="hidden" id="teacher-id">
                <div class="form-group">
                    <label>Employee Number</label>
                    <input type="text" id="employee-no" required>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="full-name" required>
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <select id="department" required onchange="toggleDepartmentFields()">
                        <option value="">Select Department Level</option>
                        <option value="Senior High School">Senior High School</option>
                        <option value="Junior High School">Junior High School</option>
                    </select>
                </div>
                <div class="form-group" id="shs-strand-group" style="display:none;">
                    <label>Track / Strand</label>
                    <select id="shs-strand">
                        <option value="">Select Strand</option>
                        <option value="STEM">STEM</option>
                        <option value="ABM">ABM</option>
                        <option value="HUMSS">HUMSS</option>
                        <option value="GAS">GAS</option>
                        <option value="TVL-ICT">TVL-ICT</option>
                        <option value="TVL-HE">TVL-HE</option>
                        <option value="TVL-IA">TVL-IA</option>
                        <option value="TVL-AFA">TVL-AFA</option>
                        <option value="SPORTS">SPORTS</option>
                        <option value="ARTS AND DESIGN">ARTS AND DESIGN</option>
                    </select>
                </div>
                <div class="form-group" id="jhs-dept-group" style="display:none;">
                    <label>JHS Department</label>
                    <select id="jhs-dept">
                        <option value="">Select Department</option>
                        <option value="English Department">English Department</option>
                        <option value="Mathematics Department">Mathematics Department</option>
                        <option value="Science Department">Science Department</option>
                        <option value="Social Studies Department">Social Studies Department</option>
                        <option value="Filipino / Language Department">Filipino / Language Department</option>
                        <option value="MAPEH Department">MAPEH Department (Music, Arts, Physical Education, Health)</option>
                        <option value="TLE / TVL Department">TLE / TVL Department (Technology and Livelihood Education)</option>
                    </select>
                </div>
                
                <!-- Dynamic Subject/Grade Level Field -->
                <div class="form-group" id="subject-container">
                    <label id="subject-label">Subjects Handled</label>
                    <div id="subject-input-wrapper">
                        <input type="text" id="subjects" placeholder="Select department first" disabled>
                    </div>
                </div>

                <!-- Dynamic Sections Field -->
                <div class="form-group">
                    <label>Assigned Sections (Subject Teaching)</label>
                    <div id="sections-container" class="checkbox-container">
                        <p style="color:#999; font-size:0.9rem;">Select a department/level to view sections.</p>
                    </div>
                    <input type="hidden" id="sections">
                </div>

                <div style="margin-top: 1.5rem; border-top: 1px solid #eee; padding-top: 1rem;">
                    <h4 style="color:var(--primary); margin-bottom:1rem;">Advisory Class Assignment</h4>
                    <div class="form-group">
                        <label>Assign as Adviser for Section:</label>
                        <select id="advisory-section">
                            <option value="">-- No Advisory Class --</option>
                        </select>
                        <small style="color:#666; display:block; margin-top:5px;">Note: A section can only have one adviser.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </div>
            </form>
        </div>
    </div>

    <div id="logout-modal-container"></div>
    <script src="../../assets/js/NotificationManager.js"></script>
    <script>
        window.dbClasses = <?php echo json_encode($classes); ?>;
    </script>
    <script src="../../assets/js/adminteachers.js"></script>
    <!-- Reusing logout logic from dashboard js if needed, or including it in adminteachers.js -->
    <script>
        // Simple logout modal loader if not using admindashboard.js
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