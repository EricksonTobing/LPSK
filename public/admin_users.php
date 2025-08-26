<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/../inc/csrf.php';
require_admin();

$title = 'Admin - Users';
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
            if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['nama_lengkap']) || empty($_POST['email'])) {
                throw new Exception('Semua field wajib diisi');
            }
            
            // Validasi format email
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Format email tidak valid');
            }
            
            // Cek apakah username sudah ada
            $checkStmt = db()->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $checkStmt->execute([trim($_POST['username'])]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception('Username sudah digunakan');
            }
            
            db()->prepare("INSERT INTO users(username,password,nama_lengkap,email,role)
                           VALUES(?,?,?,?,?)")
                ->execute([
                    trim($_POST['username']),
                    password_hash((string) $_POST['password'], PASSWORD_DEFAULT),
                    trim($_POST['nama_lengkap']),
                    trim($_POST['email']),
                    $_POST['role'] === 'admin' ? 'admin' : 'user'
                ]);
                
            redirect('admin_users.php?success=User berhasil ditambahkan');
            
        } elseif ($action === 'update') {
            $id = (int) $_POST['id_user'];
            
            // Validasi input
            if (empty($_POST['username']) || empty($_POST['nama_lengkap']) || empty($_POST['email'])) {
                throw new Exception('Semua field wajib diisi');
            }
            
            // Validasi format email
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Format email tidak valid');
            }
            
            // Cek apakah username sudah ada (kecuali untuk user ini)
            $checkStmt = db()->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id_user != ?");
            $checkStmt->execute([trim($_POST['username']), $id]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception('Username sudah digunakan');
            }
            
            if (!empty($_POST['password'])) {
                $sql = "UPDATE users SET username=?, password=?, nama_lengkap=?, email=?, role=? WHERE id_user=?";
                $params = [
                    trim($_POST['username']),
                    password_hash((string) $_POST['password'], PASSWORD_DEFAULT),
                    trim($_POST['nama_lengkap']),
                    trim($_POST['email']),
                    $_POST['role'] === 'admin' ? 'admin' : 'user',
                    $id
                ];
            } else {
                $sql = "UPDATE users SET username=?, nama_lengkap=?, email=?, role=? WHERE id_user=?";
                $params = [
                    trim($_POST['username']),
                    trim($_POST['nama_lengkap']),
                    trim($_POST['email']),
                    $_POST['role'] === 'admin' ? 'admin' : 'user',
                    $id
                ];
            }
            db()->prepare($sql)->execute($params);
            redirect('admin_users.php?success=User berhasil diperbarui');
            
        } elseif ($action === 'delete') {
            $id = (int) $_POST['id_user'];
            
            // Cegah penghapusan diri sendiri
            if ($id === $_SESSION['user_id']) {
                throw new Exception('Tidak dapat menghapus akun sendiri');
            }
            
            db()->prepare("DELETE FROM users WHERE id_user=?")->execute([$id]);
            redirect('admin_users.php?success=User berhasil dihapus');
        }
    } catch (Exception $e) {
        redirect('admin_users.php?error=' . urlencode($e->getMessage()));
    }
}

/* ---------- Pagination & Search ---------- */
list($page, $per, $offset) = paginate_params();
$q = trim((string) ($_GET['q'] ?? ''));
$where = '';
$params = [];
if ($q !== '') {
    $where = "WHERE username LIKE ? OR nama_lengkap LIKE ? OR email LIKE ? OR role LIKE ?";
    $like = "%$q%";
    $params = [$like, $like, $like, $like];
}
$totalStmt = db()->prepare("SELECT COUNT(*) FROM users $where");
$totalStmt->execute($params);
$total = (int) $totalStmt->fetchColumn();

$sql = "SELECT * FROM users $where ORDER BY id_user DESC LIMIT $per OFFSET $offset";
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
        Manajemen Pengguna
        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(<?= $total ?> akun)</span>
    </h1>
    
    <button onclick="openModal('createUserModal')" class="mt-4 md:mt-0 flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Tambah Pengguna
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
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari username, nama, email, atau role..."
                   class="w-full pl-10 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm flex items-center justify-center">
            <svg class="w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
            </svg>
            Cari
        </button>
        <?php if ($q !== ''): ?>
        <a href="admin_users.php" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
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
                <th class="p-3 text-left font-semibold text-gray-700 dark:text-gray-200">Username</th>
                <th class="p-3 text-left font-semibold text-gray-700 dark:text-gray-200">Nama</th>
                <th class="p-3 text-left font-semibold text-gray-700 dark:text-gray-200">Email</th>
                <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">Role</th>
                <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $r): ?>
                    <tr class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="p-3 text-gray-800 dark:text-gray-200"><?= e($r['id_user']) ?></td>
                        <td class="p-3 text-gray-800 dark:text-gray-200 font-medium"><?= e($r['username']) ?></td>
                        <td class="p-3 text-gray-800 dark:text-gray-200"><?= e($r['nama_lengkap']) ?></td>
                        <td class="p-3 text-gray-800 dark:text-gray-200"><?= e($r['email']) ?></td>
                        <td class="p-3 text-center">
                            <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold
                                <?= $r['role'] === 'admin'
                                    ? 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-200'
                                    : 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200'; ?>">
                                <?= e($r['role']) ?>
                            </span>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center space-x-2">
                                <button onclick="openEditModal(<?= $r['id_user'] ?>, '<?= e($r['username']) ?>', '<?= e($r['nama_lengkap']) ?>', '<?= e($r['email']) ?>', '<?= e($r['role']) ?>')" 
                                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form method="post" onsubmit="return confirm('Hapus user <?= e($r['username']) ?>?')" class="inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_user" value="<?= e($r['id_user']) ?>">
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
                    <td colspan="6" class="p-4 text-center text-gray-500 dark:text-gray-400">
                        <?= $q !== '' ? 'Tidak ada hasil pencarian' : 'Belum ada data pengguna' ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($total > 0): ?>
<div class="flex flex-col sm:flex-row justify-between items-center mt-6 text-sm text-gray-600 dark:text-gray-400">
    <div>Menampilkan <strong><?= count($rows) ?></strong> dari <strong><?= $total ?></strong> akun</div>
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

<!-- Modal Create User -->
<div id="createUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tambah Pengguna Baru</h3>
                <button onclick="closeModal('createUserModal')" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form method="post" class="mt-4 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                
                <div>
                    <label for="create_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                    <input type="text" id="create_username" name="username" required 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="create_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                    <input type="password" id="create_password" name="password" required 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="create_nama_lengkap" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                    <input type="text" id="create_nama_lengkap" name="nama_lengkap" required 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="create_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" id="create_email" name="email" required 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="create_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                    <select id="create_role" name="role" 
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal('createUserModal')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
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

<!-- Modal Edit User -->
<div id="editUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Pengguna</h3>
                <button onclick="closeModal('editUserModal')" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form method="post" class="mt-4 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_id_user" name="id_user" value="">
                
                <div>
                    <label for="edit_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                    <input type="text" id="edit_username" name="username" required 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="edit_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password (kosongkan jika tidak diubah)</label>
                    <input type="password" id="edit_password" name="password" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="edit_nama_lengkap" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                    <input type="text" id="edit_nama_lengkap" name="nama_lengkap" required 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="edit_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" id="edit_email" name="email" required 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <label for="edit_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                    <select id="edit_role" name="role" 
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal('editUserModal')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
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
function openEditModal(id, username, nama_lengkap, email, role) {
    document.getElementById('edit_id_user').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_nama_lengkap').value = nama_lengkap;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    
    openModal('editUserModal');
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
