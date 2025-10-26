<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-8 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Jenis Tindak Pidana</h2>
        <p class="text-gray-600 dark:text-gray-400">Distribusi berdasarkan jenis kejahatan</p>
    </div>
    <div class="flex items-center space-x-2 bg-purple-50 dark:bg-purple-900/20 px-3 py-2 rounded-xl">
        <div class="w-3 h-3 rounded-full bg-purple-500"></div>
        <span class="text-purple-700 dark:text-purple-300 font-medium">Total: <span id="total-tindak-pidana">0</span> kasus</span>
    </div>
</div>
<div class="relative chart-container">
    <canvas id="chartTindakPidana" class="w-full h-96"></canvas>
    <div id="loading-chart-tindak-pidana" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 rounded-3xl backdrop-blur">
        <div class="text-center">
            <div class="w-12 h-12 border-4 border-purple-200 border-t-purple-500 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400">Memuat data tindak pidana...</p>
        </div>
    </div>
</div>