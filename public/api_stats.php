<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/auth.php';

// Pastikan user sudah login
require_login();

// Set header JSON
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = db();

    // --- Validasi dan sanitasi tahun yang dipilih ---
   // --- Validasi dan sanitasi tahun yang dipilih ---
$selectedYear = isset($_GET['year']) && is_numeric($_GET['year']) 
    ? (int)$_GET['year'] 
    : (int)date('Y');

// Cek apakah tahun tersedia di database
$stmt = $pdo->prepare("
    SELECT EXISTS(
        SELECT 1 FROM (
            SELECT YEAR(tgl_pengajuan) as tahun FROM permohonan
            UNION SELECT YEAR(tanggal_dispo) FROM penelaahan
            UNION SELECT YEAR(tanggal) FROM pengeluaran
            UNION SELECT YEAR(tgl_mulai_layanan) FROM layanan
            UNION SELECT tahun FROM anggaran
        ) years 
        WHERE tahun = ?
    ) as tahun_ada
");
$stmt->execute([$selectedYear]);
$tahunValid = (bool)$stmt->fetchColumn();

// Jika tahun tidak valid, cari tahun terdekat yang ada data
if (!$tahunValid) {
    $stmt = $pdo->query("
        SELECT MAX(tahun) as max_tahun 
        FROM (
            SELECT YEAR(tgl_pengajuan) as tahun FROM permohonan
            UNION SELECT YEAR(tanggal_dispo) FROM penelaahan
            UNION SELECT YEAR(tanggal) FROM pengeluaran
            UNION SELECT YEAR(tgl_mulai_layanan) FROM layanan
            UNION SELECT tahun FROM anggaran
        ) years
    ");
    $selectedYear = $stmt->fetchColumn() ?: (int)date('Y');
}

    // --- Fungsi helper untuk query database ---
    function executeQuery($pdo, $sql, $params = []) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database error in executeQuery: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }

    function getCount($pdo, $sql, $params = [], $isFloat = false) {
        $stmt = executeQuery($pdo, $sql, $params);
        $value = $stmt->fetchColumn();
        
        if ($value === false || $value === null) {
            return $isFloat ? 0.0 : 0;
        }
        
        return $isFloat ? (float)$value : (int)$value;
    }

    // --- Hitung ringkasan data utama ---
    $permohonanCount = getCount(
        $pdo, 
        "SELECT COUNT(*) FROM permohonan WHERE YEAR(tgl_pengajuan) = ?", 
        [$selectedYear]
    );

    $penelaahanCount = getCount(
        $pdo, 
        "SELECT COUNT(*) FROM penelaahan WHERE YEAR(tanggal_dispo) = ?", 
        [$selectedYear]
    );

    $layananCount = getCount(
        $pdo,
        "SELECT COUNT(*) FROM layanan 
         WHERE (YEAR(tanggal_disposisi) = ? OR (tanggal_disposisi IS NULL AND YEAR(tgl_mulai_layanan) = ?))",
        [$selectedYear, $selectedYear]
    );

    $pengeluaranCount = getCount(
        $pdo, 
        "SELECT COALESCE(SUM(jumlah), 0) FROM pengeluaran WHERE YEAR(tanggal) = ?", 
        [$selectedYear], 
        true
    );

    $counts = [
        'permohonan' => $permohonanCount,
        'penelaahan' => $penelaahanCount,
        'layanan' => $layananCount,
        'pengeluaran' => $pengeluaranCount,
        'pengeluaran_fmt' => number_format($pengeluaranCount, 0, ',', '.')
    ];

    // --- Fungsi untuk mengisi data bulanan ---
    function fillMonthlySeries($rows, $selectedYear) {
        $dataMap = [];
        
        // Mapping data dari database
        foreach ($rows as $row) {
            if (isset($row['ym'], $row['c'])) {
                $dataMap[$row['ym']] = (float)$row['c'];
            }
        }

        $monthNames = [
            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
        ];

        $labels = [];
        $data = [];

        // Generate data untuk 12 bulan
        for ($month = 1; $month <= 12; $month++) {
            $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
            $yearMonth = $selectedYear . '-' . $monthStr;
            
            $labels[] = $monthNames[$month - 1];
            $data[] = $dataMap[$yearMonth] ?? 0.0;
        }

        return [$labels, $data];
    }

    // --- Ambil data bulanan untuk chart ---
    function fetchMonthlyData($pdo, $sql, $selectedYear) {
        try {
            $stmt = executeQuery($pdo, $sql, [$selectedYear]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching monthly data: " . $e->getMessage());
            return [];
        }
    }

    // Query untuk data bulanan
    $permohonanMonthly = fetchMonthlyData(
        $pdo,
        "SELECT DATE_FORMAT(tgl_pengajuan, '%Y-%m') as ym, COUNT(*) as c 
         FROM permohonan 
         WHERE YEAR(tgl_pengajuan) = ? 
         GROUP BY ym 
         ORDER BY ym",
        $selectedYear
    );

    $penelaahanMonthly = fetchMonthlyData(
        $pdo,
        "SELECT DATE_FORMAT(tanggal_dispo, '%Y-%m') as ym, COUNT(*) as c 
         FROM penelaahan 
         WHERE YEAR(tanggal_dispo) = ? 
         GROUP BY ym 
         ORDER BY ym",
        $selectedYear
    );

    $layananMonthly = fetchMonthlyData(
        $pdo,
        "SELECT DATE_FORMAT(COALESCE(tanggal_disposisi, tgl_mulai_layanan), '%Y-%m') as ym, COUNT(*) as c 
         FROM layanan 
         WHERE YEAR(COALESCE(tanggal_disposisi, tgl_mulai_layanan)) = ? 
         GROUP BY ym 
         ORDER BY ym",
        $selectedYear
    );

    $pengeluaranMonthly = fetchMonthlyData(
        $pdo,
        "SELECT DATE_FORMAT(tanggal, '%Y-%m') as ym, SUM(jumlah) as c 
         FROM pengeluaran 
         WHERE YEAR(tanggal) = ? 
         GROUP BY ym 
         ORDER BY ym",
        $selectedYear
    );

    // Isi data series untuk chart
    list($labelsPermohonan, $dataPermohonan) = fillMonthlySeries($permohonanMonthly, $selectedYear);
    list(, $dataPenelaahan) = fillMonthlySeries($penelaahanMonthly, $selectedYear);
    list(, $dataLayanan) = fillMonthlySeries($layananMonthly, $selectedYear);
    list($labelsPengeluaran, $dataPengeluaran) = fillMonthlySeries($pengeluaranMonthly, $selectedYear);

    // --- Data Keuangan per Anggaran ---
    $stmt = executeQuery($pdo, "SELECT kode_anggaran, nama_anggaran FROM anggaran ORDER BY kode_anggaran");
    $anggaranList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = executeQuery(
        $pdo,
        "SELECT kode_anggaran, DATE_FORMAT(tanggal, '%Y-%m') as ym, SUM(jumlah) as c
         FROM pengeluaran
         WHERE YEAR(tanggal) = ?
         GROUP BY kode_anggaran, ym
         ORDER BY kode_anggaran, ym",
        [$selectedYear]
    );
    $pengeluaranByAnggaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapping pengeluaran per anggaran
    $pengeluaranMap = [];
    foreach ($pengeluaranByAnggaran as $row) {
        if (isset($row['kode_anggaran'], $row['ym'], $row['c'])) {
            $pengeluaranMap[$row['kode_anggaran']][$row['ym']] = (float)$row['c'];
        }
    }

    // Generate labels keuangan
    $labelsKeuangan = [];
    for ($month = 1; $month <= 12; $month++) {
        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
        $labelsKeuangan[] = $selectedYear . '-' . $monthStr;
    }

    // Generate datasets untuk chart keuangan
    $datasetsKeuangan = [];
    foreach ($anggaranList as $anggaran) {
        $values = [];
        foreach ($labelsKeuangan as $yearMonth) {
            $values[] = $pengeluaranMap[$anggaran['kode_anggaran']][$yearMonth] ?? 0;
        }

        $datasetsKeuangan[] = [
            'label' => $anggaran['nama_anggaran'],
            'data' => $values
        ];
    }

    // --- Data Sisa Anggaran per Kode ---
$stmt = executeQuery(
    $pdo,
    "SELECT 
        a.kode_anggaran,
        a.nama_anggaran,
        a.total_anggaran,
        COALESCE(SUM(p.jumlah), 0) as total_pengeluaran,
        (a.total_anggaran - COALESCE(SUM(p.jumlah), 0)) as sisa_anggaran
    FROM anggaran a
    LEFT JOIN pengeluaran p ON a.kode_anggaran = p.kode_anggaran 
        AND YEAR(p.tanggal) = ?
    WHERE a.tahun = ?
    GROUP BY a.kode_anggaran, a.nama_anggaran, a.total_anggaran
    ORDER BY a.kode_anggaran",
    [$selectedYear, $selectedYear]
);
$anggaranData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total keseluruhan
$totalAnggaran = 0;
$totalPengeluaran = 0;
$totalSisa = 0;
$anggaranPerKode = [];

foreach ($anggaranData as $item) {
    $anggaran = (float)$item['total_anggaran'];
    $pengeluaran = (float)$item['total_pengeluaran'];
    $sisa = (float)$item['sisa_anggaran'];

    $totalAnggaran += $anggaran;
    $totalPengeluaran += $pengeluaran;
    $totalSisa += $sisa;

    $anggaranPerKode[] = [
        'kode' => $item['kode_anggaran'],
        'nama' => $item['nama_anggaran'],
        'total' => $anggaran,
        'total_fmt' => number_format($anggaran, 0, ',', '.'),
        'pengeluaran' => $pengeluaran,
        'pengeluaran_fmt' => number_format($pengeluaran, 0, ',', '.'),
        'sisa' => $sisa,
        'sisa_fmt' => number_format($sisa, 0, ',', '.'),
        'persentase_penggunaan' => $anggaran > 0 ? round(($pengeluaran / $anggaran) * 100, 2) : 0
    ];
}

$anggaranSummary = [
    'total' => $totalAnggaran,
    'total_fmt' => number_format($totalAnggaran, 0, ',', '.'),
    'pengeluaran' => $totalPengeluaran,
    'pengeluaran_fmt' => number_format($totalPengeluaran, 0, ',', '.'),
    'sisa' => $totalSisa,
    'sisa_fmt' => number_format($totalSisa, 0, ',', '.'),
    'persentase_penggunaan' => $totalAnggaran > 0 ? round(($totalPengeluaran / $totalAnggaran) * 100, 2) : 0,
    'per_kode' => $anggaranPerKode
];


// --- Data Beban Kerja Pegawai ---
// --- Data Beban Kerja Pegawai ---
$stmt = executeQuery(
    $pdo,
    "SELECT 
        pg.id_pegawai,
        pg.nama_pegawai,
        COUNT(DISTINCT pm.no_reg_medan) as jumlah_permohonan,
        COUNT(DISTINCT pn.no_registrasi) as jumlah_penelaahan, 
        COUNT(DISTINCT ly.no_kep_smpl) as jumlah_layanan
    FROM pegawai pg
    LEFT JOIN permohonan pm ON pg.id_pegawai = pm.id_pegawai AND YEAR(pm.tgl_pengajuan) = ?
    LEFT JOIN penelaahan pn ON pg.id_pegawai = pn.id_pegawai AND YEAR(pn.tanggal_dispo) = ?
    LEFT JOIN layanan ly ON pg.id_pegawai = ly.id_pegawai AND (YEAR(ly.tanggal_disposisi) = ? OR (ly.tanggal_disposisi IS NULL AND YEAR(ly.tgl_mulai_layanan) = ?))
    GROUP BY pg.id_pegawai, pg.nama_pegawai
    ORDER BY pg.nama_pegawai",
    [$selectedYear, $selectedYear, $selectedYear, $selectedYear]
);
$bebanKerjaPegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format data untuk chart
$pegawaiLabels = [];
$permohonanData = [];
$penelaahanData = [];
$layananData = [];

foreach ($bebanKerjaPegawai as $pegawai) {
    $pegawaiLabels[] = $pegawai['nama_pegawai'];
    $permohonanData[] = (int)$pegawai['jumlah_permohonan'];
    $penelaahanData[] = (int)$pegawai['jumlah_penelaahan'];
    $layananData[] = (int)$pegawai['jumlah_layanan'];
}

$bebanKerjaChart = [
    'labels' => $pegawaiLabels,
    'datasets' => [
        [
            'label' => 'Permohonan',
            'data' => $permohonanData,
            'backgroundColor' => 'rgba(59, 130, 246, 0.7)'
        ],
        [
            'label' => 'Penelaahan',
            'data' => $penelaahanData,
            'backgroundColor' => 'rgba(16, 185, 129, 0.7)'
        ],
        [
            'label' => 'Layanan',
            'data' => $layananData,
            'backgroundColor' => 'rgba(245, 158, 11, 0.7)'
        ]
    ]
];

    // --- Data untuk Peta Provinsi ---
    $stmt = executeQuery(
        $pdo,
        "SELECT provinsi, COUNT(*) as jumlah
         FROM permohonan
         WHERE provinsi IN ('DI. ACEH', 'SUMATERA UTARA', 'SUMATERA BARAT') 
         AND YEAR(tgl_pengajuan) = ?
         GROUP BY provinsi",
        [$selectedYear]
    );
    $provinsiData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Mapping nama provinsi
    $provinsiMapping = [
        'DI. ACEH' => 'Aceh',
        'SUMATERA UTARA' => 'Sumatera Utara',
        'SUMATERA BARAT' => 'Sumatera Barat'
    ];

    $provinsiCounts = [];
    foreach ($provinsiData as $dbName => $count) {
        $displayName = $provinsiMapping[$dbName] ?? $dbName;
        $provinsiCounts[$displayName] = (int)$count;
    }

    // Pastikan semua provinsi ada dalam array
    $allProvinces = array_values($provinsiMapping);
    foreach ($allProvinces as $province) {
        if (!isset($provinsiCounts[$province])) {
            $provinsiCounts[$province] = 0;
        }
    }

    // Generate fill keys untuk peta
    $maxCount = !empty($provinsiCounts) ? max($provinsiCounts) : 0;
    $fillKeys = [];

    foreach ($allProvinces as $province) {
        $count = $provinsiCounts[$province] ?? 0;
        
        if ($maxCount > 0) {
            $ratio = $count / $maxCount;
            if ($ratio > 0.7) {
                $fillKeys[$province] = 'high';
            } elseif ($ratio > 0.3) {
                $fillKeys[$province] = 'medium';
            } else {
                $fillKeys[$province] = 'low';
            }
        } else {
            $fillKeys[$province] = 'low';
        }
    }

// --- Hitung persentase perubahan dari bulan sebelumnya ---
function calculateMonthOverMonthChange($monthlyData, $selectedYear) {
    $currentMonth = (int)date('n');
    $currentYear = (int)date('Y');
    
    // Jika tahun yang dipilih bukan tahun berjalan, gunakan Desember sebagai bulan "berjalan"
    if ($selectedYear != $currentYear) {
        $currentMonth = 12;
    }
    
    $previousMonth = $currentMonth - 1;
    $previousYear = $selectedYear;
    
    // Jika bulan sebelumnya adalah 0 (Januari), gunakan Desember tahun sebelumnya
    if ($previousMonth < 1) {
        $previousMonth = 12;
        $previousYear = $selectedYear - 1;
    }
    
    // Cek apakah data untuk bulan berjalan tersedia
    $currentValue = 0;
    $currentMonthKey = $selectedYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT);
    foreach ($monthlyData as $data) {
        if ($data['ym'] == $currentMonthKey) {
            $currentValue = (float)$data['c'];
            break;
        }
    }
    
    // Cek apakah data untuk bulan sebelumnya tersedia
    $previousValue = 0;
    $previousMonthKey = $previousYear . '-' . str_pad($previousMonth, 2, '0', STR_PAD_LEFT);
    
    // Untuk data bulan sebelumnya, kita perlu query ulang jika tahun berbeda
    if ($previousYear != $selectedYear) {
        $stmt = executeQuery(
            $pdo,
            "SELECT DATE_FORMAT(tanggal, '%Y-%m') as ym, SUM(jumlah) as c 
             FROM pengeluaran 
             WHERE YEAR(tanggal) = ? 
             GROUP BY ym 
             ORDER BY ym",
            [$previousYear]
        );
        $previousYearData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($previousYearData as $data) {
            if ($data['ym'] == $previousMonthKey) {
                $previousValue = (float)$data['c'];
                break;
            }
        }
    } else {
        foreach ($monthlyData as $data) {
            if ($data['ym'] == $previousMonthKey) {
                $previousValue = (float)$data['c'];
                break;
            }
        }
    }
    
    // Hitung persentase perubahan
    if ($previousValue == 0) {
        return $currentValue > 0 ? 100 : 0;
    }
    
    return (($currentValue - $previousValue) / $previousValue) * 100;
}

// Hitung persentase perubahan untuk setiap metrik
$permohonanChange = calculateMonthOverMonthChange($permohonanMonthly, $selectedYear);
$penelaahanChange = calculateMonthOverMonthChange($penelaahanMonthly, $selectedYear);
$layananChange = calculateMonthOverMonthChange($layananMonthly, $selectedYear);
$pengeluaranChange = calculateMonthOverMonthChange($pengeluaranMonthly, $selectedYear);

// Tambahkan ke counts
$counts['permohonan_change'] = round($permohonanChange, 1);
$counts['penelaahan_change'] = round($penelaahanChange, 1);
$counts['layanan_change'] = round($layananChange, 1);
$counts['pengeluaran_change'] = round($pengeluaranChange, 1);

// --- Data Aktivitas Terbaru ---
$stmt = executeQuery(
    $pdo,
    "(
        SELECT 
            'permohonan' as jenis,
            no_reg_medan as nomor,
            tgl_pengajuan as tanggal,
            nama_pemohon,
            'Permohonan baru diterima' as aktivitas,
            'blue' as warna,
            'fa-file-import' as icon
        FROM permohonan 
        WHERE YEAR(tgl_pengajuan) = ?
        ORDER BY tgl_pengajuan DESC 
        LIMIT 5
    )
    UNION ALL
    (
        SELECT 
            'penelaahan' as jenis,
            no_registrasi as nomor,
            tanggal_dispo as tanggal,
            '' as nama_pemohon,
            'Penelaahan selesai' as aktivitas,
            'green' as warna,
            'fa-check-circle' as icon
        FROM penelaahan 
        WHERE YEAR(tanggal_dispo) = ?
        ORDER BY tanggal_dispo DESC 
        LIMIT 5
    )
    UNION ALL
    (
        SELECT 
            'pengeluaran' as jenis,
            nomor_kuintasi as nomor,
            tanggal,
            '' as nama_pemohon,
            'Pengeluaran baru dicatat' as aktivitas,
            'amber' as warna,
            'fa-coins' as icon
        FROM pengeluaran 
        WHERE YEAR(tanggal) = ?
        ORDER BY tanggal DESC 
        LIMIT 5
    )
    ORDER BY tanggal DESC 
    LIMIT 5",
    [$selectedYear, $selectedYear, $selectedYear]
);
$aktivitasTerbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format waktu relatif
foreach ($aktivitasTerbaru as &$aktivitas) {
    $waktu = new DateTime($aktivitas['tanggal']);
    $sekarang = new DateTime();
    $selisih = $sekarang->diff($waktu);
    
    if ($selisih->y > 0) {
        $aktivitas['waktu'] = $selisih->y . ' tahun yang lalu';
    } elseif ($selisih->m > 0) {
        $aktivitas['waktu'] = $selisih->m . ' bulan yang lalu';
    } elseif ($selisih->d > 0) {
        $aktivitas['waktu'] = $selisih->d . ' hari yang lalu';
    } elseif ($selisih->h > 0) {
        $aktivitas['waktu'] = $selisih->h . ' jam yang lalu';
    } else {
        $aktivitas['waktu'] = 'Beberapa menit yang lalu';
    }
}


$response = [
    'success' => true,
    'selectedYear' => $selectedYear,
    'counts' => $counts,
    'anggaran' => $anggaranSummary,
    'aktivitas_terbaru' => $aktivitasTerbaru,
    'charts' => [
        'permohonan_line' => [
            'labels' => $labelsPermohonan,
            'permohonan' => $dataPermohonan,
            'penelaahan' => $dataPenelaahan,
            'layanan' => $dataLayanan,
        ],
        'pengeluaran' => [
            'labels' => $labelsPengeluaran,
            'data' => $dataPengeluaran,
        ],
        'beban_kerja' => $bebanKerjaChart
    ],
    'map' => [
        'provinsi_counts' => $provinsiCounts,
        'provinsi_fillkeys' => $fillKeys
    ]
];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Log error untuk debugging
    error_log("API Stats Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Terjadi kesalahan saat memuat data dashboard',
        'message' => 'Silakan coba lagi atau hubungi administrator sistem'
    ], JSON_UNESCAPED_UNICODE);
}

exit;
?>