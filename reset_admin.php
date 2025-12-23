<?php
require_once 'includes/config.php';

$username = 'admin';
$password = 'password';
$email = 'admin@mnchs.edu.ph';

// Generate a new hash using the current environment's default algorithm
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<!DOCTYPE html><html><head><title>Reset Admin</title><style>body{font-family:sans-serif;padding:20px;}</style></head><body>";
echo "<h1>Admin Account Reset</h1>";

// Check if admin exists
$check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Update existing admin
    $update = $conn->prepare("UPDATE users SET password = ?, is_active = 1 WHERE username = ?");
    $update->bind_param("ss", $hash, $username);
    
    if ($update->execute()) {
        echo "<p style='color:green'>✓ Admin password successfully reset.</p>";
    } else {
        echo "<p style='color:red'>✗ Error updating password: " . $conn->error . "</p>";
    }
    $update->close();
} else {
    // Create new admin
    $insert = $conn->prepare("INSERT INTO users (username, password, email, first_name, last_name, role, is_active) VALUES (?, ?, ?, 'System', 'Administrator', 'admin', 1)");
    $insert->bind_param("sss", $username, $hash, $email);
    
    if ($insert->execute()) {
        echo "<p style='color:green'>✓ Admin account created successfully.</p>";
    } else {
        echo "<p style='color:red'>✗ Error creating admin account: " . $conn->error . "</p>";
    }
    $insert->close();
}

echo "<p><strong>Username:</strong> admin<br><strong>Password:</strong> password</p>";
echo "<p><a href='index.php'>Go to Login</a></p>";
echo "</body></html>";
?>