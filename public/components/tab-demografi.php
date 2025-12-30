<!-- Tab Demografi -->
<div class="tab-panel hidden" data-tab="demografi">
    <!-- Gender Distribution Chart -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl mb-8">
        <?php include __DIR__ . '/chart-gender-combined.php'; ?>
    </div>

    <!-- Workload Chart -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl mb-8">
        <?php include __DIR__ . '/chart-beban-kerja.php'; ?>
    </div>

    <!-- Monitoring Section with Tabs -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl mb-8">
        <!-- Header & Tabs -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 flex items-center">
                <i class="fas fa-desktop mr-3 text-blue-600"></i> Pusat Monitoring
            </h3>
            
            <div class="bg-gray-100 dark:bg-gray-700 p-1 rounded-xl flex space-x-1">
                <button onclick="switchMonitorTab('penelaahan')" id="btn-monitor-penelaahan" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all shadow-sm bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400">
                    <i class="fas fa-clipboard-check mr-2"></i> Penelaahan
                </button>
                <button onclick="switchMonitorTab('layanan')" id="btn-monitor-layanan" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fas fa-hourglass-half mr-2"></i> Layanan
                </button>
            </div>
        </div>

        <!-- Monitoring Content -->
        <div class="relative min-h-[300px]">
            
            <!-- 1. Monitoring Penelaahan Table -->
            <div id="monitor-penelaahan" class="transition-opacity duration-300">
                <?php
                // Fetch active penelaahan
                $monitor_penelaahan_sql = "SELECT 
                                    pn.no_registrasi, 
                                    pm.nama_pemohon, 
                                    pn.tanggal_dispo, 
                                    pn.tgl_berakhir_penelaahan, 
                                    pn.waktu_tambahan,
                                    p.nama_pegawai,
                                    DATEDIFF(pn.tgl_berakhir_penelaahan, CURDATE()) as sisa_hari
                                FROM penelaahan pn
                                JOIN permohonan pm ON pn.no_reg_medan = pm.no_reg_medan
                                LEFT JOIN pegawai p ON pn.id_pegawai = p.id_pegawai
                                WHERE pn.tgl_berakhir_penelaahan IS NOT NULL 
                                  AND pn.tgl_berakhir_penelaahan >= CURDATE()
                                ORDER BY sisa_hari ASC";
                
                $monitor_p_stmt = $pdo->prepare($monitor_penelaahan_sql);
                $monitor_p_stmt->execute();
                $monitor_p_rows = $monitor_p_stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3 rounded-l-lg">Pegawai</th>
                                <th scope="col" class="px-6 py-3">Pemohon</th>
                                <th scope="col" class="px-6 py-3">Tgl Disposisi</th>
                                <th scope="col" class="px-6 py-3">Waktu Tambahan</th>
                                <th scope="col" class="px-6 py-3">Berakhir Pada</th>
                                <th scope="col" class="px-6 py-3 text-center rounded-r-lg">Sisa Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if (count($monitor_p_rows) > 0): ?>
                                <?php foreach ($monitor_p_rows as $row): 
                                    $sisa = (int)$row['sisa_hari'];
                                    $badge_class = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
                                    $status_text = $sisa . ' Hari';
                                    
                                    if ($sisa <= 3) {
                                        $badge_class = 'bg-red-200 text-red-900 dark:bg-red-900 dark:text-red-200 animate-pulse';
                                    } elseif ($sisa <= 7) {
                                        $badge_class = 'bg-yellow-200 text-yellow-900 dark:bg-yellow-800 dark:text-yellow-100';
                                    }
                                ?>
                                <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center mr-3 text-purple-600 dark:text-purple-300 font-bold text-xs">
                                                <?= strtoupper(substr($row['nama_pegawai'] ?? '?', 0, 2)) ?>
                                            </div>
                                            <?= htmlspecialchars($row['nama_pegawai'] ?? 'Belum Ditentukan') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium"><?= htmlspecialchars($row['nama_pemohon']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($row['no_registrasi']) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?= date('d M Y', strtotime($row['tanggal_dispo'])) ?>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-purple-600 dark:text-purple-400">
                                        <?= !empty($row['waktu_tambahan']) ? '+ ' . htmlspecialchars($row['waktu_tambahan']) : '-' ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?= date('d M Y', strtotime($row['tgl_berakhir_penelaahan'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="<?= $badge_class ?> px-3 py-1 rounded-full text-xs font-semibold shadow-sm inline-block min-w-[100px]">
                                            <?= $status_text ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="bg-white dark:bg-gray-800">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400 italic">
                                        Tidak ada penelaahan aktif yang mendekati tenggat waktu.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. Monitoring Layanan Table -->
            <div id="monitor-layanan" class="hidden transition-opacity duration-300">
                <?php
                // Fetch active services
                $monitor_layanan_sql = "SELECT 
                                    l.no_kep_smpl, 
                                    l.nama_terlindung, 
                                    l.tgl_mulai_layanan, 
                                    l.tgl_berakhir_layanan, 
                                    l.masa_layanan,
                                    l.tambahan_masa_layanan, 
                                    p.nama_pegawai,
                                    DATEDIFF(l.tgl_berakhir_layanan, CURDATE()) as sisa_hari
                                FROM layanan l
                                LEFT JOIN pegawai p ON l.id_pegawai = p.id_pegawai
                                WHERE l.status = 'BERJALAN' AND l.tgl_berakhir_layanan >= CURDATE()
                                ORDER BY sisa_hari ASC";
                
                $monitor_l_stmt = $pdo->prepare($monitor_layanan_sql);
                $monitor_l_stmt->execute();
                $monitor_l_rows = $monitor_l_stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3 rounded-l-lg">Pegawai</th>
                                <th scope="col" class="px-6 py-3">Terlindung</th>
                                <th scope="col" class="px-6 py-3">Masa Layanan</th>
                                <th scope="col" class="px-6 py-3 text-blue-600 dark:text-blue-400">Tambahan</th>
                                <th scope="col" class="px-6 py-3">Mulai Pada</th>
                                <th scope="col" class="px-6 py-3">Berakhir Pada</th>
                                <th scope="col" class="px-6 py-3 text-center rounded-r-lg">Sisa Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if (count($monitor_l_rows) > 0): ?>
                                <?php foreach ($monitor_l_rows as $row): 
                                    $sisa = (int)$row['sisa_hari'];
                                    $badge_class = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
                                    $status_text = $sisa . ' Hari';
                                    
                                    if ($sisa <= 7) {
                                        $badge_class = 'bg-red-200 text-red-900 dark:bg-red-900 dark:text-red-200 animate-pulse';
                                    } elseif ($sisa <= 30) {
                                        $badge_class = 'bg-yellow-200 text-yellow-900 dark:bg-yellow-800 dark:text-yellow-100';
                                    }
                                ?>
                                <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center mr-3 text-blue-600 dark:text-blue-300 font-bold text-xs">
                                                <?= strtoupper(substr($row['nama_pegawai'] ?? '?', 0, 2)) ?>
                                            </div>
                                            <?= htmlspecialchars($row['nama_pegawai'] ?? 'Belum Ditentukan') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium"><?= htmlspecialchars($row['nama_terlindung']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($row['no_kep_smpl']) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?= htmlspecialchars($row['masa_layanan']) ?>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-blue-600 dark:text-blue-400">
                                        <?= !empty($row['tambahan_masa_layanan']) ? '+' . htmlspecialchars($row['tambahan_masa_layanan']) : '-' ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?= date('d M Y', strtotime($row['tgl_mulai_layanan'])) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?= date('d M Y', strtotime($row['tgl_berakhir_layanan'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="<?= $badge_class ?> px-3 py-1 rounded-full text-xs font-semibold shadow-sm inline-block min-w-[100px]">
                                            <?= $status_text ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="bg-white dark:bg-gray-800">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400 italic">
                                        Tidak ada layanan yang sedang berjalan saat ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>

<script>
function switchMonitorTab(tab) {
    const penelaahanView = document.getElementById('monitor-penelaahan');
    const layananView = document.getElementById('monitor-layanan');
    const btnPenelaahan = document.getElementById('btn-monitor-penelaahan');
    const btnLayanan = document.getElementById('btn-monitor-layanan');
    
    // Active class (Card style) vs Inactive (Transparent)
    const activeClasses = ['bg-white', 'dark:bg-gray-600', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm'];
    const inactiveClasses = ['text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-200'];

    if (tab === 'penelaahan') {
        penelaahanView.classList.remove('hidden');
        layananView.classList.add('hidden');
        
        btnPenelaahan.classList.add(...activeClasses);
        btnPenelaahan.classList.remove(...inactiveClasses);
        
        btnLayanan.classList.remove(...activeClasses);
        btnLayanan.classList.add(...inactiveClasses);
    } else {
        layananView.classList.remove('hidden');
        penelaahanView.classList.add('hidden');
        
        btnLayanan.classList.add(...activeClasses);
        btnLayanan.classList.remove(...inactiveClasses);
        
        btnPenelaahan.classList.remove(...activeClasses);
        btnPenelaahan.classList.add(...inactiveClasses);
    }
}
</script>
</div>