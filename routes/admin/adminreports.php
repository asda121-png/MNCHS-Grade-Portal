<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../index.php'); // Redirect to login page
    exit();
}

require_once '../../includes/config.php';

$gradeLevels = [];
$sectionsByGrade = [];
$subjects = [];
$academicYears = [];

try {
    if ($gradeResult = $conn->query("SELECT DISTINCT grade_level FROM classes WHERE grade_level IS NOT NULL ORDER BY grade_level")) {
        while ($row = $gradeResult->fetch_assoc()) {
            $gradeLevels[] = (int) $row['grade_level'];
        }
        $gradeResult->free();
    }

    if ($sectionResult = $conn->query("SELECT grade_level, section FROM classes WHERE grade_level IS NOT NULL AND section IS NOT NULL AND TRIM(section) <> '' ORDER BY grade_level, section")) {
        while ($row = $sectionResult->fetch_assoc()) {
            $grade = isset($row['grade_level']) ? (int) $row['grade_level'] : null;
            $section = isset($row['section']) ? trim((string) $row['section']) : '';
            if ($grade === null || $section === '') {
                continue;
            }
            if (!isset($sectionsByGrade[$grade])) {
                $sectionsByGrade[$grade] = [];
            }
            if (!in_array($section, $sectionsByGrade[$grade], true)) {
                $sectionsByGrade[$grade][] = $section;
            }
        }
        $sectionResult->free();
    }

    if ($subjectResult = $conn->query("SELECT id, subject_name FROM subjects ORDER BY subject_name")) {
        while ($row = $subjectResult->fetch_assoc()) {
            $subjects[] = [
                'id' => isset($row['id']) ? (int) $row['id'] : null,
                'name' => isset($row['subject_name']) ? (string) $row['subject_name'] : ''
            ];
        }
        $subjectResult->free();
    }

    if ($yearResult = $conn->query("SELECT year, is_active FROM academic_years ORDER BY start_date DESC")) {
        while ($row = $yearResult->fetch_assoc()) {
            $yearLabel = isset($row['year']) ? (string) $row['year'] : '';
            if ($yearLabel === '') {
                continue;
            }
            $academicYears[] = [
                'year' => $yearLabel,
                'is_active' => isset($row['is_active']) ? (int) $row['is_active'] === 1 : false
            ];
        }
        $yearResult->free();
    }
} catch (Throwable $e) {
    error_log('Error preparing admin reports data: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports | MNCHS Grade Portal</title>
    <link rel="icon" href="../../assets/images/logo.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #800000; --primary-dark: #660000; --accent: #FFD700;
            --text: #2d3436; --text-light: #636e72; --shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); color: var(--text); }
        .header { background: linear-gradient(90deg, var(--primary), var(--primary-dark)); color: white; padding: 1.2rem 2rem; box-shadow: 0 4px 15px rgba(128, 0, 0, 0.3); position: sticky; top: 0; z-index: 1000; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.8rem; font-weight: 600; }
        .user-info { display: flex; align-items: center; gap: 25px; }
        .notification-bell { position: relative; color: white; font-size: 1.3rem; text-decoration: none; }
        .notification-badge { position: absolute; top: -5px; right: -8px; background-color: #e74c3c; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; font-weight: 700; display: none; justify-content: center; align-items: center; border: 2px solid var(--primary-dark); }
        .container { display: flex; min-height: calc(100vh - 77px); }
        .sidebar { width: 260px; background: white; padding: 2rem 1.5rem; box-shadow: 5px 0 15px rgba(0,0,0,0.05); position: sticky; top: 77px; height: calc(100vh - 77px); overflow-y: auto; }
        .sidebar-logo-container { text-align: center; margin-bottom: 2rem; }
        .sidebar-logo { max-width: 120px; height: auto; }
        .sidebar ul { list-style: none; }
        .sidebar ul li { margin-bottom: 8px; }
        .sidebar ul li a { display: flex; align-items: center; gap: 12px; padding: 14px 18px; color: var(--text); text-decoration: none; border-radius: 12px; font-weight: 500; transition: all 0.3s ease; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background: var(--primary); color: white; transform: translateX(5px); box-shadow: 0 5px 15px rgba(128, 0, 0, 0.2); }
        .sidebar ul li a i { font-size: 1.1rem; width: 20px; text-align: center; }
        .main-content { flex: 1; padding: 2.5rem; }
        .page-header { margin-bottom: 2rem; }
        .page-header h2 { font-size: 2.15rem; color: var(--primary); }
        .content-box { background: white; border-radius: 16px; box-shadow: var(--shadow); padding: 2rem; }
        .content-box h3 { font-size: 1.5rem; color: var(--text); margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 1rem; }
        .reports-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .report-card {
            background-color: #f9fafb; border: 1px solid #eee; border-radius: 12px;
            padding: 1.5rem; display: flex; flex-direction: column;
            transition: all 0.3s ease;
        }
        .report-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.07); }
        .report-card .icon { font-size: 2rem; color: var(--primary); margin-bottom: 1rem; }
        .report-card .title { font-size: 1.2rem; font-weight: 600; color: var(--text); margin-bottom: 0.5rem; }
        .report-card .description { font-size: 1rem; color: var(--text-light); flex-grow: 1; margin-bottom: 1.5rem; }
        .btn-generate {
            background: var(--primary); color: white; padding: 12px 18px; border-radius: 8px;
            text-decoration: none; font-weight: 500; display: inline-flex; align-items: center;
            gap: 8px; border: none; cursor: pointer; transition: all 0.3s ease; align-self: flex-start;
            font-size: 1.05rem;
        }
        .btn-generate:hover { background: var(--primary-dark); }

        /* Modal styling */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.55); display: none; align-items: center; justify-content: center; z-index: 2000; }
        .modal-overlay.show { display: flex; }
        .modal { background: #fff; border-radius: 16px; padding: 2rem; width: 100%; max-width: 480px; box-shadow: 0 20px 45px rgba(0,0,0,0.18); position: relative; }
        .modal h3 { font-size: 1.6rem; color: var(--primary); margin-bottom: 1.5rem; }
        .modal .close-button { position: absolute; top: 16px; right: 20px; border: none; background: transparent; font-size: 1.8rem; color: var(--text-light); cursor: pointer; }
        .modal .form-group { margin-bottom: 1rem; }
        .modal label { display: block; font-weight: 600; margin-bottom: 0.4rem; color: var(--text); }
        .modal select { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #dfe6e9; font-family: 'Poppins', sans-serif; font-size: 1rem; }
        .modal .modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }
        .modal .btn-secondary { background: #f1f2f6; color: var(--text); border: 1px solid #dcdde1; padding: 10px 18px; border-radius: 8px; cursor: pointer; font-size: 1rem; }
        .modal .btn-secondary:hover { background: #e0e3e6; }
        .modal .btn-primary { padding: 10px 20px; font-size: 1.05rem; }
        .modal .form-row { display: flex; gap: 1rem; }
        .modal .form-row .form-group { flex: 1; }
        .hidden { display: none !important; }
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
                <li><a href="adminteachers.php"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                <li><a href="adminreports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h2>Generate Reports</h2>
                    <p style="color: var(--text-light);">Create and export school data reports.</p>
                </div>
            </div>

            <!-- Report Generation Section -->
            <div class="content-box">
                <h3>Available Reports</h3>
                <div class="reports-grid">
                    <!-- Report Option 1 -->
                    <div class="report-card">
                        <i class="icon fas fa-users"></i>
                        <h4 class="title">Master List of Students</h4>
                        <p class="description">Generate a comprehensive list of all enrolled students for a selected school year.</p>
                        <button class="btn-generate" data-report-type="student-masterlist"><i class="fas fa-download"></i> Generate</button>
                    </div>
                    <!-- Report Option 2 -->
                    <div class="report-card">
                        <i class="icon fas fa-clipboard-list"></i>
                        <h4 class="title">Grades per Section</h4>
                        <p class="description">Export the final grades for all students in a specific class section and subject.</p>
                        <button class="btn-generate" data-report-type="section-grades"><i class="fas fa-download"></i> Generate</button>
                    </div>
                    <!-- Report Option 3 -->
                    <div class="report-card">
                        <i class="icon fas fa-chalkboard-teacher"></i>
                        <h4 class="title">List of Teachers</h4>
                        <p class="description">Generate a list of all active faculty members, including their contact information and department.</p>
                        <button class="btn-generate" data-report-type="teacher-list"><i class="fas fa-download"></i> Generate</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Report configuration modal -->
    <div id="reportModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="reportModalTitle">
        <div class="modal">
            <button class="close-button" type="button" aria-label="Close" id="closeReportModal">&times;</button>
            <h3 id="reportModalTitle">Configure Report</h3>
            <form id="reportForm">
                <input type="hidden" name="type" id="reportTypeInput" value="">

                <div class="form-section hidden" data-section="student-masterlist">
                    <div class="form-group">
                        <label for="studentMasterlistGrade">Grade Level (optional)</label>
                        <select id="studentMasterlistGrade" name="grade_level" data-required="false">
                            <option value="">All grade levels</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="studentMasterlistYear">Academic Year (optional)</label>
                        <select id="studentMasterlistYear" name="academic_year" data-required="false">
                            <option value="">All academic years</option>
                        </select>
                    </div>
                </div>

                <div class="form-section hidden" data-section="section-grades">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sectionGradesGrade">Grade Level</label>
                            <select id="sectionGradesGrade" name="grade_level" data-required="true">
                                <option value="">Select grade level</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="sectionGradesSection">Section</label>
                            <select id="sectionGradesSection" name="section" data-required="true">
                                <option value="">Select section</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sectionGradesSubject">Subject</label>
                            <select id="sectionGradesSubject" name="subject_id" data-required="true">
                                <option value="">Select subject</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="sectionGradesYear">Academic Year (optional)</label>
                            <select id="sectionGradesYear" name="academic_year" data-required="false">
                                <option value="">All academic years</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="cancelReportModal">Cancel</button>
                    <button type="submit" class="btn-primary">Download CSV</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Container for the logout modal -->
    <div id="logout-modal-container"></div>

    <!-- Shared admin notification logic -->
    <script src="../../assets/js/NotificationManager.js"></script>

    <script>
        window.reportOptions = <?php
            echo json_encode(
                [
                    'gradeLevels' => $gradeLevels,
                    'sectionsByGrade' => $sectionsByGrade,
                    'subjects' => $subjects,
                    'academicYears' => $academicYears
                ],
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            );
        ?>;
    </script>

    <!-- Link to the external JavaScript file -->
    <script src="../../assets/js/adminreports.js"></script>

</body>
</html>