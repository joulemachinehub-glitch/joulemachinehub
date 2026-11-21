      </div><!-- Close MAIN PAGE WRAPPER -->

      <!-- DASHBOARD BOTTOM FOOTER -->
      <footer class="mt-auto bg-white border-t border-slate-200 py-4 px-6 text-center sm:text-left text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
        <div>
          &copy; <?php echo date('Y'); ?> Joule Machine Hub. All rights reserved.
        </div>
        <div class="flex items-center gap-4 text-xs text-slate-500">
          <a href="/#faqs" class="hover:text-blue-600 transition-colors">Seller Help</a>
          <a href="/#faqs" class="hover:text-blue-600 transition-colors">Terms of Service</a>
          <a href="membership.php" class="text-blue-600 font-bold hover:underline">Membership</a>
        </div>
      </footer>

    </main>
  </div><!-- Close flex-1 flex overflow-hidden -->

  <script>
    function toggleDashboardSidebar() {
      const sidebar = document.getElementById('dashboardSidebar');
      const backdrop = document.getElementById('dashboardBackdrop');
      if (!sidebar) return;
      
      if (sidebar.classList.contains('-translate-x-full')) {
        sidebar.classList.remove('-translate-x-full');
        if (backdrop) backdrop.classList.remove('hidden');
      } else {
        sidebar.classList.add('-translate-x-full');
        if (backdrop) backdrop.classList.add('hidden');
      }
    }
  </script>
</body>
</html>
