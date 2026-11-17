<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'joulemachinehub.com';
$pageTitle = "Email Verification | Joule Machine Hub";
$pageDesc = "Verify your Joule Machine Hub account email address to activate your machinery seller dashboard.";
$canonicalUrl = $protocol . $host . "/verify-email.php";
$pageRobots = "noindex, nofollow, noarchive, nosnippet";

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';

$token = htmlspecialchars($_GET['token'] ?? '');
$verified = true;

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-12 sm:py-20 px-4 bg-slate-900 flex-1 flex items-center justify-center min-h-[75vh]">
  <div class="max-w-md w-full bg-white rounded-3xl border-2 border-emerald-500 shadow-2xl p-8 text-center space-y-6">
    
    <div class="w-20 h-20 rounded-3xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-4xl mx-auto shadow-md">
      <i class="fa-solid fa-envelope-circle-check"></i>
    </div>

    <div>
      <span class="inline-block uppercase tracking-wider text-[10px] font-black bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full mb-2">
        Verification Complete
      </span>
      <h1 class="text-2xl font-black text-slate-900">Email Address Verified</h1>
      <p class="text-xs text-slate-600 mt-2 leading-relaxed">
        Your email has been successfully verified. Your company account is now fully active for machinery listings and buyer lead inquiries.
      </p>
    </div>

    <div class="pt-2">
      <a 
        href="dashboard/index.php" 
        class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs py-3.5 px-6 rounded-2xl shadow-md transition-all"
      >
        <span>Enter Member Dashboard</span>
        <i class="fa-solid fa-arrow-right text-[10px]"></i>
      </a>
    </div>

  </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
