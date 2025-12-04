<?php
// admin/users.php - NO DATABASE
session_start();

// Simulate login (for testing)
$_SESSION['role'] = 'admin';
$_SESSION['full_name'] = 'School Administrator';
$_SESSION['email'] = 'admin@email.com';

// Hard-coded users
$users = [
    ['id' => 1, 'name' => 'Mary Johnson',   'username' => 'tmary',   'email' => 'mary@mnchs.edu',     'role' => 'teacher', 'status' => 'active'],
    ['id' => 2, 'name' => 'Tom Lee',        'username' => 's67890',  'email' => 'tom@student.edu',    'role' => 'student', 'status' => 'active'],
    ['id' => 3, 'name' => 'Lisa Lee',       'username' => 'p67890',  'email' => 'lisa@parent.com',    'role' => 'parent',  'status' => 'active'],
    ['id' => 4, 'name' => 'John Smith',     'username' => 'jsmith',  'email' => 'john@mnchs.edu',     'role' => 'teacher', 'status' => 'active'],
    ['id' => 5, 'name' => 'Emma Brown',     'username' => 'ebrown',  'email' => 'emma@mnchs.edu',     'role' => 'teacher', 'status' => 'inactive'],
    ['id' => 6, 'name' => 'Alex Kim',       'username' => 's11223',  'email' => 'alex@student.edu',   'role' => 'student', 'status' => 'active'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management - MNCHS Grade Portal</title>
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

        /* --- SIDEBAR (Consistent with Dashboard) --- */
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
        .page-title p { font-size: 0.95rem; }

        /* --- CREATIVE BUTTONS --- */
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

        .btn-outline-custom {
            border: 2px solid #e0e0e0;
            color: #666;
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            background: transparent;
            transition: all 0.3s;
        }
        .btn-outline-custom:hover {
            border-color: var(--mn-blue);
            color: var(--mn-blue);
            background: rgba(0, 149, 217, 0.05);
        }

        /* --- FILTER BAR --- */
        .filter-bar {
            background: var(--mn-white);
            border-radius: 15px;
            padding: 1rem 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            margin-bottom: 2rem;
            display: flex;
            gap: 15px;
            align-items: center;
            border: 1px solid rgba(0,0,0,0.02);
        }

        .search-wrapper {
            position: relative;
            flex-grow: 1;
        }
        .search-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        .search-wrapper input {
            padding-left: 40px;
            border-radius: 10px;
            border: 1px solid #eee;
            background: #FAFAFA;
            padding-top: 0.7rem;
            padding-bottom: 0.7rem;
        }
        .search-wrapper input:focus {
            background: #fff;
            border-color: var(--mn-blue);
            box-shadow: 0 0 0 3px rgba(0, 149, 217, 0.1);
        }
        
        .filter-select {
            border-radius: 10px;
            border: 1px solid #eee;
            padding-top: 0.7rem;
            padding-bottom: 0.7rem;
            background-color: #FAFAFA;
            min-width: 150px;
            cursor: pointer;
        }

        /* --- TABLE DESIGN --- */
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
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: rgba(224, 50, 43, 0.1);
            color: var(--mn-red);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            margin-right: 15px;
        }

        .username-badge {
            font-family: monospace;
            color: var(--mn-blue);
            background: rgba(0, 149, 217, 0.05);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        /* Status Pills */
        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pill.active { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .status-pill.inactive { background: rgba(108, 117, 125, 0.1); color: #6c757d; }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .status-pill.active .status-dot { background: #28a745; box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2); }
        .status-pill.inactive .status-dot { background: #6c757d; }

        /* Role Badges */
        .role-badge {
            font-weight: 500;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
        }
        .role-teacher { background: rgba(0, 149, 217, 0.1); color: var(--mn-blue); }
        .role-student { background: rgba(246, 214, 74, 0.2); color: #b89803; }
        .role-parent  { background: rgba(226, 36, 28, 0.1); color: var(--mn-flame-red); }

        /* Actions */
        .btn-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s;
            background: #fff;
            color: #888;
        }
        .btn-icon:hover { background: #f0f0f0; color: var(--mn-black); transform: scale(1.1); }
        .btn-icon.edit:hover { color: var(--mn-blue); background: rgba(0, 149, 217, 0.1); }
        .btn-icon.delete:hover { color: var(--mn-red); background: rgba(224, 50, 43, 0.1); }

        /* Modal Customization */
        .modal-content {
            border-radius: 25px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        .modal-header { border-bottom: none; padding: 2rem 2rem 1rem; }
        .modal-footer { border-top: none; padding: 1rem 2rem 2rem; }
        .modal-body { padding: 0 2rem 1rem; }
        .form-label { font-size: 0.85rem; font-weight: 600; color: #777; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control, .form-select {
            border-radius: 12px;
            padding: 0.7rem 1rem;
            border: 1px solid #eee;
            background: #fdfdfd;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--mn-blue);
            box-shadow: 0 0 0 4px rgba(0, 149, 217, 0.1);
        }

        @media (max-width: 992px) {
            .sidebar { width: 80px; padding: 1.5rem 0.5rem; }
            .sidebar .nav-text, .sidebar .brand span { display: none; }
            .sidebar .brand { justify-content: center; }
            .sidebar .nav-link { justify-content: center; }
            .sidebar .nav-link i { margin: 0; }
            .main-content { margin-left: 80px; padding: 1.5rem; }
            .filter-bar { flex-direction: column; align-items: stretch; gap: 10px; }
        }
    </style>
</head>
<body>

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
        <a href="users.php" class="nav-link active">
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
            <h2>User Management</h2>
            <p class="text-muted mb-0">Control access for Teachers, Students, and Parents</p>
        </div>
        <div class="d-flex gap-3">
            <button class="btn btn-outline-custom" id="exportCsv">
                <i class="bi bi-download me-2"></i> Export
            </button>
            <button class="btn btn-creative" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-lg me-2"></i> Add New User
            </button>
        </div>
    </div>

    <div class="filter-bar">
        <div class="search-wrapper">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" class="form-control border-0 shadow-none" placeholder="Search user by name, email, or username...">
        </div>
        <div style="width: 1px; height: 30px; background: #eee;" class="d-none d-md-block"></div>
        <select id="roleFilter" class="form-select filter-select border-0 shadow-none">
            <option value="">All Roles</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
            <option value="parent">Parent</option>
        </select>
        <select id="statusFilter" class="form-select filter-select border-0 shadow-none">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <button class="btn btn-link text-muted text-decoration-none" id="clearFilters" title="Clear Filters">
            <i class="bi bi-x-circle-fill fs-5"></i>
        </button>
    </div>

    <div class="content-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="usersTable">
                <thead>
                    <tr>
                        <th class="ps-4">User Profile</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): 
                        // Determine Role Class
                        $roleClass = 'role-parent';
                        if($user['role'] === 'teacher') $roleClass = 'role-teacher';
                        if($user['role'] === 'student') $roleClass = 'role-student';
                    ?>
                    <tr data-role="<?= $user['role'] ?>" data-status="<?= $user['status'] ?>">
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="user-avatar">
                                    <?= substr($user['name'], 0, 1) ?>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($user['name']) ?></div>
                                    <div class="text-muted small" style="font-size: 0.8rem;"><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="username-badge">@<?= htmlspecialchars($user['username']) ?></span>
                        </td>
                        <td>
                            <span class="badge role-badge <?= $roleClass ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="status-pill <?= $user['status'] ?>">
                                <div class="status-dot"></div>
                                <?= ucfirst($user['status']) ?>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn-icon edit edit-btn" 
                                    data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                    data-id="<?= $user['id'] ?>" 
                                    data-name="<?= htmlspecialchars($user['name']) ?>" 
                                    data-username="<?= $user['username'] ?>" 
                                    data-email="<?= $user['email'] ?>" 
                                    data-role="<?= $user['role'] ?>" 
                                    data-status="<?= $user['status'] ?>">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button class="btn-icon delete delete-btn">
                                <i class="bi bi-trash-fill"></i>
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
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" placeholder="e.g. Mary Johnson" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" placeholder="mjohnson" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" placeholder="mary@mnchs.edu" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select class="form-select" required>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                                <option value="parent">Parent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Default Password</label>
                        <input type="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-creative px-4" id="saveAddUser">Create User</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email & Username</label>
                        <div class="input-group">
                            <input type="email" id="editEmail" class="form-control" placeholder="Email">
                            <input type="text" id="editUsername" class="form-control" placeholder="Username">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select id="editRole" class="form-select" required>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                                <option value="parent">Parent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select id="editStatus" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-creative px-4" id="saveEditUser">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Real-time Search & Filter Logic
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const tableRows = document.querySelectorAll('#usersTable tbody tr');

    function filterTable() {
        const search = searchInput.value.toLowerCase();
        const role = roleFilter.value;
        const status = statusFilter.value;

        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowRole = row.dataset.role;
            const rowStatus = row.dataset.status;

            const matchesSearch = text.includes(search);
            const matchesRole = !role || rowRole === role;
            const matchesStatus = !status || rowStatus === status;

            if (matchesSearch && matchesRole && matchesStatus) {
                row.style.display = '';
                // Add a subtle animation on reveal
                row.style.animation = 'fadeIn 0.3s ease';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterTable);
    roleFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
    
    document.getElementById('clearFilters').addEventListener('click', () => {
        searchInput.value = '';
        roleFilter.value = '';
        statusFilter.value = '';
        filterTable();
    });

    // Populate Edit Modal
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editUserId').value = btn.dataset.id;
            document.getElementById('editName').value = btn.dataset.name;
            document.getElementById('editUsername').value = btn.dataset.username;
            document.getElementById('editEmail').value = btn.dataset.email;
            document.getElementById('editRole').value = btn.dataset.role;
            document.getElementById('editStatus').value = btn.dataset.status;
        });
    });

    // Mock Alerts
    document.getElementById('saveAddUser').addEventListener('click', () => {
        alert('Design Demo: User created successfully!');
        bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
    });
    document.getElementById('saveEditUser').addEventListener('click', () => {
        alert('Design Demo: User details updated!');
        bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
    });
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (confirm('Design Demo: Are you sure you want to delete this user?')) {
                // In a real app, you would make an AJAX call here
                alert('User deleted.');
            }
        });
    });
</script>

<style>
    /* Animation helper */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</body>
</html>