<?php
require_once __DIR__ . '/inc/db.php';

try {
    $pdo = db();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `system_updates` (
      `id` int NOT NULL AUTO_INCREMENT,
      `version` varchar(50) NOT NULL,
      `title` varchar(255) NOT NULL,
      `description` text,
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS `user_update_reads` (
      `id` int NOT NULL AUTO_INCREMENT,
      `user_id` int NOT NULL,
      `update_id` int NOT NULL,
      `read_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
      FOREIGN KEY (`update_id`) REFERENCES `system_updates` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "Tables created successfully.\n";

    // Insert a test update if none exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM system_updates");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO system_updates (version, title, description) VALUES ('1.0.0', 'Sistem Update v1.0', 'Ini adalah update sistem pertama. Fitur baru: Notifikasi Update.')");
        echo "Test update inserted.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
