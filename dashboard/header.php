<?php
// Joule Machine Hub - Dedicated Dashboard Header & Navigation Component
// STRICT PRIVACY: Zero search engine indexing & zero cache headers
if (!headers_sent()) {
    header("Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("X-Robots-Tag: noindex, nofollow, noarchive, nosnippet", true);
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/seller.php';
require_once __DIR__ . '/../includes/membership.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$currentUser = getCurrentUser();
$userId = $currentUser['id'] ?? $currentUser['user_id'] ?? '';
$userSub = getUserSubscription($userId);
$userStats = getUserListingStats($userId);
$currentScript = basename($_SERVER['PHP_SELF'] ?? 'index.php');

$pageRobots = "noindex, nofollow, noarchive, nosnippet";
$pageTitle = isset($pageTitle) ? $pageTitle : "Seller Dashboard | Joule Machine Hub";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  
  <!-- Strict Google Non-Index Directives for All Dashboard Pages -->
  <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
  <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">
  
  <!-- Tailwind CSS & FontAwesome -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            joule: {
              blue: '#0B2545',
              navy: '#081022',
              brightblue: '#134074',
              yellow: '#EEB902',
              gold: '#F4C430',
              accent: '#0066FF'
            }
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
  </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col antialiased">

  <!-- TOP MOBILE APP BAR (Visible on screens < lg) -->
  <header class="lg:hidden bg-joule-navy text-white px-4 py-3 flex items-center justify-between border-b border-slate-800 sticky top-0 z-40 shadow-md">
    <div class="flex items-center gap-2.5">
      <button 
        type="button" 
        onclick="toggleDashboardSidebar()" 
        class="w-9 h-9 rounded-xl bg-slate-800 text-white flex items-center justify-center hover:bg-slate-700 transition-colors"
        aria-label="Toggle Navigation Menu"
      >
        <i class="fa-solid fa-bars text-sm"></i>
      </button>
      <a href="/dashboard" class="flex items-center gap-2">
        <span class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center font-black text-white text-[11px] shadow-sm">
          JMH
        </span>
        <span class="font-extrabold text-sm tracking-tight text-white">Dashboard</span>
      </a>
    </div>

    <div class="flex items-center gap-2">
      <a 
        href="membership.php" 
        class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full <?php echo $userSub['plan_details']['badge_color']; ?> border"
      >
        <?php echo htmlspecialchars($userSub['plan_name']); ?>
      </a>
      <a 
        href="/" 
        target="_blank" 
        class="w-8 h-8 rounded-lg bg-slate-800 text-slate-300 hover:text-white flex items-center justify-center text-xs"
        title="View Public Marketplace"
      >
        <i class="fa-solid fa-arrow-up-right-from-square"></i>
      </a>
    </div>
  </header>

  <!-- MAIN DASHBOARD SHELL: Sidebar + Main Content -->
  <div class="flex-1 flex overflow-hidden">
    
    <!-- DESKTOP / MOBILE SIDEBAR -->
    <aside 
      id="dashboardSidebar" 
      class="fixed inset-y-0 left-0 z-50 w-64 bg-joule-navy text-slate-300 flex flex-col transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 lg:static lg:z-auto shadow-2xl lg:shadow-none border-r border-slate-800"
    >
      <!-- Sidebar Brand Header -->
      <div class="p-5 border-b border-slate-800/80 flex items-center justify-between">
        <a href="/dashboard" class="flex items-center gap-2.5 group">
          <span class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center font-black text-white text-xs shadow-md group-hover:bg-blue-500 transition-colors shrink-0">
            JMH
          </span>
          <div>
            <div class="text-white font-black text-sm tracking-tight leading-tight">Joule Machine Hub</div>
            <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Member Dashboard</div>
          </div>
        </a>
        <button 
          type="button" 
          onclick="toggleDashboardSidebar()" 
          class="lg:hidden text-slate-400 hover:text-white p-1"
        >
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>

      <!-- Current User Snippet & Plan Badge -->
      <div class="p-4 mx-3 my-3 bg-slate-900/90 rounded-2xl border border-slate-800">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-joule-yellow text-slate-950 flex items-center justify-center font-black text-sm shrink-0 shadow-md">
            <?php echo strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)); ?>
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-xs font-bold text-white truncate">
              <?php echo htmlspecialchars($currentUser['name'] ?? 'Seller User'); ?>
            </div>
            <div class="text-[11px] text-slate-400 truncate">
              <?php echo htmlspecialchars($currentUser['company'] ?? 'Industrial Plant'); ?>
            </div>
          </div>
        </div>

        <!-- Plan Status Strip -->
        <div class="mt-3 pt-2.5 border-t border-slate-800 flex items-center justify-between text-[11px]">
          <span class="text-slate-400">Plan:</span>
          <a href="membership.php" class="font-bold text-amber-400 hover:text-amber-300 inline-flex items-center gap-1">
            <span><?php echo htmlspecialchars($userSub['plan_name']); ?></span>
            <i class="fa-solid fa-chevron-right text-[8px]"></i>
          </a>
        </div>
      </div>

      <!-- Sidebar Navigation Menu Links -->
      <nav class="flex-1 px-3 py-2 space-y-1 overflow-y-auto text-xs font-semibold">
        
        <!-- 1. Dashboard Overview -->
        <a 
          href="/dashboard" 
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors <?php echo $currentScript === 'index.php' ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>"
        >
          <i class="fa-solid fa-gauge-high w-4 text-center"></i>
          <span>Dashboard Overview</span>
        </a>

        <!-- 2. Add New Machine -->
        <a 
          href="add-machine.php" 
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors <?php echo $currentScript === 'add-machine.php' ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>"
        >
          <i class="fa-solid fa-circle-plus w-4 text-center text-amber-400"></i>
          <span>Add New Machine</span>
        </a>

        <!-- 3. My Uploaded Machines -->
        <a 
          href="my-machines.php" 
          class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-colors <?php echo $currentScript === 'my-machines.php' || $currentScript === 'edit-machine.php' ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>"
        >
          <div class="flex items-center gap-3">
            <i class="fa-solid fa-boxes-stacked w-4 text-center"></i>
            <span>My Machines</span>
          </div>
          <?php if (!empty($userStats['total'])): ?>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 text-slate-200">
              <?php echo $userStats['total']; ?>
            </span>
          <?php endif; ?>
        </a>

        <!-- 4. My Enquiries -->
        <a 
          href="enquiries.php" 
          class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-colors <?php echo $currentScript === 'enquiries.php' ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>"
        >
          <div class="flex items-center gap-3">
            <i class="fa-solid fa-comments w-4 text-center"></i>
            <span>My Enquiries</span>
          </div>
          <?php if (!empty($userStats['total_enquiries'])): ?>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400">
              <?php echo $userStats['total_enquiries']; ?>
            </span>
          <?php endif; ?>
        </a>

        <!-- Divider -->
        <div class="pt-3 pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">
          Billing &amp; Promotion
        </div>

        <!-- 5. My Membership -->
        <a 
          href="membership.php" 
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors <?php echo $currentScript === 'membership.php' ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>"
        >
          <i class="fa-solid fa-crown w-4 text-center text-yellow-400"></i>
          <span>My Membership</span>
        </a>

        <!-- 6. Payments & Billing -->
        <a 
          href="payments.php" 
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors <?php echo $currentScript === 'payments.php' || $currentScript === 'checkout.php' || $currentScript === 'invoices.php' ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>"
        >
          <i class="fa-solid fa-receipt w-4 text-center"></i>
          <span>Payments &amp; Billing</span>
        </a>

        <!-- Divider -->
        <div class="pt-3 pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">
          Account Settings
        </div>

        <!-- 7. Company Profile -->
        <a 
          href="profile.php" 
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors <?php echo $currentScript === 'profile.php' ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>"
        >
          <i class="fa-solid fa-building w-4 text-center"></i>
          <span>Company Profile</span>
        </a>

        <!-- 8. Change Password -->
        <a 
          href="change-password.php" 
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors <?php echo $currentScript === 'change-password.php' ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>"
        >
          <i class="fa-solid fa-lock w-4 text-center"></i>
          <span>Change Password</span>
        </a>

        <!-- 9. Logout -->
        <a 
          href="logout.php" 
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-rose-400 hover:bg-rose-950/40 hover:text-rose-300 transition-colors"
        >
          <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
          <span>Logout</span>
        </a>

      </nav>

      <!-- Sidebar Bottom Link: Public Marketplace -->
      <div class="p-3 border-t border-slate-800/80">
        <a 
          href="/" 
          class="w-full flex items-center justify-center gap-2 bg-slate-800/80 hover:bg-slate-700/80 text-slate-200 text-xs font-bold py-2.5 px-3 rounded-xl transition-colors"
        >
          <i class="fa-solid fa-globe text-blue-400"></i>
          <span>Browse Public Site</span>
        </a>
      </div>

    </aside>

    <!-- SIDEBAR BACKDROP ON MOBILE -->
    <div 
      id="dashboardBackdrop" 
      onclick="toggleDashboardSidebar()" 
      class="fixed inset-0 bg-slate-950/70 z-40 hidden lg:hidden"
    ></div>

    <!-- MAIN DASHBOARD CONTENT AREA -->
    <main class="flex-1 flex flex-col overflow-y-auto bg-slate-50 min-h-screen">
      
      <!-- DESKTOP TOP BAR -->
      <div class="hidden lg:flex items-center justify-between bg-white border-b border-slate-200 px-8 py-3.5 sticky top-0 z-30 shadow-xs">
        <div>
          <h2 class="text-lg font-black text-slate-900 tracking-tight">
            <?php echo htmlspecialchars($pageTitle); ?>
          </h2>
          <p class="text-xs text-slate-500">
            Welcome back, <strong class="text-slate-800"><?php echo htmlspecialchars($currentUser['name'] ?? ''); ?></strong>
          </p>
        </div>

        <div class="flex items-center gap-3">
          <!-- Public Site Button -->
          <a 
            href="/" 
            target="_blank" 
            class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-joule-blue bg-slate-100 hover:bg-slate-200 px-3.5 py-2 rounded-xl transition-colors border border-slate-200"
          >
            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            <span>View Marketplace</span>
          </a>

          <!-- Add Machine Quick CTA -->
          <a 
            href="add-machine.php" 
            class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl shadow-md transition-all cursor-pointer"
          >
            <i class="fa-solid fa-plus text-[10px]"></i>
            <span>Add Machine</span>
          </a>

          <!-- Profile Badge -->
          <div class="pl-2 border-l border-slate-200 flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-joule-blue text-white flex items-center justify-center font-black text-xs">
              <?php echo strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)); ?>
            </span>
          </div>
        </div>
      </div>

      <!-- MAIN PAGE WRAPPER -->
      <div class="p-4 sm:p-6 lg:p-8 flex-1 space-y-6">
