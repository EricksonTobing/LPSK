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
    try {
        csrf_verify();
        
        if ($active_tab === 'anggaran') {
            if (isset($_POST['tambah_anggaran'])) {
                // Handle tambah anggaran
                $kode_anggaran = trim($_POST['kode_anggaran'] ?? '');
                $nama_anggaran = trim($_POST['nama_anggaran'] ?? '');
                $total_anggaran = $_POST['total_anggaran'] ?? '';
                $tahun = $_POST['tahun'] ?? '';
                
                if (!empty($kode_anggaran) && !empty($nama_anggaran) && !empty($total_anggaran) && !empty($tahun)) {
                    $stmt = db()->prepare("INSERT INTO anggaran (kode_anggaran, nama_anggaran, total_anggaran, tahun) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$kode_anggaran, $nama_anggaran, $total_anggaran, $tahun]);
                    $_SESSION['success'] = "Data anggaran berhasil ditambahkan";
                } else {
                    $_SESSION['error'] = "Semua field harus diisi";
                }
            }
            elseif (isset($_POST['edit_anggaran'])) {
                // Handle edit anggaran
                $kode_anggaran = trim($_POST['kode_anggaran'] ?? '');
                $tahun = $_POST['tahun'] ?? '';
                $nama_anggaran = trim($_POST['nama_anggaran'] ?? '');
                $total_anggaran = $_POST['total_anggaran'] ?? '';
                
                if (!empty($kode_anggaran) && !empty($nama_anggaran) && !empty($total_anggaran) && !empty($tahun)) {
                    $stmt = db()->prepare("UPDATE anggaran SET nama_anggaran = ?, total_anggaran = ? WHERE kode_anggaran = ? AND tahun = ?");
                    $stmt->execute([$nama_anggaran, $total_anggaran, $kode_anggaran, $tahun]);
                    $_SESSION['success'] = "Data anggaran berhasil diperbarui";
                } else {
                    $_SESSION['error'] = "Semua field harus diisi";
                }
            }
            elseif (isset($_POST['hapus_anggaran'])) {
                // Handle hapus anggaran
                $kode_anggaran = $_POST['kode_anggaran'] ?? '';
                $tahun = $_POST['tahun'] ?? '';
                
                if (!empty($kode_anggaran) && !empty($tahun)) {
                    // Cek apakah anggaran masih digunakan di pengeluaran
                    $check_stmt = db()->prepare("SELECT COUNT(*) FROM pengeluaran WHERE kode_anggaran = ? AND tahun = ?");
                    $check_stmt->execute([$kode_anggaran, $tahun]);
                    
                    if ($check_stmt->fetchColumn() > 0) {
                        $_SESSION['error'] = "Tidak dapat menghapus anggaran karena masih digunakan dalam pengeluaran";
                    } else {
                        $stmt = db()->prepare("DELETE FROM anggaran WHERE kode_anggaran = ? AND tahun = ?");
                        $stmt->execute([$kode_anggaran, $tahun]);
                        $_SESSION['success'] = "Data anggaran berhasil dihapus";
                    }
                }
            }
        } 
        elseif ($active_tab === 'pengeluaran') {
            if (isset($_POST['tambah_pengeluaran'])) {
                // Handle tambah pengeluaran
                $nomor_kuintasi = trim($_POST['nomor_kuintasi'] ?? '');
                $kode_anggaran = $_POST['kode_anggaran'] ?? '';
                $tahun = $_POST['tahun'] ?? '';
                $jumlah = $_POST['jumlah'] ?? '';
                $tanggal = $_POST['tanggal'] ?? '';
                $kode_mak = $_POST['kode_mak'] ?? '';
                $keterangan = trim($_POST['keterangan'] ?? '');
                
                if (!empty($nomor_kuintasi) && !empty($kode_anggaran) && !empty($tahun) && !empty($jumlah) && !empty($tanggal) && !empty($kode_mak)) {
                    $stmt = db()->prepare("INSERT INTO pengeluaran (nomor_kuintasi, kode_anggaran, tahun, jumlah, tanggal, kode_mak, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nomor_kuintasi, $kode_anggaran, $tahun, $jumlah, $tanggal, $kode_mak, $keterangan]);
                    $_SESSION['success'] = "Data pengeluaran berhasil ditambahkan";
                } else {
                    $_SESSION['error'] = "Semua field wajib harus diisi";
                }
            }
            elseif (isset($_POST['edit_pengeluaran'])) {
                // Handle edit pengeluaran
                $nomor_kuintasi = trim($_POST['nomor_kuintasi'] ?? '');
                $kode_anggaran = $_POST['kode_anggaran'] ?? '';
                $tahun = $_POST['tahun'] ?? '';
                $jumlah = $_POST['jumlah'] ?? '';
                $tanggal = $_POST['tanggal'] ?? '';
                $kode_mak = $_POST['kode_mak'] ?? '';
                $keterangan = trim($_POST['keterangan'] ?? '');
                
                if (!empty($nomor_kuintasi) && !empty($kode_anggaran) && !empty($tahun) && !empty($jumlah) && !empty($tanggal) && !empty($kode_mak)) {
                    $stmt = db()->prepare("UPDATE pengeluaran SET kode_anggaran = ?, tahun = ?, jumlah = ?, tanggal = ?, kode_mak = ?, keterangan = ? WHERE nomor_kuintasi = ?");
                    $stmt->execute([$kode_anggaran, $tahun, $jumlah, $tanggal, $kode_mak, $keterangan, $nomor_kuintasi]);
                    $_SESSION['success'] = "Data pengeluaran berhasil diperbarui";
                } else {
                    $_SESSION['error'] = "Semua field wajib harus diisi";
                }
            }
            elseif (isset($_POST['hapus_pengeluaran'])) {
                // Handle hapus pengeluaran
                $nomor_kuintasi = $_POST['nomor_kuintasi'] ?? '';
                
                if (!empty($nomor_kuintasi)) {
                    $stmt = db()->prepare("DELETE FROM pengeluaran WHERE nomor_kuintasi = ?");
                    $stmt->execute([$nomor_kuintasi]);
                    $_SESSION['success'] = "Data pengeluaran berhasil dihapus";
                }
            }
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Gagal memproses data: " . $e->getMessage();
    }
    
    // Redirect untuk menghindari resubmission - perbaikan di sini
    $redirect_url = "keuangan.php?tab=" . urlencode($active_tab);
    if (!empty($_GET['q'])) {
        $redirect_url .= "&q=" . urlencode($_GET['q']);
    }
    if (!empty($_GET['page'])) {
        $redirect_url .= "&page=" . urlencode($_GET['page']);
    }
    
    header("Location: " . $redirect_url);
    exit();
}

list($page, $per, $offset) = paginate_params();
$q = trim((string)($_GET['q'] ?? ''));
$where = '';
$params = [];

// Query berdasarkan tab aktif
if ($active_tab === 'pengeluaran') {
    // Query untuk pengeluaran - perbaikan sesuai dengan struktur tabel
    $sql = "SELECT p.*, a.nama_anggaran, a.total_anggaran, m.nama_mak
            FROM pengeluaran p
            LEFT JOIN anggaran a ON a.kode_anggaran = p.kode_anggaran AND a.tahun = p.tahun
            LEFT JOIN mak m ON m.kode_mak = p.kode_mak";

    $count_sql = "SELECT COUNT(*) FROM pengeluaran p 
                  LEFT JOIN anggaran a ON a.kode_anggaran = p.kode_anggaran AND a.tahun = p.tahun
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
            LEFT JOIN anggaran a ON a.kode_anggaran = p.kode_anggaran AND a.tahun = p.tahun
            LEFT JOIN mak m ON m.kode_mak = p.kode_mak";

    $count_sql = "SELECT COUNT(*) FROM pengeluaran p 
                  LEFT JOIN anggaran a ON a.kode_anggaran = p.kode_anggaran AND a.tahun = p.tahun
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
    $anggaran_stmt = db()->query("SELECT kode_anggaran, tahun, nama_anggaran FROM anggaran ORDER BY tahun DESC, nama_anggaran");
    $anggaran_options = $anggaran_stmt->fetchAll();
    
    $mak_stmt = db()->query("SELECT kode_mak, nama_mak FROM mak ORDER BY nama_mak");
    $mak_options = $mak_stmt->fetchAll();
}

// Handle untuk mendapatkan data edit
$edit_data = null;
if (isset($_GET['edit']) && $_GET['edit']) {
    if ($active_tab === 'anggaran' && isset($_GET['kode']) && isset($_GET['tahun'])) {
        $edit_stmt = db()->prepare("SELECT * FROM anggaran WHERE kode_anggaran = ? AND tahun = ?");
        $edit_stmt->execute([$_GET['kode'], $_GET['tahun']]);
        $edit_data = $edit_stmt->fetch();
    } elseif ($active_tab === 'pengeluaran' && isset($_GET['nomor'])) {
        $edit_stmt = db()->prepare("SELECT * FROM pengeluaran WHERE nomor_kuintasi = ?");
        $edit_stmt->execute([$_GET['nomor']]);
        $edit_data = $edit_stmt->fetch();
    }
}
?>

<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl md:text-3xl font-semibold mb-6">Manajemen Keuangan</h1>

    <!-- Tab Navigation - Responsive -->
    <div class="flex flex-wrap border-b border-gray-200 mb-6 overflow-x-auto">
        <?php foreach ($tabs as $tab_id => $tab_label): ?>
            <a href="?tab=<?= $tab_id ?><?= $q ? '&q='.urlencode($q) : '' ?>" 
               class="py-3 px-4 md:px-6 text-sm font-medium whitespace-nowrap <?= $active_tab === $tab_id ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' ?>">
                <?= e($tab_label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Alerts -->
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
        <!-- Ringkasan Keuangan - Responsive Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Anggaran</h3>
                <p class="text-2xl font-bold text-green-600">Rp <?= number_format($total_anggaran, 0, ',', '.') ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Pengeluaran</h3>
                <p class="text-2xl font-bold text-red-600">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500 sm:col-span-2 lg:col-span-1">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Sisa Anggaran</h3>
                <p class="text-2xl font-bold <?= $sisa_anggaran >= 0 ? 'text-blue-600' : 'text-red-600' ?>">
                    Rp <?= number_format($sisa_anggaran, 0, ',', '.') ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ((auth_user()['role'] ?? '') === 'admin'): ?>
    <!-- Action Buttons - Responsive -->
    <div class="mb-4 flex flex-wrap gap-2">
        <?php if ($active_tab === 'anggaran'): ?>
            <button onclick="showModal('anggaranModal')" 
               class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm shadow hover:bg-green-700 transition-colors duration-150 flex items-center">
               <i class="fas fa-plus mr-2"></i>
               <span class="hidden sm:inline">Tambah</span> Anggaran
            </button>
        <?php elseif ($active_tab === 'pengeluaran'): ?>
            <button onclick="showModal('pengeluaranModal')" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm shadow hover:bg-blue-700 transition-colors duration-150 flex items-center">
               <i class="fas fa-plus mr-2"></i>
               <span class="hidden sm:inline">Tambah</span> Pengeluaran
            </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Search Form -->
        <div class="p-4 bg-gray-50 border-b">
            <form method="GET" class="space-y-3">
                <input type="hidden" name="tab" value="<?= e($active_tab) ?>">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari data <?= $active_tab ?>..." 
                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>Cari
                        </button>
                        <a href="keuangan.php?tab=<?= e($active_tab) ?>" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-undo mr-2"></i>Reset
                        </a>
                    </div>
                </div>
                
                <!-- Export Links -->
                <div class="flex flex-wrap gap-3 text-sm">
                    <a class="text-blue-600 hover:text-blue-800 hover:underline flex items-center" 
                       href="export.php?<?= build_query(array_merge($_GET, ['fmt'=>'xlsx','t'=>'keuangan'])) ?>">
                        <i class="fas fa-file-excel mr-1"></i>Export Excel
                    </a>
                    <a class="text-blue-600 hover:text-blue-800 hover:underline flex items-center" 
                       href="export.php?<?= build_query(array_merge($_GET, ['fmt'=>'csv','t'=>'keuangan'])) ?>">
                        <i class="fas fa-file-csv mr-1"></i>CSV
                    </a>
                    <a class="text-blue-600 hover:text-blue-800 hover:underline flex items-center" 
                       href="export.php?<?= build_query(array_merge($_GET, ['fmt'=>'pdf','t'=>'keuangan'])) ?>">
                        <i class="fas fa-file-pdf mr-1"></i>PDF
                    </a>
                </div>
            </form>
        </div>

        <?php if ($total > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <?php if ($active_tab === 'pengeluaran'): ?>
                            <th class="p-3 text-left font-semibold">Tanggal</th>
                            <th class="p-3 text-left font-semibold">No Kuitansi</th>
                            <th class="p-3 text-left font-semibold">Kode Anggaran</th>
                            <th class="p-3 text-left font-semibold">Nama Anggaran</th>
                            <th class="p-3 text-left font-semibold">Tahun</th>
                            <th class="p-3 text-left font-semibold">Kode MAK</th>
                            <th class="p-3 text-left font-semibold">Nama MAK</th>
                            <th class="p-3 text-right font-semibold">Jumlah</th>
                            <th class="p-3 text-left font-semibold">Keterangan</th>
                            <?php if ((auth_user()['role'] ?? '') === 'admin'): ?>
                                <th class="p-3 text-center font-semibold">Aksi</th>
                            <?php endif; ?>
                        <?php elseif ($active_tab === 'anggaran'): ?>
                            <th class="p-3 text-left font-semibold">Kode Anggaran</th>
                            <th class="p-3 text-left font-semibold">Nama Anggaran</th>
                            <th class="p-3 text-right font-semibold">Total Anggaran</th>
                            <th class="p-3 text-left font-semibold">Tahun</th>
                            <?php if ((auth_user()['role'] ?? '') === 'admin'): ?>
                                <th class="p-3 text-center font-semibold">Aksi</th>
                            <?php endif; ?>
                        <?php else: ?>
                            <th class="p-3 text-left font-semibold">Tanggal</th>
                            <th class="p-3 text-left font-semibold">No Kuitansi</th>
                            <th class="p-3 text-left font-semibold">Kode Anggaran</th>
                            <th class="p-3 text-left font-semibold">Nama Anggaran</th>
                            <th class="p-3 text-left font-semibold">Tahun</th>
                            <th class="p-3 text-left font-semibold">Kode MAK</th>
                            <th class="p-3 text-left font-semibold">Nama MAK</th>
                            <th class="p-3 text-right font-semibold">Jumlah</th>
                            <th class="p-3 text-left font-semibold">Keterangan</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($rows as $r): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <?php if ($active_tab === 'pengeluaran'): ?>
                                <td class="p-3"><?= e($r['tanggal']) ?></td>
                                <td class="p-3 font-medium"><?= e($r['nomor_kuintasi']) ?></td>
                                <td class="p-3"><?= e($r['kode_anggaran']) ?></td>
                                <td class="p-3"><?= e($r['nama_anggaran']) ?></td>
                                <td class="p-3"><?= e($r['tahun']) ?></td>
                                <td class="p-3"><?= e($r['kode_mak']) ?></td>
                                <td class="p-3"><?= e($r['nama_mak'] ?? '') ?></td>
                                <td class="p-3 text-right font-medium">Rp <?= number_format((float)$r['jumlah'], 0, ',', '.') ?></td>
                                <td class="p-3"><?= e($r['keterangan']) ?></td>
                                <?php if ((auth_user()['role'] ?? '') === 'admin'): ?>
                                    <td class="p-3 text-center">
                                        <div class="flex justify-center gap-1">
                                            <a href="#" onclick="editPengeluaran('<?= e($r['nomor_kuintasi']) ?>')" 
                                               class="bg-yellow-500 text-white px-2 py-1 rounded text-xs hover:bg-yellow-600 transition-colors" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="nomor_kuintasi" value="<?= e($r['nomor_kuintasi']) ?>">
                                                <button type="submit" name="hapus_pengeluaran" 
                                                        class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600 transition-colors" 
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            <?php elseif ($active_tab === 'anggaran'): ?>
                                <td class="p-3 font-medium"><?= e($r['kode_anggaran']) ?></td>
                                <td class="p-3"><?= e($r['nama_anggaran']) ?></td>
                                <td class="p-3 text-right font-medium">Rp <?= number_format((float)$r['total_anggaran'], 0, ',', '.') ?></td>
                                <td class="p-3"><?= e($r['tahun']) ?></td>
                                <?php if ((auth_user()['role'] ?? '') === 'admin'): ?>
                                    <td class="p-3 text-center">
                                        <div class="flex justify-center gap-1">
                                            <a href="#" onclick="editAnggaran('<?= e($r['kode_anggaran']) ?>', '<?= e($r['tahun']) ?>')" 
                                               class="bg-yellow-500 text-white px-2 py-1 rounded text-xs hover:bg-yellow-600 transition-colors" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="kode_anggaran" value="<?= e($r['kode_anggaran']) ?>">
                                                <input type="hidden" name="tahun" value="<?= e($r['tahun']) ?>">
                                                <button type="submit" name="hapus_anggaran" 
                                                        class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600 transition-colors" 
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            <?php else: ?>
                                <td class="p-3"><?= e($r['tanggal']) ?></td>
                                <td class="p-3 font-medium"><?= e($r['nomor_kuintasi']) ?></td>
                                <td class="p-3"><?= e($r['kode_anggaran']) ?></td>
                                <td class="p-3"><?= e($r['nama_anggaran']) ?></td>
                                <td class="p-3"><?= e($r['tahun']) ?></td>
                                <td class="p-3"><?= e($r['kode_mak']) ?></td>
                                <td class="p-3"><?= e($r['nama_mak'] ?? '') ?></td>
                                <td class="p-3 text-right font-medium">Rp <?= number_format((float)$r['jumlah'], 0, ',', '.') ?></td>
                                <td class="p-3"><?= e($r['keterangan']) ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex flex-col sm:flex-row justify-between items-center px-4 py-3 bg-gray-50 border-t text-sm">
            <div class="mb-2 sm:mb-0">
                <span class="text-gray-700">
                    Total Data: <span class="font-semibold"><?= number_format($total, 0, ',', '.') ?></span>
                </span>
            </div>
            <div class="flex flex-wrap justify-center gap-1">
                <?php 
                $paginationParams = $_GET;
                unset($paginationParams['page']);
                
                $totalPages = ceil($total / $per);
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                // Previous button
                if ($page > 1):
                    $paginationParams['page'] = $page - 1;
                ?>
                    <a class="px-3 py-2 text-sm border rounded bg-white text-gray-700 hover:bg-gray-50 transition-colors" 
                       href="?<?= build_query($paginationParams) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): 
                    $paginationParams['page'] = $i;
                ?>
                    <a class="px-3 py-2 text-sm border rounded transition-colors <?= $i == $page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 hover:bg-gray-50' ?>" 
                       href="?<?= build_query($paginationParams) ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php 
                // Next button
                if ($page < $totalPages):
                    $paginationParams['page'] = $page + 1;
                ?>
                    <a class="px-3 py-2 text-sm border rounded bg-white text-gray-700 hover:bg-gray-50 transition-colors" 
                       href="?<?= build_query($paginationParams) ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php else: ?>
        <div class="text-center py-12 px-4">
            <div class="mb-4">
                <i class="fas fa-inbox text-6xl text-gray-300"></i>
            </div>
            <?php if ($q): ?>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ditemukan data</h3>
                <p class="text-gray-600 mb-4">Pencarian untuk "<strong><?= e($q) ?></strong>" tidak menghasilkan data.</p>
                <a href="keuangan.php?tab=<?= e($active_tab) ?>" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke semua data
                </a>
            <?php else: ?>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data <?= $active_tab ?></h3>
                <p class="text-gray-600 mb-4">Mulai dengan menambahkan data <?= $active_tab ?> pertama Anda.</p>
                <?php if ((auth_user()['role'] ?? '') === 'admin'): ?>
                    <?php if ($active_tab === 'anggaran'): ?>
                        <button onclick="showModal('anggaranModal')" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Tambahkan data anggaran
                        </button>
                    <?php elseif ($active_tab === 'pengeluaran'): ?>
                        <button onclick="showModal('pengeluaranModal')" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Tambahkan data pengeluaran
                        </button>
                    <?php else: ?>
                        <div class="space-x-2">
                            <a href="keuangan.php?tab=anggaran" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-chart-line mr-2"></i>Lihat data anggaran
                            </a>
                            <a href="keuangan.php?tab=pengeluaran" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-receipt mr-2"></i>Lihat data pengeluaran
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Anggaran -->
<div id="anggaranModal" class="modal fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="modal-header bg-blue-600 text-white p-4 rounded-t-lg flex justify-between items-center">
            <h3 class="text-lg font-semibold"><?= $edit_data ? 'Edit' : 'Tambah' ?> Anggaran</h3>
            <button onclick="hideModal('anggaranModal')" class="text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" class="p-4">
            <?= csrf_field() ?>
            <?php if ($edit_data): ?>
                <input type="hidden" name="edit_anggaran" value="1">
            <?php else: ?>
                <input type="hidden" name="tambah_anggaran" value="1">
            <?php endif; ?>
            
            <div class="space-y-4">
                <div>
                    <label for="kode_anggaran" class="block text-sm font-medium text-gray-700 mb-1">Kode Anggaran</label>
                    <input type="text" id="kode_anggaran" name="kode_anggaran" 
                           value="<?= e($edit_data['kode_anggaran'] ?? '') ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           required <?= $edit_data ? 'readonly' : '' ?>>
                </div>
                
                <div>
                    <label for="nama_anggaran" class="block text-sm font-medium text-gray-700 mb-1">Nama Anggaran</label>
                    <input type="text" id="nama_anggaran" name="nama_anggaran" 
                           value="<?= e($edit_data['nama_anggaran'] ?? '') ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           required>
                </div>
                
                <div>
                    <label for="total_anggaran" class="block text-sm font-medium text-gray-700 mb-1">Total Anggaran</label>
                    <input type="number" id="total_anggaran" name="total_anggaran" 
                           value="<?= e($edit_data['total_anggaran'] ?? '') ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           required min="0" step="1000">
                </div>
                
                <div>
                    <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                    <input type="number" id="tahun" name="tahun" 
                           value="<?= e($edit_data['tahun'] ?? date('Y')) ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           required min="2000" max="2100">
                </div>
            </div>
            
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="hideModal('anggaranModal')" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <?= $edit_data ? 'Update' : 'Simpan' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pengeluaran -->
<div id="pengeluaranModal" class="modal fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="modal-header bg-blue-600 text-white p-4 rounded-t-lg flex justify-between items-center">
            <h3 class="text-lg font-semibold"><?= $edit_data ? 'Edit' : 'Tambah' ?> Pengeluaran</h3>
            <button onclick="hideModal('pengeluaranModal')" class="text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" class="p-4">
            <?= csrf_field() ?>
            <?php if ($edit_data): ?>
                <input type="hidden" name="edit_pengeluaran" value="1">
            <?php else: ?>
                <input type="hidden" name="tambah_pengeluaran" value="1">
            <?php endif; ?>
            
            <div class="space-y-4">
                <div>
                    <label for="nomor_kuintasi" class="block text-sm font-medium text-gray-700 mb-1">Nomor Kuitansi</label>
                    <input type="text" id="nomor_kuintasi" name="nomor_kuintasi" 
                           value="<?= e($edit_data['nomor_kuintasi'] ?? '') ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           required <?= $edit_data ? 'readonly' : '' ?>>
                </div>
                
                <div>
                    <label for="kode_anggaran" class="block text-sm font-medium text-gray-700 mb-1">Kode Anggaran</label>
                    <select id="kode_anggaran" name="kode_anggaran" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                        <option value="">-- Pilih Kode Anggaran --</option>
                        <?php foreach ($anggaran_options as $option): ?>
                            <option value="<?= e($option['kode_anggaran']) ?>" 
                                    data-tahun="<?= e($option['tahun']) ?>"
                                    <?= ($edit_data['kode_anggaran'] ?? '') === $option['kode_anggaran'] && ($edit_data['tahun'] ?? '') === $option['tahun'] ? 'selected' : '' ?>>
                                <?= e($option['kode_anggaran']) ?> - <?= e($option['nama_anggaran']) ?> (<?= e($option['tahun']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                    <input type="number" id="tahun" name="tahun" 
                           value="<?= e($edit_data['tahun'] ?? date('Y')) ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           required min="2000" max="2100">
                </div>
                
                <div>
                    <label for="jumlah" class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                    <input type="number" id="jumlah" name="jumlah" 
                           value="<?= e($edit_data['jumlah'] ?? '') ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           required min="0" step="1000">
                </div>
                
                <div>
                    <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" 
                           value="<?= e($edit_data['tanggal'] ?? date('Y-m-d')) ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           required>
                </div>
                
                <div>
                    <label for="kode_mak" class="block text-sm font-medium text-gray-700 mb-1">Kode MAK</label>
                    <select id="kode_mak" name="kode_mak" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                        <option value="">-- Pilih Kode MAK --</option>
                        <?php foreach ($mak_options as $option): ?>
                            <option value="<?= e($option['kode_mak']) ?>" 
                                    <?= ($edit_data['kode_mak'] ?? '') === $option['kode_mak'] ? 'selected' : '' ?>>
                                <?= e($option['kode_mak']) ?> - <?= e($option['nama_mak']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" 
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                              rows="3"><?= e($edit_data['keterangan'] ?? '') ?></textarea>
                </div>
            </div>
            
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="hideModal('pengeluaranModal')" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <?= $edit_data ? 'Update' : 'Simpan' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Fungsi untuk menampilkan modal
function showModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

// Fungsi untuk menyembunyikan modal
function hideModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    // Reset form jika modal ditutup
    document.querySelector(`#${modalId} form`).reset();
}

// Fungsi untuk mengedit anggaran
function editAnggaran(kode, tahun) {
    // Redirect ke halaman dengan parameter edit
    window.location.href = `keuangan.php?tab=anggaran&edit=1&kode=${encodeURIComponent(kode)}&tahun=${encodeURIComponent(tahun)}`;
}

// Fungsi untuk mengedit pengeluaran
function editPengeluaran(nomor) {
    // Redirect ke halaman dengan parameter edit
    window.location.href = `keuangan.php?tab=pengeluaran&edit=1&nomor=${encodeURIComponent(nomor)}`;
}

// Event listener untuk tombol escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (!modal.classList.contains('hidden')) {
                hideModal(modal.id);
            }
        });
    }
});

// Event listener untuk klik di luar modal
document.addEventListener('click', function(e) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (e.target === modal) {
            hideModal(modal.id);
        }
    });
});

// Auto-sync tahun dengan kode anggaran
document.getElementById('kode_anggaran')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const tahun = selectedOption.getAttribute('data-tahun');
    if (tahun) {
        document.getElementById('tahun').value = tahun;
    }
});

// Tampilkan modal jika ada data edit
<?php if ($edit_data): ?>
    window.addEventListener('DOMContentLoaded', function() {
        <?php if ($active_tab === 'anggaran'): ?>
            showModal('anggaranModal');
        <?php elseif ($active_tab === 'pengeluaran'): ?>
            showModal('pengeluaranModal');
        <?php endif; ?>
    });
<?php endif; ?>
</script>

<?php
require __DIR__ . '/../inc/layout_footer.php';
?>