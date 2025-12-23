<?php
session_start();
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$admin_name = $_SESSION['user_name'] ?? 'Administrator';

// --- Securely load API Key from .env file ---
$google_api_key = '';
$dotenv_path = __DIR__ . '/../../.env';

if (file_exists($dotenv_path)) {
    $lines = file($dotenv_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        if (trim($name) === 'GOOGLE_CALENDAR_API_KEY') {
            $value = trim($value);
            if (substr($value, 0, 1) == '"' && substr($value, -1) == '"') {
                $google_api_key = substr($value, 1, -1);
            } else { $google_api_key = $value; }
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | MNCHS Grade Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link rel="icon" href="../../assets/images/logo.ico" type="image/x-icon">
    <style>
        :root {
            --primary: #800000; --primary-dark: #660000; --accent: #FFD700;
            --text: #2d3436; --text-light: #636e72; --bg: #f5f6fa; --white: #ffffff;
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
        .welcome-card { background: white; padding: 2rem; border-radius: 16px; box-shadow: var(--shadow); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
        .action-buttons { display: flex; gap: 1rem; }
        .btn { padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: var(--transition); display: flex; align-items: center; gap: 8px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: #e0e0e0; color: var(--text); }
        .btn-secondary:hover { background: #d0d0d0; }
        #calendar-container { background: white; padding: 2rem; border-radius: 16px; box-shadow: var(--shadow); }
        
        /* Modal Styles */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: none; justify-content: center; align-items: center; z-index: 2000; }
        .modal-content { background: white; padding: 2rem; border-radius: 16px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <header class="header">
        <h1><i class="fas fa-shield-alt"></i> MNCHS Admin</h1>
        <div class="user-info">
            <span><?php echo htmlspecialchars($admin_name); ?></span>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-logo-container"><img src="../../assets/images/logo.png" alt="MNCHS Logo" class="sidebar-logo"></div>
            <ul>
                <li><a href="admindashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="adminteachers.php"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a></li>
                <li><a href="adminreports.php"><i class="fas fa-file-alt"></i> Reports</a></li>
                <li><a href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <div class="welcome-card">
                <div>
                    <h2>School Calendar & Schedule</h2>
                    <p>Manage school events and grade encoding schedules.</p>
                </div>
                <div class="action-buttons">
                    <button id="add-event-btn" class="btn btn-primary"><i class="fas fa-plus"></i> Add Event</button>
                    <button id="manage-events-btn" class="btn btn-secondary"><i class="fas fa-list"></i> Manage Events & Schedule</button>
                </div>
            </div>

            <div id="calendar-container">
                <div id="calendar"></div>
            </div>
        </main>
    </div>

    <!-- Add Event Modal -->
    <div id="add-event-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add School Event</h3>
                <button id="add-event-cancel-btn" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
            </div>
            <form id="add-event-form">
                <div class="form-group">
                    <label>Event Title</label>
                    <input type="text" id="add-event-title" required>
                </div>
                <div class="form-group">
                    <label>Event Type</label>
                    <select id="add-event-type">
                        <option value="holiday">Holiday</option>
                        <option value="examination">Examination</option>
                        <option value="meeting">Meeting</option>
                        <option value="deadline">Deadline</option>
                        <option value="celebration">Celebration</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group" id="add-other-type-container" style="display:none;">
                    <label>Specify Type</label>
                    <input type="text" id="add-other-type">
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" id="add-event-start" required>
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" id="add-event-end" required>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" id="add-event-published" checked> Publish to Portal</label>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manage Events & Schedule Modal -->
    <div id="manage-events-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3>Manage Events & Grading Schedule</h3>
                <button id="close-manage-events-btn" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
            </div>
            
            <!-- Grading Periods Section -->
            <div style="margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid #eee;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h4 style="color:var(--primary);">Quarterly Grade Encoding Schedule</h4>
                    <button id="add-grading-period-btn" class="btn btn-primary" style="font-size:0.9rem;"><i class="fas fa-clock"></i> Set Schedule</button>
                </div>
                <div id="grading-periods-section">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Events List -->
            <div>
                <h4 style="color:var(--primary); margin-bottom:1rem;">All Events</h4>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f9f9f9; text-align:left;">
                            <th style="padding:10px;">Title</th>
                            <th style="padding:10px;">Date</th>
                            <th style="padding:10px;">Type</th>
                            <th style="padding:10px;">Status</th>
                            <th style="padding:10px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="events-table-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Grading Period Modal -->
    <div id="grading-period-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Set Grade Encoding Schedule</h3>
            </div>
            <form id="grading-period-form">
                <div class="form-group">
                    <label>Quarter</label>
                    <select id="gp-quarter" required>
                        <option value="1">1st Quarter</option>
                        <option value="2">2nd Quarter</option>
                        <option value="3">3rd Quarter</option>
                        <option value="4">4th Quarter</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Open Date (Start)</label>
                    <input type="date" id="gp-start-date" required>
                </div>
                <div class="form-group">
                    <label>Close Date (End)</label>
                    <input type="date" id="gp-end-date" required>
                </div>
                <div class="modal-footer">
                    <button type="button" id="gp-cancel-btn" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Event Modal (Hidden) -->
    <div id="edit-event-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Event</h3>
                <button id="edit-event-cancel-btn" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
            </div>
            <form id="edit-event-form">
                <input type="hidden" id="edit-event-id">
                <div class="form-group">
                    <label>Event Title</label>
                    <input type="text" id="edit-event-title" required>
                </div>
                <div class="form-group">
                    <label>Event Type</label>
                    <select id="edit-event-type">
                        <option value="holiday">Holiday</option>
                        <option value="examination">Examination</option>
                        <option value="meeting">Meeting</option>
                        <option value="deadline">Deadline</option>
                        <option value="celebration">Celebration</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group" id="edit-other-type-container" style="display:none;">
                    <label>Specify Type</label>
                    <input type="text" id="edit-other-type">
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" id="edit-event-start" required>
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" id="edit-event-end" required>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" id="edit-event-published"> Publish to Portal</label>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <h3 id="confirm-title" style="color:var(--primary); margin-bottom:1rem;">Confirm Action</h3>
            <p id="confirm-message" style="margin-bottom:2rem;">Are you sure?</p>
            <div class="modal-footer" style="justify-content: center;">
                <button id="confirm-cancel-btn" class="btn btn-secondary">Cancel</button>
                <button id="confirm-ok-btn" class="btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>

    <div id="logout-modal-container"></div>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/google-calendar@5.11.3/main.global.min.js'></script>
    <script>window.GOOGLE_API_KEY = '<?php echo htmlspecialchars($google_api_key); ?>';</script>
    <script src="../../assets/js/NotificationManager.js"></script>
    <script src="../../assets/js/admindashboard.js"></script>
</body>
</html>