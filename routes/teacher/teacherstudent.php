<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../index.php'); // Redirect to login page
    exit();
}

require_once '../../includes/config.php';

function determine_student_status(array $student): array {
    $statusLabel = 'Enrolled';
    $statusClass = 'enrolled';

    if (empty($student['enrollment_date'])) {
        $statusLabel = 'Not Enrolled';
        $statusClass = 'not-enrolled';
    }

    return [$statusLabel, $statusClass];
}

function format_grade_section($gradeLevel, $section): string {
    $gradePart = ($gradeLevel !== null && $gradeLevel !== '') ? 'Grade ' . $gradeLevel : '';
    $sectionPart = $section ? trim($section) : '';

    if ($gradePart && $sectionPart) {
        return $gradePart . ' - ' . $sectionPart;
    }

    if ($gradePart || $sectionPart) {
        return $gradePart ?: $sectionPart;
    }

    return 'Not Assigned';
}

function normalize_grade_level($gradeLevel): string {
    $raw = trim((string) ($gradeLevel ?? ''));
    if ($raw === '') {
        return '';
    }

    if (preg_match('/(\d{1,2})/', $raw, $matches)) {
        return $matches[1];
    }

    return $raw;
}

$students = [];
$studentQueryError = null;
$gradeSections = [];

try {
    // Build grade->sections map from DB (merge both sources so we cover all existing sections)
    $gradeSectionRows = [];
    $gradeSectionQuery = "SELECT DISTINCT grade_level, section
        FROM (
            SELECT grade_level, section FROM classes
            UNION ALL
            SELECT grade_level, section FROM students
        ) src
        WHERE grade_level IS NOT NULL
          AND grade_level <> ''
          AND section IS NOT NULL
          AND section <> ''
        ORDER BY grade_level ASC, section ASC";

    if ($result = $conn->query($gradeSectionQuery)) {
        while ($row = $result->fetch_assoc()) {
            $gradeSectionRows[] = $row;
        }
        $result->free();
    }

    foreach ($gradeSectionRows as $row) {
        $grade = normalize_grade_level($row['grade_level'] ?? '');
        $section = trim((string) ($row['section'] ?? ''));
        if ($grade === '' || $section === '') {
            continue;
        }
        if (!isset($gradeSections[$grade])) {
            $gradeSections[$grade] = [];
        }
        if (!in_array($section, $gradeSections[$grade], true)) {
            $gradeSections[$grade][] = $section;
        }
    }

    $query = "SELECT 
                s.id,
                s.student_id,
                s.lrn,
                s.student_name,
                s.grade_level,
                s.section,
                s.guardian_contact,
                s.address,
                s.enrollment_date,
                s.date_of_birth,
                u.email,
                u.username,
                u.first_name,
                u.last_name
            FROM students s
            LEFT JOIN users u ON s.user_id = u.id
            ORDER BY s.student_name ASC";

    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        $result->free();
    } else {
        $studentQueryError = $conn->error;
    }
} catch (Throwable $e) {
    $studentQueryError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students | MNCHS Grade Portal</title>
    <link rel="icon" href="../../assets/images/logo.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #800000; --primary-dark: #660000; --accent: #FFD700;
            --text: #2d3436; --text-light: #636e72; --shadow: 0 8px 25px rgba(0,0,0,0.08);
            --header-height: 77px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .header { background: linear-gradient(90deg, var(--primary), var(--primary-dark)); color: white; padding: 1.2rem 2rem; box-shadow: 0 4px 15px rgba(128, 0, 0, 0.3); position: sticky; top: 0; z-index: 1000; display: flex; justify-content: space-between; align-items: center; min-height: var(--header-height); }
        .header h1 { font-size: 1.8rem; font-weight: 600; }
        .user-info { display: flex; align-items: center; gap: 25px; }
        .notification-bell { position: relative; color: white; font-size: 1.3rem; text-decoration: none; }
        .notification-badge { position: absolute; top: -5px; right: -8px; background-color: #e74c3c; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; font-weight: 700; display: none; justify-content: center; align-items: center; border: 2px solid var(--primary-dark); }
        .container { flex: 1; display: flex; }
        .sidebar { width: 260px; background: white; padding: 2rem 1.5rem; box-shadow: 5px 0 15px rgba(0,0,0,0.05); position: sticky; top: var(--header-height); height: calc(100vh - var(--header-height)); overflow-y: auto; }
        .sidebar-logo-container { text-align: center; margin-bottom: 2rem; }
        .sidebar-logo { max-width: 120px; height: auto; }
        .sidebar ul { list-style: none; }
        .sidebar ul li { margin-bottom: 8px; }
        .sidebar ul li a { display: flex; align-items: center; gap: 12px; padding: 14px 18px; color: var(--text); text-decoration: none; border-radius: 12px; font-weight: 500; transition: all 0.3s ease; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background: var(--primary); color: white; transform: translateX(5px); box-shadow: 0 5px 15px rgba(128, 0, 0, 0.2); }
        .sidebar ul li a i { font-size: 1.1rem; width: 20px; text-align: center; }
        .main-content { flex: 1; padding: 1.75rem 2rem; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
        .page-header h2 { font-size: 1.75rem; color: var(--primary); font-weight: 700; }
        .btn-primary { background: var(--primary); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer; transition: all 0.3s ease; font-size: 1rem; box-shadow: 0 4px 12px rgba(128, 0, 0, 0.15); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 6px 16px rgba(128, 0, 0, 0.25); }
        .content-box { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); padding: 1.75rem; border: 1px solid #f0f0f0; }
        .filters { 
            display: flex; 
            gap: 1rem; 
            margin-bottom: 1.25rem; 
            flex-wrap: wrap; 
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            padding: 1.25rem;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        .filters input, .filters select { 
            padding: 12px 16px; 
            border: 2px solid #e5e7eb; 
            border-radius: 8px; 
            font-family: 'Poppins', sans-serif; 
            font-size: 0.95rem; 
            background-color: white;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .filters input::placeholder { color: #9ca3af; font-weight: 400; }
        .filters input:focus, .filters select:focus { 
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.1);
            background-color: #fff5f5;
        }
        .search-box { 
            position: relative; 
            flex: 1;
            min-width: 250px;
        }
        .search-box input { 
            padding-left: 40px; 
            width: 100%; 
        }
        .search-box i { 
            position: absolute; 
            left: 14px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: #9ca3af; 
        }
        .table-wrapper { overflow-x: auto; margin-top: 1.25rem; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 1rem 1.25rem; text-align: left; border-bottom: 1px solid #e5e7eb; font-size: 1rem; }
        thead th { 
            background: linear-gradient(135deg, #800000 0%, #660000 100%); 
            color: white; 
            text-transform: uppercase; 
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 1px;
            border-bottom: 3px solid #4a0000;
        }
        tbody tr { 
            transition: all 0.3s ease;
            position: relative;
        }
        tbody tr:hover { 
            background-color: #fef3f3;
            box-shadow: 0 4px 12px rgba(128, 0, 0, 0.08) inset;
            transform: scale(1.002);
        }
        tbody tr:nth-child(even) { background-color: #fafbfc; }
        .status-badge { 
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px; 
            border-radius: 28px; 
            font-weight: 700; 
            font-size: 0.85rem;
            text-align: center;
            min-width: 130px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-transform: capitalize;
            letter-spacing: 0.3px;
        }
        .status-badge::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-badge.enrolled { 
            background: linear-gradient(135deg, #dcfce7 0%, #c8f7d8 100%);
            color: #145a32; 
            border: 2px solid #82d9a8;
        }
        .status-badge.enrolled::before { background-color: #27ae60; }
        .status-badge.not-enrolled { 
            background: linear-gradient(135deg, #fee2e4 0%, #fecdd3 100%);
            color: #7f1d1d; 
            border: 2px solid #fca5a5;
        }
        .status-badge.not-enrolled::before { background-color: #dc2626; }
        .action-links { 
            display: flex; 
            gap: 1.75rem;
            align-items: center;
            justify-content: flex-start;
        }
        .action-links a { 
            color: var(--primary); 
            font-weight: 700; 
            text-decoration: none; 
            transition: all 0.3s ease; 
            position: relative; 
            font-size: 0.9rem;
            padding: 8px 14px;
            border-radius: 6px;
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .action-links a:hover { 
            color: white;
            background: var(--primary);
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(128, 0, 0, 0.3);
            transform: translateY(-2px);
            border-color: var(--primary-dark);
        }
        .action-links a:active {
            transform: translateY(0);
        }

        /* Modal Styles */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; display: none; justify-content: center; align-items: flex-start; animation: fadeIn 0.3s ease; padding: 40px 20px; }
        .modal-content { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 90%; max-width: 550px; transform: scale(0.95); animation: scaleUp 0.3s ease forwards; max-height: 85vh; overflow: hidden; display: flex; flex-direction: column; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        .modal-header h3 { font-size: 1.5rem; color: var(--primary); }
        .close-button { font-size: 2rem; color: var(--text-light); cursor: pointer; background: none; border: none; line-height: 1; }
        .modal-content form { display: flex; flex-direction: column; min-height: 0; flex: 1; }
        .modal-body .form-group { margin-bottom: 1rem; }
        .modal-body .form-row { display: flex; gap: 1rem; }
        .modal-body .form-row .form-group { flex: 1; min-width: 0; }
        .modal-body .form-grid { display: grid; grid-template-columns: repeat(2, minmax(200px, 1fr)); gap: 1rem; }
        .modal-body .form-grid .full-width { grid-column: span 2; }
        .modal-body label {
            display: flex;
            align-items: flex-end;
            height: 2.6rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text);
            line-height: 1.2;
            overflow-wrap: anywhere;
        }
        .modal-body .form-grid .form-group { display: flex; flex-direction: column; }
        .modal-body { flex: 1; min-height: 0; overflow-y: auto; display: flex; flex-direction: column; padding: 1rem 0; }
        .modal-body input, .modal-body select { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 1rem; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee; }
        .btn-secondary { background: #f1f1f1; color: var(--text); border: 1px solid #ddd; }
        .btn-secondary:hover { background: #e7e7e7; }
        .modal-overlay.show { display: flex; }

        .view-details { min-height: auto; }
        .view-details ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .view-details li { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.95rem; }
        .view-details li span:first-child { font-weight: 600; color: var(--text-light); min-width: 140px; }
        .view-details li span:last-child { color: var(--text); flex: 1; text-align: left; word-break: break-word; }

        /* Multi-step form styles */
        .step-indicator { display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
        .step { flex: 1; text-align: center; color: var(--text-light); padding: 0.5rem 0; display: flex; flex-direction: column; align-items: center; }
        .step-number { background: #eee; color: var(--text-light); border-radius: 50%; width: 36px; height: 36px; line-height: 36px; display: inline-block; font-weight: 600; margin-bottom: 0.25rem; }
        .step-title { font-size: 0.8rem; }
        .step.active .step-number { background: var(--primary); color: white; }
        .step.active .step-title { color: var(--primary); font-weight: 600; }
        .form-step { display: none; }
        .form-step.active { display: flex; flex-direction: column; gap: 1rem; animation: fadeIn 0.5s; }


        /* Animations */
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes scaleUp {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        @media (max-width: 1024px) {
            .container { flex-direction: column; }
            .sidebar { position: static; width: 100%; height: auto; box-shadow: none; margin-bottom: 1.5rem; }
            .main-content { padding: 1.5rem; }
        }

        @media (max-width: 600px) {
            .modal-body .form-row { flex-direction: column; }
            .modal-body .form-grid { grid-template-columns: 1fr; }
            .modal-body .form-grid .full-width { grid-column: span 1; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="header">
        <h1>MNCHS Grade Portal</h1>
        <div class="user-info">
            <a href="#" class="notification-bell">
                <i class="fas fa-bell"></i>
                <span class="notification-badge"></span>
            </a>
            <span>Welcome, Teacher</span>
        </div>
    </header>

    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo-container"><img src="../../assets/images/logo.png" alt="MNCHS Logo" class="sidebar-logo"></div>
            <ul>
                <li><a href="teacherdashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="teacherstudent.php" class="active"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="teachermyclasses.php"><i class="fas fa-chalkboard"></i> My Classes</a></li>
                <li><a href="teachergradeentry.php"><i class="fas fa-edit"></i> Grade Entry</a></li>
                <li><a href="teachervaluesentry.php"><i class="fas fa-star"></i> Values Entry</a></li>
                <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h2>Manage Students</h2>
                </div>
                <button class="btn-primary" id="openAddStudentButton">
                    <i class="fas fa-plus"></i>
                    Add Student
                </button>
            </div>

            <!-- Student Table -->
            <div class="content-box">
                <div class="filters">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search by name or LRN...">
                    </div>
                    <select id="gradeFilter">
                        <option value="">Filter by Grade</option>
                        <option>Grade 7</option>
                        <option>Grade 8</option>
                        <option>Grade 9</option>
                        <option>Grade 10</option>
                        <option>Grade 11</option>
                        <option>Grade 12</option>
                    </select>
                    <select id="statusFilter">
                        <option value="">Filter by Status</option>
                        <option value="Enrolled">Enrolled</option>
                        <option value="Not Enrolled">Not Enrolled</option>
                    </select>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>LRN</th>
                                <th>Name</th>
                                <th>Grade & Section</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                            <?php if ($studentQueryError): ?>
                                <tr>
                                    <td colspan="5">Unable to load students: <?= htmlspecialchars($studentQueryError) ?></td>
                                </tr>
                            <?php elseif (empty($students)): ?>
                                <tr>
                                    <td colspan="5">No students found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $student): ?>
                                    <?php
                                        [$statusLabel, $statusClass] = determine_student_status($student);

                                        $rawName = isset($student['student_name']) ? trim((string) $student['student_name']) : '';
                                        $firstName = isset($student['first_name']) ? trim((string) $student['first_name']) : '';
                                        $lastName = isset($student['last_name']) ? trim((string) $student['last_name']) : '';

                                        if ($rawName === '' && ($firstName !== '' || $lastName !== '')) {
                                            $rawName = trim($firstName . ' ' . $lastName);
                                        }

                                        if ($rawName === '' && !empty($student['username'])) {
                                            $rawName = trim((string) $student['username']);
                                        }

                                        $displayName = $rawName !== '' ? $rawName : 'Unnamed Student';

                                        $studentNumber = isset($student['student_id']) ? trim((string) $student['student_id']) : '';
                                        $rawLrn = isset($student['lrn']) ? trim((string) $student['lrn']) : '';
                                        $displayLrn = $rawLrn !== '' ? $rawLrn : ($studentNumber !== '' ? $studentNumber : 'N/A');

                                        $gradeSection = format_grade_section($student['grade_level'] ?? '', $student['section'] ?? '');
                                    ?>
                                    <tr
                                        data-student-id="<?= htmlspecialchars((string)($student['id'] ?? '')) ?>"
                                        data-student-number="<?= htmlspecialchars($studentNumber) ?>"
                                        data-student-lrn="<?= htmlspecialchars($rawLrn) ?>"
                                        data-student-name="<?= htmlspecialchars($displayName) ?>"
                                        data-student-first-name="<?= htmlspecialchars($firstName) ?>"
                                        data-student-last-name="<?= htmlspecialchars($lastName) ?>"
                                        data-student-grade="<?= htmlspecialchars((string)($student['grade_level'] ?? '')) ?>"
                                        data-student-section="<?= htmlspecialchars($student['section'] ?? '') ?>"
                                        data-student-status="<?= htmlspecialchars($statusLabel) ?>"
                                        data-student-email="<?= htmlspecialchars($student['email'] ?? '') ?>"
                                        data-student-username="<?= htmlspecialchars($student['username'] ?? '') ?>"
                                        data-student-address="<?= htmlspecialchars($student['address'] ?? '') ?>"
                                        data-student-guardian="<?= htmlspecialchars($student['guardian_contact'] ?? '') ?>"
                                        data-student-dob="<?= htmlspecialchars($student['date_of_birth'] ?? '') ?>"
                                        data-student-enrolled="<?= htmlspecialchars($student['enrollment_date'] ?? '') ?>"
                                    >
                                        <td><?= htmlspecialchars($displayLrn) ?></td>
                                        <td><?= htmlspecialchars($displayName) ?></td>
                                        <td><?= htmlspecialchars($gradeSection) ?></td>
                                        <td><span class="status-badge <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                                        <td class="action-links">
                                            <a href="#" data-action="view">View</a>
                                            <a href="#" data-action="edit">Edit</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Student</h3>
                <button class="close-button">&times;</button>
            </div>
            <form id="addStudentForm">
                <div class="step-indicator">
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-title">Personal</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-title">Address</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-title">Parents</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-title">Others</div>
                    </div>
                </div>
                <div class="modal-body">
                    <!-- Step 1: Personal Information -->
                    <div class="form-step active" data-step="1">
                        <h4>Step 1: Personal Information</h4>
                        <div class="form-group">
                            <label for="studentLRN">LRN (Learner Reference Number)</label>
                            <input type="text" id="studentLRN" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="studentFirstName">First Name</label>
                                <input type="text" id="studentFirstName" required>
                            </div>
                            <div class="form-group">
                                <label for="studentLastName">Last Name</label>
                                <input type="text" id="studentLastName" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="studentMiddleName">Middle Name</label>
                                <input type="text" id="studentMiddleName" placeholder="Optional">
                            </div>
                            <div class="form-group">
                                <label for="studentSuffix">Suffix</label>
                                <input type="text" id="studentSuffix" placeholder="e.g. Jr.">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="studentEmail">Email Address</label>
                                <input type="email" id="studentEmail" required />
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Address -->
                    <div class="form-step" data-step="2">
                        <h4>Step 2: Address</h4>
                        <div class="form-group">
                            <label for="streetAddress">Street Address</label>
                            <input type="text" id="streetAddress" placeholder="House No., Street Name, Brgy.">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City/Municipality</label>
                                <input type="text" id="city">
                            </div>
                            <div class="form-group">
                                <label for="province">Province</label>
                                <input type="text" id="province">
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Parents Information -->
                    <div class="form-step" data-step="3">
                        <h4>Step 3: Parents/Guardian Information</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fatherName">Father's Name</label>
                                <input type="text" id="fatherName" placeholder="Full name">
                            </div>
                            <div class="form-group">
                                <label for="motherName">Mother's Name</label>
                                <input type="text" id="motherName" placeholder="Full name">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="guardianName">Guardian Name</label>
                                <input type="text" id="guardianName" placeholder="If different from parents">
                            </div>
                            <div class="form-group">
                                <label for="guardianRelationship">Relationship</label>
                                <input type="text" id="guardianRelationship" placeholder="e.g. Aunt, Uncle">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="studentGuardian">Guardian Contact</label>
                                <input type="tel" id="studentGuardian" placeholder="e.g. 09123456789">
                            </div>
                            <div class="form-group">
                                <label for="emergencyContact">Emergency Contact</label>
                                <input type="tel" id="emergencyContact" placeholder="e.g. 09123456789">
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Others -->
                    <div class="form-step" data-step="4">
                        <h4>Step 4: Other Information</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="studentGradeLevel">Grade Level</label>
                                <select id="studentGradeLevel" required>
                                    <option value="">Select grade</option>
                                    <?php foreach (array_keys($gradeSections) as $grade): ?>
                                        <option value="<?= htmlspecialchars((string) $grade) ?>">Grade <?= htmlspecialchars((string) $grade) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="studentSection">Section</label>
                                <select id="studentSection" required>
                                    <option value="">Select section</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" id="studentGradeSection" />
                        <div class="form-row">
                            <div class="form-group">
                                <label for="studentStatus">Enrollment Status</label>
                                <select id="studentStatus">
                                    <option value="Enrolled">Enrolled</option>
                                    <option value="Not Enrolled">Not Enrolled</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="studentDateEnrolled">Date Enrolled</label>
                                <input type="date" id="studentDateEnrolled">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-primary btn-secondary" id="cancelButton">Cancel</button>
                    <button type="button" class="btn-primary btn-secondary" id="prevButton" style="display: none;">Previous</button>
                    <button type="button" class="btn-primary" id="nextButton">Next</button>
                    <button type="submit" class="btn-primary" id="saveButton" style="display: none;">Save Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Student Modal -->
    <div id="viewStudentModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Student Details</h3>
                <button class="close-button" data-close="view">&times;</button>
            </div>
            <div class="modal-body view-details">
                <ul>
                    <li><span>LRN</span><span id="viewStudentLrn">N/A</span></li>
                    <li><span>Student Number</span><span id="viewStudentNumber">N/A</span></li>
                    <li><span>Name</span><span id="viewStudentName">N/A</span></li>
                    <li><span>Grade Level</span><span id="viewStudentGrade">N/A</span></li>
                    <li><span>Section</span><span id="viewStudentSection">N/A</span></li>
                    <li><span>Status</span><span id="viewStudentStatus">N/A</span></li>
                    <li><span>Email</span><span id="viewStudentEmail">N/A</span></li>
                    <li><span>Username</span><span id="viewStudentUsername">N/A</span></li>
                    <li><span>Guardian Contact</span><span id="viewStudentGuardian">N/A</span></li>
                    <li><span>Address</span><span id="viewStudentAddress">N/A</span></li>
                    <li><span>Date of Birth</span><span id="viewStudentDob">N/A</span></li>
                    <li><span>Enrollment Date</span><span id="viewStudentEnrollment">N/A</span></li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-primary btn-secondary" data-close="view">Close</button>
            </div>
        </div>
    </div>

    <!-- Container for the logout modal -->
    <div id="logout-modal-container"></div>

    <!-- Shared notification logic -->
    <script src="../../assets/js/NotificationManager.js"></script>

    <script>
        window.__GRADE_SECTIONS__ = <?= json_encode($gradeSections, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>

    <!-- Link to the external JavaScript file -->
    <script src="../../assets/js/adminstudents.js"></script>
</body>
</html>
