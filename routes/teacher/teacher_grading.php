<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../index.php');
    exit();
}
require_once '../../includes/config.php';

$class_subject_id = isset($_GET['class_subject_id']) ? (int)$_GET['class_subject_id'] : 0;
$teacher_user_id = $_SESSION['user_id'];

// Validate that this class_subject belongs to the logged-in teacher
$check_stmt = $conn->prepare("
    SELECT cs.id, s.subject_name, c.class_name, c.section, c.id as class_id
    FROM class_subjects cs
    JOIN teachers t ON cs.teacher_id = t.id
    JOIN subjects s ON cs.subject_id = s.id
    JOIN classes c ON cs.class_id = c.id
    WHERE cs.id = ? AND t.user_id = ?
");
$check_stmt->bind_param("ii", $class_subject_id, $teacher_user_id);
$check_stmt->execute();
$subject_info = $check_stmt->get_result()->fetch_assoc();
$check_stmt->close();

if (!$subject_info) {
    echo "Access Denied: You are not assigned to this subject/section.";
    exit();
}

// Fetch Students in this class
$students = [];
$stud_query = "SELECT s.id, s.student_name, g.q1, g.q2, g.q3, g.q4, g.id as grade_id
               FROM class_enrollments ce
               JOIN students s ON ce.student_id = s.id
               LEFT JOIN grades g ON g.student_id = s.id AND g.class_subject_id = ?
               WHERE ce.class_id = ?
               ORDER BY s.student_name";
$stmt = $conn->prepare($stud_query);
$stmt->bind_param("ii", $class_subject_id, $subject_info['class_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

// Handle Grade Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Loop through posted grades and update/insert
    foreach ($_POST['grades'] as $student_id => $quarters) {
        $q1 = $quarters['q1'] !== '' ? $quarters['q1'] : null;
        $q2 = $quarters['q2'] !== '' ? $quarters['q2'] : null;
        $q3 = $quarters['q3'] !== '' ? $quarters['q3'] : null;
        $q4 = $quarters['q4'] !== '' ? $quarters['q4'] : null;

        // Check if grade record exists
        $check = $conn->prepare("SELECT id FROM grades WHERE student_id = ? AND class_subject_id = ?");
        $check->bind_param("ii", $student_id, $class_subject_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            // Update
            $upd = $conn->prepare("UPDATE grades SET q1=?, q2=?, q3=?, q4=? WHERE student_id=? AND class_subject_id=?");
            $upd->bind_param("ddddii", $q1, $q2, $q3, $q4, $student_id, $class_subject_id);
            $upd->execute();
            $upd->close();
        } else {
            // Insert
            $ins = $conn->prepare("INSERT INTO grades (student_id, class_subject_id, q1, q2, q3, q4) VALUES (?, ?, ?, ?, ?, ?)");
            $ins->bind_param("iidddd", $student_id, $class_subject_id, $q1, $q2, $q3, $q4);
            $ins->execute();
            $ins->close();
        }
        $check->close();
    }
    // Refresh to show saved data
    header("Location: teacher_grading.php?class_subject_id=" . $class_subject_id . "&saved=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Encode Grades | MNCHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f5f6fa; padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        h2 { color: #800000; margin: 0; }
        .btn { padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; font-weight: 600; text-decoration: none; }
        .btn-primary { background: #800000; color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; color: #800000; }
        input[type="number"] { width: 60px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; text-align: center; }
        input:disabled { background: #f0f0f0; cursor: not-allowed; }
        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 6px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h2><?php echo htmlspecialchars($subject_info['subject_name']); ?></h2>
                <p style="color:#666; margin-top:5px;">
                    Class: <?php echo htmlspecialchars($subject_info['class_name']); ?> | 
                    Section: <?php echo htmlspecialchars($subject_info['section']); ?>
                </p>
            </div>
            <a href="teacherdashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?php if (isset($_GET['saved'])): ?>
            <div class="success-msg"><i class="fas fa-check-circle"></i> Grades saved successfully!</div>
        <?php endif; ?>

        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th style="text-align:center;">Q1</th>
                        <th style="text-align:center;">Q2</th>
                        <th style="text-align:center;">Q3</th>
                        <th style="text-align:center;">Q4</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                            <td style="text-align:center;">
                                <input type="number" name="grades[<?php echo $student['id']; ?>][q1]" value="<?php echo $student['q1']; ?>" min="60" max="100" step="0.01">
                            </td>
                            <td style="text-align:center;">
                                <input type="number" name="grades[<?php echo $student['id']; ?>][q2]" value="<?php echo $student['q2']; ?>" min="60" max="100" step="0.01">
                            </td>
                            <td style="text-align:center;">
                                <input type="number" name="grades[<?php echo $student['id']; ?>][q3]" value="<?php echo $student['q3']; ?>" min="60" max="100" step="0.01">
                            </td>
                            <td style="text-align:center;">
                                <input type="number" name="grades[<?php echo $student['id']; ?>][q4]" value="<?php echo $student['q4']; ?>" min="60" max="100" step="0.01">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:2rem; text-align:right;">
                <button type="submit" class="btn btn-primary btn-save"><i class="fas fa-save"></i> Save Grades</button>
            </div>
        </form>
    </div>

    <script src="../../assets/js/GradeEntryLock.js"></script>
</body>
</html>