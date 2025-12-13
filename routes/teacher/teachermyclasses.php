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
    <title>My Classes | MNCHS Grade Portal</title>
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
        .classes-table { width:100%; background:white; border-radius:16px; box-shadow:var(--shadow); border-collapse:collapse; overflow:hidden; }
        .classes-table th, .classes-table td { padding:18px 20px; text-align:left; }
        .classes-table thead { background:var(--primary); color:white; font-weight:600; }
        .classes-table tbody tr { border-bottom:1px solid #f0f0f0; }
        .classes-table tbody tr:last-child { border-bottom:none; }
        .classes-table .col-center { text-align: center; }
        .classes-table tbody tr:hover { background-color:#f5f6fa; }
        .btn-view { background:var(--primary); color:white; padding:8px 15px; border-radius:8px; text-decoration:none; font-weight:500; transition:var(--transition); display:inline-block; border:none; cursor:pointer; font-family:'Poppins',sans-serif; font-size:0.95rem; }
        .btn-view:hover { background:var(--primary-dark); transform:scale(1.05); }
        .modal-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); display:flex; justify-content:center; align-items:flex-start; padding:40px 20px; z-index:2000; opacity:0; visibility:hidden; transition:opacity 0.3s ease, visibility 0.3s ease; overflow-y:auto; }
        .modal-overlay.show { opacity:1; visibility:visible; }
        .modal-content { background:white; padding:2rem; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.2); text-align:center; max-width:800px; width:95%; transform:scale(0.9); transition:transform 0.3s ease; }
        .modal-overlay.show .modal-content { transform:scale(1); }
        .modal-content h2 { color:var(--primary); margin-bottom:1rem; font-size:1.6rem; }
        .modal-content p { color:var(--text-light); margin-bottom:2rem; font-size:1.1rem; }
        .modal-buttons { display:flex; justify-content:center; gap:1rem; }
        .modal-buttons button, .modal-buttons a { padding:12px 24px; border-radius:8px; font-weight:600; cursor:pointer; text-decoration:none; }

        /* View Students Modal */
        .modal-header { display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:1rem; margin-bottom:1.5rem; text-align:left; }
        .modal-header h3 { font-size:1.5rem; color:var(--primary); margin:0; }
        .modal-header .class-info { font-size:0.9rem; color:var(--text-light); margin-top:0.25rem; }
        .close-button { font-size:2rem; color:var(--text-light); cursor:pointer; background:none; border:none; line-height:1; }
        .close-button:hover { color:var(--primary); }
        .students-table { width:100%; border-collapse:collapse; margin-top:1rem; text-align:left; }
        .students-table th, .students-table td { padding:12px 15px; border-bottom:1px solid #f0f0f0; }
        .students-table thead th { background:linear-gradient(135deg, #800000 0%, #660000 100%); color:white; font-weight:600; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.5px; }
        .students-table tbody tr:hover { background-color:#fef3f3; }
        .students-table tbody tr:nth-child(even) { background-color:#fafbfc; }
        .search-filter { display:flex; gap:1rem; margin-bottom:1rem; align-items:center; }
        .search-filter input { flex:1; padding:10px 15px; border:1px solid #ddd; border-radius:8px; font-family:'Poppins',sans-serif; font-size:0.95rem; }
        .search-filter input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(128,0,0,0.1); }
        .modal-footer { display:flex; justify-content:flex-end; gap:1rem; margin-top:1.5rem; padding-top:1rem; border-top:1px solid #eee; }
        .btn-secondary { background:#f1f1f1; color:var(--text); border:1px solid #ddd; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif; }
        .btn-secondary:hover { background:#e7e7e7; }
        @media (max-width: 768px) {
            .container { flex-direction:column; }
            .sidebar { width:100%; height:auto; position:relative; top:0; padding:1.5rem; }
            .main-content { padding:1.5rem; }
            .header h1 { font-size:1.5rem; }
            .classes-table { display:block; overflow-x:auto; }
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
                <li><a href="teacherstudent.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="teachermyclasses.php" class="active"><i class="fas fa-users"></i> My Classes</a></li>
                <li><a href="teachergradeentry.php"><i class="fas fa-edit"></i> Grade Entry</a></li>
                <li><a href="teachervaluesentry.php"><i class="fas fa-tasks"></i> Values Entry</a></li>
                <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1 class="page-title">Classes</h1>

            <table class="classes-table">
                <thead>
                    <tr>
                        <th>Grade & Section</th>
                        <th>Subject</th>
                        <th class="col-center">No. of Students</th>
                        <th>Schedule</th>
                        <th class="col-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Grade 11 - STEM A</td>
                        <td>Pre-Calculus</td>
                        <td class="col-center">42</td>
                        <td>Mon/Wed 9:00-10:00 AM</td>
                        <td class="col-center"><button class="btn-view" data-class="11-STEM-A" data-grade="Grade 11 - STEM A" data-subject="Pre-Calculus">View Class</button></td>
                    </tr>
                    <tr>
                        <td>Grade 11 - STEM B</td>
                        <td>Pre-Calculus</td>
                        <td class="col-center">40</td>
                        <td>Mon/Wed 10:00-11:00 AM</td>
                        <td class="col-center"><button class="btn-view" data-class="11-STEM-B" data-grade="Grade 11 - STEM B" data-subject="Pre-Calculus">View Class</button></td>
                    </tr>
                    <tr>
                        <td>Grade 12 - HUMSS A</td>
                        <td>Practical Research 2</td>
                        <td class="col-center">45</td>
                        <td>Tue/Thu 1:00-2:30 PM</td>
                        <td class="col-center"><button class="btn-view" data-class="12-HUMSS-A" data-grade="Grade 12 - HUMSS A" data-subject="Practical Research 2">View Class</button></td>
                    </tr>
                    <tr>
                        <td>Grade 12 - ABM C</td>
                        <td>Applied Economics</td>
                        <td class="col-center">35</td>
                        <td>Fri 8:00-10:00 AM</td>
                        <td class="col-center"><button class="btn-view" data-class="12-ABM-C" data-grade="Grade 12 - ABM C" data-subject="Applied Economics">View Class</button></td>
                    </tr>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Container for the logout modal -->
    <div id="logout-modal-container"></div>

    <!-- View Students Modal -->
    <div id="viewStudentsModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 id="modalClassTitle">Student List</h3>
                    <div class="class-info" id="modalClassInfo"></div>
                </div>
                <button class="close-button" id="closeStudentsModal">&times;</button>
            </div>
            <div class="search-filter">
                <input type="text" id="studentSearchInput" placeholder="Search by name or LRN...">
            </div>
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>LRN</th>
                            <th>Student Name</th>
                            <th>Gender</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        <!-- Students will be loaded here -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="closeStudentsBtn">Close</button>
            </div>
        </div>
    </div>

<!-- Link to the shared JavaScript file -->
<script src="../../assets/js/NotificationManager.js"></script>
<script src="../../assets/js/teacher_shared.js"></script>

<script>
    // Sample student data for each class
    const classStudents = {
        '11-STEM-A': [
            { lrn: '123456789001', name: 'Aguilar, Juan D.', gender: 'Male', contact: '09171234567' },
            { lrn: '123456789002', name: 'Bautista, Maria C.', gender: 'Female', contact: '09181234567' },
            { lrn: '123456789003', name: 'Cruz, Pedro A.', gender: 'Male', contact: '09191234567' },
            { lrn: '123456789004', name: 'Dela Cruz, Ana B.', gender: 'Female', contact: '09201234567' },
            { lrn: '123456789005', name: 'Evangelista, Jose M.', gender: 'Male', contact: '09211234567' },
            { lrn: '123456789006', name: 'Fernandez, Rosa L.', gender: 'Female', contact: '09221234567' },
            { lrn: '123456789007', name: 'Garcia, Miguel S.', gender: 'Male', contact: '09231234567' },
            { lrn: '123456789008', name: 'Hernandez, Sofia P.', gender: 'Female', contact: '09241234567' },
        ],
        '11-STEM-B': [
            { lrn: '123456789101', name: 'Ignacio, Carlos R.', gender: 'Male', contact: '09251234567' },
            { lrn: '123456789102', name: 'Jimenez, Elena T.', gender: 'Female', contact: '09261234567' },
            { lrn: '123456789103', name: 'Laurel, Roberto V.', gender: 'Male', contact: '09271234567' },
            { lrn: '123456789104', name: 'Mendoza, Patricia W.', gender: 'Female', contact: '09281234567' },
            { lrn: '123456789105', name: 'Navarro, Antonio X.', gender: 'Male', contact: '09291234567' },
            { lrn: '123456789106', name: 'Ocampo, Cristina Y.', gender: 'Female', contact: '09301234567' },
        ],
        '12-HUMSS-A': [
            { lrn: '123456789201', name: 'Pascual, Fernando Z.', gender: 'Male', contact: '09311234567' },
            { lrn: '123456789202', name: 'Quinto, Angela A.', gender: 'Female', contact: '09321234567' },
            { lrn: '123456789203', name: 'Reyes, Benjamin B.', gender: 'Male', contact: '09331234567' },
            { lrn: '123456789204', name: 'Santos, Catherine C.', gender: 'Female', contact: '09341234567' },
            { lrn: '123456789205', name: 'Torres, Daniel D.', gender: 'Male', contact: '09351234567' },
            { lrn: '123456789206', name: 'Uy, Elizabeth E.', gender: 'Female', contact: '09361234567' },
            { lrn: '123456789207', name: 'Villanueva, Francis F.', gender: 'Male', contact: '09371234567' },
        ],
        '12-ABM-C': [
            { lrn: '123456789301', name: 'Wong, Gabriel G.', gender: 'Male', contact: '09381234567' },
            { lrn: '123456789302', name: 'Xavier, Helen H.', gender: 'Female', contact: '09391234567' },
            { lrn: '123456789303', name: 'Yap, Ian I.', gender: 'Male', contact: '09401234567' },
            { lrn: '123456789304', name: 'Zamora, Jessica J.', gender: 'Female', contact: '09411234567' },
            { lrn: '123456789305', name: 'Aquino, Kevin K.', gender: 'Male', contact: '09421234567' },
        ]
    };

    const modal = document.getElementById('viewStudentsModal');
    const modalTitle = document.getElementById('modalClassTitle');
    const modalClassInfo = document.getElementById('modalClassInfo');
    const studentsTableBody = document.getElementById('studentsTableBody');
    const searchInput = document.getElementById('studentSearchInput');
    let currentClassId = null;

    // Open modal when clicking View Class button
    document.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', function() {
            const classId = this.getAttribute('data-class');
            const gradeSection = this.getAttribute('data-grade');
            const subject = this.getAttribute('data-subject');
            
            currentClassId = classId;
            modalTitle.textContent = gradeSection;
            modalClassInfo.textContent = subject;
            
            loadStudents(classId);
            modal.classList.add('show');
            searchInput.value = '';
        });
    });

    // Load students into table
    function loadStudents(classId, filter = '') {
        const students = classStudents[classId] || [];
        const filtered = filter 
            ? students.filter(s => s.name.toLowerCase().includes(filter.toLowerCase()) || s.lrn.includes(filter))
            : students;
        
        studentsTableBody.innerHTML = filtered.length > 0 
            ? filtered.map((s, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td>${s.lrn}</td>
                    <td>${s.name}</td>
                    <td>${s.gender}</td>
                    <td>${s.contact}</td>
                </tr>
            `).join('')
            : '<tr><td colspan="5" style="text-align:center; color:#636e72; padding:2rem;">No students found.</td></tr>';
    }

    // Search filter
    searchInput.addEventListener('input', function() {
        if (currentClassId) {
            loadStudents(currentClassId, this.value);
        }
    });

    // Close modal
    document.getElementById('closeStudentsModal').addEventListener('click', () => modal.classList.remove('show'));
    document.getElementById('closeStudentsBtn').addEventListener('click', () => modal.classList.remove('show'));
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('show');
    });
</script>

</body>
</html>