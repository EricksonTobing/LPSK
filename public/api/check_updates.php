<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../inc/db.php';
require_once __DIR__ . '/../../inc/auth.php'; // Assuming this handles session start and user check

header('Content-Type: application/json');

if (!isset($_SESSION['user']['id_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user']['id_user'];

try {
    $pdo = db();

    // Get the latest system update
    $stmt = $pdo->query("SELECT * FROM system_updates ORDER BY created_at DESC LIMIT 1");
    $latestUpdate = $stmt->fetch();

    if (!$latestUpdate) {
        echo json_encode(['success' => true, 'has_update' => false]);
        exit;
    }

    // Check if the user has read this update
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_update_reads WHERE user_id = ? AND update_id = ?");
    $stmt->execute([$userId, $latestUpdate['id']]);
    $hasRead = $stmt->fetchColumn() > 0;

    if ($hasRead) {
        echo json_encode(['success' => true, 'has_update' => false]);
    } else {
        echo json_encode([
            'success' => true, 
            'has_update' => true, 
            'update' => $latestUpdate
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
