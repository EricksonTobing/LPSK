<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';

try {
    $pdo = db();
    
    echo "Checking min/max dates...\n";
    
    $tables = ['permohonan' => 'tgl_pengajuan', 'penelaahan' => 'tanggal_dispo', 'pengeluaran' => 'tanggal'];
    
    foreach ($tables as $table => $col) {
        $stmt = $pdo->query("SELECT MIN($col) as min_date, MAX($col) as max_date FROM $table");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "$table: " . ($row['min_date'] ?? 'NULL') . " to " . ($row['max_date'] ?? 'NULL') . "\n";
        
        // Count by year
        $stmt = $pdo->query("SELECT YEAR($col) as yr, COUNT(*) as c FROM $table GROUP BY yr ORDER BY yr");
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  Year " . $r['yr'] . ": " . $r['c'] . " rows\n";
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
