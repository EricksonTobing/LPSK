<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';

$t = $_GET['t'] ?? 'permohonan';
$tables = require __DIR__ . '/../inc/table_meta.php';
$meta = $tables[$t];

echo "<h1>Debug Table: $t</h1>";
echo "<h2>Columns:</h2>";
echo "<pre>";
print_r($meta['columns']);
echo "</pre>";

echo "<h2>Database Structure:</h2>";
try {
    $stmt = db()->query("DESCRIBE $t");
    $structure = $stmt->fetchAll();
    echo "<pre>";
    print_r($structure);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>