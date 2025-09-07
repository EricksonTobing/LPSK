<?php

declare(strict_types=1);

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

session_set_cookie_params([
    'lifetime' => 3600,                // Session berlaku 1 jam
    'path' => '/',                     // Berlaku untuk seluruh path domain
    'domain' => $_SERVER['HTTP_HOST'], // Hanya untuk domain ini
    'secure' => true,                  // Hanya dikirim lewat HTTPS
    'httponly' => true,                // Tidak bisa diakses JavaScript
    'samesite' => 'Strict'             // Cegah CSRF, hanya dikirim di domain sendiri
]);

// Mulai session
session_start();

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../.env.example';
}
$dotenv = Dotenv::createImmutable(dirname(__DIR__), basename($envFile));
$dotenv->load();

define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') === 'true');
define('APP_URL', rtrim($_ENV['APP_URL'] ?? '', '/'));
define('SESSION_NAME', $_ENV['SESSION_NAME'] ?? 'LPSKSESSID');

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => isset($_SERVER['HTTPS']),
    ]);
}


// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}
