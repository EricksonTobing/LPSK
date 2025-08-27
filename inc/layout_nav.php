<?php $u = auth_user(); $role = $u['role'] ?? 'user'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MEDAN MELINDUNGI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        :root {
            --primary-red: #C6100D;
            --primary-blue: #241E4E;
            --accent-red: #E53E3E;
            --accent-blue: #3182CE;
            --light-bg: #F7FAFC;
            --dark-bg: #1A202C;
            --text-light: #2D3748;
            --text-dark: #E2E8F0;
        }
        
        body {
            margin: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--primary-red);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .nav-indicator {
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-blue), var(--primary-red));
            border-radius: 2px;
            opacity: 0;
            transition: opacity 0.3s, bottom 0.3s;
        }
        
        .nav-link:hover .nav-indicator {
            opacity: 1;
            bottom: -3px;
        }
        
        .nav-link.active .nav-indicator {
            opacity: 1;
            bottom: -3px;
        }
        
        .user-initial {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-red));
        }
        
        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(198, 16, 13, 0.2);
        }
        
        .mobile-menu-item {
            position: relative;
            overflow: hidden;
        }
        
        .mobile-menu-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(to bottom, var(--primary-blue), var(--primary-red));
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .mobile-menu-item:hover::before {
            opacity: 1;
        }
        
        /* Animasi untuk notifikasi */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <header class="bg-white dark:bg-gray-800 shadow-md sticky top-0 z-50" x-data="{ sidebarOpen: false, profileOpen: false, notificationsOpen: false }">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
            <!-- Logo dan Toggle Menu -->
            <div class="flex items-center space-x-4">
                <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <a href="index.php" class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#241E4E] to-[#C6100D] flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white"></i>
                    </div>
                    <span class="text-xl font-bold bg-gradient-to-r from-[#241E4E] to-[#C6100D] bg-clip-text text-transparent">
                        MEDAN MELINDUNGI
                    </span>
                </a>
            </div>

            <!-- Navigasi Desktop -->
            <nav class="hidden md:flex items-center space-x-1">
                <a href="dashboard.php" class="nav-link relative px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-[#241E4E] dark:hover:text-white transition-colors">
                    <i class="fas fa-chart-simple mr-2"></i> Dashboard
                    <span class="nav-indicator"></span>
                </a>
                <a href="table.php?t=permohonan" class="nav-link relative px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-[#241E4E] dark:hover:text-white transition-colors">
                    <i class="fas fa-file-lines mr-2"></i> Permohonan
                    <span class="nav-indicator"></span>
                </a>
                <a href="table.php?t=penelaahan" class="nav-link relative px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-[#241E4E] dark:hover:text-white transition-colors">
                    <i class="fas fa-magnifying-glass mr-2"></i> Penelaahan
                    <span class="nav-indicator"></span>
                </a>
                <a href="table.php?t=layanan" class="nav-link relative px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-[#241E4E] dark:hover:text-white transition-colors">
                    <i class="fas fa-handshake mr-2"></i> Layanan
                    <span class="nav-indicator"></span>
                </a>
                <a href="keuangan.php" class="nav-link relative px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-[#241E4E] dark:hover:text-white transition-colors">
                    <i class="fas fa-coins mr-2"></i> Keuangan
                    <span class="nav-indicator"></span>
                </a>
                
                <?php if ($role === 'admin'): ?>
                <div class="relative group" x-data="{open: false}">
                    <button @click="open = !open" class="nav-link relative px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-[#241E4E] dark:hover:text-white transition-colors flex items-center">
                        <i class="fas fa-cog mr-2"></i> Admin
                        <i class="fas fa-chevron-down ml-1 text-xs transition-transform" :class="{'rotate-180': open}"></i>
                        <span class="nav-indicator"></span>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-40 border border-gray-200 dark:border-gray-700">
                        <a href="admin_users.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fas fa-users mr-2"></i> Manajemen User
                        </a>
                        <a href="admin_pegawai.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fas fa-user-tie mr-2"></i> Manajemen Pegawai
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </nav>

            <!-- Right Section: Pencarian, Notifikasi, Dark Mode, Profil -->
            <div class="flex items-center space-x-3">
                <!-- Pencarian -->
                <div class="hidden lg:block relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" placeholder="Cari..." class="search-input pl-10 pr-4 py-2 rounded-full text-sm border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-[#C6100D] focus:border-transparent dark:text-white">
                </div>
                
                <!-- Dark Mode Toggle -->
                <button @click="$store.darkMode.toggle()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none">
                    <i class="fas fa-moon dark:hidden text-[#241E4E]"></i>
                    <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                </button>
                
                <!-- Notifikasi -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 relative">
                        <i class="fas fa-bell text-gray-600 dark:text-gray-300"></i>
                        <span class="notification-badge pulse">3</span>
                    </button>
                    
                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-md shadow-lg overflow-hidden z-50 border border-gray-200 dark:border-gray-700">
                        <div class="px-4 py-2 bg-gradient-to-r from-[#241E4E] to-[#C6100D] text-white font-semibold flex justify-between items-center">
                            <span>Notifikasi</span>
                            <span class="text-xs bg-white/20 px-2 py-1 rounded-full">3 baru</span>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <a href="#" class="block px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                        <i class="fas fa-file-invoice"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Permohonan baru diterima</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">2 menit yang lalu</p>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="block px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Penelaahan selesai</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">1 jam yang lalu</p>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Pembayaran tertunda</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">5 jam yang lalu</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="px-4 py-2 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                            <a href="#" class="text-sm font-medium text-center w-full block text-[#241E4E] dark:text-[#C6100D]">Lihat semua notifikasi</a>
                        </div>
                    </div>
                </div>
                
                <!-- Profil Pengguna -->
                <?php if ($u): ?>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#C6100D] rounded-full">
                        <div class="user-initial w-10 h-10 rounded-full flex items-center justify-center text-white font-medium shadow-md">
                            <?= strtoupper(substr($u['nama_lengkap'] ?? $u['username'], 0, 1)) ?>
                        </div>
                        <span class="ml-2 hidden lg:inline text-gray-700 dark:text-gray-200 font-medium"><?= e($u['nama_lengkap'] ?? $u['username']) ?></span>
                        <i class="fas fa-chevron-down ml-1 text-xs text-gray-500 dark:text-gray-400 transition-transform" :class="{'rotate-180': open}"></i>
                    </button>
                    
                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-40 border border-gray-200 dark:border-gray-700">
                        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-sm font-medium text-gray-900 dark:text-white"><?= e($u['nama_lengkap'] ?? $u['username']) ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 capitalize"><?= e($u['role']) ?></p>
                        </div>
                        <a href="profile.php" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <i class="fas fa-user-circle mr-2"></i> Profil Saya
                        </a>
                        <a href="settings.php" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <i class="fas fa-cog mr-2"></i> Pengaturan
                        </a>
                        <div class="border-t border-gray-100 dark:border-gray-700"></div>
                        <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <i class="fas fa-right-from-bracket mr-2"></i> Keluar
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <div class="flex space-x-2">
                    <a class="text-sm bg-gradient-to-r from-[#241E4E] to-[#C6100D] hover:from-[#1c1752] hover:to-[#b50c0a] text-white px-4 py-2 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#C6100D] shadow-md" href="login.php">
                        Masuk
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Mobile Search Bar -->
        <div x-show="sidebarOpen" class="md:hidden px-4 py-2 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" placeholder="Cari..." class="w-full pl-10 pr-4 py-2 rounded-full text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-600 focus:ring-2 focus:ring-[#C6100D] focus:border-transparent dark:text-white">
            </div>
        </div>
    </header>

    <!-- Mobile sidebar -->
    <div x-data="{ sidebarOpen: false }" class="md:hidden">
        <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-black bg-opacity-50" @click="sidebarOpen = false"></div>
        
        <div x-show="sidebarOpen" x-transition class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 shadow-lg transform transition-transform duration-300 ease-in-out">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-[#241E4E] to-[#C6100D] text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <span class="text-lg font-bold">MEDAN MELINDUNGI</span>
                    </div>
                    <button @click="sidebarOpen = false" class="text-white focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php if ($u): ?>
                <div class="mt-4 flex items-center space-x-3">
                    <div class="user-initial w-10 h-10 rounded-full flex items-center justify-center text-white font-medium shadow-md">
                        <?= strtoupper(substr($u['nama_lengkap'] ?? $u['username'], 0, 1)) ?>
                    </div>
                    <div>
                        <p class="text-sm font-medium"><?= e($u['nama_lengkap'] ?? $u['username']) ?></p>
                        <p class="text-xs opacity-80 capitalize"><?= e($u['role']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <nav class="p-4 space-y-1 overflow-y-auto h-[calc(100%-120px)]">
                <a href="dashboard.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-chart-simple mr-3 w-5 text-center"></i> Dashboard
                </a>
                <a href="table.php?t=permohonan" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-file-lines mr-3 w-5 text-center"></i> Permohonan
                </a>
                <a href="table.php?t=penelaahan" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-magnifying-glass mr-3 w-5 text-center"></i> Penelaahan
                </a>
                <a href="table.php?t=layanan" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-handshake mr-3 w-5 text-center"></i> Layanan
                </a>
                <a href="keuangan.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-coins mr-3 w-5 text-center"></i> Keuangan
                </a>
                
                <?php if ($role === 'admin'): ?>
                <div class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-700">
                    <p class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Admin</p>
                    <a href="admin_users.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-users mr-3 w-5 text-center"></i> Manajemen User
                    </a>
                    <a href="admin_pegawai.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-user-tie mr-3 w-5 text-center"></i> Manajemen Pegawai
                    </a>
                </div>
                <?php endif; ?>
                
                <div class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-700">
                    <a href="profile.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-user-circle mr-3 w-5 text-center"></i> Profil Saya
                    </a>
                    <a href="settings.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-cog mr-3 w-5 text-center"></i> Pengaturan
                    </a>
                    <a href="logout.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-right-from-bracket mr-3 w-5 text-center"></i> Keluar
                    </a>
                </div>
            </nav>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 py-6 flex-grow">
        <!-- Konten halaman akan ditempatkan di sini -->
</body>
