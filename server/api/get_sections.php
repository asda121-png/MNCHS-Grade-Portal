<?php
// Set the content type to JSON for the response
header('Content-Type: application/json');

// Include your database configuration and the APIResponse utility
// The paths are relative to this file's location in server/api/
require_once '../../includes/config.php'; 
require_once '../../src/Utilities/APIResponse.php';

// Import the namespaced APIResponse class so we can use it directly
use src\Utilities\APIResponse;

// Check if the 'grade_level' parameter was sent in the URL
if (!isset($_GET['grade_level']) || !is_numeric($_GET['grade_level'])) {
    // If not, send a standardized error response and stop the script
    APIResponse::error('Invalid or missing grade_level parameter.', 400);
}

$gradeLevel = (int)$_GET['grade_level'];

try {
    // Your config.php provides a mysqli connection named $conn. We'll use that.
    // Query the 'classes' table for distinct sections by grade_level
    $stmt = $conn->prepare("SELECT DISTINCT section FROM classes WHERE grade_level = ? AND section IS NOT NULL AND section != '' ORDER BY section ASC");
    if (!$stmt) {
        throw new Exception('SQL statement preparation failed: ' . $conn->error);
    }

    $stmt->bind_param("i", $gradeLevel);
    $stmt->execute();
    $result = $stmt->get_result();
    $sections = [];
    while ($row = $result->fetch_assoc()) {
        $sections[] = ["section_name" => $row["section"]];
    }
    $stmt->close();

    APIResponse::success(['sections' => $sections]);

} catch (Exception $e) {
    // In case of any error, log it for debugging and send a generic server error response.
    error_log('Get Sections API Error: ' . $e->getMessage());
    APIResponse::error('An error occurred while fetching sections.', 500);
}
?>