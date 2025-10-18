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

    // --- Helper untuk membuat filter tahun/bulan + parameter ---
    function ymWhere($field, $selectedYear, $selectedMonth = null) {
        $clause = "YEAR($field) = ?";
        $params = [$selectedYear];
        if (!is_null($selectedMonth)) {
            $clause .= " AND MONTH($field) = ?";
            $params[] = $selectedMonth;
        }
        return [$clause, $params];
    }

    // --- Hitung ringkasan data utama ---
    // HANYA PERMOHONAN YANG DIFILTER (tambah kondisi tempat_permohonan != 'JAKARTA')
    // Tambahkan filter bulan jika ada
    list($w, $p) = ymWhere('tgl_pengajuan', $selectedYear, $selectedMonth);
    $permohonanCount = getCount(
        $pdo,
        "SELECT COUNT(*) FROM permohonan WHERE $w AND tempat_permohonan != 'JAKARTA'",
        $p
    );

    // Penelaahan + bulan
    list($w, $p) = ymWhere('tanggal_dispo', $selectedYear, $selectedMonth);
    $penelaahanCount = getCount(
        $pdo,
        "SELECT COUNT(*) FROM penelaahan WHERE $w",
        $p
    );

    // Layanan + bulan (dua kolom: tanggal_disposisi ATAU tgl_mulai_layanan)
    if (is_null($selectedMonth)) {
        $layananCount = getCount(
            $pdo,
            "SELECT COUNT(*) FROM layanan 
             WHERE (YEAR(tanggal_disposisi) = ? OR (tanggal_disposisi IS NULL AND YEAR(tgl_mulai_layanan) = ?))",
            [$selectedYear, $selectedYear]
        );
    } else {
        $layananCount = getCount(
            $pdo,
            "SELECT COUNT(*) FROM layanan 
             WHERE ( (YEAR(tanggal_disposisi) = ? AND MONTH(tanggal_disposisi) = ?)
                OR (tanggal_disposisi IS NULL AND YEAR(tgl_mulai_layanan) = ? AND MONTH(tgl_mulai_layanan) = ?) )",
            [$selectedYear, $selectedMonth, $selectedYear, $selectedMonth]
        );
    }

    // Pengeluaran + bulan
    list($w, $p) = ymWhere('tanggal', $selectedYear, $selectedMonth);
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

    // --- Fungsi untuk mengambil data bulanan untuk chart ---
    function fetchMonthlyData($pdo, $sqlBase, $selectedYear, $selectedMonth = null, $field = null) {
        try {
            $params = [$selectedYear];
            $sql = $sqlBase . " WHERE YEAR($field) = ?";
            if (!is_null($selectedMonth)) {
                $sql .= " AND MONTH($field) = ?";
                $params[] = $selectedMonth;
            }
            $sql .= " GROUP BY ym ORDER BY ym";
            $stmt = executeQuery($pdo, $sql, $params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching monthly data: " . $e->getMessage());
            return [];
        }
    }

    // Query untuk data bulanan
    // HANYA DATA PERMOHONAN YANG DIFILTER (tambah kondisi tempat_permohonan != 'JAKARTA')
    // Gunakan fetchMonthlyData baru
    $permohonanMonthly = fetchMonthlyData(
        $pdo,
        "SELECT DATE_FORMAT(tgl_pengajuan, '%Y-%m') as ym, COUNT(*) as c FROM permohonan",
        $selectedYear,
        $selectedMonth,
        'tgl_pengajuan'
    );

    $penelaahanMonthly = fetchMonthlyData(
        $pdo,
        "SELECT DATE_FORMAT(tanggal_dispo, '%Y-%m') as ym, COUNT(*) as c FROM penelaahan",
        $selectedYear,
        $selectedMonth,
        'tanggal_dispo'
    );

    $layananMonthly = (function() use($pdo, $selectedYear, $selectedMonth) {
        // Khusus layanan pakai COALESCE
        $params = [$selectedYear];
        $sql = "SELECT DATE_FORMAT(COALESCE(tanggal_disposisi, tgl_mulai_layanan), '%Y-%m') as ym, COUNT(*) as c 
                FROM layanan 
                WHERE YEAR(COALESCE(tanggal_disposisi, tgl_mulai_layanan)) = ?";
        if (!is_null($selectedMonth)) {
            $sql .= " AND MONTH(COALESCE(tanggal_disposisi, tgl_mulai_layanan)) = ?";
            $params[] = $selectedMonth;
        }
        $sql .= " GROUP BY ym ORDER BY ym";
        $stmt = executeQuery($pdo, $sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    })();

    $pengeluaranMonthly = fetchMonthlyData(
        $pdo,
        "SELECT DATE_FORMAT(tanggal, '%Y-%m') as ym, SUM(jumlah) as c FROM pengeluaran",
        $selectedYear,
        $selectedMonth,
        'tanggal'
    );

    // Isi data series untuk chart
    list($labelsPermohonan, $dataPermohonan) = fillMonthlySeries($permohonanMonthly, $selectedYear);
    list(, $dataPenelaahan) = fillMonthlySeries($penelaahanMonthly, $selectedYear);
    list(, $dataLayanan) = fillMonthlySeries($layananMonthly, $selectedYear);
    list($labelsPengeluaran, $dataPengeluaran) = fillMonthlySeries($pengeluaranMonthly, $selectedYear);

    // --- Data Keuangan per Anggaran ---
    // Filter bulan pada pengeluaran per anggaran
    $params = [$selectedYear];
    $sql = "SELECT kode_anggaran, DATE_FORMAT(tanggal, '%Y-%m') as ym, SUM(jumlah) as c
            FROM pengeluaran
            WHERE YEAR(tanggal) = ?";
    if (!is_null($selectedMonth)) {
        $sql .= " AND MONTH(tanggal) = ?";
        $params[] = $selectedMonth;
    }
    $sql .= " GROUP BY kode_anggaran, ym ORDER BY kode_anggaran, ym";
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
    $params = [$selectedYear, $selectedYear];
    $sql = "SELECT 
                a.kode_anggaran,
                a.nama_anggaran,
                a.total_anggaran,
                COALESCE(SUM(p.jumlah), 0) as total_pengeluaran,
                (a.total_anggaran - COALESCE(SUM(p.jumlah), 0)) as sisa_anggaran
            FROM anggaran a
            LEFT JOIN pengeluaran p ON a.kode_anggaran = p.kode_anggaran 
                AND YEAR(p.tanggal) = ?";
    if (!is_null($selectedMonth)) {
        $sql .= " AND MONTH(p.tanggal) = ?";
        $params = [$selectedYear, $selectedMonth, $selectedYear];
    }
    $sql .= " WHERE a.tahun = ?
            GROUP BY a.kode_anggaran, a.nama_anggaran, a.total_anggaran
            ORDER BY a.kode_anggaran";
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
// Tambah filter bulan
if (is_null($selectedMonth)) {
    $stmt = executeQuery(
        $pdo,
        "SELECT media_pengajuan, COUNT(*) as jumlah
         FROM permohonan 
         WHERE YEAR(tgl_pengajuan) = ? AND tempat_permohonan != 'JAKARTA'
         GROUP BY media_pengajuan
         ORDER BY jumlah DESC",
        [$selectedYear]
    );
} else {
    $stmt = executeQuery(
        $pdo,
        "SELECT media_pengajuan, COUNT(*) as jumlah
         FROM permohonan 
         WHERE YEAR(tgl_pengajuan) = ? AND MONTH(tgl_pengajuan) = ? AND tempat_permohonan != 'JAKARTA'
         GROUP BY media_pengajuan
         ORDER BY jumlah DESC",
        [$selectedYear, $selectedMonth]
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

    // --- Data Beban Kerja Pegawai ---
    // Tambahkan filter bulan di setiap join
    if (is_null($selectedMonth)) {
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
            LEFT JOIN permohonan pm ON pg.id_pegawai = pm.id_pegawai AND YEAR(pm.tgl_pengajuan) = ? AND MONTH(pm.tgl_pengajuan) = ?
            LEFT JOIN penelaahan pn ON pg.id_pegawai = pn.id_pegawai AND YEAR(pn.tanggal_dispo) = ? AND MONTH(pn.tanggal_dispo) = ?
            LEFT JOIN layanan ly ON pg.id_pegawai = ly.id_pegawai AND (
                (YEAR(ly.tanggal_disposisi) = ? AND MONTH(ly.tanggal_disposisi) = ?)
                OR (ly.tanggal_disposisi IS NULL AND YEAR(ly.tgl_mulai_layanan) = ? AND MONTH(ly.tgl_mulai_layanan) = ?)
            )
            GROUP BY pg.id_pegawai, pg.nama_pegawai
            ORDER BY pg.nama_pegawai",
            [$selectedYear, $selectedMonth, $selectedYear, $selectedMonth, $selectedYear, $selectedMonth, $selectedYear, $selectedMonth]
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
    // Tambah filter bulan
    list($wJK, $pJK) = ymWhere('tgl_pengajuan', $selectedYear, $selectedMonth);
    array_push($pJK, ); // no-op to keep syntax highlighters calm
    $stmt = executeQuery(
        $pdo,
        "SELECT jenis_kelamin, COUNT(*) as jumlah
         FROM permohonan 
         WHERE $wJK AND tempat_permohonan != 'JAKARTA'
         GROUP BY jenis_kelamin",
        $pJK
    );
    $genderPermohonan = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Format data jenis kelamin
    $genderLabels = ['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan'];
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
    // Tambah filter bulan di layanan
    if (is_null($selectedMonth)) {
        $stmt = executeQuery(
            $pdo,
            "SELECT p.jenis_kelamin, COUNT(*) as jumlah
             FROM layanan l
             JOIN permohonan p ON l.no_reg_medan = p.no_reg_medan
             WHERE (YEAR(l.tgl_mulai_layanan) = ? OR (l.tanggal_disposisi IS NOT NULL AND YEAR(l.tanggal_disposisi) = ?))
             GROUP BY p.jenis_kelamin",
            [$selectedYear, $selectedYear]
        );
    } else {
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
        if (is_null($selectedMonth)) {
            $stmt = executeQuery(
                $pdo,
                "SELECT COUNT(*) as jumlah 
                 FROM permohonan 
                 WHERE tindak_pidana = ? 
                 AND YEAR(tgl_pengajuan) = ? 
                 AND tempat_permohonan != 'JAKARTA'",
                [$jenis, $selectedYear]
            );
        } else {
            $stmt = executeQuery(
                $pdo,
                "SELECT COUNT(*) as jumlah 
                 FROM permohonan 
                 WHERE tindak_pidana = ? 
                 AND YEAR(tgl_pengajuan) = ? AND MONTH(tgl_pengajuan) = ?
                 AND tempat_permohonan != 'JAKARTA'",
                [$jenis, $selectedYear, $selectedMonth]
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
    // Tambah filter bulan
    if (is_null($selectedMonth)) {
        $stmt = executeQuery(
            $pdo,
            "SELECT status_hukum, COUNT(*) as jumlah
             FROM permohonan 
             WHERE YEAR(tgl_pengajuan) = ? AND tempat_permohonan != 'JAKARTA'
             GROUP BY status_hukum",
            [$selectedYear]
        );
    } else {
        $stmt = executeQuery(
            $pdo,
            "SELECT status_hukum, COUNT(*) as jumlah
             FROM permohonan 
             WHERE YEAR(tgl_pengajuan) = ? AND MONTH(tgl_pengajuan) = ? AND tempat_permohonan != 'JAKARTA'
             GROUP BY status_hukum",
            [$selectedYear, $selectedMonth]
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

    // --- Data Jenis Perlindungan Permohonan ---
    // Tambah filter bulan (via tgl_pengajuan di tabel permohonan)
    if (is_null($selectedMonth)) {
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
    } else {
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
    // Tambah filter bulan di layanan
    if (is_null($selectedMonth)) {
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
    } else {
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

// --- DATA BARU: Detail Permohonan per Kabupaten/Kota ---
$stmt = executeQuery(
    $pdo,
    "SELECT 
        provinsi,
        kab_kota_pemohon,
        COUNT(*) as jumlah
     FROM permohonan
     WHERE $wProv AND kab_kota_pemohon IS NOT NULL AND kab_kota_pemohon != ''
     GROUP BY provinsi, kab_kota_pemohon
     ORDER BY provinsi, jumlah DESC",
    $pProv
);
$kabupatenData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format data kabupaten
$kabupatenByProvinsi = [];
foreach ($kabupatenData as $row) {
    $provinsi = $row['provinsi'];
    $kabupaten = $row['kab_kota_pemohon'];
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
    // Batasi menurut bulan jika dipilih
    if (is_null($selectedMonth)) {
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
        'selectedYear' => $selectedYear,
        'selectedMonth' => $selectedMonth,
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
                'layanan' => $genderDataLayanan
            ],
            'tindak_pidana' => $tindakPidanaChart,
            'status_hukum' => $statusHukumChart,
            'perlindungan_permohonan' => $perlindunganPermohonanChart,
            'perlindungan_layanan' => $perlindunganLayananChart,
            'perlindungan_comparison' => $perlindunganComparisonChart,
            'media_pengajuan' => $mediaPengajuanChart
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
