<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_login();

// Tambahkan autoloader untuk PhpSpreadsheet
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Jika belum memilih format, tampilkan form pemilihan
if (!isset($_GET['fmt'])) {
    require __DIR__ . '/../inc/layout_header.php';
    require __DIR__ . '/../inc/layout_nav.php';
    
    $t = $_GET['t'] ?? '';
    $tables = require __DIR__ . '/../inc/table_meta.php';
    $title = isset($tables[$t]) ? $tables[$t]['label'] : 'Data';
    ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Export <?= e($title) ?></h2>
            
            <form method="get" action="export.php" class="space-y-6">
                <input type="hidden" name="fmt" value="xlsx">
                <input type="hidden" name="t" value="<?= e($t) ?>">
                
                <!-- Pilih Periode -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Periode</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="radio" id="semester_ini" name="periode" value="semester_ini" checked class="mr-2">
                            <label for="semester_ini" class="text-gray-700 dark:text-gray-300">Semester Ini (6 Bulan Terakhir)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="semester_lalu" name="periode" value="semester_lalu" class="mr-2">
                            <label for="semester_lalu" class="text-gray-700 dark:text-gray-300">Semester Lalu</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="tahun_ini" name="periode" value="tahun_ini" class="mr-2">
                            <label for="tahun_ini" class="text-gray-700 dark:text-gray-300">Tahun Ini</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="custom" name="periode" value="custom" class="mr-2">
                            <label for="custom" class="text-gray-700 dark:text-gray-300">Custom</label>
                        </div>
                    </div>
                </div>
                
                <!-- Custom Date Picker (hidden by default) -->
                <div id="custom_date_range" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Akhir</label>
                        <input type="date" id="end_date" name="end_date" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="table.php?t=<?= e($t) ?>" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const customDateRange = document.getElementById('custom_date_range');
        const radioButtons = document.querySelectorAll('input[name="periode"]');
        
        radioButtons.forEach(radio => {
            radio.addEventListener('change', function() {
                customDateRange.classList.toggle('hidden', this.value !== 'custom');
            });
        });
        
        // Set default dates for custom range
        const today = new Date();
        const endDate = today.toISOString().split('T')[0];
        
        const sixMonthsAgo = new Date();
        sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 6);
        const startDate = sixMonthsAgo.toISOString().split('T')[0];
        
        document.getElementById('start_date').value = startDate;
        document.getElementById('end_date').value = endDate;
    });
    </script>
    
    <?php
    require __DIR__ . '/../inc/layout_footer.php';
    exit;
}

// Jika sudah memilih format, proses export
$fmt = strtolower((string)($_GET['fmt'] ?? 'xlsx'));
$t = $_GET['t'] ?? '';
$periode = $_GET['periode'] ?? 'semester_ini';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$tables = require __DIR__ . '/../inc/table_meta.php';

function fetchData(string $t, array $tables, string $periode, string $start_date = '', string $end_date = ''): array {
    // Tentukan rentang tanggal berdasarkan periode
    $date_condition = '';
    $date_params = [];
    
    switch ($periode) {
        case 'semester_ini':
            $start_date = date('Y-m-d', strtotime('-6 months'));
            $end_date = date('Y-m-d');
            break;
            
        case 'semester_lalu':
            $start_date = date('Y-m-d', strtotime('-12 months'));
            $end_date = date('Y-m-d', strtotime('-6 months'));
            break;
            
        case 'tahun_ini':
            $start_date = date('Y-01-01');
            $end_date = date('Y-m-d');
            break;
            
        case 'custom':
            // Gunakan tanggal yang dipilih user
            if (empty($start_date)) $start_date = date('Y-m-d', strtotime('-6 months'));
            if (empty($end_date)) $end_date = date('Y-m-d');
            break;
    }
    
    if ($t === 'keuangan') {
        $q = trim((string)($_GET['q'] ?? ''));
        $where = $q ? "WHERE (p.nomor_kuintasi LIKE :q OR p.kode_anggaran LIKE :q OR a.nama_anggaran LIKE :q OR p.keterangan LIKE :q)" : "WHERE 1=1";
        
        // Tambahkan kondisi tanggal untuk keuangan
        $where .= " AND p.tanggal BETWEEN :start_date AND :end_date";
        
        $sql = "SELECT p.tanggal, p.nomor_kuintasi, p.kode_anggaran, a.nama_anggaran, p.jumlah, p.keterangan
                FROM pengeluaran p LEFT JOIN anggaran a ON a.kode_anggaran=p.kode_anggaran $where ORDER BY p.tanggal DESC";
        $stmt = db()->prepare($sql);
        if ($q) $stmt->bindValue(':q', "%$q%");
        $stmt->bindValue(':start_date', $start_date);
        $stmt->bindValue(':end_date', $end_date);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $headers = ['Tanggal','No Kuintasi','Kode Anggaran','Nama Anggaran','Jumlah','Keterangan'];
        return [$headers, $rows, $start_date, $end_date];
    }

    if (!isset($tables[$t])) { 
        http_response_code(400); 
        exit('Bad Request'); 
    }
    
    $meta = $tables[$t];
    $searchable = $meta['searchable'] ?? [];
    $filters = $meta['filters'] ?? [];

    $where = [];
    $params = [];
    $q = trim((string)($_GET['q'] ?? ''));
    
    if ($q !== '' && $searchable) {
        $like = "%$q%";
        $parts = [];
        foreach ($searchable as $s) { 
            $parts[] = "$s LIKE ?"; 
            $params[] = $like; 
        }
        $where[] = '(' . implode(' OR ', $parts) . ')';
    }
    
    foreach ($filters as $f) {
        if ($val = trim((string)($_GET[$f] ?? ''))) { 
            $where[] = "$f = ?"; 
            $params[] = $val; 
        }
    }
    
    // Tambahkan kondisi tanggal jika tabel memiliki kolom tanggal
    $date_column = '';
    if (in_array($t, ['permohonan', 'penelaahan', 'layanan', 'pengeluaran'])) {
        switch ($t) {
            case 'permohonan': $date_column = 'tgl_pengajuan'; break;
            case 'penelaahan': $date_column = 'tanggal_dispo'; break;
            case 'layanan': $date_column = 'tgl_mulai_layanan'; break;
            case 'pengeluaran': $date_column = 'tanggal'; break;
        }
        
        if ($date_column) {
            $where[] = "$date_column BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }
    }
    
    $whereSql = $where ? 'WHERE '.implode(' AND ', $where) : '';
    $stmt = db()->prepare("SELECT * FROM $t $whereSql ORDER BY 1 DESC");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $headers = array_keys($rows[0] ?? array_flip($meta['columns']));
    
    return [$headers, $rows, $start_date, $end_date];
}

[$headers, $rows, $start_date, $end_date] = fetchData($t, $tables, $periode, $start_date, $end_date);
$filename = ($t ?: 'data').'-'.date('Ymd-His');

if ($fmt === 'xlsx') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Tambahkan judul dengan informasi periode
    $sheet->setCellValue('A1', 'Laporan ' . ucfirst($t));
    $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)));
    $sheet->setCellValue('A3', 'Diekspor pada: ' . date('d/m/Y H:i:s'));
    
    // Set header tabel
    $colIdx = 1;
    $startRow = 5;
    foreach ($headers as $h) { 
        $sheet->setCellValueByColumnAndRow($colIdx++, $startRow, $h); 
    }
    
    // Isi data
    $ridx = $startRow + 1;
    foreach ($rows as $row) {
        $colIdx = 1;
        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($colIdx++, $ridx, $row[$h] ?? '');
        }
        $ridx++;
    }
    
    // Format header
    $headerStyle = [
        'font' => ['bold' => true],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6E6FA']]
    ];
    $sheet->getStyle('A'.$startRow.':'.chr(64+count($headers)).$startRow)->applyFromArray($headerStyle);
    
    // Auto size columns
    for ($i = 1; $i <= count($headers); $i++) {
        $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
    }
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename.xlsx\"");
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

http_response_code(400);
echo 'Format tidak didukung';