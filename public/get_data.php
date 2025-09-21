<?php
// =======================================================
// Error Reporting untuk Debugging
// =======================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// =======================================================
// Set Content Type JSON dari awal
// =======================================================
header('Content-Type: application/json');

// =======================================================
// Import Dependencies
// =======================================================
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';

// =======================================================
// Error Handler untuk Output JSON
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
    // Validasi Method Request
    // =======================================================
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJsonError('Method not allowed', 405);
    }

    // =======================================================
    // Validasi Parameter Input
    // =======================================================
    $t = $_GET['t'] ?? '';
    $id = $_GET['id'] ?? '';

    if (empty($t)) {
        sendJsonError('Parameter tabel (t) tidak ditemukan');
    }

    if (empty($id)) {
        sendJsonError('Parameter ID tidak ditemukan');
    }

    // =======================================================
    // Load Table Metadata
    // =======================================================
    $tables = require __DIR__ . '/../inc/table_meta.php';

    if (!isset($tables[$t])) {
        sendJsonError('Tabel tidak ditemukan atau tidak diizinkan', 404);
    }

    $meta = $tables[$t];
    $pk = $meta['pk'];
    $joins = $meta['joins'] ?? [];

    // =======================================================
    // Build Query dengan Error Handling
    // =======================================================
    try {
    // Build SELECT clause dengan joins
    $selectColumns = ["$t.*"];
    $joinClauses = [];
    $alreadyJoined = [];

    // Auto-join ke pegawai jika ada kolom id_pegawai
    $tableColumns = array_keys($meta['columns']);
    if (in_array('id_pegawai', $tableColumns) && !in_array('pegawai', $alreadyJoined)) {
        $selectColumns[] = "pegawai.nama_pegawai AS pegawai_nama_pegawai";
        $joinClauses[] = "LEFT JOIN pegawai ON $t.id_pegawai = pegawai.id_pegawai";
        $alreadyJoined[] = 'pegawai';
    }

    // Process configured joins
    foreach ($joins as $joinTable => $joinInfo) {
        if (count($joinInfo) >= 3 && !in_array($joinTable, $alreadyJoined)) {
            list($localKey, $foreignKey, $columns) = $joinInfo;
            foreach ($columns as $col) {
                $selectColumns[] = "$joinTable.$col AS {$joinTable}_$col";
            }
            $joinClauses[] = "LEFT JOIN $joinTable ON $t.$localKey = $joinTable.$foreignKey";
            $alreadyJoined[] = $joinTable;
        }
    }

    $selectSql = implode(', ', $selectColumns);
    $joinSql = implode(' ', $joinClauses);

        // =======================================================
        // Execute Main Query
        // =======================================================
         $sql = "SELECT $selectSql FROM $t $joinSql WHERE $t.$pk = ? LIMIT 1";$alreadyJoined[] = $joinTable; $alreadyJoined[] = 'pegawai';
    
    error_log("Executing SQL: $sql with ID: $id");
    
    $stmt = db()->prepare($sql);
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // =======================================================
        // Process Joined Data
        // =======================================================
        $processedData = $data;
        foreach ($joins as $joinTable => $joinInfo) {
            if (count($joinInfo) >= 3) {
                list($localKey, $foreignKey, $columns) = $joinInfo;
                foreach ($columns as $col) {
                    $joinedCol = "{$joinTable}_$col";
                    if (isset($data[$joinedCol])) {
                        $processedData[$col] = $data[$joinedCol];
                    }
                }
            }
        }

        // =======================================================
        // Handle Special Cases: jenis_perlindungan
        // =======================================================
        if ($t === 'permohonan') {
    try {
        $stmt = db()->prepare("SELECT GROUP_CONCAT(id_perlindungan) as jenis_ids 
                              FROM permohonan_perlindungan 
                              WHERE no_reg_medan = ?");
        $stmt->execute([$id]);
        $relasiData = $stmt->fetch(PDO::FETCH_ASSOC);
        $processedData['jenis_perlindungan'] = $relasiData['jenis_ids'] ?? '';
    } catch (Exception $e) {
        error_log("Error getting jenis_perlindungan for permohonan: " . $e->getMessage());
        $processedData['jenis_perlindungan'] = '';
    }
} elseif ($t === 'layanan') {
    try {
        $stmt = db()->prepare("SELECT GROUP_CONCAT(id_perlindungan) as jenis_ids 
                              FROM layanan_perlindungan 
                              WHERE no_kep_smpl = ?");
        $stmt->execute([$id]);
        $relasiData = $stmt->fetch(PDO::FETCH_ASSOC);
        $processedData['jenis_perlindungan'] = $relasiData['jenis_ids'] ?? '';
    } catch (Exception $e) {
        error_log("Error getting jenis_perlindungan for layanan: " . $e->getMessage());
        $processedData['jenis_perlindungan'] = '';
    }
}

        // =======================================================
        // Log Final Data untuk Debugging
        // =======================================================
        error_log("Final processed data: " . json_encode($processedData, JSON_UNESCAPED_UNICODE));

        // =======================================================
        // Return Success Response
        // =======================================================
        sendJsonSuccess($processedData);

    } catch (PDOException $e) {
        error_log("Database error in get_data.php: " . $e->getMessage());
        sendJsonError('Terjadi kesalahan database: ' . $e->getMessage(), 500);
    }

} catch (Exception $e) {
    error_log("General error in get_data.php: " . $e->getMessage());
    sendJsonError('Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
?>