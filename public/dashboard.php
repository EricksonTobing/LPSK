<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';

require_login();
$title = 'Dashboard';
require __DIR__ . '/../inc/layout_header.php';
require __DIR__ . '/../inc/layout_nav.php';
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-slate-900 dark:to-gray-800">
  <div class="container mx-auto px-4 py-8">
    <!-- Enhanced Header -->
    <div class="mb-8">
      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div class="flex items-center space-x-4">
          <div class="relative">
            <div class="w-16 h-16 bg-gradient-to-r from-primary-blue to-primary-red rounded-2xl flex items-center justify-center shadow-lg animate-pulse">
              <i class="fas fa-chart-line text-white text-2xl"></i>
            </div>
            <div class="absolute -top-1 -right-1 w-6 h-6 bg-green-400 rounded-full flex items-center justify-center">
              <i class="fas fa-check text-white text-xs"></i>
            </div>
          </div>
          <div>
            <h1 class="text-3xl lg:text-4xl font-bold bg-gradient-to-r from-primary-blue to-primary-red bg-clip-text text-transparent">
              Dashboard Analytics
            </h1>
            <!-- <p class="text-gray-600 dark:text-gray-300 mt-1">Sistem Monitoring & Pelaporan Terintegrasi</p> -->
          </div>
        </div>
        
        <!-- Enhanced Year Filter -->
        <div class="flex items-center space-x-4">
          <div class="backdrop-blur-sm bg-white/70 dark:bg-gray-800/70 rounded-2xl p-4 border border-white/20 dark:border-gray-700/50 shadow-xl">
            <div class="flex items-center space-x-3">
              <div class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-gradient-to-r from-primary-blue to-primary-red rounded-xl flex items-center justify-center">
                  <i class="fas fa-calendar-alt text-white text-sm"></i>
                </div>
                <div>
                  <label for="yearFilter" class="text-xs text-gray-500 dark:text-gray-400 block">Filter Tahun</label>
                  <select id="yearFilter" class="bg-transparent border-0 text-sm font-bold text-gray-700 dark:text-gray-300 focus:outline-none cursor-pointer">
                    <?php
                    try {
                        $pdo = db();
                        $stmt = $pdo->query("
                            SELECT DISTINCT tahun 
                            FROM (
                                SELECT YEAR(tgl_pengajuan) as tahun FROM permohonan
                                UNION SELECT YEAR(tanggal_dispo) FROM penelaahan
                                UNION SELECT YEAR(tanggal) FROM pengeluaran
                                UNION SELECT YEAR(tgl_mulai_layanan) FROM layanan
                                UNION SELECT tahun FROM anggaran
                            ) years 
                            WHERE tahun IS NOT NULL 
                            ORDER BY tahun DESC
                        ");
                        $availableYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        $currentYear = date('Y');
                        $selectedYear = $_GET['year'] ?? $currentYear;
                        
                        if (empty($availableYears)) {
                            $availableYears = range($currentYear, $currentYear - 5);
                        }
                        
                        foreach ($availableYears as $year) {
                            $selected = $year == $selectedYear ? 'selected' : '';
                            echo "<option value='$year' $selected>$year</option>";
                        }
                    } catch (Exception $e) {
                        $currentYear = date('Y');
                        for ($year = $currentYear; $year >= 2020; $year--) {
                            $selected = $year == $currentYear ? 'selected' : '';
                            echo "<option value='$year' $selected>$year</option>";
                        }
                    }
                    ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
          
          <button id="refreshBtn" class="group relative overflow-hidden bg-gradient-to-r from-primary-blue to-primary-red hover:from-blue-600 hover:to-purple-700 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="absolute inset-0 bg-white/20 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"></div>
            <div class="relative flex items-center space-x-2">
              <i class="fas fa-sync-alt transition-transform group-hover:rotate-180 duration-500"></i>
              <span>Refresh</span>
            </div>
          </button>
        </div>
      </div>

      <!-- Real-time Status Indicator -->
      <div class="mt-6 flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
        <div class="flex items-center space-x-2">
          <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
          <span>Live Data</span>
        </div>
        <div class="flex items-center space-x-2">
          <i class="fas fa-clock"></i>
          <span id="lastUpdate">Terakhir diperbarui: <span class="font-medium text-gray-800 dark:text-gray-200">--</span></span>
        </div>
      </div>
    </div>

    <!-- Enhanced Alert -->
    <div id="error-alert" class="hidden mb-8 animate-fade-in">
      <div class="backdrop-blur-sm bg-red-50/90 dark:bg-red-900/30 border-l-4 border-red-400 p-4 rounded-r-2xl shadow-lg">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
          </div>
          <div class="ml-3">
            <p class="text-red-700 dark:text-red-300 font-medium" id="error-message">Terjadi kesalahan saat memuat data.</p>
          </div>
          <div class="ml-auto">
            <button onclick="document.getElementById('error-alert').classList.add('hidden')" class="text-red-400 hover:text-red-600">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

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
    +0% dari bulan lalu <!-- Placeholder, akan diupdate oleh JavaScript -->
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

    <!-- Enhanced Charts Section -->
    <div class="grid grid-cols-1 2xl:grid-cols-3 gap-8 mb-8">
      <!-- Enhanced Multi-Line Chart -->
      <div class="2xl:col-span-2 backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl">
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
        <div class="relative">
          <canvas id="chartPermohonan" class="w-full h-80"></canvas>
          <div id="loading-chart-permohonan" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 rounded-3xl backdrop-blur">
            <div class="text-center">
              <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-500 rounded-full animate-spin mx-auto mb-4"></div>
              <p class="text-gray-600 dark:text-gray-400">Memuat data chart...</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Doughnut Chart -->
      <div class="backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl">
        <div class="text-center mb-8">
          <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Distribusi Anggaran</h2>
          <p class="text-gray-600 dark:text-gray-400">Alokasi dan penggunaan dana</p>
        </div>
        <div class="relative">
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
          <!-- <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Detail Anggaran</h3>
            <button class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Lihat Semua</button>
          </div> -->
          <div id="anggaran-detail" class="space-y-3 max-h-48 overflow-y-auto custom-scrollbar">
            <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
              <div class="w-8 h-8 border-2 border-gray-300 border-t-blue-500 rounded-full animate-spin mx-auto mb-2"></div>
              Memuat detail anggaran...
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Workload Chart -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl mb-8">
      <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-8 gap-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Beban Kerja Pegawai</h2>
          <p class="text-gray-600 dark:text-gray-400">Distribusi tugas dan tanggung jawab</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-sm">
          <div class="flex items-center space-x-2 bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-xl">
            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
            <span class="text-blue-700 dark:text-blue-300 font-medium">Penerima Permohonan</span>
          </div>
          <div class="flex items-center space-x-2 bg-green-50 dark:bg-green-900/20 px-3 py-2 rounded-xl">
            <div class="w-3 h-3 rounded-full bg-green-500"></div>
            <span class="text-green-700 dark:text-green-300 font-medium">CM Penelaahan</span>
          </div>
          <div class="flex items-center space-x-2 bg-amber-50 dark:bg-amber-900/20 px-3 py-2 rounded-xl">
            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
            <span class="text-amber-700 dark:text-amber-300 font-medium">CM Layanan</span>
          </div>
        </div>
      </div>
      <div class="relative">
        <canvas id="chartBebanKerja" class="w-full h-96"></canvas>
        <div id="loading-chart-beban-kerja" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 rounded-3xl backdrop-blur">
          <div class="text-center">
            <div class="w-12 h-12 border-4 border-green-200 border-t-green-500 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400">Memuat data beban kerja...</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Map Section -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl mb-8">
      <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Sebaran Geografis</h2>
        <p class="text-gray-600 dark:text-gray-400">Distribusi permohonan berdasarkan provinsi</p>
      </div>
      <div class="relative">
        <div id="map-container" class="h-96 rounded-2xl overflow-hidden bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-700 dark:to-gray-600 border border-gray-200 dark:border-gray-600"></div>
        <div id="map-loading" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 rounded-2xl backdrop-blur">
          <div class="text-center">
            <div class="w-12 h-12 border-4 border-indigo-200 border-t-indigo-500 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400">Memuat peta interaktif...</p>
          </div>
        </div>
      </div>
      <div id="map-legend" class="flex flex-wrap justify-center gap-6 mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl"></div>
    </div>

    <!-- Enhanced Recent Activities -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 rounded-3xl p-8 border border-white/20 dark:border-gray-700/50 shadow-xl">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Aktivitas Terbaru</h2>
          <p class="text-gray-600 dark:text-gray-400">Update terkini dari sistem</p>
        </div>
        <!-- <button class="flex items-center space-x-2 px-4 py-2 text-sm text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-xl transition-colors">
          <i class="fas fa-external-link-alt"></i>
          <span>Lihat Semua</span>
        </button> -->
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="aktivitas-container">
        <div class="col-span-3 text-center py-12">
          <div class="w-16 h-16 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin mx-auto mb-4"></div>
          <p class="text-gray-500 dark:text-gray-400">Memuat aktivitas terbaru...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Load external libraries -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script src="https://d3js.org/d3.v3.min.js"></script>
<script src="https://d3js.org/topojson.v1.min.js"></script>
<script>
// Load datamaps with error handling
(function() {
  const script = document.createElement('script');
  script.src = 'datamaps.indonesia.min.js';
  script.onerror = function() {
    console.warn('Datamaps Indonesia library not found. Map will not be displayed.');
    document.getElementById('map-loading').innerHTML = 
      '<div class="text-center text-gray-500 dark:text-gray-400">' +
      '<i class="fas fa-map-marked-alt text-6xl mb-4 opacity-50"></i>' +
      '<h3 class="text-lg font-semibold mb-2">Peta Tidak Tersedia</h3>' +
      '<p class="text-sm">Library datamaps.indonesia.min.js tidak ditemukan</p>' +
      '</div>';
  };
  document.head.appendChild(script);
})();
</script>

<!-- Enhanced JavaScript with additional features -->
<script>
class DashboardManager {
  constructor() {
    this.charts = {
      permohonan: null,
      anggaran: null,
      bebanKerja: null
    };
    
    this.colors = {
      primary: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'],
      backgrounds: [
        'rgba(59, 130, 246, 0.1)', 'rgba(16, 185, 129, 0.1)', 
        'rgba(245, 158, 11, 0.1)', 'rgba(139, 92, 246, 0.1)',
        'rgba(236, 72, 153, 0.1)', 'rgba(6, 182, 212, 0.1)'
      ]
    };
    
    this.init();
  }

  init() {
    this.setupEventListeners();
    this.loadDashboardData();
    this.updateTimestamp();
    
    // Auto refresh every 5 minutes
    setInterval(() => {
      this.loadDashboardData();
    }, 300000);
  }

  setupEventListeners() {
    // Event listener untuk filter tahun
    const yearFilter = document.getElementById('yearFilter');
    if (yearFilter) {
      yearFilter.addEventListener('change', () => {
        this.loadDashboardData();
      });
    }

    // Event listener untuk refresh button
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
      refreshBtn.addEventListener('click', () => {
        this.loadDashboardData();
      });
    }
  }

  updateTimestamp() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('id-ID', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    });
    const lastUpdateElement = document.querySelector('#lastUpdate span');
    if (lastUpdateElement) {
      lastUpdateElement.textContent = timeString;
    }
  }

  showError(message) {
    const errorAlert = document.getElementById('error-alert');
    const errorMessage = document.getElementById('error-message');
    
    if (errorAlert && errorMessage) {
      errorMessage.textContent = message;
      errorAlert.classList.remove('hidden');
      errorAlert.classList.add('animate-fade-in');
      
      // Auto hide after 10 seconds
      setTimeout(() => {
        errorAlert.classList.add('hidden');
      }, 10000);
    }
  }

  hideError() {
    const errorAlert = document.getElementById('error-alert');
    if (errorAlert) {
      errorAlert.classList.add('hidden');
    }
  }

  showLoadingState() {
    // Enhanced loading for cards with shimmer effect
    const statsCards = document.getElementById('stats-cards');
    if (statsCards) {
      statsCards.classList.add('opacity-60');
      
      const countElements = ['permohonan-count', 'penelaahan-count', 'layanan-count', 'pengeluaran-count'];
      countElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
          element.innerHTML = '<div class="animate-pulse bg-gray-300 dark:bg-gray-600 h-8 w-16 rounded"></div>';
        }
      });
    }

    // Show loading for charts with enhanced animation
    const loadingElements = [
      'loading-chart-permohonan',
      'loading-chart-anggaran', 
      'loading-chart-beban-kerja'
    ];
    
    loadingElements.forEach(id => {
      const element = document.getElementById(id);
      if (element) {
        element.style.display = 'flex';
      }
    });
  }

  hideLoadingState() {
    // Hide loading for cards
    const statsCards = document.getElementById('stats-cards');
    if (statsCards) {
      statsCards.classList.remove('opacity-60');
    }

    // Hide loading for charts
    const loadingElements = [
      'loading-chart-permohonan',
      'loading-chart-anggaran', 
      'loading-chart-beban-kerja'
    ];
    
    loadingElements.forEach(id => {
      const element = document.getElementById(id);
      if (element) {
        element.style.display = 'none';
      }
    });
  }

  async loadDashboardData() {
    try {
      this.hideError();
      this.showLoadingState();

      const selectedYear = document.getElementById('yearFilter')?.value || new Date().getFullYear();
      const response = await fetch(`api_stats.php?year=${selectedYear}`);
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      
      const data = await response.json();
      
      if (!data.success) {
        throw new Error(data.error || 'Terjadi kesalahan pada server');
      }

      // Update filter tahun jika berbeda dengan yang dipilih
      if (data.selectedYear && data.selectedYear != selectedYear) {
        document.getElementById('yearFilter').value = data.selectedYear;
      }
      
      this.updateStatsCards(data.counts);
      this.renderCharts(data);
      this.renderMap(data.map);
      this.renderAktivitasTerbaru(data.aktivitas_terbaru);
      this.updateTimestamp();
      
      // Animate cards after data load
      this.animateCards();
      
    } catch (error) {
      console.error('Error loading dashboard data:', error);
      this.showError(error.message || 'Gagal memuat data dashboard');
      this.resetStatsCards();
    } finally {
      this.hideLoadingState();
    }
  }

  animateCards() {
    const cards = document.querySelectorAll('#stats-cards > div');
    cards.forEach((card, index) => {
      setTimeout(() => {
        card.classList.add('animate-fade-in');
      }, index * 100);
    });
  }

  updateStatsCards(counts) {
    if (!counts) return;

    const updates = {
      'permohonan-count': counts.permohonan?.toLocaleString('id-ID') || '0',
      'penelaahan-count': counts.penelaahan?.toLocaleString('id-ID') || '0', 
      'layanan-count': counts.layanan?.toLocaleString('id-ID') || '0',
      'pengeluaran-count': counts.pengeluaran_fmt ? `Rp ${counts.pengeluaran_fmt}` : 'Rp 0'
    };

    Object.entries(updates).forEach(([id, value]) => {
      const element = document.getElementById(id);
      if (element) {
        // Add counter animation
        this.animateCounter(element, value);
      }
    });
  }

  animateCounter(element, finalValue) {
    const isRupiah = finalValue.includes('Rp');
    const numericValue = isRupiah ? 
      parseInt(finalValue.replace(/[^\d]/g, '')) : 
      parseInt(finalValue.replace(/[^\d]/g, ''));
    
    if (isNaN(numericValue)) {
      element.textContent = finalValue;
      return;
    }

    let current = 0;
    const increment = Math.ceil(numericValue / 20);
    
    const timer = setInterval(() => {
      current += increment;
      if (current >= numericValue) {
        current = numericValue;
        clearInterval(timer);
      }
      
      if (isRupiah) {
        element.textContent = `Rp ${current.toLocaleString('id-ID')}`;
      } else {
        element.textContent = current.toLocaleString('id-ID');
      }
    }, 50);
  }

  resetStatsCards() {
    const resets = {
      'permohonan-count': '0',
      'penelaahan-count': '0',
      'layanan-count': '0', 
      'pengeluaran-count': 'Rp 0'
    };

    Object.entries(resets).forEach(([id, value]) => {
      const element = document.getElementById(id);
      if (element) {
        element.textContent = value;
      }
    });
  }

updateStatsCards(counts) {
    if (!counts) return;

    const updates = {
        'permohonan-count': counts.permohonan?.toLocaleString('id-ID') || '0',
        'penelaahan-count': counts.penelaahan?.toLocaleString('id-ID') || '0', 
        'layanan-count': counts.layanan?.toLocaleString('id-ID') || '0',
        'pengeluaran-count': counts.pengeluaran_fmt ? `Rp ${counts.pengeluaran_fmt}` : 'Rp 0'
    };

    Object.entries(updates).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            this.animateCounter(element, value);
        }
    });

    // Update persentase perubahan
    this.updateChangeIndicator('permohonan-change', counts.permohonan_change, 'blue');
    this.updateChangeIndicator('penelaahan-change', counts.penelaahan_change, 'green');
    this.updateChangeIndicator('layanan-change', counts.layanan_change, 'amber');
    this.updateChangeIndicator('pengeluaran-change', counts.pengeluaran_change, 'red');
}

updateChangeIndicator(elementId, changeValue, color) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const isPositive = changeValue >= 0;
    const arrowIcon = isPositive ? 'fa-arrow-up' : 'fa-arrow-down';
    const sign = isPositive ? '+' : '';
    
    element.innerHTML = `
        <i class="fas ${arrowIcon} mr-1"></i>
        ${sign}${changeValue}% dari bulan lalu
    `;
    
    // Update warna berdasarkan nilai
    if (color === 'red') {
        // Untuk pengeluaran, nilai negatif adalah baik (pengeluaran menurun)
        element.className = `text-xs ${isPositive ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'} mt-2 font-medium`;
    } else {
        // Untuk lainnya, nilai positif adalah baik
        element.className = `text-xs ${isPositive ? 'text-${color}-600 dark:text-${color}-400' : 'text-red-600 dark:text-red-400'} mt-2 font-medium`;
    }
}

  renderCharts(data) {
    if (!data.charts) return;

    this.renderPermohonanChart(data.charts.permohonan_line);
    this.renderAnggaranChart(data.anggaran);
    this.renderBebanKerjaChart(data.charts.beban_kerja);
  }

  renderPermohonanChart(chartData) {
    // Destroy existing chart
    if (this.charts.permohonan) {
      this.charts.permohonan.destroy();
    }

    const ctx = document.getElementById('chartPermohonan');
    if (!ctx || !chartData) return;

    this.charts.permohonan = new Chart(ctx, {
      type: 'line',
      data: {
        labels: chartData.labels || [],
        datasets: [
          {
            label: 'Permohonan',
            data: chartData.permohonan || [],
            borderColor: this.colors.primary[0],
            backgroundColor: this.colors.backgrounds[0],
            tension: 0.4,
            fill: true,
            borderWidth: 4,
            pointBackgroundColor: this.colors.primary[0],
            pointBorderColor: '#ffffff',
            pointBorderWidth: 3,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointHoverBorderWidth: 4
          },
          {
            label: 'Penelaahan', 
            data: chartData.penelaahan || [],
            borderColor: this.colors.primary[1],
            backgroundColor: this.colors.backgrounds[1],
            tension: 0.4,
            fill: true,
            borderWidth: 4,
            pointBackgroundColor: this.colors.primary[1],
            pointBorderColor: '#ffffff',
            pointBorderWidth: 3,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointHoverBorderWidth: 4
          },
          {
            label: 'Layanan',
            data: chartData.layanan || [],
            borderColor: this.colors.primary[2],
            backgroundColor: this.colors.backgrounds[2],
            tension: 0.4,
            fill: true,
            borderWidth: 4,
            pointBackgroundColor: this.colors.primary[2],
            pointBorderColor: '#ffffff',
            pointBorderWidth: 3,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointHoverBorderWidth: 4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          intersect: false,
          mode: 'index'
        },
        plugins: { 
          legend: { 
            display: false // Legend sudah ada di HTML
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            titleColor: '#ffffff',
            bodyColor: '#ffffff',
            borderColor: '#374151',
            borderWidth: 2,
            cornerRadius: 12,
            displayColors: true,
            usePointStyle: true,
            titleFont: {
              size: 14,
              weight: 'bold'
            },
            bodyFont: {
              size: 13
            },
            padding: 12
          }
        },
        scales: { 
          y: { 
            beginAtZero: true,
            ticks: {
              precision: 0,
              color: '#6B7280',
              font: {
                size: 12
              }
            },
            grid: {
              color: 'rgba(229, 231, 235, 0.8)',
              drawBorder: false
            }
          },
          x: {
            ticks: {
              color: '#6B7280',
              font: {
                size: 12
              }
            },
            grid: {
              color: 'rgba(229, 231, 235, 0.8)',
              drawBorder: false
            }
          }
        },
        animation: {
          duration: 2000,
          easing: 'easeInOutQuart'
        }
      }
    });
  }

  renderAnggaranChart(anggaranData) {
    // Destroy existing chart
    if (this.charts.anggaran) {
      this.charts.anggaran.destroy();
    }

    const ctx = document.getElementById('chartAnggaran');
    if (!ctx || !anggaranData) return;

    // Update center text untuk menampilkan SISA ANGGARAN
    const centerText = document.getElementById('total-anggaran-text');
    if (centerText) {
      centerText.textContent = anggaranData.sisa_fmt ? `Rp ${anggaranData.sisa_fmt}` : 'Rp 0';
      
      // Tambahkan teks persentase sisa anggaran
      const percentageText = document.createElement('div');
      percentageText.className = 'text-xs text-gray-500 dark:text-gray-400 mt-2';
      const persentaseSisa = anggaranData.total > 0 ? 
        Math.round((anggaranData.sisa / anggaranData.total) * 100) : 0;
      percentageText.textContent = `${persentaseSisa}% tersisa`;
      
      // Hapus teks persentase lama jika ada
      const oldPercentage = centerText.nextElementSibling;
      if (oldPercentage && oldPercentage.className.includes('text-xs')) {
        oldPercentage.remove();
      }
      
      centerText.after(percentageText);
    }

    // Prepare chart data - gunakan data PENGELUARAN untuk bagian chart
    const labels = [];
    const values = [];
    const backgroundColors = [];
    
    if (anggaranData.per_kode && anggaranData.per_kode.length > 0) {
      anggaranData.per_kode.forEach((item, index) => {
        if (item.pengeluaran > 0) { // Hanya tampilkan yang ada pengeluarannya
          labels.push(`${item.kode} - ${item.nama}`);
          values.push(item.pengeluaran || 0);
          backgroundColors.push(this.colors.primary[index % this.colors.primary.length]);
        }
      });
    }

    // Jika tidak ada pengeluaran, tampilkan chart kosong
    if (values.length === 0) {
      this.charts.anggaran = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Belum ada pengeluaran'],
          datasets: [{
            data: [1],
            backgroundColor: ['#e5e7eb'],
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '75%',
          plugins: {
            legend: { display: false },
            tooltip: { enabled: false }
          }
        }
      });
      
      this.renderAnggaranDetail(anggaranData.per_kode || []);
      return;
    }

    this.charts.anggaran = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: backgroundColors,
          borderWidth: 0,
          borderRadius: 8,
          hoverOffset: 15,
          hoverBorderWidth: 4,
          hoverBorderColor: '#ffffff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '75%',
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            titleColor: '#ffffff',
            bodyColor: '#ffffff',
            borderColor: '#374151',
            borderWidth: 2,
            cornerRadius: 12,
            displayColors: true,
            usePointStyle: true,
            titleFont: {
              size: 14,
              weight: 'bold'
            },
            bodyFont: {
              size: 13
            },
            padding: 12,
            callbacks: {
              label: function(context) {
                const value = context.raw || 0;
                const formatted = value.toLocaleString('id-ID');
                const total = values.reduce((sum, val) => sum + val, 0);
                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                
                // Cari data lengkap untuk item ini
                const itemIndex = context.dataIndex;
                const itemData = anggaranData.per_kode.find(item => 
                  `${item.kode} - ${item.nama}` === context.label
                );
                
                const sisa = itemData ? itemData.sisa_fmt : '0';
                return [
                  `${context.label}:`,
                  `Pengeluaran: Rp ${formatted} (${percent}%)`,
                  `Sisa: Rp ${sisa}`
                ];
              }
            }
          }
        },
        animation: {
          animateRotate: true,
          duration: 2000,
          easing: 'easeInOutQuart'
        }
      }
    });
    
    // Render detail breakdown
    this.renderAnggaranDetail(anggaranData.per_kode || []);
  }

  renderAnggaranDetail(anggaranData) {
    const detailContainer = document.getElementById('anggaran-detail');
    if (!detailContainer) return;
    
    let html = '';
    
    if (anggaranData.length === 0) {
      html = '<div class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Tidak ada data anggaran untuk tahun ini</div>';
    } else {
      // Urutkan berdasarkan pengeluaran terbesar
      anggaranData.sort((a, b) => b.pengeluaran - a.pengeluaran);
      
      anggaranData.forEach((item, index) => {
        const color = this.colors.primary[index % this.colors.primary.length];
        const persentaseSisa = item.total > 0 ? 
          Math.round((item.sisa / item.total) * 100) : 0;
        const persentasePenggunaan = item.total > 0 ? 
          Math.round((item.pengeluaran / item.total) * 100) : 0;
        
        // Progress bar untuk visual representation
        const progressWidth = Math.min(persentasePenggunaan, 100);
        
        html += `
          <div class="group relative overflow-hidden bg-gradient-to-r from-gray-50 to-white dark:from-gray-700/30 dark:to-gray-800/30 rounded-2xl p-4 hover:shadow-lg transition-all duration-300 border border-gray-100 dark:border-gray-600/30">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center space-x-3 flex-1 min-w-0">
                <div class="w-4 h-4 rounded-full flex-shrink-0 shadow-sm" style="background: linear-gradient(135deg, ${color}, ${color}dd)"></div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-bold text-gray-700 dark:text-gray-200 truncate">${item.kode}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400 truncate">${item.nama}</div>
                </div>
              </div>
              <div class="text-right ml-3">
                <div class="text-sm font-bold ${persentaseSisa > 50 ? 'text-green-600 dark:text-green-400' : persentaseSisa > 25 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400'}">
                  Rp ${item.sisa_fmt}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  ${persentaseSisa}% tersisa
                </div>
              </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="relative">
              <div class="w-full h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-1000 ease-out" 
                     style="width: ${progressWidth}%; background: linear-gradient(90deg, ${color}, ${color}aa)"></div>
              </div>
              <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                <span>Digunakan: ${persentasePenggunaan}%</span>
                <span>Total: Rp ${item.total_fmt}</span>
              </div>
            </div>
          </div>
        `;
      });
    }
    
    detailContainer.innerHTML = html;
  }

  renderBebanKerjaChart(chartData) {
    // Destroy existing chart
    if (this.charts.bebanKerja) {
        this.charts.bebanKerja.destroy();
    }

    const ctx = document.getElementById('chartBebanKerja');
    if (!ctx || !chartData) return;

    this.charts.bebanKerja = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels || [],
            datasets: chartData.datasets?.map((dataset, index) => ({
                label: dataset.label,
                data: dataset.data || [],
                backgroundColor: dataset.backgroundColor || this.colors.primary[index],
                borderColor: dataset.backgroundColor || this.colors.primary[index],
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
                hoverBackgroundColor: dataset.backgroundColor ? 
                    dataset.backgroundColor.replace('0.7', '0.9') : 
                    this.colors.primary[index].replace('0.7', '0.9'),
                hoverBorderWidth: 3
            })) || []
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 25,
                        color: '#6B7280',
                        font: {
                            size: 13,
                            weight: '600'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#374151',
                    borderWidth: 2,
                    cornerRadius: 12,
                    displayColors: true,
                    usePointStyle: true,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw} tugas`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    ticks: {
                        color: '#6B7280',
                        font: {
                            size: 12
                        },
                        maxRotation: 45
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: '#6B7280',
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(229, 231, 235, 0.8)',
                        drawBorder: false
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });
  }

  renderAktivitasTerbaru(aktivitasData) {
    const container = document.getElementById('aktivitas-container');
    if (!container || !aktivitasData) return;

    let html = '';

    if (aktivitasData.length === 0) {
        html = `
            <div class="col-span-3 text-center py-12 text-gray-500 dark:text-gray-400">
                <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                    <i class="fas fa-inbox text-3xl opacity-50"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Tidak Ada Aktivitas</h3>
                <p class="text-sm">Belum ada aktivitas terbaru untuk ditampilkan</p>
            </div>
        `;
    } else {
        aktivitasData.forEach((aktivitas, index) => {
            const warnaKelas = {
                'blue': 'from-blue-400 to-blue-600 text-white',
                'green': 'from-green-400 to-green-600 text-white',
                'amber': 'from-amber-400 to-amber-600 text-white'
            };

            const gradientClass = warnaKelas[aktivitas.warna] || 'from-gray-400 to-gray-600 text-white';

            html += `
                <div class="group relative overflow-hidden bg-gradient-to-br from-white to-gray-50 dark:from-gray-800/80 dark:to-gray-900/80 backdrop-blur-sm rounded-2xl p-6 border border-gray-100 dark:border-gray-700/50 shadow-lg hover:shadow-xl transition-all duration-500 hover:-translate-y-1">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r ${gradientClass.replace('text-white', '')}"></div>
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br ${gradientClass} rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas ${aktivitas.icon || 'fa-bell'} text-lg"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    ${aktivitas.aktivitas}
                                </h3>
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-${aktivitas.warna}-100 dark:bg-${aktivitas.warna}-900/20 text-${aktivitas.warna}-700 dark:text-${aktivitas.warna}-300 rounded-full">
                                    ${aktivitas.jenis}
                                </span>
                            </div>
                            <div class="space-y-1 mb-3">
                                <p class="text-xs text-gray-600 dark:text-gray-300 font-mono bg-gray-100 dark:bg-gray-700/50 px-2 py-1 rounded">
                                    <i class="fas fa-hashtag mr-1"></i>${aktivitas.nomor}
                                </p>
                                ${aktivitas.nama_pemohon ? `
                                    <p class="text-xs text-gray-600 dark:text-gray-300">
                                        <i class="fas fa-user mr-1"></i>${aktivitas.nama_pemohon}
                                    </p>
                                ` : ''}
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                    <i class="fas fa-clock mr-1"></i>${aktivitas.waktu}
                                </p>
                                <button class="opacity-0 group-hover:opacity-100 transition-opacity text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                    Lihat Detail
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }

    container.innerHTML = html;
    
    // Animate items
    setTimeout(() => {
      const items = container.querySelectorAll('> div');
      items.forEach((item, index) => {
        setTimeout(() => {
          item.classList.add('animate-fade-in');
        }, index * 100);
      });
    }, 100);
  }

  renderMap(mapData) {
    if (!mapData || !window.DatamapIndonesia) {
      console.warn('Map data or DatamapIndonesia not available');
      return;
    }

    const mapContainer = document.getElementById('map-container');
    const mapLoading = document.getElementById('map-loading');
    
    if (!mapContainer) return;

    // Clear existing map
    const existingSvg = mapContainer.querySelector('svg');
    if (existingSvg) {
      existingSvg.remove();
    }

    try {
      // Prepare map data
      const formattedData = {};
      const allProvinces = ['Aceh', 'Sumatera Utara', 'Sumatera Barat'];
      
      allProvinces.forEach(province => {
        formattedData[province] = {
          value: mapData.provinsi_counts?.[province] || 0,
          fillKey: mapData.provinsi_fillkeys?.[province] || 'low'
        };
      });

      // Initialize map with enhanced styling
      const map = new DatamapIndonesia({
        element: mapContainer,
        responsive: true,
        geographyConfig: {
          highlightOnHover: true,
          popupOnHover: true,
          highlightBorderWidth: 3,
          highlightBorderColor: '#ffffff',
          highlightFillColor: function(geo) {
            const provinceName = geo.properties.provinsi;
            const item = formattedData[provinceName];
            if (item?.fillKey) {
              const fillColors = {
                high: "#dc2626",
                medium: "#f59e0b", 
                low: "#10b981"
              };
              return fillColors[item.fillKey] || "#e5e7eb";
            }
            return "#e5e7eb";
          },
          borderWidth: 1,
          borderColor: '#ffffff',
          popupTemplate: function(geo, data) {
            const value = data?.value || 0;
            const percentage = mapData.provinsi_counts ? 
              Math.round((value / Object.values(mapData.provinsi_counts).reduce((a, b) => a + b, 0)) * 100) : 0;
            return `
              <div class="backdrop-blur-sm bg-white/90 dark:bg-gray-800/90 p-4 rounded-2xl shadow-2xl border border-white/20 dark:border-gray-700/50 min-w-[200px]">
                <div class="flex items-center space-x-3 mb-3">
                  <div class="w-3 h-3 rounded-full" style="background-color: ${
                    data?.fillKey === 'high' ? '#dc2626' : 
                    data?.fillKey === 'medium' ? '#f59e0b' : '#10b981'
                  }"></div>
                  <h3 class="font-bold text-gray-800 dark:text-white">${geo.properties.provinsi}</h3>
                </div>
                <div class="space-y-2">
                  <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-300">Jumlah Permohonan:</span>
                    <span class="font-bold text-gray-800 dark:text-white">${value.toLocaleString('id-ID')}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-300">Persentase:</span>
                    <span class="font-bold text-blue-600 dark:text-blue-400">${percentage}%</span>
                  </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                  <div class="w-full h-2 bg-gray-200 dark:bg-gray-600 rounded-full">
                    <div class="h-full rounded-full transition-all duration-500" 
                         style="width: ${percentage}%; background: linear-gradient(90deg, ${
                           data?.fillKey === 'high' ? '#dc2626' : 
                           data?.fillKey === 'medium' ? '#f59e0b' : '#10b981'
                         }, ${
                           data?.fillKey === 'high' ? '#ef4444' : 
                           data?.fillKey === 'medium' ? '#fbbf24' : '#34d399'
                         })"></div>
                  </div>
                </div>
              </div>
            `;
          }
        },
        fills: {
          defaultFill: "#e5e7eb",
          high: "#dc2626",
          medium: "#f59e0b", 
          low: "#10b981"
        },
        data: formattedData
      });
      
      // Hide loading
      if (mapLoading) {
        mapLoading.style.display = 'none';
      }
      
      this.renderMapLegend(mapData);
      
    } catch (error) {
      console.error('Error rendering map:', error);
      if (mapLoading) {
        mapLoading.innerHTML = `
          <div class="text-center text-gray-500 dark:text-gray-400 py-12">
            <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
              <i class="fas fa-exclamation-triangle text-3xl text-amber-500"></i>
            </div>
            <h3 class="text-lg font-semibold mb-2">Peta Tidak Tersedia</h3>
            <p class="text-sm">Gagal memuat visualisasi peta</p>
          </div>
        `;
      }
    }
  }

  renderMapLegend(mapData) {
    const legendContainer = document.getElementById('map-legend');
    if (!legendContainer) return;
    
    // Calculate statistics for legend
    const counts = Object.values(mapData.provinsi_counts || {});
    const total = counts.reduce((a, b) => a + b, 0);
    const max = Math.max(...counts, 1);
    
    const legendItems = [
      { 
        key: 'low', 
        label: 'Rendah', 
        color: '#10b981',
        description: `0 - ${Math.round(max * 0.3)} permohonan`,
        count: counts.filter(c => c <= max * 0.3).length
      },
      { 
        key: 'medium', 
        label: 'Sedang', 
        color: '#f59e0b',
        description: `${Math.round(max * 0.3)} - ${Math.round(max * 0.7)} permohonan`,
        count: counts.filter(c => c > max * 0.3 && c <= max * 0.7).length
      },
      { 
        key: 'high', 
        label: 'Tinggi', 
        color: '#dc2626',
        description: `> ${Math.round(max * 0.7)} permohonan`,
        count: counts.filter(c => c > max * 0.7).length
      }
    ];
    
    let html = `
      <div class="flex flex-wrap justify-center items-center gap-6">
        <div class="text-center">
          <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Permohonan</div>
          <div class="text-xl font-bold text-gray-800 dark:text-white">${total.toLocaleString('id-ID')}</div>
        </div>
    `;
    
    legendItems.forEach(item => {
      html += `
        <div class="flex items-center space-x-3 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/20 dark:border-gray-700/50">
          <div class="flex items-center space-x-2">
            <div class="w-4 h-4 rounded-full border-2 border-white shadow-sm" style="background-color: ${item.color}"></div>
            <div>
              <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">${item.label}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">${item.description}</div>
            </div>
          </div>
          <div class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full font-medium">
            ${item.count} provinsi
          </div>
        </div>
      `;
    });
    
    html += '</div>';
    legendContainer.innerHTML = html;
  }
}

// Enhanced utility functions
function formatNumber(num) {
  if (num >= 1000000000) {
    return (num / 1000000000).toFixed(1) + 'M';
  } else if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'Jt';
  } else if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'Rb';
  }
  return num.toLocaleString('id-ID');
}

function formatCurrency(amount) {
  return 'Rp ' + formatNumber(amount);
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Show loading animation on page load
  document.body.classList.add('animate-fade-in');
  
  // Initialize dashboard manager
  new DashboardManager();
  
  // Enhanced theme toggle if available
  const themeToggle = document.querySelector('[data-theme-toggle]');
  if (themeToggle) {
    themeToggle.addEventListener('click', function() {
      document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    });
  }
});
</script>

<style>
/* Enhanced Custom Styles */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

* {
  font-family: 'Inter', sans-serif;
}

/* Enhanced Animations */
@keyframes fadeIn {
  from { 
    opacity: 0; 
    transform: translateY(20px) scale(0.95); 
  }
  to { 
    opacity: 1; 
    transform: translateY(0) scale(1); 
  }
}

@keyframes slideUp {
  from { 
    opacity: 0; 
    transform: translateY(40px); 
  }
  to { 
    opacity: 1; 
    transform: translateY(0); 
  }
}

@keyframes bounceSubtle {
  0%, 100% { 
    transform: translateY(0px); 
  }
  50% { 
    transform: translateY(-3px); 
  }
}

@keyframes shimmer {
  0% {
    background-position: -200px 0;
  }
  100% {
    background-position: calc(200px + 100%) 0;
  }
}

.animate-fade-in {
  animation: fadeIn 0.6s ease-out;
}

.animate-slide-up {
  animation: slideUp 0.8s ease-out;
}

.animate-bounce-subtle {
  animation: bounceSubtle 2s infinite;
}

.shimmer {
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
  background-size: 200px 100%;
  animation: shimmer 1.5s infinite;
}

/* Glass Effect Enhancements */
.glass-effect {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.18);
}

.dark .glass-effect {
  background: rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Enhanced Hover Effects */
.hover-lift {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.hover-lift:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.dark .hover-lift:hover {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
}

/* Enhanced Chart Container */
.chart-container {
  position: relative;
  height: 400px;
}

/* Map Enhancements */
#map-container {
  position: relative;
  min-height: 400px;
  border-radius: 1rem;
  overflow: hidden;
}

#map-container svg {
  width: 100% !important;
  height: 100% !important;
}

/* Enhanced Tooltip Styles */
.datamaps-hoverover {
  z-index: 1001 !important;
  pointer-events: none !important;
  filter: drop-shadow(0 10px 8px rgba(0, 0, 0, 0.04)) drop-shadow(0 4px 3px rgba(0, 0, 0, 0.1));
}

/* Loading States */
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  border-radius: inherit;
}

.dark .loading-overlay {
  background: rgba(31, 41, 55, 0.95);
}

/* Responsive Design Enhancements */
@media (max-width: 768px) {
  #anggaran-center-text .text-2xl {
    font-size: 1.5rem;
  }
  
  .chart-container {
    height: 300px;
  }
  
  #map-container {
    min-height: 300px;
  }
  
  .hover-lift:hover {
    transform: translateY(-4px) scale(1.01);
  }
}

@media (max-width: 640px) {
  .chart-container {
    height: 250px;
  }
  
  #map-container {
    min-height: 250px;
  }
}

/* Enhanced Button Styles */
button {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

button:hover {
  transform: translateY(-1px);
}

button:active {
  transform: translateY(0);
}

/* Progress Bar Animation */
@keyframes progressFill {
  from {
    width: 0%;
  }
  to {
    width: var(--progress-width);
  }
}

.progress-animated {
  animation: progressFill 1.5s ease-out;
}

/* Enhanced Card Shadows */
.card-shadow {
  box-shadow: 
    0 1px 3px 0 rgba(0, 0, 0, 0.1),
    0 1px 2px 0 rgba(0, 0, 0, 0.06),
    0 0 0 1px rgba(255, 255, 255, 0.05);
}

.dark .card-shadow {
  box-shadow: 
    0 1px 3px 0 rgba(0, 0, 0, 0.3),
    0 1px 2px 0 rgba(0, 0, 0, 0.2),
    0 0 0 1px rgba(255, 255, 255, 0.05);
}

/* Smooth transitions for all interactive elements */
* {
  -webkit-tap-highlight-color: transparent;
}

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Enhanced focus states */
button:focus,
select:focus,
input:focus {
  outline: 2px solid rgba(59, 130, 246, 0.5);
  outline-offset: 2px;
}

/* Custom select styling */
select {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  background-size: 1em;
  padding-right: 2.5rem;
}

/* Print styles */
@media print {
  .no-print {
    display: none !important;
  }
  
  .print-break {
    page-break-before: always;
  }
  
  body {
    background: white !important;
  }
  
  .backdrop-blur-sm {
    backdrop-filter: none !important;
    background: white !important;
  }
}
</style>

<?php require __DIR__ . '/../inc/layout_footer.php'; ?>