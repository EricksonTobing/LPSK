<?php
// env_check.php - Deteksi environment
function is_localhost() {
    $whitelist = array('127.0.0.1', '::1', 'localhost');
    return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
}

// Set base URL berdasarkan environment
if (is_localhost()) {
    define('BASE_URL', 'http://localhost/lpsk');
    define('ENVIRONMENT', 'development');
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    define('BASE_URL', 'https://domain-anda.com');
    define('ENVIRONMENT', 'production');
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>