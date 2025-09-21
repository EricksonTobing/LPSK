<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';

// Cek method request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Method not allowed');
}

// Ambil parameter
$id = $_GET['id'] ?? '';

if (empty($id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID pegawai tidak ditemukan']);
    exit;
}

// Query data pegawai
try {
    $sql = "SELECT nama_pegawai FROM pegawai WHERE id_pegawai = ? LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->execute([$id]);
    $pegawai = $stmt->fetch();

    if ($pegawai) {
        echo json_encode(['success' => true, 'nama_pegawai' => $pegawai['nama_pegawai']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Pegawai tidak ditemukan']);
    }
} catch (Exception $e) {
    error_log("Error in get_pegawai.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}