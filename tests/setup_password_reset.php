<?php
/**
 * Database Setup Script for Forgot Password Feature
 * This script adds the required columns to the users table if they don't exist
 */

require_once "includes/config.php";

echo "<h2>Setting up Password Reset Feature...</h2>";

try {
    // Check if reset_token column exists
    $check_query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'reset_token'";
    $result = $conn->query($check_query);
    
    if ($result->num_rows === 0) {
        echo "<p>Adding reset_token column...</p>";
        $sql1 = "ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL DEFAULT NULL";
        if ($conn->query($sql1)) {
            echo "✓ Added reset_token column<br>";
        } else {
            echo "✗ Error adding reset_token: " . $conn->error . "<br>";
        }
    } else {
        echo "✓ reset_token column already exists<br>";
    }
    
    // Check if reset_token_expiry column exists
    $check_query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'reset_token_expiry'";
    $result = $conn->query($check_query);
    
    if ($result->num_rows === 0) {
        echo "<p>Adding reset_token_expiry column...</p>";
        $sql2 = "ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME NULL DEFAULT NULL";
        if ($conn->query($sql2)) {
            echo "✓ Added reset_token_expiry column<br>";
        } else {
            echo "✗ Error adding reset_token_expiry: " . $conn->error . "<br>";
        }
    } else {
        echo "✓ reset_token_expiry column already exists<br>";
    }
    
    // Create index if it doesn't exist
    $check_index = "SELECT DISTINCT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = 'users' AND INDEX_NAME = 'idx_reset_token'";
    $result = $conn->query($check_index);
    
    if ($result->num_rows === 0) {
        echo "<p>Creating index on reset_token...</p>";
        $sql3 = "CREATE INDEX idx_reset_token ON users(reset_token)";
        if ($conn->query($sql3)) {
            echo "✓ Created index on reset_token<br>";
        } else {
            // Index might already exist with different name, that's okay
            echo "ℹ Index already exists or not needed<br>";
        }
    } else {
        echo "✓ Index on reset_token already exists<br>";
    }
    
    echo "<h3 style='color: green;'>✓ Setup Complete!</h3>";
    echo "<p><a href='index.php'>Return to Login</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Error: " . $e->getMessage() . "</h3>";
}

$conn->close();
?>
