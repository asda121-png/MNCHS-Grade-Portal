<?php
// admin/classes.php - NO DATABASE
session_start();

$_SESSION['role'] = 'admin';
$_SESSION['full_name'] = 'School Administrator';

// Mock data with added 'capacity' for the progress bar visual
$classes = [
    ['id'=>1, 'subject'=>'Mathematics', 'code'=>'MATH101', 'teacher'=>'Mary Johnson', 'section'=>'A', 'room'=>'101', 'schedule'=>'MWF 8:00-9:00 AM', 'students'=>32, 'capacity'=>40, 'color'=>'blue'],
    ['id'=>2, 'subject'=>'Science', 'code'=>'SCI102', 'teacher'=>'John Smith', 'section'=>'B', 'room'=>'Lab 1', 'schedule'=>'TTh 9:30-11:00 AM', 'students'=>28, 'capacity'=>30, 'color'=>'gold'],
    ['id'=>3, 'subject'=>'English', 'code'=>'ENG201', 'teacher'=>'Emma Brown', 'section'=>'A', 'room'=>'205', 'schedule'=>'MWF 10:00-11:00 AM', 'students'=>30, 'capacity'=>40, 'color'=>'red'],
    ['id'=>4, 'subject'=>'History', 'code'=>'HIST101', 'teacher'=>'Mary Johnson', 'section'=>'C', 'room'=>'108', 'schedule'=>'TTh 1:00-2:30 PM', 'students'=>15, 'capacity'=>40, 'color'=>'orange'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Classes - MNCHS Grade Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            /* PALETTE */
            --mn-red: #E0322B;
            --mn-white: #FFFFFF;
            --mn-black: #000000;
            --mn-gold: #F6D64A;
            --mn-blue: #0095D9;
            --mn-flame-red: #E2241C;
            --mn-flame-orange: #F39822;
            
            --sidebar-width: 280px;
            --bg-body: #F4F6F9;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-body);
            color: var(--mn-black);
            overflow-x: hidden;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(160deg, var(--mn-red) 0%, var(--mn-flame-red) 100%);
            padding: 2rem 1.5rem;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(224, 50, 43, 0.2);
            display: flex;
            flex-direction: column;
        }

        .sidebar .brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--mn-white);
            margin-bottom: 3rem;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .sidebar .brand i { color: var(--mn-gold); }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            font-weight: 500;
            padding: 1rem 1.2rem;
            margin-bottom: 0.8rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link i { margin-right: 12px; font-size: 1.1rem; }

        .sidebar .nav-link:hover {
            color: var(--mn-white);
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: var(--mn-white);
            color: var(--mn-red);
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link.active i { color: var(--mn-flame-orange); }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2.5rem 3rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title h2 { font-weight: 700; letter-spacing: -0.5px; margin-bottom: 0.2rem; }

        /* --- CREATIVE ELEMENTS --- */
        .btn-creative {
            background: linear-gradient(45deg, var(--mn-flame-red), var(--mn-flame-orange));
            border: none;
            color: white;
            padding: 0.7rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(243, 152, 34, 0.3);
            transition: all 0.3s ease;
        }
        .btn-creative:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(243, 152, 34, 0.4);
            color: white;
        }

        /* Filter Bar */
        .filter-bar {
            background: var(--mn-white);
            border-radius: 15px;
            padding: 1rem 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            margin-bottom: 2rem;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .filter-input {
            border: 1px solid #eee;
            background: #FAFAFA;
            border-radius: 10px;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
        }
        .filter-input:focus {
            background: #fff;
            border-color: var(--mn-blue);
            box-shadow: 0 0 0 3px rgba(0, 149, 217, 0.1);
            outline: none;
        }

        /* Card & Table */
        .content-card {
            background: var(--mn-white);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            border: none;
            overflow: hidden;
        }

        .table thead th {
            background: #FAFAFA;
            color: #888;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 1.2rem 2rem;
            border-bottom: 1px solid #eee;
        }
        
        .table tbody td {
            padding: 1.2rem 2rem;
            vertical-align: middle;
            border-bottom: 1px solid #f9f9f9;
        }

        /* Specific Class Elements */
        .subject-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .bg-icon-blue { background: linear-gradient(135deg, var(--mn-blue), #4AC1F6); }
        .bg-icon-gold { background: linear-gradient(135deg, var(--mn-gold), #F9E488); color: #8a6d00; }
        .bg-icon-red { background: linear-gradient(135deg, var(--mn-red), var(--mn-flame-red)); }
        .bg-icon-orange { background: linear-gradient(135deg, var(--mn-flame-orange), #FFB75E); }

        .schedule-pill {
            background: #F4F6F9;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #555;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .capacity-bar {
            height: 6px;
            width: 100px;
            background: #eee;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 5px;
        }
        
        .capacity-fill {
            height: 100%;
            border-radius: 10px;
        }

        .btn-icon {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s;
            background: white;
            color: #999;
        }
        .btn-icon:hover { background: #f0f0f0; color: var(--mn-black); transform: scale(1.1); }

        /* Modal */
        .modal-content { border-radius: 25px; border: none; }
        .modal-header { border-bottom: none; padding: 2rem 2rem 1rem; }
        .modal-body { padding: 0 2rem 2rem; }
        .modal-footer { border-top: none; padding: 0 2rem 2rem; }
        .form-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: #888; }
        .form-control, .form-select { border-radius: 10px; padding: 0.6rem 1rem; border: 1px solid #eee; }
        .form-control:focus { border-color: var(--mn-blue); box-shadow: 0 0 0 3px rgba(0,149,217,0.1); }

        @media (max-width: 992px) {
            .sidebar { width: 80px; padding: 1.5rem 0.5rem; }
            .sidebar .nav-text, .sidebar .brand span { display: none; }
            .sidebar .brand { justify-content: center; }
            .sidebar .nav-link { justify-content: center; padding: 1rem 0; } 
            .sidebar .nav-link i { margin: 0; }
            .main-content { margin-left: 80px; padding: 1.5rem; }
            .filter-bar { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>

<?php 
// Helper to get random colors for demo
function getIconClass($color) {
    return "bg-icon-" . $color;
}
?>

<nav class="sidebar">
    <a href="dashboard.php" class="brand">
        <i class="bi bi-mortarboard-fill fs-2"></i>
        <span>MNCHS</span>
    </a>
    
    <div class="d-flex flex-column h-100">
        <a href="dashboard.php" class="nav-link">
            <i class="bi bi-grid-fill"></i>
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="users.php" class="nav-link">
            <i class="bi bi-people-fill"></i>
            <span class="nav-text">Users</span>
        </a>
        <a href="classes.php" class="nav-link active">
            <i class="bi bi-journal-bookmark-fill"></i>
            <span class="nav-text">Classes</span>
        </a>
        <a href="#" class="nav-link">
            <i class="bi bi-bar-chart-line-fill"></i>
            <span class="nav-text">Reports</span>
        </a>
        
        <div class="mt-auto">
            <a href="../logout.php" class="nav-link" style="color: rgba(255,200,200,0.9);">
                <i class="bi bi-box-arrow-right"></i>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </div>
</nav>

<div class="main-content">
    <div class="page-header">
        <div class="page-title">
            <h2>Classes & Schedules</h2>
            <p class="text-muted mb-0">Manage course offerings and room assignments</p>
        </div>
        <button class="btn btn-creative" data-bs-toggle="modal" data-bs-target="#addClassModal">
            <i class="bi bi-plus-lg me-2"></i> Add Class
        </button>
    </div>

    <div class="filter-bar">
        <div class="flex-grow-1 position-relative">
            <i class="bi bi-search position-absolute text-muted" style="left: 15px; top: 12px;"></i>
            <input type="text" class="form-control filter-input" style="padding-left: 40px;" placeholder="Search subject or code...">
        </div>
        <select class="form-select filter-input" style="width: auto;">
            <option>All Teachers</option>
            <option>Mary Johnson</option>
            <option>John Smith</option>
        </select>
        <select class="form-select filter-input" style="width: auto;">
            <option>All Days</option>
            <option>MWF</option>
            <option>TTh</option>
        </select>
    </div>

    <div class="content-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Subject Information</th>
                        <th>Teacher</th>
                        <th>Schedule & Room</th>
                        <th>Capacity</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $c): 
                        // Calculate percentage for progress bar
                        $percent = ($c['students'] / $c['capacity']) * 100;
                        $progressColor = $percent > 90 ? 'var(--mn-red)' : 'var(--mn-blue)';
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="subject-icon <?= getIconClass($c['color']) ?> me-3">
                                    <?= substr($c['subject'], 0, 1) ?>
                                </div>
                                <div>
                                    <div class="fw-bold fs-6"><?= htmlspecialchars($c['subject']) ?></div>
                                    <div class="badge bg-light text-secondary border"><?= $c['code'] ?> • Sec <?= $c['section'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-2" 
                                     style="width:30px; height:30px; font-size: 0.8rem; font-weight: bold; color: #777;">
                                     <i class="bi bi-person-fill"></i>
                                </div>
                                <span class="fw-medium text-dark"><?= htmlspecialchars($c['teacher']) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <div class="schedule-pill">
                                    <i class="bi bi-clock text-primary"></i> <?= $c['schedule'] ?>
                                </div>
                                <small class="text-muted ms-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Rm. <?= $c['room'] ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold text-dark fs-6"><?= $c['students'] ?></span>
                                <span class="text-muted small">/ <?= $c['capacity'] ?></span>
                            </div>
                            <div class="capacity-bar">
                                <div class="capacity-fill" style="width: <?= $percent ?>%; background: <?= $progressColor ?>;"></div>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn-icon" title="Edit Class">
                                <i class="bi bi-pencil-fill text-primary"></i>
                            </button>
                            <button class="btn-icon" title="Delete Class">
                                <i class="bi bi-trash-fill text-danger"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add New Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Subject Name</label>
                            <input type="text" class="form-control" placeholder="e.g. Mathematics">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subject Code</label>
                            <input type="text" class="form-control" placeholder="e.g. MATH101">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assign Teacher</label>
                            <select class="form-select">
                                <option selected disabled>Select Teacher...</option>
                                <option>Mary Johnson</option>
                                <option>John Smith</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Section</label>
                            <input type="text" class="form-control" placeholder="A">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" value="40">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Schedule</label>
                            <input type="text" class="form-control" placeholder="e.g. MWF 8:00-9:00 AM">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room Assignment</label>
                            <input type="text" class="form-control" placeholder="e.g. Rm 101">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-creative px-4" onclick="alert('Class added! (Mock)')">Save Class</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>