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

// POST Handler
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
                // Skip joined columns
                $isJoined = false;
                foreach ($joins as $joinTable => $joinInfo) {
                    if (in_array($c, $joinInfo[2])) {
                        $isJoined = true;
                        break;
                    }
                }
                if ($isJoined) continue;

                $postKey = str_replace('/', '_', $c);
                $v = $_POST[$postKey] ?? null;
                if (is_string($v)) $v = trim($v);
                $data[$c] = $v === '' ? null : $v;
            }

            // Validation
            if ($action === 'create' && empty($data[$pk])) {
                $_SESSION['error'] = "Primary key ($pk) is required";
                redirect("table.php?t=$t");
            }
            if (($t === 'layanan' || $t === 'penelaahan') && empty($data['no_reg_medan'])) {
                $_SESSION['error'] = "Nomor Registrasi Medan is required";
                redirect("table.php?t=$t");
            }
            if ($t === 'layanan' && empty($data['no_registrasi'])) {
                $_SESSION['error'] = "Nomor Registrasi is required";
                redirect("table.php?t=$t");
            }

            if ($action === 'create') {
                $sql = "INSERT INTO $t (" . implode(',', array_keys($data)) . ") VALUES (" . implode(',', array_fill(0, count($data), '?')) . ")";
                db()->prepare($sql)->execute(array_values($data));
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
                db()->prepare($sql)->execute($params);
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
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
    redirect("table.php?t=$t");
}

// Fungsi untuk menentukan tipe input
function get_input_type($column, $value = '')
{
    if ($column === 'link_berkas_permohonan') {
        return ['type' => 'url'];
    }
    $enum_columns = [
        'jenis_kelamin'         => ['L', 'P'],
        'status_hukum'          => ['Saksi', 'Korban', 'Ahli', 'Pelapor', 'Saksi Pelaku'],
        'pihak_perwakilan'      => ['KELUARGA', 'APH', 'INSTASI PEMERINTAH', 'DIRI SENDIRI', 'DLL'],
        'tindak_pidana'         => ['KSA', 'PENYIKSAAN', 'KORUPSI', 'TPPO', 'PHB', 'TERORISME', 'KS', 'PENGANIAYAAN BERAT', 'NARKOTIKA', 'TPL', 'TPPU'],
        'media_pengajuan'       => ['DATANG LANGSUNG', 'WA', 'EMAIL', 'SURAT'],
        'tempat_permohonan'     => ['MEDAN', 'JAKARTA'],
        'risalah_laporan'       => ['BELUM', 'SUDAH'],
        'nama_ta_penalaahan'    => ['YM', 'MBF', 'GPJ', 'IM'],
        'proses_hukum'          => ['Penyelidikan', 'Penyidikan', 'P-19', 'P-21', 'P-22', 'Penuntutan', 'Putusan Pengadilan Negeri', 'Putusan Pengadilan Tinggi'],
        'jenis_tindak_pidana'   => ['KSA', 'PENYIKSAAN', 'KORUPSI', 'TPPO', 'PHB', 'TERORISME', 'KS', 'PENGANIAYAAN BERAT', 'NARKOTIKA', 'TPL'],
        'nama_ta_layanan'       => ['AM', 'AJC', 'RW', 'TP', 'SMW'],
        'status'                => ['BERJALAN', 'DIHENTIKAN', 'PERPANJANGAN'],
        'status_spk'            => ['Sudah TTD', 'Belum TTD'],
        'masa_layanan'          => ['3 BULAN', '6 BULAN'],
        'tambahan_masa_layanan' => ['3 BULAN', '6 BULAN'],
        'role'                  => ['admin', 'user']
    ];
    if (isset($enum_columns[$column])) {
        return ['type' => 'select', 'options' => $enum_columns[$column]];
    }
    if (
        strpos($column, 'tanggal') !== false ||
        strpos($column, 'tgl') !== false ||
        strpos($column, 'masa') !== false ||
        strpos($column, 'waktu') !== false
    ) {
        return ['type' => 'date'];
    }
    $numeric_columns = ['total_anggaran', 'jumlah'];
    if (in_array($column, $numeric_columns)) {
        return ['type' => 'number', 'step' => '0.01'];
    }
    return ['type' => 'text'];
}

// Listing with JOIN support
list($page, $per, $offset) = paginate_params();
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
    if ($val = trim((string)($_GET[$f] ?? ''))) {
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

$sql = "SELECT $selectSql FROM $t $joinSql $whereSql ORDER BY $t.$pk DESC LIMIT $per OFFSET $offset";
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
           href="export.php?<?= build_query(array_merge($_GET, ['fmt' => 'xlsx'])) ?>">
            <i class="fas fa-file-excel mr-2"></i> Excel
        </a>
        <a class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center text-sm transition-colors"
           href="export.php?<?= build_query(array_merge($_GET, ['fmt' => 'csv'])) ?>">
            <i class="fas fa-file-csv mr-2"></i> CSV
        </a>
        <a class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg flex items-center text-sm transition-colors"
           href="export.php?<?= build_query(array_merge($_GET, ['fmt' => 'pdf'])) ?>">
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
                            <?php foreach ($input_type['options'] as $option): ?>
                                <option value="<?= e($option) ?>" <?= ($_GET[$filterName] ?? '') === $option ? 'selected' : '' ?>>
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
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <?php foreach ($colLabels as $col => $label): ?>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <?= e($label) ?>
                        </th>
                    <?php endforeach; ?>
                    <?php if ($role === 'admin'): ?>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Aksi
                        </th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($processedRows as $r): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <?php foreach ($colLabels as $col => $label): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                <?php if ($col === 'link_berkas_permohonan' && !empty($r[$col])): ?>
                                    <a href="<?= e($r[$col]) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline">
                                        Lihat Berkas
                                    </a>
                                <?php else: ?>
                                    <?= e((string)($r[$col] ?? '')) ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <?php if ($role === 'admin'): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button onclick="openEditModal('<?= e($r[$pk]) ?>')" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400 transition-colors" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="confirmDelete('<?= e($r[$pk]) ?>')" class="text-red-600 hover:text-red-900 dark:hover:text-red-400 transition-colors" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-300">
            Menampilkan <span class="font-medium"><?= count($processedRows) ?></span> dari <span class="font-medium"><?= $total ?></span> hasil
        </div>
        <div class="inline-flex mt-2 xs:mt-0">
            <?php
            $prevPage   = $page > 1 ? $page - 1 : 1;
            $nextPage   = $page < ceil($total / $per) ? $page + 1 : $page;
            $prevParams = array_merge($_GET, ['page' => $prevPage]);
            $nextParams = array_merge($_GET, ['page' => $nextPage]);
            ?>
            <a href="?<?= build_query($prevParams) ?>" class="flex items-center justify-center px-3 h-8 text-sm font-medium text-white bg-gray-800 rounded-l hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600">
                <i class="fas fa-chevron-left mr-1"></i> Prev
            </a>
            <span class="flex items-center justify-center px-3 h-8 text-sm font-medium text-gray-700 bg-gray-100 dark:bg-gray-600 dark:text-white">
                <?= $page ?>
            </span>
            <a href="?<?= build_query($nextParams) ?>" class="flex items-center justify-center px-3 h-8 text-sm font-medium text-white bg-gray-800 rounded-r hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600">
                Next <i class="fas fa-chevron-right ml-1"></i>
            </a>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<?php if ($role === 'admin'): ?>
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Edit Data</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
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
                            $inputName  = str_replace('/', '_', $col);
                            $inputId    = 'edit_' . str_replace('/', '_', $col);
                        ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= e($label) ?></label>
                                <?php if ($input_type['type'] === 'select'): ?>
                                    <select name="<?= e($inputName) ?>" id="<?= e($inputId) ?>"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">- Pilih -</option>
                                        <?php foreach ($input_type['options'] as $option): ?>
                                            <option value="<?= e($option) ?>"><?= e($option) ?></option>
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

    <script>
        let rowData = <?= json_encode($processedRows) ?>;

        function openEditModal(id) {
            const row = rowData.find(r => r['<?= $pk ?>'] == id);
            if (!row) return;
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
                $inputId = 'edit_' . str_replace('/', '_', $col);
            ?>
            if (document.getElementById('<?= $inputId ?>')) {
                document.getElementById('<?= $inputId ?>').value = row['<?= $col ?>'] || '';
            }
            <?php endforeach; ?>
            document.getElementById('editModal').classList.remove('hidden');
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

        window.onclick = function (event) {
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            if (event.target == editModal) closeEditModal();
            if (event.target == deleteModal) closeDeleteModal();
        }
    </script>

    <!-- Create Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mt-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Tambah <?= e(ucfirst($t)) ?></h2>
        <form method="post" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create">
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
                $inputName  = str_replace('/', '_', $col);
                if ($col === 'link_berkas_permohonan') {
                    $input_type = ['type' => 'url'];
                }
            ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= e($label) ?></label>
                    <?php if ($input_type['type'] === 'select'): ?>
                        <select name="<?= e($inputName) ?>" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">- Pilih -</option>
                            <?php foreach ($input_type['options'] as $option): ?>
                                <option value="<?= e($option) ?>"><?= e($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($input_type['type'] === 'date'): ?>
                        <input type="date" name="<?= e($inputName) ?>"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <?php elseif ($input_type['type'] === 'number'): ?>
                        <input type="number" name="<?= e($inputName) ?>" step="<?= $input_type['step'] ?? '1' ?>"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <?php elseif ($input_type['type'] === 'url'): ?>
                        <input type="url" name="<?= e($inputName) ?>"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="https://...">
                    <?php else: ?>
                        <input type="text" name="<?= e($inputName) ?>"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="md:col-span-2 lg:col-span-3 mt-4 flex justify-end">
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Data
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../inc/layout_footer.php'; ?>