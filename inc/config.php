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
// Include custom error handler
require_once __DIR__ . '/ensure_logs.php';

$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../.env.example';
}
$dotenv = Dotenv::createImmutable(dirname(__DIR__), basename($envFile));
$dotenv->load();

define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') === 'true');
define('APP_URL', rtrim($_ENV['APP_URL'] ?? '', '/'));
define('SESSION_NAME', $_ENV['SESSION_NAME'] ?? 'LPSKSESSID');
define('LOG_PATH', __DIR__ . '/../logs/error.log');

// Environment-specific configuration
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');

// Error reporting berdasarkan environment
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', LOG_PATH);
}

// Pastikan folder logs ada
if (!is_dir(dirname(LOG_PATH))) {
    mkdir(dirname(LOG_PATH), 0755, true);
    file_put_contents(dirname(LOG_PATH) . '/.htaccess', 'Deny from all');
    file_put_contents(dirname(LOG_PATH) . '/index.html', '');
}

// Rotasi log file
define('LOG_MAX_SIZE', 10485760); // 10MB

function shouldRotateLog(): bool {
    return file_exists(LOG_PATH) && filesize(LOG_PATH) > LOG_MAX_SIZE;
}

function rotateLog(): void {
    if (file_exists(LOG_PATH)) {
        $backupPath = dirname(LOG_PATH) . '/error-' . date('Y-m-d-His') . '.log';
        rename(LOG_PATH, $backupPath);
        
        // Hapus log backup yang lebih dari 30 hari
        $files = glob(dirname(LOG_PATH) . '/error-*.log');
        $now = time();
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > 30 * 24 * 60 * 60) {
                unlink($file);
            }
        }
    }
}

// Sanitasi data sensitif dalam log
function sanitizeForLog(string $message): string {
    $sensitivePatterns = [
        '/password=[^&\s]*/i' => 'password=***',
        '/token=[^&\s]*/i' => 'token=***',
        '/authorization:\s*\w+/i' => 'authorization: ***',
        '/(\d{16})/' => '****-****-****-****', // Kartu kredit
        '/(\d{3}-\d{2}-\d{4})/' => '***-**-****', // SSN
    ];
    
    foreach ($sensitivePatterns as $pattern => $replacement) {
        $message = preg_replace($pattern, $replacement, $message);
    }
    
    return $message;
}

// Monitoring dan Alerting
function notifyCriticalError(string $message): void {
    $criticalKeywords = ['FATAL_ERROR', 'CORE_ERROR', 'COMPILE_ERROR', 'database gagal'];
    
    foreach ($criticalKeywords as $keyword) {
        if (stripos($message, $keyword) !== false) {
            // Kirim email/notification (implementasi sesuai kebutuhan)
            error_log("CRITICAL_ERROR_ALERT: " . $message, 1, 'admin@example.com');
            break;
        }
    }
}

// Rate Limiting untuk Logging
class LogRateLimiter {
    private static $lastLogTime = 0;
    private static $logCount = 0;
    
    public static function shouldLog(): bool {
        $now = time();
        $timeWindow = 60; // 1 menit
        
        if ($now - self::$lastLogTime > $timeWindow) {
            self::$lastLogTime = $now;
            self::$logCount = 0;
        }
        
        self::$logCount++;
        
        // Maksimal 100 log per menit, sisanya di-discard
        return self::$logCount <= 100;
    }
}

// Custom error handler function
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Hanya tangani error yang termasuk dalam error_reporting
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE_ERROR',
        E_CORE_WARNING => 'CORE_WARNING',
        E_COMPILE_ERROR => 'COMPILE_ERROR',
        E_COMPILE_WARNING => 'COMPILE_WARNING',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED'
    ];
    
    $errorType = $errorTypes[$errno] ?? "UNKNOWN_ERROR ($errno)";
    
    // Format pesan error
    $message = sprintf(
        "[%s] %s: %s in %s on line %d",
        date('Y-m-d H:i:s'),
        $errorType,
        $errstr,
        $errfile,
        $errline
    );
    
    // Sanitasi dan rate limiting
    $sanitizedMessage = sanitizeForLog($message);
    if (LogRateLimiter::shouldLog()) {
        error_log($sanitizedMessage . PHP_EOL, 3, LOG_PATH);
    }
    
    // Jika dalam mode debug, tampilkan juga di output
    if (defined('APP_DEBUG') && APP_DEBUG) {
        return false; // Biarkan PHP menangani error secara default
    }
    
    return true; // Jangan biarkan PHP menangani error secara default
}

// Custom exception handler function
function customExceptionHandler($exception) {
    // Format pesan exception
    $message = sprintf(
        "[%s] EXCEPTION: %s in %s on line %d\nStack trace:\n%s",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    
    // Sanitasi dan log
    $sanitizedMessage = sanitizeForLog($message);
    error_log($sanitizedMessage . PHP_EOL, 3, LOG_PATH);
    notifyCriticalError($sanitizedMessage);
    
    // Jika dalam mode debug, tampilkan juga di output
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo "<pre>" . htmlspecialchars($message) . "</pre>";
    } else {
        http_response_code(500);
        echo "Terjadi kesalahan internal. Silakan coba lagi nanti.";
    }
    
    exit;
}

// Shutdown function untuk menangani fatal errors
function customShutdownHandler() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Format pesan error fatal
        $message = sprintf(
            "[%s] FATAL_ERROR: %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );
        
        // Sanitasi dan log
        $sanitizedMessage = sanitizeForLog($message);
        error_log($sanitizedMessage . PHP_EOL, 3, LOG_PATH);
        notifyCriticalError($sanitizedMessage);
        
        // Jika dalam mode debug, tampilkan juga di output
        if (defined('APP_DEBUG') && APP_DEBUG) {
            echo "<pre>" . htmlspecialchars($message) . "</pre>";
        } else {
            http_response_code(500);
            echo "Terjadi kesalahan fatal. Silakan coba lagi nanti.";
        }
    }
}

// Cek dan rotasi log sebelum menulis
if (shouldRotateLog()) {
    rotateLog();
}



if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => isset($_SERVER['HTTPS']),
    ]);
}