<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../index.php');
    exit();
}
require_once '../../includes/config.php';

$user_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['user_name'] ?? (isset($_SESSION['first_name']) && isset($_SESSION['last_name']) ? trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : 'Teacher');

// Verify Adviser Status
$stmt = $conn->prepare("SELECT id, is_adviser, adviser_class_id FROM teachers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher || !$teacher['is_adviser'] || !$teacher['adviser_class_id']) {
    echo "Access Denied: You are not assigned as a class adviser.";
    exit();
}

$class_id = $teacher['adviser_class_id'];

// Handle Manual Enrollment (Add Student)
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student_lrn'])) {
    $lrn = trim($_POST['add_student_lrn']);
    
    // Find student by LRN
    $find_stmt = $conn->prepare("SELECT id, student_name FROM students WHERE lrn = ?");
    $find_stmt->bind_param("s", $lrn);
    $find_stmt->execute();
    $student_res = $find_stmt->get_result();
    
    if ($student_res->num_rows > 0) {
        $student = $student_res->fetch_assoc();
        $student_id = $student['id'];
        
        // Check if already enrolled in this class
        $check_enroll = $conn->prepare("SELECT id FROM class_enrollments WHERE class_id = ? AND student_id = ?");
        $check_enroll->bind_param("ii", $class_id, $student_id);
        $check_enroll->execute();
        
        if ($check_enroll->get_result()->num_rows == 0) {
            // Enroll student
            $enroll_stmt = $conn->prepare("INSERT INTO class_enrollments (class_id, student_id) VALUES (?, ?)");
            $enroll_stmt->bind_param("ii", $class_id, $student_id);
            if ($enroll_stmt->execute()) {
                $message = "<div class='alert success'>Successfully enrolled " . htmlspecialchars($student['student_name']) . ".</div>";
            } else {
                $message = "<div class='alert error'>Error enrolling student.</div>";
            }
            $enroll_stmt->close();
        } else {
            $message = "<div class='alert warning'>Student is already enrolled in this class.</div>";
        }
        $check_enroll->close();
    } else {
        $message = "<div class='alert error'>Student with LRN $lrn not found.</div>";
    }
    $find_stmt->close();
}

// Fetch Class Details
$class_stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
$class_stmt->bind_param("i", $class_id);
$class_stmt->execute();
$class_info = $class_stmt->get_result()->fetch_assoc();
$class_stmt->close();

// Fetch Enrolled Students
$students = [];
$stud_query = "SELECT s.id, s.lrn, s.student_name, s.gender 
               FROM class_enrollments ce 
               JOIN students s ON ce.student_id = s.id 
               WHERE ce.class_id = ? 
               ORDER BY s.student_name";
$stud_stmt = $conn->prepare($stud_query);
$stud_stmt->bind_param("i", $class_id);
$stud_stmt->execute();
$res = $stud_stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}
$stud_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advisory Class Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f5f6fa; color: #2d3436; padding: 2rem; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 2px solid #eee; padding-bottom: 1rem; }
        h1 { color: #800000; margin: 0; font-size: 1.5rem; }
        .btn { padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: #800000; color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; color: #800000; }
        .alert { padding: 10px; border-radius: 6px; margin-bottom: 1rem; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }
        
        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 2rem; border-radius: 8px; width: 400px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Advisory Class: <?php echo htmlspecialchars($class_info['class_name']); ?></h1>
                <p style="color:#666;">Section: <?php echo htmlspecialchars($class_info['section']); ?> | Grade <?php echo htmlspecialchars($class_info['grade_level']); ?></p>
            </div>
            <a href="teacherdashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <?php echo $message; ?>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3><i class="fas fa-users"></i> Student List</h3>
            <button onclick="document.getElementById('add-modal').style.display='flex'" class="btn btn-primary"><i class="fas fa-user-plus"></i> Manual Enrollment</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>LRN</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Academic Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($students) > 0): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['lrn']); ?></td>
                            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['gender']); ?></td>
                            <td><span style="background:#e8f5e9; color:#2e7d32; padding:2px 8px; border-radius:4px; font-size:0.85rem;">Enrolled</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; color:#999;">No students enrolled yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
            </div>
        </main>
    </div>

    <!-- Add Student Modal -->
    <div id="add-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Manual Enrollment</h3>
                <button id="close-add-modal" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
            </div>
            <p style="font-size:0.9rem; color:#666; margin-bottom:1.5rem;">Add a student to your advisory class section using their LRN. Used for late enrollees.</p>
            <form method="POST">
                <div class="form-group">
                    <label>Student LRN</label>
                    <input type="text" name="add_student_lrn" required placeholder="Enter 12-digit LRN">
                </div>
                <div class="modal-footer">
                    <button type="button" id="cancel-add-modal" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Enroll Student</button>
                </div>
            </form>
        </div>
    </div>

    <div id="logout-modal-container"></div>
    <script src="../../assets/js/NotificationManager.js"></script>
    <script src="../../assets/js/teacher_shared.js"></script>
    <script>
        // Modal Logic
        const modal = document.getElementById('add-modal');
        document.getElementById('open-add-modal').onclick = () => modal.style.display = 'flex';
        document.getElementById('close-add-modal').onclick = () => modal.style.display = 'none';
        document.getElementById('cancel-add-modal').onclick = () => modal.style.display = 'none';
        window.onclick = (e) => { if (e.target == modal) modal.style.display = 'none'; };
    </script>
</body>
</html>