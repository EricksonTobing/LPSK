<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-8 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2" id="media-pengajuan-title">Media Pengajuan Permohonan</h2>
        <p class="text-gray-600 dark:text-gray-400" id="media-pengajuan-subtitle">Distribusi berdasarkan cara pengajuan permohonan</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-4">
        <!-- Dropdown untuk memilih jenis media pengajuan -->
        <div class="flex items-center space-x-2 bg-white dark:bg-gray-800 px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700">
            <label for="filter-jenis-media" class="text-sm text-gray-600 dark:text-gray-400">Tampilkan:</label>
            <select id="filter-jenis-media" class="bg-transparent border-none text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-0">
                <option value="permohonan">Permohonan</option>
                <option value="penelaahan">Penelaahan</option>
            </select>
        </div>
        
        <!-- Total counter -->
        <div class="flex items-center space-x-2 bg-cyan-50 dark:bg-cyan-900/20 px-3 py-2 rounded-xl">
            <div class="w-3 h-3 rounded-full bg-cyan-500"></div>
            <span class="text-cyan-700 dark:text-cyan-300 font-medium">Total: <span id="total-media-pengajuan">0</span> permohonan</span>
        </div>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Chart Area -->
    <div class="relative chart-container">
        <canvas id="chartMediaPengajuan" class="w-full h-80"></canvas>
        <div id="loading-chart-media-pengajuan" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 rounded-3xl backdrop-blur">
            <div class="text-center">
                <div class="w-12 h-12 border-4 border-cyan-200 border-t-cyan-500 rounded-full animate-spin mx-auto mb-4"></div>
                <p class="text-gray-600 dark:text-gray-400">Memuat data media pengajuan...</p>
            </div>
        </div>
    </div>
    
    <!-- Detail Section -->
    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4" id="detail-media-pengajuan-title">Detail Media Pengajuan Permohonan</h3>
        <div id="media-pengajuan-detail" class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar">
            <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                <div class="w-6 h-6 border-2 border-gray-300 border-t-cyan-500 rounded-full animate-spin mx-auto mb-2"></div>
                Memuat detail media pengajuan...
            </div>
        </div>
    </div>
</div>