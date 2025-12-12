<?php
/**
 * Autoloader for PSR-4 namespaces
 * Include this file at the beginning of your application
 */

spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'src\\';
    
    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';
    
    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace the namespace separator with directory separator
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Load configuration
require_once __DIR__ . '/config/database.php';
