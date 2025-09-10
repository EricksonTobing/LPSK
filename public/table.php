<?php
// =======================================================
// Konfigurasi Error Reporting untuk Pengembangan
// =======================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// =======================================================
// Import File Konfigurasi dan Library yang Dibutuhkan
// =======================================================
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/../inc/csrf.php';

// =======================================================
// Autentikasi: Pastikan User Sudah Login
// =======================================================
require_login();

// =======================================================
// Ambil Metadata Tabel dari Konfigurasi
// =======================================================
$tables = require __DIR__ . '/../inc/table_meta.php';

// =======================================================
// Penentuan Tabel Aktif Berdasarkan Parameter GET
// =======================================================
$t = $_GET['t'] ?? 'permohonan';
if (!isset($tables[$t]) || $t === 'users') {
    http_response_code(404);
    exit('Not Found');
}

// =======================================================
// Ambil Metadata Tabel Aktif
// =======================================================
$meta       = $tables[$t];
$pk         = $meta['pk'];
$colLabels  = $meta['columns'];
$title      = $meta['label'] ?? ucfirst($t);
$searchable = $meta['searchable'] ?? [];
$filters    = $meta['filters'] ?? [];
$joins      = $meta['joins'] ?? [];
$title      = ucfirst($t);

// =======================================================
// Ambil Role User Saat Ini
// =======================================================
$role = auth_user()['role'] ?? 'user';

// =======================================================
// Handler untuk Request POST (Create, Update, Delete)
// =======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if ($role !== 'admin') {
        http_response_code(403);
        exit('Forbidden');
    }
    $action = $_POST['action'] ?? '';

    try {
        // -----------------------------------------------
        // Proses Create atau Update Data
        // -----------------------------------------------
        if ($action === 'create' || $action === 'update') {
            $data = [];
            foreach (array_keys($colLabels) as $c) {
                // Lewati kolom hasil join (read-only)
                $isJoined = false;
                foreach ($joins as $joinTable => $joinInfo) {
                    if (in_array($c, $joinInfo[2])) {
                        $isJoined = true;
                        break;
                    }
                }
                if ($isJoined) continue;

                // Ambil nilai dari POST, gunakan nama kolom asli
                $v = $_POST[$c] ?? null;
                error_log("Field $c: " . var_export($v, true));
                if (is_string($v)) $v = trim($v);
                $data[$c] = $v === '' ? null : $v;
            }

            error_log("Data to save: " . print_r($data, true));

            // Validasi: Primary Key harus diisi saat create
            if ($action === 'create' && empty($data[$pk])) {
                $_SESSION['error'] = "Primary key ($pk) is required";
                redirect("table.php?t=$t");
            }

            // -------------------------------------------
            // Query Insert Data Baru
            // -------------------------------------------
            if ($action === 'create') {
                $sql = "INSERT INTO $t (" . implode(',', array_keys($data)) . ") VALUES (" . implode(',', array_fill(0, count($data), '?')) . ")";
                error_log("SQL: $sql");
                error_log("Params: " . print_r(array_values($data), true));
                $stmt = db()->prepare($sql);
                $stmt->execute(array_values($data));
                $_SESSION['success'] = "Data created successfully";
            } else {
                // ---------------------------------------
                // Query Update Data Berdasarkan Primary Key
                // ---------------------------------------
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
        }
        // -----------------------------------------------
        // Proses Delete Data
        // -----------------------------------------------
        elseif ($action === 'delete') {
            $id = $_POST[$pk] ?? null;
            if ($id) {
                // Cek foreign key sebelum hapus pada tabel permohonan
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
        // Penanganan error database yang user-friendly
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

// =======================================================
// Fungsi: Menentukan Tipe Input Form Berdasarkan Kolom
// =======================================================
function get_input_type($column, $value = '')
{
    // Kolom khusus untuk URL
    if ($column === 'link_berkas_permohonan') {
        return ['type' => 'url'];
    }

    // Daftar enum kolom beserta opsi-nya
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
            'TPL' => 'TPL',
            'TPPU' => 'TPPU'
        ],
        'nama_ta_layanan'       => ['AM' => 'AM', 'AJC' => 'AJC', 'RW' => 'RW', 'TP' => 'TP', 'SMW' => 'SMW'],
        'status'                => ['BERJALAN' => 'Berjalan', 'DIHENTIKAN' => 'Dihentikan', 'PERPANJANGAN' => 'Perpanjangan'],
        'status_spk'            => ['Sudah TTD' => 'Sudah TTD', 'Belum TTD' => 'Belum TTD'],
        'masa_layanan'          => ['3 BULAN' => '3 Bulan', '6 BULAN' => '6 Bulan'],
        'tambahan_masa_layanan' => ['3 BULAN' => '3 Bulan', '6 BULAN' => '6 Bulan'],
        'role'                  => ['admin' => 'Admin', 'user' => 'User'],
        'aktif'                 => [1 => 'Aktif', 0 => 'Tidak Aktif']
    ];

    // Jika kolom enum, kembalikan tipe select beserta opsi
    if (isset($enum_columns[$column])) {
        return ['type' => 'select', 'options' => $enum_columns[$column]];
    }

    // Kolom bertipe tanggal
    if (
        strpos($column, 'tanggal') !== false ||
        strpos($column, 'tgl') !== false ||
        $column === 'tahun'
    ) {
        return ['type' => 'date'];
    }

    // Kolom bertipe numerik
    $numeric_columns = ['total_anggaran', 'jumlah'];
    if (in_array($column, $numeric_columns)) {
        return ['type' => 'number', 'step' => '0.01'];
    }

    // Kolom foreign key: id_pegawai
    if ($column === 'id_pegawai') {
        try {
            $stmt = db()->query("SELECT id_pegawai, nama_pegawai FROM pegawai ORDER BY nama_pegawai");
            $pegawai = $stmt->fetchAll();
            $options = [];
            foreach ($pegawai as $p) {
                $options[$p['id_pegawai']] = $p['nama_pegawai'];
            }
            return ['type' => 'select', 'options' => $options];
        } catch (Exception $e) {
            return ['type' => 'text'];
        }
    }

    // Kolom foreign key: kode_mak
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

    // Kolom foreign key: kode_anggaran
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

    // Kolom tahun: dropdown tahun
    if ($column === 'tahun') {
        $currentYear = date('Y');
        $years = range($currentYear - 10, $currentYear + 5);
        return ['type' => 'select', 'options' => array_combine($years, $years)];
    }

    // Default: text
    return ['type' => 'text'];
}

// =======================================================
// Fungsi: Bantuan/Tooltip untuk Setiap Kolom
// =======================================================
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
        'tgl_berakhir_layanan' => 'Tanggal berakhirnya layanan (otomatis terhitung tidak perlu diisi)',
        'wilayah_hukum' => 'Wilayah hukum yang menangani',
        'nama_ta_layanan' => 'Nama Technical Assistant layanan',
        'status' => 'Status layanan saat ini'
    ];
    return $help_messages[$column] ?? 'Isikan data yang sesuai';
}

// =======================================================
// Fungsi: Menentukan Field yang Wajib Diisi
// =======================================================
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



// =======================================================
// Konfigurasi Pilihan Baris per Halaman (Pagination)
// =======================================================
$per_page_options = [10, 20, 50, 100];
$per_page = isset($_GET['per_page']) && in_array((int)$_GET['per_page'], $per_page_options)
    ? (int)$_GET['per_page']
    : 10;

error_log("Final per page: " . $per_page);

// =======================================================
// Logging untuk Debugging Pagination dan Parameter GET
// =======================================================
error_log("Per page value: " . $per_page);
error_log("GET parameters: " . print_r($_GET, true));



// =======================================================
// Ambil Parameter Sorting dari GET
// =======================================================
$sort_column = $_GET['sort'] ?? $pk;
$sort_order = $_GET['order'] ?? 'DESC';

// Validasi kolom sorting (termasuk kolom hasil join)
$valid_columns = array_keys($colLabels);
foreach ($joins as $joinInfo) {
    $valid_columns = array_merge($valid_columns, $joinInfo[2]);
}
if (!in_array($sort_column, $valid_columns)) {
    $sort_column = $pk;
}

// Validasi urutan sorting
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

// Tentukan tabel untuk ORDER BY (jika kolom join)
$orderByTable = $t;
$orderByColumn = $sort_column;
foreach ($joins as $joinTable => $joinInfo) {
    if (in_array($sort_column, $joinInfo[2])) {
        $orderByTable = $joinTable;
        break;
    }
}
$orderBy = "$orderByTable.$sort_column $sort_order";

// =======================================================
// Proses Pagination dan Query Data
// =======================================================
list($page, $per_page, $offset) = paginate_params($per_page);
error_log("Page: $page, Per Page: $per_page, Offset: $offset");
$q      = trim((string)($_GET['q'] ?? ''));
$where  = [];
$params = [];

// =======================================================
// Build SELECT dan JOIN Clause
// =======================================================
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

// =======================================================
// Proses Pencarian (Search)
// =======================================================
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

// =======================================================
// Proses Filter Kolom
// =======================================================
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

// =======================================================
// Hitung Total Data untuk Pagination
// =======================================================
$totalStmt = db()->prepare("SELECT COUNT(*) FROM $t $joinSql $whereSql");
$totalStmt->execute(array_merge($joinParams, $params));
$total = (int)$totalStmt->fetchColumn();

// =======================================================
// Query Data Utama untuk Ditampilkan
// =======================================================
$sql = "SELECT $selectSql FROM $t $joinSql $whereSql ORDER BY $orderBy LIMIT $per_page OFFSET $offset";
$stmt = db()->prepare($sql);
$stmt->execute(array_merge($joinParams, $params));
$rows = $stmt->fetchAll();

// =======================================================
// Proses Data Join untuk Ditampilkan di Tabel
// =======================================================
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

// =======================================================
// Tampilkan Header dan Navigasi Layout
// =======================================================
require __DIR__ . '/../inc/layout_header.php';
require __DIR__ . '/../inc/layout_nav.php';
?>

<!--
    ============================
    Bagian Tampilan (HTML)
    ============================
    - Header Section
    - Alert Messages
    - Info Card
    - Main Card (Filter, Table, Pagination)
    - Modal (Create, Edit, Delete)
-->

<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="mb-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= e(ucfirst($t)) ?></h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Kelola data <?= e(strtolower($title)) ?> dengan mudah</p>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
            <!-- Tombol Export Data -->
            <a class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg flex items-center text-sm transition-all shadow-md hover:shadow-lg"
                href="export_spout.php?t=<?= e($t) ?>">
                <i class="fas fa-rocket mr-2"></i> Export (Spout)
            </a>
        </div>
    </div>

    <!-- Alert Messages (Error & Success) -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm dark:bg-red-900/20 dark:border-red-400">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 dark:text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 dark:text-red-300"><?= e($_SESSION['error']) ?></p>
                </div>
                <button type="button" class="ml-auto text-red-500 hover:text-red-700" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['error']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg shadow-sm dark:bg-green-900/20 dark:border-green-400">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 dark:text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700 dark:text-green-300"><?= e($_SESSION['success']) ?></p>
                </div>
                <button type="button" class="ml-auto text-green-500 hover:text-green-700" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success']) ?>
        </div>
    <?php endif; ?>

    <!-- Info Card: Panduan Penggunaan -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg shadow-sm dark:bg-blue-900/20 dark:border-blue-400">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500 dark:text-blue-400"></i>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Panduan Penggunaan</p>
                <div class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Gunakan kolom pencarian untuk mencari data tertentu</li>
                        <li>Klik pada header kolom untuk mengurutkan data</li>
                        <li>Gunakan filter untuk menyaring data yang ditampilkan</li>
                        <?php if ($role === 'admin'): ?>
                            <li>Klik tombol <i class="fas fa-plus text-xs"></i> untuk menambah data baru</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <button type="button" class="ml-auto text-blue-500 hover:text-blue-700" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Main Card: Filter, Table, dan Pagination -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden mb-8 border border-gray-200 dark:border-gray-700">
        <!-- Filter Section -->
        <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
            <form class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <input type="hidden" name="t" value="<?= e($t) ?>">
                <input type="hidden" name="sort" value="<?= e($sort_column) ?>">
                <input type="hidden" name="order" value="<?= e($sort_order) ?>">
                
                <!-- Input Pencarian -->
                <div class="col-span-full md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pencarian Cepat</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Ketik untuk mencari..."
                            class="pl-10 w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary-blue focus:border-primary-blue dark:bg-gray-700 dark:text-white transition-colors">
                        <?php if ($q): ?>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <a href="?t=<?= e($t) ?>&sort=<?= e($sort_column) ?>&order=<?= e($sort_order) ?>"
                                   class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dynamic Filters -->
                <?php foreach ($filters as $f): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= e($colLabels[$f] ?? $f) ?></label>
                        <?php
                        $input_type = get_input_type($f);
                        $filterName = str_replace('/', '_', $f);
                        ?>
                        <?php if ($input_type['type'] === 'select'): ?>
                            <select name="<?= e($filterName) ?>" 
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary-blue focus:border-primary-blue dark:bg-gray-700 dark:text-white transition-colors">
                                <option value="">- Semua -</option>
                                <?php foreach ($input_type['options'] as $key => $option): ?>
                                    <option value="<?= e($key) ?>" <?= (($_GET[$filterName] ?? '') === (string)$key) ? 'selected' : '' ?>>
                                        <?= e($option) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="<?= e($input_type['type']) ?>" name="<?= e($filterName) ?>" value="<?= e($_GET[$filterName] ?? '') ?>"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary-blue focus:border-primary-blue dark:bg-gray-700 dark:text-white transition-colors">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <!-- Tombol Aksi Filter dan Reset -->
                <div class="col-span-full flex flex-col sm:flex-row gap-2 pt-2">
                    <button type="submit" class="px-4 py-2 bg-primary-blue hover:bg-primary-blue/90 text-white rounded-lg transition-all flex items-center justify-center shadow-md hover:shadow-lg">
                        <i class="fas fa-filter mr-2"></i> Terapkan Filter
                    </button>
                    <a href="table.php?t=<?= e($t) ?>&sort=<?= e($pk) ?>&order=DESC" 
                       class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white rounded-lg transition-all flex items-center justify-center">
                        <i class="fas fa-sync-alt mr-2"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Controls: Info Total Data, Per Page, dan Tombol Tambah -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 border-b border-gray-100 dark:border-gray-700 gap-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Total: <span class="font-semibold text-primary-blue dark:text-primary-red"><?= $total ?></span> data
                <?php if ($q): ?>
                    <span class="ml-2">(Hasil pencarian: "<?= e($q) ?>")</span>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-3">
                <label class="text-sm text-gray-600 dark:text-gray-400">Baris per halaman:</label>
                <select id="per_page_select" 
                    class="border border-gray-300 dark:border-gray-600 rounded-lg py-1 px-2 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary-blue transition-colors">
                    <?php foreach ($per_page_options as $option): ?>
                        <option value="<?= $option ?>" <?= $per_page == $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($role === 'admin'): ?>
                    <button onclick="openCreateModal()" 
                        class="px-4 py-2 bg-gradient-to-r from-primary-blue to-primary-red hover:from-primary-blue/90 hover:to-primary-red/90 text-white rounded-lg flex items-center text-sm transition-all shadow-md hover:shadow-lg ml-2">
                        <i class="fas fa-plus mr-2"></i> Tambah Data
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Table Section: Data Tabel -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-12 sticky left-0 bg-gray-50 dark:bg-gray-700 z-10">
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
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors group"
                                onclick="sortTable('<?= e($col) ?>')">
                                <div class="flex items-center">
                                    <span><?= e($label) ?></span> 
                                    <?php if ($is_current_sort): ?>
                                        <span class="ml-1 text-primary-blue dark:text-primary-red"><?= $sort_icon ?></span>
                                    <?php else: ?>
                                        <span class="ml-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <i class="fas fa-sort text-gray-400"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </th>
                        <?php endforeach; ?>
                        <?php if ($role === 'admin'): ?>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider sticky right-0 bg-gray-50 dark:bg-gray-700 z-10">
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
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400 text-center sticky left-0 bg-white dark:bg-gray-800 z-10">
                                <?= $row_number++ ?>
                            </td>
                            <?php foreach ($colLabels as $col => $label): ?>
                                <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200 max-w-xs truncate group relative" title="<?= e((string)($r[$col] ?? '')) ?>">
                                    <?php if ($col === 'id_pegawai' && !empty($r['pegawai_nama_pegawai'])): ?>
                                        <?= e($r['pegawai_nama_pegawai']) ?>
                                    <?php elseif ($col === 'link_berkas_permohonan' && !empty($r[$col])): ?>
                                        <a href="<?= e($r[$col]) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline flex items-center transition-colors">
                                            <i class="fas fa-external-link-alt mr-1 text-xs"></i> Lihat Berkas
                                        </a>
                                    <?php else: ?>
                                        <?= e(truncate_text((string)($r[$col] ?? ''), 50)) ?>
                                        <?php if (strlen((string)($r[$col] ?? '')) > 50): ?>
                                            <div class="absolute inset-0 bg-gradient-to-l from-transparent to-white dark:to-gray-800 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <?php if ($role === 'admin'): ?>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium sticky right-0 bg-white dark:bg-gray-800 z-10">
                                    <div class="flex justify-end space-x-2">
                                        <button onclick="openEditModal('<?= e($r[$pk]) ?>')" 
                                            class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400 transition-colors p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="confirmDelete('<?= e($r[$pk]) ?>')" 
                                            class="text-red-600 hover:text-red-900 dark:hover:text-red-400 transition-colors p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                                            title="Hapus">
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
                                <i class="fas fa-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada data yang ditemukan</p>
                                <?php if ($q || !empty($filters)): ?>
                                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Coba ubah kata kunci pencarian atau filter</p>
                                    <a href="table.php?t=<?= e($t) ?>" class="text-primary-blue dark:text-primary-red hover:underline mt-2 text-sm">
                                        Tampilkan semua data
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Section -->
        <?php if ($total > 0): ?>
            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                <div class="flex flex-col sm:flex-row items-center justify-between space-y-3 sm:space-y-0">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Menampilkan <span class="font-medium"><?= $offset + 1 ?></span> - 
                        <span class="font-medium"><?= min($offset + $per_page, $total) ?></span> dari 
                        <span class="font-medium"><?= $total ?></span> data
                    </div>
                    <div class="flex items-center space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?<?= build_query(['t' => $t, 'page' => $page - 1, 'per_page' => $per_page, 'q' => $q, 'sort' => $sort_column, 'order' => $sort_order] + array_filter($_GET, fn($k) => in_array($k, array_map(fn($f) => str_replace('/', '_', $f), $filters)), ARRAY_FILTER_USE_KEY)) ?>" 
                               class="px-3 py-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                                <i class="fas fa-chevron-left mr-1"></i> Sebelumnya
                            </a>
                        <?php endif; ?>
                        <?php
                        // Hitung range halaman untuk navigasi pagination
                        $start_page = max(1, $page - 2);
                        $end_page = min($start_page + 4, ceil($total / $per_page));
                        if ($end_page - $start_page < 4) {
                            $start_page = max(1, $end_page - 4);
                        }
                        ?>
                        <?php for ($p = $start_page; $p <= $end_page; $p++): ?>
                            <a href="?<?= build_query([
                                't' => $t, 
                                'page' => $p, 
                                'per_page' => $per_page, 
                                'q' => $q, 
                                'sort' => $sort_column, 
                                'order' => $sort_order
                            ] + array_filter($_GET, function($k) use ($filters) {
                                $filterNames = array_map(function($f) {
                                    return str_replace('/', '_', $f);
                                }, $filters);
                                return in_array($k, $filterNames);
                            }, ARRAY_FILTER_USE_KEY)) ?>" 
                               class="px-3 py-1 rounded-lg border <?= $p == $page ? 'border-primary-blue bg-primary-blue text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' ?>">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>
                        <?php if ($page < ceil($total / $per_page)): ?>
                            <a href="?<?= build_query(['t' => $t, 'page' => $page + 1, 'per_page' => $per_page, 'q' => $q, 'sort' => $sort_column, 'order' => $sort_order] + array_filter($_GET, fn($k) => in_array($k, array_map(fn($f) => str_replace('/', '_', $f), $filters)), ARRAY_FILTER_USE_KEY)) ?>" 
                               class="px-3 py-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                                Selanjutnya <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($role === 'admin'): ?>
    <!--
        ============================
        Modal Section (Create, Edit, Delete)
        ============================
    -->

    <!-- Modal Tambah Data -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden transition-opacity">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto transform transition-transform">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Tambah Data <?= e($title) ?></h3>
            </div>
            <form id="createForm" method="post" class="p-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($colLabels as $col => $label): 
                        // Lewati kolom hasil join pada form create
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

    <!-- Modal Edit Data -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden transition-opacity">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto transform transition-transform">
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

    <!-- Modal Konfirmasi Hapus Data -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden transition-opacity">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md transform transition-transform">
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

<!--
    ============================
    Bagian Script (Javascript)
    ============================
    - Handler per_page select
    - Modal handler
    - Sort handler
    - Notification handler
    - Keyboard shortcut
    - Responsive table
-->

<script>
// Handler untuk perubahan baris per halaman (per_page)
document.addEventListener('DOMContentLoaded', function() {
    const perPageSelect = document.getElementById('per_page_select');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            // Ambil semua parameter dari URL saat ini
            const urlParams = new URLSearchParams(window.location.search);
            // Update parameter per_page dan reset ke halaman 1
            urlParams.set('per_page', this.value);
            urlParams.set('page', '1');
            // Pastikan parameter 't' tidak hilang
            if (!urlParams.has('t')) {
                urlParams.set('t', '<?= e($t) ?>');
            }
            // Redirect ke URL baru
            window.location.href = `table.php?${urlParams.toString()}`;
        });
    }
});

// Fungsi untuk membuka modal tambah data
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('createModal').classList.add('opacity-100');
        document.querySelector('#createModal > div').classList.add('scale-100');
    }, 10);
}

// Fungsi untuk menutup modal tambah data
function closeCreateModal() {
    document.getElementById('createModal').classList.remove('opacity-100');
    document.querySelector('#createModal > div').classList.remove('scale-100');
    setTimeout(() => {
        document.getElementById('createModal').classList.add('hidden');
    }, 200);
}

// Fungsi untuk membuka modal edit data
function openEditModal(id) {
    // Ambil data berdasarkan ID via AJAX
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
                setTimeout(() => {
                    document.getElementById('editModal').classList.add('opacity-100');
                    document.querySelector('#editModal > div').classList.add('scale-100');
                }, 10);
            } else {
                showNotification('Gagal memuat data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat memuat data', 'error');
        });
}

// Fungsi untuk menutup modal edit data
function closeEditModal() {
    document.getElementById('editModal').classList.remove('opacity-100');
    document.querySelector('#editModal > div').classList.remove('scale-100');
    setTimeout(() => {
        document.getElementById('editModal').classList.add('hidden');
    }, 200);
}

// Fungsi untuk membuka modal konfirmasi hapus
function confirmDelete(id) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteModal').classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('deleteModal').classList.add('opacity-100');
        document.querySelector('#deleteModal > div').classList.add('scale-100');
    }, 10);
}

// Fungsi untuk menutup modal konfirmasi hapus
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('opacity-100');
    document.querySelector('#deleteModal > div').classList.remove('scale-100');
    setTimeout(() => {
        document.getElementById('deleteModal').classList.add('hidden');
    }, 200);
}

// Fungsi untuk sorting tabel berdasarkan kolom
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

// Fungsi untuk menampilkan notifikasi (info, error, success)
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transform transition-transform duration-300 ${
        type === 'error' ? 'bg-red-500 text-white' : 
        type === 'success' ? 'bg-green-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : type === 'success' ? 'fa-check-circle' : 'fa-info-circle'} mr-2"></i>
            <span>${message}</span>
            <button class="ml-4" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    document.body.appendChild(notification);
    // Animasi masuk
    setTimeout(() => {
        notification.classList.add('translate-x-0', 'opacity-100');
    }, 10);
    // Auto remove setelah 5 detik
    setTimeout(() => {
        notification.classList.remove('translate-x-0', 'opacity-100');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.parentElement.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Handler: Tutup modal jika klik di luar area modal
document.querySelectorAll('.fixed').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            if (this.id === 'createModal') closeCreateModal();
            if (this.id === 'editModal') closeEditModal();
            if (this.id === 'deleteModal') closeDeleteModal();
        }
    });
});

// Handler: Keyboard shortcut (ESC untuk tutup modal, Ctrl+N untuk tambah data)
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

// Fungsi: Penanganan tabel responsif (opsional, bisa dikembangkan)
function initResponsiveTable() {
    const table = document.querySelector('table');
    if (!table) return;
    // Tambahkan class container responsif
    const container = table.parentElement;
    container.classList.add('responsive-table-container');
    // Tambahkan indikator scroll untuk mobile
    const addScrollIndicators = () => {
        if (window.innerWidth < 768) {
            container.classList.add('has-scroll-indicators');
        } else {
            container.classList.remove('has-scroll-indicators');
        }
    };
    window.addEventListener('resize', addScrollIndicators);
    addScrollIndicators();
}
</script>

<?php
// =======================================================
// Tampilkan Footer Layout
// =======================================================
require __DIR__ . '/../inc/layout_footer.php';
?>