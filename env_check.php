<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';

try {
    $db = db();
    echo "✅ Database connected successfully!<br>";
    echo "Database name: " . $db->query("SELECT DATABASE()")->fetchColumn();
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}
?>