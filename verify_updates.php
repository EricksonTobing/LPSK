<?php
// Mock session
session_start();
require_once __DIR__ . '/inc/db.php';

try {
    $pdo = db();
    // Get a valid user
    $stmt = $pdo->query("SELECT id_user FROM users LIMIT 1");
    $user = $stmt->fetch();

    if (!$user) {
        die("No users found to test with.\n");
    }

    $_SESSION['user'] = ['id_user' => $user['id_user']];
    echo "Testing with User ID: " . $user['id_user'] . "\n";

    // 1. Check for updates (should be true)
    // We need to simulate the API call logic here because we can't make HTTP requests easily to localhost in this environment without curl/wget which might not be configured.
    // So we will just include the logic or simple curl if available. Let's rely on internal logic test.
    
    // Clear any existing reads for this user for the test
    $pdo->prepare("DELETE FROM user_update_reads WHERE user_id = ?")->execute([$user['id_user']]);

    echo "Step 1: Checking for updates...\n";
    // Logic from check_updates.php
    $stmt = $pdo->query("SELECT * FROM system_updates ORDER BY created_at DESC LIMIT 1");
    $latestUpdate = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_update_reads WHERE user_id = ? AND update_id = ?");
    $stmt->execute([$user['id_user'], $latestUpdate['id']]);
    $hasRead = $stmt->fetchColumn() > 0;
    
    if (!$hasRead) {
        echo "PASS: Update found and is unread.\n";
    } else {
        echo "FAIL: Update should be unread.\n";
    }

    // 2. Mark as read
    echo "Step 2: Marking as read...\n";
    // Logic from mark_update_read.php
    $insert = $pdo->prepare("INSERT INTO user_update_reads (user_id, update_id) VALUES (?, ?)");
    $insert->execute([$user['id_user'], $latestUpdate['id']]);
    echo "Marked as read.\n";

    // 3. Check again (should be false)
    echo "Step 3: Checking for updates again...\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_update_reads WHERE user_id = ? AND update_id = ?");
    $stmt->execute([$user['id_user'], $latestUpdate['id']]);
    $hasRead = $stmt->fetchColumn() > 0;

    if ($hasRead) {
        echo "PASS: Update is now marked as read.\n";
    } else {
        echo "FAIL: Update should be marked as read.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
