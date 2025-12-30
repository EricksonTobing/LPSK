<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-8 gap-4">
    <div>
        <!-- TAMBAHKAN ID PADA ELEMEN JUDUL -->
        <h2 id="status-hukum-title" class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Status Hukum Pemohon</h2>
        <p id="status-hukum-subtitle" class="text-gray-600 dark:text-gray-400">Distribusi berdasarkan status hukum dalam proses</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-4">
        <!-- Dropdown untuk memilih jenis status -->
        <div class="flex items-center space-x-2 bg-white dark:bg-gray-800 px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700">
            <label for="filter-jenis-status" class="text-sm text-gray-600 dark:text-gray-400">Tampilkan:</label>
            <select id="filter-jenis-status" class="bg-transparent border-none text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-0">
                <option value="permohonan">Status Permohonan</option>
                <option value="penelaahan">Status Penelaahan</option>
            </select>
        </div>
        
        <!-- Total counter -->
        <div class="flex items-center space-x-2 bg-indigo-50 dark:bg-indigo-900/20 px-3 py-2 rounded-xl">
            <div class="w-3 h-3 rounded-full bg-indigo-500"></div>
            <span class="text-indigo-700 dark:text-indigo-300 font-medium">Total: <span id="total-status-hukum">0</span> pemohon</span>
        </div>
    </div>
</div>
<div class="relative chart-container">
    <canvas id="chartStatusHukum" class="w-full h-96"></canvas>
    <div id="loading-chart-status-hukum" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 rounded-3xl backdrop-blur">
        <div class="text-center">
            <div class="w-12 h-12 border-4 border-indigo-200 border-t-indigo-500 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400">Memuat data status hukum...</p>
        </div>
    </div>
</div>
