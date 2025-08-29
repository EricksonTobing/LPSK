<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/../inc/csrf.php';
require_admin();

// Handle POST requests first
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
                
            header('Location: admin_pegawai.php?success=Pegawai berhasil ditambahkan');
            exit();
            
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
                
            header('Location: admin_pegawai.php?success=Pegawai berhasil diperbarui');
            exit();
            
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
            header('Location: admin_pegawai.php?success=Pegawai berhasil dihapus');
            exit();
        }
    } catch (Exception $e) {
        header('Location: admin_pegawai.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}

// Set header setelah semua operasi POST selesai
$title = 'Admin - Pegawai';
require __DIR__ . '/../inc/layout_header.php';
require __DIR__ . '/../inc/layout_nav.php';

// Handle feedback messages
$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;

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

<style>
:root {
  --primary-red: #C6100D;
  --primary-dark-blue: #241E4E;
  --secondary-blue: #3430A4;
  --light-bg: #F8FAFC;
  --text-dark: #1E293B;
  --text-light: #64748B;
  --border-color: #E2E8F0;
}

.bg-primary-red { background-color: var(--primary-red); }
.bg-primary-dark-blue { background-color: var(--primary-dark-blue); }
.bg-secondary-blue { background-color: var(--secondary-blue); }
.text-primary-red { color: var(--primary-red); }
.text-primary-dark-blue { color: var(--primary-dark-blue); }
.border-primary-red { border-color: var(--primary-red); }
.border-primary-dark-blue { border-color: var(--primary-dark-blue); }

.hover\:bg-primary-red:hover { background-color: var(--primary-red); }
.hover\:bg-primary-dark-blue:hover { background-color: var(--primary-dark-blue); }

@media (max-width: 640px) {
  .table-responsive {
    display: block;
    width: 100%;
    overflow-x: auto;
  }
  
  .modal-container {
    margin: 1rem;
    width: auto;
  }
}
</style>

<div class="container mx-auto px-4 py-6">
    <!-- Notifikasi -->
    <?php if ($success): ?>
    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700"><?= e($success) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700"><?= e($error) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Page Title -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-primary-dark-blue">
            Manajemen Pegawai
            <span class="text-sm font-normal text-gray-500">(<?= $total ?> pegawai)</span>
        </h1>
        
        <button onclick="openModal('createPegawaiModal')" class="mt-4 md:mt-0 flex items-center justify-center px-4 py-2 bg-primary-dark-blue hover:bg-secondary-blue text-white rounded-lg transition">
            <i class="fas fa-plus mr-2"></i>
            Tambah Pegawai
        </button>
    </div>

    <!-- Toolbar Search -->
    <div class="bg-white rounded-lg shadow p-4 mb-6 border border-gray-200">
        <form class="flex flex-col sm:flex-row gap-3 items-center">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari nama, jabatan, unit kerja, atau email..."
                       class="w-full pl-10 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-dark-blue focus:border-primary-dark-blue">
            </div>
            <button type="submit" class="w-full sm:w-auto bg-primary-dark-blue hover:bg-secondary-blue text-white px-4 py-2 rounded-lg text-sm flex items-center justify-center">
                <i class="fas fa-search mr-1"></i>
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
    <div class="bg-white rounded-lg shadow overflow-x-auto border border-gray-200">
        <div class="table-responsive">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-primary-dark-blue">
                    <tr>
                        <th class="p-3 text-left font-semibold">ID</th>
                        <th class="p-3 text-left font-semibold">Nama Pegawai</th>
                        <th class="p-3 text-left font-semibold">Jabatan</th>
                        <th class="p-3 text-left font-semibold">Unit Kerja</th>
                        <th class="p-3 text-left font-semibold">Email</th>
                        <th class="p-3 text-left font-semibold">No. Telp</th>
                        <th class="p-3 text-center font-semibold">Status</th>
                        <th class="p-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rows) > 0): ?>
                        <?php foreach ($rows as $r): ?>
                            <tr class="border-t border-gray-200 hover:bg-gray-50 transition">
                                <td class="p-3"><?= e($r['id_pegawai']) ?></td>
                                <td class="p-3 font-medium"><?= e($r['nama_pegawai']) ?></td>
                                <td class="p-3"><?= e($r['jabatan'] ?? '-') ?></td>
                                <td class="p-3"><?= e($r['unit_kerja'] ?? '-') ?></td>
                                <td class="p-3"><?= e($r['email'] ?? '-') ?></td>
                                <td class="p-3"><?= e($r['no_telp'] ?? '-') ?></td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-1 text-xs rounded-full <?= $r['aktif'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $r['aktif'] ? 'Aktif' : 'Non-Aktif' ?>
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <button onclick="openEditModal(<?= $r['id_pegawai'] ?>, '<?= e($r['nama_pegawai']) ?>', '<?= e($r['jabatan'] ?? '') ?>', '<?= e($r['unit_kerja'] ?? '') ?>', '<?= e($r['email'] ?? '') ?>', '<?= e($r['no_telp'] ?? '') ?>', <?= $r['aktif'] ? 1 : 0 ?>)" 
                                                class="text-primary-dark-blue hover:text-secondary-blue">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="post" onsubmit="return confirm('Hapus pegawai <?= e($r['nama_pegawai']) ?>?')" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_pegawai" value="<?= e($r['id_pegawai']) ?>">
                                            <button type="submit" class="text-primary-red hover:text-red-700">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="p-4 text-center text-gray-500">
                                <?= $q !== '' ? 'Tidak ada hasil pencarian' : 'Belum ada data pegawai' ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total > 0): ?>
    <div class="flex flex-col sm:flex-row justify-between items-center mt-6 text-sm text-gray-600">
        <div>Menampilkan <strong><?= count($rows) ?></strong> dari <strong><?= $total ?></strong> pegawai</div>
        <div class="flex flex-wrap gap-1 mt-2 sm:mt-0">
            <?php
            $params = $_GET;
            $pmax = (int) ceil($total / $per);
            
            // Previous button
            if ($page > 1) {
                $params['page'] = $page - 1;
                echo '<a class="px-3 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-100" href="?' . build_query($params) . '">&laquo;</a>';
            }
            
            // Page numbers
            $start = max(1, $page - 2);
            $end = min($pmax, $start + 4);
            
            if ($end - $start < 4) {
                $start = max(1, $end - 4);
            }
            
            for ($i = $start; $i <= $end; $i++):
                $params['page'] = $i;
                $active = $i === $page ? 'bg-primary-dark-blue text-white' : 'bg-white hover:bg-gray-100';
            ?>
                <a class="px-3 py-1.5 rounded-md border border-gray-300 <?= $active ?>" href="?<?= build_query($params) ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php
            if ($page < $pmax) {
                $params['page'] = $page + 1;
                echo '<a class="px-3 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-100" href="?' . build_query($params) . '">&raquo;</a>';
            }
            ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal Create Pegawai -->
    <div id="createPegawaiModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-4 border w-full max-w-md modal-container bg-white rounded-lg shadow-lg">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-semibold text-primary-dark-blue">Tambah Pegawai Baru</h3>
                <button onclick="closeModal('createPegawaiModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="post" class="py-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pegawai</label>
                        <input type="text" name="nama_pegawai" required 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-dark-blue focus:border-primary-dark-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                        <input type="text" name="jabatan" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-dark-blue focus:border-primary-dark-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
                        <input type="text" name="unit_kerja" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-dark-blue focus:border-primary-dark-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-dark-blue focus:border-primary-dark-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                        <input type="tel" name="no_telp" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-dark-blue focus:border-primary-dark-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="aktif" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-dark-blue focus:border-primary-dark-blue">
                            <option value="1">Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end gap-2 pt-4 mt-4 border-t">
                    <button type="button" onclick="closeModal('createPegawaiModal')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary-dark-blue hover:bg-secondary-blue text-white rounded-lg">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Pegawai -->
    <div id="editPegawaiModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-4 border w-full max-w-md modal-container bg-white rounded-lg shadow-lg">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-semibold text-primary-dark-blue">Edit Data Pegawai</h3>
                <button onclick="closeModal('editPegawaiModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="post" class="py-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_id_pegawai" name="id_pegawai">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pegawai</label>
                        <input type="text" id="edit_nama_pegawai" name="nama_pegawai" required 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-dark-blue focus:border-primary-dark-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                        <input type="text" id="edit_jabatan" name="jabatan" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-dark-blue focus:border-primary-dark-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
                        <input type="text" id="edit_unit_kerja" name="unit_kerja" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-dark-blue focus:border-primary-dark-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="edit_email" name="email" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-dark-blue focus:border-primary-dark-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                        <input type="tel" id="edit_no_telp" name="no_telp" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-dark-blue focus:border-primary-dark-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="edit_aktif" name="aktif" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-dark-blue focus:border-primary-dark-blue">
                            <option value="1">Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end gap-2 pt-4 mt-4 border-t">
                    <button type="button" onclick="closeModal('editPegawaiModal')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary-dark-blue hover:bg-secondary-blue text-white rounded-lg">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function openEditModal(id, nama, jabatan, unitKerja, email, noTelp, aktif) {
    // Pastikan nilai tidak undefined
    document.getElementById('edit_id_pegawai').value = id || '';
    document.getElementById('edit_nama_pegawai').value = nama || '';
    document.getElementById('edit_jabatan').value = jabatan || '';
    document.getElementById('edit_unit_kerja').value = unitKerja || '';
    document.getElementById('edit_email').value = email || '';
    document.getElementById('edit_no_telp').value = noTelp || '';
    document.getElementById('edit_aktif').value = aktif !== undefined ? aktif : 1;
    
    openModal('editPegawaiModal');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('fixed')) {
        document.querySelectorAll('.fixed').forEach(modal => {
            modal.classList.add('hidden');
        });
    }
}

// Prevent event propagation in modal content
document.querySelectorAll('.modal-container').forEach(container => {
    container.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});
</script>

<?php require __DIR__ . '/../inc/layout_footer.php'; ?>