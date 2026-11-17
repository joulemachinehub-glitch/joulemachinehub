<?php
// Custom 404 Error Handler - Automatic Redirect to Home Page
http_response_code(404);

$pageTitle = "404 - Page Not Found | Redirecting to Home Page";
$pageDesc = "The requested URL or machinery listing could not be found on our marketplace. Redirecting you to the home page.";
$pageRobots = "noindex, nofollow, noarchive, nosnippet";

require_once __DIR__ . '/includes/header.php';
?>

<meta http-equiv="refresh" content="3;url=index.php">

<main class="flex-1 bg-slate-50 flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
  <div class="max-w-xl w-full text-center bg-white border border-slate-200 rounded-3xl p-8 sm:p-12 shadow-xl relative overflow-hidden">
    
    <!-- Top Decorative Gradient Accent -->
    <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-amber-400"></div>

    <!-- 404 Badge & Graphic -->
    <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-blue-50 text-blue-600 mb-6 shadow-inner border border-blue-100">
      <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
    </div>

    <!-- Error Title -->
    <div class="inline-block px-3 py-1 bg-rose-100 text-rose-700 text-xs font-black uppercase tracking-wider rounded-full mb-3">
      404 Error • Page Not Found
    </div>
    
    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-3">
      Oops! We Couldn't Find That Page
    </h1>

    <p class="text-slate-600 text-sm leading-relaxed mb-6 max-w-md mx-auto">
      The link you opened might be broken, mistyped, or the machinery listing has been moved. You are being redirected to our marketplace homepage in 
      <span id="countdown" class="font-black text-blue-600 text-base">3</span> seconds.
    </p>

    <!-- Animated Countdown Progress Bar -->
    <div class="w-full bg-slate-100 rounded-full h-2 mb-8 overflow-hidden">
      <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-1000 ease-linear w-full"></div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
      <a 
        href="index.php" 
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-6 py-3 rounded-xl shadow-md hover:shadow-lg transition-all"
      >
        <i class="fa-solid fa-house"></i>
        <span>Go to Homepage Now</span>
      </a>

      <a 
        href="index.php#catalog" 
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm px-6 py-3 rounded-xl border border-slate-200 transition-all"
      >
        <i class="fa-solid fa-boxes-stacked text-blue-600"></i>
        <span>Browse Machines</span>
      </a>
    </div>

    <!-- Secondary Help Link -->
    <div class="mt-8 pt-6 border-t border-slate-100 text-xs text-slate-500">
      Looking for a specific machine? 
      <button 
        type="button" 
        onclick="openRequirementModal()" 
        class="text-blue-600 hover:text-blue-800 font-bold underline ml-1 cursor-pointer"
      >
        Post an RFQ Requirement
      </button>
    </div>

  </div>
</main>

<script>
  // Automatic 3-Second Countdown & Redirect to Home Page
  (function() {
    let timeLeft = 3;
    const countdownEl = document.getElementById('countdown');
    const progressBar = document.getElementById('progressBar');
    
    const interval = setInterval(function() {
      timeLeft--;
      if (countdownEl) {
        countdownEl.textContent = timeLeft;
      }
      if (progressBar) {
        const percent = Math.max(0, (timeLeft / 3) * 100);
        progressBar.style.width = percent + '%';
      }
      if (timeLeft <= 0) {
        clearInterval(interval);
        window.location.href = 'index.php';
      }
    }, 1000);
  })();
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
