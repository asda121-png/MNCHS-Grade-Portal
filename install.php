<?php
require_once 'includes/config.php';

$sql_file = __DIR__ . '/database/schema.sql';

echo "<!DOCTYPE html><html><head><title>Database Installation</title><style>body{font-family:sans-serif;padding:20px;line-height:1.6;}</style></head><body>";
echo "<h1>MNCHS Grade Portal - Database Installation</h1>";

if (file_exists($sql_file)) {
    $sql = file_get_contents($sql_file);
    
    // Split SQL into individual queries
    $queries = explode(';', $sql);
    
    echo "<div style='background:#f0f0f0; padding:15px; border-radius:5px;'>";
    echo "<strong>Connecting to database...</strong> Connected.<br>";
    echo "<strong>Executing schema...</strong><br>";
    
    $error = false;
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            if (!$conn->query($query)) {
                echo "<span style='color:red;'>Error executing query: " . $conn->error . "</span><br>";
                echo "<small>Query: " . htmlspecialchars(substr($query, 0, 100)) . "...</small><br>";
                $error = true;
            }
        }
    }
    
    if (!$error) {
        echo "<h3 style='color:green;'>Database setup completed successfully!</h3>";
        echo "<p>You can now <a href='index.php' style='background:#800000; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Go to Login Page</a></p>";
        echo "<p><strong>Default Admin Credentials:</strong><br>Username: <code>admin</code><br>Password: <code>password</code></p>";
    }
    echo "</div>";
} else {
    echo "<h3 style='color:red;'>Error: schema.sql not found in database directory.</h3>";
}
echo "</body></html>";
?>