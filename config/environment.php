<?php
// /config/environment.php
// Universal environment detection

// Turn on error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Detect environment
function detectEnvironment() {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Check for local development environments
    if ($host === 'localhost' || 
        strpos($host, '127.0.0.1') !== false ||
        strpos($host, '192.168.') !== false ||
        strpos($host, '10.') !== false ||
        strpos($host, '172.') !== false) {
        return 'development';
    }
    
    return 'production';
}

$environment = detectEnvironment();

// Set error reporting based on environment
if ($environment === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

// Define environment constant
define('ENVIRONMENT', $environment);
define('IS_DEV', $environment === 'development');
define('IS_PROD', $environment === 'production');

// For debugging
if (IS_DEV) {
    error_log("Environment: " . ENVIRONMENT);
    error_log("Host: " . ($_SERVER['HTTP_HOST'] ?? 'N/A'));
    error_log("Script: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A'));
}
?>