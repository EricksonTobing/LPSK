<div class="text-center mb-8">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Distribusi Anggaran</h2>
    <p class="text-gray-600 dark:text-gray-400">Alokasi dan penggunaan dana</p>
</div>
<div class="relative chart-container">
    <canvas id="chartAnggaran" class="w-full h-64"></canvas>
    <div id="anggaran-center-text" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
        <div class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Sisa Anggaran</p>
            <div class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white" id="total-anggaran-text">Rp 0</div>
        </div>
    </div>
    <div id="loading-chart-anggaran" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 rounded-3xl backdrop-blur">
        <div class="text-center">
            <div class="w-12 h-12 border-4 border-purple-200 border-t-purple-500 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400">Memuat data anggaran...</p>
        </div>
    </div>
</div>

<div class="mt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Detail Anggaran</h3>
        <button class="text-xs text-blue-600 dark:text-blue-400 hover:underline" onclick="window.tabManager.switchTab('keuangan')">Lihat Semua</button>
    </div>
    <div id="anggaran-detail" class="space-y-3 max-h-48 overflow-y-auto custom-scrollbar">
        <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
            <div class="w-8 h-8 border-2 border-gray-300 border-t-blue-500 rounded-full animate-spin mx-auto mb-2"></div>
            Memuat detail anggaran...
        </div>
    </div>
</div>