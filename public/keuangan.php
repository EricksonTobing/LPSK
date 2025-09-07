<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/../inc/csrf.php';

require_login();
// Pastikan session sudah started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan session messages ada
if (!isset($_SESSION['error'])) $_SESSION['error'] = null;
if (!isset($_SESSION['success'])) $_SESSION['success'] = null;
$title = 'Keuangan';

// Handle form submission
$active_tab = $_GET['tab'] ?? 'keuangan';
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
                    // Validasi tahun
                    if ($tahun < 2000 || $tahun > 2100) {
                        $_SESSION['error'] = "Tahun harus antara 2000 dan 2100";
                    } else {
                        $stmt = db()->prepare("INSERT INTO anggaran (kode_anggaran, nama_anggaran, total_anggaran, tahun) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$kode_anggaran, $nama_anggaran, $total_anggaran, $tahun]);
                        $_SESSION['success'] = "Data anggaran berhasil ditambahkan";
                    }
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
                    // Validasi tahun
                    if ($tahun < 2000 || $tahun > 2100) {
                        $_SESSION['error'] = "Tahun harus antara 2000 dan 2100";
                    } else {
                        $stmt = db()->prepare("UPDATE anggaran SET nama_anggaran = ?, total_anggaran = ? WHERE kode_anggaran = ? AND tahun = ?");
                        $stmt->execute([$nama_anggaran, $total_anggaran, $kode_anggaran, $tahun]);
                        $_SESSION['success'] = "Data anggaran berhasil diperbarui";
                    }
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
        
    }
     catch (Exception $e) {
        // Tangani error di sini
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    }
  // Redirect untuk menghindari resubmission
    $redirect_url = 'keuangan.php?tab=' . urlencode($active_tab);

    // Pertahankan parameter pencarian
    if (!empty($_GET['q'])) {
        $redirect_url .= '&q=' . urlencode($_GET['q']);
    }

    // Pertahankan parameter tahun
    if (!empty($_GET['tahun'])) {
        $redirect_url .= '&tahun=' . urlencode($_GET['tahun']);
    }

    // Pertahankan parameter page untuk pagination
    if (!empty($_GET['page']) && $_GET['page'] > 1) {
        $redirect_url .= '&page=' . (int)$_GET['page'];
    }

    // Pastikan tidak ada output sebelum redirect
    if (!headers_sent()) {
        header("Location: " . $redirect_url);
        exit();
    } else {
        // Jika headers sudah dikirim, gunakan JavaScript redirect
        echo "<script>window.location.href = '" . htmlspecialchars($redirect_url) . "';</script>";
        exit();
    }
}



require __DIR__ . '/../inc/layout_header.php';
require __DIR__ . '/../inc/layout_nav.php';

// Tentukan tab aktif
$tabs = [
    'keuangan' => 'Ringkasan Keuangan',
    'anggaran' => 'Data Anggaran', 
    'pengeluaran' => 'Data Pengeluaran'
];

list($page, $per, $offset) = paginate_params();
$q = trim((string)($_GET['q'] ?? ''));
// Default tahun filter ke tahun terkini untuk tab ringkasan keuangan
$current_year = date('Y');
$tahun_filter = $_GET['tahun'] ?? ($active_tab === 'keuangan' ? $current_year : '');

$where = '';
$params = [];

// Query berdasarkan tab aktif
if ($active_tab === 'pengeluaran') {
    // Query untuk pengeluaran - perbaikan sesuai dengan struktur tabel
    $sql = "SELECT p.*, a.nama_anggaran, m.nama_mak
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

    // Filter tahun untuk pengeluaran
    if (!empty($tahun_filter)) {
        $where_clauses[] = "p.tahun = ?";
        $params[] = $tahun_filter;
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

    // Filter tahun untuk anggaran
    if (!empty($tahun_filter)) {
        $where_clauses[] = "a.tahun = ?";
        $params[] = $tahun_filter;
    }

} else {
    // Query default (keuangan) - tetap menggunakan pengeluaran untuk ringkasan
    $sql = "SELECT p.*, a.nama_anggaran, m.nama_mak
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

    // Filter tahun untuk ringkasan keuangan
    if (!empty($tahun_filter)) {
        $where_clauses[] = "p.tahun = ?";
        $params[] = $tahun_filter;
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
if (!empty($params)) {
    $count_stmt->execute($params);
} else {
    $count_stmt->execute();
}
$total = (int)$count_stmt->fetchColumn();

// Eksekusi query utama
$stmt = db()->prepare($sql);
$param_index = 0;

// Bind search parameters jika ada
if (!empty($params)) {
    foreach ($params as $param) {
        $stmt->bindValue(++$param_index, $param, PDO::PARAM_STR);
    }
}

// Bind pagination parameters
$stmt->bindValue(++$param_index, $per, PDO::PARAM_INT);
$stmt->bindValue(++$param_index, $offset, PDO::PARAM_INT);

$stmt->execute();
$rows = $stmt->fetchAll();

// Hitung total anggaran dan pengeluaran untuk ringkasan dengan filter tahun
$total_anggaran = 0;
$total_pengeluaran = 0;
$sisa_anggaran = 0;

if ($active_tab === 'keuangan') {
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

// Ambil daftar tahun untuk filter - hanya tahun yang ada data di database
$tahun_options = [];
$tahun_stmt = db()->query("SELECT DISTINCT tahun FROM (
    SELECT tahun FROM anggaran 
    UNION 
    SELECT tahun FROM pengeluaran
) AS years ORDER BY tahun DESC");
$tahun_options = $tahun_stmt->fetchAll(PDO::FETCH_COLUMN);

// Jika tidak ada data tahun, tambahkan tahun berjalan
if (empty($tahun_options)) {
    $tahun_options = [date('Y')];
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

<div class="container mx-auto px-4 py-6 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300">
    <h1 class="text-2xl md:text-3xl font-semibold mb-6">Manajemen Keuangan</h1>

    <!-- Tab Navigation - Responsive -->
    <div class="flex flex-wrap border-b border-gray-200 dark:border-gray-700 mb-6 overflow-x-auto">
        <?php foreach ($tabs as $tab_id => $tab_label): ?>
            <a href="?tab=<?= $tab_id ?><?= $q ? '&q='.urlencode($q) : '' ?><?= $tahun_filter ? '&tahun='.urlencode($tahun_filter) : '' ?>" 
               class="py-3 px-4 md:px-6 text-sm font-medium whitespace-nowrap <?= $active_tab === $tab_id ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' ?>">
                <?= e($tab_label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 dark:text-red-300"><?= e($_SESSION['error']) ?></p>
                </div>
            </div>
            <?php unset($_SESSION['error']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 mb-6 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700 dark:text-green-300"><?= e($_SESSION['success']) ?></p>
                </div>
            </div>
            <?php unset($_SESSION['success']) ?>
        </div>
    <?php endif; ?>

    <?php if ($active_tab === 'keuangan'): ?>
        <!-- Ringkasan Keuangan - Responsive Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Total Anggaran</h3>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">Rp <?= number_format($total_anggaran, 0, ',', '.') ?></p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tahun: <?= $tahun_filter ?: 'Semua Tahun' ?></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Total Pengeluaran</h3>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tahun: <?= $tahun_filter ?: 'Semua Tahun' ?></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-l-4 border-blue-500 sm:col-span-2 lg:col-span-1">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Sisa Anggaran</h3>
                <p class="text-2xl font-bold <?= $sisa_anggaran >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' ?>">
                    Rp <?= number_format($sisa_anggaran, 0, ',', '.') ?>
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tahun: <?= $tahun_filter ?: 'Semua Tahun' ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ((auth_user()['role'] ?? '') === 'admin'): ?>
    <!-- Action Buttons - Responsive -->
    <div class="mb-4 flex flex-wrap gap-2">
        <?php if ($active_tab === 'anggaran'): ?>
            <button onclick="showModal('anggaranModal')" 
               class="px-4 py-2 bg-gradient-to-r from-primary-blue to-primary-red hover:from-primary-blue/90 hover:to-primary-red/90 text-white rounded-lg flex items-center text-sm transition-all shadow-md hover:shadow-lg ml-2">
               <i class="fas fa-plus mr-2"></i>
               <span class="hidden sm:inline">Tambah</span> Anggaran
            </button>
        <?php elseif ($active_tab === 'pengeluaran'): ?>
            <button onclick="showModal('pengeluaranModal')" 
               class="px-4 py-2 bg-gradient-to-r from-primary-blue to-primary-red hover:from-primary-blue/90 hover:to-primary-red/90 text-white rounded-lg flex items-center text-sm transition-all shadow-md hover:shadow-lg ml-2">
               <i class="fas fa-plus mr-2"></i>
               <span class="hidden sm:inline">Tambah</span> Pengeluaran
            </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Data Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <!-- Search Form -->
        <div class="p-4 bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
            <form method="GET" class="space-y-3">
                <input type="hidden" name="tab" value="<?= e($active_tab) ?>">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari data <?= $active_tab ?>..." 
                           class="flex-1 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    
                    <!-- Filter Tahun -->
                    <select name="tahun" class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Semua Tahun</option>
                        <?php foreach ($tahun_options as $tahun_opt): ?>
                            <option value="<?= e($tahun_opt) ?>" <?= $tahun_filter == $tahun_opt ? 'selected' : '' ?>>
                                <?= e($tahun_opt) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-primary-blue hover:bg-primary-blue/90 text-white rounded-lg transition-all flex items-center justify-center shadow-md hover:shadow-lg">
                            <i class="fas fa-search mr-2"></i>Cari
                        </button>
                        <a href="keuangan.php?tab=<?= e($active_tab) ?>" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-undo mr-2"></i>Reset
                        </a>
                    </div>
                </div>
                
                <!-- Export Links -->
                <div class="flex flex-wrap gap-3 text-sm">
                    <?php if ($active_tab === 'pengeluaran'): ?>
                       
                        <a class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline flex items-center" 
                           href="export_spout.php?t=pengeluaran&<?= http_build_query($_GET) ?>">
                            <i class="fas fa-rocket mr-2"></i> Export (Spout)
                        </a>
                    <?php elseif ($active_tab === 'anggaran'): ?>
                         
                        <a class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline flex items-center" 
                           href="export_spout.php?t=anggaran&<?= http_build_query($_GET) ?>">
                            <i class="fas fa-rocket mr-2"></i> Export (Spout)
                        </a>
                    <?php else: ?>
                     
                        <!-- Untuk tab keuangan, buat export khusus ringkasan -->
                        <!-- <a class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline flex items-center" 
                           href="export_spout.php?t=ringkasan&<?= http_build_query($_GET) ?>">
                            <i class="fas fa-rocket mr-2"></i> Export (Spout)
                        </a> -->
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if ($total > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <?php if ($active_tab === 'pengeluaran'): ?>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Tanggal</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">No Kuitansi</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Kode Anggaran</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Nama Anggaran</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Tahun</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Kode MAK</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Nama MAK</th>
                            <th class="p-3 text-right font-semibold text-gray-900 dark:text-gray-100">Jumlah</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Keterangan</th>
                            <?php if ((auth_user()['role'] ?? '') === 'admin'): ?>
                                <th class="p-3 text-center font-semibold text-gray-900 dark:text-gray-100">Aksi</th>
                            <?php endif; ?>
                        <?php elseif ($active_tab === 'anggaran'): ?>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Kode Anggaran</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Nama Anggaran</th>
                            <th class="p-3 text-right font-semibold text-gray-900 dark:text-gray-100">Total Anggaran</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Tahun</th>
                            <?php if ((auth_user()['role'] ?? '') === 'admin'): ?>
                                <th class="p-3 text-center font-semibold text-gray-900 dark:text-gray-100">Aksi</th>
                            <?php endif; ?>
                        <?php else: ?>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Tanggal</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">No Kuitansi</th>
                            <th class="p-3 text-left font-semibold text-gray-90 dark:text-gray-100">Kode Anggaran</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Nama Anggaran</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Tahun</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Kode MAK</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Nama MAK</th>
                            <th class="p-3 text-right font-semibold text-gray-900 dark:text-gray-100">Jumlah</th>
                            <th class="p-3 text-left font-semibold text-gray-900 dark:text-gray-100">Keterangan</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($rows as $r): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <?php if ($active_tab === 'pengeluaran'): ?>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['tanggal']) ?></td>
                                <td class="p-3 font-medium text-gray-900 dark:text-gray-100"><?= e($r['nomor_kuintasi']) ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['kode_anggaran']) ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['nama_anggaran']) ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['tahun']) ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['kode_mak']) ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['nama_mak'] ?? '') ?></td>
                                <td class="p-3 text-right font-medium text-gray-900 dark:text-gray-100">Rp <?= number_format((float)$r['jumlah'], 0, ',', '.') ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['keterangan']) ?></td>
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
                                <td class="p-3 font-medium text-gray-900 dark:text-gray-100"><?= e($r['kode_anggaran']) ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['nama_anggaran']) ?></td>
                                <td class="p-3 text-right font-medium text-gray-900 dark:text-gray-100">Rp <?= number_format((float)$r['total_anggaran'], 0, ',', '.') ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['tahun']) ?></td>
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
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['tanggal']) ?></td>
                                <td class="p-3 font-medium text-gray-900 dark:text-gray-100"><?= e($r['nomor_kuintasi']) ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['kode_anggaran']) ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['nama_anggaran']) ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['tahun']) ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['kode_mak']) ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['nama_mak'] ?? '') ?></td>
                                <td class="p-3 text-right font-medium text-gray-900 dark:text-gray-100">Rp <?= number_format((float)$r['jumlah'], 0, ',', '.') ?></td>
                                <td class="p-3 text-gray-900 dark:text-gray-100"><?= e($r['keterangan']) ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex flex-col sm:flex-row justify-between items-center px-4 py-3 bg-gray-50 dark:bg-gray-700 border-t text-sm">
            <div class="mb-2 sm:mb-0">
                <span class="text-gray-700 dark:text-gray-300">
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
                    <a class="px-3 py-2 text-sm border rounded bg-white dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" 
                       href="?<?= build_query($paginationParams) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
               
                <?php for ($i = $startPage; $i <= $endPage; $i++): 
                    $paginationParams['page'] = $i;
                ?>
                    <a class="px-3 py-2 text-sm border rounded <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' ?> transition-colors" 
                       href="?<?= build_query($paginationParams) ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
              
                <?php if ($page < $totalPages): 
                    $paginationParams['page'] = $page + 1;
                ?>
                    <a class="px-3 py-2 text-sm border rounded bg-white dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" 
                       href="?<?= build_query($paginationParams) ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                <p>Tidak ada data <?= $active_tab ?> yang ditemukan.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Anggaran -->
<div id="anggaranModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden transition-opacity duration-300">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?= $edit_data ? 'Edit' : 'Tambah' ?> Data Anggaran</h3>
            <button onclick="hideModal('anggaranModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" class="p-4 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="<?= $edit_data ? 'edit_anggaran' : 'tambah_anggaran' ?>" value="1">
            
            <?php if ($edit_data): ?>
                <input type="hidden" name="kode_anggaran" value="<?= e($edit_data['kode_anggaran']) ?>">
                <input type="hidden" name="tahun" value="<?= e($edit_data['tahun']) ?>">
            <?php else: ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode Anggaran</label>
                    <input type="text" name="kode_anggaran" required 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                           value="<?= e($edit_data['kode_anggaran'] ?? '') ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                    <input type="number" name="tahun" required min="2000" max="2100" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                           value="<?= e($edit_data['tahun'] ?? date('Y')) ?>">
                </div>
            <?php endif; ?>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Anggaran</label>
                <input type="text" name="nama_anggaran" required 
                       class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                       value="<?= e($edit_data['nama_anggaran'] ?? '') ?>">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Anggaran</label>
                <input type="number" name="total_anggaran" required step="0.01" 
                       class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                       value="<?= e($edit_data['total_anggaran'] ?? '') ?>">
            </div>
            
            <div class="flex justify-end gap-2 pt-4 border-t dark:border-gray-700">
                <button type="button" onclick="hideModal('anggaranModal')" 
                        class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <?= $edit_data ? 'Update' : 'Simpan' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pengeluaran   -->
<div id="pengeluaranModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden transition-opacity duration-300">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?= $edit_data ? 'Edit' : 'Tambah' ?> Data Pengeluaran</h3>
            <button onclick="hideModal('pengeluaranModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" class="p-4 space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="<?= $edit_data ? 'edit_pengeluaran' : 'tambah_pengeluaran' ?>" value="1">
            
            <?php if ($edit_data): ?>
                <input type="hidden" name="nomor_kuintasi" value="<?= e($edit_data['nomor_kuintasi']) ?>">
            <?php else: ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor Kuitansi</label>
                    <input type="text" name="nomor_kuintasi" required 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                           value="<?= e($edit_data['nomor_kuintasi'] ?? '') ?>">
                </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode Anggaran</label>
    <select name="kode_anggaran" required 
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            id="kode_anggaran_select">
        <option value="">Pilih Kode Anggaran</option>
        <?php foreach ($anggaran_options as $opt): ?>
            <option value="<?= e($opt['kode_anggaran']) ?>" 
                    data-tahun="<?= e($opt['tahun']) ?>"
                    <?= ($edit_data['kode_anggaran'] ?? '') == $opt['kode_anggaran'] && ($edit_data['tahun'] ?? '') == $opt['tahun'] ? 'selected' : '' ?>>
                <?= e($opt['kode_anggaran']) ?> - <?= e($opt['nama_anggaran']) ?> (<?= e($opt['tahun']) ?>)
            </option>
        <?php endforeach; ?>
    </select>
</div>          
                <!-- TAMBAHKAN INPUT HIDDEN UNTUK TAHUN -->
<input type="hidden" name="tahun" id="tahun_hidden" value="<?= e($edit_data['tahun'] ?? '') ?>">
<!-- TAMBAHKAN FIELD TAHUN UNTUK DISPLAY SAJA -->
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
    <input type="text" id="tahun_display" readonly 
           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-gray-100 dark:bg-gray-700 dark:text-white"
           value="<?= e($edit_data['tahun'] ?? '') ?>">
</div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah</label>
                    <input type="number" name="jumlah" required step="0.01" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                           value="<?= e($edit_data['jumlah'] ?? '') ?>">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" required 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                           value="<?= e($edit_data['tanggal'] ?? date('Y-m-d')) ?>">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode MAK</label>
                <select name="kode_mak" required 
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Pilih Kode MAK</option>
                    <?php foreach ($mak_options as $opt): ?>
                        <option value="<?= e($opt['kode_mak']) ?>" 
                                <?= ($edit_data['kode_mak'] ?? '') == $opt['kode_mak'] ? 'selected' : '' ?>>
                            <?= e($opt['kode_mak']) ?> - <?= e($opt['nama_mak']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan</label>
                <textarea name="keterangan" rows="2"
                          class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"><?= e($edit_data['keterangan'] ?? '') ?></textarea>
            </div>
            
            <div class="flex justify-end gap-2 pt-4 border-t dark:border-gray-700">
                <button type="button" onclick="hideModal('pengeluaranModal')" 
                        class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <?= $edit_data ? 'Update' : 'Simpan' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Functions
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.add('opacity-100');
        // Update tahun based on selected kode_anggaran when modal opens
        if (modalId === 'pengeluaranModal') {
            updateTahunFromAnggaran();
        }
    }, 10);
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('opacity-100');
    setTimeout(() => {
        modal.classList.add('hidden');
        // Reset form jika modal ditutup
        window.location.href = window.location.pathname + '?tab=' + '<?= $active_tab ?>';
    }, 300);
}

// Close modal when clicking outside
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('fixed')) {
        hideModal(e.target.id);
    }
});

// Edit Functions
function editAnggaran(kode, tahun) {
    window.location.href = '?tab=anggaran&edit=1&kode=' + encodeURIComponent(kode) + '&tahun=' + encodeURIComponent(tahun);
}

function editPengeluaran(nomor) {
    window.location.href = '?tab=pengeluaran&edit=1&nomor=' + encodeURIComponent(nomor);
}

// Auto-update tahun when kode_anggaran changes in pengeluaran modal
// document.querySelector('select[name="kode_anggaran"]')?.addEventListener('change', function() {
//     const selectedOption = this.options[this.selectedIndex];
//     const tahun = selectedOption.getAttribute('data-tahun');
//     if (tahun) {
//         document.querySelector('select[name="tahun"]').value = tahun;
//     }
// });

// Auto-update tahun when kode_anggaran changes in pengeluaran modal
function updateTahunFromAnggaran() {
    const kodeAnggaranSelect = document.querySelector('select[name="kode_anggaran"]');
    const tahunHidden = document.getElementById('tahun_hidden');
    const tahunDisplay = document.getElementById('tahun_display');
    
    if (kodeAnggaranSelect && tahunHidden && tahunDisplay) {
        kodeAnggaranSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const tahun = selectedOption.getAttribute('data-tahun');
            if (tahun) {
                tahunHidden.value = tahun;
                tahunDisplay.value = tahun;
            }
        });
        
        // Also trigger on page load if kode_anggaran is already selected
        if (kodeAnggaranSelect.value) {
            const selectedOption = kodeAnggaranSelect.options[kodeAnggaranSelect.selectedIndex];
            const tahun = selectedOption.getAttribute('data-tahun');
            if (tahun) {
                tahunHidden.value = tahun;
                tahunDisplay.value = tahun;
            }
        }
    }
}

// Panggil fungsi saat modal dibuka atau halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    updateTahunFromAnggaran();
});

// Show modal if there's edit data
<?php if ($edit_data): ?>
    window.onload = function() {
        <?php if ($active_tab === 'anggaran'): ?>
            showModal('anggaranModal');
        <?php elseif ($active_tab === 'pengeluaran'): ?>
            showModal('pengeluaranModal');
        <?php endif; ?>
    };
<?php endif; ?>
</script>

<?php
require __DIR__ . '/../inc/layout_footer.php';
?>
