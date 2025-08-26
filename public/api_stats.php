<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/auth.php';
require_login();

$pdo = db();

// --- Get selected year from request ---
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$currentYear = (int)date('Y');

// Validasi tahun
if ($selectedYear < 2020 || $selectedYear > $currentYear) {
  $selectedYear = $currentYear;
}

$startDate = $selectedYear . '-01-01';
$endDate = $selectedYear . '-12-31';

// --- Summary Counts ---
// Permohonan - menggunakan tgl_pengajuan
$stmt = $pdo->prepare("SELECT COUNT(*) FROM permohonan WHERE YEAR(tgl_pengajuan) = ?");
$stmt->execute([$selectedYear]);
$permohonanCount = (int)$stmt->fetchColumn();

// Penelaahan - menggunakan tanggal_dispo  
$stmt = $pdo->prepare("SELECT COUNT(*) FROM penelaahan WHERE YEAR(tanggal_dispo) = ?");
$stmt->execute([$selectedYear]);
$penelaahanCount = (int)$stmt->fetchColumn();

// Layanan - menggunakan tanggal_disposisi
$stmt = $pdo->prepare("SELECT COUNT(*) FROM layanan WHERE YEAR(tanggal_disposisi) = ?");
$stmt->execute([$selectedYear]);
$layananCount = (int)$stmt->fetchColumn();

// Pengeluaran - menggunakan tanggal
$stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran WHERE YEAR(tanggal) = ?");
$stmt->execute([$selectedYear]);
$pengeluaranCount = (float)$stmt->fetchColumn();

$counts = [
    'permohonan'  => $permohonanCount,
    'penelaahan'  => $penelaahanCount,
    'layanan'     => $layananCount,
    'pengeluaran' => $pengeluaranCount,
];
$counts['pengeluaran_fmt'] = number_format($counts['pengeluaran'], 0, ',', '.');

// --- Chart Data: Per Tahun yang Dipilih ---
function seriesFill($rows, $selectedYear) {
  $map = [];
  foreach ($rows as $r) {
      if (isset($r['ym']) && isset($r['c'])) {
          $map[$r['ym']] = (float)$r['c'];
      }
  }
  $labels = [];
  $data = [];
  
  // Nama bulan dalam bahasa Indonesia
  $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  
  for ($i = 1; $i <= 12; $i++) {
      $month = str_pad($i, 2, '0', STR_PAD_LEFT);
      $ym = $selectedYear . '-' . $month;
      $labels[] = $monthNames[$i-1]; // Gunakan nama bulan Indonesia
      $data[] = (float)($map[$ym] ?? 0);
  }
  return [$labels, $data];
}

// Permohonan
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(tgl_pengajuan,'%Y-%m') ym, COUNT(*) c
    FROM permohonan
    WHERE YEAR(tgl_pengajuan) = ?
    GROUP BY ym ORDER BY ym
");
$stmt->execute([$selectedYear]);
$perm = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Penelaahan
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(tanggal_dispo,'%Y-%m') ym, COUNT(*) c
    FROM penelaahan
    WHERE YEAR(tanggal_dispo) = ?
    GROUP BY ym ORDER BY ym
");
$stmt->execute([$selectedYear]);
$penel = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Layanan (per tahun yang dipilih)
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(tanggal_disposisi,'%Y-%m') ym, COUNT(*) c
    FROM layanan
    WHERE YEAR(tanggal_disposisi) = ?
    GROUP BY ym ORDER BY ym
");
$stmt->execute([$selectedYear]);
$layan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pengeluaran (per tahun yang dipilih)
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(tanggal,'%Y-%m') ym, SUM(jumlah) c
    FROM pengeluaran
    WHERE YEAR(tanggal) = ?
    GROUP BY ym ORDER BY ym
");
$stmt->execute([$selectedYear]);
$peng = $stmt->fetchAll(PDO::FETCH_ASSOC);

list($labelsPerm, $dataPerm) = seriesFill($perm, $selectedYear);
list(, $dataPenel) = seriesFill($penel, $selectedYear);
list(, $dataLayan) = seriesFill($layan, $selectedYear);
list($labelsPeng, $dataPeng) = seriesFill($peng, $selectedYear);

// --- Data Keuangan per Anggaran ---
$anggarans = $pdo->query("SELECT kode_anggaran, nama_anggaran FROM anggaran")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT kode_anggaran, DATE_FORMAT(tanggal,'%Y-%m') ym, SUM(jumlah) c
    FROM pengeluaran
    WHERE YEAR(tanggal) = ?
    GROUP BY kode_anggaran, ym
");
$stmt->execute([$selectedYear]);
$pengByAnggaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mapPeng = [];
foreach ($pengByAnggaran as $r) {
    if (isset($r['kode_anggaran'], $r['ym'], $r['c'])) {
        $mapPeng[$r['kode_anggaran']][$r['ym']] = (float)$r['c'];
    }
}

$labelsKeu = [];
for ($i = 1; $i <= 12; $i++) {
    $month = str_pad($i, 2, '0', STR_PAD_LEFT);
    $labelsKeu[] = $selectedYear . '-' . $month;
}

$datasetsKeu = [];
foreach ($anggarans as $a) {
    $vals = [];
    foreach ($labelsKeu as $ym) {
        $vals[] = isset($mapPeng[$a['kode_anggaran']][$ym]) ? $mapPeng[$a['kode_anggaran']][$ym] : 0;
    }
    $datasetsKeu[] = [
        'label' => $a['nama_anggaran'],
        'data'  => $vals
    ];
}

// Pastikan semua array memiliki data default
if (empty($dataPerm)) $dataPerm = array_fill(0, 12, 0);
if (empty($dataPenel)) $dataPenel = array_fill(0, 12, 0); 
if (empty($dataLayan)) $dataLayan = array_fill(0, 12, 0);
if (empty($dataPeng)) $dataPeng = array_fill(0, 12, 0);

// --- Data untuk Peta Provinsi (3 Provinsi) ---
$stmt = $pdo->prepare("
    SELECT provinsi, COUNT(*) c
    FROM permohonan
    WHERE provinsi IN ('DI. ACEH', 'SUMATERA UTARA', 'SUMATERA BARAT') 
    AND YEAR(tgl_pengajuan) = ?
    GROUP BY provinsi
");
$stmt->execute([$selectedYear]);
$prov = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$map = [
    'DI. ACEH' => 'Aceh',
    'SUMATERA UTARA' => 'Sumatera Utara',
    'SUMATERA BARAT' => 'Sumatera Barat'
];

$provCounts = [];
foreach ($prov as $k => $v) {
    $key = isset($map[$k]) ? $map[$k] : $k;
    $provCounts[$key] = (int)$v;
}

$allProvinces = array_values($map);
foreach ($allProvinces as $province) {
    if (!isset($provCounts[$province])) $provCounts[$province] = 0;
}

// --- FillKey untuk Peta ---
$maxCount = !empty($provCounts) ? max($provCounts) : 0;
$fillKeys = [];
foreach ($allProvinces as $province) {
    $count = $provCounts[$province] ?? 0;
    if ($maxCount > 0) {
        $ratio = $count / $maxCount;
        if ($ratio > 0.7)      $fillKeys[$province] = 'high';
        elseif ($ratio > 0.3)  $fillKeys[$province] = 'medium';
        else                   $fillKeys[$province] = 'low';
    } else {
        $fillKeys[$province] = 'low';
    }
}

// --- Output JSON ---
header('Content-Type: application/json');
echo json_encode([
    'selectedYear' => $selectedYear,
    'counts' => $counts,
    'charts' => [
        'permohonan_line' => [
            'labels'     => $labelsPerm,
            'permohonan' => $dataPerm,
            'penelaahan' => $dataPenel,
            'layanan'    => $dataLayan,
        ],
        'keuangan' => [
            'labels'   => $labelsKeu,
            'datasets' => $datasetsKeu,
        ],
        'pengeluaran' => [
            'labels' => $labelsPeng,
            'data'   => $dataPeng,
        ],
    ],
    'map' => [
        'provinsi_counts'    => $provCounts,
        'provinsi_fillkeys' => $fillKeys
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

exit;
?>