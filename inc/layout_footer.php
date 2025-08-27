</main>

<footer class="bg-white dark:bg-gray-800 border-t dark:border-gray-700 text-center text-sm text-gray-600 dark:text-gray-400 py-6 mt-auto">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex flex-col md:flex-row justify-between items-center">
      <p>&copy; <?= date('Y'); ?> LPSK App &mdash; Built with ❤️</p>
      <p class="mt-1 md:mt-0 text-xs">Sistem Informasi Manajemen LPSK</p>
      <div class="mt-2 md:mt-0 flex space-x-4">
        <a href="#" class="text-gray-500 hover:text-primary-red transition-colors"><i class="fab fa-facebook"></i></a>
        <a href="#" class="text-gray-500 hover:text-primary-red transition-colors"><i class="fab fa-twitter"></i></a>
        <a href="#" class="text-gray-500 hover:text-primary-red transition-colors"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
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
     // Ensure proper touch handling
    Alpine.store('sidebar', {
        open: false,
        toggle() {
            this.open = !this.open;
            // Prevent body scroll when sidebar is open
            if (this.open) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        },
        close() {
            this.open = false;
            document.body.style.overflow = '';
        }
    });
    
    // Handle viewport changes for mobile
    function handleViewportChange() {
        if (window.innerWidth >= 768) {
            Alpine.store('sidebar').close();
        }
    }
    
    window.addEventListener('resize', handleViewportChange);
    window.addEventListener('orientationchange', () => {
        setTimeout(handleViewportChange, 100);
    });
});

// Ensure proper initialization
document.addEventListener('DOMContentLoaded', function() {
    // Force Alpine to reinitialize if needed
    if (typeof Alpine !== 'undefined') {
        Alpine.start();
    }
});
</script>

<script src="/assets/js/app.js"></script>
</body>
</html>