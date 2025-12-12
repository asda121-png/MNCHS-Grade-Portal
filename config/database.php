<?php
/**
 * Application Configuration
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'grade_portal');

// Application Settings
define('APP_NAME', 'MNCHS Grade Portal');
define('APP_ENV', 'production'); // development, production
define('APP_DEBUG', false);

// Session Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('SESSION_COOKIE_SECURE', false); // Set to true in production with HTTPS

// File Upload Settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);

// API Settings
define('API_TIMEOUT', 30);

// Notification Settings
define('NOTIFICATION_RETENTION_DAYS', 30);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Timezone
date_default_timezone_set('Asia/Manila');

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}
