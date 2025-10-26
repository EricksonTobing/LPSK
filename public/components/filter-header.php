<?php
// Filter Header Component
// Digunakan di dashboard.php untuk filter tahun dan bulan
?>

<!-- Enhanced Year & Month Filter -->
<div class="flex flex-col space-y-4">
  <div class="flex items-center space-x-4">
    <div class="backdrop-blur-sm bg-white/70 dark:bg-gray-800/70 rounded-2xl p-4 border border-white/20 dark:border-gray-700/50 shadow-xl flex-1">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-gradient-to-r from-primary-blue to-primary-red rounded-xl flex items-center justify-center">
          <i class="fas fa-calendar-alt text-white text-sm"></i>
        </div>
        <div class="flex items-center space-x-3 flex-1">
          <!-- Filter Rentang Waktu -->
          <div>
            <label for="dateRangeFilter" class="text-xs text-gray-500 dark:text-gray-400 block">Rentang Waktu</label>
            <select id="dateRangeFilter" class="bg-transparent border-0 text-sm font-bold text-gray-700 dark:text-gray-300 focus:outline-none cursor-pointer">
              <option value="year">Per Tahun</option>
              <option value="month">Per Bulan</option>
              <option value="custom">Rentang Kustom</option>
            </select>
          </div>

          <!-- Filter Tahun -->
          <div id="yearFilterContainer">
            <label for="yearFilter" class="text-xs text-gray-500 dark:text-gray-400 block">Tahun</label>
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
          
          <!-- Filter Bulan -->
          <div id="monthFilterContainer">
            <label for="monthFilter" class="text-xs text-gray-500 dark:text-gray-400 block">Bulan</label>
            <select id="monthFilter" class="bg-transparent border-0 text-sm font-bold text-gray-700 dark:text-gray-300 focus:outline-none cursor-pointer">
              <?php
                $bulanNama = [
                  1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                  7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
                ];
                $selectedMonth = (isset($_GET['month']) && is_numeric($_GET['month']) && (int)$_GET['month']>=1 && (int)$_GET['month']<=12) ? (int)$_GET['month'] : '';
                echo "<option value=\"\">Semua bulan</option>";
                for ($m=1; $m<=12; $m++) {
                  $sel = ($selectedMonth === $m) ? 'selected' : '';
                  echo "<option value=\"$m\" $sel>{$bulanNama[$m]}</option>";
                }
              ?>
            </select>
          </div>

          <!-- Filter Rentang Kustom -->
          <div id="customRangeContainer" class="hidden">
            <div class="flex items-center space-x-2">
              <div>
                <label for="startDate" class="text-xs text-gray-500 dark:text-gray-400 block">Dari</label>
                <input type="date" id="startDate" class="bg-transparent border-0 text-sm font-bold text-gray-700 dark:text-gray-300 focus:outline-none cursor-pointer max-w-32">
              </div>
              <div class="pt-4">
                <span class="text-gray-400">-</span>
              </div>
              <div>
                <label for="endDate" class="text-xs text-gray-500 dark:text-gray-400 block">Sampai</label>
                <input type="date" id="endDate" class="bg-transparent border-0 text-sm font-bold text-gray-700 dark:text-gray-300 focus:outline-none cursor-pointer max-w-32">
              </div>
            </div>
          </div>
          
          <!-- Separator -->
          <div class="h-8 w-px bg-gray-300 dark:bg-gray-600"></div>
          
          <!-- Quick Filter Buttons -->
          <div class="flex items-center space-x-2 ml-2">
            <button id="btnCurrentMonth" class="text-xs px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors" title="Bulan Ini">
              <i class="fas fa-calendar-day mr-1"></i>Bulan Ini
            </button>
            <button id="btnLastMonth" class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" title="Bulan Lalu">
              <i class="fas fa-calendar-minus mr-1"></i>Bulan Lalu
            </button>
            <button id="btnCurrentYear" class="text-xs px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors" title="Tahun Ini">
              <i class="fas fa-calendar mr-1"></i>Tahun Ini
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <button id="refreshBtn" class="group relative overflow-hidden bg-gradient-to-r from-primary-blue to-primary-red hover:from-blue-600 hover:to-purple-700 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl h-fit">
      <div class="absolute inset-0 bg-white/20 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"></div>
      <div class="relative flex items-center space-x-2">
        <i class="fas fa-sync-alt transition-transform group-hover:rotate-180 duration-500"></i>
        <span>Refresh</span>
      </div>
    </button>
  </div>

  <!-- Filter Indicator -->
  <div id="filterIndicator" class="hidden items-center gap-3 text-sm bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-lg">
    <div class="flex items-center gap-2">
      <i class="fas fa-filter text-blue-500"></i>
      <span class="text-gray-600 dark:text-gray-400">Filter:</span>
      <span id="filterText" class="font-medium text-blue-600 dark:text-blue-400"></span>
    </div>
    <button id="clearFilter" class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors flex items-center gap-1">
      <i class="fas fa-times"></i>
      <span>Reset</span>
    </button>
  </div>
</div>

<script>
// Enhanced filter functionality with date range
document.addEventListener('DOMContentLoaded', function() {
    const dateRangeFilter = document.getElementById('dateRangeFilter');
    const yearFilterContainer = document.getElementById('yearFilterContainer');
    const monthFilterContainer = document.getElementById('monthFilterContainer');
    const customRangeContainer = document.getElementById('customRangeContainer');
    const filterIndicator = document.getElementById('filterIndicator');
    const filterText = document.getElementById('filterText');
    const clearFilter = document.getElementById('clearFilter');
    
    // Set default dates for custom range
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const firstDayOfLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
    const lastDayOfLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
    
    document.getElementById('startDate').value = firstDayOfMonth.toISOString().split('T')[0];
    document.getElementById('endDate').value = today.toISOString().split('T')[0];
    
    // Toggle filter visibility based on date range selection
    dateRangeFilter.addEventListener('change', function() {
        const value = this.value;
        
        // Hide all containers first
        yearFilterContainer.classList.add('hidden');
        monthFilterContainer.classList.add('hidden');
        customRangeContainer.classList.add('hidden');
        
        // Show relevant containers
        if (value === 'year') {
            yearFilterContainer.classList.remove('hidden');
        } else if (value === 'month') {
            yearFilterContainer.classList.remove('hidden');
            monthFilterContainer.classList.remove('hidden');
        } else if (value === 'custom') {
            customRangeContainer.classList.remove('hidden');
        }
        
        // Update filter indicator
        if (window.dashboardManager) {
            window.dashboardManager.updateFilterIndicator();
        }
    });
    
    // Clear filter functionality
    clearFilter.addEventListener('click', function() {
        // Reset all filters to default
        dateRangeFilter.value = 'year';
        document.getElementById('yearFilter').value = new Date().getFullYear();
        document.getElementById('monthFilter').value = '';
        document.getElementById('startDate').value = firstDayOfMonth.toISOString().split('T')[0];
        document.getElementById('endDate').value = today.toISOString().split('T')[0];
        
        // Trigger change event
        dateRangeFilter.dispatchEvent(new Event('change'));
        
        // Hide filter indicator
        filterIndicator.classList.add('hidden');
        
        // Reload dashboard data
        if (window.dashboardManager) {
            window.dashboardManager.loadDashboardData();
        }
    });
    
    // Trigger change event to set initial state
    dateRangeFilter.dispatchEvent(new Event('change'));
    
    // Quick filter: Tahun ini
    const btnCurrentYear = document.getElementById('btnCurrentYear');
    if (btnCurrentYear) {
        btnCurrentYear.addEventListener('click', () => {
            const currentDate = new Date();
            dateRangeFilter.value = 'year';
            dateRangeFilter.dispatchEvent(new Event('change'));
            document.getElementById('yearFilter').value = currentDate.getFullYear();
            if (window.dashboardManager) {
                window.dashboardManager.loadDashboardData();
                window.dashboardManager.updateFilterIndicator();
            }
        });
    }
    
    // Event listeners for custom date inputs
    document.getElementById('startDate').addEventListener('change', function() {
        if (dateRangeFilter.value === 'custom' && window.dashboardManager) {
            window.dashboardManager.loadDashboardData();
            window.dashboardManager.updateFilterIndicator();
        }
    });
    
    document.getElementById('endDate').addEventListener('change', function() {
        if (dateRangeFilter.value === 'custom' && window.dashboardManager) {
            window.dashboardManager.loadDashboardData();
            window.dashboardManager.updateFilterIndicator();
        }
    });
    
    // Expose functions for dashboardManager
    window.filterManager = {
        showFilterIndicator: function(text) {
            filterText.textContent = text;
            filterIndicator.classList.remove('hidden');
        },
        
        hideFilterIndicator: function() {
            filterIndicator.classList.add('hidden');
        },
        
        getFilterState: function() {
            return {
                dateRange: dateRangeFilter.value,
                year: document.getElementById('yearFilter').value,
                month: document.getElementById('monthFilter').value,
                startDate: document.getElementById('startDate').value,
                endDate: document.getElementById('endDate').value
            };
        }
    };
});
</script>