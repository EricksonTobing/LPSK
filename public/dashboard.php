<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';
require_login();
$title = 'Dashboard';
require __DIR__ . '/../inc/layout_header.php';
require __DIR__ . '/../inc/layout_nav.php';
?>

<!-- Tambahkan filter tahun di bagian atas halaman -->
<div class="flex justify-between items-center mb-6">
  <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">Dashboard</h1>
  
  <div class="flex items-center gap-3">
    <label for="yearFilter" class="text-sm font-medium text-gray-700 dark:text-gray-300">Tahun:</label>
    <select id="yearFilter" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 text-gray-700 dark:text-gray-300">
      <?php
      // Generate tahun dari 2020 hingga tahun sekarang
      $currentYear = date('Y');
      for ($year = $currentYear; $year >= 2020; $year--) {
        $selected = $year == $currentYear ? 'selected' : '';
        echo "<option value='$year' $selected>$year</option>";
      }
      ?>
    </select>
  </div>
</div>

<!-- Kartu ringkasan -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8" id="stats-cards">
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700 border-l-4 border-l-blue-500">
    <div class="flex justify-between items-start">
      <div>
        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Permohonan</div>
        <div class="text-2xl font-bold text-gray-800 dark:text-white" id="permohonan-count">0</div>
      </div>
      <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
        <i class="text-xl fas fa-file-lines"></i>
      </div>
    </div>
    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
      <span class="text-xs flex items-center text-green-600 dark:text-green-400">
        <i class="mr-1 fas fa-arrow-up"></i>
        <span>12.5%</span>
      </span>
      <span class="text-xs text-gray-500 dark:text-gray-400">vs bulan lalu</span>
    </div>
  </div>
  
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700 border-l-4 border-l-green-500">
    <div class="flex justify-between items-start">
      <div>
        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Penelaahan</div>
        <div class="text-2xl font-bold text-gray-800 dark:text-white" id="penelaahan-count">0</div>
      </div>
      <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
        <i class="text-xl fas fa-magnifying-glass"></i>
      </div>
    </div>
    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
      <span class="text-xs flex items-center text-green-600 dark:text-green-400">
        <i class="mr-1 fas fa-arrow-up"></i>
        <span>8.3%</span>
      </span>
      <span class="text-xs text-gray-500 dark:text-gray-400">vs bulan lalu</span>
    </div>
  </div>
  
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700 border-l-4 border-l-amber-500">
    <div class="flex justify-between items-start">
      <div>
        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Layanan</div>
        <div class="text-2xl font-bold text-gray-800 dark:text-white" id="layanan-count">0</div>
      </div>
      <div class="p-2 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
        <i class="text-xl fas fa-handshake"></i>
      </div>
    </div>
    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
      <span class="text-xs flex items-center text-red-600 dark:text-red-400">
        <i class="mr-1 fas fa-arrow-down"></i>
        <span>4.2%</span>
      </span>
      <span class="text-xs text-gray-500 dark:text-gray-400">vs bulan lalu</span>
    </div>
  </div>
  
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700 border-l-4 border-l-purple-500">
    <div class="flex justify-between items-start">
      <div>
        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Pengeluaran</div>
        <div class="text-2xl font-bold text-gray-800 dark:text-white" id="pengeluaran-count">Rp 0</div>
      </div>
      <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
        <i class="text-xl fas fa-coins"></i>
      </div>
    </div>
    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
      <span class="text-xs flex items-center text-green-600 dark:text-green-400">
        <i class="mr-1 fas fa-arrow-up"></i>
        <span>15.7%</span>
      </span>
      <span class="text-xs text-gray-500 dark:text-gray-400">vs bulan lalu</span>
    </div>
  </div>
</div>

<!-- Grafik -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
  <!-- Chart Multi-Line untuk Statistik Permohonan -->
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700">
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
    <canvas id="chartPermohonan" class="h-72 w-full"></canvas>
  </div>
  
  <!-- Chart Line untuk Statistik Keuangan -->
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-5 gap-3">
      <h2 class="font-semibold text-lg text-gray-800 dark:text-white">Statistik Keuangan per Anggaran</h2>
      <div class="relative">
        <button id="timeFilterBtn" class="text-sm px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg flex items-center bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
          <span>Bulanan</span>
          <i class="fas fa-chevron-down ml-2 text-xs"></i>
        </button>
      </div>
    </div>
    <canvas id="chartPengeluaran" class="h-72 w-full"></canvas>
  </div>
</div>

<!-- Peta dan Data Terbaru -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700">
    <h2 class="font-semibold text-lg text-gray-800 dark:text-white mb-5">Distribusi Permohonan per Provinsi</h2>
    <div id="map-container" class="h-80 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
      <p class="text-gray-500 dark:text-gray-400 text-center p-4" id="map-loading">
        Memuat peta...
      </p>
    </div>
    <div id="map-legend" class="flex flex-wrap justify-center gap-4 mt-4"></div>
  </div>
  
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700">
    <h2 class="font-semibold text-lg text-gray-800 dark:text-white mb-5">Aktivitas Terbaru</h2>
    <div class="space-y-4">
      <div class="flex items-start p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
        <div class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 p-2 rounded-lg mr-3 flex-shrink-0">
          <i class="fas fa-file-import"></i>
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-800 dark:text-white">Permohonan baru diterima</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Nomor: PMH-2023-0876</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">2 jam yang lalu</p>
        </div>
      </div>
      <div class="flex items-start p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
        <div class="bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 p-2 rounded-lg mr-3 flex-shrink-0">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-800 dark:text-white">Penelaahan selesai</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Nomor: PNL-2023-0543</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">5 jam yang lalu</p>
        </div>
      </div>
      <div class="flex items-start p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
        <div class="bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 p-2 rounded-lg mr-3 flex-shrink-0">
          <i class="fas fa-coins"></i>
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-800 dark:text-white">Pengeluaran baru dicatat</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Nomor: KWT-2023-0321</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">1 hari yang lalu</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Load libraries -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://d3js.org/d3.v3.min.js"></script>
<script src="https://d3js.org/topojson.v1.min.js"></script>
<script src="datamaps.indonesia.min.js" onerror="console.error('Gagal memuat library peta Indonesia')"></script>

<script>
  
// Variabel global untuk menyimpan instance chart
let permohonanChart = null;
let pengeluaranChart = null;

// Fungsi utama untuk memuat data dashboard
async function loadDashboardData() {
  // Tambahkan di awal fungsi loadDashboardData
document.getElementById('stats-cards').classList.add('opacity-50');
document.querySelectorAll('#stats-cards .text-2xl').forEach(el => {
  el.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
});
  try {
    const selectedYear = document.getElementById('yearFilter').value;
    const res = await fetch(`api_stats.php?year=${selectedYear}`);
    
    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
    
    const data = await res.json();
    
    // Update card values dengan format yang benar
    document.getElementById('permohonan-count').textContent = data.counts.permohonan.toLocaleString('id-ID');
    document.getElementById('penelaahan-count').textContent = data.counts.penelaahan.toLocaleString('id-ID');
    document.getElementById('layanan-count').textContent = data.counts.layanan.toLocaleString('id-ID');
    document.getElementById('pengeluaran-count').textContent = 'Rp ' + data.counts.pengeluaran.toLocaleString('id-ID');
    
    // Render charts
    renderCharts(data);
    
    // Render map jika data tersedia
    if (data.map && data.map.provinsi_counts) {
      renderMap(data.map.provinsi_counts, data.map.provinsi_fillkeys);
    }
    
  } catch (error) {
    console.error('Error loading dashboard data:', error);
    alert('Gagal memuat data dashboard. Silakan refresh halaman.');
  }
  // Dan di akhir (dalam finally)
finally {
  document.getElementById('stats-cards').classList.remove('opacity-50');
}
}

// Fungsi untuk merender chart
function renderCharts(data) {
  // Hancurkan chart lama jika ada
  if (permohonanChart) {
    permohonanChart.destroy();
  }
  if (pengeluaranChart) {
    pengeluaranChart.destroy();
  }

// Chart 1: Multi-line (permohonan, penelaahan, layanan)
permohonanChart = new Chart(permohonanCtx, {
  type: 'line',
  data: {
    labels: data.charts.permohonan_line.labels,
    datasets: [
      {
        label: 'Permohonan',
        data: data.charts.permohonan_line.permohonan_data,
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        fill: true,
        borderWidth: 2
      },
      {
        label: 'Penelaahan', 
        data: data.charts.permohonan_line.penelaahan_data,
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        tension: 0.4,
        fill: true,
        borderWidth: 2
      },
      {
        label: 'Layanan',
        data: data.charts.permohonan_line.layanan_data,
        borderColor: '#f59e0b',
        backgroundColor: 'rgba(245, 158, 11, 0.1)',
        tension: 0.4,
        fill: true,
        borderWidth: 2
      }
    ]
  },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { 
          legend: { 
            position: 'bottom',
            labels: {
              usePointStyle: true,
              padding: 20
            }
          } 
        },
        scales: { 
          y: { 
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          } 
        }
      }
    });
  }

  // Chart 2: Line chart untuk keuangan per anggaran
  const pengeluaranCtx = document.getElementById('chartPengeluaran');
  if (pengeluaranCtx) {
    // Format data untuk chart pengeluaran
    const labels = data.charts.keuangan.labels.map(label => {
      const [year, month] = label.split('-');
      const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      return `${monthNames[parseInt(month) - 1]} `;
    });
    
    pengeluaranChart = new Chart(pengeluaranCtx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: data.charts.keuangan.datasets.map((dataset, index) => ({
          label: dataset.label,
          data: dataset.data,
          borderColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'][index % 6],
          backgroundColor: ['rgba(59, 130, 246, 0.1)', 'rgba(16, 185, 129, 0.1)', 'rgba(245, 158, 11, 0.1)', 'rgba(139, 92, 246, 0.1)', 'rgba(236, 72, 153, 0.1)', 'rgba(6, 182, 212, 0.1)'][index % 6],
          tension: 0.4,
          fill: true,
          borderWidth: 2
        }))
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { 
          legend: { 
            position: 'bottom',
            labels: {
              usePointStyle: true,
              padding: 20
            }
          } 
        },
        scales: { 
          y: { 
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                if (value >= 1000000) {
                  return 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                } else if (value >= 1000) {
                  return 'Rp ' + (value / 1000).toFixed(1) + 'Rb';
                }
                return 'Rp ' + value;
              }
            }
          } 
        }
      }
    });
  }
}

// Fungsi untuk merender peta
function renderMap(provinsiData, fillKeys) {
  const mapContainer = document.getElementById("map-container");
  if (!mapContainer) {
    console.error("Map container not found");
    return;
  }

  // Hapus peta lama jika ada
  const oldSvg = mapContainer.querySelector('svg');
  if (oldSvg) {
    oldSvg.remove();
  }

  // Tampilkan loading text
  document.getElementById("map-loading").style.display = "block";
  
  // Coba render peta setelah sedikit delay untuk memastikan DOM siap
  setTimeout(() => {
    try {
      // Data untuk peta
      const mapData = {};
      const allProvinces = ['Aceh', 'Sumatera Utara', 'Sumatera Barat'];
      
      allProvinces.forEach(province => {
        mapData[province] = {
          value: provinsiData[province] || 0,
          fillKey: fillKeys[province] || 'low'
        };
      });

      // Inisialisasi peta
      const map = new DatamapIndonesia({
        element: mapContainer,
        geographyConfig: {
          highlightOnHover: true,
          popupOnHover: true,
          highlightBorderWidth: 3,
          highlightBorderColor: '#FFFFFF',
          highlightFillColor: function(geo) {
            const provinceName = geo.properties.provinsi;
            const item = mapData[provinceName];
            if (item && item.fillKey) {
              const fillColors = {
                high: "#d73027",
                medium: "#fc8d59", 
                low: "#fee08b",
                default: "#e0e0e0"
              };
              return fillColors[item.fillKey] || fillColors.default;
            }
            return "#e0e0e0";
          }
        },
        fills: {
          defaultFill: "#e0e0e0",
          high: "#d73027",
          medium: "#fc8d59", 
          low: "#fee08b"
        },
        data: mapData
      });
      
      // Sembunyikan loading text
      document.getElementById("map-loading").style.display = "none";
      addMapLegend();
      
    } catch (error) {
      console.error("Error initializing map:", error);
      document.getElementById("map-loading").textContent = "Gagal memuat peta. Pastikan file datamaps.indonesia.min.js sudah dimuat.";
    }
  }, 100);
}

// Fungsi untuk menambahkan legenda peta
function addMapLegend() {
  const legendDiv = document.getElementById("map-legend");
  if (!legendDiv) return;
  
  legendDiv.innerHTML = '';
  
  const legendConfig = [
    { key: "low", label: "Rendah", color: "#fee08b" },
    { key: "medium", label: "Sedang", color: "#fc8d59" },
    { key: "high", label: "Tinggi", color: "#d73027" }
  ];
  
  legendConfig.forEach(item => {
    const box = document.createElement("div");
    box.className = "flex items-center gap-2 text-sm";
    box.innerHTML = `
      <span style="display:inline-block;width:16px;height:16px;background:${item.color};border:1px solid #ccc"></span>
      <span>${item.label}</span>
    `;
    legendDiv.appendChild(box);
  });
}

// Event listener saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
  // Load data pertama kali
  loadDashboardData();
  
  // Event listener untuk filter tahun
  document.getElementById('yearFilter').addEventListener('change', function() {
    loadDashboardData();
  });
  
  // Event listener untuk filter waktu (jika diperlukan)
  document.getElementById('timeFilterBtn').addEventListener('click', function() {
    // Tambahkan logika filter waktu di sini jika diperlukan
    console.log('Filter waktu diklik');
  });
});
</script>

<style>
.map-tooltip {
  position: absolute;
  z-index: 10000 !important;
  pointer-events: none;
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  transition: opacity 0.2s ease;
}

.province path {
  transition: all 0.3s ease;
  cursor: pointer;
}

.province path:hover {
  filter: brightness(1.1);
  stroke-width: 2px;
  stroke: #fff;
}

#map-container {
  position: relative;
  width: 100%;
  height: 320px;
  overflow: hidden;
}

#map-container svg {
  width: 100%;
  height: 100%;
}

/* Pastikan tooltip tidak terhalang */
body {
  position: relative;
}

/* Styling untuk chart container */
canvas {
  display: block;
  max-width: 100%;
}
</style>

<?php require __DIR__ . '/../inc/layout_footer.php'; ?>