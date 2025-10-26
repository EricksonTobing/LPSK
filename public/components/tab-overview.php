<!-- Tab Overview -->
<div class="tab-panel active" data-tab="overview">
    <!-- Enhanced Charts Section -->
    <div class="grid grid-cols-1 2xl:grid-cols-3 gap-8 mb-8">
        <!-- Enhanced Multi-Line Chart -->
        <div class="2xl:col-span-2 backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl">
            <?php include __DIR__ . '/chart-permohonan.php'; ?>
        </div>

        <!-- Enhanced Doughnut Chart -->
        <div class="backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl">
            <?php include __DIR__ . '/chart-anggaran.php'; ?>
        </div>
    </div>

    <!-- Media Pengajuan Chart Section -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl mb-8">
        <?php include __DIR__ . '/chart-media-pengajuan.php'; ?>
    </div>

    <!-- Status Hukum Chart Section -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl mb-8">
        <?php include __DIR__ . '/chart-status-hukum.php'; ?>
    </div>

    <!-- Tindak Pidana Chart Section -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl mb-8">
        <?php include __DIR__ . '/chart-tindak-pidana.php'; ?>
    </div>
</div>