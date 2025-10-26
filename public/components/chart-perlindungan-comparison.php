<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-8 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Perbandingan Jenis Perlindungan</h2>
        <p class="text-gray-600 dark:text-gray-400">Permohonan vs Layanan yang Diberikan</p>
    </div>
    <div class="flex flex-wrap items-center gap-3 text-sm">
        <div class="flex items-center space-x-2 bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-xl">
            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
            <span class="text-blue-700 dark:text-blue-300 font-medium">Permohonan</span>
        </div>
        <div class="flex items-center space-x-2 bg-green-50 dark:bg-green-900/20 px-3 py-2 rounded-xl">
            <div class="w-3 h-3 rounded-full bg-green-500"></div>
            <span class="text-green-700 dark:text-green-300 font-medium">Layanan</span>
        </div>
    </div>
</div>
<div class="relative chart-container">
    <canvas id="chartPerlindunganComparison" class="w-full h-96"></canvas>
    <div id="loading-chart-perlindungan-comparison" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 rounded-3xl backdrop-blur">
        <div class="text-center">
            <div class="w-12 h-12 border-4 border-indigo-200 border-t-indigo-500 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400">Memuat data perbandingan...</p>
        </div>
    </div>
</div>

<!-- Ringkasan Rasio -->
<div class="mt-6 flex justify-center items-center space-x-6 bg-gray-50 dark:bg-gray-700/30 backdrop-blur-sm rounded-xl p-4 border border-gray-100 dark:border-gray-600/30">
    <div class="text-center">
        <div class="text-xs text-gray-500 dark:text-gray-400">Total Permohonan</div>
        <div class="text-lg font-bold text-blue-600 dark:text-blue-400" id="total-permohonan-comparison">0</div>
    </div>
    <div class="text-center">
        <div class="text-xs text-gray-500 dark:text-gray-400">Total Layanan</div>
        <div class="text-lg font-bold text-green-600 dark:text-green-400" id="total-layanan-comparison">0</div>
    </div>
    <div class="text-center">
        <div class="text-xs text-gray-500 dark:text-gray-400">Rasio Pemenuhan</div>
        <div class="text-lg font-bold" id="rasio-pemenuhan">0%</div>
    </div>
</div>