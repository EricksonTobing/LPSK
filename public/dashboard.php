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
         <!-- Include Filter Header Component -->
    <?php include __DIR__ . '/components/filter-header.php'; ?>
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
<?php include __DIR__ . '/components/alert.php'; ?>


<!-- Enhanced KPI Cards -->
<?php include __DIR__ . '/components/kpi-cards.php'; ?>

<!-- Tab Navigation -->
<?php include __DIR__ . '/components/tab-navigation.php'; ?>


<!-- Tab Content Container -->
<div id="tab-content">
        <?php include __DIR__ . '/components/tab-overview.php'; ?>
    <?php include __DIR__ . '/components/tab-perlindungan.php'; ?>
    <?php include __DIR__ . '/components/tab-demografi.php'; ?>
    <?php include __DIR__ . '/components/tab-geografis.php'; ?>
    <?php include __DIR__ . '/components/tab-keuangan.php'; ?>
    <?php include __DIR__ . '/components/tab-aktivitas.php'; ?>
    
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
    const mapLoadingElement = document.getElementById('map-loading');
    if (mapLoadingElement) {
      mapLoadingElement.innerHTML = 
        '<div class="text-center text-gray-500 dark:text-gray-400 py-12">' +
        '<div class="w-20 h-20 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">' +
        '<i class="fas fa-map-marked-alt text-5xl opacity-50"></i>' +
        '</div>' +
        '<h3 class="text-lg font-semibold mb-2">Peta Tidak Tersedia</h3>' +
        '<p class="text-sm">Library datamaps.indonesia.min.js tidak ditemukan.</p>' +
        '</div>';
    }
  };
  document.head.appendChild(script);
})();
</script>

<script>


class TabManager {
    constructor() {
        this.currentTab = 'overview';
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadTabState();
    }

    setupEventListeners() {
      // Event listener untuk filter rentang waktu
    const dateRangeFilter = document.getElementById('dateRangeFilter');
    if (dateRangeFilter) {
        dateRangeFilter.addEventListener('change', () => {
            this.updateFilterVisibility();
            this.loadDashboardData();
            this.updateFilterIndicator();
        });
    }

    // Event listener untuk tanggal kustom
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    
    if (startDate) {
        startDate.addEventListener('change', () => {
            if (document.getElementById('dateRangeFilter').value === 'custom') {
                this.loadDashboardData();
                this.updateFilterIndicator();
            }
        });
    }
    
    if (endDate) {
        endDate.addEventListener('change', () => {
            if (document.getElementById('dateRangeFilter').value === 'custom') {
                this.loadDashboardData();
                this.updateFilterIndicator();
            }
        });
    }



        // Event listener untuk tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', (e) => {
                const tab = e.target.closest('.tab-button').dataset.tab;
                this.switchTab(tab);
            });
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.altKey) {
                switch(e.key) {
                    case '1': e.preventDefault(); this.switchTab('overview'); break;
                    case '2': e.preventDefault(); this.switchTab('perlindungan'); break;
                    case '3': e.preventDefault(); this.switchTab('demografi'); break;
                    case '4': e.preventDefault(); this.switchTab('geografis'); break;
                    case '5': e.preventDefault(); this.switchTab('keuangan'); break;
                    case '6': e.preventDefault(); this.switchTab('aktivitas'); break;
                }
            }
        });
    }

    switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            const isActive = button.dataset.tab === tabName;
            button.classList.toggle('bg-gradient-to-r', isActive);
            button.classList.toggle('from-primary-blue', isActive);
            button.classList.toggle('to-primary-red', isActive);
            button.classList.toggle('text-white', isActive);
            button.classList.toggle('shadow-lg', isActive);
            button.classList.toggle('bg-white/50', !isActive);
            button.classList.toggle('dark:bg-gray-700/50', !isActive);
            button.classList.toggle('text-gray-700', !isActive);
            button.classList.toggle('dark:text-gray-300', !isActive);
        });

        // Update tab panels
        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.toggle('active', panel.dataset.tab === tabName);
            panel.classList.toggle('hidden', panel.dataset.tab !== tabName);
        });

        // Animate tab transition
        this.animateTabTransition(tabName);

        // Save to localStorage
        localStorage.setItem('dashboardActiveTab', tabName);
        this.currentTab = tabName;

        // Load tab-specific data jika diperlukan
        this.loadTabData(tabName);
    }

    animateTabTransition(tabName) {
        const activePanel = document.querySelector(`.tab-panel[data-tab="${tabName}"]`);
        if (activePanel) {
            activePanel.style.opacity = '0';
            activePanel.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                activePanel.style.transition = 'all 0.5s ease-out';
                activePanel.style.opacity = '1';
                activePanel.style.transform = 'translateY(0)';
            }, 50);
        }
    }

    loadTabState() {
        const savedTab = localStorage.getItem('dashboardActiveTab');
        if (savedTab && document.querySelector(`.tab-button[data-tab="${savedTab}"]`)) {
            this.switchTab(savedTab);
        }
    }

    loadTabData(tabName) {
        // Jika tab keuangan dipilih, render detail anggaran yang lebih lengkap
        if (tabName === 'keuangan') {
            this.renderFullAnggaranDetail();
        }
        
        // Jika tab baru dipilih, refresh charts yang mungkin perlu update
        setTimeout(() => {
            const chartsToRefresh = this.getChartsForTab(tabName);
            chartsToRefresh.forEach(chartId => {
                if (window.dashboardManager?.charts[chartId]) {
                    // Trigger chart resize untuk menangani responsive layout
                    setTimeout(() => {
                        if (window.dashboardManager.charts[chartId]) {
                            window.dashboardManager.charts[chartId].resize();
                        }
                    }, 100);
                }
            });
        }, 300);
    }

    getChartsForTab(tabName) {
        const chartMap = {
            'overview': ['permohonan', 'anggaran', 'statusHukum', 'tindakPidana'],
            'perlindungan': ['perlindunganComparison', 'perlindunganPermohonan', 'perlindunganLayanan'],
            'demografi': ['genderCombined'],
            'geografis': [], // Map doesn't use Chart.js
            'keuangan': [], // Data tables
            'aktivitas': [] // Activity lists
        };
        
        return chartMap[tabName] || [];
    }

    renderFullAnggaranDetail() {
        const container = document.getElementById('anggaran-detail-full');
        if (!container || !window.dashboardManager?.lastData?.anggaran) return;

        const anggaranData = window.dashboardManager.lastData.anggaran.per_kode || [];
        
        let html = `
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-2xl">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">${window.dashboardManager.lastData.anggaran.total_fmt}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Anggaran</div>
                </div>
                <div class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-2xl">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">${window.dashboardManager.lastData.anggaran.pengeluaran_fmt}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Pengeluaran</div>
                </div>
                <div class="text-center p-6 bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20 rounded-2xl">
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">${window.dashboardManager.lastData.anggaran.sisa_fmt}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Sisa Anggaran</div>
                </div>
            </div>
        `;

        if (anggaranData.length > 0) {
            html += `
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50">
                                <th class="p-3 text-left font-semibold text-gray-700 dark:text-gray-300">Kode</th>
                                <th class="p-3 text-left font-semibold text-gray-700 dark:text-gray-300">Nama Anggaran</th>
                                <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-300">Total</th>
                                <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-300">Pengeluaran</th>
                                <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-300">Sisa</th>
                                <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-300">Persentase</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            `;

            anggaranData.forEach(item => {
                const percentage = item.persentase_penggunaan || 0;
                const percentageColor = percentage >= 80 ? 'text-red-600' : 
                                      percentage >= 60 ? 'text-amber-600' : 'text-green-600';
                
                html += `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="p-3 font-mono text-gray-800 dark:text-gray-200">${item.kode}</td>
                        <td class="p-3 text-gray-700 dark:text-gray-300">${item.nama}</td>
                        <td class="p-3 text-right font-medium text-gray-800 dark:text-gray-200">Rp ${item.total_fmt}</td>
                        <td class="p-3 text-right font-medium text-amber-600 dark:text-amber-400">Rp ${item.pengeluaran_fmt}</td>
                        <td class="p-3 text-right font-medium text-green-600 dark:text-green-400">Rp ${item.sisa_fmt}</td>
                        <td class="p-3 text-right font-medium ${percentageColor}">${percentage}%</td>
                    </tr>
                `;
            });

            html += `</tbody></table></div>`;
        } else {
            html += `
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-coins text-4xl mb-4 opacity-50"></i>
                    <p>Tidak ada data anggaran untuk tahun ini</p>
                </div>
            `;
        }

        container.innerHTML = html;
    }
}

class DashboardManager {
  constructor() {
    this.lastData = null;
    this.charts = {
      permohonan: null,
      anggaran: null,
      bebanKerja: null,
      genderCombined: null, 
      tindakPidana: null,
      statusHukum: null,
      mediaPengajuan: null,
      perlindunganComparison: null,
      perlindunganPermohonan: null,
      perlindunganLayanan: null
    };
    
    this.colors = {
      primary: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'],
      backgrounds: [
        'rgba(59, 130, 246, 0.1)', 'rgba(16, 185, 129, 0.1)', 
        'rgba(245, 158, 11, 0.1)', 'rgba(139, 92, 246, 0.1)',
        'rgba(236, 72, 153, 0.1)', 'rgba(6, 182, 212, 0.1)'
      ]
    };

    this.isInitialLoad = true; //flag untuk menandai ini load pertama
    
    this.init();
  }

  init() {
    this.setupEventListeners();
    
    // Load filter state
    this.loadFilterState();
    
    this.loadDashboardData();
    this.updateTimestamp();
    
    // Auto refresh every 5 minutes
    setInterval(() => {
        this.loadDashboardData();
    }, 300000);
    
    // Save filter state on change
    const yearFilter = document.getElementById('yearFilter');
    const monthFilter = document.getElementById('monthFilter');
    
    if (yearFilter) {
        yearFilter.addEventListener('change', () => this.saveFilterState());
    }
    
    if (monthFilter) {
        monthFilter.addEventListener('change', () => this.saveFilterState());
    }
  }

  // Method untuk mendeteksi refresh


  setupEventListeners() {
    // Event listener untuk filter tahun
    const yearFilter = document.getElementById('yearFilter');
    if (yearFilter) {
        yearFilter.addEventListener('change', () => {
            this.loadDashboardData();
            this.updateFilterIndicator();
        });
    }

    // Event listener untuk filter bulan
    const monthFilter = document.getElementById('monthFilter');
    if (monthFilter) {
        monthFilter.addEventListener('change', () => {
            this.loadDashboardData();
            this.updateFilterIndicator();
        });
    }

    // Event listener untuk refresh button
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            this.loadDashboardData();
        });
    }

    // Quick filter: Bulan ini
    const btnCurrentMonth = document.getElementById('btnCurrentMonth');
    if (btnCurrentMonth) {
        btnCurrentMonth.addEventListener('click', () => {
            const currentDate = new Date();
            document.getElementById('yearFilter').value = currentDate.getFullYear();
            document.getElementById('monthFilter').value = currentDate.getMonth() + 1;
            this.loadDashboardData();
            this.updateFilterIndicator();
        });
    }

    // Quick filter: Bulan lalu
    const btnLastMonth = document.getElementById('btnLastMonth');
    if (btnLastMonth) {
        btnLastMonth.addEventListener('click', () => {
            const lastMonth = new Date();
            lastMonth.setMonth(lastMonth.getMonth() - 1);
            document.getElementById('yearFilter').value = lastMonth.getFullYear();
            document.getElementById('monthFilter').value = lastMonth.getMonth() + 1;
            this.loadDashboardData();
            this.updateFilterIndicator();
        });
    }

    // Clear filter
    const clearFilter = document.getElementById('clearFilter');
    if (clearFilter) {
        clearFilter.addEventListener('click', () => {
            document.getElementById('monthFilter').value = '';
            this.loadDashboardData();
            this.updateFilterIndicator();
        });
    }
    const filterJenisStatus = document.getElementById('filter-jenis-status');
if (filterJenisStatus) {
    filterJenisStatus.addEventListener('change', () => {
        if (this.lastData) {
            const jenisStatus = filterJenisStatus.value;
            // Panggil updateStatusHukumUI untuk update judul
            if (jenisStatus === 'penelaahan') {
                this.updateStatusHukumUI(this.lastData.charts.status_hukum_penelaahan || {}, 'penelaahan');
                this.renderStatusHukumChart(this.lastData.charts.status_hukum_penelaahan || {});
            } else {
                this.updateStatusHukumUI(this.lastData.charts.status_hukum || {}, 'permohonan');
                this.renderStatusHukumChart(this.lastData.charts.status_hukum || {});
            }
        }
    });
}
  }

setupMediaPengajuanFilter() {
    const filterJenisMedia = document.getElementById('filter-jenis-media');
    if (filterJenisMedia) {
        filterJenisMedia.addEventListener('change', () => {
            if (this.lastData) {
                const jenisMedia = filterJenisMedia.value;
                this.updateMediaPengajuanUI(jenisMedia);
                this.renderMediaPengajuanChart(
                    jenisMedia === 'penelaahan' 
                        ? this.lastData.charts.media_pengajuan_penelaahan || {} 
                        : this.lastData.charts.media_pengajuan || {}
                );
            }
        });
    }
}

//   updateFilterVisibility() {
//     const dateRangeFilter = document.getElementById('dateRangeFilter');
//     const yearFilterContainer = document.getElementById('yearFilterContainer');
//     const monthFilterContainer = document.getElementById('monthFilterContainer');
//     const customRangeContainer = document.getElementById('customRangeContainer');
    
//     if (!dateRangeFilter) return;
    
//     const value = dateRangeFilter.value;
    
    // Hide all containers first
    // yearFilterContainer.classList.add('hidden');
    // monthFilterContainer.classList.add('hidden');
    // customRangeContainer.classList.add('hidden');
    
    // Show relevant containers
//     if (value === 'year') {
//         yearFilterContainer.classList.remove('hidden');
//     } else if (value === 'month') {
//         yearFilterContainer.classList.remove('hidden');
//         monthFilterContainer.classList.remove('hidden');
//     } else if (value === 'custom') {
//         customRangeContainer.classList.remove('hidden');
//     }
// }



  // Method untuk update indicator filter
  updateFilterIndicator() {
    const dateRangeFilter = document.getElementById('dateRangeFilter');
    const yearFilter = document.getElementById('yearFilter');
    const monthFilter = document.getElementById('monthFilter');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const filterIndicator = document.getElementById('filterIndicator');
    const filterText = document.getElementById('filterText');
    
    if (!dateRangeFilter || !filterIndicator || !filterText) return;
    
    const filterType = dateRangeFilter.value;
    let displayText = '';
    
    if (filterType === 'year' && yearFilter) {
        displayText = `Tahun ${yearFilter.value}`;
    } else if (filterType === 'month' && yearFilter && monthFilter) {
        const monthNames = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        const monthName = monthNames[parseInt(monthFilter.value) - 1];
        displayText = `${monthName} ${yearFilter.value}`;
    } else if (filterType === 'custom' && startDate && endDate) {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        const formatOptions = { day: 'numeric', month: 'long', year: 'numeric' };
        displayText = `${start.toLocaleDateString('id-ID', formatOptions)} - ${end.toLocaleDateString('id-ID', formatOptions)}`;
    } else {
        displayText = 'Semua waktu';
    }
    
    filterText.textContent = displayText;
    
    if (filterType !== 'year' || (monthFilter && monthFilter.value)) {
        filterIndicator.classList.remove('hidden');
        filterIndicator.classList.add('flex');
    } else {
        filterIndicator.classList.add('hidden');
        filterIndicator.classList.remove('flex');
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
      'loading-chart-beban-kerja',
      'loading-chart-gender-combined', 
      'loading-chart-tindak-pidana',
      'loading-chart-status-hukum',
      'loading-chart-media-pengajuan',
      'loading-chart-perlindungan-comparison',
      'loading-chart-perlindungan-permohonan',
      'loading-chart-perlindungan-layanan'
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
      'loading-chart-beban-kerja',
      'loading-chart-gender-combined',
      'loading-chart-tindak-pidana',
      'loading-chart-status-hukum',
      'loading-chart-media-pengajuan',
      'loading-chart-perlindungan-comparison',
      'loading-chart-perlindungan-permohonan',
      'loading-chart-perlindungan-layanan'
    ];
    
    loadingElements.forEach(id => {
      const element = document.getElementById(id);
      if (element) {
        element.style.display = 'none';
      }
    });
  }

  cleanupCharts() {
    Object.keys(this.charts).forEach(chartName => {
        if (this.charts[chartName] && typeof this.charts[chartName].destroy === 'function') {
            try {
                this.charts[chartName].destroy();
            } catch (error) {
                console.warn(`Error destroying chart ${chartName}:`, error);
            }
            this.charts[chartName] = null;
        }
    });
}

  async loadDashboardData() {
    try {
        this.hideError();
        this.showLoadingState();
        this.cleanupCharts();

        const dateRangeType = document.getElementById('dateRangeFilter')?.value || 'year';
        const selectedYear = document.getElementById('yearFilter')?.value || new Date().getFullYear();
        const selectedMonth = document.getElementById('monthFilter')?.value || '';
        const startDate = document.getElementById('startDate')?.value || '';
        const endDate = document.getElementById('endDate')?.value || '';
        
        // Build query string berdasarkan tipe filter
        let queryString = `date_range_type=${dateRangeType}`;
        
        if (dateRangeType === 'year') {
            queryString += `&year=${selectedYear}`;
        } else if (dateRangeType === 'month') {
            queryString += `&year=${selectedYear}&month=${selectedMonth}`;
        } else if (dateRangeType === 'custom') {
            queryString += `&start_date=${startDate}&end_date=${endDate}`;
        }
        
        const response = await fetch(`api_stats.php?${queryString}`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        this.lastData = data;
        
        if (!data.success) {
            throw new Error(data.error || 'Terjadi kesalahan pada server');
        }

        // Update filter jika berbeda dari yang diminta (misal: jika data hanya tersedia untuk tahun tertentu)
        if (data.selectedYear && data.selectedYear != selectedYear) {
            document.getElementById('yearFilter').value = data.selectedYear;
        }
        if (data.selectedMonth !== undefined && data.selectedMonth !== null) { // Cek jika undefined atau null
             document.getElementById('monthFilter').value = data.selectedMonth;
        }
        
        this.updateStatsCards(data.counts);
        this.renderCharts(data);
        // Inisialisasi judul status hukum saat pertama kali load
const initialJenisStatus = document.getElementById('filter-jenis-status')?.value || 'permohonan';
if (data.charts) {
    if (initialJenisStatus === 'penelaahan') {
        this.updateStatusHukumUI(data.charts.status_hukum_penelaahan || {}, 'penelaahan');
    } else {
        this.updateStatusHukumUI(data.charts.status_hukum || {}, 'permohonan');
    }
}
        this.renderMap(data.map);
        this.renderAktivitasTerbaru(data.aktivitas_terbaru);
        this.updateTimestamp();
        this.updateFilterIndicator();
        
        this.animateCards();
        
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        this.showError(error.message || 'Gagal memuat data dashboard');
        this.resetStatsCards();
    } finally {
        this.hideLoadingState();
    }
  }

  // method untuk load state filter dari localStorage
  loadFilterState() {


    const savedYear = localStorage.getItem('dashboardFilterYear');
    const savedMonth = localStorage.getItem('dashboardFilterMonth');
    
    if (savedYear) {
      const yearFilter = document.getElementById('yearFilter');
      if (yearFilter) yearFilter.value = savedYear;
    }
    
    if (savedMonth) {
      const monthFilter = document.getElementById('monthFilter');
      if (monthFilter) monthFilter.value = savedMonth;
    }
    
    this.updateFilterIndicator();
  }

  // method untuk save state filter
  saveFilterState() {
    // Jangan save filter state jika ini adalah initial load
    if (this.isInitialLoad) {
      return;
    }

    const yearFilter = document.getElementById('yearFilter');
    const monthFilter = document.getElementById('monthFilter');
    
    if (yearFilter) {
      localStorage.setItem('dashboardFilterYear', yearFilter.value);
    }
    
    if (monthFilter) {
      localStorage.setItem('dashboardFilterMonth', monthFilter.value);
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
            this.animateCounter(element, value);
        }
    });

    // Update persentase perubahan
    this.updateChangeIndicator('permohonan-change', counts.permohonan_change, 'blue');
    this.updateChangeIndicator('penelaahan-change', counts.penelaahan_change, 'green');
    this.updateChangeIndicator('layanan-change', counts.layanan_change, 'amber');
    this.updateChangeIndicator('pengeluaran-change', counts.pengeluaran_change, 'red');
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
    if (!data || !data.charts) {
        console.warn('No chart data available');
        return;
    }

    try {
        const chartsData = data.charts;
        
        this.renderPermohonanChart(chartsData.permohonan_line || {});
        this.renderAnggaranChart(data.anggaran || {});
        this.renderBebanKerjaChart(chartsData.beban_kerja || {});
        this.renderGenderCharts(chartsData);
        
        
        const jenisStatus = document.getElementById('filter-jenis-status')?.value || 'permohonan';
        if (jenisStatus === 'penelaahan') {
            this.renderStatusHukumChart(chartsData.status_hukum_penelaahan || {});
        } else {
            this.renderStatusHukumChart(chartsData.status_hukum || {});
        }
        
        this.renderTindakPidanaChart(chartsData.tindak_pidana || {});
        
        // PERBAIKAN: Setup filter media pengajuan dan render chart
        this.setupMediaPengajuanFilter();
        const jenisMedia = document.getElementById('filter-jenis-media')?.value || 'permohonan';
        if (jenisMedia === 'penelaahan') {
            this.updateMediaPengajuanUI('penelaahan');
            this.renderMediaPengajuanChart(chartsData.media_pengajuan_penelaahan || {});
        } else {
            this.updateMediaPengajuanUI('permohonan');
            this.renderMediaPengajuanChart(chartsData.media_pengajuan || {});
        }
        
        this.renderPerlindunganComparisonChart(chartsData.perlindungan_comparison || {});
        this.renderPerlindunganPermohonanChart(chartsData.perlindungan_permohonan || {});
        this.renderPerlindunganLayananChart(chartsData.perlindungan_layanan || {});
        
    } catch (error) {
        console.error('Error rendering charts:', error);
        this.showError('Gagal memuat beberapa chart: ' + error.message);
    }
}

// Method untuk merender chart media pengajuan
renderMediaPengajuanChart(chartData) {
    const ctx = document.getElementById('chartMediaPengajuan');
    if (!ctx) {
        console.error('Canvas chartMediaPengajuan tidak ditemukan');
        return;
    }

    // Destroy existing chart
    if (this.charts.mediaPengajuan) {
        this.charts.mediaPengajuan.destroy();
    }

    // Hide loading
    const loadingElement = document.getElementById('loading-chart-media-pengajuan');
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }

    // Update total count
   const totalElement = document.getElementById('total-media-pengajuan');
if (totalElement && chartData.total !== undefined) {
    totalElement.textContent = chartData.total.toLocaleString('id-ID');
}

    // Check if data is available
    if (!chartData || !chartData.labels || chartData.labels.length === 0) {
        console.warn('Data media pengajuan tidak tersedia');
        
        // Show empty state
        this.charts.mediaPengajuan = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Tidak ada data'],
                datasets: [{
                    data: [1],
                    backgroundColor: ['#e5e7eb']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
        
        this.renderMediaPengajuanDetail(chartData);
        return;
    }

    // Create the chart - menggunakan doughnut chart
    this.charts.mediaPengajuan = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartData.labels || [],
            datasets: [{
                data: chartData.data || [],
                backgroundColor: chartData.labels.map((_, index) => 
                    this.colors.primary[index % this.colors.primary.length]
                ),
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverBorderWidth: 4,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        color: '#6B7280',
                        font: {
                            size: 11,
                            weight: '600'
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    
                                    return {
                                        text: `${label}: ${value}`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        strokeStyle: data.datasets[0].borderColor,
                                        lineWidth: data.datasets[0].borderWidth,
                                        pointStyle: 'circle',
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
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
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });

    // Render detail
    this.renderMediaPengajuanDetail(chartData);
}


// Method untuk merender detail media pengajuan
renderMediaPengajuanDetail(chartData) {
    const container = document.getElementById('media-pengajuan-detail');
    if (!container) return;

    let html = '';

    if (!chartData.labels || chartData.labels.length === 0) {
        html = '<div class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Tidak ada data media pengajuan</div>';
    } else {
        const total = chartData.total || 0;
        
        chartData.labels.forEach((label, index) => {
            const value = chartData.data[index] || 0;
            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
            const color = this.colors.primary[index % this.colors.primary.length];
            
            html += `
                <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800/50 rounded-xl hover:shadow-md transition-all duration-300">
                    <div class="flex items-center space-x-3 flex-1">
                        <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: ${color}"></div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">${label}</span>
                    </div>
                    <div class="text-right ml-3">
                        <div class="text-sm font-bold text-gray-800 dark:text-white">${value}</div>
                        <div class="text-xs bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300 px-2 py-1 rounded-full font-medium">${percentage}%</div>
                    </div>
                </div>
            `;
        });

        // Summary
        html += `
            <div class="mt-4 p-4 bg-gradient-to-r from-cyan-50 to-cyan-100 dark:from-cyan-900/20 dark:to-cyan-800/20 rounded-xl">
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total</div>
                        <div class="text-lg font-bold text-cyan-600 dark:text-cyan-400">${total.toLocaleString('id-ID')}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Jenis Media</div>
                        <div class="text-lg font-bold text-cyan-600 dark:text-cyan-400">${chartData.labels.length}</div>
                    </div>
                </div>
            </div>
        `;
    }

    container.innerHTML = html;
}

  // Method untuk merender chart gabungan jenis kelamin
 renderGenderCombinedChart(permohonanData, penelaahanData, layananData) {
    const ctx = document.getElementById('chartGenderCombined');
    if (!ctx) {
        console.warn('Canvas chartGenderCombined tidak ditemukan');
        return;
    }
    
    // Destroy existing chart
    if (this.charts.genderCombined) {
        this.charts.genderCombined.destroy();
    }
    
    // Hide loading
    const loadingElement = document.getElementById('loading-chart-gender-combined');
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }
    
    // Pastikan data arrays ada
    const labels = permohonanData.labels || ['Laki-laki', 'Perempuan'];
    const permohonanArray = permohonanData.data || [0, 0];
    const penelaahanArray = penelaahanData.data || [0, 0];
    const layananArray = layananData.data || [0, 0];
    
    this.charts.genderCombined = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Permohonan',
                    data: permohonanArray,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                    barPercentage: 0.4,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Penelaahan',
                    data: penelaahanArray,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                    barPercentage: 0.4,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Layanan',
                    data: layananArray,
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                    barPercentage: 0.4,
                    categoryPercentage: 0.8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        color: '#6B7280',
                        font: {
                            size: 12,
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
                            const value = context.raw || 0;
                            const dataset = context.dataset;
                            const total = dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${dataset.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6B7280',
                        font: {
                            size: 12
                        }
                    }
                },
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
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });
}

// Method helper untuk chart gabungan kosong
renderEmptyCombinedChart(ctx) {
    this.charts.genderCombined = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Tidak ada data'],
            datasets: [
                {
                    label: 'Permohonan',
                    data: [0],
                    backgroundColor: 'rgba(59, 130, 246, 0.7)'
                },
                {
                    label: 'Layanan',
                    data: [0],
                    backgroundColor: 'rgba(16, 185, 129, 0.7)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                tooltip: { enabled: false }
            }
        }
    });
}


  // Method untuk merender detail jenis kelamin
 renderGenderDetail(containerId, chartData, color) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    let html = '';
    
    // Pastikan data tidak null
    const safeChartData = chartData || { labels: [], data: [], total: 0 };
    
    if (!safeChartData.labels || safeChartData.labels.length === 0) {
        html = '<div class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Tidak ada data</div>';
    } else {
        const total = safeChartData.total || 0;
        const colorClass = color === 'blue' ? 
            'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' :
            'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300';
        
        safeChartData.labels.forEach((label, index) => {
            const value = safeChartData.data[index] || 0;
            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
            
            html += `
                <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800/50 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 rounded-full bg-${color}-500"></div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">${label}</span>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-gray-800 dark:text-white">${value}</div>
                        <div class="text-xs ${colorClass} px-2 py-1 rounded-full font-medium">${percentage}%</div>
                    </div>
                </div>
            `;
        });
        
        // Summary
        html += `
            <div class="mt-3 p-3 bg-gradient-to-r from-${color}-50 to-${color}-100 dark:from-${color}-900/20 dark:to-${color}-800/20 rounded-xl">
                <div class="text-center">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total</div>
                    <div class="text-lg font-bold text-${color}-600 dark:text-${color}-400">${total.toLocaleString('id-ID')}</div>
                </div>
            </div>
        `;
    }
    
    container.innerHTML = html;
}


  renderTindakPidanaChart(chartData) {
    const ctx = document.getElementById('chartTindakPidana');
    if (!ctx) {
        console.error('Canvas chartTindakPidana tidak ditemukan');
        return;
    }

    // Destroy existing chart
    if (this.charts.tindakPidana) {
        this.charts.tindakPidana.destroy();
    }

    // Hide loading
    const loadingElement = document.getElementById('loading-chart-tindak-pidana');
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }

    // Check if data is available
    if (!chartData || !chartData.labels || chartData.labels.length === 0) {
        console.warn('Data tindak pidana tidak tersedia');
        
        // Show empty state
        this.charts.tindakPidana = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Tidak ada data'],
                datasets: [{
                    label: 'Jumlah Kasus',
                    data: [0],
                    backgroundColor: '#e5e7eb'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
        
        // Update total count to 0
        const totalElement = document.getElementById('total-tindak-pidana');
        if (totalElement) {
            totalElement.textContent = '0';
        }
        return;
    }

    // Update total count
    const totalElement = document.getElementById('total-tindak-pidana');
    if (totalElement && chartData.total) {
        totalElement.textContent = chartData.total.toLocaleString('id-ID');
    }

    // Create the chart - menggunakan bar chart
    this.charts.tindakPidana = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels || [],
            datasets: [{
                label: 'Jumlah Kasus',
                data: chartData.data || [],
                backgroundColor: chartData.labels.map((_, index) => 
                    this.colors.primary[index % this.colors.primary.length]
                ),
                borderColor: chartData.labels.map((_, index) => 
                    this.colors.primary[index % this.colors.primary.length]
                ),
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
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
                            const total = chartData.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${value} kasus (${percentage}%)`;
                        }
                    }
                }
            },
            scales: {
                x: {
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
                y: {
                    ticks: {
                        color: '#6B7280',
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        display: false
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

  updateMediaPengajuanUI(jenisMedia) {
    const title = jenisMedia === 'penelaahan' 
        ? 'Media Pengajuan Pemohon (Penelaahan)' 
        : 'Media Pengajuan Permohonan';
    
    const subtitle = jenisMedia === 'penelaahan'
        ? 'Distribusi berdasarkan cara pengajuan yang telah melalui proses penelaahan'
        : 'Distribusi berdasarkan cara pengajuan permohonan';
    
    const detailTitle = jenisMedia === 'penelaahan'
        ? 'Detail Media Pengajuan Penelaahan'
        : 'Detail Media Pengajuan Permohonan';
    
    const totalLabel = jenisMedia === 'penelaahan' ? 'permohonan' : 'permohonan';

    // Update judul dan subtitle
    const titleElement = document.getElementById('media-pengajuan-title');
    const subtitleElement = document.getElementById('media-pengajuan-subtitle');
    const detailTitleElement = document.getElementById('detail-media-pengajuan-title');
    const totalElement = document.getElementById('total-media-pengajuan');
    
    if (titleElement) titleElement.textContent = title;
    if (subtitleElement) subtitleElement.textContent = subtitle;
    if (detailTitleElement) detailTitleElement.textContent = detailTitle;
    
    // Update total label
    if (totalElement && totalElement.parentElement) {
        totalElement.parentElement.innerHTML = `Total: <span id="total-media-pengajuan">${totalElement.textContent}</span> ${totalLabel}`;
    }
}



  updateStatusHukumUI(chartData, jenisStatus) {
    // Update judul berdasarkan jenis status
    const title = jenisStatus === 'penelaahan' 
        ? 'Status Hukum Pemohon (Penelaahan)' 
        : 'Status Hukum Pemohon';
    const subtitle = jenisStatus === 'penelaahan'
        ? 'Distribusi status hukum pemohon yang telah melalui proses penelaahan'
        : 'Distribusi berdasarkan status hukum dalam proses permohonan';

    // PERBAIKAN: Gunakan selector yang tepat dengan ID yang sudah ditambahkan
    const titleElement = document.getElementById('status-hukum-title');
    const subtitleElement = document.getElementById('status-hukum-subtitle');
    
    if (titleElement) titleElement.textContent = title;
    if (subtitleElement) subtitleElement.textContent = subtitle;

    // Update total
    const total = chartData.total || 0;
    const totalElement = document.getElementById('total-status-hukum');
    if (totalElement) {
        totalElement.textContent = total.toLocaleString('id-ID');
    }
}


  renderStatusHukumChart(chartData) {
    const ctx = document.getElementById('chartStatusHukum');
    if (!ctx) {
        console.error('Canvas chartStatusHukum tidak ditemukan');
        return;
    }

    // Dapatkan jenis status dari dropdown
    const jenisStatus = document.getElementById('filter-jenis-status')?.value || 'permohonan';
    
    // Update UI judul dan total SEBELUM membuat chart
    this.updateStatusHukumUI(chartData, jenisStatus);

    // Destroy existing chart
    if (this.charts.statusHukum) {
        this.charts.statusHukum.destroy();
    }

    // Hide loading
    const loadingElement = document.getElementById('loading-chart-status-hukum');
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }

    // Check if data is available
    if (!chartData || !chartData.labels || chartData.labels.length === 0) {
        console.warn('Data status hukum tidak tersedia');
        
        // Show empty state
        this.charts.statusHukum = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Tidak ada data'],
                datasets: [{
                    data: [1],
                    backgroundColor: ['#e5e7eb']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
        return;
    }

    // Create the chart - menggunakan pie chart untuk variasi
    this.charts.statusHukum = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: chartData.labels || [],
            datasets: [{
                data: chartData.data || [],
                backgroundColor: chartData.labels.map((_, index) => 
                    this.colors.primary[index % this.colors.primary.length]
                ),
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverBorderWidth: 4,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        color: '#6B7280',
                        font: {
                            size: 12,
                            weight: '600'
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    
                                    return {
                                        text: `${label}: ${value} (${percentage}%)`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        strokeStyle: data.datasets[0].borderColor,
                                        lineWidth: data.datasets[0].borderWidth,
                                        pointStyle: 'circle',
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
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
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });
}

  

  // Method untuk merender chart perbandingan jenis perlindungan
  renderPerlindunganComparisonChart(chartData) {
    const ctx = document.getElementById('chartPerlindunganComparison');
    if (!ctx) return;

    // Destroy existing chart
    if (this.charts.perlindunganComparison) {
        this.charts.perlindunganComparison.destroy();
    }

    // Hide loading
    const loadingElement = document.getElementById('loading-chart-perlindungan-comparison');
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }

    // Calculate totals and ratio
    const totalPermohonan = chartData.permohonan ? chartData.permohonan.reduce((a, b) => a + b, 0) : 0;
    const totalLayanan = chartData.layanan ? chartData.layanan.reduce((a, b) => a + b, 0) : 0;
    const rasioPemenuhan = totalPermohonan > 0 ? Math.round((totalLayanan / totalPermohonan) * 100) : 0;

    // Update total counters
    const totalPermohonanElement = document.getElementById('total-permohonan-comparison');
    const totalLayananElement = document.getElementById('total-layanan-comparison');
    const rasioElement = document.getElementById('rasio-pemenuhan');

    if (totalPermohonanElement) {
        totalPermohonanElement.textContent = `Permohonan: ${totalPermohonan.toLocaleString('id-ID')}`;
    }
    if (totalLayananElement) {
        totalLayananElement.textContent = `Layanan: ${totalLayanan.toLocaleString('id-ID')}`;
    }
    if (rasioElement) {
        rasioElement.textContent = `Rasio: ${rasioPemenuhan}%`;
        
        // Warna berdasarkan rasio pemenuhan
        if (rasioPemenuhan >= 80) {
            rasioElement.className = 'text-green-700 dark:text-green-300 font-medium';
        } else if (rasioPemenuhan >= 60) {
            rasioElement.className = 'text-amber-700 dark:text-amber-300 font-medium';
        } else {
            rasioElement.className = 'text-red-700 dark:text-red-300 font-medium';
        }
    }

    // Check if data is available
    if (!chartData || !chartData.labels || chartData.labels.length === 0) {
        this.renderEmptyChart(ctx, 'Tidak ada data perbandingan');
        return;
    }

    this.charts.perlindunganComparison = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels || [],
            datasets: [
                {
                    label: 'Permohonan',
                    data: chartData.permohonan || [],
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    borderRadius: 6
                },
                {
                    label: 'Layanan',
                    data: chartData.layanan || [],
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 2,
                    borderRadius: 6
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        color: '#6B7280',
                        font: {
                            size: 12,
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
                    callbacks: {
                        label: function(context) {
                            const value = context.raw || 0;
                            const totalDataset = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = totalDataset > 0 ? ((value / totalDataset) * 100).toFixed(1) : 0;
                            return `${context.dataset.label}: ${value} (${percentage}%)`;
                        },
                        afterLabel: function(context) {
                            if (context.datasetIndex === 1) { // Hanya untuk dataset layanan
                                const permohonanValue = chartData.permohonan[context.dataIndex] || 0;
                                const layananValue = context.raw || 0;
                                if (permohonanValue > 0) {
                                    const pemenuhan = Math.round((layananValue / permohonanValue) * 100);
                                    return `Tingkat pemenuhan: ${pemenuhan}%`;
                                }
                            }
                            return null;
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: false,
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: '#6B7280'
                    },
                    title: {
                        display: true,
                        text: 'Jumlah',
                        color: '#6B7280'
                    }
                },
                y: {
                    stacked: false,
                    ticks: {
                        color: '#6B7280',
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
  }


  // Method untuk merender chart jenis perlindungan permohonan sebagai PIE CHART
  // Perbaiki method renderPerlindunganPermohonanChart
renderPerlindunganPermohonanChart(chartData) {
    const ctx = document.getElementById('chartPerlindunganPermohonan');
    if (!ctx) return;

    // Destroy existing chart dengan pengecekan yang aman
    if (this.charts.perlindunganPermohonan && typeof this.charts.perlindunganPermohonan.destroy === 'function') {
        this.charts.perlindunganPermohonan.destroy();
        this.charts.perlindunganPermohonan = null;
    }

    // Hide loading
    const loadingElement = document.getElementById('loading-chart-perlindungan-permohonan');
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }

    // PERBAIKAN: Pastikan chartData tidak null dan memiliki struktur yang benar
    const safeChartData = chartData || { 
        sub_pilihan: [], 
        kategori: [], 
        data: [], 
        total: 0 
    };

    // Update total counter di header
    const totalHeaderElement = document.getElementById('total-perlindungan-permohonan');
    if (totalHeaderElement) {
        totalHeaderElement.textContent = safeChartData.total ? safeChartData.total.toLocaleString('id-ID') : '0';
    }

    // Check if data is available
    if (!safeChartData.sub_pilihan || safeChartData.sub_pilihan.length === 0) {
        this.renderEmptyPieChart(ctx, 'Tidak ada data permohonan');
        this.renderPerlindunganPermohonanDetail(safeChartData);
        return;
    }

    try {
        // Create PIE chart untuk permohonan
        this.charts.perlindunganPermohonan = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: safeChartData.sub_pilihan || [],
                datasets: [{
                    data: safeChartData.data || [],
                    backgroundColor: (safeChartData.sub_pilihan || []).map((_, index) => 
                        this.colors.primary[index % this.colors.primary.length]
                    ),
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverBorderWidth: 4,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            color: '#6B7280',
                            font: {
                                size: 11,
                                weight: '600'
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        
                                        return {
                                            text: `${label}: ${value}`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].borderColor,
                                            lineWidth: data.datasets[0].borderWidth,
                                            pointStyle: 'circle',
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
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
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${value} permohonan (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    } catch (error) {
        console.error('Error creating perlindunganPermohonan chart:', error);
        this.renderEmptyPieChart(ctx, 'Error loading chart');
    }

    // Render detail permohonan
    this.renderPerlindunganPermohonanDetail(safeChartData);
}


  // Method untuk merender detail jenis perlindungan permohonan
 renderPerlindunganPermohonanDetail(chartData) {
    const container = document.getElementById('perlindungan-permohonan-detail');
    if (!container) return;

    let html = '';

    // PERBAIKAN: Pastikan data tidak null
    const safeChartData = chartData || { 
        sub_pilihan: [], 
        kategori: [], 
        data: [], 
        total: 0 
    };

    if (!safeChartData.sub_pilihan || safeChartData.sub_pilihan.length === 0) {
        html = '<div class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Tidak ada data jenis perlindungan</div>';
    } else {
        const total = safeChartData.data.reduce((sum, val) => sum + (val || 0), 0);
        
        // Group by kategori
        const groupedByKategori = {};
        safeChartData.sub_pilihan.forEach((subPilihan, index) => {
            const kategori = safeChartData.kategori?.[index] || 'Tidak Diketahui';
            const jumlah = safeChartData.data?.[index] || 0;
            
            if (!groupedByKategori[kategori]) {
                groupedByKategori[kategori] = [];
            }
            
            groupedByKategori[kategori].push({
                subPilihan,
                jumlah,
                percentage: total > 0 ? ((jumlah / total) * 100).toFixed(1) : 0
            });
        });

        // Generate HTML
        Object.keys(groupedByKategori).forEach(kategori => {
            const kategoriTotal = groupedByKategori[kategori].reduce((sum, item) => sum + item.jumlah, 0);
            const kategoriPercentage = total > 0 ? ((kategoriTotal / total) * 100).toFixed(1) : 0;
            
            html += `
                <div class="mb-4 p-3 bg-white dark:bg-gray-800/50 rounded-xl shadow-sm">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">${kategori}</h4>
                        <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-2 py-1 rounded-full">
                            ${kategoriTotal} (${kategoriPercentage}%)
                        </span>
                    </div>
                    <div class="space-y-2">
            `;
            
            groupedByKategori[kategori].forEach(item => {
                html += `
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600 dark:text-gray-400 truncate flex-1">${item.subPilihan}</span>
                            <div class="text-right ml-2 flex-shrink-0">
                                <div class="font-medium text-gray-800 dark:text-white">${item.jumlah}</div>
                                <div class="text-gray-500 dark:text-gray-400">${item.percentage}%</div>
                            </div>
                        </div>
                `;
            });
            
            html += `</div></div>`;
        });

        // Summary section
        html += `
            <div class="mt-4 p-3 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total Permohonan</div>
                        <div class="text-sm font-bold text-blue-600 dark:text-blue-400">${total}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Jenis Perlindungan</div>
                        <div class="text-sm font-bold text-blue-600 dark:text-blue-400">${safeChartData.sub_pilihan.length}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Kategori</div>
                        <div class="text-sm font-bold text-blue-600 dark:text-blue-400">${Object.keys(groupedByKategori).length}</div>
                    </div>
                </div>
            </div>
        `;
    }

    container.innerHTML = html;
}



  // Method untuk merender chart jenis perlindungan layanan sebagai PIE CHART
  renderPerlindunganLayananChart(chartData) {
    const ctx = document.getElementById('chartPerlindunganLayanan');
    if (!ctx) return;

    // Destroy existing chart dengan pengecekan yang lebih aman
    if (this.charts.perlindunganLayanan && typeof this.charts.perlindunganLayanan.destroy === 'function') {
        this.charts.perlindunganLayanan.destroy();
        this.charts.perlindunganLayanan = null;
    }

    // Hide loading
    const loadingElement = document.getElementById('loading-chart-perlindungan-layanan');
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }

    // Update total counter di header
    const totalHeaderElement = document.getElementById('total-perlindungan-layanan');
    if (totalHeaderElement) {
        totalHeaderElement.textContent = chartData.total ? chartData.total.toLocaleString('id-ID') : '0';
    }

    // Check if data is available
    if (!chartData || !chartData.sub_pilihan || chartData.sub_pilihan.length === 0) {
        this.renderEmptyPieChart(ctx, 'Tidak ada data layanan');
        this.renderPerlindunganLayananDetail([]);
        return;
    }

    try {
        // Create PIE chart untuk layanan
        this.charts.perlindunganLayanan = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: chartData.sub_pilihan || [],
                datasets: [{
                    data: chartData.data || [],
                    backgroundColor: chartData.sub_pilihan.map((_, index) => 
                        this.colors.primary[index % this.colors.primary.length]
                    ),
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverBorderWidth: 4,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            color: '#6B7280',
                            font: {
                                size: 11,
                                weight: '600'
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        
                                        return {
                                            text: `${label}: ${value}`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].borderColor,
                                            lineWidth: data.datasets[0].borderWidth,
                                            pointStyle: 'circle',
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
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
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${value} layanan (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    } catch (error) {
        console.error('Error creating perlindunganLayanan chart:', error);
        this.renderEmptyPieChart(ctx, 'Error loading chart');
    }

    // Render detail layanan
    this.renderPerlindunganLayananDetail(chartData);
}

  // Method untuk merender detail jenis perlindungan layanan
  renderPerlindunganLayananDetail(chartData) {
    const container = document.getElementById('perlindungan-layanan-detail');
    if (!container) return;

    let html = '';

    if (!chartData.sub_pilihan || chartData.sub_pilihan.length === 0) {
        html = '<div class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Tidak ada data jenis perlindungan</div>';
    } else {
        const total = chartData.data.reduce((sum, val) => sum + val, 0);
        
        // Group by kategori
        const groupedByKategori = {};
        chartData.sub_pilihan.forEach((subPilihan, index) => {
            const kategori = chartData.kategori[index];
            const jumlah = chartData.data[index];
            
            if (!groupedByKategori[kategori]) {
                groupedByKategori[kategori] = [];
            }
            
            groupedByKategori[kategori].push({
                subPilihan,
                jumlah,
                percentage: total > 0 ? ((jumlah / total) * 100).toFixed(1) : 0
            });
        });

        // Generate HTML
        Object.keys(groupedByKategori).forEach(kategori => {
            const kategoriTotal = groupedByKategori[kategori].reduce((sum, item) => sum + item.jumlah, 0);
            const kategoriPercentage = total > 0 ? ((kategoriTotal / total) * 100).toFixed(1) : 0;
            
            html += `
                <div class="mb-4 p-3 bg-white dark:bg-gray-800/50 rounded-xl shadow-sm">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">${kategori}</h4>
                        <span class="text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-1 rounded-full">
                            ${kategoriTotal} (${kategoriPercentage}%)
                        </span>
                    </div>
                    <div class="space-y-2">
            `;
            
            groupedByKategori[kategori].forEach(item => {
                html += `
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600 dark:text-gray-400 truncate flex-1">${item.subPilihan}</span>
                            <div class="text-right ml-2 flex-shrink-0">
                                <div class="font-medium text-gray-800 dark:text-white">${item.jumlah}</div>
                                <div class="text-gray-500 dark:text-gray-400">${item.percentage}%</div>
                            </div>
                        </div>
                `;
            });
            
            html += `</div></div>`;
        });

        // Summary section
        html += `
            <div class="mt-4 p-3 bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total Layanan</div>
                        <div class="text-sm font-bold text-green-600 dark:text-green-400">${total}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Jenis Perlindungan</div>
                        <div class="text-sm font-bold text-green-600 dark:text-green-400">${chartData.sub_pilihan.length}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Kategori</div>
                        <div class="text-sm font-bold text-green-600 dark:text-green-400">${new Set(chartData.kategori).size}</div>
                    </div>
                </div>
            </div>
        `;
    }

    container.innerHTML = html;
  }


  // Method helper untuk pie chart kosong
  renderEmptyPieChart(ctx, message) {
    // Destroy existing chart dengan pengecekan yang aman
    const chartId = ctx.id.replace('chart', '').toLowerCase();
    if (this.charts[chartId] && typeof this.charts[chartId].destroy === 'function') {
        this.charts[chartId].destroy();
        this.charts[chartId] = null;
    }
    
    try {
        this.charts[chartId] = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [message],
                datasets: [{
                    data: [1],
                    backgroundColor: ['#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    } catch (error) {
        console.error('Error creating empty pie chart:', error);
    }
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
        labels: chartData.labels || [], // Labels dinamis dari API
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

  // NEW: Method untuk merender detail jenis kelamin
  renderGenderDetail(containerId, chartData, color) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    let html = '';
    
    if (!chartData || !chartData.labels || chartData.labels.length === 0) {
      html = '<div class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Tidak ada data</div>';
    } else {
      const total = chartData.total || 0;
      const colorClass = color === 'blue' ? 
        'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' :
        'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300';
      
      chartData.labels.forEach((label, index) => {
        const value = chartData.data[index] || 0;
        const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
        const colors = ['bg-blue-500', 'bg-pink-500', 'bg-purple-500']; // Default colors
        
        html += `
          <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
            <div class="flex items-center space-x-3">
              <div class="w-3 h-3 rounded-full ${colors[index] || 'bg-gray-500'}"></div>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">${label}</span>
            </div>
            <div class="text-right">
              <div class="text-sm font-bold text-gray-800 dark:text-white">${value}</div>
              <div class="text-xs ${colorClass} px-2 py-1 rounded-full font-medium">${percentage}%</div>
            </div>
          </div>
        `;
      });
      
      // Summary
      html += `
        <div class="mt-3 p-3 bg-gradient-to-r from-${color}-50 to-${color}-100 dark:from-${color}-900/20 dark:to-${color}-800/20 rounded-xl">
          <div class="text-center">
            <div class="text-xs text-gray-500 dark:text-gray-400">Total</div>
            <div class="text-lg font-bold text-${color}-600 dark:text-${color}-400">${total.toLocaleString('id-ID')}</div>
          </div>
        </div>
      `;
    }
    
    container.innerHTML = html;
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


 renderGenderCharts(chartsData) {
    if (!chartsData || !chartsData.gender_distribution) {
        console.warn('Gender distribution data not available');
        this.renderEmptyGenderCharts();
        return;
    }
    
    const genderData = chartsData.gender_distribution;
    
    // Pastikan data tidak null
    const permohonanData = genderData.permohonan || { labels: [], data: [], total: 0 };
    const penelaahanData = genderData.penelaahan || { labels: [], data: [], total: 0 };
    const layananData = genderData.layanan || { labels: [], data: [], total: 0 };
    
    // Update total counters
    const totalPermohonanElement = document.getElementById('total-gender-permohonan-text');
    const totalPenelaahanElement = document.getElementById('total-gender-penelaahan-text');
    const totalLayananElement = document.getElementById('total-gender-layanan-text');
    
    if (totalPermohonanElement) {
        totalPermohonanElement.textContent = permohonanData.total ? permohonanData.total.toLocaleString('id-ID') : '0';
    }
    if (totalPenelaahanElement) {
        totalPenelaahanElement.textContent = penelaahanData.total ? penelaahanData.total.toLocaleString('id-ID') : '0';
    }
    if (totalLayananElement) {
        totalLayananElement.textContent = layananData.total ? layananData.total.toLocaleString('id-ID') : '0';
    }
    
    // Render chart gabungan
    this.renderGenderCombinedChart(permohonanData, penelaahanData, layananData);
    
    // Render detail untuk masing-masing
    this.renderGenderDetail('gender-permohonan-detail', permohonanData, 'blue');
    this.renderGenderDetail('gender-penelaahan-detail', penelaahanData, 'emerald');
    this.renderGenderDetail('gender-layanan-detail', layananData, 'green');
}

// Method untuk menangani chart gender kosong
renderEmptyGenderCharts() {
    const ctx = document.getElementById('chartGenderCombined');
    if (!ctx) return;
    
    // Destroy existing chart
    if (this.charts.genderCombined) {
        this.charts.genderCombined.destroy();
    }
    
    // Hide loading
    const loadingElement = document.getElementById('loading-chart-gender-combined');
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }
    
    // Create empty chart
    this.charts.genderCombined = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Tidak ada data'],
            datasets: [
                {
                    label: 'Permohonan',
                    data: [0],
                    backgroundColor: 'rgba(59, 130, 246, 0.7)'
                },
                {
                    label: 'Layanan', 
                    data: [0],
                    backgroundColor: 'rgba(16, 185, 129, 0.7)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                tooltip: { enabled: false }
            }
        }
    });
    
    // Update total counters ke 0
    const totalPermohonanElement = document.getElementById('total-gender-permohonan-text');
    const totalLayananElement = document.getElementById('total-gender-layanan-text');
    
    if (totalPermohonanElement) totalPermohonanElement.textContent = '0';
    if (totalLayananElement) totalLayananElement.textContent = '0';
    
    // Render empty details
    this.renderGenderDetail('gender-permohonan-detail', { labels: [], data: [], total: 0 }, 'blue');
    this.renderGenderDetail('gender-layanan-detail', { labels: [], data: [], total: 0 }, 'green');
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

    // PERBAIKAN: Pastikan aktivitasData adalah array
    const safeAktivitasData = Array.isArray(aktivitasData) ? aktivitasData : [];

    if (safeAktivitasData.length === 0) {
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
        safeAktivitasData.forEach((aktivitas, index) => {
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
      const items = container.children;
      items.forEach((item, index) => {
        setTimeout(() => {
          item.classList.add('animate-fade-in');
        }, index * 100);
      });
    }, 100);
  }

renderMap(mapData) {
    console.log('renderMap called with:', mapData);
    
    if (!mapData || !window.DatamapIndonesia) {
        console.warn('Map data or DatamapIndonesia not available');
        return;
    }

    const mapContainer = document.getElementById('map-container');
    const mapLoading = document.getElementById('map-loading');
    
    if (!mapContainer) {
        console.error('Map container not found');
        return;
    }

    // Clear existing map
    const existingSvg = mapContainer.querySelector('svg');
    if (existingSvg) {
        existingSvg.remove();
    }

    try {
        // Prepare map data
        const formattedData = {};
        const allProvinces = [
            'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Jambi', 'Sumatera Selatan', 
            'Bengkulu', 'Lampung', 'Kepulauan Bangka Belitung', 'Kepulauan Riau',
            'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'DI Yogyakarta', 'Jawa Timur', 'Banten',
            'Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur',
            'Kalimantan Barat', 'Kalimantan Tengah', 'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara',
            'Sulawesi Utara', 'Sulawesi Tengah', 'Sulawesi Selatan', 'Sulawesi Tenggara', 'Gorontalo', 'Sulawesi Barat',
            'Maluku', 'Maluku Utara',
            'Papua Barat', 'Papua', 'Papua Selatan', 'Papua Tengah', 'Papua Pegunungan', 'Papua Barat Daya'
        ];
        
        allProvinces.forEach(province => {
            formattedData[province] = {
                value: mapData.provinsi_counts?.[province] || 0,
                fillKey: mapData.provinsi_fillkeys?.[province] || 'defaultFill',
                // DATA BARU: detail kabupaten untuk provinsi ini
                kabupaten: mapData.kabupaten_detail?.[province] || []
            };
        });

        console.log('Formatted map data with kabupaten:', formattedData);

        // Initialize map dengan tooltip yang lebih detail
        const map = new DatamapIndonesia({
            element: mapContainer,
            responsive: true,
            geographyConfig: {
                highlightOnHover: true,
                popupOnHover: true,
                highlightBorderWidth: 3,
                highlightBorderColor: '#ffffff',
                borderWidth: 1,
                borderColor: '#ffffff',
                popupTemplate: function(geo, data) {
    console.log('Popup template called for:', geo.properties.provinsi, data);
    
    const value = data?.value || 0;
    const totalCount = mapData.total_semua_provinsi || Object.values(mapData.provinsi_counts || {}).reduce((a, b) => a + b, 0) || 1;
    const percentage = totalCount > 0 ? Math.round((value / totalCount) * 100) : 0;
    
    // DATA BARU: Detail kabupaten - tampilkan lebih banyak
    const kabupatenList = data?.kabupaten || [];
    let kabupatenHTML = '';
    
    if (kabupatenList.length > 0) {
        // Tampilkan maksimal 8 kabupaten teratas (diperbanyak dari 5)
        const topKabupaten = [...kabupatenList].sort((a, b) => b.jumlah - a.jumlah).slice(0, 8);
        const totalProvinsi = topKabupaten.reduce((sum, kab) => sum + kab.jumlah, 0);
        
        kabupatenHTML = `
            <div style="margin-top: 12px; border-top: 1px solid #e5e7eb; padding-top: 8px;">
                <div style="font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                    Top Kabupaten/Kota (${kabupatenList.length} total):
                </div>
                ${topKabupaten.map((kab, index) => {
                    const kabPercentage = totalProvinsi > 0 ? Math.round((kab.jumlah / totalProvinsi) * 100) : 0;
                    return `
                        <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 4px; font-size: 11px; padding: 2px 0;">
                            <span style="flex: 1; color: #6b7280; ${index < 3 ? 'font-weight: 600;' : ''}">
                                ${index + 1}. ${kab.kabupaten}
                            </span>
                            <div style="text-align: right;">
                                <div style="font-weight: ${index < 3 ? 'bold' : 'normal'}; color: #1f2937;">${kab.jumlah.toLocaleString('id-ID')}</div>
                                <div style="font-size: 10px; color: ${kabPercentage >= 10 ? '#059669' : kabPercentage >= 5 ? '#d97706' : '#6b7280'};">${kabPercentage}%</div>
                            </div>
                        </div>
                    `;
                }).join('')}
                ${kabupatenList.length > 8 ? `
                    <div style="font-size: 10px; color: #9ca3af; text-align: center; margin-top: 6px; padding: 4px; background: #f9fafb; border-radius: 4px;">
                        +${kabupatenList.length - 8} kabupaten lainnya
                    </div>
                ` : ''}
            </div>
        `;
    } else {
        kabupatenHTML = `
            <div style="margin-top: 8px; font-size: 11px; color: #9ca3af; text-align: center; padding: 8px; background: #f9fafb; border-radius: 4px;">
                Tidak ada data kabupaten/kota
            </div>
        `;
    }
    
    return `
        <div class="map-tooltip-content" style="
            background: white;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-family: system-ui, sans-serif;
            min-width: 280px;
            max-width: 350px;
            color: #333;
        ">
            <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: bold; color: #1a1a1a;">
                ${geo.properties.provinsi}
            </h3>
            <div style="margin-bottom: 6px;">
                <span>Total Permohonan: </span>
                <strong style="color: #2563eb;">${value.toLocaleString('id-ID')}</strong>
            </div>
            <div style="margin-bottom: 8px;">
                <span>Persentase Nasional: </span>
                <strong style="color: #059669;">${percentage}%</strong>
            </div>
            <div style="width: 100%; height: 4px; background: #e5e7eb; border-radius: 2px; overflow: hidden; margin-bottom: 8px;">
                <div style="
                    height: 100%; 
                    width: ${percentage}%; 
                    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
                    transition: width 0.5s ease;
                "></div>
            </div>
            ${kabupatenHTML}
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

        console.log('Map initialized successfully');
        
        // Hide loading
        if (mapLoading) {
            mapLoading.style.display = 'none';
        }
        
        this.renderMapLegend(mapData);
        this.renderKabupatenTable(mapData); // DATA BARU: Render tabel kabupaten
        
    } catch (error) {
        console.error('Error rendering map:', error);
        if (mapLoading) {
            mapLoading.innerHTML = `
                <div class="text-center text-gray-500 dark:text-gray-400 py-12">
                    <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-3xl text-amber-500"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Peta Tidak Tersedia</h3>
                    <p class="text-sm">Error: ${error.message}</p>
                </div>
            `;
        }
    }

// DAta BARU: Render Table Kabupaten
    // ... [existing renderKabupatenTable code] ... 
    // This is actually replacing the end of renderKabupatenTable to append the new function, 
    // but better to just insert AFTER it if I can target the closing brace.
    // However, replace_file_content works on ranges. 
    // I will append the NEW function after the closing brace of the previous function.
}

  renderAktivitasTerbaru(aktivitasData) {
    const container = document.getElementById('aktivitas-container');
    if (!container) return;

    if (!aktivitasData || aktivitasData.length === 0) {
        container.innerHTML = `
            <div class="col-span-3 text-center py-12">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-history text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Belum ada aktivitas</h3>
                <p class="text-gray-500 dark:text-gray-400">Aktivitas sistem akan muncul di sini</p>
            </div>
        `;
        return;
    }

    let html = '<div class="col-span-3 space-y-0">';

    aktivitasData.forEach(item => {
        // Format timestamp
        const date = new Date(item.changed_at);
        const timeStr = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

        // Badge color based on action
        let badgeClass = 'bg-gray-100 text-gray-800';
        let actionLabel = item.action;
        let icon = 'fa-info';
        let changeDetails = '';

        // Helper to parse JSON
        const safeParse = (jsonStr) => {
            try { return typeof jsonStr === 'string' ? JSON.parse(jsonStr) : jsonStr; } catch (e) { return {}; }
        };
        
        const oldVals = safeParse(item.old_values);
        const newVals = safeParse(item.new_values);

        if (item.action === 'INSERT') {
            badgeClass = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800';
            actionLabel = 'TAMBAH';
            icon = 'fa-plus';
            // Show new key details
            changeDetails = `<div class="mt-2 space-y-1">`;
            for (const [key, val] of Object.entries(newVals || {})) {
                if (key !== 'id' && key !== 'created_at' && key !== 'updated_at' && val !== null && val !== '') {
                     changeDetails += `<div class="text-xs text-gray-600 dark:text-gray-400">
                        <span class="font-semibold text-green-600 dark:text-green-400">+ ${key}:</span> ${val}
                     </div>`;
                }
            }
            changeDetails += `</div>`;

        } else if (item.action === 'UPDATE') {
            badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800';
            actionLabel = 'UPDATE';
            icon = 'fa-edit';
            
            // Calc diff
            changeDetails = `<div class="mt-2 space-y-1">`;
            let hasChanges = false;
            for (const key in newVals) {
                if (newVals.hasOwnProperty(key) && oldVals.hasOwnProperty(key)) {
                    if (newVals[key] != oldVals[key]) { // Loose equality for numbers
                         hasChanges = true;
                         changeDetails += `<div class="text-xs text-gray-600 dark:text-gray-400">
                            <span class="font-medium text-gray-500">${key}:</span> 
                            <span class="text-red-500 line-through mr-1">${oldVals[key]}</span>
                            <i class="fas fa-arrow-right text-xs text-gray-400 mx-1"></i>
                            <span class="text-blue-600 font-medium">${newVals[key]}</span>
                         </div>`;
                    }
                }
            }
            if (!hasChanges) changeDetails += `<div class="text-xs italic text-gray-500">Data diperbarui tanpa perubahan nilai</div>`;
            changeDetails += `</div>`;

        } else if (item.action === 'DELETE') {
            badgeClass = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800';
            actionLabel = 'HAPUS';
            icon = 'fa-trash';
            
            // Show deleted summary
            changeDetails = `<div class="mt-2 space-y-1">`;
             // Show first 3 keys as summary
            let count = 0;
            for (const [key, val] of Object.entries(oldVals || {})) {
                if (key !== 'created_at' && key !== 'updated_at' && count < 3) {
                     changeDetails += `<div class="text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-semibold text-red-400">- ${key}:</span> ${val}
                     </div>`;
                     count++;
                }
            }
            changeDetails += `</div>`;
        }

        // Determine description
        let mainDesc = `<span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded border border-gray-200 dark:border-gray-600 uppercase tracking-wide">${item.table_name}</span>`;
        if (item.record_id) {
            mainDesc += ` <span class="text-xs text-gray-400 mx-1">#</span><span class="font-mono text-xs text-gray-600 dark:text-gray-300 font-bold">${item.record_id}</span>`;
        }

        // Use display_name from API (which coalesces users.nama_lengkap and audit_logs.user_name)
        const displayName = item.display_name || item.user_name || 'System / Unknown';
        const userAvatar = displayName.charAt(0).toUpperCase();

        html += `
            <div class="relative pl-8 pb-8 border-l-2 border-gray-200 dark:border-gray-700 last:border-0 last:pb-0 group">
                <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 group-hover:border-blue-500 transition-colors"></div>
                
                <div class="mb-1 text-xs text-gray-500 dark:text-gray-400 flex items-center">
                    <i class="far fa-clock mr-1"></i> ${timeStr}
                </div>
                
                <div class="bg-white dark:bg-gray-700/50 rounded-xl p-4 border border-gray-100 dark:border-gray-600/50 shadow-sm hover:shadow-md transition-all hover:bg-gray-50 dark:hover:bg-gray-700">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900 dark:to-purple-900 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold text-sm shadow-sm ring-2 ring-white dark:ring-gray-700">
                                ${userAvatar}
                            </div>
                            <div>
                                <div class="font-semibold text-sm text-gray-800 dark:text-gray-200">${displayName}</div>
                                <div class="flex items-center gap-2">
                                     <div class="text-xs text-gray-500 dark:text-gray-400">ID: ${item.user_id || '-'}</div>
                                     ${item.user_role ? `<span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 uppercase tracking-wider font-bold">${item.user_role}</span>` : ''}
                                </div>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-bold rounded-full flex items-center gap-1.5 shadow-sm transform group-hover:scale-105 transition-transform ${badgeClass}">
                            <i class="fas ${icon} text-[10px]"></i> ${actionLabel}
                        </span>
                    </div>
                    
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2 border-b border-gray-200 dark:border-gray-700 pb-2">
                            ${mainDesc} 
                        </div>
                        <div class="max-h-40 overflow-y-auto custom-scrollbar pr-1">
                            ${changeDetails}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
  }

// DATA BARU: Method untuk merender tabel detail kabupaten - SEMUA DATA
renderKabupatenTable(mapData) {
    const legendContainer = document.getElementById('map-legend');
    if (!legendContainer) return;
    
    // Calculate statistics for legend
    const counts = Object.values(mapData.provinsi_counts || {});
    const total = counts.reduce((a, b) => a + b, 0);
    const max = Math.max(...counts, 1);
    
    // Define legend items
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
      <div class="w-full">
        <!-- Legend Header -->
        <div class="flex flex-wrap justify-between items-center mb-6">
          <div class="text-center">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Permohonan</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white">${total.toLocaleString('id-ID')}</div>
          </div>
          <div class="flex flex-wrap gap-4">
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
    
    html += `
          </div>
        </div>
        
        <!-- DATA BARU: Tabel Detail Kabupaten - SEMUA DATA -->
        <div class="bg-white/80 dark:bg-gray-800/80 rounded-2xl p-6 border border-white/20 dark:border-gray-700/50">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center">
              <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
              Detail Permohonan per Kabupaten/Kota
            </h3>
            <div class="text-xs text-gray-500 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-full">
              <i class="fas fa-arrows-up-down mr-1"></i>Scroll untuk melihat semua data
            </div>
          </div>
          
          <div class="max-h-96 overflow-y-auto custom-scrollbar">
    `;
    
    // Check if kabupaten data exists
    if (!mapData.kabupaten_detail || Object.keys(mapData.kabupaten_detail).length === 0) {
        html += `
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                    <i class="fas fa-map-marked-alt text-2xl opacity-50"></i>
                </div>
                <h4 class="text-lg font-semibold mb-2">Tidak Ada Data</h4>
                <p class="text-sm">Belum ada data kabupaten/kota untuk ditampilkan</p>
            </div>
        `;
    } else {
        // Hitung total kabupaten
        let totalKabupaten = 0;
        Object.values(mapData.kabupaten_detail).forEach(kabList => {
            totalKabupaten += kabList.length;
        });
        
        // Summary statistics
        html += `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">${totalKabupaten}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Total Kabupaten/Kota</div>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">${Object.keys(mapData.kabupaten_detail).length}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Provinsi</div>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">${total.toLocaleString('id-ID')}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Total Permohonan</div>
                </div>
            </div>
        `;
        
        // Group and display ALL kabupaten data
        Object.entries(mapData.kabupaten_detail).forEach(([provinsi, kabupatenList]) => {
            if (kabupatenList.length > 0) {
                // Hitung total untuk provinsi ini
                const totalProvinsi = kabupatenList.reduce((sum, kab) => sum + kab.jumlah, 0);
                const percentageProvinsi = total > 0 ? Math.round((totalProvinsi / total) * 100) : 0;
                
                html += `
                    <div class="mb-8 last:mb-0">
                        <div class="flex items-center justify-between mb-4 p-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700/30 dark:to-gray-600/30 rounded-xl">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-white">
                                    ${provinsi}
                                </h4>
                                <div class="flex items-center space-x-4 mt-1">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        ${kabupatenList.length} kabupaten/kota
                                    </span>
                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                        ${totalProvinsi.toLocaleString('id-ID')} permohonan
                                    </span>
                                    <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                        ${percentageProvinsi}% dari total
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Rata-rata per kabupaten</div>
                                <div class="text-sm font-bold text-gray-800 dark:text-white">
                                    ${Math.round(totalProvinsi / kabupatenList.length).toLocaleString('id-ID')}
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                `;
                
                // Urutkan kabupaten berdasarkan jumlah descending
                const sortedKabupaten = [...kabupatenList].sort((a, b) => b.jumlah - a.jumlah);
                
                // Tampilkan SEMUA kabupaten untuk provinsi ini
                sortedKabupaten.forEach((kab, index) => {
                    const percentage = totalProvinsi > 0 ? Math.round((kab.jumlah / totalProvinsi) * 100) : 0;
                    const isTop3 = index < 3;
                    
                    html += `
                        <div class="group relative overflow-hidden bg-white dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-600/30 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-300 hover:shadow-md ${isTop3 ? 'ring-2 ring-amber-400 dark:ring-amber-500' : ''}">
                            ${isTop3 ? `
                                
                            ` : ''}
                            <div class="p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate flex-1" title="${kab.kabupaten}">
                                        ${kab.kabupaten}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-gray-800 dark:text-white">
                                            ${kab.jumlah.toLocaleString('id-ID')}
                                        </div>
                                        <div class="text-xs ${percentage >= 10 ? 'text-green-600 dark:text-green-400' : percentage >= 5 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400'} font-medium">
                                            ${percentage}% dari provinsi
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Progress bar untuk persentase dalam provinsi -->
                            <div class="absolute bottom-0 left-0 w-full h-1 bg-gray-200 dark:bg-gray-600">
                                <div class="h-full bg-gradient-to-r from-blue-400 to-blue-600 transition-all duration-500" 
                                     style="width: ${Math.min(percentage, 100)}%"></div>
                            </div>
                        </div>
                    `;
                });
                
                html += `</div></div>`;
            }
        });
    }
    
    html += `
          </div>
        </div>
      </div>
    `;
    
    legendContainer.innerHTML = html;
}

  renderMapLegend(mapData) {
    const legendContainer = document.getElementById('map-legend');
    if (!legendContainer) return;
    
    // Calculate statistics for legend
    const counts = Object.values(mapData.provinsi_counts || {});
    const total = counts.reduce((a, b) => a + b, 0);
    const max = Math.max(...counts, 1); // Ensure max is at least 1 to avoid division by zero
    
    // Define legend items based on thresholds
    const legendItems = [
      { 
        key: 'low', 
        label: 'Rendah', 
        color: '#10b981', // Green
        description: `0 - ${Math.round(max * 0.3)} permohonan`,
        count: counts.filter(c => c <= max * 0.3).length
      },
      { 
        key: 'medium', 
        label: 'Sedang', 
        color: '#f59e0b', // Amber
        description: `${Math.round(max * 0.3)} - ${Math.round(max * 0.7)} permohonan`,
        count: counts.filter(c => c > max * 0.3 && c <= max * 0.7).length
      },
      { 
        key: 'high', 
        label: 'Tinggi', 
        color: '#dc2626', // Red
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
  window.dashboardManager = new DashboardManager();
  
  // Initialize tab manager
  window.tabManager = new TabManager();
  
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


/* Tab System Styles */
.tab-panel {
    display: block;
    animation: fadeInUp 0.5s ease-out;
}

.tab-panel.hidden {
    display: none;
}

.tab-button {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.tab-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.5s;
}

.tab-button:hover::before {
    left: 100%;
}

.tab-button.active {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
}

/* Keyboard shortcut hint */
.tab-button::after {
    content: attr(data-shortcut);
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ef4444;
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    opacity: 0;
    transition: opacity 0.3s;
}

.tab-button:hover::after {
    opacity: 1;
}

/* Animation for tab content */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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
  overflow: visible; /* Important for tooltips */
}

#map-container svg {
  width: 100% !important;
  height: 100% !important;
  overflow: visible; /* Important for tooltips */
}

/* Province Path Styles */
#map-container .province {
  transition: all 0.2s ease;
  cursor: pointer;
}

#map-container .province:hover {
  filter: brightness(1.1);
}

/* Ensure nothing blocks tooltips */
.backdrop-blur-sm,
.bg-white\/80,
.dark\:bg-gray-800\/80 {
  z-index: auto !important;
}

/* Override z-index if needed for blocking elements */
.rounded-3xl {
  position: relative;
  z-index: auto;
}

/* For map container to allow tooltips */
.chart-container,
.map-legend {
  z-index: auto !important;
}

/* Map Tooltip Styles */
.datamaps-hoverover {
  position: absolute !important;
  z-index: 10001 !important; /* Ensure tooltips are on top */
  pointer-events: none !important; /* Prevent interference */
  background: rgba(255, 255, 255, 0.98) !important;
  backdrop-filter: blur(10px) !important;
  -webkit-backdrop-filter: blur(10px) !important;
  border: 1px solid rgba(0, 0, 0, 0.1) !important;
  border-radius: 8px !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
  font-family: system-ui, -apple-system, sans-serif !important;
  font-size: 13px !important;
  line-height: 1.4 !important;
  max-width: 250px !important;
  word-wrap: break-word !important;
}

.dark .datamaps-hoverover {
  background: rgba(31, 41, 55, 0.98) !important;
  border-color: rgba(255, 255, 255, 0.2) !important;
  color: #e5e7eb !important;
}

/* Map Container Styles for Centering */
#map-container {
    position: relative;
    min-height: 400px;
    border-radius: 1rem;
    overflow: visible;
    margin: 0 auto; /* Center horizontally */
}

/* Responsive centering */
@media (max-width: 1024px) {
    #map-container {
        max-width: 90vw;
        min-height: 350px;
    }
}

@media (max-width: 768px) {
    #map-container {
        max-width: 95vw;
        min-height: 300px;
    }
}

@media (max-width: 640px) {
    #map-container {
        max-width: 100%;
        min-height: 250px;
    }
}

/* Ensure map stays centered */
.map-center-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
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
  -webkit-tap-highlight-color: transparent; /* Removes the highlight on tap for mobile */
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

/* Enhanced Pie Chart Styles */
.pie-chart-container {
    position: relative;
    height: 320px;
}

.pie-chart-legend {
    max-height: 200px;
    overflow-y: auto;
}

.pie-chart-legend::-webkit-scrollbar {
    width: 4px;
}

.pie-chart-legend::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 2px;
}

.pie-chart-legend::-webkit-scrollbar-thumb {
    background: rgba(59, 130, 246, 0.5);
    border-radius: 2px;
}

/* Responsive adjustments for pie charts */
@media (max-width: 1024px) {
    .pie-chart-container {
        height: 280px;
    }
}

@media (max-width: 768px) {
    .pie-chart-container {
        height: 250px;
    }
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

/* Ensure canvas elements are properly handled */
canvas {
    display: block;
    max-width: 100%;
    height: auto;
}

.chart-container canvas {
    width: 100% !important;
    height: 100% !important;
}

/* Enhanced Map Legend Styles */
#map-legend {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

/* Responsive table styles */
@media (max-width: 768px) {
    #map-legend .grid {
        grid-template-columns: 1fr !important;
    }
}

/* Hover effects for kabupaten items */
.kabupaten-item {
    transition: all 0.3s ease;
}

.kabupaten-item:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Map Tooltip Styles */
.datamaps-hoverover {
    position: absolute !important;
    z-index: 10001 !important; 
    pointer-events: none !important; 
    background: rgba(255, 255, 255, 0.98) !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
    border: 1px solid rgba(0, 0, 0, 0.1) !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
    font-family: system-ui, -apple-system, sans-serif !important;
    font-size: 13px !important;
    line-height: 1.4 !important;
    max-width: 250px !important;
    word-wrap: break-word !important;
}

.dark .datamaps-hoverover {
    background: rgba(31, 41, 55, 0.98) !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
    color: #e5e7eb !important;
}

/* Enhanced Scrollbar for Kabupaten Table */
.max-h-96::-webkit-scrollbar {
    width: 8px;
}

.max-h-96::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 4px;
}

.max-h-96::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 4px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
}

/* Hover effects for kabupaten cards */
.kabupaten-card {
    transition: all 0.3s ease;
}

.kabupaten-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.15);
}

/* Custom date range styles */
#customRangeContainer input[type="date"] {
    border-bottom: 1px solid #d1d5db;
    padding: 2px 4px;
}

#customRangeContainer input[type="date"]:focus {
    outline: none;
    border-bottom-color: #3b82f6;
}

.dark #customRangeContainer input[type="date"] {
    border-bottom-color: #4b5563;
    color: #e5e7eb;
}

.dark #customRangeContainer input[type="date"]:focus {
    border-bottom-color: #60a5fa;
}

/* Responsive design */
@media (max-width: 768px) {
    #customRangeContainer .flex {
        flex-direction: column;
        gap: 8px;
    }
    
    #customRangeContainer .pt-4 {
        padding-top: 0;
    }
}


/* Enhanced Filter Styles */
.filter-container {
  transition: all 0.3s ease-in-out;
}

.filter-select {
  transition: all 0.2s ease;
}

.filter-select:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  border-color: #3b82f6;
}

.quick-filter-btn {
  transition: all 0.2s ease;
}

.quick-filter-btn:hover {
  transform: translateY(-1px);
}

/* Responsive improvements */
@media (max-width: 768px) {
  .filter-grid {
    grid-template-columns: 1fr !important;
    gap: 1rem !important;
  }
  
  .filter-actions {
    flex-direction: column;
    align-items: stretch;
  }
}

/* Dark mode enhancements */
.dark .filter-select {
  background: rgba(55, 65, 81, 0.5);
  border-color: rgba(75, 85, 99, 0.5);
}

.dark .filter-select:focus {
  border-color: #60a5fa;
  box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
}

</style>


<?php include __DIR__ . '/components/system-update-modal.php'; ?>


<?php require __DIR__ . '/../inc/layout_footer.php'; ?>

