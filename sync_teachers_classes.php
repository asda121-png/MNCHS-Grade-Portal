<?php
require_once 'includes/config.php';

echo "<h1>Syncing Teachers' Assigned Classes</h1>";
echo "<p>This script populates the <code>class_subjects</code> table based on the <code>assigned_sections</code> text field in the <code>teachers</code> table.</p>";

// --- Helper Functions (Duplicated from API for standalone execution) ---

function getOrCreateClassId_Sync($conn, $classStr) {
    $classStr = trim($classStr);
    if (empty($classStr)) return null;

    if (is_numeric($classStr)) {
        $res = $conn->query("SELECT id FROM classes WHERE id = " . (int)$classStr);
        if ($res && $res->num_rows > 0) return (int)$classStr;
    }

    $escStr = $conn->real_escape_string($classStr);
    $query = "SELECT id FROM classes WHERE section = '$escStr' OR CONCAT(class_name, ' ', section) = '$escStr' OR CONCAT(class_name, '-', section) = '$escStr' OR CONCAT(class_name, ' - ', section) = '$escStr' LIMIT 1";
    $res = $conn->query($query);
    if ($res && $res->num_rows > 0) {
        return (int)$res->fetch_assoc()['id'];
    }

    $className = '';
    $gradeLevel = 0;
    $section = '';
    $academicYear = '2025-2026';

    if (preg_match('/^Grade\s+(\d+)\s+-\s+(.+)$/i', $classStr, $matches)) {
        $gradeLevel = (int)$matches[1];
        $section = $matches[2];
        $className = "Grade " . $gradeLevel;
    } elseif (preg_match('/^(.+?)\s+(\d+)-(.+)$/i', $classStr, $matches)) {
        $gradeLevel = (int)$matches[2];
        $section = $matches[3];
        $className = $matches[1] . " " . $matches[2];
    } else {
        $section = $classStr;
        $className = "Uncategorized";
    }

    $stmt = $conn->prepare("INSERT INTO classes (class_name, grade_level, section, academic_year) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $className, $gradeLevel, $section, $academicYear);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return null;
}

function getOrCreateSubjectId_Sync($conn, $subjectName) {
    $subjectName = trim($subjectName);
    if (empty($subjectName)) return null;

    $stmt = $conn->prepare("SELECT id FROM subjects WHERE subject_name = ?");
    $stmt->bind_param("s", $subjectName);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return $row['id'];
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
    $stmt->bind_param("s", $subjectName);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return null;
}

// --- Main Logic ---

$query = "SELECT t.id, t.department, t.specialization, t.assigned_sections, u.first_name, u.last_name 
          FROM teachers t JOIN users u ON t.user_id = u.id";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<ul>";
    while ($teacher = $result->fetch_assoc()) {
        $name = $teacher['first_name'] . ' ' . $teacher['last_name'];
        echo "<li>Processing <strong>$name</strong>...";
        
        $teacherId = $teacher['id'];
        $dept = $teacher['department'];
        $specialization = $teacher['specialization'];
        $assignedSectionsStr = $teacher['assigned_sections'];

        // Clear existing
        $conn->query("DELETE FROM class_subjects WHERE teacher_id = $teacherId");

        if (!empty($assignedSectionsStr)) {
            $sections = array_unique(array_map('trim', explode(',', $assignedSectionsStr)));
            $isJHS = strpos($dept, 'Junior High School') !== false || 
                     in_array($dept, ['English Department', 'Mathematics Department', 'Science Department', 'Social Studies Department', 'Filipino / Language Department', 'MAPEH Department', 'TLE / TVL Department']);

            $count = 0;
            foreach ($sections as $sectionName) {
                if (empty($sectionName)) continue;

                $classId = getOrCreateClassId_Sync($conn, $sectionName);
                if (!$classId) continue;

                $subjectName = $specialization;
                if ($isJHS) {
                    $gradeLevel = 0;
                    $cRes = $conn->query("SELECT grade_level FROM classes WHERE id = $classId");
                    if ($cRes && $row = $cRes->fetch_assoc()) $gradeLevel = $row['grade_level'];

                    $prefix = 'Subject';
                    if (strpos($dept, 'Math') !== false) $prefix = 'Mathematics';
                    elseif (strpos($dept, 'Science') !== false) $prefix = 'Science';
                    elseif (strpos($dept, 'English') !== false) $prefix = 'English';
                    elseif (strpos($dept, 'Filipino') !== false) $prefix = 'Filipino';
                    elseif (strpos($dept, 'Social') !== false) $prefix = 'Araling Panlipunan';
                    elseif (strpos($dept, 'MAPEH') !== false) $prefix = 'MAPEH';
                    elseif (strpos($dept, 'TLE') !== false) $prefix = 'Technology and Livelihood Education';
                    
                    $subjectName = $prefix . ' ' . $gradeLevel;
                }

                $subjectId = getOrCreateSubjectId_Sync($conn, $subjectName);

                if ($classId && $subjectId) {
                    $conn->query("INSERT INTO class_subjects (class_id, subject_id, teacher_id) VALUES ($classId, $subjectId, $teacherId)");
                    $count++;
                }
            }
            echo " <span style='color:green'>Synced $count classes.</span>";
        } else {
            echo " <span style='color:gray'>No assigned sections.</span>";
        }
        echo "</li>";
    }
    echo "</ul>";
    echo "<h3>Done! Check the Teacher Dashboard now.</h3>";
} else {
    echo "<p>No teachers found.</p>";
}
?>