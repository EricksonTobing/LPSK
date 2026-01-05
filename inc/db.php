<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $_ENV['DB_HOST'] ?? '127.0.0.1',
        $_ENV['DB_PORT'] ?? '3306',
        $_ENV['DB_NAME'] ?? 'lpskbckp'
    );

    $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASS'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Audit Trail Context Injection
    if (session_status() === PHP_SESSION_NONE) {
        // Start session if not already started to access user data
        session_start();
    }

    if (isset($_SESSION['user']) && isset($_SESSION['user']['id_user'])) {
        $userId = $_SESSION['user']['id_user'];
        $userName = $_SESSION['user']['username'] ?? 'unknown'; // Use username as key identifier
        
        // Escape logic handled by prepare/execute to prevent injection
        // Using direct query for session variables set
        try {
            $stmt = $pdo->prepare("SET @audit_user_id = ?, @audit_user_name = ?");
            $stmt->execute([$userId, $userName]);
        } catch (Exception $e) {
            // Silently fail to not block app execution if audit set fails
            error_log("Failed to set audit variables: " . $e->getMessage());
        }
    }

    return $pdo;
}
