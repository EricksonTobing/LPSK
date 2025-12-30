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

    // --- Validasi dan sanitasi tahun dan bulan yang dipilih ---
    $selectedYear = isset($_GET['year']) && is_numeric($_GET['year']) 
        ? (int)$_GET['year'] 
        : (int)date('Y');

    $selectedMonth = null;
    if (isset($_GET['month']) && is_numeric($_GET['month'])) {
        $m = (int)$_GET['month'];
        if ($m >= 1 && $m <= 12) {
            $selectedMonth = $m;
        }
    }

    // Cek apakah tahun tersedia di database
    $stmt = $pdo->prepare("SELECT EXISTS(SELECT 1 FROM permohonan WHERE YEAR(tgl_pengajuan) = ?) as tahun_ada");
    $stmt->execute([$selectedYear]);
    $tahunValid = (bool)$stmt->fetchColumn();

    // Jika tahun tidak valid, cari tahun terdekat yang ada data
    if (!$tahunValid) {
        $stmt = $pdo->query("SELECT MAX(YEAR(tgl_pengajuan)) as max_tahun FROM permohonan");
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



    $dateRangeType = $_GET['date_range_type'] ?? 'year';
$selectedYear = null;
$selectedMonth = null;
$startDate = null;
$endDate = null;

// Handle different date range types
if ($dateRangeType === 'year') {
    $selectedYear = isset($_GET['year']) && is_numeric($_GET['year']) 
        ? (int)$_GET['year'] 
        : (int)date('Y');
} elseif ($dateRangeType === 'month') {
    $selectedYear = isset($_GET['year']) && is_numeric($_GET['year']) 
        ? (int)$_GET['year'] 
        : (int)date('Y');
    
    if (isset($_GET['month']) && is_numeric($_GET['month'])) {
        $m = (int)$_GET['month'];
        if ($m >= 1 && $m <= 12) {
            $selectedMonth = $m;
        }
    }
} elseif ($dateRangeType === 'custom') {
    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        $startDate = $_GET['start_date'];
        $endDate = $_GET['end_date'];
        
        // Validate dates
        if (!strtotime($startDate) || !strtotime($endDate)) {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-d');
        }
        
        // Ensure end date is not before start date
        if (strtotime($endDate) < strtotime($startDate)) {
            $endDate = $startDate;
        }
    } else {
        // Default to current month if dates not provided
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-d');
    }
}

// --- Determine Granularity (Monthly vs Daily) ---
$isDaily = false;
if ($dateRangeType === 'month' && $selectedMonth) {
    // If specific month is selected, show daily breakdown
    $isDaily = true;
    // Set start and end date for the month iteration
    $startDate = date('Y-m-01', strtotime("$selectedYear-$selectedMonth-01"));
    $endDate = date('Y-m-t', strtotime("$selectedYear-$selectedMonth-01"));
} elseif ($dateRangeType === 'custom' && $startDate && $endDate) {
    // If custom range is short (<= 60 days for comfortable daily view), show daily breakdown
    $d1 = new DateTime($startDate);
    $d2 = new DateTime($endDate);
    $diff = $d1->diff($d2)->days;
    if ($diff <= 60) {
        $isDaily = true;
    }
}

// SQL Group Format
// Monthly: %Y-%m
// Daily: %Y-%m-%d
$sqlDateFormat = $isDaily ? '%Y-%m-%d' : '%Y-%m';

    // --- Update fungsi ymWhere untuk handle rentang kustom ---
function ymWhere($field, $selectedYear, $selectedMonth = null, $startDate = null, $endDate = null, $dateRangeType = 'year') {
    if ($dateRangeType === 'custom' && $startDate && $endDate) {
        $clause = "$field BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        return [$clause, $params];
    } elseif (!is_null($selectedMonth)) {
        $clause = "YEAR($field) = ? AND MONTH($field) = ?";
        $params = [$selectedYear, $selectedMonth];
        return [$clause, $params];
    } else {
        $clause = "YEAR($field) = ?";
        $params = [$selectedYear];
        return [$clause, $params];
    }
}

// --- Fungsi helper untuk query dengan rentang kustom ---
function buildDateCondition($field, $dateRangeType, $selectedYear, $selectedMonth, $startDate, $endDate) {
    if ($dateRangeType === 'custom') {
        return "$field BETWEEN '$startDate' AND '$endDate'";
    } elseif ($selectedMonth) {
        return "YEAR($field) = $selectedYear AND MONTH($field) = $selectedMonth";
    } else {
        return "YEAR($field) = $selectedYear";
    }
}

    // --- Hitung ringkasan data utama ---
// PERMOHONAN
list($w, $p) = ymWhere('tgl_pengajuan', $selectedYear, $selectedMonth, $startDate, $endDate, $dateRangeType);
$permohonanCount = getCount(
    $pdo,
    "SELECT COUNT(*) FROM permohonan WHERE $w AND tempat_permohonan != 'JAKARTA'",
    $p
);

// PENELAAHAN
list($w, $p) = ymWhere('tanggal_dispo', $selectedYear, $selectedMonth, $startDate, $endDate, $dateRangeType);
$penelaahanCount = getCount(
    $pdo,
    "SELECT COUNT(*) FROM penelaahan WHERE $w",
    $p
);

// LAYANAN - handle special case with COALESCE
if ($dateRangeType === 'custom') {
    $layananCount = getCount(
        $pdo,
        "SELECT COUNT(*) FROM layanan 
         WHERE (COALESCE(tanggal_disposisi, tgl_mulai_layanan) BETWEEN ? AND ?)",
        [$startDate, $endDate]
    );
} elseif (!is_null($selectedMonth)) {
    $layananCount = getCount(
        $pdo,
        "SELECT COUNT(*) FROM layanan 
         WHERE ( (YEAR(tanggal_disposisi) = ? AND MONTH(tanggal_disposisi) = ?)
            OR (tanggal_disposisi IS NULL AND YEAR(tgl_mulai_layanan) = ? AND MONTH(tgl_mulai_layanan) = ?) )",
        [$selectedYear, $selectedMonth, $selectedYear, $selectedMonth]
    );
} else {
    $layananCount = getCount(
        $pdo,
        "SELECT COUNT(*) FROM layanan 
         WHERE (YEAR(tanggal_disposisi) = ? OR (tanggal_disposisi IS NULL AND YEAR(tgl_mulai_layanan) = ?))",
        [$selectedYear, $selectedYear]
    );
}

// PENGELUARAN
list($w, $p) = ymWhere('tanggal', $selectedYear, $selectedMonth, $startDate, $endDate, $dateRangeType);
$pengeluaranCount = getCount(
    $pdo,
    "SELECT COALESCE(SUM(jumlah), 0) FROM pengeluaran WHERE $w",
    $p,
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
    // --- Fungsi umum untuk mengisi data series (Monthly/Daily) ---
   function fillChartSeries($rows, $selectedYear, $selectedMonth = null, $startDate = null, $endDate = null, $dateRangeType = 'year', $isDaily = false) {
    $dataMap = [];
    
    // Mapping data dari database
    foreach ($rows as $row) {
        if (isset($row['ym'], $row['c'])) {
            $dataMap[$row['ym']] = (float)$row['c'];
        }
    }

    $labels = [];
    $data = [];

    if ($isDaily && $startDate && $endDate) {
        // Daily Iteration
        $current = new DateTime($startDate);
        $end = new DateTime($endDate);
        
        while ($current <= $end) {
            $dateKey = $current->format('Y-m-d');
            // Label format: "01 Jan"
            $label = $current->format('d M');
            
            $labels[] = $label;
            $data[] = $dataMap[$dateKey] ?? 0.0;
            
            $current->modify('+1 day');
        }
    } elseif ($dateRangeType === 'custom' && $startDate && $endDate) {
        // Custom Range Monthly (Wide range)
        $start = DateTime::createFromFormat('Y-m-d', $startDate);
        $end = DateTime::createFromFormat('Y-m-d', $endDate);
        
        $current = clone $start;
        $current->modify('first day of this month');
        
        while ($current <= $end) {
            $yearMonth = $current->format('Y-m');
            $monthName = $current->format('M Y');
            
            $labels[] = $monthName;
            $data[] = $dataMap[$yearMonth] ?? 0.0;
            
            $current->modify('+1 month');
        }
        
    } elseif (!is_null($selectedMonth)) {
        // Single Month view but NOT daily mode (e.g. data anomaly or very short month without dates? unlikely to hit this with current logic)
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $yearMonth = $selectedYear . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT);
        
        return [
            [$monthNames[$selectedMonth - 1]],
            [$dataMap[$yearMonth] ?? 0.0]
        ];
        
    } else {
        // Yearly view (12 months)
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        for ($month = 1; $month <= 12; $month++) {
            $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
            $yearMonth = $selectedYear . '-' . $monthStr;
            
            $labels[] = $monthNames[$month - 1];
            $data[] = $dataMap[$yearMonth] ?? 0.0;
        }
    }
    
    return [$labels, $data];
}

    // --- Fungsi mengambil data chart (Monthly/Weekly) ---
    function fetchChartData($pdo, $sqlBase, $selectedYear, $selectedMonth = null, $field = null, $startDate = null, $endDate = null, $dateRangeType = 'year', $sqlDateFormat = '%Y-%m') {
    try {
        if ($dateRangeType === 'custom') {
            $sql = $sqlBase . " WHERE $field BETWEEN ? AND ? 
                    GROUP BY DATE_FORMAT($field, '$sqlDateFormat') 
                    ORDER BY DATE_FORMAT($field, '$sqlDateFormat')";
            $params = [$startDate, $endDate];
        } elseif (!is_null($selectedMonth)) {
            $sql = $sqlBase . " WHERE YEAR($field) = ? AND MONTH($field) = ? 
                    GROUP BY DATE_FORMAT($field, '$sqlDateFormat') ORDER BY DATE_FORMAT($field, '$sqlDateFormat')";
            $params = [$selectedYear, $selectedMonth];
        } else {
            $sql = $sqlBase . " WHERE YEAR($field) = ? 
                    GROUP BY DATE_FORMAT($field, '$sqlDateFormat') ORDER BY DATE_FORMAT($field, '$sqlDateFormat')";
            $params = [$selectedYear];
        }
        
        $stmt = executeQuery($pdo, $sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching chart data: " . $e->getMessage());
        return [];
    }
}

    // Query untuk data bulanan
    // HANYA DATA PERMOHONAN YANG DIFILTER (tambah kondisi tempat_permohonan != 'JAKARTA')
    $permohonanMonthly = (function() use($pdo, $selectedYear, $selectedMonth, $startDate, $endDate, $dateRangeType, $sqlDateFormat) {
    try {
        if ($dateRangeType === 'custom') {
            $sql = "SELECT DATE_FORMAT(tgl_pengajuan, '$sqlDateFormat') as ym, COUNT(*) as c 
                    FROM permohonan 
                    WHERE tgl_pengajuan BETWEEN ? AND ? AND tempat_permohonan != 'JAKARTA'
                    GROUP BY ym ORDER BY ym";
            $params = [$startDate, $endDate];
        } elseif (!is_null($selectedMonth)) {
            $sql = "SELECT DATE_FORMAT(tgl_pengajuan, '$sqlDateFormat') as ym, COUNT(*) as c 
                    FROM permohonan 
                    WHERE YEAR(tgl_pengajuan) = ? AND MONTH(tgl_pengajuan) = ? AND tempat_permohonan != 'JAKARTA'
                    GROUP BY ym ORDER BY ym";
            $params = [$selectedYear, $selectedMonth];
        } else {
            $sql = "SELECT DATE_FORMAT(tgl_pengajuan, '$sqlDateFormat') as ym, COUNT(*) as c 
                    FROM permohonan 
                    WHERE YEAR(tgl_pengajuan) = ? AND tempat_permohonan != 'JAKARTA'
                    GROUP BY ym ORDER BY ym";
            $params = [$selectedYear];
        }
        
        $stmt = executeQuery($pdo, $sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching permohonan chart data: " . $e->getMessage());
        return [];
    }
})();

$penelaahanMonthly = fetchChartData(
    $pdo,
    "SELECT DATE_FORMAT(pn.tanggal_dispo, '$sqlDateFormat') as ym, COUNT(*) as c 
     FROM penelaahan pn 
     JOIN permohonan pm ON pn.no_reg_medan = pm.no_reg_medan",
    $selectedYear,
    $selectedMonth,
    'pn.tanggal_dispo',
    $startDate,
    $endDate,
    $dateRangeType,
    $sqlDateFormat,
    " AND pm.tempat_permohonan != 'JAKARTA'"
);

    // Query untuk data bulanan LAYANAN (special handling)
$layananMonthly = (function() use($pdo, $selectedYear, $selectedMonth, $startDate, $endDate, $dateRangeType, $sqlDateFormat) {
    try {
        if ($dateRangeType === 'custom') {
            $sql = "SELECT DATE_FORMAT(COALESCE(tanggal_disposisi, tgl_mulai_layanan), '$sqlDateFormat') as ym, COUNT(*) as c 
                    FROM layanan 
                    WHERE COALESCE(tanggal_disposisi, tgl_mulai_layanan) BETWEEN ? AND ?
                    GROUP BY ym ORDER BY ym";
            $params = [$startDate, $endDate];
        } elseif (!is_null($selectedMonth)) {
            $sql = "SELECT DATE_FORMAT(COALESCE(tanggal_disposisi, tgl_mulai_layanan), '$sqlDateFormat') as ym, COUNT(*) as c 
                    FROM layanan 
                    WHERE ( (YEAR(tanggal_disposisi) = ? AND MONTH(tanggal_disposisi) = ?)
                        OR (tanggal_disposisi IS NULL AND YEAR(tgl_mulai_layanan) = ? AND MONTH(tgl_mulai_layanan) = ?) )
                    GROUP BY ym ORDER BY ym";
            $params = [$selectedYear, $selectedMonth, $selectedYear, $selectedMonth];
        } else {
            $sql = "SELECT DATE_FORMAT(COALESCE(tanggal_disposisi, tgl_mulai_layanan), '$sqlDateFormat') as ym, COUNT(*) as c 
                    FROM layanan 
                    WHERE YEAR(COALESCE(tanggal_disposisi, tgl_mulai_layanan)) = ?
                    GROUP BY ym ORDER BY ym";
            $params = [$selectedYear];
        }
        
        $stmt = executeQuery($pdo, $sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching layanan chart data: " . $e->getMessage());
        return [];
    }
})();

    // Query untuk data bulanan PENGELUARAN
$pengeluaranMonthly = fetchChartData(
    $pdo,
    "SELECT DATE_FORMAT(tanggal, '$sqlDateFormat') as ym, SUM(jumlah) as c FROM pengeluaran",
    $selectedYear,
    $selectedMonth,
    'tanggal',
    $startDate,
    $endDate,
    $dateRangeType,
    $sqlDateFormat
);

    // Isi data series untuk chart
    list($labelsPermohonan, $dataPermohonan) = fillChartSeries(
    $permohonanMonthly, 
    $selectedYear, 
    $selectedMonth, 
    $startDate, 
    $endDate, 
    $dateRangeType,
    $isDaily
);

list($labelsPenelaahan, $dataPenelaahan) = fillChartSeries(
    $penelaahanMonthly, 
    $selectedYear, 
    $selectedMonth, 
    $startDate, 
    $endDate, 
    $dateRangeType,
    $isDaily
);

list($labelsLayanan, $dataLayanan) = fillChartSeries(
    $layananMonthly, 
    $selectedYear, 
    $selectedMonth, 
    $startDate, 
    $endDate, 
    $dateRangeType,
    $isDaily
);

list($labelsPengeluaran, $dataPengeluaran) = fillChartSeries(
    $pengeluaranMonthly, 
    $selectedYear, 
    $selectedMonth, 
    $startDate, 
    $endDate, 
    $dateRangeType,
    $isDaily
);

$finalLabels = $labelsPermohonan; // Gunakan labels dari permohonan sebagai referensi

$response['charts']['permohonan_line'] = [
    'labels' => $finalLabels,
    'permohonan' => $dataPermohonan,
    'penelaahan' => $dataPenelaahan, 
    'layanan' => $dataLayanan,
];

    // --- Data Keuangan per Anggaran ---
    // Filter bulan pada pengeluaran per anggaran
    // --- Data Keuangan per Anggaran ---
if ($dateRangeType === 'custom') {
    $sql = "SELECT kode_anggaran, DATE_FORMAT(tanggal, '%Y-%m') as ym, SUM(jumlah) as c
            FROM pengeluaran
            WHERE tanggal BETWEEN ? AND ?
            GROUP BY kode_anggaran, ym ORDER BY kode_anggaran, ym";
    $params = [$startDate, $endDate];
} elseif (!is_null($selectedMonth)) {
    $sql = "SELECT kode_anggaran, DATE_FORMAT(tanggal, '%Y-%m') as ym, SUM(jumlah) as c
            FROM pengeluaran
            WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ?
            GROUP BY kode_anggaran, ym ORDER BY kode_anggaran, ym";
    $params = [$selectedYear, $selectedMonth];
} else {
    $sql = "SELECT kode_anggaran, DATE_FORMAT(tanggal, '%Y-%m') as ym, SUM(jumlah) as c
            FROM pengeluaran
            WHERE YEAR(tanggal) = ?
            GROUP BY kode_anggaran, ym ORDER BY kode_anggaran, ym";
    $params = [$selectedYear];
}
$stmt = executeQuery($pdo, $sql, $params);
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
    foreach ($pengeluaranMap as $kodeAnggaran => $monthlyData) {
        $values = [];
        foreach ($labelsKeuangan as $yearMonth) {
            $values[] = $monthlyData[$yearMonth] ?? 0;
        }

        $datasetsKeuangan[] = [
            'label' => $kodeAnggaran,
            'data' => $values
        ];
    }

    // --- Data Sisa Anggaran per Kode ---
    // Batasi pengeluaran menurut bulan yang dipilih (anggaran tetap per tahun)
    if ($dateRangeType === 'custom') {
    $sql = "SELECT 
                a.kode_anggaran,
                a.nama_anggaran,
                a.total_anggaran,
                COALESCE(SUM(p.jumlah), 0) as total_pengeluaran,
                (a.total_anggaran - COALESCE(SUM(p.jumlah), 0)) as sisa_anggaran
            FROM anggaran a
            LEFT JOIN pengeluaran p ON a.kode_anggaran = p.kode_anggaran 
                AND p.tanggal BETWEEN ? AND ?
            WHERE a.tahun = YEAR(?)
            GROUP BY a.kode_anggaran, a.nama_anggaran, a.total_anggaran
            ORDER BY a.kode_anggaran";
    $params = [$startDate, $endDate, $startDate];
} elseif (!is_null($selectedMonth)) {
    $sql = "SELECT 
                a.kode_anggaran,
                a.nama_anggaran,
                a.total_anggaran,
                COALESCE(SUM(p.jumlah), 0) as total_pengeluaran,
                (a.total_anggaran - COALESCE(SUM(p.jumlah), 0)) as sisa_anggaran
            FROM anggaran a
            LEFT JOIN pengeluaran p ON a.kode_anggaran = p.kode_anggaran 
                AND YEAR(p.tanggal) = ? AND MONTH(p.tanggal) = ?
            WHERE a.tahun = ?
            GROUP BY a.kode_anggaran, a.nama_anggaran, a.total_anggaran
            ORDER BY a.kode_anggaran";
    $params = [$selectedYear, $selectedMonth, $selectedYear];
} else {
    $sql = "SELECT 
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
            ORDER BY a.kode_anggaran";
    $params = [$selectedYear, $selectedYear];
}
$stmt = executeQuery($pdo, $sql, $params);
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

// --- Data Media Pengajuan Permohonan ---
if ($dateRangeType === 'custom') {
    $stmt = executeQuery(
        $pdo,
        "SELECT media_pengajuan, COUNT(*) as jumlah
         FROM permohonan 
         WHERE tgl_pengajuan BETWEEN ? AND ? AND tempat_permohonan != 'JAKARTA'
         GROUP BY media_pengajuan
         ORDER BY jumlah DESC",
        [$startDate, $endDate]
    );
} elseif (!is_null($selectedMonth)) {
    $stmt = executeQuery(
        $pdo,
        "SELECT media_pengajuan, COUNT(*) as jumlah
         FROM permohonan 
         WHERE YEAR(tgl_pengajuan) = ? AND MONTH(tgl_pengajuan) = ? AND tempat_permohonan != 'JAKARTA'
         GROUP BY media_pengajuan
         ORDER BY jumlah DESC",
        [$selectedYear, $selectedMonth]
    );
} else {
    $stmt = executeQuery(
        $pdo,
        "SELECT media_pengajuan, COUNT(*) as jumlah
         FROM permohonan 
         WHERE YEAR(tgl_pengajuan) = ? AND tempat_permohonan != 'JAKARTA'
         GROUP BY media_pengajuan
         ORDER BY jumlah DESC",
        [$selectedYear]
    );
}

 
$mediaPengajuanData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Format data media pengajuan
$mediaPengajuanChart = [
    'labels' => [],
    'data' => [],
    'total' => 0
];

if (!empty($mediaPengajuanData)) {
    foreach ($mediaPengajuanData as $media => $jumlah) {
        $mediaPengajuanChart['labels'][] = $media;
        $mediaPengajuanChart['data'][] = (int)$jumlah;
        $mediaPengajuanChart['total'] += (int)$jumlah;
    }
}

// --- Data Media Pengajuan Penelaahan ---
if ($dateRangeType === 'custom') {
    $stmt = executeQuery(
        $pdo,
        "SELECT p.media_pengajuan, COUNT(*) as jumlah
         FROM penelaahan pn
         JOIN permohonan p ON pn.no_reg_medan = p.no_reg_medan
         WHERE pn.tanggal_dispo BETWEEN ? AND ?
         GROUP BY p.media_pengajuan
         ORDER BY jumlah DESC",
        [$startDate, $endDate]
    );
} elseif (!is_null($selectedMonth)) {
    $stmt = executeQuery(
        $pdo,
        "SELECT p.media_pengajuan, COUNT(*) as jumlah
         FROM penelaahan pn
         JOIN permohonan p ON pn.no_reg_medan = p.no_reg_medan
         WHERE YEAR(pn.tanggal_dispo) = ? AND MONTH(pn.tanggal_dispo) = ?
         GROUP BY p.media_pengajuan
         ORDER BY jumlah DESC",
        [$selectedYear, $selectedMonth]
    );
} else {
    $stmt = executeQuery(
        $pdo,
        "SELECT p.media_pengajuan, COUNT(*) as jumlah
         FROM penelaahan pn
         JOIN permohonan p ON pn.no_reg_medan = p.no_reg_medan
         WHERE YEAR(pn.tanggal_dispo) = ?
         GROUP BY p.media_pengajuan
         ORDER BY jumlah DESC",
        [$selectedYear]
    );
}


$mediaPengajuanPenelaahanRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$mediaPengajuanPenelaahanData = [];
$totalPenelaahanMedia = 0;   // ← total khusus penelaahan

foreach ($mediaPengajuanPenelaahanRows as $row) {
    $media = $row['media_pengajuan'];
    $jumlah = (int)$row['jumlah'];
    $mediaPengajuanPenelaahanData[$media] = $jumlah;
    $totalPenelaahanMedia += $jumlah;
}

// Format data media pengajuan penelaahan
$mediaPengajuanPenelaahanChart = [
    'labels' => array_keys($mediaPengajuanPenelaahanData),
    'data'   => array_values($mediaPengajuanPenelaahanData),
    'total'  => $totalPenelaahanMedia,   // ← kirim ke frontend
];

    
    // --- Data Beban Kerja Pegawai ---
if ($dateRangeType === 'custom') {
    $stmt = executeQuery(
        $pdo,
        "SELECT 
            pg.id_pegawai,
            pg.nama_pegawai,
            COUNT(DISTINCT pm.no_reg_medan) as jumlah_permohonan,
            COUNT(DISTINCT pn.no_registrasi) as jumlah_penelaahan, 
            COUNT(DISTINCT ly.no_kep_smpl) as jumlah_layanan
        FROM pegawai pg
        LEFT JOIN permohonan pm ON pg.id_pegawai = pm.id_pegawai 
            AND pm.tgl_pengajuan BETWEEN ? AND ? 
            AND pm.tempat_permohonan != 'JAKARTA'  -- FILTER BARU
        LEFT JOIN penelaahan pn ON pg.id_pegawai = pn.id_pegawai AND pn.tanggal_dispo BETWEEN ? AND ?
        LEFT JOIN layanan ly ON pg.id_pegawai = ly.id_pegawai 
            AND (COALESCE(ly.tanggal_disposisi, ly.tgl_mulai_layanan) BETWEEN ? AND ?)
        GROUP BY pg.id_pegawai, pg.nama_pegawai
        ORDER BY pg.nama_pegawai",
        [$startDate, $endDate, $startDate, $endDate, $startDate, $endDate]
    );
} elseif (!is_null($selectedMonth)) {
    $stmt = executeQuery(
        $pdo,
        "SELECT 
            pg.id_pegawai,
            pg.nama_pegawai,
            COUNT(DISTINCT pm.no_reg_medan) as jumlah_permohonan,
            COUNT(DISTINCT pn.no_registrasi) as jumlah_penelaahan, 
            COUNT(DISTINCT ly.no_kep_smpl) as jumlah_layanan
        FROM pegawai pg
        LEFT JOIN permohonan pm ON pg.id_pegawai = pm.id_pegawai 
            AND YEAR(pm.tgl_pengajuan) = ? AND MONTH(pm.tgl_pengajuan) = ?
            AND pm.tempat_permohonan != 'JAKARTA'  -- FILTER BARU
        LEFT JOIN penelaahan pn ON pg.id_pegawai = pn.id_pegawai 
            AND YEAR(pn.tanggal_dispo) = ? AND MONTH(pn.tanggal_dispo) = ?
        LEFT JOIN layanan ly ON pg.id_pegawai = ly.id_pegawai AND (
            (YEAR(ly.tanggal_disposisi) = ? AND MONTH(ly.tanggal_disposisi) = ?)
            OR (ly.tanggal_disposisi IS NULL AND YEAR(ly.tgl_mulai_layanan) = ? AND MONTH(ly.tgl_mulai_layanan) = ?)
        )
        GROUP BY pg.id_pegawai, pg.nama_pegawai
        ORDER BY pg.nama_pegawai",
        [$selectedYear, $selectedMonth, $selectedYear, $selectedMonth, $selectedYear, $selectedMonth, $selectedYear, $selectedMonth]
    );
} else {
    $stmt = executeQuery(
        $pdo,
        "SELECT 
            pg.id_pegawai,
            pg.nama_pegawai,
            COUNT(DISTINCT pm.no_reg_medan) as jumlah_permohonan,
            COUNT(DISTINCT pn.no_registrasi) as jumlah_penelaahan, 
            COUNT(DISTINCT ly.no_kep_smpl) as jumlah_layanan
        FROM pegawai pg
        LEFT JOIN permohonan pm ON pg.id_pegawai = pm.id_pegawai 
            AND YEAR(pm.tgl_pengajuan) = ? 
            AND pm.tempat_permohonan != 'JAKARTA'  -- FILTER BARU
        LEFT JOIN penelaahan pn ON pg.id_pegawai = pn.id_pegawai AND YEAR(pn.tanggal_dispo) = ?
        LEFT JOIN layanan ly ON pg.id_pegawai = ly.id_pegawai 
            AND (YEAR(ly.tanggal_disposisi) = ? OR (ly.tanggal_disposisi IS NULL AND YEAR(ly.tgl_mulai_layanan) = ?))
        GROUP BY pg.id_pegawai, pg.nama_pegawai
        ORDER BY pg.nama_pegawai",
        [$selectedYear, $selectedYear, $selectedYear, $selectedYear]
    );
}
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
    // Tambah filter bulan
    list($wProv, $pProv) = ymWhere('tgl_pengajuan', $selectedYear, $selectedMonth);
    $stmt = executeQuery(
        $pdo,
        "SELECT provinsi, COUNT(*) as jumlah
         FROM permohonan
         WHERE provinsi IN ('ACEH', 'SUMATERA UTARA', 'SUMATERA BARAT') 
         AND $wProv
         GROUP BY provinsi",
        $pProv
    );
    $provinsiData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Mapping nama provinsi
    $provinsiMapping = [
        'ACEH' => 'Aceh',
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

    // --- Data Jenis Kelamin Permohonan ---
    // --- Data Jenis Kelamin Permohonan ---
list($wJK, $pJK) = ymWhere('tgl_pengajuan', $selectedYear, $selectedMonth, $startDate, $endDate, $dateRangeType);
$stmt = executeQuery(
    $pdo,
    "SELECT jenis_kelamin, COUNT(*) as jumlah
     FROM permohonan 
     WHERE $wJK AND tempat_permohonan != 'JAKARTA'
     GROUP BY jenis_kelamin",
    $pJK
);
$genderPermohonan = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Definisikan gender labels
$genderLabels = [
    'Laki-laki' => 'Laki-laki',
    'Perempuan' => 'Perempuan'
    // Tambahkan kode lain jika ada
];

// Format data gender permohonan
$genderDataPermohonan = [
    'labels' => [],
    'data' => [],
    'total' => 0
];

foreach ($genderLabels as $code => $label) {
    $count = $genderPermohonan[$code] ?? 0;
    $genderDataPermohonan['labels'][] = $label;
    $genderDataPermohonan['data'][] = $count;
    $genderDataPermohonan['total'] += $count;
}

// --- Data Jenis Kelamin Layanan ---
if ($dateRangeType === 'custom') {
    $stmt = executeQuery(
        $pdo,
        "SELECT p.jenis_kelamin, COUNT(*) as jumlah
         FROM layanan l
         JOIN permohonan p ON l.no_reg_medan = p.no_reg_medan
         WHERE COALESCE(l.tanggal_disposisi, l.tgl_mulai_layanan) BETWEEN ? AND ?
         GROUP BY p.jenis_kelamin",
        [$startDate, $endDate]
    );
} elseif (!is_null($selectedMonth)) {
    $stmt = executeQuery(
        $pdo,
        "SELECT p.jenis_kelamin, COUNT(*) as jumlah
         FROM layanan l
         JOIN permohonan p ON l.no_reg_medan = p.no_reg_medan
         WHERE ( (YEAR(l.tgl_mulai_layanan) = ? AND MONTH(l.tgl_mulai_layanan) = ?)
             OR (l.tanggal_disposisi IS NOT NULL AND YEAR(l.tanggal_disposisi) = ? AND MONTH(l.tanggal_disposisi) = ?) )
         GROUP BY p.jenis_kelamin",
        [$selectedYear, $selectedMonth, $selectedYear, $selectedMonth]
    );
} else {
    $stmt = executeQuery(
        $pdo,
        "SELECT p.jenis_kelamin, COUNT(*) as jumlah
         FROM layanan l
         JOIN permohonan p ON l.no_reg_medan = p.no_reg_medan
         WHERE (YEAR(l.tgl_mulai_layanan) = ? OR (l.tanggal_disposisi IS NOT NULL AND YEAR(l.tanggal_disposisi) = ?))
         GROUP BY p.jenis_kelamin",
        [$selectedYear, $selectedYear]
    );
}
$genderLayanan = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);



    $genderDataLayanan = [
        'labels' => [],
        'data' => [],
        'total' => 0
    ];

    foreach ($genderLabels as $code => $label) {
        $count = $genderLayanan[$code] ?? 0;
        $genderDataLayanan['labels'][] = $label;
        $genderDataLayanan['data'][] = $count;
        $genderDataLayanan['total'] += $count;
    }

// --- Data Jenis Kelamin Penelaahan ---
list($wJKPenelaahan, $pJKPenelaahan) = ymWhere('pn.tanggal_dispo', $selectedYear, $selectedMonth, $startDate, $endDate, $dateRangeType);
$stmt = executeQuery(
    $pdo,
    "SELECT p.jenis_kelamin, COUNT(*) as jumlah
     FROM penelaahan pn
     JOIN permohonan p ON pn.no_reg_medan = p.no_reg_medan
     WHERE $wJKPenelaahan
     GROUP BY p.jenis_kelamin",
    $pJKPenelaahan
);
$genderPenelaahan = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Format data gender penelaahan
$genderDataPenelaahan = [
    'labels' => [],
    'data' => [],
    'total' => 0
];

foreach ($genderLabels as $code => $label) {
    $count = $genderPenelaahan[$code] ?? 0;
    $genderDataPenelaahan['labels'][] = $label;
    $genderDataPenelaahan['data'][] = $count;
    $genderDataPenelaahan['total'] += $count;
}

    // --- Data Jenis Tindak Pidana ---
    // Daftar semua nilai enum yang mungkin (hardcode berdasarkan struktur database)
$allTindakPidana = [
    'KSA', 'PENYIKSAAN', 'KORUPSI', 'TPPO', 'PHB', 'TERORISME', 
    'KS', 'PENGANIAYAAN BERAT', 'NARKOTIKA', 'TPL', 'TPPU', 'PENGANIAYAAN'
];

$tindakPidanaChart = [
    'labels' => [],
    'data' => [],
    'total' => 0
];

// Query untuk setiap jenis tindak pidana
foreach ($allTindakPidana as $jenis) {
    if ($dateRangeType === 'custom') {
        $stmt = executeQuery(
            $pdo,
            "SELECT COUNT(*) as jumlah 
             FROM permohonan 
             WHERE tindak_pidana = ? 
             AND tgl_pengajuan BETWEEN ? AND ?
             AND tempat_permohonan != 'JAKARTA'",
            [$jenis, $startDate, $endDate]
        );
    } elseif (!is_null($selectedMonth)) {
        $stmt = executeQuery(
            $pdo,
            "SELECT COUNT(*) as jumlah 
             FROM permohonan 
             WHERE tindak_pidana = ? 
             AND YEAR(tgl_pengajuan) = ? AND MONTH(tgl_pengajuan) = ?
             AND tempat_permohonan != 'JAKARTA'",
            [$jenis, $selectedYear, $selectedMonth]
        );
    } else {
        $stmt = executeQuery(
            $pdo,
            "SELECT COUNT(*) as jumlah 
             FROM permohonan 
             WHERE tindak_pidana = ? 
             AND YEAR(tgl_pengajuan) = ? 
             AND tempat_permohonan != 'JAKARTA'",
            [$jenis, $selectedYear]
        );
    }
    
    $jumlah = (int)$stmt->fetchColumn();
    
    $tindakPidanaChart['labels'][] = $jenis;
    $tindakPidanaChart['data'][] = $jumlah;
    $tindakPidanaChart['total'] += $jumlah;
}

    // Urutkan berdasarkan jumlah descending
    array_multisort($tindakPidanaChart['data'], SORT_DESC, $tindakPidanaChart['labels']);

    // --- Data Status Hukum ---
if ($dateRangeType === 'custom') {
    $stmt = executeQuery(
        $pdo,
        "SELECT status_hukum, COUNT(*) as jumlah
         FROM permohonan 
         WHERE tgl_pengajuan BETWEEN ? AND ? AND tempat_permohonan != 'JAKARTA'
         GROUP BY status_hukum",
        [$startDate, $endDate]
    );
} elseif (!is_null($selectedMonth)) {
    $stmt = executeQuery(
        $pdo,
        "SELECT status_hukum, COUNT(*) as jumlah
         FROM permohonan 
         WHERE YEAR(tgl_pengajuan) = ? AND MONTH(tgl_pengajuan) = ? AND tempat_permohonan != 'JAKARTA'
         GROUP BY status_hukum",
        [$selectedYear, $selectedMonth]
    );
} else {
    $stmt = executeQuery(
        $pdo,
        "SELECT status_hukum, COUNT(*) as jumlah
         FROM permohonan 
         WHERE YEAR(tgl_pengajuan) = ? AND tempat_permohonan != 'JAKARTA'
         GROUP BY status_hukum",
        [$selectedYear]
    );
}
$statusHukumData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Format data status hukum
    $statusHukumChart = [
        'labels' => [],
        'data' => [],
        'total' => 0
    ];

    if (!empty($statusHukumData)) {
        foreach ($statusHukumData as $status => $jumlah) {
            $statusHukumChart['labels'][] = $status;
            $statusHukumChart['data'][] = (int)$jumlah;
            $statusHukumChart['total'] += (int)$jumlah;
        }
    }

    // --- Data Status Hukum Penelaahan ---
if ($dateRangeType === 'custom') {
    $stmt = executeQuery(
        $pdo,
        "SELECT p.status_hukum, COUNT(*) as jumlah
         FROM penelaahan pn
         JOIN permohonan p ON pn.no_reg_medan = p.no_reg_medan
         WHERE pn.tanggal_dispo BETWEEN ? AND ? AND p.tempat_permohonan != 'JAKARTA'
         GROUP BY p.status_hukum",
        [$startDate, $endDate]
    );
} elseif (!is_null($selectedMonth)) {
    $stmt = executeQuery(
        $pdo,
        "SELECT p.status_hukum, COUNT(*) as jumlah
         FROM penelaahan pn
         JOIN permohonan p ON pn.no_reg_medan = p.no_reg_medan
         WHERE YEAR(pn.tanggal_dispo) = ? AND MONTH(pn.tanggal_dispo) = ? AND p.tempat_permohonan != 'JAKARTA'
         GROUP BY p.status_hukum",
        [$selectedYear, $selectedMonth]
    );
} else {
    $stmt = executeQuery(
        $pdo,
        "SELECT p.status_hukum, COUNT(*) as jumlah
         FROM penelaahan pn
         JOIN permohonan p ON pn.no_reg_medan = p.no_reg_medan
         WHERE YEAR(pn.tanggal_dispo) = ? AND p.tempat_permohonan != 'JAKARTA'
         GROUP BY p.status_hukum",
        [$selectedYear]
    );
}
$statusHukumPenelaahan = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // --- Data Jenis Perlindungan Permohonan ---
    // Tambah filter bulan (via tgl_pengajuan di tabel permohonan)
    if ($dateRangeType === 'custom') {
    $stmt = executeQuery(
        $pdo,
        "SELECT jp.kategori, jp.sub_pilihan, COUNT(pp.id) as jumlah
         FROM permohonan_perlindungan pp
         JOIN jenis_perlindungan jp ON pp.id_perlindungan = jp.id
         JOIN permohonan p ON pp.no_reg_medan = p.no_reg_medan
         WHERE p.tgl_pengajuan BETWEEN ? AND ? AND p.tempat_permohonan != 'JAKARTA'
         GROUP BY jp.kategori, jp.sub_pilihan
         ORDER BY jp.kategori, jumlah DESC",
        [$startDate, $endDate]
    );
} elseif (!is_null($selectedMonth)) {
    $stmt = executeQuery(
        $pdo,
        "SELECT jp.kategori, jp.sub_pilihan, COUNT(pp.id) as jumlah
         FROM permohonan_perlindungan pp
         JOIN jenis_perlindungan jp ON pp.id_perlindungan = jp.id
         JOIN permohonan p ON pp.no_reg_medan = p.no_reg_medan
         WHERE YEAR(p.tgl_pengajuan) = ? AND MONTH(p.tgl_pengajuan) = ? AND p.tempat_permohonan != 'JAKARTA'
         GROUP BY jp.kategori, jp.sub_pilihan
         ORDER BY jp.kategori, jumlah DESC",
        [$selectedYear, $selectedMonth]
    );
} else {
    $stmt = executeQuery(
        $pdo,
        "SELECT jp.kategori, jp.sub_pilihan, COUNT(pp.id) as jumlah
         FROM permohonan_perlindungan pp
         JOIN jenis_perlindungan jp ON pp.id_perlindungan = jp.id
         JOIN permohonan p ON pp.no_reg_medan = p.no_reg_medan
         WHERE YEAR(p.tgl_pengajuan) = ? AND p.tempat_permohonan != 'JAKARTA'
         GROUP BY jp.kategori, jp.sub_pilihan
         ORDER BY jp.kategori, jumlah DESC",
        [$selectedYear]
    );
}
$jenisPerlindunganPermohonan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format data jenis perlindungan permohonan
$perlindunganPermohonanChart = [
    'kategori' => [],
    'sub_pilihan' => [],
    'data' => [],
    'total' => 0
];

foreach ($jenisPerlindunganPermohonan as $item) {
    $perlindunganPermohonanChart['kategori'][] = $item['kategori'];
    $perlindunganPermohonanChart['sub_pilihan'][] = $item['sub_pilihan'];
    $perlindunganPermohonanChart['data'][] = (int)$item['jumlah'];
    $perlindunganPermohonanChart['total'] += (int)$item['jumlah'];
}

    // --- Data Jenis Perlindungan Layanan ---
    if ($dateRangeType === 'custom') {
    $stmt = executeQuery(
        $pdo,
        "SELECT jp.kategori, jp.sub_pilihan, COUNT(lp.id) as jumlah
         FROM layanan_perlindungan lp
         JOIN jenis_perlindungan jp ON lp.id_perlindungan = jp.id
         JOIN layanan l ON lp.no_kep_smpl = l.no_kep_smpl
         WHERE COALESCE(l.tanggal_disposisi, l.tgl_mulai_layanan) BETWEEN ? AND ?
         GROUP BY jp.kategori, jp.sub_pilihan
         ORDER BY jp.kategori, jumlah DESC",
        [$startDate, $endDate]
    );
} elseif (!is_null($selectedMonth)) {
    $stmt = executeQuery(
        $pdo,
        "SELECT jp.kategori, jp.sub_pilihan, COUNT(lp.id) as jumlah
         FROM layanan_perlindungan lp
         JOIN jenis_perlindungan jp ON lp.id_perlindungan = jp.id
         JOIN layanan l ON lp.no_kep_smpl = l.no_kep_smpl
         WHERE ( (YEAR(l.tgl_mulai_layanan) = ? AND MONTH(l.tgl_mulai_layanan) = ?)
             OR (l.tanggal_disposisi IS NOT NULL AND YEAR(l.tanggal_disposisi) = ? AND MONTH(l.tanggal_disposisi) = ?) )
         GROUP BY jp.kategori, jp.sub_pilihan
         ORDER BY jp.kategori, jumlah DESC",
        [$selectedYear, $selectedMonth, $selectedYear, $selectedMonth]
    );
} else {
    $stmt = executeQuery(
        $pdo,
        "SELECT jp.kategori, jp.sub_pilihan, COUNT(lp.id) as jumlah
         FROM layanan_perlindungan lp
         JOIN jenis_perlindungan jp ON lp.id_perlindungan = jp.id
         JOIN layanan l ON lp.no_kep_smpl = l.no_kep_smpl
         WHERE (YEAR(l.tgl_mulai_layanan) = ? OR (l.tanggal_disposisi IS NOT NULL AND YEAR(l.tanggal_disposisi) = ?))
         GROUP BY jp.kategori, jp.sub_pilihan
         ORDER BY jp.kategori, jumlah DESC",
        [$selectedYear, $selectedYear]
    );
}
$jenisPerlindunganLayanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data jenis perlindungan layanan
    $perlindunganLayananChart = [
        'kategori' => [],
        'sub_pilihan' => [],
        'data' => [],
        'total' => 0
    ];

    foreach ($jenisPerlindunganLayanan as $item) {
        $perlindunganLayananChart['kategori'][] = $item['kategori'];
        $perlindunganLayananChart['sub_pilihan'][] = $item['sub_pilihan'];
        $perlindunganLayananChart['data'][] = (int)$item['jumlah'];
        $perlindunganLayananChart['total'] += (int)$item['jumlah'];
    }

    // --- Data Gabungan untuk Chart Perbandingan ---
    // Ambil semua jenis perlindungan yang ada
    $stmt = executeQuery($pdo, "SELECT id, kategori, sub_pilihan FROM jenis_perlindungan ORDER BY kategori, sub_pilihan");
    $allJenisPerlindungan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $perlindunganComparisonChart = [
        'labels' => [],
        'permohonan' => [],
        'layanan' => []
    ];

    // Mapping data permohonan
    $permohonanMap = [];
    foreach ($jenisPerlindunganPermohonan as $item) {
        $key = $item['kategori'] . ' - ' . $item['sub_pilihan'];
        $permohonanMap[$key] = (int)$item['jumlah'];
    }

    // Mapping data layanan
    $layananMap = [];
    foreach ($jenisPerlindunganLayanan as $item) {
        $key = $item['kategori'] . ' - ' . $item['sub_pilihan'];
        $layananMap[$key] = (int)$item['jumlah'];
    }

    // Gabungkan semua jenis perlindungan
    foreach ($allJenisPerlindungan as $jenis) {
        $key = $jenis['kategori'] . ' - ' . $jenis['sub_pilihan'];
        $perlindunganComparisonChart['labels'][] = $key;
        $perlindunganComparisonChart['permohonan'][] = $permohonanMap[$key] ?? 0;
        $perlindunganComparisonChart['layanan'][] = $layananMap[$key] ?? 0;
    }


    // --- Data untuk Peta Provinsi ---
// --- Data untuk Peta Provinsi ---
list($wProv, $pProv) = ymWhere('tgl_pengajuan', $selectedYear, $selectedMonth, $startDate, $endDate, $dateRangeType);
// Query untuk data provinsi 
$stmt = executeQuery(
    $pdo,
    "SELECT provinsi, COUNT(*) as jumlah
     FROM permohonan
     WHERE provinsi IN ('ACEH', 'SUMATERA UTARA', 'SUMATERA BARAT') 
     AND $wProv
     AND (tempat_permohonan IS NULL OR tempat_permohonan != 'JAKARTA')
     GROUP BY provinsi",
    $pProv
);
$provinsiData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// --- Data Detail Permohonan per Kabupaten/Kota ---
$stmt = executeQuery(
    $pdo,
    "SELECT 
        provinsi,
        kab_kot_locus,
        COUNT(*) as jumlah
     FROM permohonan
     WHERE $wProv 
     AND kab_kot_locus IS NOT NULL 
     AND kab_kot_locus != ''
     AND (tempat_permohonan IS NULL OR tempat_permohonan != 'JAKARTA')
     GROUP BY provinsi, kab_kot_locus
     ORDER BY provinsi, jumlah DESC",
    $pProv
);
$kabupatenData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format data kabupaten
$kabupatenByProvinsi = [];
foreach ($kabupatenData as $row) {
    $provinsi = $row['provinsi'];
    $kabupaten = $row['kab_kot_locus'];
    $jumlah = (int)$row['jumlah'];
    
    if (!isset($kabupatenByProvinsi[$provinsi])) {
        $kabupatenByProvinsi[$provinsi] = [];
    }
    
    $kabupatenByProvinsi[$provinsi][] = [
        'kabupaten' => $kabupaten,
        'jumlah' => $jumlah
    ];
}

// Hitung total semua provinsi untuk persentase
$totalSemuaProvinsi = array_sum($provinsiData);

    // --- Data Aktivitas Terbaru ---
    if ($dateRangeType === 'custom') {
    $stmt = executeQuery(
        $pdo,
        "(SELECT 
            'permohonan' as jenis,
            no_reg_medan as nomor,
            tgl_pengajuan as tanggal,
            nama_pemohon,
            'Permohonan baru diterima' as aktivitas,
            'blue' as warna,
            'fa-file-import' as icon
        FROM permohonan 
        WHERE tgl_pengajuan BETWEEN ? AND ? AND tempat_permohonan != 'JAKARTA'
        ORDER BY tgl_pengajuan DESC 
        LIMIT 5)
        UNION ALL
        (SELECT 
            'penelaahan' as jenis,
            no_registrasi as nomor,
            tanggal_dispo as tanggal,
            '' as nama_pemohon,
            'Penelaahan selesai' as aktivitas,
            'green' as warna,
            'fa-check-circle' as icon
        FROM penelaahan 
        WHERE tanggal_dispo BETWEEN ? AND ?
        ORDER BY tanggal_dispo DESC 
        LIMIT 5)
        UNION ALL
        (SELECT 
            'pengeluaran' as jenis,
            nomor_kuintasi as nomor,
            tanggal,
            '' as nama_pemohon,
            'Pengeluaran baru dicatat' as aktivitas,
            'amber' as warna,
            'fa-coins' as icon
        FROM pengeluaran 
        WHERE tanggal BETWEEN ? AND ?
        ORDER BY tanggal DESC 
        LIMIT 5)
        ORDER BY tanggal DESC 
        LIMIT 5",
        [$startDate, $endDate, $startDate, $endDate, $startDate, $endDate]
    );
}
    elseif (!is_null($selectedMonth)) {
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
                WHERE YEAR(tgl_pengajuan) = ? AND tempat_permohonan != 'JAKARTA'
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
    } else {
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
                WHERE YEAR(tgl_pengajuan) = ? AND MONTH(tgl_pengajuan) = ? AND tempat_permohonan != 'JAKARTA'
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
                WHERE YEAR(tanggal_dispo) = ? AND MONTH(tanggal_dispo) = ?
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
                WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ?
                ORDER BY tanggal DESC 
                LIMIT 5
            )
            ORDER BY tanggal DESC 
            LIMIT 5",
            [$selectedYear, $selectedMonth, $selectedYear, $selectedMonth, $selectedYear, $selectedMonth]
        );
    }
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

    // --- Hitung persentase perubahan dari bulan sebelumnya ---
    function calculateMonthOverMonthChange($pdo, $monthlyData, $selectedYear) {
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        
        if ($selectedYear != $currentYear) {
            $currentMonth = 12;
        }
        
        $previousMonth = $currentMonth - 1;
        $previousYear = $selectedYear;
        
        if ($previousMonth < 1) {
            $previousMonth = 12;
            $previousYear = $selectedYear - 1;
        }
        
        $currentValue = 0;
        $currentMonthKey = $selectedYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT);
        foreach ($monthlyData as $data) {
            if ($data['ym'] == $currentMonthKey) {
                $currentValue = (float)$data['c'];
                break;
            }
        }
        
        $previousValue = 0;
        $previousMonthKey = $previousYear . '-' . str_pad($previousMonth, 2, '0', STR_PAD_LEFT);
        
        if ($previousYear != $selectedYear) {
            $stmt = executeQuery(
                $pdo,
                "SELECT DATE_FORMAT(tgl_pengajuan, '%Y-%m') as ym, COUNT(*) as c 
                 FROM permohonan 
                 WHERE YEAR(tgl_pengajuan) = ?
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
        
        if ($previousValue == 0) {
            return $currentValue > 0 ? 100 : 0;
        }
        
        return (($currentValue - $previousValue) / $previousValue) * 100;
    }

    // Hitung persentase perubahan untuk setiap metrik
    $permohonanChange = calculateMonthOverMonthChange($pdo, $permohonanMonthly, $selectedYear);
    $penelaahanChange = calculateMonthOverMonthChange($pdo, $penelaahanMonthly, $selectedYear);
    $layananChange = calculateMonthOverMonthChange($pdo, $layananMonthly, $selectedYear);
    $pengeluaranChange = calculateMonthOverMonthChange($pdo, $pengeluaranMonthly, $selectedYear);

    // Tambahkan ke counts
    $counts['permohonan_change'] = round($permohonanChange, 1);
    $counts['penelaahan_change'] = round($penelaahanChange, 1);
    $counts['layanan_change'] = round($layananChange, 1);
    $counts['pengeluaran_change'] = round($pengeluaranChange, 1);

    // Jika bulan dipilih, nolkan MoM change agar tidak membingungkan
    if (!is_null($selectedMonth)) {
        $counts['permohonan_change'] = 0.0;
        $counts['penelaahan_change'] = 0.0;
        $counts['layanan_change'] = 0.0;
        $counts['pengeluaran_change'] = 0.0;
    }

    $response = [
        'success' => true,
    'date_range_type' => $dateRangeType,
    'selectedYear' => $selectedYear,
    'selectedMonth' => $selectedMonth,
    'start_date' => $startDate,
    'end_date' => $endDate,
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
            'beban_kerja' => $bebanKerjaChart,
            'gender_distribution' => [ 
                'permohonan' => $genderDataPermohonan,
                'penelaahan' => $genderDataPenelaahan,
                'layanan' => $genderDataLayanan
            ],
            'tindak_pidana' => $tindakPidanaChart,
            'status_hukum' => $statusHukumChart,
            'status_hukum_penelaahan' => [
    'labels' => array_keys($statusHukumPenelaahan),
    'data' => array_values($statusHukumPenelaahan),
    'total' => array_sum($statusHukumPenelaahan)
],
            'perlindungan_permohonan' => $perlindunganPermohonanChart,
            'perlindungan_layanan' => $perlindunganLayananChart,
            'perlindungan_comparison' => $perlindunganComparisonChart,
            'media_pengajuan' => $mediaPengajuanChart,
            'media_pengajuan_penelaahan' => $mediaPengajuanPenelaahanChart,
            
        ],
        'map' => [
            'provinsi_counts' => $provinsiCounts,
            'provinsi_fillkeys' => $fillKeys,
            'kabupaten_detail' => $kabupatenByProvinsi, // DATA BARU
            'total_semua_provinsi' => $totalSemuaProvinsi // DATA BARU
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