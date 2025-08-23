</main>

<footer class="bg-white dark:bg-gray-800 border-t dark:border-gray-700 text-center text-sm text-gray-600 dark:text-gray-400 py-6 mt-auto">
  <div class="max-w-7xl mx-auto px-4">
    <p>&copy; <?= date('Y'); ?> LPSK App &mdash; Built with ❤️</p>
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

<script src="/assets/js/app.js"></script>
</body>
</html>