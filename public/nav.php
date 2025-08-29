<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';

try {
    $db = db();
    echo "Database connection OK\n";
    
    $tables = require __DIR__ . '/../inc/table_meta.php';
    echo "Table meta loaded: " . count($tables) . " tables\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>