<?php
declare(strict_types=1);

function e(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function input(string $key, $default=null) {
    return $_REQUEST[$key] ?? $default;
}

function paginate_params(): array {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per  = min(100, max(5, (int)($_GET['per'] ?? 10)));
    $offset = ($page - 1) * $per;
    return [$page, $per, $offset];
}

function build_query(array $params, array $omit=[]): string {
    foreach ($omit as $o) unset($params[$o]);
    return http_build_query(array_filter($params, fn($v)=>$v!=='' && $v!==null));
}

function truncate_text(string $text, int $length = 50): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

function redirect(string $url): void { header("Location: $url"); exit; }
