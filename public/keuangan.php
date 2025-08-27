<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/../inc/csrf.php';
require_login();
$title = 'Keuangan';
require __DIR__ . '/../inc/layout_header.php';
require __DIR__ . '/../inc/layout_nav.php';

// Tentukan tab aktif
$active_tab = $_GET['tab'] ?? 'keuangan';
$tabs = [
    'keuangan' => 'Ringkasan Keuangan',
    'anggaran' => 'Data Anggaran', 
    'pengeluaran' => 'Data Pengeluaran'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    
    if ($active_tab === 'anggaran' && isset($_POST['tambah_anggaran'])) {
        // Handle tambah anggaran
        $kode_anggaran = $_POST['kode_anggaran'] ?? '';
        $nama_anggaran = $_POST['nama_anggaran'] ?? '';
        $total_anggaran = $_POST['total_anggaran'] ?? '';
        $tahun = $_POST['tahun'] ?? '';
        
        if (!empty($kode_anggaran) && !empty($nama_anggaran) && !empty($total_anggaran) && !empty($tahun)) {
            try {
                $stmt = db()->prepare("INSERT INTO anggaran (kode_anggaran, nama_anggaran, total_anggaran, tahun) VALUES (?, ?, ?, ?)");
                $stmt->execute([$kode_anggaran, $nama_anggaran, $total_anggaran, $tahun]);
                $_SESSION['success'] = "Data anggaran berhasil ditambahkan";
            } catch (PDOException $e) {
                $_SESSION['error'] = "Gagal menambahkan data: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Semua field harus diisi";
        }
    } 
    elseif ($active_tab === 'pengeluaran' && isset($_POST['tambah_pengeluaran'])) {
        // Handle tambah pengeluaran
        $nomor_kuintasi = $_POST['nomor_kuintasi'] ?? '';
        $kode_anggaran = $_POST['kode_anggaran'] ?? '';
        $jumlah = $_POST['jumlah'] ?? '';
        $tanggal = $_POST['tanggal'] ?? '';
        $kode_mak = $_POST['kode_mak'] ?? '';
        $keterangan = $_POST['keterangan'] ?? '';
        
        if (!empty($nomor_kuintasi) && !empty($kode_anggaran) && !empty($jumlah) && !empty($tanggal) && !empty($kode_mak)) {
            try {
                $stmt = db()->prepare("INSERT INTO pengeluaran (nomor_kuintasi, kode_anggaran, jumlah, tanggal, kode_mak, keterangan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nomor_kuintasi, $kode_anggaran, $jumlah, $tanggal, $kode_mak, $keterangan]);
                $_SESSION['success'] = "Data pengeluaran berhasil ditambahkan";
            } catch (PDOException $e) {
                $_SESSION['error'] = "Gagal menambahkan data: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Semua field wajib harus diisi";
        }
    }
    
    // Redirect untuk menghindari resubmission
    redirect("keuangan.php?tab=" . $active_tab);
}

list($page, $per, $offset) = paginate_params();
$q = trim((string)($_GET['q'] ?? ''));
$where = '';
$params = [];

// Query berdasarkan tab aktif
if ($active_tab === 'pengeluaran') {
    // Query untuk pengeluaran
    $sql = "SELECT p.*, a.nama_anggaran, a.total_anggaran, m.nama_mak
            FROM pengeluaran p
            LEFT JOIN anggaran a ON a.kode_anggaran = p.kode_anggaran
            LEFT JOIN mak m ON m.kode_mak = p.kode_mak";

    $count_sql = "SELECT COUNT(*) FROM pengeluaran p 
                  LEFT JOIN anggaran a ON a.kode_anggaran = p.kode_anggaran
                  LEFT JOIN mak m ON m.kode_mak = p.kode_mak";
    
    $params = [];
    $where_clauses = [];

    // Handle pencarian untuk pengeluaran
    if ($q) {
        $where_clauses[] = "(p.nomor_kuintasi LIKE ? OR p.kode_anggaran LIKE ? OR a.nama_anggaran LIKE ? OR p.keterangan LIKE ? OR m.nama_mak LIKE ?)";
        $search_param = "%$q%";
        $params = array_fill(0, 5, $search_param);
    }

} elseif ($active_tab === 'anggaran') {
    // Query untuk anggaran
    $sql = "SELECT a.* FROM anggaran a";
    $count_sql = "SELECT COUNT(*) FROM anggaran a";
    
    $params = [];
    $where_clauses = [];

    // Handle pencarian untuk anggaran
    if ($q) {
        $where_clauses[] = "(a.kode_anggaran LIKE ? OR a.nama_anggaran LIKE ?)";
        $search_param = "%$q%";
        $params = array_fill(0, 2, $search_param);
    }

} else {
    // Query default (keuangan) - tetap menggunakan pengeluaran untuk ringkasan
    $sql = "SELECT p.*, a.nama_anggaran, a.total_anggaran, m.nama_mak
            FROM pengeluaran p
            LEFT JOIN anggaran a ON a.kode_anggaran = p.kode_anggaran
            LEFT JOIN mak m ON m.kode_mak = p.kode_mak";

    $count_sql = "SELECT COUNT(*) FROM pengeluaran p 
                  LEFT JOIN anggaran a ON a.kode_anggaran = p.kode_anggaran
                  LEFT JOIN mak m ON m.kode_mak = p.kode_mak";
    
    $params = [];
    $where_clauses = [];

    if ($q) {
        $where_clauses[] = "(p.nomor_kuintasi LIKE ? OR p.kode_anggaran LIKE ? OR a.nama_anggaran LIKE ? OR p.keterangan LIKE ? OR m.nama_mak LIKE ?)";
        $search_param = "%$q%";
        $params = array_fill(0, 5, $search_param);
    }
}

// Gabungkan WHERE clause jika ada
if (!empty($where_clauses)) {
    $where = " WHERE " . implode(" AND ", $where_clauses);
    $sql .= $where;
    $count_sql .= $where;
}

// Tambahkan ORDER BY dan LIMIT
$sql .= " ORDER BY " . ($active_tab === 'anggaran' ? "a.kode_anggaran" : "p.tanggal") . " DESC LIMIT ? OFFSET ?";

// Hitung total
$count_stmt = db()->prepare($count_sql);
if ($q) {
    if ($active_tab === 'anggaran') {
        $count_stmt->execute(array_fill(0, 2, "%$q%"));
    } else {
        $count_stmt->execute(array_fill(0, 5, "%$q%"));
    }
} else {
    $count_stmt->execute();
}
$total = (int)$count_stmt->fetchColumn();

// Eksekusi query utama
$stmt = db()->prepare($sql);
$param_index = 0;

// Bind search parameters jika ada
if ($q) {
    $param_count = ($active_tab === 'anggaran') ? 2 : 5;
    for ($i = 0; $i < $param_count; $i++) {
        $stmt->bindValue(++$param_index, "%$q%", PDO::PARAM_STR);
    }
}

// Bind pagination parameters
$stmt->bindValue(++$param_index, $per, PDO::PARAM_INT);
$stmt->bindValue(++$param_index, $offset, PDO::PARAM_INT);

$stmt->execute();
$rows = $stmt->fetchAll();

// Hitung total anggaran dan pengeluaran untuk ringkasan
$total_anggaran = 0;
$total_pengeluaran = 0;
$sisa_anggaran = 0;

if ($active_tab === 'keuangan') {
    $anggaran_stmt = db()->query("SELECT SUM(total_anggaran) as total FROM anggaran");
    $total_anggaran = (float)($anggaran_stmt->fetchColumn() ?? 0);
    
    $pengeluaran_stmt = db()->query("SELECT SUM(jumlah) as total FROM pengeluaran");
    $total_pengeluaran = (float)($pengeluaran_stmt->fetchColumn() ?? 0);
    
    $sisa_anggaran = $total_anggaran - $total_pengeluaran;
}

// Ambil data untuk dropdown
$anggaran_options = [];
$mak_options = [];

if ($active_tab === 'pengeluaran' || (auth_user()['role'] ?? '') === 'admin') {
    $anggaran_stmt = db()->query("SELECT kode_anggaran, nama_anggaran FROM anggaran ORDER BY nama_anggaran");
    $anggaran_options = $anggaran_stmt->fetchAll();
    
    $mak_stmt = db()->query("SELECT kode_mak, nama_mak FROM mak ORDER BY nama_mak");
    $mak_options = $mak_stmt->fetchAll();
}
?>

<h1 class="text-2xl font-semibold mb-4">Manajemen Keuangan</h1>

<!-- Tab Navigation -->
<div class="flex border-b border-gray-200 mb-6">
    <?php foreach ($tabs as $tab_id => $tab_label): ?>
        <a href="?tab=<?= $tab_id ?><?= $q ? '&q='.urlencode($q) : '' ?>" 
           class="py-3 px-6 text-sm font-medium <?= $active_tab === $tab_id ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' ?>">
            <?= e($tab_label) ?>
        </a>
    <?php endforeach; ?>
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

<?php if ($active_tab === 'keuangan'): ?>
    <!-- Ringkasan Keuangan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Anggaran</h3>
            <p class="text-2xl font-bold text-green-600">Rp <?= number_format($total_anggaran, 0, ',', '.') ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Pengeluaran</h3>
            <p class="text-2xl font-bold text-red-600">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Sisa Anggaran</h3>
            <p class="text-2xl font-bold <?= $sisa_anggaran >= 0 ? 'text-blue-600' : 'text-red-600' ?>">
                Rp <?= number_format($sisa_anggaran, 0, ',', '.') ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<?php if ((auth_user()['role'] ?? '') === 'admin'): ?>
<div class="mb-4 flex gap-2">
    <?php if ($active_tab === 'anggaran'): ?>
        <button onclick="toggleForm('anggaranForm')" 
           class="bg-green-600 text-white px-3 py-2 rounded-lg text-sm shadow hover:bg-green-700 transition-colors duration-150"
           title="Tambah Data Anggaran">
           + Tambah Anggaran
        </button>
    <?php elseif ($active_tab === 'pengeluaran'): ?>
        <button onclick="toggleForm('pengeluaranForm')" 
           class="bg-blue-600 text-white px-3 py-2 rounded-lg text-sm shadow hover:bg-blue-700 transition-colors duration-150"
           title="Tambah Data Pengeluaran">
           + Tambah Pengeluaran
        </button>
    <?php endif; ?>
</div>

<!-- Form Tambah Anggaran -->
<?php if ($active_tab === 'anggaran'): ?>
<div id="anggaranForm" class="bg-white rounded-lg shadow p-4 mb-6 hidden">
    <h3 class="text-lg font-semibold mb-3">Tambah Data Anggaran</h3>
    <form method="POST">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Anggaran</label>
                <input type="text" name="kode_anggaran" required 
                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Anggaran</label>
                <input type="text" name="nama_anggaran" required 
                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Total Anggaran (Rp)</label>
                <input type="number" name="total_anggaran" step="0.01" required 
                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select name="tahun" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">- Pilih Tahun -</option>
                    <?php for ($y = date('Y'); $y >= date('Y') - 10; $y--): ?>
                        <option value="<?= $y ?>"><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <button type="button" onclick="toggleForm('anggaranForm')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg transition-colors">
                Batal
            </button>
            <button type="submit" name="tambah_anggaran" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                Simpan
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Form Tambah Pengeluaran -->
<?php if ($active_tab === 'pengeluaran'): ?>
<div id="pengeluaranForm" class="bg-white rounded-lg shadow p-4 mb-6 hidden">
    <h3 class="text-lg font-semibold mb-3">Tambah Data Pengeluaran</h3>
    <form method="POST">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Kuitansi</label>
                <input type="text" name="nomor_kuintasi" required 
                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" name="tanggal" required 
                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Anggaran</label>
                <select name="kode_anggaran" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">- Pilih Anggaran -</option>
                    <?php foreach ($anggaran_options as $option): ?>
                        <option value="<?= e($option['kode_anggaran']) ?>"><?= e($option['kode_anggaran'] . ' - ' . $option['nama_anggaran']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode MAK</label>
                <select name="kode_mak" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">- Pilih MAK -</option>
                    <?php foreach ($mak_options as $option): ?>
                        <option value="<?= e($option['kode_mak']) ?>"><?= e($option['kode_mak'] . ' - ' . $option['nama_mak']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah (Rp)</label>
                <input type="number" name="jumlah" step="0.01" required 
                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" 
                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <button type="button" onclick="toggleForm('pengeluaranForm')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg transition-colors">
                Batal
            </button>
            <button type="submit" name="tambah_pengeluaran" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                Simpan
            </button>
        </div>
    </form>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow p-4">
    <form method="GET" class="mb-3">
        <input type="hidden" name="tab" value="<?= e($active_tab) ?>">
        <div class="flex items-center gap-2 mb-2">
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari data <?= $active_tab ?>..." 
                   class="border rounded px-3 py-2 flex-1">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Cari</button>
            <a href="keuangan.php?tab=<?= e($active_tab) ?>" class="bg-gray-600 text-white px-4 py-2 rounded">Reset</a>
        </div>
        
        <div class="flex gap-3 mt-2">
            <a class="text-sm text-blue-600 hover:underline" href="export.php?<?= build_query(array_merge($_GET, ['fmt'=>'xlsx','t'=>'keuangan'])) ?>">Export Excel</a>
            <a class="text-sm text-blue-600 hover:underline" href="export.php?<?= build_query(array_merge($_GET, ['fmt'=>'csv','t'=>'keuangan'])) ?>">CSV</a>
            <a class="text-sm text-blue-600 hover:underline" href="export.php?<?= build_query(array_merge($_GET, ['fmt'=>'pdf','t'=>'keuangan'])) ?>">PDF</a>
        </div>
    </form>

    <?php if ($total > 0): ?>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <?php if ($active_tab === 'pengeluaran'): ?>
                        <th class="p-2 text-left">Tanggal</th>
                        <th class="p-2 text-left">No Kuintasi</th>
                        <th class="p-2 text-left">Kode Anggaran</th>
                        <th class="p-2 text-left">Nama Anggaran</th>
                        <th class="p-2 text-left">Kode MAK</th>
                        <th class="p-2 text-left">Nama MAK</th>
                        <th class="p-2 text-right">Jumlah</th>
                        <th class="p-2 text-left">Keterangan</th>
                    <?php elseif ($active_tab === 'anggaran'): ?>
                        <th class="p-2 text-left">Kode Anggaran</th>
                        <th class="p-2 text-left">Nama Anggaran</th>
                        <th class="p-2 text-right">Total Anggaran</th>
                        <th class="p-2 text-left">Tahun</th>
                    <?php else: ?>
                        <th class="p-2 text-left">Tanggal</th>
                        <th class="p-2 text-left">No Kuintasi</th>
                        <th class="p-2 text-left">Kode Anggaran</th>
                        <th class="p-2 text-left">Nama Anggaran</th>
                        <th class="p-2 text-left">Kode MAK</th>
                        <th class="p-2 text-left">Nama MAK</th>
                        <th class="p-2 text-right">Jumlah</th>
                        <th class="p-2 text-left">Keterangan</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <?php if ($active_tab === 'pengeluaran'): ?>
                            <td class="p-2"><?= e($r['tanggal']) ?></td>
                            <td class="p-2"><?= e($r['nomor_kuintasi']) ?></td>
                            <td class="p-2"><?= e($r['kode_anggaran']) ?></td>
                            <td class="p-2"><?= e($r['nama_anggaran']) ?></td>
                            <td class="p-2"><?= e($r['kode_mak']) ?></td>
                            <td class="p-2"><?= e($r['nama_mak'] ?? '') ?></td>
                            <td class="p-2 text-right">Rp <?= number_format((float)$r['jumlah'], 0, ',', '.') ?></td>
                            <td class="p-2"><?= e($r['keterangan']) ?></td>
                        <?php elseif ($active_tab === 'anggaran'): ?>
                            <td class="p-2"><?= e($r['kode_anggaran']) ?></td>
                            <td class="p-2"><?= e($r['nama_anggaran']) ?></td>
                            <td class="p-2 text-right">Rp <?= number_format((float)$r['total_anggaran'], 0, ',', '.') ?></td>
                            <td class="p-2"><?= e($r['tahun']) ?></td>
                        <?php else: ?>
                            <td class="p-2"><?= e($r['tanggal']) ?></td>
                            <td class="p-2"><?= e($r['nomor_kuintasi']) ?></td>
                            <td class="p-2"><?= e($r['kode_anggaran']) ?></td>
                            <td class="p-2"><?= e($r['nama_anggaran']) ?></td>
                            <td class="p-2"><?= e($r['kode_mak']) ?></td>
                            <td class="p-2"><?= e($r['nama_mak'] ?? '') ?></td>
                            <td class="p-2 text-right">Rp <?= number_format((float)$r['jumlah'], 0, ',', '.') ?></td>
                            <td class="p-2"><?= e($r['keterangan']) ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="flex justify-between items-center mt-3 text-sm">
        <div>Total Data: <?= number_format($total, 0, ',', '.') ?></div>
        <div class="flex gap-1">
            <?php 
            $paginationParams = $_GET;
            unset($paginationParams['page']);
            
            for ($i = 1; $i <= ceil($total / $per); $i++): 
                $paginationParams['page'] = $i;
            ?>
                <a class="px-2 py-1 border rounded <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700' ?>" 
                   href="?<?= build_query($paginationParams) ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    
    <?php else: ?>
    <div class="text-center py-8 text-gray-500">
        <?php if ($q): ?>
            <p>Tidak ditemukan data untuk pencarian: <strong>"<?= e($q) ?>"</strong></p>
            <p class="text-sm mt-2"><a href="keuangan.php?tab=<?= e($active_tab) ?>" class="text-blue-600 hover:underline">Kembali ke semua data</a></p>
        <?php else: ?>
            <p>Belum ada data <?= $active_tab ?>.</p>
            <?php if ((auth_user()['role'] ?? '') === 'admin'): ?>
                <p class="text-sm mt-2">
                    <?php if ($active_tab === 'anggaran'): ?>
                        <a href="javascript:void(0)" onclick="toggleForm('anggaranForm')" class="text-blue-600 hover:underline">Tambahkan data anggaran</a>
                    <?php elseif ($active_tab === 'pengeluaran'): ?>
                        <a href="javascript:void(0)" onclick="toggleForm('pengeluaranForm')" class="text-blue-600 hover:underline">Tambahkan data pengeluaran</a>
                    <?php else: ?>
                        <a href="keuangan.php?tab=anggaran" class="text-blue-600 hover:underline">Lihat data anggaran</a> atau 
                        <a href="keuangan.php?tab=pengeluaran" class="text-blue-600 hover:underline">data pengeluaran</a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleForm(formId) {
    const form = document.getElementById(formId);
    form.classList.toggle('hidden');
}

// Untuk menutup form ketika klik di luar form
document.addEventListener('click', function(event) {
    const forms = document.querySelectorAll('#anggaranForm, #pengeluaranForm');
    forms.forEach(form => {
        if (!form.classList.contains('hidden') && !form.contains(event.target) && 
            !event.target.matches('button[onclick*="toggleForm"]')) {
            form.classList.add('hidden');
        }
    });
});
</script>

<?php require __DIR__ . '/../inc/layout_footer.php'; ?>