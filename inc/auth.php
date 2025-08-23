<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

function auth_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!auth_user()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void {
    require_login();
    if (($_SESSION['user']['role'] ?? 'user') !== 'admin') {
        http_response_code(403);
        exit('Forbidden: admin only');
    }
}

function login(string $username, string $password): bool {
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id_user' => (int)$user['id_user'],
            'username' => $user['username'],
            'nama_lengkap' => $user['nama_lengkap'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        session_regenerate_id(true);
        return true;
    }
    return false;
}

function logout(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
}
