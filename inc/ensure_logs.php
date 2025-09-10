<?php
// Script untuk memastikan folder logs ada dan dapat ditulisi
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
    file_put_contents($logDir . '/.htaccess', 'Deny from all');
    file_put_contents($logDir . '/index.html', '');
}

// Cek apakah folder logs dapat ditulisi
if (!is_writable($logDir)) {
    chmod($logDir, 0755);
}

// Cek apakah file error.log ada dan dapat ditulisi
$logFile = $logDir . '/error.log';
if (!file_exists($logFile)) {
    file_put_contents($logFile, '');
    chmod($logFile, 0644);
}