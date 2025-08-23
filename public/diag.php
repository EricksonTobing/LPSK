<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosa Masalah Navigasi - LPSK App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .problem-solved {
            border-left: 4px solid #10B981;
        }
        .problem-ongoing {
            border-left: 4px solid #F59E0B;
        }
        .problem-critical {
            border-left: 4px solid #EF4444;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <header class="bg-white rounded-xl shadow-md p-6 mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Diagnosa Masalah Navigasi</h1>
            <p class="text-gray-600">Halaman ini akan membantu mengidentifikasi mengapa Anda tidak dapat membuka halaman lain</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="card bg-white rounded-xl shadow-md p-6 problem-critical">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Penyebab Umum</h2>
                <ul class="list-disc list-inside space-y-2 text-gray-700">
                    <li>File PHP tidak ditemukan (404 error)</li>
                    <li>Kesalahan sintaks PHP</li>
                    <li>Masalah konfigurasi server</li>
                    <li>Izin akses file tidak tepat</li>
                    <li>Error pada file include</li>
                    <li>Masalah session</li>
                </ul>
            </div>

            <div class="card bg-white rounded-xl shadow-md p-6 problem-ongoing">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Langkah Diagnosa</h2>
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Periksa konsol browser (F12)</li>
                    <li>Cek error log server</li>
                    <li>Verifikasi path file</li>
                    <li>Periksa izin file</li>
                    <li>Test koneksi database</li>
                    <li>Verifikasi konfigurasi</li>
                </ol>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Test Navigasi</h2>
            <p class="text-gray-600 mb-4">Klik link di bawah ini untuk menguji apakah halaman dapat diakses:</p>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="dashboard.php" class="bg-blue-100 hover:bg-blue-200 text-blue-800 py-3 px-4 rounded-lg text-center transition-colors">
                    <i class="fas fa-chart-simple text-lg mb-2"></i>
                    <p>Dashboard</p>
                </a>
                <a href="table.php?t=permohonan" class="bg-green-100 hover:bg-green-200 text-green-800 py-3 px-4 rounded-lg text-center transition-colors">
                    <i class="fas fa-file-lines text-lg mb-2"></i>
                    <p>Permohonan</p>
                </a>
                <a href="keuangan.php" class="bg-purple-100 hover:bg-purple-200 text-purple-800 py-3 px-4 rounded-lg text-center transition-colors">
                    <i class="fas fa-coins text-lg mb-2"></i>
                    <p>Keuangan</p>
                </a>
                <a href="admin_users.php" class="bg-red-100 hover:bg-red-200 text-red-800 py-3 px-4 rounded-lg text-center transition-colors">
                    <i class="fas fa-gears text-lg mb-2"></i>
                    <p>Admin</p>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Solusi Cepat</h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                            <i class="fas fa-folder-open text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-800">Periksa File Include</h3>
                            <p class="text-sm text-gray-600">Pastikan file-file include (config.php, auth.php, dll) ada di lokasi yang benar.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-green-100 p-2 rounded-lg mr-3">
                            <i class="fas fa-database text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-800">Test Koneksi Database</h3>
                            <p class="text-sm text-gray-600">Pastikan konfigurasi database benar dan server database berjalan.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-purple-100 p-2 rounded-lg mr-3">
                            <i class="fas fa-key text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-800">Periksa Session & Auth</h3>
                            <p class="text-sm text-gray-600">Pastikan tidak ada masalah dengan session dan authentication.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Laporan Error</h2>
                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Error</label>
                        <select class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option>Halaman tidak ditemukan (404)</option>
                            <option>Error PHP</option>
                            <option>Error Database</option>
                            <option>Error Izin Akses</option>
                            <option>Lainnya</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pesan Error</label>
                        <textarea rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Deskripsikan error yang terjadi..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Halaman yang Diakses</label>
                        <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="URL halaman yang error">
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors">
                        Simpan Laporan
                    </button>
                </form>
            </div>
        </div>

        <footer class="mt-12 text-center text-gray-600">
            <p>LPSK App &copy; 2023 - Halaman Diagnosa</p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Test semua link navigasi
            const links = document.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && href !== '#') {
                        console.log(`Mencoba mengakses: ${href}`);
                    }
                });
            });

            // Tambahkan efek visual pada kartu
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.boxShadow = '';
                });
            });
        });
    </script>
</body>
</html>