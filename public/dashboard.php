<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';

require_login();
$title = 'Dashboard';
require __DIR__ . '/../inc/layout_header.php';
require __DIR__ . '/../inc/layout_nav.php';
?>

<div class="container mx-auto px-4 py-6">
  <!-- Header dengan filter tahun -->
  <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">Dashboard</h1>
    
    <div class="flex items-center gap-3">
      <label for="yearFilter" class="text-sm font-medium text-gray-700 dark:text-gray-300">Tahun:</label>
      <select id="yearFilter" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 text-gray-700 dark:text-gray-300 min-w-[100px]">
        <?php
        $currentYear = date('Y');
        for ($year = $currentYear; $year >= 2020; $year--) {
          $selected = $year == $currentYear ? 'selected' : '';
          echo "<option value='$year' $selected>$year</option>";
        }
        ?>
      </select>
    </div>
  </div>

  <!-- Alert untuk error -->
  <div id="error-alert" class="hidden mb-6 p-4 bg-red-100 dark:bg-red-900/20 border border-red-300 dark:border-red-700 rounded-lg">
    <div class="flex items-center">
      <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 mr-2"></i>
      <span class="text-red-800 dark:text-red-200" id="error-message">Terjadi kesalahan saat memuat data.</span>
    </div>
  </div>

  <!-- Kartu ringkasan -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8" id="stats-cards">
    <!-- Card Permohonan -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-gray-100 dark:border-gray-700 border-l-4 border-l-blue-500 hover:shadow-md transition-shadow duration-200">
      <div class="flex justify-between items-start">
        <div class="flex-1">
          <div class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Permohonan</div>
          <div class="text-2xl font-bold text-gray-800 dark:text-white" id="permohonan-count">0</div>
        </div>
        <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex-shrink-0">
          <i class="text-xl fas fa-file-lines"></i>
        </div>
      </div>
    </div>
    
    <!-- Card Penelaahan -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-gray-100 dark:border-gray-700 border-l-4 border-l-green-500 hover:shadow-md transition-shadow duration-200">
      <div class="flex justify-between items-start">
        <div class="flex-1">
          <div class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Penelaahan</div>
          <div class="text-2xl font-bold text-gray-800 dark:text-white" id="penelaahan-count">0</div>
        </div>
        <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex-shrink-0">
          <i class="text-xl fas fa-magnifying-glass"></i>
        </div>
      </div>
    </div>
    
    <!-- Card Layanan -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-gray-100 dark:border-gray-700 border-l-4 border-l-amber-500 hover:shadow-md transition-shadow duration-200">
      <div class="flex justify-between items-start">
        <div class="flex-1">
          <div class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Layanan</div>
          <div class="text-2xl font-bold text-gray-800 dark:text-white" id="layanan-count">0</div>
        </div>
        <div class="p-2 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex-shrink-0">
          <i class="text-xl fas fa-handshake"></i>
        </div>
      </div>
    </div>
    
    <!-- Card Pengeluaran -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-gray-100 dark:border-gray-700 border-l-4 border-l-purple-500 hover:shadow-md transition-shadow duration-200">
      <div class="flex justify-between items-start">
        <div class="flex-1">
          <div class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Pengeluaran</div>
          <div class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white" id="pengeluaran-count">Rp 0</div>
        </div>
        <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex-shrink-0">
          <i class="text-xl fas fa-coins"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Grafik utama -->
  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
    <!-- Chart Multi-Line untuk Statistik Permohonan -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-5 gap-3">
        <h2 class="font-semibold text-lg text-gray-800 dark:text-white">Statistik Permohonan</h2>
        <div class="flex flex-wrap items-center text-sm gap-3">
          <span class="flex items-center">
            <span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>
            <span class="text-gray-600 dark:text-gray-400">Permohonan</span>
          </span>
          <span class="flex items-center">
            <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
            <span class="text-gray-600 dark:text-gray-400">Penelaahan</span>
          </span>
          <span class="flex items-center">
            <span class="w-3 h-3 rounded-full bg-amber-500 mr-2"></span>
            <span class="text-gray-600 dark:text-gray-400">Layanan</span>
          </span>
        </div>
      </div>
      <div class="relative">
        <canvas id="chartPermohonan" class="w-full h-72"></canvas>
        <div id="loading-chart-permohonan" class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800 rounded-lg">
          <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
        </div>
      </div>
    </div>

    <!-- Doughnut Chart untuk Distribusi Anggaran -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
      <h2 class="font-semibold text-lg text-gray-800 dark:text-white mb-5 text-center">Distribusi Anggaran</h2>
      <div class="relative">
        <div class="relative">
          <canvas id="chartAnggaran" class="w-full h-64"></canvas>
          <div id="anggaran-center-text" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
            <div class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white" id="total-anggaran-text">Rp 0</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Anggaran</div>
          </div>
        </div>
        <div id="loading-chart-anggaran" class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800 rounded-lg">
          <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
        </div>
      </div>
      
      <!-- Detail anggaran per kode -->
      <div class="mt-4">
        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Detail per Kode:</div>
        <div id="anggaran-detail" class="space-y-2 max-h-40 overflow-y-auto">
          <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Memuat data...</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Chart keuangan dan Peta -->
  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
    <!-- Chart Line untuk Statistik Keuangan -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-5 gap-3">
        <h2 class="font-semibold text-lg text-gray-800 dark:text-white">Statistik Keuangan per Anggaran</h2>
      </div>
      <div class="relative">
        <canvas id="chartPengeluaran" class="w-full h-72"></canvas>
        <div id="loading-chart-pengeluaran" class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800 rounded-lg">
          <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
        </div>
      </div>
    </div>

    <!-- Peta Provinsi -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
      <h2 class="font-semibold text-lg text-gray-800 dark:text-white mb-5">Distribusi Permohonan per Provinsi</h2>
      <div class="relative">
        <div id="map-container" class="h-80 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700"></div>
        <div id="map-loading" class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
          <div class="text-center">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
            <p class="text-gray-500 dark:text-gray-400">Memuat peta...</p>
          </div>
        </div>
      </div>
      <div id="map-legend" class="flex flex-wrap justify-center gap-4 mt-4"></div>
    </div>
  </div>

  <!-- Aktivitas terbaru -->
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
    <h2 class="font-semibold text-lg text-gray-800 dark:text-white mb-5">Aktivitas Terbaru</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div class="flex items-start p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
        <div class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 p-2 rounded-lg mr-3 flex-shrink-0">
          <i class="fas fa-file-import"></i>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 dark:text-white truncate">Permohonan baru diterima</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Nomor: PMH-2023-0876</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">2 jam yang lalu</p>
        </div>
      </div>
      
      <div class="flex items-start p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
        <div class="bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 p-2 rounded-lg mr-3 flex-shrink-0">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 dark:text-white truncate">Penelaahan selesai</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Nomor: PNL-2023-0543</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">5 jam yang lalu</p>
        </div>
      </div>
      
      <div class="flex items-start p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
        <div class="bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 p-2 rounded-lg mr-3 flex-shrink-0">
          <i class="fas fa-coins"></i>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 dark:text-white truncate">Pengeluaran baru dicatat</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Nomor: KWT-2023-0321</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">1 hari yang lalu</p>
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
      '<i class="fas fa-map-marked-alt text-4xl mb-2 opacity-50"></i>' +
      '<p>Peta tidak tersedia</p>' +
      '<p class="text-xs">Library datamaps.indonesia.min.js tidak ditemukan</p>' +
      '</div>';
  };
  document.head.appendChild(script);
})();
</script>

<script>
class DashboardManager {
  constructor() {
    this.charts = {
      permohonan: null,
      pengeluaran: null,
      anggaran: null
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
  }

  setupEventListeners() {
    // Event listener untuk filter tahun
    const yearFilter = document.getElementById('yearFilter');
    if (yearFilter) {
      yearFilter.addEventListener('change', () => {
        this.loadDashboardData();
      });
    }
  }

  showError(message) {
    const errorAlert = document.getElementById('error-alert');
    const errorMessage = document.getElementById('error-message');
    
    if (errorAlert && errorMessage) {
      errorMessage.textContent = message;
      errorAlert.classList.remove('hidden');
      
      // Auto hide after 5 seconds
      setTimeout(() => {
        errorAlert.classList.add('hidden');
      }, 5000);
    }
  }

  hideError() {
    const errorAlert = document.getElementById('error-alert');
    if (errorAlert) {
      errorAlert.classList.add('hidden');
    }
  }

  showLoadingState() {
    // Show loading for cards
    const statsCards = document.getElementById('stats-cards');
    if (statsCards) {
      statsCards.classList.add('opacity-60');
      
      const countElements = ['permohonan-count', 'penelaahan-count', 'layanan-count', 'pengeluaran-count'];
      countElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
          element.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i>';
        }
      });
    }

    // Show loading for charts
    const loadingElements = [
      'loading-chart-permohonan',
      'loading-chart-anggaran', 
      'loading-chart-pengeluaran'
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
      'loading-chart-pengeluaran'
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
      
      this.updateStatsCards(data.counts);
      this.renderCharts(data);
      this.renderMap(data.map);
      
    } catch (error) {
      console.error('Error loading dashboard data:', error);
      this.showError(error.message || 'Gagal memuat data dashboard');
      this.resetStatsCards();
    } finally {
      this.hideLoadingState();
    }
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
        element.textContent = value;
      }
    });
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

  renderCharts(data) {
    if (!data.charts) return;

    this.renderPermohonanChart(data.charts.permohonan_line);
    this.renderKeuanganChart(data.charts.keuangan);
    this.renderAnggaranChart(data.anggaran);
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
            borderWidth: 3,
            pointBackgroundColor: this.colors.primary[0],
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5
          },
          {
            label: 'Penelaahan', 
            data: chartData.penelaahan || [],
            borderColor: this.colors.primary[1],
            backgroundColor: this.colors.backgrounds[1],
            tension: 0.4,
            fill: true,
            borderWidth: 3,
            pointBackgroundColor: this.colors.primary[1],
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5
          },
          {
            label: 'Layanan',
            data: chartData.layanan || [],
            borderColor: this.colors.primary[2],
            backgroundColor: this.colors.backgrounds[2],
            tension: 0.4,
            fill: true,
            borderWidth: 3,
            pointBackgroundColor: this.colors.primary[2],
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5
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
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#ffffff',
            bodyColor: '#ffffff',
            borderColor: '#374151',
            borderWidth: 1
          }
        },
        scales: { 
          y: { 
            beginAtZero: true,
            ticks: {
              precision: 0,
              color: '#6B7280'
            },
            grid: {
              color: '#E5E7EB'
            }
          },
          x: {
            ticks: {
              color: '#6B7280'
            },
            grid: {
              color: '#E5E7EB'
            }
          }
        }
      }
    });
  }

  renderKeuanganChart(chartData) {
    // Destroy existing chart
    if (this.charts.pengeluaran) {
      this.charts.pengeluaran.destroy();
    }

    const ctx = document.getElementById('chartPengeluaran');
    if (!ctx || !chartData) return;

    // Format labels untuk bulan
    const labels = chartData.labels?.map(label => {
      const [year, month] = label.split('-');
      const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      return monthNames[parseInt(month) - 1] || label;
    }) || [];
    
    this.charts.pengeluaran = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: chartData.datasets?.map((dataset, index) => ({
          label: dataset.label,
          data: dataset.data || [],
          borderColor: this.colors.primary[index % this.colors.primary.length],
          backgroundColor: this.colors.backgrounds[index % this.colors.backgrounds.length],
          tension: 0.4,
          fill: true,
          borderWidth: 3,
          pointBackgroundColor: this.colors.primary[index % this.colors.primary.length],
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 4
        })) || []
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
            position: 'bottom',
            labels: {
              usePointStyle: true,
              padding: 20,
              color: '#6B7280'
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#ffffff',
            bodyColor: '#ffffff',
            borderColor: '#374151',
            borderWidth: 1,
            callbacks: {
              label: function(context) {
                let value = context.raw || 0;
                let formatted = '';
                
                if (value >= 1000000000) {
                  formatted = 'Rp ' + (value / 1000000000).toFixed(1) + 'M';
                } else if (value >= 1000000) {
                  formatted = 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                } else if (value >= 1000) {
                  formatted = 'Rp ' + (value / 1000).toFixed(1) + 'Rb';
                } else {
                  formatted = 'Rp ' + value.toLocaleString('id-ID');
                }
                
                return context.dataset.label + ': ' + formatted;
              }
            }
          }
        },
        scales: { 
          y: { 
            beginAtZero: true,
            ticks: {
              color: '#6B7280',
              callback: function(value) {
                if (value >= 1000000000) {
                  return 'Rp ' + (value / 1000000000).toFixed(1) + 'M';
                } else if (value >= 1000000) {
                  return 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                } else if (value >= 1000) {
                  return 'Rp ' + (value / 1000).toFixed(1) + 'Rb';
                }
                return 'Rp ' + value.toLocaleString('id-ID');
              }
            },
            grid: {
              color: '#E5E7EB'
            }
          },
          x: {
            ticks: {
              color: '#6B7280'
            },
            grid: {
              color: '#E5E7EB'
            }
          }
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

    // Update center text
    const centerText = document.getElementById('total-anggaran-text');
    if (centerText) {
      centerText.textContent = anggaranData.total_fmt ? `Rp ${anggaranData.total_fmt}` : 'Rp 0';
    }

    // Prepare chart data
    const labels = [];
    const values = [];
    
    if (anggaranData.per_kode && anggaranData.per_kode.length > 0) {
      anggaranData.per_kode.forEach(item => {
        labels.push(`${item.kode} - ${item.nama}`);
        values.push(item.total || 0);
      });
    }

    this.charts.anggaran = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: this.colors.primary.slice(0, labels.length),
          borderWidth: 0,
          borderRadius: 8,
          hoverOffset: 12,
          hoverBorderWidth: 3,
          hoverBorderColor: '#ffffff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#ffffff',
            bodyColor: '#ffffff',
            borderColor: '#374151',
            borderWidth: 1,
            callbacks: {
              label: function(context) {
                const value = context.raw || 0;
                const formatted = value.toLocaleString('id-ID');
                const percentage = context.parsed || 0;
                return `${context.label}: Rp ${formatted} (${percentage.toFixed(1)}%)`;
              }
            }
          }
        },
        animation: {
          animateRotate: true,
          duration: 1000
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
      html = '<div class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Tidak ada data anggaran untuk tahun ini</div>';
    } else {
      anggaranData.forEach((item, index) => {
        const color = this.colors.primary[index % this.colors.primary.length];
        
        html += `
          <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <div class="flex items-center flex-1 min-w-0">
              <span class="w-3 h-3 rounded-full flex-shrink-0 mr-3" style="background-color: ${color}"></span>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">${item.kode}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">${item.nama}</div>
              </div>
            </div>
            <div class="text-sm font-semibold text-gray-600 dark:text-gray-400 ml-3">
              Rp ${item.total_fmt}
            </div>
          </div>
        `;
      });
    }
    
    detailContainer.innerHTML = html;
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

      // Initialize map
      const map = new DatamapIndonesia({
        element: mapContainer,
        responsive: true,
        geographyConfig: {
          highlightOnHover: true,
          popupOnHover: true,
          highlightBorderWidth: 2,
          highlightBorderColor: '#ffffff',
          highlightFillColor: function(geo) {
            const provinceName = geo.properties.provinsi;
            const item = formattedData[provinceName];
            if (item?.fillKey) {
              const fillColors = {
                high: "#dc2626",
                medium: "#f59e0b", 
                low: "#84cc16"
              };
              return fillColors[item.fillKey] || "#e5e7eb";
            }
            return "#e5e7eb";
          },
          popupTemplate: function(geo, data) {
            const value = data?.value || 0;
            return `<div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-lg border">
              <strong class="text-gray-800 dark:text-white">${geo.properties.provinsi}</strong><br/>
              <span class="text-gray-600 dark:text-gray-300">Permohonan: ${value}</span>
            </div>`;
          }
        },
        fills: {
          defaultFill: "#e5e7eb",
          high: "#dc2626",
          medium: "#f59e0b", 
          low: "#84cc16"
        },
        data: formattedData
      });
      
      // Hide loading
      if (mapLoading) {
        mapLoading.style.display = 'none';
      }
      
      this.renderMapLegend();
      
    } catch (error) {
      console.error('Error rendering map:', error);
      if (mapLoading) {
        mapLoading.innerHTML = `
          <div class="text-center text-gray-500 dark:text-gray-400">
            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
            <p>Gagal memuat peta</p>
          </div>
        `;
      }
    }
  }

  renderMapLegend() {
    const legendContainer = document.getElementById('map-legend');
    if (!legendContainer) return;
    
    const legendItems = [
      { key: 'low', label: 'Rendah', color: '#84cc16' },
      { key: 'medium', label: 'Sedang', color: '#f59e0b' },
      { key: 'high', label: 'Tinggi', color: '#dc2626' }
    ];
    
    let html = '';
    legendItems.forEach(item => {
      html += `
        <div class="flex items-center gap-2 text-sm">
          <span class="w-4 h-4 rounded border border-gray-300" style="background-color: ${item.color}"></span>
          <span class="text-gray-600 dark:text-gray-400">${item.label}</span>
        </div>
      `;
    });
    
    legendContainer.innerHTML = html;
  }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  new DashboardManager();
});
</script>

<style>
/* Custom styles for dashboard */
.chart-container {
  position: relative;
  height: 300px;
}

/* Map styles */
#map-container {
  position: relative;
  min-height: 320px;
}

#map-container svg {
  width: 100% !important;
  height: 100% !important;
}

/* Tooltip styles */
.datamaps-hoverover {
  z-index: 1000 !important;
  pointer-events: none !important;
}

/* Loading states */
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.dark .loading-overlay {
  background: rgba(31, 41, 55, 0.9);
}

/* Chart responsiveness */
@media (max-width: 768px) {
  #anggaran-center-text .text-2xl {
    font-size: 1.25rem;
  }
  
  .chart-container {
    height: 250px;
  }
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Custom scrollbar for anggaran detail */
#anggaran-detail::-webkit-scrollbar {
  width: 4px;
}

#anggaran-detail::-webkit-scrollbar-track {
  background: transparent;
}

#anggaran-detail::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 2px;
}

#anggaran-detail::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.8);
}
</style>

<?php require __DIR__ . '/../inc/layout_footer.php'; ?>