<div class="text-center mb-8">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Jenis Perlindungan - Layanan</h2>
    <p class="text-gray-600 dark:text-gray-400">Jenis perlindungan yang diberikan</p>
    <div class="mt-4 flex justify-center">
        <div class="bg-green-50 dark:bg-green-900/20 px-4 py-2 rounded-xl inline-flex items-center space-x-2">
            <div class="w-3 h-3 rounded-full bg-green-500"></div>
            <span class="text-green-700 dark:text-green-300 font-medium">
                Total: <span id="total-perlindungan-layanan">0</span> jenis perlindungan diberikan
            </span>
        </div>
    </div>
</div>

<!-- Chart Area -->
<div class="relative pie-chart-container">
    <canvas id="chartPerlindunganLayanan" class="w-full h-80"></canvas>
    <div id="loading-chart-perlindungan-layanan" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 rounded-3xl backdrop-blur">
        <div class="text-center">
            <div class="w-12 h-12 border-4 border-green-200 border-t-green-500 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400">Memuat data layanan...</p>
        </div>
    </div>
</div>

<!-- Scrollable Detail Section -->
<div class="bg-gray-50 dark:bg-gray-700/30 rounded-2xl p-4 mt-6">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Detail Kategori</h3>
        <span class="text-xs text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-600 px-2 py-1 rounded-full">
            <i class="fas fa-arrows-up-down mr-1"></i>Scroll untuk melihat lebih banyak
        </span>
    </div>
    
    <div id="perlindungan-layanan-detail" class="max-h-48 overflow-y-auto custom-scrollbar space-y-3">
        <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
            <div class="w-6 h-6 border-2 border-gray-300 border-t-green-500 rounded-full animate-spin mx-auto mb-2"></div>
            Memuat detail kategori...
        </div>
    </div>
</div>