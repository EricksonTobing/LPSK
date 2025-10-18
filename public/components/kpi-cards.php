<?php
// components/dashboard/kpi-cards.php
?>

<!-- Enhanced KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8" id="stats-cards">
  <!-- Enhanced Permohonan Card -->
  <div class="group relative overflow-hidden backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-6 border border-white/20 dark:border-gray-700/50 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-blue-600"></div>
    <div class="absolute top-4 right-4 opacity-10 group-hover:opacity-20 transition-opacity">
      <i class="fas fa-file-lines text-6xl text-blue-500"></i>
    </div>
    <div class="relative">
      <div class="flex items-center justify-between mb-4">
        <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
          <i class="fas fa-file-lines text-white text-xl"></i>
        </div>
        <div class="text-right">
          <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
            <i class="fas fa-arrow-up text-blue-600 dark:text-blue-400 text-xs"></i>
          </div>
        </div>
      </div>
      <div>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Permohonan</p>
        <p class="text-3xl font-bold text-gray-800 dark:text-white" id="permohonan-count">
          <span class="inline-block">0</span>
        </p>
        <p class="text-xs text-blue-600 dark:text-blue-400 mt-2 font-medium" id="permohonan-change">
          +0% dari bulan lalu
        </p>
      </div>
    </div>
  </div>
  
  <!-- Enhanced Penelaahan Card -->
  <div class="group relative overflow-hidden backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-6 border border-white/20 dark:border-gray-700/50 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-green-400 to-green-600"></div>
    <div class="absolute top-4 right-4 opacity-10 group-hover:opacity-20 transition-opacity">
      <i class="fas fa-magnifying-glass text-6xl text-green-500"></i>
    </div>
    <div class="relative">
      <div class="flex items-center justify-between mb-4">
        <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
          <i class="fas fa-magnifying-glass text-white text-xl"></i>
        </div>
        <div class="text-right">
          <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
            <i class="fas fa-arrow-up text-green-600 dark:text-green-400 text-xs"></i>
          </div>
        </div>
      </div>
      <div>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Penelaahan</p>
        <p class="text-3xl font-bold text-gray-800 dark:text-white" id="penelaahan-count">
          <span class="inline-block">0</span>
        </p>
        <p class="text-xs text-green-600 dark:text-green-400 mt-2 font-medium" id="penelaahan-change">
          +0% dari bulan lalu
        </p>
      </div>
    </div>
  </div>
  
  <!-- Enhanced Layanan Card -->
  <div class="group relative overflow-hidden backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-6 border border-white/20 dark:border-gray-700/50 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-400 to-amber-600"></div>
    <div class="absolute top-4 right-4 opacity-10 group-hover:opacity-20 transition-opacity">
      <i class="fas fa-handshake text-6xl text-amber-500"></i>
    </div>
    <div class="relative">
      <div class="flex items-center justify-between mb-4">
        <div class="w-14 h-14 bg-gradient-to-br from-amber-400 to-amber-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
          <i class="fas fa-handshake text-white text-xl"></i>
        </div>
        <div class="text-right">
          <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
            <i class="fas fa-arrow-up text-amber-600 dark:text-amber-400 text-xs"></i>
          </div>
        </div>
      </div>
      <div>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Layanan</p>
        <p class="text-3xl font-bold text-gray-800 dark:text-white" id="layanan-count">
          <span class="inline-block">0</span>
        </p>
        <p class="text-xs text-amber-600 dark:text-amber-400 mt-2 font-medium" id="layanan-change">
          +0% dari bulan lalu
        </p>
      </div>
    </div>
  </div>
  
  <!-- Enhanced Pengeluaran Card -->
  <div class="group relative overflow-hidden backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-6 border border-white/20 dark:border-gray-700/50 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-400 to-purple-600"></div>
    <div class="absolute top-4 right-4 opacity-10 group-hover:opacity-20 transition-opacity">
      <i class="fas fa-coins text-6xl text-purple-500"></i>
    </div>
    <div class="relative">
      <div class="flex items-center justify-between mb-4">
        <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
          <i class="fas fa-coins text-white text-xl"></i>
        </div>
        <div class="text-right">
          <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
            <i class="fas fa-arrow-down text-red-600 dark:text-red-400 text-xs"></i>
          </div>
        </div>
      </div>
      <div>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Pengeluaran</p>
        <p class="text-2xl lg:text-3xl font-bold text-gray-800 dark:text-white" id="pengeluaran-count">
          <span class="inline-block">Rp 0</span>
        </p>
        <p class="text-xs text-red-600 dark:text-red-400 mt-2 font-medium" id="pengeluaran-change">
          +0% dari bulan lalu
        </p>
      </div>
    </div>
  </div>
</div>