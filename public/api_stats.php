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
    $selectedYear = isset($_GET['year']) && is_numeric($_GET['year']) 
        ? (int)$_GET['year'] 
        : (int)date('Y');
    
    $currentYear = (int)date('Y');
    
    // Batasi rentang tahun yang valid
    if ($selectedYear < 2020 || $selectedYear > $currentYear) {
        $selectedYear = $currentYear;
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
            'persentase' => $totalAnggaran > 0 ? round(($anggaran / $totalAnggaran) * 100, 2) : 0
        ];
    }

    $anggaranSummary = [
        'total' => $totalAnggaran,
        'total_fmt' => number_format($totalAnggaran, 0, ',', '.'),
        'pengeluaran' => $totalPengeluaran,
        'pengeluaran_fmt' => number_format($totalPengeluaran, 0, ',', '.'),
        'sisa' => $totalSisa,
        'sisa_fmt' => number_format($totalSisa, 0, ',', '.'),
        'per_kode' => $anggaranPerKode
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

    // --- Siapkan response JSON ---
    $response = [
        'success' => true,
        'selectedYear' => $selectedYear,
        'counts' => $counts,
        'anggaran' => $anggaranSummary,
        'charts' => [
            'permohonan_line' => [
                'labels' => $labelsPermohonan,
                'permohonan' => $dataPermohonan,
                'penelaahan' => $dataPenelaahan,
                'layanan' => $dataLayanan,
            ],
            'keuangan' => [
                'labels' => $labelsKeuangan,
                'datasets' => $datasetsKeuangan,
            ],
            'pengeluaran' => [
                'labels' => $labelsPengeluaran,
                'data' => $dataPengeluaran,
            ],
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