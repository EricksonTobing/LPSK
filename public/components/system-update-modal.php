<!-- Enhanced System Update Modal -->
<div id="systemUpdateModal" style="z-index: 99999;" class="hidden fixed inset-0 overflow-y-auto animate-fade-in" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity animate-fade-in" aria-hidden="true" id="modalBackdrop"></div>
    
    <!-- Modal container -->
    <div class="inline-block align-bottom bg-gradient-to-br from-white via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative animate-slide-up">
      <!-- Decorative elements -->
      <div class="absolute -top-12 -right-12 w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full opacity-20 blur-xl"></div>
      <div class="absolute -bottom-8 -left-8 w-20 h-20 bg-gradient-to-br from-green-400 to-cyan-500 rounded-full opacity-20 blur-xl"></div>
      
      <!-- Header with gradient -->
      <div class="relative bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 pt-6 pb-4 px-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="relative">
              <div class="w-12 h-12 bg-red/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                <i class="fas fa-rocket text-white text-xl"></i>
              </div>
              <div class="absolute -top-1 -right-1 w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center animate-bounce">
                <span class="text-xs font-bold">!</span>
              </div>
            </div>
            <div>
              <h3 class="text-xl font-bold text-white" id="modal-title">
                Pembaruan Sistem 
              </h3>
              <div class="flex items-center space-x-2 mt-1">
                <span class="px-2 py-1 bg-white/20 backdrop-blur-sm text-white text-xs font-medium rounded-full" id="updateVersion">
                  v1.0.9
                </span>
                <span class="text-white/80 text-sm">
                  <i class="fas fa-clock mr-1"></i>Baru saja
                </span>
              </div>
            </div>
          </div>
          <button type="button" class="text-white/70 hover:text-white transition-colors" id="btnCloseModal">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>
      </div>
      
      <!-- Main content -->
      <div class="px-6 py-6">
        <!-- Image/Illustration Section -->
        <div class="mb-6 relative overflow-hidden rounded-xl border border-blue-200 dark:border-blue-800/50 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-4">
          <div class="flex items-center justify-center">
            <div class="relative">
              <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-cyan-400 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                <i class="fas fa-sync-alt text-white text-3xl"></i>
              </div>
              <div class="absolute -inset-4 bg-gradient-to-r from-blue-400/30 to-cyan-400/30 rounded-full blur-xl animate-ping opacity-30"></div>
            </div>
            <div class="ml-6">
              <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">Peningkatan Baru</h4>
              <p class="text-sm text-gray-600 dark:text-gray-300">Perbaikan dan penambahan fitur
              </p>
            </div>
          </div>
          <!-- Animated background elements -->
          <div class="absolute top-0 right-0 w-16 h-16 bg-blue-200/20 dark:bg-blue-500/10 rounded-full -mt-4 -mr-4"></div>
          <div class="absolute bottom-0 left-0 w-12 h-12 bg-cyan-200/20 dark:bg-cyan-500/10 rounded-full -mb-3 -ml-3"></div>
        </div>
        
        <!-- Update description -->
        <div class="mb-6">
          <div class="flex items-center mb-3">
            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-3">
              <i class="fas fa-list-check text-blue-600 dark:text-blue-400"></i>
            </div>
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white">Apa yang Baru?</h4>
          </div>
          
          <div class="space-y-3" id="updateDescription">
            <!-- Content will be populated by JavaScript -->
            <div class="p-3 bg-white dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700">
              <div class="flex items-start">
                <div class="w-6 h-6 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <i class="fas fa-check text-green-600 dark:text-green-400 text-xs"></i>
                </div>
                <p class="ml-3 text-gray-700 dark:text-gray-300">
                  Penambahan fitur Monitoring Penelaahan Pegawai 
                  <strong class="text-red-600 dark:text-red-400">(LAKUKAN PENGISIAN WAKTU AKHIR PENELAAHAN)</strong>
                </p>
              </div>
            </div>
            <div class="p-3 bg-white dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700">
              <div class="flex items-start">
                <div class="w-6 h-6 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <i class="fas fa-check text-green-600 dark:text-green-400 text-xs"></i>
                </div>
                <p class="ml-3 text-gray-700 dark:text-gray-300">
                  Penambahan <i>Inkracht</i> pada proses hukum 
                </p>
              </div>
            </div>
            <div class="p-3 bg-white dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700">
              <div class="flex items-start">
                <div class="w-6 h-6 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <i class="fas fa-check text-green-600 dark:text-green-400 text-xs"></i>
                </div>
                <p class="ml-3 text-gray-700 dark:text-gray-300">
                  Perbaikan Monitoring Aktivitas User lebih rinci 
                </p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Important notes -->
        <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/30 rounded-xl">
          <div class="flex items-start">
            <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center flex-shrink-0 mr-3">
              <i class="fas fa-exclamation-triangle text-amber-600 dark:text-amber-400"></i>
            </div>
            <div>
              <h5 class="font-medium text-amber-800 dark:text-amber-300 mb-1">Penting!</h5>
              <p class="text-sm text-amber-700 dark:text-amber-400/80">
                Upayakan menjaga konsistensi dalam input data.
                <br>
                <strong class="text-red-600 dark:text-red-400">Pastikan Akun user sesuai dengan pengguna agar jika terjadi penambahan atau perubahan data maka akun dari pengguna tersebut harus bertanggung jawab.</strong>
                <br>
                <strong class="text-red-600 dark:text-red-400">Seluruh aktifitas user akan di simpan dan di tampilkan di halaman dashboard monitoring aktivitas user.</strong>
              </p>
            </div>
          </div>
        </div>
        
        <!-- Progress indicator (hidden by default, shown when updating) -->
        <div id="updateProgress" class="hidden mb-6">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Mengunduh pembaruan...</span>
            <span class="text-sm font-bold text-blue-600 dark:text-blue-400" id="progressPercentage">0%</span>
          </div>
          <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
            <div id="progressBar" class="bg-gradient-to-r from-blue-500 to-cyan-400 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
          </div>
        </div>
      </div>
      
      <!-- Footer with action buttons -->
      <div class="bg-gray-50/80 dark:bg-gray-800/50 backdrop-blur-sm px-6 py-4 border-t border-gray-100 dark:border-gray-700/50">
        <div class="flex flex-col sm:flex-row gap-3 justify-end">
          <button type="button" id="btnExit" class="flex-1 sm:flex-none sm:w-32 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded-xl border border-gray-200 dark:border-gray-600 transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center">
            <span>Keluar</span>
          </button>
          <button type="button" id="btnUnderstand" class="flex-1 sm:flex-none sm:w-40 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-medium py-2 px-4 rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
            <i class="fas fa-check"></i>
            <span>Saya Mengerti</span>
          </button>
        </div>
      </div>
      
      <!-- Version badge -->
      <div class="absolute -top-3 right-6">
        <div class="px-3 py-1 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-xs font-bold rounded-full shadow-lg">
          TERBARU
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check for updates
    fetch('api/check_updates.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.has_update) {
                const modal = document.getElementById('systemUpdateModal');
                const versionSpan = document.getElementById('updateVersion');
                const descContainer = document.getElementById('updateDescription');
                const btnUnderstand = document.getElementById('btnUnderstand');
                const btnExit = document.getElementById('btnExit');
                const btnClose = document.getElementById('btnCloseModal');
                
                if (modal) {
                    // Update version
                    if (versionSpan) {
                        versionSpan.textContent = `v${data.update.version}`;
                    }
                    
                    // Update description with features list
                    if (descContainer && data.update.features) {
                        const features = Array.isArray(data.update.features) ? 
                            data.update.features : 
                            data.update.description.split('\n').filter(f => f.trim());
                        
                        descContainer.innerHTML = features.map(feature => `
                            <div class="p-3 bg-white dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700">
                                <div class="flex items-start">
                                    <div class="w-6 h-6 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <i class="fas fa-check text-green-600 dark:text-green-400 text-xs"></i>
                                    </div>
                                    <p class="ml-3 text-gray-700 dark:text-gray-300">${feature.trim()}</p>
                                </div>
                            </div>
                        `).join('');
                    }
                    
                    // Show modal with animation
                    setTimeout(() => {
                        modal.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                    }, 1500); // Show after 1.5 seconds
                    
                    // Close modal handler
                    const closeModal = () => {
                        modal.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    };
                    
                    // Understand button
                    if (btnUnderstand) {
                        btnUnderstand.addEventListener('click', function() {
                            this.disabled = true;
                            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Memproses...</span>';
                            
                            fetch('api/mark_update_read.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ update_id: data.update.id })
                            }).then(() => {
                                closeModal();
                                showNotification('Terima kasih, informasi pembaruan telah dibaca.', 'success');
                            }).catch(() => {
                                closeModal();
                            });
                        });
                    }
                    
                    // Exit button: simply close the modal
                    if (btnExit) {
                         btnExit.addEventListener('click', closeModal);
                    }
                    
                    // Close button (X)
                    if (btnClose) {
                        btnClose.addEventListener('click', closeModal);
                    }
                    
                    // Close on backdrop click
                    document.getElementById('modalBackdrop').addEventListener('click', closeModal);
                }
            }
        })
        .catch(err => console.error('Error checking updates:', err));
    
    // Notification function
    function showNotification(message, type = 'info') {
        const types = {
            success: { color: 'green', icon: 'check-circle' },
            error: { color: 'red', icon: 'exclamation-circle' },
            warning: { color: 'amber', icon: 'exclamation-triangle' },
            info: { color: 'blue', icon: 'info-circle' }
        };
        
        const typeInfo = types[type] || types.info;
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-[10000] bg-${typeInfo.color}-50 dark:bg-${typeInfo.color}-900/30 border border-${typeInfo.color}-200 dark:border-${typeInfo.color}-800 rounded-xl shadow-xl p-4 max-w-sm transform translate-x-full transition-transform duration-300`;
        notification.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-${typeInfo.color}-100 dark:bg-${typeInfo.color}-900/50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-${typeInfo.icon} text-${typeInfo.color}-600 dark:text-${typeInfo.color}-400"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">${message}</p>
                </div>
                <button class="ml-4 text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Auto remove
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
        
        // Close button
        notification.querySelector('button').addEventListener('click', () => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        });
    }
});
</script>

<style>
/* Additional styles for the enhanced modal */
#systemUpdateModal {
    opacity: 0;
    animation: fadeIn 0.3s ease-out forwards;
}

#systemUpdateModal .animate-slide-up {
    animation: slideUp 0.5s cubic-bezier(0.18, 0.89, 0.32, 1.28) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { 
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to { 
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Pulse animation for important elements */
@keyframes pulse-subtle {
    0%, 100% { 
        opacity: 1;
        transform: scale(1);
    }
    50% { 
        opacity: 0.8;
        transform: scale(1.05);
    }
}

.animate-pulse-subtle {
    animation: pulse-subtle 2s ease-in-out infinite;
}

/* Progress bar animation */
@keyframes progressShimmer {
    0% { background-position: -200px 0; }
    100% { background-position: calc(200px + 100%) 0; }
}

.progress-shimmer {
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.4),
        transparent
    );
    background-size: 200px 100%;
    animation: progressShimmer 1.5s infinite;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    #systemUpdateModal .sm\\:max-w-lg {
        max-width: 95%;
    }
    
    #systemUpdateModal .px-6 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}
</style>