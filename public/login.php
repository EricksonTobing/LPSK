<?php
define('BASE_PATH', dirname(__DIR__));
/**
 * Memuat file konfigurasi utama aplikasi.
 */
require_once BASE_PATH . '/inc/config.php';

/**
 * Memuat fungsi-fungsi otentikasi pengguna.
 */
require_once BASE_PATH . '/inc/auth.php';

/**
 * Memuat fungsi-fungsi untuk perlindungan CSRF.
 */
require_once BASE_PATH . '/inc/csrf.php';

/**
 * Memuat helper umum untuk aplikasi.
 */
require_once BASE_PATH . '/inc/helpers.php';

// Inisialisasi variabel error untuk menampung pesan error login
$error = null;

/**
 * Proses login ketika form dikirimkan (POST request).
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifikasi token CSRF untuk keamanan
    csrf_verify();

    // Proses login dengan username dan password yang diberikan
    $ok = login(trim($_POST['username'] ?? ''), (string)($_POST['password'] ?? ''));

    // Jika login berhasil, redirect ke dashboard
    if ($ok) {
        redirect('dashboard.php');
    }

    // Jika login gagal, tampilkan pesan error
    $error = 'Username atau password salah';
}

// Set judul halaman
$title = 'Login - MEDAN MELINDUNGI';

// Memuat layout header
require BASE_PATH . '/inc/layout_header.php';
?>

<div class="bg-gray-50 dark:bg-gray-900 h-screen w-screen flex justify-center items-center p-4">
    <div class="bg-white dark:bg-gray-800 px-6 py-8 rounded-lg border border-gray-200 dark:border-gray-700 shadow-md w-full max-w-md">
        <!-- Logo dan Judul -->
        <div class="flex flex-col items-center justify-center mb-6">
            <div class="w-16 h-16 bg-gradient-to-br from-primary-blue to-primary-red rounded-full flex items-center justify-center mb-3">
                <!-- <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> -->
                    <img src="assets/img/logo.png" alt="MEDAN MELINDUNGI Logo">
                    <!-- <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg> -->
            </div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">MEDAN MELINDUNGI</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 text-center">Sistem Informasi Terpadu untuk Layanan Internal</p>
        </div>

        <!-- Form Login -->
        <form method="post">
            <?= csrf_field() ?>
            
            <!-- Pesan Error -->
            <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md flex justify-between items-center">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-red-700 dark:text-red-300 text-sm"><?= e($error) ?></span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <?php endif; ?>

            <!-- Field Username -->
            <div class="flex flex-col mb-4">
                <label class="text-xs text-gray-500 dark:text-gray-400 mb-1">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input 
                        id="username" 
                        name="username" 
                        type="text" 
                        autocomplete="username" 
                        required 
                        class="w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-blue focus:border-transparent dark:bg-gray-700 dark:text-white" 
                        placeholder="Masukkan username"
                        value="<?= e($_POST['username'] ?? '') ?>"
                    />
                </div>
            </div>

            <!-- Field Password -->
            <div class="flex flex-col mb-6">
                <label class="text-xs text-gray-500 dark:text-gray-400 mb-1">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        autocomplete="current-password" 
                        required 
                        class="w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-blue focus:border-transparent dark:bg-gray-700 dark:text-white" 
                        placeholder="Masukkan password"
                    />
                </div>
            </div>

            <!-- Opsi Remember Me -->
            <div class="flex items-center mb-6">
                <input 
                    id="remember" 
                    name="remember" 
                    type="checkbox" 
                    class="h-4 w-4 text-primary-blue focus:ring-primary-blue border-gray-300 rounded"
                />
                <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Ingat saya</label>
            </div>

            <!-- Tombol Submit -->
            <div class="flex flex-col items-center justify-center">
                <button type="submit" class="w-full py-2 px-4 bg-gradient-to-r from-primary-blue to-primary-red text-white font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-blue transition-all duration-200 hover:opacity-90 shadow-md">
                    Masuk
                </button>
                
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-4 text-center">
                    Butuh bantuan? 
                    <a href="#" class="font-medium text-primary-blue hover:text-primary-red dark:text-blue-400">Hubungi administrator</a>
                </p>
            </div>
        </form>

        <!-- Copyright -->
        <div class="mt-8 pt-4 border-t border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                &copy; <?= date('Y'); ?> LPSK App &mdash; Created by Intern Students Of The Faculty of Computer Science, Catholic University of Santo Tomas
            </p>
        </div>
    </div>
</div>

