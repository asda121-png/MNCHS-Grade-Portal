<?php
/*
 * Main Database Connection
 */

// Load database credentials from .env file
$env_file = __DIR__ . '/../.env';
$db_host = "localhost";
$db_username = "root";
$db_password = "password";
$db_name = "mnchs_grade_portal";
$db_port = 3306;

if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) continue;
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (substr($value, 0, 1) == '"' && substr($value, -1) == '"') {
                $value = substr($value, 1, -1);
            }
            
            switch ($key) {
                case 'DB_HOST':
                    $db_host = $value;
                    break;
                case 'DB_USERNAME':
                    $db_username = $value;
                    break;
                case 'DB_PASSWORD':
                    $db_password = $value;
                    break;
                case 'DB_NAME':
                    $db_name = $value;
                    break;
                case 'DB_PORT':
                    $db_port = (int)$value;
                    break;
            }
        }
    }
}

// Use environment variables if set, fallback to .env values
$host = getenv('DB_HOST') ?: $db_host;
$username = getenv('DB_USERNAME') ?: $db_username;
$password = getenv('DB_PASSWORD') ?: $db_password;
$database = getenv('DB_NAME') ?: $db_name;
$port = getenv('DB_PORT') ? (int)getenv('DB_PORT') : $db_port;

// First, connect without selecting a database to check credentials and create DB if needed
try {
    $conn_temp = new mysqli($host, $username, $password, "", $port);
    if ($conn_temp->connect_error) {
        throw new Exception($conn_temp->connect_error);
    }
    
    // Check if database exists, if not create it
    $check_db = $conn_temp->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $conn_temp->real_escape_string($database) . "'");
    
    if ($check_db->num_rows == 0) {
        // Database doesn't exist, create it
        $create_sql = "CREATE DATABASE IF NOT EXISTS `" . $conn_temp->real_escape_string($database) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (!$conn_temp->query($create_sql)) {
            throw new Exception("Failed to create database: " . $conn_temp->error);
        }
    }
    
    $conn_temp->close();
} catch (Exception $e) {
    http_response_code(503);
    error_log("Database initialization error: " . $e->getMessage());
    die("Database Error: " . $e->getMessage());
}

// Now connect to the actual database
try {
    $conn = new mysqli($host, $username, $password, $database, $port);
    
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }
} catch (Exception $e) {
    http_response_code(503);
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database Connection Error: " . $e->getMessage());
}

// Set character set to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");
