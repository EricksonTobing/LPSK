<!-- Tab Perlindungan -->
<div class="tab-panel hidden" data-tab="perlindungan">
    <!-- Jenis Perlindungan Charts -->
    <div class="grid grid-cols-1 2xl:grid-cols-3 gap-8 mb-8">
        <!-- Chart Perbandingan Jenis Perlindungan -->
        <div class="xl:col-span-2 backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl">
            <?php include __DIR__ . '/chart-perlindungan-comparison.php'; ?>
        </div>

        <!-- Chart Distribusi Jenis Perlindungan Permohonan -->
        <div class="xl:col-span-2 backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl">
            <?php include __DIR__ . '/chart-perlindungan-permohonan.php'; ?>
        </div>

        <!-- Chart Distribusi Jenis Perlindungan Layanan -->
        <div class="xl:col-span-2 backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl">
            <?php include __DIR__ . '/chart-perlindungan-layanan.php'; ?>
        </div>
    </div>
</div>