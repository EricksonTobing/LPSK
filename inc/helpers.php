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

function is_assoc(array $arr) {
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function redirect(string $url): void { header("Location: $url"); exit; }
