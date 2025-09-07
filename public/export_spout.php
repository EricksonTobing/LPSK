<?php
// Tingkatkan limit memory dan waktu eksekusi untuk dataset besar
ini_set('memory_limit', '512M');
set_time_limit(300); // 5 menit

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_login();

// Pastikan tidak ada output sebelum header
if (ob_get_level() > 0) {
    ob_end_clean();
}

// Cek apakah Spout tersedia sebelum digunakan
$autoload_file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload_file)) {
    header('Content-Type: text/plain');
    http_response_code(500);
    exit('Spout library tidak ditemukan. Install dengan: composer require box/spout');
}

require_once $autoload_file;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Type;

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
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white flex items-center">
                <i class="fas fa-file-export mr-3 text-blue-600"></i>
                Export <?= e($title) ?> (Spout)
            </h2>
            
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Export dengan Spout:</strong> Lebih cepat dan hemat memori untuk dataset besar. 
                            Cocok untuk data dengan ribuan hingga jutaan baris.
                        </p>
                    </div>
                </div>
            </div>
            
            <form method="get" action="export_spout.php" class="space-y-6">
                <input type="hidden" name="t" value="<?= e($t) ?>">
                
                <!-- Pilih Format -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Format Export</label>
                    <div class="space-y-2">
                        <div class="flex items-center p-3 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                            <input type="radio" id="format_xlsx" name="fmt" value="xlsx" checked class="mr-3 text-blue-600">
                            <div class="flex-1">
                                <label for="format_xlsx" class="text-gray-700 dark:text-gray-300 font-medium cursor-pointer">(XLSX)</label>
                                <p class="text-xs text-gray-500 mt-1">Optimal untuk data besar, dengan formatting yang baik</p> <span class="text-red-500">Masih perbaikan </span>
                            </div>
                            <i class="fas fa-file-excel text-green-600"></i>
                        </div>
                        <div class="flex items-center p-3 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                            <input type="radio" id="format_csv" name="fmt" value="csv" class="mr-3 text-blue-600">
                            <div class="flex-1">
                                <label for="format_csv" class="text-gray-700 dark:text-gray-300 font-medium cursor-pointer">CSV</label>
                                <p class="text-xs text-gray-500 mt-1">Paling cepat dan ringan untuk dataset sangat besar</p>
                            </div>
                            <i class="fas fa-file-csv text-blue-600"></i>
                        </div>
                        <div class="flex items-center p-3 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                            <input type="radio" id="format_ods" name="fmt" value="ods" class="mr-3 text-blue-600">
                            <div class="flex-1">
                                <label for="format_ods" class="text-gray-700 dark:text-gray-300 font-medium cursor-pointer">OpenDocument (ODS)</label>
                                <p class="text-xs text-gray-500 mt-1">Format terbuka, kompatibel dengan LibreOffice</p><span class="text-red-500">Masih perbaikan </span>
                            </div>
                            <i class="fas fa-file-alt text-orange-600"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Pilih Periode -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Periode Data</label>
                    <div class="space-y-2">
                        <div class="flex items-center p-2 rounded">
                            <input type="radio" id="semester_ini" name="periode" value="semester_ini" checked class="mr-2">
                            <label for="semester_ini" class="text-gray-700 dark:text-gray-300">Semester Ini (6 Bulan Terakhir)</label>
                        </div>
                        <div class="flex items-center p-2 rounded">
                            <input type="radio" id="semester_lalu" name="periode" value="semester_lalu" class="mr-2">
                            <label for="semester_lalu" class="text-gray-700 dark:text-gray-300">Semester Lalu</label>
                        </div>
                        <div class="flex items-center p-2 rounded">
                            <input type="radio" id="tahun_ini" name="periode" value="tahun_ini" class="mr-2">
                            <label for="tahun_ini" class="text-gray-700 dark:text-gray-300">Tahun Ini</label>
                        </div>
                        <div class="flex items-center p-2 rounded">
                            <input type="radio" id="semua_data" name="periode" value="semua_data" class="mr-2">
                            <label for="semua_data" class="text-gray-700 dark:text-gray-300">Semua Data</label>
                        </div>
                        <div class="flex items-center p-2 rounded">
                            <input type="radio" id="custom" name="periode" value="custom" class="mr-2">
                            <label for="custom" class="text-gray-700 dark:text-gray-300">Rentang Tanggal Kustom</label>
                        </div>
                    </div>
                </div>
                
                <!-- Custom Date Picker -->
                <div id="custom_date_range" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Akhir</label>
                        <input type="date" id="end_date" name="end_date" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                
                <!-- Advanced Options -->
                <div class="border-t pt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Opsi Lanjutan</h4>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="checkbox" id="include_summary" name="include_summary" value="1" class="mr-2" checked>
                            <label for="include_summary" class="text-sm text-gray-700 dark:text-gray-300">Sertakan ringkasan data</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="format_numbers" name="format_numbers" value="1" class="mr-2" checked>
                            <label for="format_numbers" class="text-sm text-gray-700 dark:text-gray-300">Format angka otomatis</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="freeze_header" name="freeze_header" value="1" class="mr-2" checked>
                            <label for="freeze_header" class="text-sm text-gray-700 dark:text-gray-300">Bekukan baris header (Excel only)</label>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="table.php?t=<?= e($t) ?>" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all shadow-lg hover:shadow-xl">
                        <i class="fas fa-download mr-2"></i>Export Data
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
$include_summary = !empty($_GET['include_summary']);
$format_numbers = !empty($_GET['format_numbers']);
$freeze_header = !empty($_GET['freeze_header']);

// Validasi input
if (empty($t)) {
    header('Content-Type: text/plain');
    http_response_code(400);
    exit('Parameter tabel (t) tidak boleh kosong');
}

$tables = require __DIR__ . '/../inc/table_meta.php';

// Fungsi untuk memeriksa dan membersihkan output buffer
function cleanOutputBuffers() {
    while (ob_get_level() > 0) {
        $status = ob_get_status();
        if (isset($status['name']) && $status['name'] === 'default output handler') {
            break;
        }
        ob_end_clean();
    }
}

if ($t === 'ringkasan') {
    try {
        // Ambil parameter tahun jika ada
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
        $persentase_terpakai = $total_anggaran > 0 ? ($total_pengeluaran / $total_anggaran) * 100 : 0;

        // Siapkan data ringkasan
        $ringkasan_data = [
            ['Item', 'Nilai'],
            ['Periode', $tahun_filter ?: 'Semua Tahun'],
            ['Total Anggaran', $total_anggaran],
            ['Total Pengeluaran', $total_pengeluaran],
            ['Sisa Anggaran', $sisa_anggaran],
            ['Persentase Terpakai', round($persentase_terpakai, 2) . '%']
        ];

        // Tentukan nama file dan content type
        $filename = 'ringkasan-keuangan-' . ($tahun_filter ?: 'all') . '-' . date('Ymd-His');
        
        switch ($fmt) {
            case 'xlsx':
                $filename .= '.xlsx';
                $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
                
            case 'csv':
                $filename .= '.csv';
                $contentType = 'text/csv; charset=UTF-8';
                break;
                
            case 'ods':
                $filename .= '.ods';
                $contentType = 'application/vnd.oasis.opendocument.spreadsheet';
                break;
                
            default:
                throw new Exception('Format tidak didukung: ' . $fmt);
        }

        // Bersihkan output buffer sebelum mengirim header
        cleanOutputBuffers();
        
        // Set headers
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Buat writer
        switch ($fmt) {
            case 'xlsx':
                $writer = WriterEntityFactory::createXLSXWriter();
                break;
                
            case 'csv':
                $writer = WriterEntityFactory::createCSVWriter();
                $writer->setShouldAddBOM(true); // Tambah BOM untuk UTF-8
                break;
                
            case 'ods':
                $writer = WriterEntityFactory::createODSWriter();
                break;
        }

        $writer->openToFile('php://output');

        // Style untuk header
        if ($fmt !== 'csv') {
            $headerStyle = (new StyleBuilder())
                ->setFontBold()
                ->setFontSize(12)
                ->setFontColor(Color::WHITE)
                ->setBackgroundColor(Color::BLUE)
                ->setCellAlignment(CellAlignment::CENTER)
                ->build();
        }

        // Tulis data ringkasan
        foreach ($ringkasan_data as $index => $rowData) {
            $row = WriterEntityFactory::createRowFromArray($rowData);
            
            // Apply style untuk header (baris pertama)
            if ($index === 0 && $fmt !== 'csv' && isset($headerStyle)) {
                $row->setStyle($headerStyle);
            }
            
            $writer->addRow($row);
        }

        $writer->close();
        exit;

    } catch (Exception $e) {
        cleanOutputBuffers();
        header('Content-Type: text/plain');
        http_response_code(500);
        exit('Error exporting ringkasan: ' . $e->getMessage());
    }
}

if (!isset($tables[$t])) {
    cleanOutputBuffers();
    header('Content-Type: text/plain');
    http_response_code(400);
    exit('Tabel tidak ditemukan');
}

function fetchDataSpout(string $t, array $tables, string $periode, string $start_date = '', string $end_date = ''): array {
    try {
        // Tentukan rentang tanggal berdasarkan periode
        $current_year = date('Y');
        
        switch ($periode) {
            case 'semester_ini':
                // Semester ini: Jan-Juni atau Juli-Des berdasarkan bulan sekarang
                $current_month = date('n');
                if ($current_month >= 1 && $current_month <= 6) {
                    // Semester 1 (Jan-Juni)
                    $start_date = date('Y-01-01');
                    $end_date = date('Y-06-30');
                } else {
                    // Semester 2 (Juli-Des)
                    $start_date = date('Y-07-01');
                    $end_date = date('Y-12-31');
                }
                break;
                
            case 'semester_lalu':
                // Semester lalu: lawan dari semester ini
                $current_month = date('n');
                if ($current_month >= 1 && $current_month <= 6) {
                    // Sekarang semester 1, semester lalu adalah semester 2 tahun lalu
                    $last_year = date('Y') - 1;
                    $start_date = $last_year . '-07-01';
                    $end_date = $last_year . '-12-31';
                } else {
                    // Sekarang semester 2, semester lalu adalah semester 1 tahun ini
                    $start_date = date('Y-01-01');
                    $end_date = date('Y-06-30');
                }
                break;
                
            case 'tahun_ini':
                $start_date = date('Y-01-01');
                $end_date = date('Y-m-d');
                break;
                
            case 'semua_data':
                $start_date = '';
                $end_date = '';
                break;
                
            case 'custom':
                // Gunakan tanggal yang dipilih user
                if (empty($start_date)) $start_date = date('Y-m-d', strtotime('-6 months'));
                if (empty($end_date)) $end_date = date('Y-m-d');
                break;
                
            default:
                $start_date = date('Y-m-d', strtotime('-6 months'));
                $end_date = date('Y-m-d');
                break;
        }
        
        // Validasi format tanggal jika ada
        if ($start_date && !DateTime::createFromFormat('Y-m-d', $start_date)) {
            throw new Exception('Format tanggal mulai tidak valid');
        }
        if ($end_date && !DateTime::createFromFormat('Y-m-d', $end_date)) {
            throw new Exception('Format tanggal akhir tidak valid');
        }
        
        
        $meta = $tables[$t];
        $searchable = $meta['searchable'] ?? [];
        $filters = $meta['filters'] ?? [];
        $joins = $meta['joins'] ?? [];
        $colLabels = $meta['columns'] ?? [];
        
        // Build SELECT clause with joins - PERBAIKAN KHUSUS UNTUK PENGELUARAN
        if ($t === 'pengeluaran') {
            // Query khusus untuk pengeluaran dengan JOIN yang benar
            $selectColumns = [
                "p.nomor_kuintasi",
                "p.kode_anggaran", 
                "p.tahun",
                "p.jumlah",
                "p.tanggal",
                "p.kode_mak",
                "p.keterangan",
                "a.nama_anggaran",
                "m.nama_mak"
            ];
            $selectSql = implode(', ', $selectColumns);
            $joinSql = "LEFT JOIN anggaran a ON p.kode_anggaran = a.kode_anggaran AND p.tahun = a.tahun 
                       LEFT JOIN mak m ON p.kode_mak = m.kode_mak";
        } else {
            // Logic JOIN original untuk tabel lain
            $selectColumns = ["$t.*"];
            $joinClauses = [];
            
            foreach ($joins as $joinTable => $joinInfo) {
                list($localKey, $foreignKey, $columns) = $joinInfo;
                foreach ($columns as $col) {
                    $selectColumns[] = "$joinTable.$col AS {$joinTable}_$col";
                }
                $joinClauses[] = "LEFT JOIN $joinTable ON $t.$localKey = $joinTable.$foreignKey";
            }
            
            $selectSql = implode(', ', $selectColumns);
            $joinSql = implode(' ', $joinClauses);
        }
        
        $where = [];
        $params = [];
        $q = trim((string)($_GET['q'] ?? ''));
        
        // Search functionality - PERBAIKAN UNTUK PENGELUARAN
        if ($q !== '' && !empty($searchable)) {
            $like = "%$q%";
            $searchParts = [];
            foreach ($searchable as $searchCol) {
                if ($t === 'pengeluaran') {
                    // Mapping kolom pencarian untuk pengeluaran
                    $searchMapping = [
                        'nomor_kuintasi' => 'p.nomor_kuintasi',
                        'kode_anggaran' => 'p.kode_anggaran',
                        'keterangan' => 'p.keterangan'
                    ];
                    if (isset($searchMapping[$searchCol])) {
                        $searchParts[] = $searchMapping[$searchCol] . " LIKE ?";
                        $params[] = $like;
                    }
                } else {
                    // Logic original untuk tabel lain
                    $searchTable = $t;
                    foreach ($joins as $joinTable => $joinInfo) {
                        if (in_array($searchCol, $joinInfo[2])) {
                            $searchTable = $joinTable;
                            break;
                        }
                    }
                    $searchParts[] = "$searchTable.$searchCol LIKE ?";
                    $params[] = $like;
                }
            }
            if (!empty($searchParts)) {
                $where[] = '(' . implode(' OR ', $searchParts) . ')';
            }
        }
        
        // Filter functionality - PERBAIKAN UNTUK PENGELUARAN
        foreach ($filters as $filterCol) {
            $filterName = str_replace('/', '_', $filterCol);
            $filterValue = trim((string)($_GET[$filterName] ?? ''));
            
            if ($filterValue !== '') {
                if ($t === 'pengeluaran') {
                    // Mapping filter untuk pengeluaran
                    $filterMapping = [
                        'kode_anggaran' => 'p.kode_anggaran',
                        'kode_mak' => 'p.kode_mak',
                        'tahun' => 'p.tahun'
                    ];
                    if (isset($filterMapping[$filterCol])) {
                        $where[] = $filterMapping[$filterCol] . " = ?";
                        $params[] = $filterValue;
                    }
                } else {
                    // Logic original untuk tabel lain
                    $filterTable = $t;
                    foreach ($joins as $joinTable => $joinInfo) {
                        if (in_array($filterCol, $joinInfo[2])) {
                            $filterTable = $joinTable;
                            break;
                        }
                    }
                    $where[] = "$filterTable.$filterCol = ?";
                    $params[] = $filterValue;
                }
            }
        }
        
        // Date range filter
        if ($periode !== 'semua_data' && $start_date && $end_date) {
            $date_columns_map = [
                'permohonan' => 'tgl_pengajuan',
                'penelaahan' => 'tanggal_dispo', 
                'layanan' => 'tgl_mulai_layanan',
                'pengeluaran' => 'p.tanggal' // PERBAIKAN: gunakan alias p
            ];
            
            if (isset($date_columns_map[$t])) {
                $date_column = $date_columns_map[$t];
                $where[] = "$date_column BETWEEN ? AND ?";
                $params[] = $start_date;
                $params[] = $end_date;
            }
        }
        
        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Build final query - PERBAIKAN ORDER BY
        if ($t === 'pengeluaran') {
    $finalQuery = "SELECT $selectSql FROM pengeluaran p $joinSql $whereSql ORDER BY p.tanggal DESC, p.nomor_kuintasi DESC";
} else {
    // Tentukan kolom tanggal default berdasarkan nama tabel
    $dateColumnMap = [
        'permohonan' => 'tgl_pengajuan',
        'penelaahan' => 'tanggal_dispo',
        'layanan' => 'tgl_mulai_layanan',
        'anggaran' => 'tahun', // atau 'tahun' jika ingin tahun
    ];

    $orderColumn = $dateColumnMap[$t] ?? 'created_at'; // fallback ke created_at

    $finalQuery = "SELECT DISTINCT $selectSql FROM $t $joinSql $whereSql ORDER BY $orderColumn DESC";
}
        $dateColumnMap = [
    'permohonan'    => 'tgl_pengajuan',
    'penelaahan'    => 'tanggal_dispo',
    'layanan'       => 'tgl_mulai_layanan',
    'pengeluaran'   => 'tanggal',
    'anggaran'      => 'tahun',
    'mak'           => 'created_at',
    'pegawai'       => 'created_at',
    'users'         => 'created_at',
];

        error_log("Spout Export Query: " . $finalQuery);
        error_log("Spout Export Params: " . print_r($params, true));
        
        $stmt = db()->prepare($finalQuery);
        $stmt->execute($params);
        
        // Hitung total untuk summary - PERBAIKAN COUNT
        if ($t === 'pengeluaran') {
            $totalStmt = db()->prepare("SELECT COUNT(*) FROM pengeluaran p $joinSql $whereSql");
        } else {
            $totalStmt = db()->prepare("SELECT COUNT(DISTINCT $t.{$meta['pk']}) FROM $t $joinSql $whereSql");
        }
        $totalStmt->execute($params);
        $totalRows = (int)$totalStmt->fetchColumn();
        
        $headers = array_values($colLabels);
        $headerKeys = array_keys($colLabels);
        
        return [$stmt, $headers, $headerKeys, $totalRows, $start_date, $end_date, $joins];
        
    } catch (Exception $e) {
        error_log("Error in fetchDataSpout: " . $e->getMessage());
        throw new Exception("Gagal mengambil data: " . $e->getMessage());
    }
}

try {
    [$stmt, $headers, $headerKeys, $totalRows, $start_date, $end_date, $joins] = fetchDataSpout($t, $tables, $periode, $start_date, $end_date);
    
    $filename = ($t ?: 'data') . '-' . date('Ymd-His');
    $meta = $tables[$t];
    $tableLabel = $meta['label'] ?? ucfirst($t);
    
    // Tentukan tipe file berdasarkan format
    switch ($fmt) {
        case 'xlsx':
            $writer = WriterEntityFactory::createXLSXWriter();
            $filename .= '.xlsx';
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            break;
            
        case 'csv':
            $writer = WriterEntityFactory::createCSVWriter();
            $writer->setShouldAddBOM(true); // Tambah BOM untuk UTF-8
            $filename .= '.csv';
            $contentType = 'text/csv; charset=UTF-8';
            break;
            
        case 'ods':
            $writer = WriterEntityFactory::createODSWriter();
            $filename .= '.ods';
            $contentType = 'application/vnd.oasis.opendocument.spreadsheet';
            break;
            
        default:
            throw new Exception('Format tidak didukung: ' . $fmt);
    }
    
    // Bersihkan output buffer sebelum mengirim header
    cleanOutputBuffers();
    
    // Set headers untuk download
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Buat writer ke output stream
    $writer->openToFile('php://output');
    
    // Style untuk header
    if ($fmt !== 'csv') {
        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(12)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::BLUE)
            ->setCellAlignment(CellAlignment::CENTER)
            ->build();
    }
    
    // Tulis informasi summary jika diminta
    if ($include_summary && $fmt !== 'csv') {
        $summaryData = [
            WriterEntityFactory::createRowFromArray(['LAPORAN ' . strtoupper($tableLabel)]),
            WriterEntityFactory::createRowFromArray(['']), // Baris kosong
            WriterEntityFactory::createRowFromArray(['Periode:', $start_date && $end_date ? date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)) : 'Semua Data']),
            WriterEntityFactory::createRowFromArray(['Total Data:', $totalRows . ' baris']),
            WriterEntityFactory::createRowFromArray(['Diekspor pada:', date('d/m/Y H:i:s')]),
            WriterEntityFactory::createRowFromArray(['Format:', strtoupper($fmt)]),
            WriterEntityFactory::createRowFromArray(['']), // Baris kosong
        ];
        
        foreach ($summaryData as $row) {
            $writer->addRow($row);
        }
    }
    
    // Tulis header kolom
    $headerRow = WriterEntityFactory::createRowFromArray($headers);
    if ($fmt !== 'csv' && isset($headerStyle)) {
        $headerRow = WriterEntityFactory::createRowFromArray($headers, $headerStyle);
    }
    $writer->addRow($headerRow);
    
    // Tulis data secara streaming
    $rowCount = 0;
$batchSize = 1000;

while ($row = $stmt->fetch()) {
    $processedRow = [];
    
    foreach ($headerKeys as $col) {
        $value = '';
        
        // PERBAIKAN: Mapping khusus untuk pengeluaran
        if ($t === 'pengeluaran') {
            switch($col) {
                case 'nomor_kuintasi':
                    $value = $row['nomor_kuintasi'] ?? '';
                    break;
                case 'kode_anggaran':
                    $value = $row['kode_anggaran'] ?? '';
                    break;
                case 'tahun':
                    $value = $row['tahun'] ?? '';
                    break;
                case 'jumlah':
                    $value = $row['jumlah'] ?? '';
                    break;
                case 'tanggal':
                    $value = $row['tanggal'] ?? '';
                    break;
                case 'kode_mak':
                    $value = $row['kode_mak'] ?? '';
                    break;
                case 'keterangan':
                    $value = $row['keterangan'] ?? '';
                    break;
                case 'nama_anggaran':
                    $value = $row['nama_anggaran'] ?? '';
                    break;
                case 'nama_mak':
                    $value = $row['nama_mak'] ?? '';
                    break;
                default:
                    $value = $row[$col] ?? '';
            }
        } else {
            // Logic original untuk tabel lain
            $value = $row[$col] ?? '';
            
            if (($value === '' || $value === null) && !empty($joins)) {
                foreach ($joins as $joinTable => $joinInfo) {
                    $joinedCol = "{$joinTable}_$col";
                    if (isset($row[$joinedCol]) && $row[$joinedCol] !== '') {
                        $value = $row[$joinedCol];
                        break;
                    }
                }
            }
        }
        
        // Format nilai sesuai tipe kolom
        if ($format_numbers) {
            // Format tanggal
            if (strpos($col, 'tgl') !== false || strpos($col, 'tanggal') !== false) {
                if ($value && $value !== '0000-00-00') {
                    try {
                        $value = date('d/m/Y', strtotime($value));
                    } catch (Exception $e) {
                        // Biarkan nilai asli jika gagal format
                    }
                }
            }
            // Format angka
            elseif (in_array($col, ['total_anggaran', 'jumlah']) && is_numeric($value)) {
                $value = number_format((float)$value, 2, ',', '.');
            }
        }
        
        // Handle null values
        if ($value === null) {
            $value = '';
        }
        
        $processedRow[] = $value;
    }
    
    $dataRow = WriterEntityFactory::createRowFromArray($processedRow);
    $writer->addRow($dataRow);
    
    $rowCount++;
    
    // Flush output buffer secara berkala untuk mencegah timeout
    if ($rowCount % 100 === 0) {
        flush();
        if (ob_get_level() > 0) {
            ob_flush();
        }
    }
}
    
    // Tulis ringkasan akhir jika diminta dan bukan CSV
    if ($include_summary && $fmt !== 'csv' && $rowCount > 0) {
        $writer->addRow(WriterEntityFactory::createRowFromArray([''])); // Baris kosong
        $writer->addRow(WriterEntityFactory::createRowFromArray(['RINGKASAN:']));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Total baris data:', $rowCount]));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Berhasil diekspor pada:', date('d/m/Y H:i:s')]));
    }
    
    $writer->close();
    exit;
    
} catch (Exception $e) {
    // Clean any output
    cleanOutputBuffers();
    
    error_log("Spout export error: " . $e->getMessage());
    
    // Tampilkan error yang user-friendly
    header('Content-Type: text/html; charset=UTF-8');
    
    $errorMessage = 'Terjadi kesalahan saat export: ' . htmlspecialchars($e->getMessage());
    if (strpos($e->getMessage(), 'Spout') !== false || strpos($e->getMessage(), 'Box\\Spout') !== false) {
        $errorMessage = 'Library Spout tidak tersedia. Install dengan: composer require box/spout';
    }
    
    echo '<!DOCTYPE html><html><head><title>Export Error</title>';
    echo '<style>body { font-family: Arial, sans-serif; padding: 20px; background: #f8f9fa; }';
    echo '.error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; }</style></head>';
    echo '<body><div class="error"><h3>Export Error</h3><p>' . $errorMessage . '</p>';
    
    if (isset($t)) {
        echo '<p><a href="table.php?t=' . htmlspecialchars($t) . '">Kembali ke Tabel</a></p>';
    }
    
    echo '</div></body></html>';
    exit;
}
?>