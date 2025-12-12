<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

require_once '../../includes/config.php';

function determine_teacher_status(array $teacher): array {
    $isAdviser = isset($teacher['is_adviser']) ? (int) $teacher['is_adviser'] : 0;
    $adviserClass = isset($teacher['class_name']) ? trim((string)$teacher['class_name']) : '';
    
    // All teachers are subject teachers by default
    // If is_adviser = 1, they are BOTH adviser and subject teacher
    if ($isAdviser === 1 && $adviserClass !== '') {
        return ['Adviser (' . $adviserClass . ') & Subject Teacher', 'adviser'];
    } elseif ($isAdviser === 1) {
        return ['Adviser & Subject Teacher', 'adviser'];
    }
    return ['Subject Teacher', 'subject-teacher'];
}

function build_teacher_name(array $teacher): string {
    $first = isset($teacher['first_name']) ? trim((string) $teacher['first_name']) : '';
    $last = isset($teacher['last_name']) ? trim((string) $teacher['last_name']) : '';
    $fullName = trim($first . ' ' . $last);
    if ($fullName === '' && !empty($teacher['username'])) {
        $fullName = trim((string) $teacher['username']);
    }
    return $fullName !== '' ? $fullName : 'Unnamed Teacher';
}

function grade_level_label(int $level): string {
    return 'Grade ' . $level;
}

function fetch_teacher_grade_sections(mysqli $conn): array {
    $sections = [];
    $sql = <<<SQL
SELECT
    teacher_id,
    grade_level,
    GROUP_CONCAT(
        DISTINCT CASE
            WHEN section IS NULL OR TRIM(section) = '' THEN NULL
            ELSE TRIM(section)
        END
        ORDER BY section SEPARATOR ', '
    ) AS sections
FROM classes
WHERE teacher_id IS NOT NULL
  AND grade_level BETWEEN 7 AND 12
GROUP BY teacher_id, grade_level
SQL;

    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $teacherId = isset($row['teacher_id']) ? (int) $row['teacher_id'] : 0;
            $gradeLevel = isset($row['grade_level']) ? (int) $row['grade_level'] : null;
            if ($teacherId <= 0 || $gradeLevel === null) {
                continue;
            }
            $key = $teacherId . ':' . $gradeLevel;
            $sections[$key] = isset($row['sections']) ? (string) $row['sections'] : '';
        }
        $result->free();
    }

    return $sections;
}

$teachers = [];
$teacherGradeRows = [];
$teacherQueryError = null;

try {
    $query = "SELECT 
                t.id,
                t.teacher_id,
                t.department,
                t.specialization,
                t.hire_date,
                COALESCE(t.is_adviser, 0) as is_adviser,
                t.adviser_class_id,
                c.class_name,
                u.email,
                u.username,
                u.first_name,
                u.last_name,
                u.is_active
            FROM teachers t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN classes c ON t.adviser_class_id = c.id
            ORDER BY t.teacher_id ASC";

    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $teachers[] = $row;
        }
        $result->free();
    } else {
        $teacherQueryError = $conn->error;
    }

    if ($teacherQueryError === null) {
        $sectionsByTeacherGrade = fetch_teacher_grade_sections($conn);
        $gradeLevels = [7, 8, 9, 10, 11, 12];

        foreach ($teachers as $teacher) {
            $teacherId = isset($teacher['id']) ? (int) $teacher['id'] : 0;
            if ($teacherId <= 0) {
                continue;
            }

            foreach ($gradeLevels as $gradeLevel) {
                $gradeLabel = grade_level_label($gradeLevel);
                $sectionsKey = $teacherId . ':' . $gradeLevel;
                $sectionsRaw = $sectionsByTeacherGrade[$sectionsKey] ?? '';
                $sectionsDisplay = $sectionsRaw !== '' ? $sectionsRaw : 'Not Assigned';
                $schoolLevel = $gradeLevel <= 10 ? 'Junior High School' : 'Senior High School';

                $teacherGradeRows[] = array_merge($teacher, [
                    'grade_level' => $gradeLevel,
                    'grade_level_label' => $gradeLabel,
                    'sections_raw' => $sectionsRaw,
                    'sections_display' => $sectionsDisplay,
                    'school_level' => $schoolLevel,
                ]);
            }
        }
    }
} catch (Throwable $e) {
    $teacherQueryError = $e->getMessage();
    $teacherGradeRows = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Teachers | MNCHS Grade Portal</title>
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
        th, td { padding: 0.875rem 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; font-size: 1rem; }
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
            padding: 6px 12px; 
            border-radius: 20px; 
            font-weight: 700; 
            font-size: 0.75rem;
            text-align: center;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            letter-spacing: 0.2px;
        }
        .status-badge::before {
            content: '';
            display: inline-block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .status-badge i {
            margin-right: 4px;
            font-size: 0.8rem;
        }
        .status-badge.adviser { 
            background: linear-gradient(135deg, #dcfce7 0%, #c8f7d8 100%);
            color: #145a32; 
            border: 2px solid #82d9a8;
        }
        .status-badge.adviser::before { background-color: #27ae60; }
        .status-badge.subject-teacher { 
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #0c4a6e; 
            border: 2px solid #3b82f6;
        }
        .status-badge.subject-teacher::before { background-color: #2563eb; }
        .action-links { 
            display: flex; 
            gap: 1rem;
            align-items: center;
            justify-content: flex-start;
        }
        .action-links a { 
            color: var(--primary); 
            font-weight: 700; 
            text-decoration: none; 
            transition: all 0.3s ease; 
            position: relative; 
            font-size: 0.85rem;
            padding: 6px 10px;
            border-radius: 4px;
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 3px;
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

        /* Table Cell Styling */
        tbody td:first-child { 
            color: #1f2937; 
            letter-spacing: 0.3px;
            font-size: 1.05rem;
        }
        tbody td:nth-child(2) { 
            color: #2d3436;
            font-size: 1.02rem;
        }
        .teacher-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .teacher-name {
            font-weight: 600;
            color: #2d3436;
        }
        .teacher-department {
            font-size: 0.75rem;
            color: #9ca3af;
            font-weight: 400;
        }
        tbody td:nth-child(3) { 
            color: #6b7280; 
            font-size: 0.95rem;
        }
        .grade-badge {
            display: inline-block;
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8rem;
            text-align: center;
            min-width: auto;
        }
        .grade-badge.jhs {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #3730a3;
            border: 1px solid #a5b4fc;
        }
        .grade-badge.shs {
            background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
            color: #831843;
            border: 1px solid #f472b6;
        }
        .sections-container {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .section-badge {
            display: inline-block;
            padding: 0.35rem 0.55rem;
            border-radius: 16px;
            background: #f3f4f6;
            color: #4b5563;
            font-weight: 700;
            font-size: 0.75rem;
            border: 1px solid #d1d5db;
        }
        tbody td:nth-child(4),
        tbody td:nth-child(5) {
            color: #4b5563;
            font-weight: 500;
        }
        tbody td:nth-child(6) { 
            text-align: center;
            padding: 1rem 1.25rem;
        }
        tbody td:nth-child(7) { 
            text-align: left;
        }

        /* Modal Styles */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; display: none; justify-content: center; align-items: center; animation: fadeIn 0.3s ease; }
        .modal-content { background: white; padding: 2.5rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 520px; transform: scale(0.95); animation: scaleUp 0.3s ease forwards; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1.25rem; }
        .modal-header h3 { font-size: 1.75rem; color: var(--primary); }
        .close-button { font-size: 2rem; color: var(--text-light); cursor: pointer; background: none; border: none; line-height: 1; }
        .modal-body { min-height: 360px; display: flex; flex-direction: column; }
        .modal-body .form-group { margin-bottom: 1rem; }
        .modal-body .form-row { display: flex; gap: 1rem; }
        .modal-body .form-row .form-group { flex: 1; }
        .modal-body label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text); font-size: 1.05rem; }
        .modal-body input, .modal-body select { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 1.05rem; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; }
        .btn-secondary { background: #f1f1f1; color: var(--text); border: 1px solid #ddd; }
        .btn-secondary:hover { background: #e7e7e7; }
        .modal-overlay.show { display: flex; }

        .view-details { min-height: auto; }
        .view-details ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem; }
        .view-details li { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.95rem; }
        .view-details li span:first-child { font-weight: 600; color: var(--text-light); min-width: 140px; }
        .view-details li span:last-child { color: var(--text); flex: 1; text-align: left; word-break: break-word; }

        /* Multi-step form styles */
        .step-indicator { display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
        .step { flex: 1; text-align: center; color: var(--text-light); padding: 0.5rem 0; display: flex; flex-direction: column; align-items: center; }
        .step-number { background: #eee; color: var(--text-light); border-radius: 50%; width: 40px; height: 40px; line-height: 40px; display: inline-block; font-weight: 600; margin-bottom: 0.25rem; font-size: 1.05rem; }
        .step-title { font-size: 1.05rem; }
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
            <span>Welcome, Admin</span>
        </div>
    </header>

    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo-container"><img src="../../assets/images/logo.png" alt="MNCHS Logo" class="sidebar-logo"></div>
            <ul>
                <li><a href="admindashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="adminstudents.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="adminteachers.php" class="active"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                <li><a href="adminreports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h2>Manage Teachers</h2>
                </div>
                <button id="addTeacherBtn" class="btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Teacher
                </button>
            </div>

            <!-- Teacher Table -->
            <div class="content-box">
                <div class="filters">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search by name or ID...">
                    </div>
                    <select id="gradeFilter">
                        <option value="">Filter by Grade</option>
                        <option value="7">Grade 7</option>
                        <option value="8">Grade 8</option>
                        <option value="9">Grade 9</option>
                        <option value="10">Grade 10</option>
                        <option value="11">Grade 11</option>
                        <option value="12">Grade 12</option>
                    </select>
                    <select id="statusFilter">
                        <option value="">Filter by Role</option>
                        <option value="Adviser">Adviser</option>
                        <option value="Adviser & Subject Teacher">Adviser & Subject Teacher</option>
                        <option value="Subject Teacher">Subject Teacher</option>
                    </select>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Grade & Level</th>
                                <th>Sections</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="teacherTableBody">
                            <?php if ($teacherQueryError): ?>
                                <tr>
                                    <td colspan="6">Unable to load teachers: <?= htmlspecialchars($teacherQueryError) ?></td>
                                </tr>
                            <?php elseif (empty($teacherGradeRows)): ?>
                                <tr>
                                    <td colspan="6">No teachers found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($teacherGradeRows as $teacher): ?>
                                    <?php
                                        [$statusLabel, $statusClass] = determine_teacher_status($teacher);
                                        $displayName = build_teacher_name($teacher);
                                        $employeeId = isset($teacher['teacher_id']) ? trim((string)$teacher['teacher_id']) : '';
                                        $displayEmployeeId = $employeeId !== '' ? $employeeId : 'N/A';
                                        $email = isset($teacher['email']) ? trim((string)$teacher['email']) : '';
                                        $gradeLevelValue = isset($teacher['grade_level']) ? (int)$teacher['grade_level'] : null;
                                        $gradeLevelLabel = $teacher['grade_level_label'] ?? ($gradeLevelValue !== null ? grade_level_label($gradeLevelValue) : 'Grade N/A');
                                        $sectionsRaw = $teacher['sections_raw'] ?? '';
                                        $sectionsDisplay = $teacher['sections_display'] ?? 'Not Assigned';
                                        $schoolLevel = $teacher['school_level'] ?? ($gradeLevelValue !== null ? ($gradeLevelValue <= 10 ? 'Junior High School' : 'Senior High School') : '');
                                        $hireDate = isset($teacher['hire_date']) ? trim((string)$teacher['hire_date']) : '';
                                        $department = isset($teacher['department']) ? trim((string)$teacher['department']) : '';
                                        $specialization = isset($teacher['specialization']) ? trim((string)$teacher['specialization']) : '';
                                        $adviserClassName = isset($teacher['class_name']) ? trim((string)$teacher['class_name']) : '';
                                        $displayAdviserClass = ($statusLabel === 'Adviser' && $adviserClassName !== '') ? $adviserClassName : 'N/A';
                                    ?>
                                    <tr
                                        data-teacher-id="<?= htmlspecialchars((string)($teacher['id'] ?? '')) ?>"
                                        data-teacher-employee-id="<?= htmlspecialchars($employeeId) ?>"
                                        data-teacher-name="<?= htmlspecialchars($displayName) ?>"
                                        data-teacher-first-name="<?= htmlspecialchars($teacher['first_name'] ?? '') ?>"
                                        data-teacher-middle-name="<?= htmlspecialchars($teacher['middle_name'] ?? '') ?>"
                                        data-teacher-last-name="<?= htmlspecialchars($teacher['last_name'] ?? '') ?>"
                                        data-teacher-suffix="<?= htmlspecialchars($teacher['suffix'] ?? '') ?>"
                                        data-teacher-username="<?= htmlspecialchars($teacher['username'] ?? '') ?>"
                                        data-teacher-email="<?= htmlspecialchars($email) ?>"
                                        data-teacher-phone="<?= htmlspecialchars($teacher['phone_number'] ?? '') ?>"
                                        data-teacher-department="<?= htmlspecialchars($department) ?>"
                                        data-teacher-specialization="<?= htmlspecialchars($specialization) ?>"
                                        data-teacher-grade-level="<?= htmlspecialchars((string)($gradeLevelValue ?? '')) ?>"
                                        data-teacher-grade-level-readable="<?= htmlspecialchars($gradeLevelLabel) ?>"
                                        data-teacher-sections="<?= htmlspecialchars($sectionsRaw) ?>"
                                        data-teacher-sections-readable="<?= htmlspecialchars($sectionsDisplay) ?>"
                                        data-teacher-school-level="<?= htmlspecialchars($schoolLevel) ?>"
                                        data-teacher-hire-date="<?= htmlspecialchars($hireDate) ?>"
                                        data-teacher-status="<?= htmlspecialchars($statusLabel) ?>"
                                        data-teacher-adviser-class="<?= htmlspecialchars($adviserClassName) ?>"
                                        data-teacher-is-adviser="<?= htmlspecialchars((string)($teacher['is_adviser'] ?? 0)) ?>"
                                    >
                                        <td><?= htmlspecialchars($displayEmployeeId) ?></td>
                                        <td>
                                            <div class="teacher-info">
                                                <div class="teacher-name"><?= htmlspecialchars($displayName) ?></div>
                                                <?php if (!empty($department)): ?>
                                                    <div class="teacher-department"><?= htmlspecialchars($department) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                                $gradeClass = $gradeLevelValue !== null && $gradeLevelValue <= 10 ? 'jhs' : 'shs';
                                                $gradeLabel = $gradeLevelValue !== null && $gradeLevelValue <= 10 ? 'JHS' : 'SHS';
                                            ?>
                                            <span class="grade-badge <?= htmlspecialchars($gradeClass) ?>">
                                                <?= htmlspecialchars($gradeLabel) ?> <?= htmlspecialchars($gradeLevelValue) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="sections-container">
                                                <?php
                                                    $sections = array_filter(array_map('trim', explode(',', $sectionsDisplay)));
                                                    if (empty($sections)) {
                                                        echo '<span class="section-badge">—</span>';
                                                    } else {
                                                        foreach ($sections as $section) {
                                                            echo '<span class="section-badge">' . htmlspecialchars($section) . '</span>';
                                                        }
                                                    }
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                                if (strpos($statusLabel, 'Adviser') !== false && strpos($statusLabel, 'Subject') !== false) {
                                                    echo '<span class="status-badge adviser" title="' . htmlspecialchars($statusLabel) . '">';
                                                    echo '<i class="fas fa-user-tie"></i>';
                                                    echo 'Adviser';
                                                    echo '</span>';
                                                } elseif (strpos($statusLabel, 'Adviser') !== false) {
                                                    echo '<span class="status-badge adviser" title="' . htmlspecialchars($statusLabel) . '">';
                                                    echo '<i class="fas fa-user-tie"></i>';
                                                    echo 'Adviser';
                                                    echo '</span>';
                                                } else {
                                                    echo '<span class="status-badge subject-teacher" title="Subject Teacher">';
                                                    echo '<i class="fas fa-book"></i>';
                                                    echo 'Teacher';
                                                    echo '</span>';
                                                }
                                            ?>
                                        </td>
                                        <td class="action-links">
                                            <a href="#" data-action="view" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="#" data-action="edit" title="Edit"><i class="fas fa-edit"></i></a>
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

    <!-- Add Teacher Modal -->
    <div id="addTeacherModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Teacher</h3>
                <button class="close-button">&times;</button>
            </div>
            <form id="addTeacherForm">
                <div class="step-indicator">
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-title">Personal</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-title">Contact</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-title">Assignment</div>
                    </div>
                </div>
                <div class="modal-body">
                    <!-- Step 1: Personal Information -->
                    <div class="form-step active" data-step="1">
                        <h4>Step 1: Personal Information</h4>
                        <div class="form-group">
                            <label for="teacherEmployeeID">Employee ID</label>
                            <input type="text" id="teacherEmployeeID" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="teacherFirstName">First Name</label>
                                <input type="text" id="teacherFirstName" required>
                            </div>
                            <div class="form-group">
                                <label for="teacherLastName">Last Name</label>
                                <input type="text" id="teacherLastName" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="teacherMiddleName">Middle Name</label>
                                <input type="text" id="teacherMiddleName" placeholder="Optional">
                            </div>
                            <div class="form-group">
                                <label for="teacherSuffix">Suffix</label>
                                <input type="text" id="teacherSuffix" placeholder="e.g. Jr.">
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Contact Information -->
                    <div class="form-step" data-step="2">
                        <h4>Step 2: Contact Information</h4>
                        <div class="form-group">
                            <label for="teacherEmail">Email Address</label>
                            <input type="email" id="teacherEmail" required>
                        </div>
                        <div class="form-group">
                            <label for="teacherPhone">Phone Number</label>
                            <input type="tel" id="teacherPhone" placeholder="e.g. 09123456789">
                        </div>
                    </div>

                    <!-- Step 3: Assignment Details -->
                    <div class="form-step" data-step="3">
                        <h4>Step 3: Assignment Details</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="teacherSchoolLevel">School Level</label>
                                <select id="teacherSchoolLevel">
                                    <option value="">Select school level</option>
                                    <option value="Junior High School">Junior High School</option>
                                    <option value="Senior High School">Senior High School</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="teacherDepartment">Department</label>
                                <select id="teacherDepartment">
                                    <option value="">Select department</option>
                                    <option value="Junior High">Junior High</option>
                                    <option value="Senior High">Senior High</option>
                                    <option value="Science">Science</option>
                                    <option value="Mathematics">Mathematics</option>
                                    <option value="Languages">Languages</option>
                                    <option value="MAPEH">MAPEH</option>
                                    <option value="TLE">TLE</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="teacherSpecialization">Specialization</label>
                                <select id="teacherSpecialization">
                                    <option value="">Select specialization</option>
                                    <option value="English">English</option>
                                    <option value="Filipino">Filipino</option>
                                    <option value="Mathematics">Mathematics</option>
                                    <option value="Science">Science</option>
                                    <option value="Social Studies">Social Studies</option>
                                    <option value="MAPEH">MAPEH</option>
                                    <option value="TLE">TLE</option>
                                    <option value="ICT">ICT</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="teacherGradeLevel">Grade Level</label>
                                <select id="teacherGradeLevel">
                                    <option value="">Select grade level</option>
                                    <option value="7">Grade 7</option>
                                    <option value="8">Grade 8</option>
                                    <option value="9">Grade 9</option>
                                    <option value="10">Grade 10</option>
                                    <option value="11">Grade 11</option>
                                    <option value="12">Grade 12</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="teacherStatus">Role</label>
                                <select id="teacherStatus" required>
                                    <option value="subject-teacher">Subject Teacher</option>
                                    <option value="adviser">Adviser (Class Adviser)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="teacherAdviserClass">Adviser Class (if Adviser)</label>
                                <select id="teacherAdviserClass">
                                    <option value="">Not an Adviser</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-primary btn-secondary" id="cancelButton">Cancel</button>
                    <button type="button" class="btn-primary btn-secondary" id="prevButton" style="display: none;">Previous</button>
                    <button type="button" class="btn-primary" id="nextButton">Next</button>
                    <button type="submit" class="btn-primary" id="saveButton" style="display: none;">Save Teacher</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Teacher Modal -->
    <div id="viewTeacherModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Teacher Details</h3>
                <button class="close-button" data-close="view">&times;</button>
            </div>
            <div class="modal-body view-details">
                <ul>
                    <li><span>Employee ID</span><span id="viewTeacherEmployeeId">N/A</span></li>
                    <li><span>Name</span><span id="viewTeacherName">N/A</span></li>
                    <li><span>Email</span><span id="viewTeacherEmail">N/A</span></li>
                    <li><span>Username</span><span id="viewTeacherUsername">N/A</span></li>
                    <li><span>School Level</span><span id="viewTeacherSchoolLevel">N/A</span></li>
                    <li><span>Department</span><span id="viewTeacherDepartment">N/A</span></li>
                    <li><span>Specialization</span><span id="viewTeacherSpecialization">N/A</span></li>
                    <li><span>Grade Level</span><span id="viewTeacherGradeLevel">N/A</span></li>
                    <li><span>Role</span><span id="viewTeacherStatus">N/A</span></li>
                    <li><span>Adviser Class</span><span id="viewTeacherAdviserClass">N/A</span></li>
                    <li><span>Hire Date</span><span id="viewTeacherHireDate">N/A</span></li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-primary btn-secondary" data-close="view">Close</button>
            </div>
        </div>
    </div>

    <!-- Container for the logout modal -->
    <div id="logout-modal-container"></div>

    <!-- Shared admin notification logic -->
    <script src="../../assets/js/NotificationManager.js"></script>

    <!-- Link to the external JavaScript file -->
    <script src="../../assets/js/adminteachers.js"></script>
</body>
</html>