<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../inc/db.php';
require_once __DIR__ . '/../../inc/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']['id_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$updateId = $input['update_id'] ?? null;

if (!$updateId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing update_id']);
    exit;
}

$userId = $_SESSION['user']['id_user'];

try {
    $pdo = db();

    // Check if valid update_id
    $stmt = $pdo->prepare("SELECT id FROM system_updates WHERE id = ?");
    $stmt->execute([$updateId]);
    if (!$stmt->fetch()) {
         http_response_code(404);
         echo json_encode(['success' => false, 'error' => 'Update not found']);
         exit;
    }

    // Insert into user_update_reads (ignore if already exists)
    // MySQL handles INSERT IGNORE or ON DUPLICATE implicitly with unique constraint if present, 
    // but here we just check first or use INSERT IGNORE logic if we had unique key constraint.
    // For now simple check-then-insert is safer without unique constraint on (user_id, update_id) pair physically enforced (though logically implied)
    
    // Check first
    $check = $pdo->prepare("SELECT id FROM user_update_reads WHERE user_id = ? AND update_id = ?");
    $check->execute([$userId, $updateId]);
    if (!$check->fetch()) {
        $insert = $pdo->prepare("INSERT INTO user_update_reads (user_id, update_id) VALUES (?, ?)");
        $insert->execute([$userId, $updateId]);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
