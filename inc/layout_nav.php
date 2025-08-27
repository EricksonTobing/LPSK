<?php 
require __DIR__ . '/../inc/layout_header.php';
$u = auth_user(); $role = $u['role'] ?? 'user'; 
?>
    <!-- Main wrapper dengan shared Alpine.js data -->
    <div x-data="{ sidebarOpen: false, profileOpen: false, notificationsOpen: false }">
        <header class="bg-white dark:bg-gray-800 shadow-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
                <!-- Logo dan Toggle Menu -->
                <div class="flex items-center space-x-3 xs:space-x-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition-colors">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <a href="index.php" class="flex items-center space-x-2">
                        <div class="w-8 h-8 xs:w-10 xs:h-10 rounded-lg bg-gradient-to-br from-primary-blue to-primary-red flex items-center justify-center">
                            <i class="fas fa-shield-alt text-white text-sm xs:text-base"></i>
                        </div>
                        <span class="text-lg xs:text-xl font-bold bg-gradient-to-r from-primary-blue to-primary-red bg-clip-text text-transparent hidden xs:block">
                            MEDAN MELINDUNGI
                        </span>
                    </a>
                </div>

                <!-- Navigasi Desktop -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="dashboard.php" class="nav-link relative px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-primary-blue dark:hover:text-white transition-colors">
                        <i class="fas fa-chart-simple mr-2"></i> Dashboard
                        <span class="nav-indicator"></span>
                    </a>
                    <a href="table.php?t=permohonan" class="nav-link relative px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-primary-blue dark:hover:text-white transition-colors">
                        <i class="fas fa-file-lines mr-2"></i> Permohonan
                        <span class="nav-indicator"></span>
                    </a>
                    <a href="table.php?t=penelaahan" class="nav-link relative px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-primary-blue dark:hover:text-white transition-colors">
                        <i class="fas fa-magnifying-glass mr-2"></i> Penelaahan
                        <span class="nav-indicator"></span>
                    </a>
                    <a href="table.php?t=layanan" class="nav-link relative px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-primary-blue dark:hover:text-white transition-colors">
                        <i class="fas fa-handshake mr-2"></i> Layanan
                        <span class="nav-indicator"></span>
                    </a>
                    <a href="keuangan.php" class="nav-link relative px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-primary-blue dark:hover:text-white transition-colors">
                        <i class="fas fa-coins mr-2"></i> Keuangan
                        <span class="nav-indicator"></span>
                    </a>
                    
                    <?php if ($role === 'admin'): ?>
                    <div class="relative group" x-data="{open: false}">
                        <button @click="open = !open" class="nav-link relative px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-primary-blue dark:hover:text-white transition-colors flex items-center">
                            <i class="fas fa-cog mr-2"></i> Admin
                            <i class="fas fa-chevron-down ml-1 text-xs transition-transform" :class="{'rotate-180': open}"></i>
                            <span class="nav-indicator"></span>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-40 border border-gray-200 dark:border-gray-700">
                            <a href="admin_users.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <i class="fas fa-users mr-2"></i> Manajemen User
                            </a>
                            <a href="admin_pegawai.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <i class="fas fa-user-tie mr-2"></i> Manajemen Pegawai
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </nav>

                <!-- Right Section: Notifikasi, Dark Mode, Profil -->
                <div class="flex items-center space-x-2 xs:space-x-3">
                    <!-- Dark Mode Toggle -->
                    <button @click="$store.darkMode.toggle()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none" aria-label="Toggle dark mode">
                        <i class="fas fa-moon dark:hidden text-primary-blue text-lg"></i>
                        <i class="fas fa-sun hidden dark:block text-yellow-400 text-lg"></i>
                    </button>
                    
                    <!-- Profil Pengguna -->
                    <?php if ($u): ?>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-red rounded-full transition-colors">
                            <div class="user-initial w-8 h-8 xs:w-10 xs:h-10 rounded-full flex items-center justify-center text-white font-medium shadow-md">
                                <?= strtoupper(substr($u['nama_lengkap'] ?? $u['username'], 0, 1)) ?>
                            </div>
                            <span class="ml-2 hidden lg:inline text-gray-700 dark:text-gray-200 font-medium"><?= e($u['nama_lengkap'] ?? $u['username']) ?></span>
                            <i class="fas fa-chevron-down ml-1 text-xs text-gray-500 dark:text-gray-400 transition-transform" :class="{'rotate-180': open}"></i>
                        </button>
                        
                        <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-40 border border-gray-200 dark:border-gray-700">
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= e($u['nama_lengkap'] ?? $u['username']) ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 capitalize"><?= e($u['role']) ?></p>
                            </div>
                            <a href="profile.php" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <i class="fas fa-user-circle mr-2"></i> Profil Saya
                            </a>
                            <a href="settings.php" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <i class="fas fa-cog mr-2"></i> Pengaturan
                            </a>
                            <div class="border-t border-gray-100 dark:border-gray-700"></div>
                            <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <i class="fas fa-right-from-bracket mr-2"></i> Keluar
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="flex space-x-2">
                        <a class="text-sm bg-gradient-to-r from-primary-blue to-primary-red hover:from-[#1c1752] hover:to-[#b50c0a] text-white px-3 xs:px-4 py-2 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-red shadow-md" href="login.php">
                            Masuk
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Mobile sidebar -->
        <div class="md:hidden">
            <!-- Overlay -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-40 bg-black bg-opacity-50" 
                 @click="sidebarOpen = false"
                 style="display: none;"
                 x-cloak></div>
            
            <!-- Sidebar -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="transform transition ease-in-out duration-300" 
                 x-transition:enter-start="-translate-x-full" 
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300" 
                 x-transition:leave-start="translate-x-0" 
                 x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 shadow-lg"
                 style="display: none;"
                 x-cloak>
                
                <!-- Header Sidebar -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-primary-blue to-primary-red text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <span class="text-lg font-bold">MEDAN MELINDUNGI</span>
                        </div>
                        <button @click="sidebarOpen = false" class="text-white hover:text-gray-200 focus:outline-none transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                    <?php if ($u): ?>
                    <div class="mt-4 flex items-center space-x-3">
                        <div class="user-initial w-10 h-10 rounded-full flex items-center justify-center text-white font-medium shadow-md">
                            <?= strtoupper(substr($u['nama_lengkap'] ?? $u['username'], 0, 1)) ?>
                        </div>
                        <div>
                            <p class="text-sm font-medium truncate"><?= e($u['nama_lengkap'] ?? $u['username']) ?></p>
                            <p class="text-xs opacity-80 capitalize"><?= e($u['role']) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Navigation Menu -->
                <nav class="p-4 space-y-1 overflow-y-auto h-[calc(100%-120px)]">
                    <a href="dashboard.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" @click="sidebarOpen = false">
                        <i class="fas fa-chart-simple mr-3 w-5 text-center"></i> Dashboard
                    </a>
                    <a href="table.php?t=permohonan" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" @click="sidebarOpen = false">
                        <i class="fas fa-file-lines mr-3 w-5 text-center"></i> Permohonan
                    </a>
                    <a href="table.php?t=penelaahan" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" @click="sidebarOpen = false">
                        <i class="fas fa-magnifying-glass mr-3 w-5 text-center"></i> Penelaahan
                    </a>
                    <a href="table.php?t=layanan" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" @click="sidebarOpen = false">
                        <i class="fas fa-handshake mr-3 w-5 text-center"></i> Layanan
                    </a>
                    <a href="keuangan.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" @click="sidebarOpen = false">
                        <i class="fas fa-coins mr-3 w-5 text-center"></i> Keuangan
                    </a>
                    
                    <?php if ($role === 'admin'): ?>
                    <div class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-700">
                        <p class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Admin</p>
                        <a href="admin_users.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" @click="sidebarOpen = false">
                            <i class="fas fa-users mr-3 w-5 text-center"></i> Manajemen User
                        </a>
                        <a href="admin_pegawai.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" @click="sidebarOpen = false">
                            <i class="fas fa-user-tie mr-3 w-5 text-center"></i> Manajemen Pegawai
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($u): ?>
                    <div class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-700">
                        <a href="profile.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" @click="sidebarOpen = false">
                            <i class="fas fa-user-circle mr-3 w-5 text-center"></i> Profil Saya
                        </a>
                        <a href="settings.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" @click="sidebarOpen = false">
                            <i class="fas fa-cog mr-3 w-5 text-center"></i> Pengaturan
                        </a>
                        <a href="logout.php" class="mobile-menu-item flex items-center py-3 px-3 rounded-lg text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" @click="sidebarOpen = false">
                            <i class="fas fa-right-from-bracket mr-3 w-5 text-center"></i> Keluar
                        </a>
                    </div>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 py-6 flex-grow w-full">
        <!-- Konten halaman akan ditempatkan di sini -->