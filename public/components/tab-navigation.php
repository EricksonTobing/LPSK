<?php
// components/dashboard/tab-navigation.php
?>

<!-- Tab Navigation -->
<div class="mb-8">
    <div class="backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-2 border border-white/20 dark:border-gray-700/50 shadow-xl">
        <div class="flex flex-wrap gap-2" id="dashboard-tabs">
            <!-- Tab Overview (Default Active) -->
            <button class="tab-button px-6 py-3 rounded-2xl font-medium transition-all duration-300 bg-gradient-to-r from-primary-blue to-primary-red text-white shadow-lg" data-tab="overview">
                <i class="fas fa-chart-pie mr-2"></i>Overview
            </button>
            
            <!-- Tab Perlindungan -->
            <button class="tab-button px-6 py-3 rounded-2xl font-medium transition-all duration-300 bg-white/50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-white/80 dark:hover:bg-gray-600/50" data-tab="perlindungan">
                <i class="fas fa-shield-alt mr-2"></i>Perlindungan
            </button>
            
            <!-- Tab Demografi -->
            <button class="tab-button px-6 py-3 rounded-2xl font-medium transition-all duration-300 bg-white/50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-white/80 dark:hover:bg-gray-600/50" data-tab="demografi">
                <i class="fas fa-users mr-2"></i>Demografi
            </button>
            
            <!-- Tab Geografis -->
            <button class="tab-button px-6 py-3 rounded-2xl font-medium transition-all duration-300 bg-white/50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-white/80 dark:hover:bg-gray-600/50" data-tab="geografis">
                <i class="fas fa-map mr-2"></i>Geografis
            </button>
            
            <!-- Tab Keuangan -->
            <button class="tab-button px-6 py-3 rounded-2xl font-medium transition-all duration-300 bg-white/50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-white/80 dark:hover:bg-gray-600/50" data-tab="keuangan">
                <i class="fas fa-coins mr-2"></i>Keuangan
            </button>
            
            <!-- Tab Aktivitas -->
            <button class="tab-button px-6 py-3 rounded-2xl font-medium transition-all duration-300 bg-white/50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-white/80 dark:hover:bg-gray-600/50" data-tab="aktivitas">
                <i class="fas fa-clock mr-2"></i>Aktivitas
            </button>
        </div>
    </div>
</div>