<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_login();

// Ambil parameter
$fmt = strtolower($_GET['fmt'] ?? 'xlsx');
$tahun_filter = $_GET['tahun'] ?? '';

// Hitung total anggaran dan pengeluaran
$total_anggaran = 0;
$total_pengeluaran = 0;

$anggaran_sql = "SELECT SUM(total_anggaran) as total FROM anggaran";
$pengeluaran_sql = "SELECT SUM(jumlah) as total FROM pengeluaran";

$anggaran_params = [];
$pengeluaran_params = [];

if (!empty($tahun_filter)) {
    $anggaran_sql .= " WHERE tahun = ?";
    $anggaran_params[] = $tahun_filter;
    
    $pengeluaran_sql .= " WHERE tahun = ?";
    $pengeluaran_params[] = $tahun_filter;
}

$anggaran_stmt = db()->prepare($anggaran_sql);
$anggaran_stmt->execute($anggaran_params);
$total_anggaran = (float)($anggaran_stmt->fetchColumn() ?? 0);

$pengeluaran_stmt = db()->prepare($pengeluaran_sql);
$pengeluaran_stmt->execute($pengeluaran_params);
$total_pengeluaran = (float)($pengeluaran_stmt->fetchColumn() ?? 0);

$sisa_anggaran = $total_anggaran - $total_pengeluaran;

// Data untuk export
$ringkasan_data = [
    'Periode' => $tahun_filter ?: 'Semua Tahun',
    'Total Anggaran' => $total_anggaran,
    'Total Pengeluaran' => $total_pengeluaran,
    'Sisa Anggaran' => $sisa_anggaran,
    'Persentase Terpakai' => $total_anggaran > 0 ? ($total_pengeluaran / $total_anggaran) * 100 : 0
];

// Header berdasarkan format
$filename = "ringkasan-keuangan-" . ($tahun_filter ?: 'all') . "-" . date('Ymd-His');

switch ($fmt) {
    case 'xlsx':
        exportExcel($ringkasan_data, $filename);
        break;
    case 'csv':
        exportCSV($ringkasan_data, $filename);
        break;
    case 'pdf':
        exportPDF($ringkasan_data, $filename);
        break;
    default:
        header('Content-Type: text/plain');
        echo "Format tidak didukung";
        exit;
}

function exportExcel($data, $filename) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
    
    // Simple Excel output menggunakan HTML table (untuk implementasi nyata gunakan library seperti PhpSpreadsheet)
    echo "<table border='1'>";
    echo "<tr><th>Item</th><th>Nilai</th></tr>";
    foreach ($data as $key => $value) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($key) . "</td>";
        if (is_numeric($value) && $key !== 'Periode') {
            echo "<td>Rp " . number_format($value, 0, ',', '.') . "</td>";
        } else {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    exit;
}

function exportCSV($data, $filename) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Item', 'Nilai']);
    foreach ($data as $key => $value) {
        fputcsv($output, [$key, $value]);
    }
    fclose($output);
    exit;
}

function exportPDF($data, $filename) {
    // Implementasi PDF export (gunakan library seperti TCPDF atau Dompdf)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
    
    // Simple HTML output sebagai placeholder
    echo "<h1>Ringkasan Keuangan</h1>";
    foreach ($data as $key => $value) {
        echo "<p><strong>$key:</strong> $value</p>";
    }
    exit;
}
?>