<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';

// Cek method request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Method not allowed');
}

// Ambil parameter
$t = $_GET['t'] ?? '';
$id = $_GET['id'] ?? '';

if (empty($t) || empty($id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
    exit;
}

// Load table meta
$tables = require __DIR__ . '/../inc/table_meta.php';

if (!isset($tables[$t])) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Tabel tidak ditemukan']);
    exit;
}

$meta = $tables[$t];
$pk = $meta['pk'];
$joins = $meta['joins'] ?? [];

// Build SELECT clause dengan joins
$selectColumns = ["$t.*"];
$joinClauses = [];
$joinParams = [];

foreach ($joins as $joinTable => $joinInfo) {
    list($localKey, $foreignKey, $columns) = $joinInfo;
    foreach ($columns as $col) {
        $selectColumns[] = "$joinTable.$col AS {$joinTable}_$col";
    }
    $joinClauses[] = "LEFT JOIN $joinTable ON $t.$localKey = $joinTable.$foreignKey";
}

$selectSql = implode(', ', $selectColumns);
$joinSql = implode(' ', $joinClauses);

// Query data
$sql = "SELECT $selectSql FROM $t $joinSql WHERE $t.$pk = ? LIMIT 1";
$stmt = db()->prepare($sql);
$stmt->execute([$id]);
$data = $stmt->fetch();

if ($data) {
    // Process joined data
    $processedData = $data;
    foreach ($joins as $joinTable => $joinInfo) {
        list($localKey, $foreignKey, $columns) = $joinInfo;
        foreach ($columns as $col) {
            $joinedCol = "{$joinTable}_$col";
            if (isset($data[$joinedCol])) {
                $processedData[$col] = $data[$joinedCol];
            }
        }
    }
    
    echo json_encode(['success' => true, 'data' => $processedData]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
}