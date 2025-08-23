<?php $u = auth_user(); ?>
<header class="bg-white dark:bg-gray-800 shadow-lg sticky top-0 z-30">
  <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
    <div class="flex items-center">
      <button @click="sidebarOpen = !sidebarOpen" class="md:hidden mr-2 text-gray-500 dark:text-gray-400 focus:outline-none">
        <i class="fas fa-bars text-lg"></i>
      </button>
      <a href="index.php" class="text-xl font-bold text-primary-600 dark:text-primary-400 flex items-center">
        <i class="fas fa-shield-alt mr-2"></i>
        <!-- <img src="/img/logo.png" alt="LPSK Logo" class="h-8 mr-2"> -->
         MEDAN MELINDUNGI
      </a>
    </div>
    
    <nav class="hidden md:flex gap-6 text-sm">
      <a href="dashboard.php" class="hover:text-primary-600 dark:text-white hover:text-primary-400 transition-colors flex items-center py-1">
        <i class="fas fa-chart-simple mr-1"></i> Dashboard
      </a>
      <a href="table.php?t=permohonan" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center py-1">
        <i class="fas fa-file-lines mr-1"></i> Permohonan
      </a>
      <a href="table.php?t=penelaahan" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center py-1">
        <i class="fas fa-magnifying-glass mr-1"></i> Penelaahan
      </a>
      <a href="table.php?t=layanan" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center py-1">
        <i class="fas fa-handshake mr-1"></i> Layanan
      </a>
      <a href="keuangan.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center py-1">
        <i class="fas fa-coins mr-1"></i> Keuangan
      </a>
      <?php if ($u && $u['role']==='admin'): ?>
        <a href="admin_users.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center py-1">
          <i class="fas fa-gears mr-1"></i> Admin
        </a>
      <?php endif; ?>
    </nav>
    
    <div class="flex items-center gap-4">
      <button @click="$store.darkMode.toggle()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none">
        <i class="fas fa-moon dark:hidden"></i>
        <i class="fas fa-sun hidden dark:block"></i>
      </button>
      
      <?php if ($u): ?>
        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open" class="flex items-center text-sm focus:outline-none">
            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center text-primary-600 dark:text-primary-400 font-medium">
              <?= strtoupper(substr($u['nama_lengkap'] ?? $u['username'], 0, 1)) ?>
            </div>
            <span class="ml-2 hidden md:inline"><?= e($u['nama_lengkap'] ?? $u['username']) ?></span>
            <i class="fas fa-chevron-down ml-1 text-xs"></i>
          </button>
          
          <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-1 z-40 border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
              <p class="text-sm font-medium"><?= e($u['nama_lengkap'] ?? $u['username']) ?></p>
              <p class="text-xs text-gray-500 dark:text-gray-400"><?= e($u['role']) ?></p>
            </div>
            <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
              <i class="fas fa-right-from-bracket mr-2"></i> Logout
            </a>
          </div>
        </div>
      <?php else: ?>
        <a class="text-sm bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900" href="login.php">
          Login
        </a>
      <?php endif; ?>
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
      <a href="table.php?t=permohonan" class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
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
      <?php if ($u && $u['role']==='admin'): ?>
        <a href="admin_users.php" class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
          <i class="fas fa-gears mr-2"></i> Admin
        </a>
      <?php endif; ?>
    </nav>
  </div>
</div>

<main class="max-w-7xl mx-auto px-4 py-6 flex-grow">