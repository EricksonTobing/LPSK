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
        $_SESSION['error'] = "Database error: " . $e->getMessage();
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
                    <td colspan="<?= count($colLabels) + ($role === 'admin' ? 2 : 1) ?>" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-inbox text-3xl mb-2 text-gray-300"></i>
                            <p>Tidak ada data yang ditemukan</p>
                            <?php if ($q || array_filter($_GET, function($val, $key) { return $key !== 't' && $key !== 'page' && $key !== 'sort' && $key !== 'order' && $val !== ''; }, ARRAY_FILTER_USE_BOTH)): ?>
                                <p class="text-sm mt-1">Coba sesuaikan filter pencarian Anda</p>
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
    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-sm text-gray-700 dark:text-gray-300">
            Menampilkan <span class="font-medium"><?= $offset + 1 ?></span> - <span class="font-medium"><?= min($offset + $per, $total) ?></span> dari <span class="font-medium"><?= $total ?></span> hasil
        </div>
        <div class="inline-flex mt-2 xs:mt-0">
            <?php
            $prevPage   = $page > 1 ? $page - 1 : 1;
            $nextPage   = $page < ceil($total / $per) ? $page + 1 : $page;
            $prevParams = array_merge($_GET, ['page' => $prevPage, 'sort' => $sort_column, 'order' => $sort_order]);
            $nextParams = array_merge($_GET, ['page' => $nextPage, 'sort' => $sort_column, 'order' => $sort_order]);
            ?>
            <a href="?<?= build_query($prevParams) ?>" class="flex items-center justify-center px-3 h-8 text-sm font-medium text-white bg-gray-800 rounded-l hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 <?= $page <= 1 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                <i class="fas fa-chevron-left mr-1"></i> Prev
            </a>
            <span class="flex items-center justify-center px-3 h-8 text-sm font-medium text-gray-700 bg-gray-100 dark:bg-gray-600 dark:text-white">
                <?= $page ?> / <?= ceil($total / $per) ?>
            </span>
            <a href="?<?= build_query($nextParams) ?>" class="flex items-center justify-center px-3 h-8 text-sm font-medium text-white bg-gray-800 rounded-r hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 <?= $page >= ceil($total / $per) ? 'opacity-50 cursor-not-allowed' : '' ?>">
                Next <i class="fas fa-chevron-right ml-1"></i>
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<?php if ($role === 'admin'): ?>
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Edit Data</h3>
                    <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="editForm" method="post" class="mt-4 space-y-4 max-h-96 overflow-y-auto pr-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="editPk" name="<?= e($pk) ?>" value="">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                       
<?php foreach ($colLabels as $col => $label):
    $isJoined = false;
    foreach ($joins as $joinTable => $joinInfo) {
        if (in_array($col, $joinInfo[2])) {
            $isJoined = true;
            break;
        }
    }
    if ($isJoined) continue;
    $input_type = get_input_type($col);
    $inputName  = $col; // Gunakan nama kolom asli
    $inputId    = 'edit_' . $col;
?>
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= e($label) ?></label>
    <?php if ($input_type['type'] === 'select'): ?>
        <select name="<?= e($inputName) ?>" id="<?= e($inputId) ?>" 
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            <option value="">- Pilih -</option>
            <?php foreach ($input_type['options'] as $key => $option): ?>
                <option value="<?= e($key) ?>"><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
    <?php elseif ($input_type['type'] === 'date'): ?>
        <input type="date" name="<?= e($inputName) ?>" id="<?= e($inputId) ?>" 
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
    <?php elseif ($input_type['type'] === 'number'): ?>
        <input type="number" name="<?= e($inputName) ?>" id="<?= e($inputId) ?>" 
            step="<?= $input_type['step'] ?? '1' ?>"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
    <?php elseif ($input_type['type'] === 'url'): ?>
        <input type="url" name="<?= e($inputName) ?>" id="<?= e($inputId) ?>" 
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            placeholder="https://...">
    <?php else: ?>
        <input type="text" name="<?= e($inputName) ?>" id="<?= e($inputId) ?>" 
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
    <?php endif; ?>
</div>
<?php endforeach; ?>
                    </div>
                    <div class="flex justify-end pt-4 border-t mt-6">
                        <button type="button" onclick="closeEditModal()" class="mr-3 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mt-3">Konfirmasi Hapus</h3>
                <div class="mt-2 px-4 py-3">
                    <p class="text-sm text-gray-500 dark:text-gray-300">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="mt-4 flex justify-center gap-3">
                    <form id="deleteForm" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="deletePk" name="<?= e($pk) ?>" value="">
                        <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Button -->
    <div class="fixed bottom-6 right-6">
        <button onclick="openCreateModal()" class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center text-2xl transition-all hover:scale-110">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Tambah Data Baru</h3>
                    <button type="button" onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="createForm" method="post" class="mt-4 space-y-4 max-h-96 overflow-y-auto pr-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="create">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($colLabels as $col => $label): 
                            $isJoined = false;
                            foreach ($joins as $joinTable => $joinInfo) {
                                if (in_array($col, $joinInfo[2])) {
                                    $isJoined = true;
                                    break;
                                }
                            }
                            if ($isJoined) continue;
                            $input_type = get_input_type($col);
                            $inputName  = $col; // PERBAIKAN: Gunakan nama kolom asli
                            $inputId    = 'create_' . str_replace('/', '_', $col);
                        ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= e($label) ?></label>
                                <?php if ($input_type['type'] === 'select'): ?>
                                    <select name="<?= e($inputName) ?>" id="<?= e($inputId) ?>" 
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">- Pilih -</option>
                                        <?php foreach ($input_type['options'] as $key => $option): ?>
                                            <option value="<?= e($key) ?>"><?= e($option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($input_type['type'] === 'date'): ?>
                                    <input type="date" name="<?= e($inputName) ?>" id="<?= e($inputId) ?>" 
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <?php elseif ($input_type['type'] === 'number'): ?>
                                    <input type="number" name="<?= e($inputName) ?>" id="<?= e($inputId) ?>" 
                                        step="<?= $input_type['step'] ?? '1' ?>"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <?php elseif ($input_type['type'] === 'url'): ?>
                                    <input type="url" name="<?= e($inputName) ?>" id="<?= e($inputId) ?>" 
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="https://...">
                                <?php else: ?>
                                    <input type="text" name="<?= e($inputName) ?>" id="<?= e($inputId) ?>" 
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex justify-end pt-4 border-t mt-6">
                        <button type="button" onclick="closeCreateModal()" class="mr-3 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            Tambah Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
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

function openEditModal(id) {
    fetch(`ajax_get_row.php?t=<?= e($t) ?>&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = data.data;
                document.getElementById('editPk').value = id;
                
                <?php foreach ($colLabels as $col => $label): 
    $isJoined = false;
    foreach ($joins as $joinTable => $joinInfo) {
        if (in_array($col, $joinInfo[2])) {
            $isJoined = true;
            break;
        }
    }
    if ($isJoined) continue;
?>
if (row.hasOwnProperty('<?= e($col) ?>')) {
    const inputElement = document.getElementById('edit_<?= e($col) ?>');
    if (inputElement) {
        inputElement.value = row['<?= e($col) ?>'] || '';
    }
}
<?php endforeach; ?>
                
                document.getElementById('editModal').classList.remove('hidden');
            } else {
                alert('Error loading data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading data');
        });
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function confirmDelete(id) {
    document.getElementById('deletePk').value = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.getElementById('createForm').reset();
}

// Per page change handler
document.getElementById('per_page_select').addEventListener('change', function() {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', this.value);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
});

// Close modals on outside click
document.addEventListener('click', function(event) {
    const editModal = document.getElementById('editModal');
    const deleteModal = document.getElementById('deleteModal');
    const createModal = document.getElementById('createModal');
    
    if (editModal && !editModal.classList.contains('hidden') && event.target === editModal) {
        closeEditModal();
    }
    if (deleteModal && !deleteModal.classList.contains('hidden') && event.target === deleteModal) {
        closeDeleteModal();
    }
    if (createModal && !createModal.classList.contains('hidden') && event.target === createModal) {
        closeCreateModal();
    }
});

// Escape key to close modals
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEditModal();
        closeDeleteModal();
        closeCreateModal();
    }
});
</script>

<?php
require __DIR__ . '/../inc/layout_footer.php';
?>