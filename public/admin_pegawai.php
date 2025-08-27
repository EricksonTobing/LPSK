<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/../inc/csrf.php';
require_admin();

$title = 'Admin - Pegawai';
require __DIR__ . '/../inc/layout_header.php';
require __DIR__ . '/../inc/layout_nav.php';

// Handle feedback messages
$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;

/* ---------- POST Handler ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? null;
    
    try {
        if ($action === 'create') {
            // Validasi input
            if (empty($_POST['nama_pegawai'])) {
                throw new Exception('Nama pegawai wajib diisi');
            }
            
            // Prepare data untuk insert
            $data = [
                'nama_pegawai' => trim($_POST['nama_pegawai']),
                'jabatan' => !empty($_POST['jabatan']) ? trim($_POST['jabatan']) : null,
                'unit_kerja' => !empty($_POST['unit_kerja']) ? trim($_POST['unit_kerja']) : null,
                'email' => !empty($_POST['email']) ? trim($_POST['email']) : null,
                'no_telp' => !empty($_POST['no_telp']) ? trim($_POST['no_telp']) : null,
                'aktif' => isset($_POST['aktif']) ? 1 : 0
            ];
            
            db()->prepare("INSERT INTO pegawai(nama_pegawai, jabatan, unit_kerja, email, no_telp, aktif) 
                          VALUES(:nama_pegawai, :jabatan, :unit_kerja, :email, :no_telp, :aktif)")
                ->execute($data);
                
            redirect('admin_pegawai.php?success=Pegawai berhasil ditambahkan');
            
        } elseif ($action === 'update') {
            $id = (int) $_POST['id_pegawai'];
            
            // Validasi input
            if (empty($_POST['nama_pegawai'])) {
                throw new Exception('Nama pegawai wajib diisi');
            }
            
            // Prepare data untuk update
            $data = [
                'nama_pegawai' => trim($_POST['nama_pegawai']),
                'jabatan' => !empty($_POST['jabatan']) ? trim($_POST['jabatan']) : null,
                'unit_kerja' => !empty($_POST['unit_kerja']) ? trim($_POST['unit_kerja']) : null,
                'email' => !empty($_POST['email']) ? trim($_POST['email']) : null,
                'no_telp' => !empty($_POST['no_telp']) ? trim($_POST['no_telp']) : null,
                'aktif' => isset($_POST['aktif']) ? 1 : 0,
                'id_pegawai' => $id
            ];
            
            db()->prepare("UPDATE pegawai SET 
                          nama_pegawai = :nama_pegawai, 
                          jabatan = :jabatan, 
                          unit_kerja = :unit_kerja, 
                          email = :email, 
                          no_telp = :no_telp, 
                          aktif = :aktif 
                          WHERE id_pegawai = :id_pegawai")
                ->execute($data);
                
            redirect('admin_pegawai.php?success=Pegawai berhasil diperbarui');
            
        } elseif ($action === 'delete') {
            $id = (int) $_POST['id_pegawai'];
            
            // Cek apakah pegawai digunakan di tabel lain
            $checkPermohonan = db()->prepare("SELECT COUNT(*) FROM permohonan WHERE id_pegawai = ?");
            $checkPermohonan->execute([$id]);
            
            $checkPenelaahan = db()->prepare("SELECT COUNT(*) FROM penelaahan WHERE id_pegawai = ?");
            $checkPenelaahan->execute([$id]);
            
            $checkLayanan = db()->prepare("SELECT COUNT(*) FROM layanan WHERE id_pegawai = ?");
            $checkLayanan->execute([$id]);
            
            if ($checkPermohonan->fetchColumn() > 0 || $checkPenelaahan->fetchColumn() > 0 || $checkLayanan->fetchColumn() > 0) {
                throw new Exception('Tidak dapat menghapus: Pegawai digunakan di data lain');
            }
            
            db()->prepare("DELETE FROM pegawai WHERE id_pegawai=?")->execute([$id]);
            redirect('admin_pegawai.php?success=Pegawai berhasil dihapus');
        }
    } catch (Exception $e) {
        redirect('admin_pegawai.php?error=' . urlencode($e->getMessage()));
    }
}

/* ---------- Pagination & Search ---------- */
list($page, $per, $offset) = paginate_params();
$q = trim((string) ($_GET['q'] ?? ''));
$where = '';
$params = [];
if ($q !== '') {
    $where = "WHERE (nama_pegawai LIKE ? OR jabatan LIKE ? OR unit_kerja LIKE ? OR email LIKE ?)";
    $like = "%$q%";
    $params = [$like, $like, $like, $like];
}
$totalStmt = db()->prepare("SELECT COUNT(*) FROM pegawai $where");
$totalStmt->execute($params);
$total = (int) $totalStmt->fetchColumn();

$sql = "SELECT * FROM pegawai $where ORDER BY nama_pegawai ASC LIMIT $per OFFSET $offset";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>

<!-- Notifikasi -->
<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 relative" role="alert">
    <span class="block sm:inline"><?= e($success) ?></span>
    <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
        <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <title>Close</title>
            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
        </svg>
    </button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 relative" role="alert">
    <span class="block sm:inline"><?= e($error) ?></span>
    <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <title>Close</title>
            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
        </svg>
    </button>
</div>
<?php endif; ?>

<!-- Page Title -->
<div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
    <h1 class="text-2xl md:text-3xl font-bold dark:text-white">
        Manajemen Pegawai
        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(<?= $total ?> pegawai)</span>
    </h1>
    
    <button onclick="openModal('createPegawaiModal')" class="mt-4 md:mt-0 flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Tambah Pegawai
    </button>
</div>

<!-- Toolbar Search -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 mb-6">
    <form class="flex flex-col sm:flex-row gap-3 items-center">
        <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
            </div>
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari nama, jabatan, unit kerja, atau email..."
                   class="w-full pl-10 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm flex items-center justify-center">
            <svg class="w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
            </svg>
            Cari
        </button>
        <?php if ($q !== ''): ?>
        <a href="admin_pegawai.php" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Tabel -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-100 dark:bg-gray-700 sticky top-0 z-10">
            <tr>
                <th class="p-3 text-left font-semibold text-gray-700 dark:text-gray-200">ID</th>
                <th class="p-3 text-left font-semibold text-gray-700 dark:text-gray-200">Nama Pegawai</th>
                <th class="p-3 text-left font-semibold text-gray-700 dark:text-gray-200">Jabatan</th>
                <th class="p-3 text-left font-semibold text-gray-700 dark:text-gray-200">Unit Kerja</th>
                <th class="p-3 text-left font-semibold text-gray-700 dark:text-gray-200">Email</th>
                <th class="p-3 text-left font-semibold text-gray-700 dark:text-gray-200">No. Telp</th>
                <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">Status</th>
                <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $r): ?>
                    <tr class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="p-3 text-gray-800 dark:text-gray-200"><?= e($r['id_pegawai']) ?></td>
                        <td class="p-3 text-gray-800 dark:text-gray-200 font-medium"><?= e($r['nama_pegawai']) ?></td>
                        <td class="p-3 text-gray-800 dark:text-gray-200"><?= e($r['jabatan'] ?? '-') ?></td>
                        <td class="p-3 text-gray-800 dark:text-gray-200"><?= e($r['unit_kerja'] ?? '-') ?></td>
                        <td class="p-3 text-gray-800 dark:text-gray-200"><?= e($r['email'] ?? '-') ?></td>
                        <td class="p-3 text-gray-800 dark:text-gray-200"><?= e($r['no_telp'] ?? '-') ?></td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-1 text-xs rounded-full <?= $r['aktif'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $r['aktif'] ? 'Aktif' : 'Non-Aktif' ?>
                            </span>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center space-x-2">
                                <button onclick="openEditModal(<?= $r['id_pegawai'] ?>, '<?= e($r['nama_pegawai']) ?>', '<?= e($r['jabatan'] ?? '') ?>', '<?= e($r['unit_kerja'] ?? '') ?>', '<?= e($r['email'] ?? '') ?>', '<?= e($r['no_telp'] ?? '') ?>', <?= $r['aktif'] ? 1 : 0 ?>)" 
                                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form method="post" onsubmit="return confirm('Hapus pegawai <?= e($r['nama_pegawai']) ?>?')" class="inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_pegawai" value="<?= e($r['id_pegawai']) ?>">
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="p-4 text-center text-gray-500 dark:text-gray-400">
                        <?= $q !== '' ? 'Tidak ada hasil pencarian' : 'Belum ada data pegawai' ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($total > 0): ?>
<div class="flex flex-col sm:flex-row justify-between items-center mt-6 text-sm text-gray-600 dark:text-gray-400">
    <div>Menampilkan <strong><?= count($rows) ?></strong> dari <strong><?= $total ?></strong> pegawai</div>
    <div class="flex gap-1 mt-2 sm:mt-0">
        <?php
        $params = $_GET;
        $pmax = (int) ceil($total / $per);
        
        // Previous button
        if ($page > 1) {
            $params['page'] = $page - 1;
            echo '<a class="px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700" href="?' . build_query($params) . '">&laquo;</a>';
        }
        
        // Page numbers
        $start = max(1, $page - 2);
        $end = min($pmax, $start + 4);
        
        if ($end - $start < 4) {
            $start = max(1, $end - 4);
        }
        
        for ($i = $start; $i <= $end; $i++):
            $params['page'] = $i;
            $active = $i === $page ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700';
        ?>
            <a class="px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 <?= $active ?>" href="?<?= build_query($params) ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        
        <?php
        if ($page < $pmax) {
            $params['page'] = $page + 1;
            echo '<a class="px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700" href="?' . build_query($params) . '">&raquo;</a>';
        }
        ?>
    </div>
</div>
<?php endif; ?>

<!-- Modal Create Pegawai -->
<div id="createPegawaiModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tambah Pegawai Baru</h3>
                <button onclick="closeModal('createPegawaiModal')" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form method="post" class="mt-4 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                
                <div>
                    <label for="create_nama_pegawai" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Pegawai *</label>
                    <input type="text" id="create_nama_pegawai" name="nama_pegawai" required 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="create_jabatan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jabatan</label>
                    <input type="text" id="create_jabatan" name="jabatan" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="create_unit_kerja" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit Kerja</label>
                    <input type="text" id="create_unit_kerja" name="unit_kerja" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="create_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" id="create_email" name="email" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="create_no_telp" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. Telepon</label>
                    <input type="text" id="create_no_telp" name="no_telp" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="create_aktif" name="aktif" value="1" checked
                           class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="create_aktif" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Aktif</label>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal('createPegawaiModal')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pegawai -->
<div id="editPegawaiModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Pegawai</h3>
                <button onclick="closeModal('editPegawaiModal')" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form method="post" class="mt-4 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_id_pegawai" name="id_pegawai" value="">
                
                <div>
                    <label for="edit_nama_pegawai" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Pegawai *</label>
                    <input type="text" id="edit_nama_pegawai" name="nama_pegawai" required 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="edit_jabatan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jabatan</label>
                    <input type="text" id="edit_jabatan" name="jabatan" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="edit_unit_kerja" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit Kerja</label>
                    <input type="text" id="edit_unit_kerja" name="unit_kerja" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="edit_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" id="edit_email" name="email" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="edit_no_telp" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. Telepon</label>
                    <input type="text" id="edit_no_telp" name="no_telp" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="edit_aktif" name="aktif" value="1"
                           class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="edit_aktif" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Aktif</label>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal('editPegawaiModal')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fungsi untuk membuka modal
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

// Fungsi untuk menutup modal
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Fungsi untuk membuka modal edit dengan data yang sudah diisi
function openEditModal(id, nama_pegawai, jabatan, unit_kerja, email, no_telp, aktif) {
    document.getElementById('edit_id_pegawai').value = id;
    document.getElementById('edit_nama_pegawai').value = nama_pegawai;
    document.getElementById('edit_jabatan').value = jabatan;
    document.getElementById('edit_unit_kerja').value = unit_kerja;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_no_telp').value = no_telp;
    document.getElementById('edit_aktif').checked = aktif === 1;
    
    openModal('editPegawaiModal');
}

// Tutup modal jika mengklik di luar area modal
window.onclick = function(event) {
    if (event.target.classList.contains('fixed')) {
        document.querySelectorAll('.fixed').forEach(modal => {
            modal.classList.add('hidden');
        });
    }
}
</script>

<?php require __DIR__ . '/../inc/layout_footer.php'; ?>