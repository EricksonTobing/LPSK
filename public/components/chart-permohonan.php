<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-8 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Tren Statistik</h2>
        <p class="text-gray-600 dark:text-gray-400">Perkembangan data sepanjang tahun</p>
    </div>
    <div class="flex flex-wrap items-center gap-4 text-sm">
        <div class="flex items-center space-x-2 bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-xl">
            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
            <span class="text-blue-700 dark:text-blue-300 font-medium">Permohonan</span>
        </div>
        <div class="flex items-center space-x-2 bg-green-50 dark:bg-green-900/20 px-3 py-2 rounded-xl">
            <div class="w-3 h-3 rounded-full bg-green-500"></div>
            <span class="text-green-700 dark:text-green-300 font-medium">Penelaahan</span>
        </div>
        <div class="flex items-center space-x-2 bg-amber-50 dark:bg-amber-900/20 px-3 py-2 rounded-xl">
            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
            <span class="text-amber-700 dark:text-amber-300 font-medium">Layanan</span>
        </div>
    </div>
</div>
<div class="relative chart-container">
    <canvas id="chartPermohonan" class="w-full h-80"></canvas>
    <div id="loading-chart-permohonan" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 rounded-3xl backdrop-blur">
        <div class="text-center">
            <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-500 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400">Memuat data chart...</p>
        </div>
    </div>
</div>