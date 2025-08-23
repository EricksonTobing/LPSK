<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: $persist(false) }" :class="{ 'dark': darkMode }">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>LPSK App - Tabel Data</title>
  
  <!-- Tailwind via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#eff6ff',
              100: '#dbeafe',
              500: '#3b82f6',
              600: '#2563eb',
              700: '#1d4ed8',
            }
          }
        }
      }
    }
  </script>
  
  <!-- AlpineJS -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  
  <!-- Icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    .fade-in {
      animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .tab-active {
      border-bottom: 3px solid #3b82f6;
      color: #3b82f6;
      font-weight: 600;
    }
    
    .tab-inactive {
      border-bottom: 3px solid transparent;
      color: #6b7280;
      transition: all 0.2s ease;
    }
    
    .tab-inactive:hover {
      color: #3b82f6;
      border-bottom: 3px solid #93c5fd;
    }
    
    .table-container {
      max-height: calc(100vh - 250px);
      overflow-y: auto;
    }
    
    /* Scrollbar styling */
    .table-container::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }
    
    .table-container::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 4px;
    }
    
    .table-container::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 4px;
    }
    
    .table-container::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }
    
    .dark .table-container::-webkit-scrollbar-track {
      background: #374151;
    }
    
    .dark .table-container::-webkit-scrollbar-thumb {
      background: #4b5563;
    }
    
    .dark .table-container::-webkit-scrollbar-thumb:hover {
      background: #6b7280;
    }

    #map-container {
  position: relative;
  width: 100%;
  height: 320px;
}

#map-container svg {
  width: 100%;
  height: 100%;
}

.map-tooltip {
  position: absolute;
  background: #fff;
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
  font-size: 12px;
  pointer-events: none;
  z-index: 1000;
}

.map-tooltip strong {
  display: block;
  margin-bottom: 4px;
  color: #333;
}
  </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-200 flex flex-col min-h-screen">

<!-- Header -->
<header class="bg-white dark:bg-gray-800 shadow-lg sticky top-0 z-30">
  <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
    <div class="flex items-center">
      <button @click="sidebarOpen = !sidebarOpen" class="md:hidden mr-2 text-gray-500 dark:text-gray-400 focus:outline-none">
        <i class="fas fa-bars text-lg"></i>
      </button>
      <a href="index.php" class="text-xl font-bold text-primary-600 dark:text-primary-400 flex items-center">
        <i class="fas fa-shield-alt mr-2"></i>LPSK Data
      </a>
    </div>
    
    <nav class="hidden md:flex gap-6 text-sm">
      <a href="dashboard.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center py-1">
        <i class="fas fa-chart-simple mr-1"></i> Dashboard
      </a>
      <a href="table.php?t=permohonan" class="tab-active transition-colors flex items-center py-1">
        <i class="fas fa-file-lines mr-1"></i> Permohonan
      </a>
      <a href="table.php?t=penelaahan" class="tab-inactive transition-colors flex items-center py-1">
        <i class="fas fa-magnifying-glass mr-1"></i> Penelaahan
      </a>
      <a href="table.php?t=layanan" class="tab-inactive transition-colors flex items-center py-1">
        <i class="fas fa-handshake mr-1"></i> Layanan
      </a>
      <a href="keuangan.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center py-1">
        <i class="fas fa-coins mr-1"></i> Keuangan
      </a>
      <a href="admin_users.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center py-1">
        <i class="fas fa-gears mr-1"></i> Admin
      </a>
    </nav>
    
    <div class="flex items-center gap-4">
      <button @click="darkMode = !darkMode" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none">
        <i class="fas fa-moon dark:hidden"></i>
        <i class="fas fa-sun hidden dark:block"></i>
      </button>
      
      <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center text-sm focus:outline-none">
          <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center text-primary-600 dark:text-primary-400 font-medium">
            A
          </div>
          <span class="ml-2 hidden md:inline">Admin User</span>
          <i class="fas fa-chevron-down ml-1 text-xs"></i>
        </button>
        
        <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-1 z-40 border border-gray-200 dark:border-gray-700">
          <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
            <p class="text-sm font-medium">Admin User</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">admin</p>
          </div>
          <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <i class="fas fa-right-from-bracket mr-2"></i> Logout
          </a>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- Mobile sidebar -->
<div x-data="{ sidebarOpen: false }" class="md:hidden">
  <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-black/50" @click="sidebarOpen = false"></div>
  
  <div x-show="sidebarOpen" x-transition class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 shadow-lg transform transition-transform duration-300 ease-in-out">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <span class="text-lg font-bold text-primary-600 dark:text-primary-400">Menu</span>
        <button @click="sidebarOpen = false" class="text-gray-500 dark:text-gray-400 focus:outline-none">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
    
    <nav class="p-4 space-y-2">
      <a href="dashboard.php" class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <i class="fas fa-chart-simple mr-2"></i> Dashboard
      </a>
      <a href="table.php?t=permohonan" class="flex items-center py-2 px-3 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 transition-colors">
        <i class="fas fa-file-lines mr-2"></i> Permohonan
      </a>
      <a href="table.php?t=penelaahan" class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <i class="fas fa-magnifying-glass mr-2"></i> Penelaahan
      </a>
      <a href="table.php?t=layanan" class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <i class="fas fa-handshake mr-2"></i> Layanan
      </a>
      <a href="keuangan.php" class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <i class="fas fa-coins mr-2"></i> Keuangan
      </a>
      <a href="admin_users.php" class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <i class="fas fa-gears mr-2"></i> Admin
      </a>
    </nav>
  </div>
</div>

<main class="max-w-7xl mx-auto px-4 py-6 flex-grow">
  <div class="fade-in">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">Data Permohonan Kasus</h1>
      
      <div class="flex gap-3">
        <button class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm transition-colors flex items-center focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
          <i class="fas fa-plus mr-2"></i> Tambah Data
        </button>
        
        <div class="relative" x-data="{ exportOpen: false }">
          <button @click="exportOpen = !exportOpen" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg text-sm transition-colors flex items-center focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-offset-gray-800">
            <i class="fas fa-download mr-2"></i> Ekspor
            <i class="fas fa-chevron-down ml-2 text-xs"></i>
          </button>
          
          <div x-show="exportOpen" @click.outside="exportOpen = false" x-transition class="absolute right-0 mt-1 w-40 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-10">
            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
              <i class="fas fa-file-excel mr-2 text-green-600"></i> Excel
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
              <i class="fas fa-file-csv mr-2 text-blue-600"></i> CSV
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
              <i class="fas fa-file-pdf mr-2 text-red-600"></i> PDF
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6">
      <a href="table.php?t=permohonan" class="tab-active py-3 px-6 text-sm font-medium">
        Permohonan
      </a>
      <a href="table.php?t=penelaahan" class="tab-inactive py-3 px-6 text-sm font-medium">
        Penelaahan
      </a>
      <a href="table.php?t=layanan" class="tab-inactive py-3 px-6 text-sm font-medium">
        Layanan
      </a>
    </div>

    <!-- Search dan Filter -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-4 mb-6">
      <form class="flex flex-wrap gap-3 items-end mb-4 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cari</label>
          <div class="relative">
            <input type="text" placeholder="Ketik untuk mencari..."
              class="w-full border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          </div>
        </div>
        
        <div class="min-w-[150px]">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Kelamin</label>
          <select class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <option value="">- Semua -</option>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
          </select>
        </div>
        
        <div class="min-w-[150px]">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Media Pengajuan</label>
          <select class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <option value="">- Semua -</option>
            <option value="Online">Online</option>
            <option value="Offline">Offline</option>
          </select>
        </div>
        
        <div class="flex gap-2">
          <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm transition-colors flex items-center focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
            <i class="fas fa-filter mr-2"></i> Terapkan
          </button>
          <button type="reset" class="bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-800 dark:text-white px-4 py-2 rounded-lg text-sm transition-colors flex items-center focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-offset-gray-800">
            <i class="fas fa-sync mr-2"></i> Reset
          </button>
        </div>
      </form>

      <!-- Tabel Data -->
      <div class="table-container rounded-lg">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-gray-100 dark:bg-gray-700">
              <th class="p-3 text-left font-medium text-gray-700 dark:text-gray-300">No. Registrasi Medan</th>
              <th class="p-3 text-left font-medium text-gray-700 dark:text-gray-300">Nama Pemohon</th>
              <th class="p-3 text-left font-medium text-gray-700 dark:text-gray-300">Jenis Kelamin</th>
              <th class="p-3 text-left font-medium text-gray-700 dark:text-gray-300">Status Hukum</th>
              <th class="p-3 text-left font-medium text-gray-700 dark:text-gray-300">Tanggal Pengajuan</th>
              <th class="p-3 text-left font-medium text-gray-700 dark:text-gray-300">Tindak Pidana</th>
              <th class="p-3 text-center font-medium text-gray-700 dark:text-gray-300">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="p-3 text-gray-800 dark:text-gray-200">REG-2023-001</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Budi Santoso</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Laki-laki</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Tersangka</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">12/05/2023</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Penipuan</td>
              <td class="p-3 text-center">
                <div class="flex justify-center gap-2">
                  <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button class="text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="p-3 text-gray-800 dark:text-gray-200">REG-2023-002</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Siti Rahayu</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Perempuan</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Korban</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">15/05/2023</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Kekerasan</td>
              <td class="p-3 text-center">
                <div class="flex justify-center gap-2">
                  <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button class="text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="p-3 text-gray-800 dark:text-gray-200">REG-2023-003</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Ahmad Fauzi</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Laki-laki</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Saksi</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">18/05/2023</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Korupsi</td>
              <td class="p-3 text-center">
                <div class="flex justify-center gap-2">
                  <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button class="text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="p-3 text-gray-800 dark:text-gray-200">REG-2023-004</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Dewi Lestari</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Perempuan</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Korban</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">22/05/2023</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Pencurian</td>
              <td class="p-3 text-center">
                <div class="flex justify-center gap-2">
                  <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button class="text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="p-3 text-gray-800 dark:text-gray-200">REG-2023-005</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Rudi Hermawan</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Laki-laki</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Tersangka</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">25/05/2023</td>
              <td class="p-3 text-gray-800 dark:text-gray-200">Narkotika</td>
              <td class="p-3 text-center">
                <div class="flex justify-center gap-2">
                  <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button class="text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex flex-col sm:flex-row justify-between items-center mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="text-sm text-gray-600 dark:text-gray-400 mb-3 sm:mb-0">
          Menampilkan <span class="font-medium">5</span> dari <span class="font-medium">24</span> data
        </div>
        
        <div class="flex gap-1">
          <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg bg-primary-600 text-white border-primary-600 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
            1
          </button>
          <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
            2
          </button>
          <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
            3
          </button>
          <span class="px-2 py-1">...</span>
          <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
            5
          </button>
          <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</main>

<footer class="bg-white dark:bg-gray-800 border-t dark:border-gray-700 text-center text-sm text-gray-600 dark:text-gray-400 py-6 mt-auto">
  <div class="max-w-7xl mx-auto px-4">
    <p>&copy; 2023 LPSK App &mdash; Built with ❤️</p>
    <p class="mt-1 text-xs">Sistem Informasi Manajemen LPSK</p>
  </div>
</footer>

<!-- Dark-mode store -->
<script>
  document.addEventListener('alpine:init', () => {
    Alpine.store('darkMode', {
      on: localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
      
      toggle() {
        this.on = !this.on;
        localStorage.setItem('darkMode', this.on);
        document.documentElement.classList.toggle('dark', this.on);
      },
      
      init() {
        // Apply on load
        document.documentElement.classList.toggle('dark', this.on);
      }
    });
  });
</script>

<script>
// Fungsi untuk menangani navigasi tab
document.addEventListener('DOMContentLoaded', function() {
  // Simulasi data loading
  const simulateLoading = () => {
    const content = document.querySelector('.fade-in');
    content.style.opacity = '0';
    
    setTimeout(() => {
      content.style.opacity = '1';
    }, 300);
  };
  
  // Tangani klik pada tab
  const tabs = document.querySelectorAll('.tab-inactive, .tab-active');
  tabs.forEach(tab => {
    tab.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Hapus kelas aktif dari semua tab
      tabs.forEach(t => {
        t.classList.remove('tab-active');
        t.classList.add('tab-inactive');
      });
      
      // Tambahkan kelas aktif ke tab yang diklik
      this.classList.remove('tab-inactive');
      this.classList.add('tab-active');
      
      // Simulasi loading data
      simulateLoading();
      
      // Ganti judul halaman berdasarkan tab yang dipilih
      const pageTitle = document.querySelector('h1');
      if (this.textContent.includes('Penelaahan')) {
        pageTitle.textContent = 'Data Penelaahan Permohonan';
      } else if (this.textContent.includes('Layanan')) {
        pageTitle.textContent = 'Data Layanan Kasus';
      } else {
        pageTitle.textContent = 'Data Permohonan Kasus';
      }
    });
  });
});
</script>

</body>
</html>