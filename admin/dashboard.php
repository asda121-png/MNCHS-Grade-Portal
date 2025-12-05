<?php
// admin/dashboard.php - NO DATABASE
session_start();

// Force admin role (for testing)
$_SESSION['role'] = 'admin';
$_SESSION['full_name'] = 'School Administrator';
$_SESSION['email'] = 'admin@email.com';

// Mock data
$stats = [
    'total_students' => 248,
    'total_teachers' => 18,
    'total_classes'  => 42,
    'attendance_today' => 94.2
];

$users = [
    ['id' => 1, 'name' => 'Mary Johnson',   'username' => 'tmary',   'email' => 'mary@mnchs.edu',   'role' => 'teacher'],
    ['id' => 2, 'name' => 'Tom Lee',        'username' => 's67890',  'email' => 'tom@student.edu',  'role' => 'student'],
    ['id' => 3, 'name' => 'Lisa Lee',       'username' => 'p67890',  'email' => 'lisa@parent.com',  'role' => 'parent'],
    ['id' => 4, 'name' => 'John Smith',     'username' => 'jsmith',  'email' => 'john@mnchs.edu',   'role' => 'teacher'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - MNCHS Grade Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            /* YOUR PALETTE */
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

        /* --- SIDEBAR DESIGN --- */
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
            border-right: 1px solid rgba(255,255,255,0.1);
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
        
        .sidebar .brand i {
            color: var(--mn-gold);
            filter: drop-shadow(0 0 5px rgba(246, 214, 74, 0.5));
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            font-weight: 500;
            padding: 1rem 1.2rem;
            margin-bottom: 0.8rem;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
            transition: transform 0.3s;
        }

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
        
        .sidebar .nav-link.active i {
            color: var(--mn-flame-orange);
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2.5rem 3rem;
        }

        /* --- HEADER --- */
        .header-section {
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-text h2 {
            font-weight: 700;
            color: var(--mn-black);
            letter-spacing: -0.5px;
        }
        
        .welcome-text span {
            color: var(--mn-flame-red);
        }

        .clock-badge {
            background: var(--mn-white);
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            font-weight: 600;
            color: var(--mn-blue);
            border: 1px solid rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* --- STATS CARDS --- */
        .stat-card {
            background: var(--mn-white);
            border-radius: 20px;
            padding: 1.8rem;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }

        /* Specific Card Accents based on your palette */
        .card-blue::before { background: var(--mn-blue); }
        .card-gold::before { background: var(--mn-gold); }
        .card-red::before { background: var(--mn-red); }
        .card-orange::before { background: var(--mn-flame-orange); }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        }

        .stat-icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .bg-soft-blue { background: rgba(0, 149, 217, 0.1); color: var(--mn-blue); }
        .bg-soft-gold { background: rgba(246, 214, 74, 0.2); color: #d4b106; }
        .bg-soft-red { background: rgba(224, 50, 43, 0.1); color: var(--mn-red); }
        .bg-soft-orange { background: rgba(243, 152, 34, 0.1); color: var(--mn-flame-orange); }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--mn-black);
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #888;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* --- TABLE & CARD --- */
        .content-card {
            background: var(--mn-white);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            border: none;
            padding: 0;
            overflow: hidden;
        }

        .card-header-custom {
            padding: 1.5rem 2rem;
            background: var(--mn-white);
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-creative {
            background: linear-gradient(45deg, var(--mn-flame-red), var(--mn-flame-orange));
            border: none;
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(243, 152, 34, 0.3);
            transition: all 0.3s ease;
        }

        .btn-creative:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(243, 152, 34, 0.4);
            color: white;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #FAFAFA;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 1rem 2rem;
            border-bottom: 1px solid #eee;
        }

        .table tbody td {
            padding: 1.2rem 2rem;
            vertical-align: middle;
            border-bottom: 1px solid #f9f9f9;
            color: #444;
            font-weight: 500;
        }

        .table-hover tbody tr:hover {
            background-color: #fffcf5; /* Subtle hint of the gold/yellow */
        }

        .badge-custom {
            padding: 0.5em 1em;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .badge-teacher { background: rgba(0, 149, 217, 0.1); color: var(--mn-blue); }
        .badge-student { background: rgba(246, 214, 74, 0.2); color: #B69200; }
        .badge-parent  { background: rgba(226, 36, 28, 0.1); color: var(--mn-flame-red); }

        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .btn-action.edit { color: var(--mn-blue); background: rgba(0, 149, 217, 0.05); }
        .btn-action.edit:hover { background: var(--mn-blue); color: white; }
        
        .btn-action.delete { color: var(--mn-red); background: rgba(224, 50, 43, 0.05); }
        .btn-action.delete:hover { background: var(--mn-red); color: white; }

        /* --- RESPONSIVE --- */
        @media (max-width: 992px) {
            .sidebar { width: 80px; padding: 1.5rem 1rem; }
            .sidebar .nav-text, .sidebar .brand span { display: none; }
            .sidebar .brand { justify-content: center; margin-bottom: 2rem; }
            .sidebar .nav-link { justify-content: center; padding: 1rem 0; }
            .sidebar .nav-link i { margin-right: 0; font-size: 1.4rem; }
            .main-content { margin-left: 80px; padding: 1.5rem; }
        }
    </style>
</head>
<body>

<nav class="sidebar">
    <a href="#" class="brand">
        <i class="bi bi-mortarboard-fill fs-2"></i>
        <span>MNCHS</span>
    </a>
    
    <div class="d-flex flex-column h-100">
        <a href="#" class="nav-link active">
            <i class="bi bi-grid-fill"></i>
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="users.php" class="nav-link">
            <i class="bi bi-people-fill"></i>
            <span class="nav-text">Users</span>
        </a>
        <a href="classes.php" class="nav-link">
            <i class="bi bi-journal-bookmark-fill"></i>
            <span class="nav-text">Classes</span>
        </a>
        <a href="#" class="nav-link">
            <i class="bi bi-bar-chart-line-fill"></i>
            <span class="nav-text">Reports</span>
        </a>
        
        <div class="mt-auto">
            <a href="#" class="nav-link">
                <i class="bi bi-gear-fill"></i>
                <span class="nav-text">Settings</span>
            </a>
            <a href="../logout.php" class="nav-link" style="color: rgba(255,200,200,0.9);">
                <i class="bi bi-box-arrow-right"></i>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </div>
</nav>

<div class="main-content">
    
    <div class="header-section">
        <div class="welcome-text">
            <h2>Hello, <span><?= htmlspecialchars($_SESSION['full_name']) ?></span>! 👋</h2>
            <p class="text-muted mb-0">Here's what's happening at school today.</p>
        </div>
        <div class="clock-badge">
            <i class="bi bi-calendar-event text-warning"></i>
            <span id="clock-date">Loading...</span>
            <div style="width:1px; height:20px; background:#ddd; margin:0 5px;"></div>
            <span id="clock-time" style="color:var(--mn-black);">00:00</span>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card card-blue">
                <div class="stat-icon-circle bg-soft-blue">
                    <i class="bi bi-backpack"></i>
                </div>
                <div class="stat-value"><?= $stats['total_students'] ?></div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card card-gold">
                <div class="stat-icon-circle bg-soft-gold">
                    <i class="bi bi-person-video3"></i>
                </div>
                <div class="stat-value"><?= $stats['total_teachers'] ?></div>
                <div class="stat-label">Total Teachers</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card card-orange">
                <div class="stat-icon-circle bg-soft-orange">
                    <i class="bi bi-collection"></i>
                </div>
                <div class="stat-value"><?= $stats['total_classes'] ?></div>
                <div class="stat-label">Active Classes</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card card-red">
                <div class="stat-icon-circle bg-soft-red">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="stat-value"><?= $stats['attendance_today'] ?>%</div>
                <div class="stat-label">Attendance Rate</div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header-custom">
            <div>
                <h5 class="fw-bold mb-1">User Management</h5>
                <small class="text-muted">Manage system access and roles</small>
            </div>
            <button class="btn btn-creative" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-lg me-2"></i> Add New User
            </button>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Profile</th>
                        <th>Credentials</th>
                        <th>Role Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="text-muted">#<?= str_pad($user['id'], 3, '0', STR_PAD_LEFT) ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px; color: var(--mn-red); font-weight: bold; border: 1px solid #eee;">
                                    <?= substr($user['name'], 0, 1) ?>
                                </div>
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($user['name']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <code style="color: var(--mn-blue); background: rgba(0,149,217,0.05); padding: 2px 6px; border-radius: 4px;">@<?= htmlspecialchars($user['username']) ?></code>
                        </td>
                        <td>
                            <?php 
                                $badgeClass = 'badge-custom ';
                                if($user['role'] === 'teacher') $badgeClass .= 'badge-teacher';
                                elseif($user['role'] === 'student') $badgeClass .= 'badge-student';
                                else $badgeClass .= 'badge-parent';
                            ?>
                            <span class="<?= $badgeClass ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <button class="btn-action edit me-1" title="Edit">
                                <i class="bi bi-pencil-fill" style="font-size: 0.8rem;"></i>
                            </button>
                            <button class="btn-action delete" title="Delete">
                                <i class="bi bi-trash-fill" style="font-size: 0.8rem;"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Full Name</label>
                        <input type="text" class="form-control form-control-lg" placeholder="e.g. John Doe" style="font-size: 0.95rem; border-radius: 10px;">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Username</label>
                            <input type="text" class="form-control" placeholder="jdoe" style="border-radius: 10px;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Role</label>
                            <select class="form-select" style="border-radius: 10px;">
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                                <option value="parent">Parent</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Email</label>
                        <input type="email" class="form-control" placeholder="john@mnchs.edu" style="border-radius: 10px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Password</label>
                        <input type="password" class="form-control" placeholder="••••••••" style="border-radius: 10px;">
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 0.5rem 1.5rem;">Cancel</button>
                <button type="button" class="btn btn-creative ms-2">Save User</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function updateClock() {
        const now = new Date();
        const time = now.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit' });
        const date = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        
        document.getElementById('clock-date').innerText = date;
        document.getElementById('clock-time').innerText = time;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // Mock action alerts
    document.querySelectorAll('.btn-action, .btn-creative').forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Check if it's the modal trigger, ignore if so
            if(!btn.hasAttribute('data-bs-toggle')) {
                // alert('UI Demo: Action Triggered');
            }
        });
    });
</script>
</body>
</html>