<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';

require_login();

$tables = require __DIR__ . '/../inc/table_meta.php';

$t = $_GET['t'] ?? 'permohonan';
if (!isset($tables[$t]) || $t === 'users') {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Table not found']);
    exit;
}

$meta = $tables[$t];
$pk = $meta['pk'];
$joins = $meta['joins'] ?? [];

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID required']);
    exit;
}

try {
    // Build SELECT clause with joins
    $selectColumns = ["$t.*"];
    $joinClauses = [];
    
    foreach ($joins as $joinTable => $joinInfo) {
        list($localKey, $foreignKey, $columns) = $joinInfo;
        foreach ($columns as $col) {
            $selectColumns[] = "$joinTable.$col AS {$joinTable}_$col";
        }
        $joinClauses[] = "LEFT JOIN $joinTable ON $t.$localKey = $joinTable.$foreignKey";
    }
    
    $selectSql = implode(', ', $selectColumns);
    $joinSql = implode(' ', $joinClauses);
    
    $sql = "SELECT $selectSql FROM $t $joinSql WHERE $t.$pk = ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    
    if ($data) {
        // Process joined data to match expected field names
        $result = [];
        foreach ($data as $key => $value) {
            // Handle joined columns (convert joined_table_column back to column)
            if (strpos($key, '_') !== false) {
                $parts = explode('_', $key, 2);
                if (count($parts) === 2 && isset($joins[$parts[0]])) {
                    $result[$parts[1]] = $value;
                }
            }
            $result[$key] = $value;
        }
        
        echo json_encode(['success' => true, 'data' => $result]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data not found']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>