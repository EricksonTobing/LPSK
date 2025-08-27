<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/../inc/csrf.php';

require_login();
$tables = require __DIR__ . '/../inc/table_meta.php';

$t = $_GET['t'] ?? 'permohonan';
if (!isset($tables[$t]) || $t === 'users') {
    http_response_code(404);
    exit('Not Found');
}

$meta       = $tables[$t];
$pk         = $meta['pk'];
$colLabels  = $meta['columns'];
$title      = $meta['label'] ?? ucfirst($t);
$searchable = $meta['searchable'] ?? [];
$filters    = $meta['filters'] ?? [];
$joins      = $meta['joins'] ?? [];
$title      = ucfirst($t);

$role = auth_user()['role'] ?? 'user';

// POST Handler - PERBAIKAN UTAMA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if ($role !== 'admin') {
        http_response_code(403);
        exit('Forbidden');
    }
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create' || $action === 'update') {
            $data = [];
            foreach (array_keys($colLabels) as $c) {
                // Skip joined columns (they are read-only)
                $isJoined = false;
                foreach ($joins as $joinTable => $joinInfo) {
                    if (in_array($c, $joinInfo[2])) {
                        $isJoined = true;
                        break;
                    }
                }
                if ($isJoined) continue;

                // PERBAIKAN: Gunakan nama kolom asli
                $v = $_POST[$c] ?? null;
                
                error_log("Field $c: " . var_export($v, true));
                
                if (is_string($v)) $v = trim($v);
                $data[$c] = $v === '' ? null : $v;
            }

            error_log("Data to save: " . print_r($data, true));

            // Validation
            if ($action === 'create' && empty($data[$pk])) {
                $_SESSION['error'] = "Primary key ($pk) is required";
                redirect("table.php?t=$t");
            }

            if ($action === 'create') {
                $sql = "INSERT INTO $t (" . implode(',', array_keys($data)) . ") VALUES (" . implode(',', array_fill(0, count($data), '?')) . ")";
                error_log("SQL: $sql");
                error_log("Params: " . print_r(array_values($data), true));
                
                $stmt = db()->prepare($sql);
                $stmt->execute(array_values($data));
                $_SESSION['success'] = "Data created successfully";
            } else {
                $id = $_POST[$pk] ?? null;
                if (!$id) {
                    $_SESSION['error'] = "ID required";
                    redirect("table.php?t=$t");
                }
                $set = [];
                $params = [];
                foreach ($data as $k => $v) {
                    $set[] = "$k=?";
                    $params[] = $v;
                }
                $params[] = $id;
                $sql = "UPDATE $t SET " . implode(',', $set) . " WHERE $pk=?";
                error_log("SQL: $sql");
                error_log("Params: " . print_r($params, true));
                
                $stmt = db()->prepare($sql);
                $stmt->execute($params);
                $_SESSION['success'] = "Data updated successfully";
            }
        
        } elseif ($action === 'delete') {
            $id = $_POST[$pk] ?? null;
            if ($id) {
                // Cek foreign key sebelum hapus
                if ($t === 'permohonan') {
                    $checkLayanan = db()->prepare("SELECT COUNT(*) FROM layanan WHERE no_reg_medan = ?");
                    $checkLayanan->execute([$id]);
                    $checkPenelaahan = db()->prepare("SELECT COUNT(*) FROM penelaahan WHERE no_reg_medan = ?");
                    $checkPenelaahan->execute([$id]);
                    if ($checkLayanan->fetchColumn() > 0 || $checkPenelaahan->fetchColumn() > 0) {
                        $_SESSION['error'] = "Cannot delete: Data is referenced in other tables";
                        redirect("table.php?t=$t");
                    }
                }
                $sql = "DELETE FROM $t WHERE $pk=?";
                db()->prepare($sql)->execute([$id]);
                $_SESSION['success'] = "Data deleted successfully";
            }
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        
        // User-friendly error messages
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        
        if (strpos($errorMessage, 'foreign key constraint') !== false) {
            $_SESSION['error'] = "Tidak dapat menghapus data karena masih digunakan di tabel lain";
        } elseif (strpos($errorMessage, 'duplicate entry') !== false) {
            $_SESSION['error'] = "Data dengan nilai yang sama sudah ada dalam sistem";
        } elseif (strpos($errorMessage, 'data too long') !== false) {
            $_SESSION['error'] = "Data yang dimasukkan terlalu panjang untuk field tertentu";
        } else {
            $_SESSION['error'] = "Terjadi kesalahan database. Silakan coba lagi atau hubungi administrator.";
        }
    }
    redirect("table.php?t=$t");
}

// PERBAIKAN FUNGSI get_input_type
function get_input_type($column, $value = '')
{
    // Handle URL field
    if ($column === 'link_berkas_permohonan') {
        return ['type' => 'url'];
    }

    // PERBAIKAN: Daftar lengkap kolom enum dengan data yang benar
    $enum_columns = [
        'jenis_kelamin'         => ['L' => 'Laki-laki', 'P' => 'Perempuan'],
        'status_hukum'          => [
            'Saksi' => 'Saksi', 
            'Korban' => 'Korban', 
            'Ahli' => 'Ahli', 
            'Pelapor' => 'Pelapor', 
            'Saksi Pelaku' => 'Saksi Pelaku'
        ],
        'pihak_perwakilan'      => [
            'KELUARGA' => 'Keluarga', 
            'APH' => 'APH', 
            'INSTANSI PEMERINTAH' => 'Instansi Pemerintah', 
            'DIRI SENDIRI' => 'Diri Sendiri', 
            'DLL' => 'Lainnya'
        ],
        'tindak_pidana'         => [
            'KSA' => 'KSA', 
            'PENYIKSAAN' => 'Penyiksaan', 
            'KORUPSI' => 'Korupsi', 
            'TPPO' => 'TPPO', 
            'PHB' => 'PHB', 
            'TERORISME' => 'Terorisme', 
            'KS' => 'KS', 
            'PENGANIAYAAN BERAT' => 'Penganiayaan Berat', 
            'NARKOTIKA' => 'Narkotika', 
            'TPL' => 'TPL', 
            'TPPU' => 'TPPU'
        ],
        'media_pengajuan'       => [
            'DATANG LANGSUNG' => 'Datang Langsung', 
            'WA' => 'WhatsApp', 
            'EMAIL' => 'Email', 
            'SURAT' => 'Surat', 
            'MPP' => 'MPP'
        ],
        'tempat_permohonan'     => ['MEDAN' => 'Medan', 'JAKARTA' => 'Jakarta'],
        'risalah_laporan'       => ['BELUM' => 'Belum', 'SUDAH' => 'Sudah'],
        'nama_ta_penalaahan'    => ['YM' => 'YM', 'MBF' => 'MBF', 'GPJ' => 'GPJ', 'IM' => 'IM'],
        'proses_hukum'          => [
            'Penyelidikan' => 'Penyelidikan', 
            'Penyidikan' => 'Penyidikan', 
            'P-19' => 'P-19', 
            'P-21' => 'P-21', 
            'P-22' => 'P-22', 
            'Penuntutan' => 'Penuntutan', 
            'Putusan Pengadilan Negeri' => 'Putusan Pengadilan Negeri', 
            'Putusan Pengadilan Tinggi' => 'Putusan Pengadilan Tinggi'
        ],
        'jenis_tindak_pidana'   => [
            'KSA' => 'KSA', 
            'PENYIKSAAN' => 'Penyiksaan', 
            'KORUPSI' => 'Korupsi', 
            'TPPO' => 'TPPO', 
            'PHB' => 'PHB', 
            'TERORISME' => 'Terorisme', 
            'KS' => 'KS', 
            'PENGANIAYAAN BERAT' => 'Penganiayaan Berat', 
            'NARKOTIKA' => 'Narkotika', 
            'TPL' => 'TPL'
        ],
        'nama_ta_layanan'       => ['AM' => 'AM', 'AJC' => 'AJC', 'RW' => 'RW', 'TP' => 'TP', 'SMW' => 'SMW'],
        'status'                => ['BERJALAN' => 'Berjalan', 'DIHENTIKAN' => 'Dihentikan', 'PERPANJANGAN' => 'Perpanjangan'],
        'status_spk'            => ['Sudah TTD' => 'Sudah TTD', 'Belum TTD' => 'Belum TTD'],
        'masa_layanan'          => ['3 BULAN' => '3 Bulan', '6 BULAN' => '6 Bulan'],
        'tambahan_masa_layanan' => ['3 BULAN' => '3 Bulan', '6 BULAN' => '6 Bulan'],
        'role'                  => ['admin' => 'Admin', 'user' => 'User'],
        'aktif'                 => [1 => 'Aktif', 0 => 'Tidak Aktif']
    ];

    if (isset($enum_columns[$column])) {
        return ['type' => 'select', 'options' => $enum_columns[$column]];
    }

    // Handle date fields
    if (
        strpos($column, 'tanggal') !== false ||
        strpos($column, 'tgl') !== false ||
        $column === 'tahun'
    ) {
        return ['type' => 'date'];
    }

    // Handle numeric fields
    $numeric_columns = ['total_anggaran', 'jumlah'];
    if (in_array($column, $numeric_columns)) {
        return ['type' => 'number', 'step' => '0.01'];
    }

    // Handle foreign keys
    if ($column === 'id_pegawai') {
        try {
            $stmt = db()->query("SELECT id_pegawai, nama_pegawai FROM pegawai ORDER BY nama_pegawai");
            $pegawai = $stmt->fetchAll();
            $options = ['' => '- Pilih -'];
            foreach ($pegawai as $p) {
                $options[$p['id_pegawai']] = $p['nama_pegawai'];
            }
            return ['type' => 'select', 'options' => $options];
        } catch (Exception $e) {
            return ['type' => 'text'];
        }
    }

    if ($column === 'kode_mak') {
        try {
            $stmt = db()->query("SELECT kode_mak, nama_mak FROM mak ORDER BY nama_mak");
            $mak = $stmt->fetchAll();
            $options = ['' => '- Pilih -'];
            foreach ($mak as $m) {
                $options[$m['kode_mak']] = $m['nama_mak'];
            }
            return ['type' => 'select', 'options' => $options];
        } catch (Exception $e) {
            return ['type' => 'text'];
        }
    }

    if ($column === 'kode_anggaran') {
        try {
            $stmt = db()->query("SELECT kode_anggaran, nama_anggaran FROM anggaran ORDER BY nama_anggaran");
            $anggaran = $stmt->fetchAll();
            $options = ['' => '- Pilih -'];
            foreach ($anggaran as $a) {
                $options[$a['kode_anggaran']] = $a['nama_anggaran'];
            }
            return ['type' => 'select', 'options' => $options];
        } catch (Exception $e) {
            return ['type' => 'text'];
        }
    }

    // Handle tahun di anggaran
    if ($column === 'tahun') {
        $currentYear = date('Y');
        $years = range($currentYear - 10, $currentYear + 5);
        return ['type' => 'select', 'options' => array_combine($years, $years)];
    }

    return ['type' => 'text'];
}

// FUNGSI TAMBAHAN: get_field_help
function get_field_help($column) {
    $help_messages = [
        'no_reg_medan' => 'Nomor registrasi unik dari Medan',
        'nama_pemohon' => 'Nama lengkap pemohon perlindungan',
        'jenis_kelamin' => 'Jenis kelamin pemohon',
        'status_hukum' => 'Status hukum pemohon (Saksi, Korban, Ahli, Pelapor, Saksi Pelaku)',
        'tgl_pengajuan' => 'Tanggal pengajuan permohonan',
        'pihak_perwakilan' => 'Pihak yang mewakili pemohon',
        'tindak_pidana' => 'Jenis tindak pidana yang dilaporkan',
        'id_pegawai' => 'Petugas yang menerima permohonan',
        'kelengkapan_berkas' => 'Kelengkapan dokumen yang diserahkan',
        'media_pengajuan' => 'Media yang digunakan untuk pengajuan',
        'link_berkas_permohonan' => 'Link Google Drive atau penyimpanan online untuk berkas',
        'jenis_perlindungan' => 'Jenis perlindungan yang dimohonkan',
        'kab_kot_locus' => 'Kabupaten/Kota tempat kejadian',
        'provinsi' => 'Provinsi tempat kejadian',
        'kab_kota_pemohon' => 'Kabupaten/Kota asal pemohon',
        'provinsi_pemohon' => 'Provinsi asal pemohon',
        'tempat_permohonan' => 'Tempat permohonan diajukan',
        
        // Penelaahan
        'no_registrasi' => 'Nomor registrasi penelaahan',
        'proses_hukum' => 'Tahap proses hukum saat ini',
        'tanggal_dispo' => 'Tanggal disposisi penelaahan',
        'proses_penalaahan' => 'Proses dan perkembangan penelaahan',
        'tgl_berakhir_penelaahan' => 'Tanggal berakhirnya masa penelaahan',
        'waktu_tambahan' => 'Waktu tambahan jika diperlukan',
        'nama_ta_penalaahan' => 'Nama Technical Assistant penelaahan',
        'risalah_laporan' => 'Status risalah laporan',
        
        // Layanan
        'no_kep_smpl' => 'Nomor Keputusan SMPL',
        'no_spk' => 'Nomor SPK (Surat Perintah Kerja)',
        'tgl_no_kep_smpl' => 'Tanggal Keputusan SMPL',
        'status_spk' => 'Status penandatanganan SPK',
        'nama_terlindung' => 'Nama yang diberikan perlindungan',
        'jenis_tindak_pidana' => 'Jenis tindak pidana yang terjadi',
        'tgl_mulai_layanan' => 'Tanggal mulai pemberian layanan',
        'masa_layanan' => 'Masa berlaku layanan',
        'tambahan_masa_layanan' => 'Perpanjangan masa layanan jika ada',
        'tgl_berakhir_layanan' => 'Tanggal berakhirnya layanan',
        'wilayah_hukum' => 'Wilayah hukum yang menangani',
        'nama_ta_layanan' => 'Nama Technical Assistant layanan',
        'status' => 'Status layanan saat ini'
    ];
    
    return $help_messages[$column] ?? 'Isikan data yang sesuai';
}

// FUNGSI TAMBAHAN: is_required_field
function is_required_field($column, $pk) {
    $required_fields = [
        'permohonan' => ['no_reg_medan', 'nama_pemohon', 'jenis_kelamin', 'status_hukum', 
                        'tgl_pengajuan', 'pihak_perwakilan', 'tindak_pidana', 'id_pegawai', 
                        'media_pengajuan', 'tempat_permohonan'],
        'penelaahan' => ['no_registrasi', 'no_reg_medan', 'proses_hukum', 'tanggal_dispo', 
                        'id_pegawai', 'nama_ta_penalaahan'],
        'layanan' => ['no_kep_smpl', 'no_reg_medan', 'no_registrasi', 'tgl_no_kep_smpl', 
                     'nama_terlindung', 'jenis_tindak_pidana', 'id_pegawai', 'tgl_mulai_layanan', 
                     'masa_layanan', 'nama_ta_layanan']
    ];
    
    return in_array($column, $required_fields[$_GET['t'] ?? 'permohonan']) && $column !== $pk;
}

// Get per_page value from request or use default
$per_page_options = [10, 20, 50];
$per_page = isset($_GET['per_page']) && in_array((int)$_GET['per_page'], $per_page_options)
    ? (int)$_GET['per_page']
    : 20;

// Get sort parameters
$sort_column = $_GET['sort'] ?? $pk;
$sort_order = $_GET['order'] ?? 'DESC';

// Validate sort column - include joined columns
$valid_columns = array_keys($colLabels);
foreach ($joins as $joinInfo) {
    $valid_columns = array_merge($valid_columns, $joinInfo[2]);
}
if (!in_array($sort_column, $valid_columns)) {
    $sort_column = $pk;
}

// Validate sort order
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

// Build ORDER BY clause - handle joined columns
$orderByTable = $t;
$orderByColumn = $sort_column;
foreach ($joins as $joinTable => $joinInfo) {
    if (in_array($sort_column, $joinInfo[2])) {
        $orderByTable = $joinTable;
        break;
    }
}
$orderBy = "$orderByTable.$sort_column $sort_order";

// Listing with JOIN support
list($page, $per, $offset) = paginate_params($per_page);
$q      = trim((string)($_GET['q'] ?? ''));
$where  = [];
$params = [];

// Build SELECT clause with joins
$selectColumns = ["$t.*"];
$joinClauses   = [];
$joinParams    = [];

foreach ($joins as $joinTable => $joinInfo) {
    list($localKey, $foreignKey, $columns) = $joinInfo;
    foreach ($columns as $col) {
        $selectColumns[] = "$joinTable.$col AS {$joinTable}_$col";
    }
    $joinClauses[] = "LEFT JOIN $joinTable ON $t.$localKey = $joinTable.$foreignKey";
}

$selectSql = implode(', ', $selectColumns);
$joinSql   = implode(' ', $joinClauses);

// Search
if ($q !== '' && $searchable) {
    $like  = "%$q%";
    $parts = [];
    foreach ($searchable as $s) {
        $isJoined    = false;
        $searchTable = $t;
        foreach ($joins as $joinTable => $joinInfo) {
            if (in_array($s, $joinInfo[2])) {
                $isJoined    = true;
                $searchTable = $joinTable;
                break;
            }
        }
        $parts[]  = "$searchTable.$s LIKE ?";
        $params[] = $like;
    }
    $where[] = '(' . implode(' OR ', $parts) . ')';
}

// Filter kolom
foreach ($filters as $f) {
    $filterName = str_replace('/', '_', $f);
    if ($val = trim((string)($_GET[$filterName] ?? ''))) {
        $isJoined    = false;
        $filterTable = $t;
        foreach ($joins as $joinTable => $joinInfo) {
            if (in_array($f, $joinInfo[2])) {
                $isJoined    = true;
                $filterTable = $joinTable;
                break;
            }
        }
        $where[]  = "$filterTable.$f = ?";
        $params[] = $val;
    }
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$totalStmt = db()->prepare("SELECT COUNT(*) FROM $t $joinSql $whereSql");
$totalStmt->execute(array_merge($joinParams, $params));
$total = (int)$totalStmt->fetchColumn();

$sql = "SELECT $selectSql FROM $t $joinSql $whereSql ORDER BY $orderBy LIMIT $per OFFSET $offset";
$stmt = db()->prepare($sql);
$stmt->execute(array_merge($joinParams, $params));
$rows = $stmt->fetchAll();

// Process joined data for display
$processedRows = [];
foreach ($rows as $row) {
    $processedRow = $row;
    foreach ($joins as $joinTable => $joinInfo) {
        list($localKey, $foreignKey, $columns) = $joinInfo;
        foreach ($columns as $col) {
            $joinedCol = "{$joinTable}_$col";
            if (isset($row[$joinedCol])) {
                $processedRow[$col] = $row[$joinedCol];
                // Simpan juga dengan nama yang lebih spesifik untuk referensi
                $processedRow["{$joinTable}_$col"] = $row[$joinedCol];
            }
        }
    }
    $processedRows[] = $processedRow;
}

require __DIR__ . '/../inc/layout_header.php';
require __DIR__ . '/../inc/layout_nav.php';
?>

<!-- Tambahkan di bagian atas table.php setelah title -->
<div class="alert alert-info mb-6">
    <div class="alert-icon">
        <i class="fas fa-info-circle"></i>
    </div>
    <div class="alert-content">
        <strong>Panduan Penggunaan:</strong>
        <ul class="mt-1 list-disc list-inside text-sm">
            <li>Gunakan filter untuk menyaring data yang ditampilkan</li>
            <li>Klik pada header kolom untuk mengurutkan data</li>
            <li>Gunakan tombol + untuk menambah data baru</li>
            <li>Field dengan tanda * wajib diisi</li>
            <li>Arahkan kursor ke field untuk melihat petunjuk pengisian</li>
        </ul>
    </div>
    <button class="alert-close" onclick="this.parentElement.remove()">
        <i class="fas fa-times"></i>
    </button>
</div>

<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= e(ucfirst($t)) ?></h1>
    <div class="flex flex-wrap gap-2 items-center">
        <a class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center text-sm transition-colors"
           href="export.php?<?= build_query(array_merge($_GET, ['fmt' => 'xlsx', 'sort' => $sort_column, 'order' => $sort_order])) ?>">
            <i class="fas fa-file-excel mr-2"></i> Excel
        </a>
        <a class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center text-sm transition-colors"
           href="export.php?<?= build_query(array_merge($_GET, ['fmt' => 'csv', 'sort' => $sort_column, 'order' => $sort_order])) ?>">
            <i class="fas fa-file-csv mr-2"></i> CSV
        </a>
        <a class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg flex items-center text-sm transition-colors"
           href="export.php?<?= build_query(array_merge($_GET, ['fmt' => 'pdf', 'sort' => $sort_column, 'order' => $sort_order])) ?>">
            <i class="fas fa-file-pdf mr-2"></i> PDF
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700"><?= e($_SESSION['error']) ?></p>
            </div>
        </div>
        <?php unset($_SESSION['error']) ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700"><?= e($_SESSION['success']) ?></p>
            </div>
        </div>
        <?php unset($_SESSION['success']) ?>
    </div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden mb-8">
    <!-- Filter Section -->
    <div class="p-4 border-b border-gray-100 dark:border-gray-700">
        <form class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <input type="hidden" name="t" value="<?= e($t) ?>">
            <input type="hidden" name="sort" value="<?= e($sort_column) ?>">
            <input type="hidden" name="order" value="<?= e($sort_order) ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Ketik untuk mencari..."
                        class="pl-10 w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            <?php foreach ($filters as $f): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= e($colLabels[$f] ?? $f) ?></label>
                    <?php
                    $input_type = get_input_type($f);
                    $filterName = str_replace('/', '_', $f);
                    ?>
                    <?php if ($input_type['type'] === 'select'): ?>
                        <select name="<?= e($filterName) ?>" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">- Semua -</option>
                            <?php foreach ($input_type['options'] as $key => $option): ?>
                                <option value="<?= e($key) ?>" <?= (($_GET[$filterName] ?? '') === (string)$key) ? 'selected' : '' ?>>
                                    <?= e($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="<?= e($input_type['type']) ?>" name="<?= e($filterName) ?>" value="<?= e($_GET[$filterName] ?? '') ?>"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors">
                    Terapkan Filter
                </button>
                <a href="table.php?t=<?= e($t) ?>&sort=<?= e($pk) ?>&order=DESC" class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-sync-alt mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Controls -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 border-b border-gray-100 dark:border-gray-700 gap-4">
        <div class="text-sm text-gray-600 dark:text-gray-400">
            Total: <span class="font-semibold"><?= $total ?></span> data
            <?php if ($q): ?>
                <span class="ml-2">(Hasil pencarian: "<?= e($q) ?>")</span>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600 dark:text-gray-400">Baris per halaman:</label>
            <select id="per_page_select" class="border border-gray-300 dark:border-gray-600 rounded-lg py-1 px-2 dark:bg-gray-700 dark:text-white">
                <?php foreach ($per_page_options as $option): ?>
                    <option value="<?= $option ?>" <?= $per_page == $option ? 'selected' : '' ?>><?= $option ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Table Section -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-12">
                        No
                    </th>
                    <?php foreach ($colLabels as $col => $label): 
                        $is_current_sort = $sort_column === $col;
                        $new_order = $is_current_sort && $sort_order === 'DESC' ? 'ASC' : 'DESC';
                        $sort_icon = '';
                        
                        if ($is_current_sort) {
                            $sort_icon = $sort_order === 'ASC' ? '↑' : '↓';
                        }
                    ?>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                            onclick="sortTable('<?= e($col) ?>')">
                            <?= e($label) ?> 
                            <?php if ($is_current_sort): ?>
                                <span class="ml-1"><?= $sort_icon ?></span>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                    <?php if ($role === 'admin'): ?>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Aksi
                        </th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (count($processedRows) > 0): ?>
                <?php $row_number = $offset + 1; ?>
                <?php foreach ($processedRows as $r): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400 text-center">
                            <?= $row_number++ ?>
                        </td>
                        <?php foreach ($colLabels as $col => $label): ?>
                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200 max-w-xs truncate" title="<?= e((string)($r[$col] ?? '')) ?>">
                                <?php if ($col === 'id_pegawai' && !empty($r['pegawai_nama_pegawai'])): ?>
                                    <?= e($r['pegawai_nama_pegawai']) ?>
                                <?php elseif ($col === 'link_berkas_permohonan' && !empty($r[$col])): ?>
                                    <a href="<?= e($r[$col]) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline flex items-center">
                                        <i class="fas fa-external-link-alt mr-1 text-xs"></i> Lihat Berkas
                                    </a>
                                <?php else: ?>
                                    <?= e(truncate_text((string)($r[$col] ?? ''), 50)) ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <?php if ($role === 'admin'): ?>
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button onclick="openEditModal('<?= e($r[$pk]) ?>')" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400 transition-colors p-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="confirmDelete('<?= e($r[$pk]) ?>')" class="text-red-600 hover:text-red-900 dark:hover:text-red-400 transition-colors p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?= count($colLabels) + ($role === 'admin' ? 2 : 1) ?>" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex flex-col items-center justify-center py-8">
                            <i class="fas fa-inbox text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
                            <p class="text-gray-500 dark:text-gray-400">Tidak ada data yang ditemukan</p>
                            <?php if ($q || !empty($filters)): ?>
                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Coba ubah kata kunci pencarian atau filter</p>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total > 0): ?>
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
            <div class="flex flex-col sm:flex-row items-center justify-between space-y-3 sm:space-y-0">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Menampilkan <span class="font-medium"><?= $offset + 1 ?></span> - 
                    <span class="font-medium"><?= min($offset + $per, $total) ?></span> dari 
                    <span class="font-medium"><?= $total ?></span> data
                </div>
                <div class="flex items-center space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?<?= build_query(['page' => $page - 1, 'per_page' => $per, 'q' => $q, 'sort' => $sort_column, 'order' => $sort_order] + array_filter($_GET, fn($k) => in_array($k, array_map(fn($f) => str_replace('/', '_', $f), $filters)), ARRAY_FILTER_USE_KEY)) ?>" class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <i class="fas fa-chevron-left mr-1"></i> Sebelumnya
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($start_page + 4, ceil($total / $per));
                    if ($end_page - $start_page < 4) {
                        $start_page = max(1, $end_page - 4);
                    }
                    ?>
                    
                    <?php for ($p = $start_page; $p <= $end_page; $p++): ?>
                        <a href="?<?= build_query(['page' => $p, 'per_page' => $per, 'q' => $q, 'sort' => $sort_column, 'order' => $sort_order] + array_filter($_GET, fn($k) => in_array($k, array_map(fn($f) => str_replace('/', '_', $f), $filters)), ARRAY_FILTER_USE_KEY)) ?>" class="px-3 py-1 rounded border <?= $p == $page ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' ?> transition-colors">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < ceil($total / $per)): ?>
                        <a href="?<?= build_query(['page' => $page + 1, 'per_page' => $per, 'q' => $q, 'sort' => $sort_column, 'order' => $sort_order] + array_filter($_GET, fn($k) => in_array($k, array_map(fn($f) => str_replace('/', '_', $f), $filters)), ARRAY_FILTER_USE_KEY)) ?>" class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Selanjutnya <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($role === 'admin'): ?>
    <!-- Floating Action Button -->
    <button onclick="openCreateModal()" class="fixed bottom-8 right-8 w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center transition-all hover:scale-110" title="Tambah Data Baru">
        <i class="fas fa-plus text-xl"></i>
    </button>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Tambah Data <?= e($title) ?></h3>
            </div>
            <form id="createForm" method="post" class="p-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($colLabels as $col => $label): 
                        // Skip joined columns in create form
                        $isJoined = false;
                        foreach ($joins as $joinTable => $joinInfo) {
                            if (in_array($col, $joinInfo[2])) {
                                $isJoined = true;
                                break;
                            }
                        }
                        if ($isJoined) continue;
                        
                        $input_type = get_input_type($col);
                        $required = is_required_field($col, $pk);
                        $help_text = get_field_help($col);
                    ?>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <?= e($label) ?>
                                <?php if ($required): ?>
                                    <span class="text-red-500">*</span>
                                <?php endif; ?>
                            </label>
                            <?php if ($input_type['type'] === 'select'): ?>
                                <select name="<?= e($col) ?>" 
                                    <?= $required ? 'required' : '' ?>
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    title="<?= e($help_text) ?>">
                                    <option value="">- Pilih -</option>
                                    <?php foreach ($input_type['options'] as $key => $option): ?>
                                        <option value="<?= e($key) ?>"><?= e($option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="<?= e($input_type['type']) ?>" 
                                    name="<?= e($col) ?>" 
                                    <?= $required ? 'required' : '' ?>
                                    <?php if (isset($input_type['step'])): ?>step="<?= e($input_type['step']) ?>"<?php endif; ?>
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="<?= e($label) ?>"
                                    title="<?= e($help_text) ?>">
                            <?php endif; ?>
                            <p class="text-xs text-gray-500 dark:text-gray-400"><?= e($help_text) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeCreateModal()" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Edit Data <?= e($title) ?></h3>
            </div>
            <form id="editForm" method="post" class="p-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="<?= e($pk) ?>" id="editId">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($colLabels as $col => $label): 
                        $input_type = get_input_type($col);
                        $required = is_required_field($col, $pk);
                        $help_text = get_field_help($col);
                    ?>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <?= e($label) ?>
                                <?php if ($required): ?>
                                    <span class="text-red-500">*</span>
                                <?php endif; ?>
                            </label>
                            <?php if ($input_type['type'] === 'select'): ?>
                                <select name="<?= e($col) ?>" 
                                    id="edit_<?= e($col) ?>"
                                    <?= $required ? 'required' : '' ?>
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    title="<?= e($help_text) ?>">
                                    <option value="">- Pilih -</option>
                                    <?php foreach ($input_type['options'] as $key => $option): ?>
                                        <option value="<?= e($key) ?>"><?= e($option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="<?= e($input_type['type']) ?>" 
                                    name="<?= e($col) ?>" 
                                    id="edit_<?= e($col) ?>"
                                    <?= $required ? 'required' : '' ?>
                                    <?php if (isset($input_type['step'])): ?>step="<?= e($input_type['step']) ?>"<?php endif; ?>
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="<?= e($label) ?>"
                                    title="<?= e($help_text) ?>">
                            <?php endif; ?>
                            <p class="text-xs text-gray-500 dark:text-gray-400"><?= e($help_text) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Konfirmasi Hapus</h3>
            </div>
            <form id="deleteForm" method="post" class="p-6">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="<?= e($pk) ?>" id="deleteId">
                <p class="text-gray-600 dark:text-gray-300 mb-4">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                        Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
// Per page selection
document.getElementById('per_page_select').addEventListener('change', function() {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', this.value);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
});

// Modal functions
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}

function openEditModal(id) {
    // Fetch data for this ID
    fetch(`get_data.php?t=<?= e($t) ?>&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editId').value = id;
                <?php foreach ($colLabels as $col => $label): ?>
                    const <?= e($col) ?>Input = document.getElementById('edit_<?= e($col) ?>');
                    if (<?= e($col) ?>Input) {
                        <?= e($col) ?>Input.value = data.data.<?= e($col) ?> || '';
                    }
                <?php endforeach; ?>
                document.getElementById('editModal').classList.remove('hidden');
            } else {
                alert('Gagal memuat data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat data');
        });
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function confirmDelete(id) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modals when clicking outside
document.querySelectorAll('.fixed').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
});

// Sort function
function sortTable(column) {
    const url = new URL(window.location.href);
    const currentSort = url.searchParams.get('sort');
    const currentOrder = url.searchParams.get('order');
    
    let newOrder = 'DESC';
    if (currentSort === column) {
        newOrder = currentOrder === 'DESC' ? 'ASC' : 'DESC';
    }
    
    url.searchParams.set('sort', column);
    url.searchParams.set('order', newOrder);
    window.location.href = url.toString();
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateModal();
        closeEditModal();
        closeDeleteModal();
    }
    if (e.ctrlKey && e.key === 'n' && <?= $role === 'admin' ? 'true' : 'false' ?>) {
        e.preventDefault();
        openCreateModal();
    }
});
</script>

<?php
require __DIR__ . '/../inc/layout_footer.php';
?>