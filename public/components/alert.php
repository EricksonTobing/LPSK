<?php
// components/dashboard/alert.php
?>

<!-- Enhanced Alert -->
<div id="error-alert" class="hidden mb-8 animate-fade-in">
    <div class="backdrop-blur-sm bg-red-50/90 dark:bg-red-900/30 border-l-4 border-red-400 p-4 rounded-r-2xl shadow-lg">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
            </div>
            <div class="ml-3">
                <p class="text-red-700 dark:text-red-300 font-medium" id="error-message">Terjadi kesalahan saat memuat data.</p>
            </div>
            <div class="ml-auto">
                <button onclick="document.getElementById('error-alert').classList.add('hidden')" class="text-red-400 hover:text-red-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>