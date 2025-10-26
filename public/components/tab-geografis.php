<!-- Tab Geografis -->
<div class="tab-panel hidden" data-tab="geografis">
    <!-- Enhanced Map Section -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl mb-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Sebaran Geografis</h2>
            <p class="text-gray-600 dark:text-gray-400">Distribusi permohonan berdasarkan provinsi</p>
        </div>
        
        <!-- Container untuk peta yang dipusatkan -->
        <div class="map-center-wrapper">
            <div class="relative max-w-4xl w-full">
                <div id="map-container" class="h-96 rounded-2xl overflow-visible bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-700 dark:to-gray-600 border border-gray-200 dark:border-gray-600 mx-auto"></div>
                <div id="map-loading" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 rounded-2xl backdrop-blur">
                    <div class="text-center">
                        <div class="w-12 h-12 border-4 border-indigo-200 border-t-indigo-500 rounded-full animate-spin mx-auto mb-4"></div>
                        <p class="text-gray-600 dark:text-gray-400">Memuat peta interaktif...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="map-legend" class="flex flex-wrap justify-center gap-6 mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl"></div>
    </div>
</div>