<div class="text-center mb-8">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Distribusi Jenis Kelamin</h2>
    <p class="text-gray-600 dark:text-gray-400">Perbandingan data pemohon, penelaahan, dan penerima layanan</p>
    <div class="mt-4 flex justify-center space-x-4 flex-wrap gap-2">
        <div class="bg-blue-50 dark:bg-blue-900/20 px-4 py-2 rounded-xl inline-flex items-center space-x-2">
            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
            <span class="text-blue-700 dark:text-blue-300 font-medium">
                Permohonan: <span id="total-gender-permohonan-text">0</span>
            </span>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-900/20 px-4 py-2 rounded-xl inline-flex items-center space-x-2">
            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
            <span class="text-emerald-700 dark:text-emerald-300 font-medium">
                Penelaahan: <span id="total-gender-penelaahan-text">0</span>
            </span>
        </div>
        <div class="bg-green-50 dark:bg-green-900/20 px-4 py-2 rounded-xl inline-flex items-center space-x-2">
            <div class="w-3 h-3 rounded-full bg-green-500"></div>
            <span class="text-green-700 dark:text-green-300 font-medium">
                Layanan: <span id="total-gender-layanan-text">0</span>
            </span>
        </div>
    </div>
</div>

<div class="relative chart-container">
    <canvas id="chartGenderCombined" class="w-full h-80"></canvas>
    <div id="loading-chart-gender-combined" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 rounded-3xl backdrop-blur">
        <div class="text-center">
            <div class="w-12 h-12 border-4 border-indigo-200 border-t-indigo-500 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400">Memuat data jenis kelamin...</p>
        </div>
    </div>
</div>

<!-- Detail Section -->
<div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Detail Permohonan -->
    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-2xl p-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3 text-center">Detail Permohonan</h3>
        <div id="gender-permohonan-detail" class="max-h-48 overflow-y-auto custom-scrollbar space-y-3">
            <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                <div class="w-6 h-6 border-2 border-gray-300 border-t-blue-500 rounded-full animate-spin mx-auto mb-2"></div>
                Memuat detail permohonan...
            </div>
        </div>
    </div>
    
    <!-- Detail Penelaahan -->
    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-2xl p-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3 text-center">Detail Penelaahan</h3>
        <div id="gender-penelaahan-detail" class="max-h-48 overflow-y-auto custom-scrollbar space-y-3">
            <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                <div class="w-6 h-6 border-2 border-gray-300 border-t-emerald-500 rounded-full animate-spin mx-auto mb-2"></div>
                Memuat detail penelaahan...
            </div>
        </div>
    </div>
    
    <!-- Detail Layanan -->
    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-2xl p-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3 text-center">Detail Layanan</h3>
        <div id="gender-layanan-detail" class="max-h-48 overflow-y-auto custom-scrollbar space-y-3">
            <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                <div class="w-6 h-6 border-2 border-gray-300 border-t-green-500 rounded-full animate-spin mx-auto mb-2"></div>
                Memuat detail layanan...
            </div>
        </div>
    </div>
</div>