<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo 'Access denied.';
    exit();
}

require_once '../../includes/config.php';

$type = isset($_GET['type']) ? trim((string) $_GET['type']) : '';
if ($type === '') {
    http_response_code(400);
    echo 'Missing report type.';
    exit();
}

$prepareCsvResponse = static function ($filename) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
};

$escapeCsvOutput = static function ($value) {
    if ($value === null) {
        return '';
    }
    if (is_bool($value)) {
        return $value ? 'Yes' : 'No';
    }
    return (string) $value;
};

$writeCsv = static function ($headers, $rows) use ($escapeCsvOutput) {
    $output = fopen('php://output', 'w');
    if ($output === false) {
        throw new RuntimeException('Unable to open output stream.');
    }

    fputcsv($output, $headers);
    foreach ($rows as $row) {
        $line = [];
        foreach ($headers as $headerKey) {
            $line[] = isset($row[$headerKey]) ? $escapeCsvOutput($row[$headerKey]) : '';
        }
        fputcsv($output, $line);
    }
    fclose($output);
};

try {
    switch ($type) {
        case 'student-masterlist':
            $gradeLevel = isset($_GET['grade_level']) && $_GET['grade_level'] !== '' ? (int) $_GET['grade_level'] : null;
            $academicYear = isset($_GET['academic_year']) ? trim((string) $_GET['academic_year']) : '';

            $filenameParts = ['student_masterlist'];
            if ($gradeLevel !== null) {
                $filenameParts[] = 'grade' . $gradeLevel;
            }
            if ($academicYear !== '') {
                $filenameParts[] = str_replace(['/', ' '], '-', strtolower($academicYear));
            }
            $filenameParts[] = date('Ymd_His');

            $prepareCsvResponse(implode('_', $filenameParts) . '.csv');

            $query = 'SELECT s.student_id, s.student_name, s.grade_level, s.section, u.email, u.is_active
                      FROM students s
                      LEFT JOIN users u ON s.user_id = u.id';
            $conditions = [];
            $params = [];
            $types = '';

            if ($gradeLevel !== null) {
                $conditions[] = 's.grade_level = ?';
                $params[] = $gradeLevel;
                $types .= 'i';
            }
            if ($academicYear !== '') {
                $conditions[] = 'EXISTS (
                    SELECT 1
                    FROM classes c
                    JOIN class_enrollments ce ON ce.class_id = c.id
                    WHERE ce.student_id = s.id AND c.academic_year = ?
                )';
                $params[] = $academicYear;
                $types .= 's';
            }
            if ($conditions) {
                $query .= ' WHERE ' . implode(' AND ', $conditions);
            }
            $query .= ' ORDER BY s.grade_level, s.section, s.student_name';

            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                throw new RuntimeException('Database error preparing statement: ' . $conn->error);
            }

            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = [
                    'Student ID' => $row['student_id'] ?? '',
                    'Student Name' => $row['student_name'] ?? '',
                    'Grade Level' => $row['grade_level'] ?? '',
                    'Section' => $row['section'] ?? '',
                    'Email' => $row['email'] ?? '',
                    'Active' => isset($row['is_active']) ? ((int) $row['is_active'] === 1 ? 'Yes' : 'No') : ''
                ];
            }
            $stmt->close();

            $headers = ['Student ID', 'Student Name', 'Grade Level', 'Section', 'Email', 'Active'];
            if (!empty($rows)) {
                $headers = array_keys($rows[0]);
            }
            $writeCsv($headers, $rows);
            break;

        case 'section-grades':
            $gradeLevel = isset($_GET['grade_level']) ? (int) $_GET['grade_level'] : null;
            $section = isset($_GET['section']) ? trim((string) $_GET['section']) : '';
            $subjectId = isset($_GET['subject_id']) ? (int) $_GET['subject_id'] : 0;
            $academicYear = isset($_GET['academic_year']) ? trim((string) $_GET['academic_year']) : '';

            if ($gradeLevel === null || $gradeLevel === 0 || $section === '' || $subjectId === 0) {
                http_response_code(400);
                echo 'Missing required filters for section grades report.';
                exit();
            }

            $filenameParts = ['section_grades', 'grade' . $gradeLevel, preg_replace('/\s+/', '-', strtolower($section))];
            if ($academicYear !== '') {
                $filenameParts[] = str_replace(['/', ' '], '-', strtolower($academicYear));
            }
            $filenameParts[] = date('Ymd_His');

            $prepareCsvResponse(implode('_', $filenameParts) . '.csv');

            $query = 'SELECT
                        s.student_id,
                        s.student_name,
                        subj.subject_name,
                        ROUND(AVG(g.percentage), 2) AS average_percentage,
                        COUNT(g.id) AS assessments_count
                      FROM classes c
                      JOIN class_enrollments ce ON ce.class_id = c.id
                      JOIN students s ON ce.student_id = s.id
                      JOIN class_subjects cs ON cs.class_id = c.id
                      JOIN subjects subj ON subj.id = cs.subject_id
                      LEFT JOIN grades g ON g.student_id = s.id AND g.class_subject_id = cs.id
                      WHERE c.grade_level = ? AND c.section = ? AND subj.id = ?';
            $params = [$gradeLevel, $section, $subjectId];
            $types = 'isi';

            if ($academicYear !== '') {
                $query .= ' AND c.academic_year = ?';
                $params[] = $academicYear;
                $types .= 's';
            }

            $query .= ' GROUP BY s.student_id, s.student_name, subj.subject_name
                        ORDER BY s.student_name';

            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                throw new RuntimeException('Database error preparing statement: ' . $conn->error);
            }
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = [
                    'Student ID' => $row['student_id'] ?? '',
                    'Student Name' => $row['student_name'] ?? '',
                    'Subject' => $row['subject_name'] ?? '',
                    'Average Percentage' => isset($row['average_percentage']) ? $row['average_percentage'] : 'N/A',
                    'Assessments Count' => $row['assessments_count'] ?? 0,
                ];
            }
            $stmt->close();

            $headers = ['Student ID', 'Student Name', 'Subject', 'Average Percentage', 'Assessments Count'];
            if (!empty($rows)) {
                $headers = array_keys($rows[0]);
            }
            $writeCsv($headers, $rows);
            break;

        case 'teacher-list':
            $filename = 'teacher_list_' . date('Ymd_His') . '.csv';
            $prepareCsvResponse($filename);

            $query = 'SELECT
                        t.teacher_id,
                        u.first_name,
                        u.last_name,
                        u.email,
                        t.department,
                        t.specialization,
                        u.is_active
                      FROM teachers t
                      JOIN users u ON t.user_id = u.id
                      ORDER BY u.last_name, u.first_name';

            $result = $conn->query($query);
            if ($result === false) {
                throw new RuntimeException('Database query failed: ' . $conn->error);
            }

            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = [
                    'Teacher ID' => $row['teacher_id'] ?? '',
                    'First Name' => $row['first_name'] ?? '',
                    'Last Name' => $row['last_name'] ?? '',
                    'Email' => $row['email'] ?? '',
                    'Department' => $row['department'] ?? '',
                    'Specialization' => $row['specialization'] ?? '',
                    'Active' => isset($row['is_active']) ? ((int) $row['is_active'] === 1 ? 'Yes' : 'No') : ''
                ];
            }
            $result->free();

            $headers = ['Teacher ID', 'First Name', 'Last Name', 'Email', 'Department', 'Specialization', 'Active'];
            if (!empty($rows)) {
                $headers = array_keys($rows[0]);
            }
            $writeCsv($headers, $rows);
            break;

        default:
            http_response_code(400);
            echo 'Unsupported report type.';
            exit();
    }
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'An error occurred while generating the report. Please try again later.';
    error_log('Report generation error: ' . $e->getMessage());
    exit();
}

exit();
