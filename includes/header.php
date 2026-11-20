<?php
// 0. Enable Gzip Output Compression for Ultra-Fast TTFB & Network Transfer
if (!headers_sent() && !ob_get_level()) {
    if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
        ob_start('ob_gzhandler');
    } else {
        ob_start();
    }
}
// Header component for Joule Machine Hub with Auth Integration & Enhanced Navigation
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../data/machines.php';

$currentCategory = isset($_GET['category']) ? $_GET['category'] : 'All Categories';
$currentCity = isset($_GET['city']) ? $_GET['city'] : 'All Cities';
$currentSearch = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$authUser = getCurrentUser();
$logged = isLoggedIn();

// High-Speed LiteSpeed Caching & Header Controls
// Private admin/auth pages stay uncached & non-indexed; public marketplace pages leverage ultra-fast LiteSpeed cache
$isPrivateSection = str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'admin') 
                 || str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'login') 
                 || str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'register') 
                 || str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'dashboard')
                 || str_contains($_SERVER['REQUEST_URI'] ?? '', 'admin')
                 || str_contains($_SERVER['REQUEST_URI'] ?? '', 'login')
                 || str_contains($_SERVER['REQUEST_URI'] ?? '', 'register')
                 || str_contains($_SERVER['REQUEST_URI'] ?? '', 'dashboard');

// SEO Defaults (can be customized per page)
$baseHref = $baseHref ?? '/';
$pageTitle = isset($pageTitle) ? $pageTitle : 'Joule Machine Hub | Worldwide Used / New Industrial Machinery Network';
$pageDesc = isset($pageDesc) ? $pageDesc : 'Buy and sell verified used / new industrial machinery worldwide. Haas, Mazak, DMG Mori, Amada, Demag, Kaeser through Joule Machine Hub B2B machinery network.';
$canonicalUrl = isset($canonicalUrl) ? $canonicalUrl : '';
$ogTitle = isset($ogTitle) ? $ogTitle : $pageTitle;
$ogDesc = isset($ogDesc) ? $ogDesc : $pageDesc;
$ogImage = isset($ogImage) ? $ogImage : '/assets/images/logo-v2.png';
$ogType = isset($ogType) ? $ogType : 'website';
$defaultRobots = $isPrivateSection ? 'noindex, nofollow, noarchive, nosnippet' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
$pageRobots = isset($pageRobots) ? $pageRobots : $defaultRobots;

if (!headers_sent()) {
    if ($isPrivateSection) {
        header("Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("X-LiteSpeed-Cache-Control: no-cache");
    } else {
        header("X-LiteSpeed-Cache-Control: public, max-age=180");
        header("X-LiteSpeed-Tag: jmh_marketplace");
        header("Cache-Control: public, max-age=60, s-maxage=180");
    }
    header("X-Robots-Tag: " . $pageRobots, true);
}

$cssFile = __DIR__ . '/../assets/css/style.css';
$assetVer = file_exists($cssFile) ? filemtime($cssFile) : time();

$activeCurr = getUserDetectedCurrency();
$currFlags = ['INR' => '🇮🇳', 'USD' => '🇺🇸', 'EUR' => '🇪🇺'];
$currSymbols = ['INR' => '₹', 'USD' => '$', 'EUR' => '€'];
$currActiveFlag = $currFlags[$activeCurr] ?? '🇮🇳';
$currActiveSymbol = $currSymbols[$activeCurr] ?? '₹';
?>
<!DOCTYPE html>
<html lang="en" data-detected-currency="<?php echo htmlspecialchars($activeCurr); ?>">
<head>
  <!-- Google Tag Manager / Google tag -->
  <script async src="https://www.googletagmanager.com/gtm.js?id=GTM-PBZ69DXR"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
  </script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
  <meta name="google-site-verification" content="s4yWBUBnQA5wFlplKFSFbehGoD5_SbOx37a3y9p7YyM" />
  <base href="<?php echo htmlspecialchars($baseHref); ?>">
  
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($pageDesc); ?>">
  <?php if (!empty($pageKeywords)): ?>
    <meta name="keywords" content="<?php echo htmlspecialchars($pageKeywords); ?>">
  <?php endif; ?>
  <meta name="author" content="<?php echo htmlspecialchars($pageAuthor ?? 'Joule Machine Hub'); ?>">

  <?php if (!empty($geoPlacename)): ?>
    <meta name="geo.placename" content="<?php echo htmlspecialchars($geoPlacename); ?>">
  <?php endif; ?>
  <?php if (!empty($geoRegion)): ?>
    <meta name="geo.region" content="<?php echo htmlspecialchars($geoRegion); ?>">
  <?php endif; ?>

  <?php if (!empty($canonicalUrl)): ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">
  <?php endif; ?>
  
  <!-- OpenGraph Meta Tags for Social & Search Previews -->
  <meta property="og:site_name" content="Joule Machine Hub">
  <meta property="og:locale" content="en_US">
  <meta property="og:title" content="<?php echo htmlspecialchars($ogTitle); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($ogDesc); ?>">
  <meta property="og:type" content="<?php echo htmlspecialchars($ogType); ?>">
  <?php if (!empty($canonicalUrl)): ?>
    <meta property="og:url" content="<?php echo htmlspecialchars($canonicalUrl); ?>">
  <?php endif; ?>
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>">
  <meta property="og:image:alt" content="<?php echo htmlspecialchars($ogImageAlt ?? $ogTitle); ?>">
  <?php if (!empty($productPrice)): ?>
    <meta property="product:price:amount" content="<?php echo htmlspecialchars($productPrice); ?>">
    <meta property="product:price:currency" content="<?php echo htmlspecialchars($productCurrency ?? 'INR'); ?>">
    <meta property="product:availability" content="instock">
    <meta property="product:condition" content="<?php echo !empty($productCondition) ? htmlspecialchars($productCondition) : 'used'; ?>">
  <?php endif; ?>
  
  <!-- Twitter Card Meta Tags -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="@JouleMachineHub">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($ogTitle); ?>">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($ogDesc); ?>">
  <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage); ?>">
  <meta name="twitter:image:alt" content="<?php echo htmlspecialchars($ogImageAlt ?? $ogTitle); ?>">

  <!-- Search Engine Robots Directive -->
  <meta name="robots" content="<?php echo htmlspecialchars($pageRobots); ?>">
  <meta name="googlebot" content="<?php echo htmlspecialchars($pageRobots); ?>">
  
  <?php if (!empty($jsonLd)): ?>
    <!-- Structured JSON-LD Schema Markup (Google Product & Rich Results) -->
    <script type="application/ld+json">
<?php echo is_array($jsonLd) ? json_encode($jsonLd, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $jsonLd; ?>
    </script>
  <?php endif; ?>
  
  <!-- Preconnect & DNS Prefetch for Speed -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
  <link rel="dns-prefetch" href="https://cdn.tailwindcss.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            joule: {
              blue: '#0d2847',
              navy: '#071626',
              brightblue: '#1d4ed8',
              yellow: '#facc15',
              darkyellow: '#eab308',
              amber: '#f59e0b',
              gray: '#f8fafc'
            }
          }
        }
      }
    }
  </script>
  
  <!-- Favicon & Touch Icons -->
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon-v2.png?v=<?php echo $assetVer; ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon-v2.png?v=<?php echo $assetVer; ?>">
  <link rel="shortcut icon" href="/favicon.ico?v=<?php echo $assetVer; ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/favicon-v2.png?v=<?php echo $assetVer; ?>">
  
  <!-- FontAwesome Icons for Industrial Icons (Non-blocking Asynchronous Load) -->
  <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>
  <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo $assetVer; ?>">

  <!-- Universal 2-in-a-row Mobile Alignment Guarantee -->
  <style>
    @media (max-width: 639.98px) {
      .jmh-mobile-grid-2 {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 0.5rem !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
      }
      .jmh-mobile-grid-2 > .span-full,
      .jmh-mobile-grid-2 > .col-span-2 {
        grid-column: 1 / -1 !important;
        width: 100% !important;
      }
      .jmh-mobile-grid-2 > .col-1,
      .jmh-mobile-grid-2 > .col-span-1 {
        grid-column: span 1 !important;
        width: 100% !important;
      }
      .jmh-mobile-grid-2 > * {
        min-width: 0 !important;
        box-sizing: border-box !important;
      }
    }

    /* Equipment Spotlight Card Typography Guarantee */
    .blog-reader-body .jmh-spotlight-card {
      color: #ffffff !important;
    }
    .blog-reader-body .jmh-spotlight-card h3,
    .blog-reader-body .jmh-spotlight-card .spotlight-title {
      color: #ffffff !important;
      font-size: 1.35rem !important;
      line-height: 1.35 !important;
      margin-top: 0 !important;
      margin-bottom: 0.5rem !important;
      border-bottom: none !important;
      padding-bottom: 0 !important;
    }
    .blog-reader-body .jmh-spotlight-card p,
    .blog-reader-body .jmh-spotlight-card .spotlight-desc {
      color: #e2e8f0 !important;
      margin-bottom: 0.75rem !important;
      font-size: 0.875rem !important;
      line-height: 1.6 !important;
    }
    /* Ensure smooth position: sticky across all modern browsers without scroll-container disruption */
    html, body {
      overflow-x: clip !important;
    }
  </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen overflow-x-clip max-w-full w-full pb-20 lg:pb-0">

  <!-- MAIN NAVIGATION: Clean White Theme with Navy & Gold Brand Accents -->
  <header class="bg-white text-slate-800 sticky top-0 z-40 shadow-xs border-b border-slate-200 w-full">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 py-1.5 sm:py-2">
      <div class="flex items-center justify-between gap-1 sm:gap-6">
        
        <!-- Logo (Top navigation left) -->
        <a href="index.php" class="flex items-center group shrink min-w-0" title="Joule Machine Hub - Worldwide Used / New Industrial Machinery Network">
          <img 
            src="/assets/images/logo-v2.png?v=<?php echo $assetVer; ?>" 
            alt="Joule Machine Hub - Worldwide Used / New Industrial Machinery Network" 
            class="h-10 sm:h-11 md:h-12 lg:h-[56px] w-auto max-w-[48vw] sm:max-w-[220px] md:max-w-none object-contain transition-transform group-hover:scale-[1.02] rounded-none"
            width="170"
            height="56"
          >
        </a>

        <!-- CENTER NAVIGATION LINKS (Visible on lg screens only) -->
        <nav class="hidden lg:flex items-center gap-1 text-xs font-bold text-slate-700">
          <a href="index.php#catalog" class="px-3 py-2 rounded-xl hover:text-joule-blue hover:bg-slate-100 transition-colors flex items-center gap-1.5">
            <i class="fa-solid fa-boxes-stacked text-amber-500"></i>
            <span>Browse Machines</span>
          </a>

          <a href="/category" class="px-3 py-2 rounded-xl hover:text-joule-blue hover:bg-slate-100 transition-colors flex items-center gap-1.5">
            <i class="fa-solid fa-shapes text-amber-500"></i>
            <span>Categories</span>
          </a>

          <a href="/about" class="px-3 py-2 rounded-xl hover:text-joule-blue hover:bg-slate-100 transition-colors flex items-center gap-1.5">
            <i class="fa-solid fa-circle-info text-amber-500"></i>
            <span>About Us</span>
          </a>
        </nav>

        <!-- RIGHT ACTION CTAs -->
        <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
          
          <?php if ($logged): ?>
            <div class="flex items-center gap-1.5 sm:gap-2 bg-slate-50 border border-slate-200 rounded-xl p-1 sm:p-1.5 pl-2 sm:pl-3">
              <div class="text-left text-xs hidden md:block">
                <div class="flex items-center gap-1 font-bold text-slate-900 truncate max-w-[120px]">
                  <span><?php echo htmlspecialchars($authUser['name']); ?></span>
                </div>
                <div class="text-[10px] text-amber-700 font-bold uppercase">
                  <?php echo isAdmin() ? '★ Admin Desk' : ($authUser['role'] === 'seller' ? '★ Seller' : '★ Buyer'); ?>
                </div>
              </div>
              <a 
                href="<?php echo isAdmin() ? 'admin.php' : 'dashboard.php'; ?>" 
                class="bg-joule-yellow hover:bg-amber-400 text-slate-950 text-xs font-black px-2.5 sm:px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1"
                title="<?php echo isAdmin() ? 'Go to Admin Portal' : 'Go to Dashboard'; ?>"
              >
                <i class="fa-solid <?php echo isAdmin() ? 'fa-shield-halved' : 'fa-gauge'; ?>"></i>
                <span class="hidden sm:inline"><?php echo isAdmin() ? 'Admin Desk' : 'Dashboard'; ?></span>
              </a>
              <a 
                href="logout.php" 
                class="bg-slate-200 hover:bg-rose-100 text-slate-600 hover:text-rose-700 text-xs px-2 py-1.5 rounded-lg transition-colors"
                title="Sign Out"
              >
                <i class="fa-solid fa-power-off"></i>
              </a>
            </div>
          <?php else: ?>
            <!-- Desktop Sign In / Register (Hidden on small mobile, inside hamburger on mobile) -->
            <div class="hidden md:flex items-center gap-2">
              <a 
                href="login.php" 
                class="text-xs font-bold text-slate-700 hover:text-joule-blue border border-slate-300 hover:border-slate-400 bg-white hover:bg-slate-50 rounded-xl px-3 py-2 flex items-center gap-1.5 transition-colors"
              >
                <i class="fa-solid fa-arrow-right-to-bracket text-amber-500"></i>
                <span>Sign In</span>
              </a>
              <a 
                href="register.php" 
                class="text-xs font-black text-white bg-joule-blue hover:bg-blue-900 rounded-xl px-3.5 py-2 flex items-center gap-1.5 transition-colors shadow-xs"
              >
                <i class="fa-solid fa-user-plus text-joule-yellow"></i>
                <span>Register</span>
              </a>
            </div>
          <?php endif; ?>

          <!-- Currency Switcher Dropdown (INR / USD / EUR) -->
          <div class="relative inline-block text-left" id="currencySwitcherWrap">
            <button 
              type="button" 
              onclick="toggleCurrencyDropdown(event)" 
              class="bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-800 font-bold text-xs px-1.5 sm:px-2.5 py-1.5 sm:py-2 rounded-xl flex items-center gap-1 sm:gap-1.5 transition-colors cursor-pointer"
              title="Change Display Currency"
              aria-label="Currency Selector"
            >
              <span id="currentCurrencyFlag"><?php echo $currActiveFlag; ?></span>
              <span id="currentCurrencyCode" class="text-slate-900 font-black text-[10px] sm:text-xs"><?php echo $activeCurr; ?><span class="hidden sm:inline"> (<?php echo $currActiveSymbol; ?>)</span></span>
              <i class="fa-solid fa-chevron-down text-[8px] sm:text-[9px] text-slate-500"></i>
            </button>
            <div id="currencyDropdownMenu" class="hidden absolute right-0 mt-2 w-36 bg-white rounded-xl shadow-2xl border-2 border-slate-200 py-1 z-50 text-xs animate-in fade-in duration-150">
              <div class="px-3 py-1 text-[10px] font-black uppercase text-slate-400 border-b border-slate-100">
                Select Currency
              </div>
              <button type="button" onclick="setCurrency('INR')" class="w-full text-left px-3 py-2 hover:bg-blue-50 flex items-center justify-between text-slate-800 font-bold cursor-pointer transition-colors">
                <span class="flex items-center gap-2"><span>🇮🇳</span> <span>INR (₹)</span></span>
                <span class="text-[10px] font-mono text-slate-400">Lakhs</span>
              </button>
              <button type="button" onclick="setCurrency('USD')" class="w-full text-left px-3 py-2 hover:bg-blue-50 flex items-center justify-between text-slate-800 font-bold cursor-pointer transition-colors">
                <span class="flex items-center gap-2"><span>🇺🇸</span> <span>USD ($)</span></span>
                <span class="text-[10px] font-mono text-slate-400">$ USD</span>
              </button>
              <button type="button" onclick="setCurrency('EUR')" class="w-full text-left px-3 py-2 hover:bg-blue-50 flex items-center justify-between text-slate-800 font-bold cursor-pointer transition-colors">
                <span class="flex items-center gap-2"><span>🇪🇺</span> <span>EUR (€)</span></span>
                <span class="text-[10px] font-mono text-slate-400">€ Euro</span>
              </button>
            </div>
          </div>

          <!-- Sell Machine Free Button -->
          <button 
            type="button" 
            onclick="openPostModal()" 
            class="bg-joule-yellow hover:bg-amber-400 text-slate-950 text-xs font-black px-2 sm:px-4 py-1.5 sm:py-2.5 rounded-xl shadow-xs hover:shadow-md transition-all flex items-center gap-1 sm:gap-1.5 cursor-pointer border border-amber-300 shrink-0"
          >
            <i class="fa-solid fa-circle-plus text-xs"></i>
            <span class="text-xs font-black">Sell</span>
          </button>

          <!-- Mobile Menu Hamburger Button -->
          <button 
            type="button" 
            id="mobileMenuBtn" 
            onclick="toggleMobileMenu()" 
            class="lg:hidden w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center border border-slate-300 transition-all text-xs sm:text-base focus:outline-none cursor-pointer shrink-0" 
            aria-label="Toggle navigation menu"
          >
            <i id="mobileMenuIcon" class="fa-solid fa-bars"></i>
          </button>
        </div>

      </div>
    </div>

    <!-- Mobile Drawer Menu (Visible when hamburger clicked on mobile/tablet) -->
    <div id="mobileMenuDrawer" class="hidden lg:hidden bg-white border-t border-slate-200 px-4 py-4 space-y-3 shadow-xl transition-all">
      
      <!-- Mobile Auth Action Box -->
      <?php if ($logged): ?>
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3 flex items-center justify-between">
          <div>
            <div class="text-[10px] text-amber-700 font-bold uppercase">
              <?php echo $authUser['role'] === 'seller' ? '★ Verified Seller' : '★ Registered Buyer'; ?>
            </div>
            <div class="font-black text-slate-900 text-xs mt-0.5 truncate max-w-[180px]">
              <?php echo htmlspecialchars($authUser['name']); ?>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <a href="<?php echo isAdmin() ? 'admin.php' : 'dashboard.php'; ?>" class="bg-joule-yellow text-slate-950 font-black px-3 py-1.5 rounded-lg text-xs">
              <?php echo isAdmin() ? 'Admin Desk' : 'Dashboard'; ?>
            </a>
            <a href="logout.php" class="bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold px-2.5 py-1.5 rounded-lg text-xs">
              Logout
            </a>
          </div>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-2 gap-2 pb-1">
          <a href="login.php" class="text-center py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs border border-slate-300 flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-arrow-right-to-bracket text-amber-500"></i>
            <span>Sign In</span>
          </a>
          <a href="register.php" class="text-center py-2.5 rounded-xl bg-joule-blue hover:bg-blue-900 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-user-plus text-joule-yellow"></i>
            <span>Register Free</span>
          </a>
        </div>
      <?php endif; ?>

      <!-- Mobile Navigation Links Grid -->
      <nav class="grid grid-cols-2 gap-2 text-xs font-bold text-slate-700 pt-1">
        <a href="index.php#catalog" onclick="toggleMobileMenu()" class="p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 flex items-center gap-2 border border-slate-200">
          <i class="fa-solid fa-boxes-stacked text-amber-500"></i>
          <span>Browse Machines</span>
        </a>
        <a href="/category" onclick="toggleMobileMenu()" class="p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 flex items-center gap-2 border border-slate-200">
          <i class="fa-solid fa-shapes text-amber-500"></i>
          <span>Categories</span>
        </a>
        <a href="/about" onclick="toggleMobileMenu()" class="p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 flex items-center gap-2 border border-slate-200">
          <i class="fa-solid fa-circle-info text-amber-500"></i>
          <span>About Us</span>
        </a>
        <a href="/about#how-it-works" onclick="toggleMobileMenu()" class="p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 flex items-center gap-2 border border-slate-200">
          <i class="fa-solid fa-diagram-project text-blue-600"></i>
          <span>How It Works</span>
        </a>
        <a href="/blog" onclick="toggleMobileMenu()" class="p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 flex items-center gap-2 border border-slate-200">
          <i class="fa-solid fa-newspaper text-emerald-600"></i>
          <span>Machinery Blog</span>
        </a>
        <a href="/sitemap.php" onclick="toggleMobileMenu()" class="p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 flex items-center gap-2 border border-slate-200">
          <i class="fa-solid fa-sitemap text-purple-600"></i>
          <span>Sitemap Directory</span>
        </a>
      </nav>

      <!-- Mobile Currency Selector -->
      <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5">
        <div class="text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1.5 flex items-center justify-between">
          <span>Display Currency</span>
          <span class="text-joule-blue text-[11px] font-black" id="mobileActiveCurrencyLabel">INR (₹)</span>
        </div>
        <div class="grid grid-cols-3 gap-1.5 text-xs font-bold">
          <button type="button" onclick="setCurrency('INR')" class="py-1.5 px-2 rounded-lg bg-white border border-slate-200 text-slate-800 hover:bg-joule-blue hover:text-white transition-colors text-center cursor-pointer">
            🇮🇳 INR
          </button>
          <button type="button" onclick="setCurrency('USD')" class="py-1.5 px-2 rounded-lg bg-white border border-slate-200 text-slate-800 hover:bg-joule-blue hover:text-white transition-colors text-center cursor-pointer">
            🇺🇸 USD
          </button>
          <button type="button" onclick="setCurrency('EUR')" class="py-1.5 px-2 rounded-lg bg-white border border-slate-200 text-slate-800 hover:bg-joule-blue hover:text-white transition-colors text-center cursor-pointer">
            🇪🇺 EUR
          </button>
        </div>
      </div>

      <!-- Instant Machine Enquiry Form Button in Drawer -->
      <button type="button" onclick="closeMobileMenu(); openRequirementModal();" class="block w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs text-center border border-emerald-600 shadow-sm cursor-pointer">
        <i class="fa-solid fa-file-signature mr-1.5"></i> Instant Machine Enquiry Form
      </button>
    </div>

  </header>
