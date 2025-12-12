<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

require_once '../../includes/config.php';

$message = '';
$messageType = '';

// Get all teachers and classes
$teachers = [];
$classes = [];

$teacherQuery = "SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users u 
                 INNER JOIN teachers t ON u.id = t.user_id ORDER BY first_name, last_name";
if ($result = $conn->query($teacherQuery)) {
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
    $result->free();
}

$classQuery = "SELECT id, class_name FROM classes ORDER BY class_name";
if ($result = $conn->query($classQuery)) {
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    $result->free();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teacher_id'])) {
    $teacher_id = (int)$_POST['teacher_id'];
    $is_adviser = isset($_POST['is_adviser']) ? 1 : 0;
    $adviser_class_id = $is_adviser && !empty($_POST['adviser_class_id']) ? (int)$_POST['adviser_class_id'] : null;
    
    if ($is_adviser && $adviser_class_id === null) {
        $message = 'Please select a class for the adviser';
        $messageType = 'error';
    } else {
        $updateQuery = "UPDATE teachers SET is_adviser = $is_adviser, adviser_class_id = " . 
                      ($adviser_class_id !== null ? $adviser_class_id : 'NULL') . 
                      " WHERE id = $teacher_id";
        
        if ($conn->query($updateQuery)) {
            $message = 'Teacher role updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating teacher: ' . $conn->error;
            $messageType = 'error';
        }
    }
}

// Get current teacher status
$teacherStatus = [];
$statusQuery = "SELECT t.id, CONCAT(u.first_name, ' ', u.last_name) as name, 
                       t.is_adviser, t.adviser_class_id, c.class_name
                FROM teachers t
                INNER JOIN users u ON t.user_id = u.id
                LEFT JOIN classes c ON t.adviser_class_id = c.id
                ORDER BY u.first_name, u.last_name";
if ($result = $conn->query($statusQuery)) {
    while ($row = $result->fetch_assoc()) {
        $teacherStatus[] = $row;
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teacher Roles | MNCHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #2d3436;
            min-height: 100vh;
            padding: 2rem;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            background: linear-gradient(90deg, #800000, #660000);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(128, 0, 0, 0.3);
        }
        .header h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        .header p { opacity: 0.9; }
        
        .message {
            padding: 1.25rem 1.75rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .message.success {
            background: linear-gradient(135deg, #dcfce7 0%, #c8f7d8 100%);
            color: #145a32;
            border: 2px solid #82d9a8;
        }
        .message.error {
            background: linear-gradient(135deg, #fee2e4 0%, #fecdd3 100%);
            color: #7f1d1d;
            border: 2px solid #fca5a5;
        }
        
        .section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .section h2 {
            color: #800000;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2d3436;
        }
        select, input[type="checkbox"] {
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        select {
            width: 100%;
        }
        select:focus, input:focus {
            outline: none;
            border-color: #800000;
            box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
        }
        button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        .btn-submit {
            background: #800000;
            color: white;
            flex: 1;
        }
        .btn-submit:hover {
            background: #660000;
            box-shadow: 0 4px 12px rgba(128, 0, 0, 0.3);
        }
        
        .status-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        .status-table th {
            background: linear-gradient(135deg, #800000 0%, #660000 100%);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 700;
        }
        .status-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .status-table tbody tr:hover {
            background: #f9fafb;
        }
        .status-table tbody tr:nth-child(even) {
            background: #fafbfc;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .badge-adviser {
            background: linear-gradient(135deg, #dcfce7 0%, #c8f7d8 100%);
            color: #145a32;
            border: 2px solid #82d9a8;
        }
        .badge-subject {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #0c4a6e;
            border: 2px solid #3b82f6;
        }
        .badge-both {
            background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
            color: #831843;
            border: 2px solid #f472b6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users"></i> Manage Teacher Roles</h1>
            <p>Set teacher adviser and subject teacher assignments</p>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="section">
            <h2>Assign Teacher Roles</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="teacher_id">Select Teacher <span style="color: #800000;">*</span></label>
                    <select name="teacher_id" id="teacher_id" required>
                        <option value="">-- Choose a teacher --</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_adviser" id="is_adviser" onchange="toggleAdviserClass()">
                        <label for="is_adviser" style="margin-bottom: 0;">Make this teacher an Adviser</label>
                    </div>
                </div>
                
                <div class="form-group" id="adviser_class_group" style="display: none;">
                    <label for="adviser_class_id">Adviser Class <span style="color: #800000;">*</span></label>
                    <select name="adviser_class_id" id="adviser_class_id">
                        <option value="">-- Select class to advise --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Update Role
                    </button>
                </div>
            </form>
        </div>
        
        <div class="section">
            <h2>Current Teacher Roles</h2>
            <table class="status-table">
                <thead>
                    <tr>
                        <th>Teacher Name</th>
                        <th>Role</th>
                        <th>Adviser Class</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teacherStatus as $status): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($status['name']); ?></td>
                            <td>
                                <?php
                                    $isAdviser = (int)$status['is_adviser'];
                                    if ($isAdviser && !empty($status['class_name'])) {
                                        echo '<span class="badge badge-both">';
                                        echo '<i class="fas fa-check-circle"></i>';
                                        echo 'Adviser (' . htmlspecialchars($status['class_name']) . ') & Subject Teacher';
                                        echo '</span>';
                                    } elseif ($isAdviser) {
                                        echo '<span class="badge badge-adviser">';
                                        echo '<i class="fas fa-check-circle"></i>';
                                        echo 'Adviser & Subject Teacher';
                                        echo '</span>';
                                    } else {
                                        echo '<span class="badge badge-subject">';
                                        echo '<i class="fas fa-check-circle"></i>';
                                        echo 'Subject Teacher';
                                        echo '</span>';
                                    }
                                ?>
                            </td>
                            <td><?php echo !empty($status['class_name']) ? htmlspecialchars($status['class_name']) : 'N/A'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        function toggleAdviserClass() {
            const isAdviser = document.getElementById('is_adviser').checked;
            document.getElementById('adviser_class_group').style.display = isAdviser ? 'block' : 'none';
        }
    </script>
</body>
</html>
