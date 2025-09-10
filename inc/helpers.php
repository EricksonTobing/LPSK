<?php
declare(strict_types=1);

function e(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function input(string $key, $default=null) {
    return $_REQUEST[$key] ?? $default;
}

function paginate_params($per_page = 10) {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $per_page;
    return [$page, $per_page, $offset];
}

function build_query(array $params, array $omit=[]): string {
    foreach ($omit as $o) unset($params[$o]);
    return http_build_query(array_filter($params, function($v) {
        return $v !== '' && $v !== null && $v !== [];
    }));
}

function build_export_query(array $params): string {
    $queryParams = [];
    foreach ($params as $key => $value) {
        if (!empty($value) || $value === '0') {
            $queryParams[$key] = $value;
        }
    }
    return http_build_query($queryParams);
}

function truncate_text(string $text, int $length = 50): string {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length - 3) . '...';
}

function debug_log($message) {
    error_log(date('Y-m-d H:i:s') . ' - ' . $message);
}

function error_log_message($message, $level = 'ERROR') {
    $logMessage = sprintf(
        "[%s] %s: %s",
        date('Y-m-d H:i:s'),
        $level,
        $message
    );
    
    // Gunakan sanitasi dari config.php jika tersedia
    if (function_exists('sanitizeForLog')) {
        $logMessage = sanitizeForLog($logMessage);
    }
    
    // Gunakan rate limiting dari config.php jika tersedia
    if (class_exists('LogRateLimiter') && !LogRateLimiter::shouldLog()) {
        return;
    }
    
    // Tulis ke file log
    error_log($logMessage . PHP_EOL, 3, LOG_PATH);
}

function is_assoc(array $arr) {
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST'];
    $base = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    
    return $protocol . $domain . $base . $path;
}

function redirect(string $url): void { header("Location: $url"); exit; }