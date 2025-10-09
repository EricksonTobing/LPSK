<?php
// =======================================================
// Error Reporting untuk Debugging
// =======================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// =======================================================
// Set Header JSON
// =======================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// =======================================================
// Import Dependencies
// =======================================================
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';

// =======================================================
// Error Handler
// =======================================================
function sendJsonError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

function sendJsonSuccess($data) {
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

try {
    // =======================================================
    // Validasi Method
    // =======================================================
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJsonError('Method not allowed', 405);
    }

    // =======================================================
    // Ambil dan Validasi Parameter
    // =======================================================
    $ids = $_GET['ids'] ?? '';

    if (empty($ids)) {
        sendJsonSuccess([]);
    }

    // =======================================================
    // Process IDs
    // =======================================================
    $idArray = array_filter(array_map('trim', explode(',', $ids)));
    
    if (empty($idArray)) {
        sendJsonSuccess([]);
    }

    // Validasi bahwa semua ID adalah numeric
    foreach ($idArray as $id) {
        if (!is_numeric($id)) {
            sendJsonError('Invalid ID format: ' . $id);
        }
    }

    error_log("Processing jenis_perlindungan IDs: " . implode(', ', $idArray));

    // =======================================================
    // Query Database
    // =======================================================
    $placeholders = implode(',', array_fill(0, count($idArray), '?'));
    
    $sql = "SELECT kategori, sub_pilihan FROM jenis_perlindungan WHERE id IN ($placeholders) ORDER BY kategori, sub_pilihan";
    
    error_log("Executing SQL: $sql");
    
    $stmt = db()->prepare($sql);
    $stmt->execute($idArray);
    $jenisList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Query result: " . json_encode($jenisList, JSON_UNESCAPED_UNICODE));

    // =======================================================
    // Format Result
    // =======================================================
    $result = array_map(function($item) {
        return $item['sub_pilihan'];
    }, $jenisList);

    error_log("Final result: " . json_encode($result, JSON_UNESCAPED_UNICODE));

    // sendJsonSuccess($result);
    echo json_encode(['success' => true, 'names' => $result]);
exit;

} catch (PDOException $e) {
    error_log("Database error in get_jenis_perlindungan.php: " . $e->getMessage());
    sendJsonError('Terjadi kesalahan database: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    error_log("General error in get_jenis_perlindungan.php: " . $e->getMessage());
    sendJsonError('Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
?>