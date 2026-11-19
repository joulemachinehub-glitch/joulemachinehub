<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';

$categories = array_values(array_unique(array_filter(array_column($machines, 'category'))));

// Page SEO Settings
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$canonicalUrl = $protocol . $host . '/sitemap.php';
$pageTitle = "Used & New Industrial Machinery Sitemap | Joule Machine Hub";
$pageDesc = "Browse the complete Joule Machine Hub directory of used and new industrial machinery, equipment categories and product listings. Find machines and submit enquiries.";
$pageRobots = "index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1";

$jsonLd = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'CollectionPage',
            'name' => $pageTitle,
            'description' => $pageDesc,
            'url' => $canonicalUrl,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Joule Machine Hub',
                'url' => $protocol . $host . '/'
            ]
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => $protocol . $host . '/'
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Industrial Machinery Sitemap',
                    'item' => $canonicalUrl
                ]
            ]
        ]
    ]
];

require_once __DIR__ . '/includes/header.php';

// Group machines by category
$machinesByCategory = [];
foreach ($categories as $cat) {
    if ($cat === 'All Categories') continue;
    $machinesByCategory[$cat] = array_filter($machines, fn($m) => $m['category'] === $cat);
}

// Unique brands
$uniqueBrands = [];
foreach ($machines as $m) {
    if (!empty($m['brand'])) {
        $uniqueBrands[$m['brand']] = ($uniqueBrands[$m['brand']] ?? 0) + 1;
    }
}
arsort($uniqueBrands);
?>

<main class="max-w-7xl mx-auto px-4 py-8 sm:py-12 flex-1 w-full space-y-10">

  <!-- 1. Breadcrumbs & Header -->
  <div class="space-y-3 border-b border-slate-200 pb-6">
    <nav class="flex items-center gap-2 text-xs text-slate-500 font-semibold">
      <a href="index.php" class="hover:text-joule-brightblue flex items-center gap-1">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
      </a>
      <span>/</span>
      <span class="text-slate-800 font-bold">HTML Sitemap & Directory</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-950 flex items-center gap-3">
          <span class="w-10 h-10 rounded-xl bg-joule-blue text-joule-yellow flex items-center justify-center text-lg">
            <i class="fa-solid fa-sitemap"></i>
          </span>
          <span>HTML Sitemap & Directory</span>
        </h1>
        <p class="text-xs text-slate-600 mt-1">
          Explore all <?php echo count($machines); ?> verified used and new industrial machines across all <?php echo count($categories); ?> core machinery categories.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button type="button" onclick="openRequirementModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs px-4 py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition-all cursor-pointer">
          <i class="fa-solid fa-file-signature"></i>
          <span>Instant Machine Inquiry</span>
        </button>
      </div>
    </div>
  </div>

  <!-- 2. Main Marketplace Links & Quick Actions Grid -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <!-- Box 1: Core Navigation -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 space-y-3 shadow-xs">
      <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2">
        <i class="fa-solid fa-compass text-joule-brightblue"></i>
        <span>Primary Marketplace Pages</span>
      </h3>
      <ul class="space-y-2 text-xs font-semibold text-slate-700">
        <li>
          <a href="index.php" class="hover:text-joule-brightblue flex items-center justify-between">
            <span>Marketplace Home</span>
            <span class="text-[10px] text-slate-400">Main Portal</span>
          </a>
        </li>
        <li>
          <a href="index.php#catalog" class="hover:text-joule-brightblue flex items-center justify-between">
            <span>Browse All Machinery (<?php echo count($machines); ?> Units)</span>
            <span class="text-[10px] bg-blue-100 text-blue-900 font-bold px-1.5 py-0.5 rounded">Catalog</span>
          </a>
        </li>
        <li>
          <a href="/category" class="hover:text-joule-brightblue flex items-center justify-between">
            <span>All Categories Hub (<?php echo count($categories); ?> Categories)</span>
            <span class="text-[10px] text-slate-400">Directory</span>
          </a>
        </li>
        <li>
          <a href="/about" class="hover:text-joule-brightblue flex items-center justify-between font-bold text-joule-blue">
            <span>About Us &amp; Business Model</span>
            <span class="text-[10px] bg-amber-100 text-amber-950 font-bold px-1.5 py-0.5 rounded">Mediator Desk</span>
          </a>
        </li>
        <li>
          <a href="/blog" class="hover:text-joule-brightblue flex items-center justify-between font-bold text-joule-blue">
            <span>Machinery Blog &amp; Engineering Guides</span>
            <span class="text-[10px] bg-amber-100 text-amber-950 font-bold px-1.5 py-0.5 rounded">5 Guides</span>
          </a>
        </li>
        <li>
          <a href="index.php#faqs" class="hover:text-joule-brightblue flex items-center justify-between">
            <span>Buyer & Seller FAQ Guide</span>
            <span class="text-[10px] text-slate-400">6 Questions</span>
          </a>
        </li>
      </ul>
    </div>

    <!-- Box 2: Buyer & Seller Actions -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 space-y-3 shadow-xs">
      <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2">
        <i class="fa-solid fa-bolt text-amber-500"></i>
        <span>Buyer & Seller Services</span>
      </h3>
      <ul class="space-y-2 text-xs font-semibold text-slate-700">
        <li>
          <button type="button" onclick="openPostModal()" class="w-full text-left hover:text-joule-brightblue flex items-center justify-between cursor-pointer">
            <span>Post Machine for Sale (New or Used)</span>
            <span class="text-[10px] bg-amber-100 text-amber-950 font-bold px-1.5 py-0.5 rounded">100% Free</span>
          </button>
        </li>
        <li>
          <button type="button" onclick="openRequirementModal()" class="w-full text-left hover:text-joule-brightblue flex items-center justify-between cursor-pointer">
            <span>Post Machinery Buy Requirement (RFQ)</span>
            <span class="text-[10px] bg-emerald-100 text-emerald-950 font-bold px-1.5 py-0.5 rounded">Direct Match</span>
          </button>
        </li>
        <li>
          <a href="login.php" class="hover:text-joule-brightblue flex items-center justify-between">
            <span>User / Seller Sign In</span>
            <span class="text-[10px] text-slate-400">Portal Access</span>
          </a>
        </li>
        <li>
          <a href="register.php" class="hover:text-joule-brightblue flex items-center justify-between">
            <span>Register as Verified Buyer / Seller</span>
            <span class="text-[10px] text-slate-400">Free Account</span>
          </a>
        </li>
      </ul>
    </div>

    <!-- Box 3: Contact & Valuation Desk -->
    <div class="bg-gradient-to-br from-blue-900 to-joule-navy text-white rounded-2xl p-5 space-y-3 shadow-sm border border-blue-800">
      <h3 class="text-sm font-black text-joule-yellow uppercase tracking-wider flex items-center gap-2 border-b border-blue-800 pb-2">
        <i class="fa-solid fa-phone-volume"></i>
        <span>Direct Machinery Desk</span>
      </h3>
      <div class="space-y-2 text-xs text-blue-100">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-file-signature text-joule-yellow"></i>
          <span>Direct Inquiry Desk: <button type="button" onclick="openRequirementModal()" class="font-bold text-joule-yellow hover:underline ml-1 cursor-pointer">Fill Inquiry Form →</button></span>
        </div>
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-envelope text-joule-yellow"></i>
          <span>Email: <a href="mailto:joulemachinehub@gmail.com" class="font-bold text-white hover:underline">joulemachinehub@gmail.com</a></span>
        </div>
        <div class="text-[11px] text-blue-200/80 leading-relaxed pt-1 border-t border-blue-800">
          <strong class="text-white">B2B Mediator Desk:</strong> Connect with verified industrial machinery inventory worldwide through our mediated desk.
        </div>
      </div>
    </div>

  </div>

  <!-- 3. Machinery Categories (All Core Sectors) -->
  <div id="collections" class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-4 shadow-xs scroll-mt-20">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
      <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
        <i class="fa-solid fa-gears text-joule-brightblue"></i>
        <span>Machinery Categories</span>
      </h3>
      <span class="text-xs text-slate-500 font-bold"><?php echo count($categories); ?> Core Categories</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
      <?php foreach ($categories as $cat): 
        if ($cat === 'All Categories') continue;
        $cCount = count(array_filter($machines, fn($m) => $m['category'] === $cat));
        $catMeta = null;
        global $categoryMap;
        foreach ($categoryMap as $cm) {
          if (strtolower($cm['name']) === strtolower($cat)) {
            $catMeta = $cm;
            break;
          }
        }
      ?>
        <a 
          href="<?php echo htmlspecialchars(getCategoryUrl($cat)); ?>"
          class="p-4 rounded-xl border border-slate-200 hover:border-joule-brightblue hover:bg-blue-50/50 flex flex-col justify-between transition-all group shadow-xs"
        >
          <div class="space-y-1">
            <div class="font-black text-xs sm:text-sm text-slate-800 group-hover:text-joule-brightblue flex items-center justify-between">
              <span><?php echo htmlspecialchars($cat); ?></span>
              <i class="fa-solid fa-arrow-right text-[11px] text-slate-300 group-hover:text-joule-brightblue group-hover:translate-x-1 transition-all"></i>
            </div>
            <?php if ($catMeta && !empty($catMeta['description'])): ?>
              <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed">
                <?php echo htmlspecialchars($catMeta['description']); ?>
              </p>
            <?php endif; ?>
          </div>
          <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[10px] text-slate-400 font-semibold">Collection URL</span>
            <span class="text-[11px] bg-slate-100 text-slate-700 font-mono font-bold px-2 py-0.5 rounded-full group-hover:bg-joule-blue group-hover:text-joule-yellow">
              <?php echo $cCount; ?> Available
            </span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- 3B. Priority Manufacturers Directory -->
  <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-4 shadow-xs">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
      <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
        <i class="fa-solid fa-industry text-joule-brightblue"></i>
        <span>Priority Manufacturers Directory</span>
      </h3>
      <span class="text-xs text-slate-500 font-bold">Key Global OEMs</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
      <?php 
        $priorityMfgs = [
          'komatsu' => ['name' => 'Komatsu', 'desc' => 'Mechanical Power Presses & Stamping'],
          'amada' => ['name' => 'Amada', 'desc' => 'Power Presses & Sheet Metal Machinery'],
          'dingli' => ['name' => 'Dingli', 'desc' => 'Aerial Work Platforms & Scissor Lifts'],
          'haitian' => ['name' => 'Haitian', 'desc' => 'Plastic Injection Molding Machines']
        ];
        foreach ($priorityMfgs as $mSlug => $mData):
      ?>
        <a href="manufacturer/<?php echo htmlspecialchars($mSlug); ?>" class="p-4 rounded-xl border border-slate-200 hover:border-joule-brightblue hover:bg-blue-50/50 flex flex-col justify-between transition-all group shadow-xs">
          <div>
            <div class="font-black text-xs sm:text-sm text-slate-800 group-hover:text-joule-brightblue flex items-center justify-between">
              <span><?php echo htmlspecialchars($mData['name']); ?></span>
              <i class="fa-solid fa-arrow-right text-[11px] text-slate-300 group-hover:text-joule-brightblue"></i>
            </div>
            <p class="text-[11px] text-slate-500 mt-1"><?php echo htmlspecialchars($mData['desc']); ?></p>
          </div>
          <div class="mt-3 pt-2 border-t border-slate-100 text-[10px] text-joule-brightblue font-bold">
            View <?php echo htmlspecialchars($mData['name']); ?> Inventory →
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- 4. Complete Machinery Listing Directory (All Verified Units Grouped by Category) -->
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b-2 border-slate-200 pb-4">
      <div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-950 flex items-center gap-2.5">
          <i class="fa-solid fa-boxes-stacked text-joule-brightblue"></i>
          <span>Complete Machinery Catalog (<?php echo count($machines); ?> Verified Units)</span>
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Click any unit to inspect detailed technical specifications, on-site photos, and direct seller contact details.
        </p>
      </div>
      <span class="text-xs bg-joule-yellow text-slate-950 font-black px-3 py-1 rounded-full border border-amber-300 self-start sm:self-auto">
        Updated Daily
      </span>
    </div>

    <?php foreach ($machinesByCategory as $catName => $catMachines): ?>
      <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-4 shadow-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
          <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-joule-brightblue"></span>
            <span><?php echo htmlspecialchars($catName); ?></span>
          </h3>
          <span class="text-xs text-slate-500 font-bold"><?php echo count($catMachines); ?> Units</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <?php foreach ($catMachines as $m): 
            $mUrl = getMachineUrl($m);
          ?>
            <a 
              href="<?php echo htmlspecialchars($mUrl); ?>" 
              class="p-3 rounded-2xl border border-slate-200 hover:border-joule-brightblue hover:bg-blue-50/40 transition-all flex items-start gap-3 group"
            >
              <img 
                src="<?php echo htmlspecialchars($m['images'][0]); ?>" 
                alt="<?php echo htmlspecialchars($m['year'] . ' ' . $m['brand'] . ' ' . $m['model'] . ' ' . $m['category']); ?>" 
                class="w-16 h-16 rounded-xl object-cover bg-slate-900 border border-slate-200 shrink-0 group-hover:scale-105 transition-transform"
                loading="lazy"
              >
              <div class="min-w-0 flex-1 text-xs">
                <div class="flex items-center gap-1.5 text-[10px] text-slate-500">
                  <span class="font-bold text-blue-700"><?php echo htmlspecialchars($m['brand'] ?? 'Industrial'); ?></span>
                  <span>•</span>
                  <span><?php echo htmlspecialchars((string)($m['year'] ?? 'Verified')); ?></span>
                </div>
                <h4 class="font-bold text-slate-900 group-hover:text-joule-brightblue truncate mt-0.5" title="<?php echo htmlspecialchars($m['title'] ?? ''); ?>">
                  <?php echo htmlspecialchars($m['title'] ?? 'Verified Machinery'); ?>
                </h4>
                <div class="flex items-center justify-between mt-1 text-[11px]">
                  <span class="font-black text-slate-950"><?php echo htmlspecialchars($m['price_display'] ?? (!empty($m['price']) ? '₹' . number_format($m['price']) : 'Request Price')); ?></span>
                  <span class="text-slate-500 truncate max-w-[100px]"><?php echo htmlspecialchars($m['city'] ?? 'Global Hub'); ?></span>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- 5. Popular Brands Directory -->
  <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-4 shadow-xs">
    <h3 class="text-base font-black text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
      <i class="fa-solid fa-award text-amber-500"></i>
      <span>Popular Industrial Brands & Manufacturers</span>
    </h3>
    <div class="flex flex-wrap gap-2">
      <?php foreach ($uniqueBrands as $bName => $bCount): ?>
        <?php
          $brandMachine = null;
          foreach ($machines as $bm) {
            if (isset($bm['brand']) && strtolower(trim($bm['brand'])) === strtolower(trim($bName))) {
              $brandMachine = $bm;
              break;
            }
          }
          $brandUrl = $brandMachine ? getMachineUrl($brandMachine) : ('category/' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $bName)));
        ?>
        <a 
          href="<?php echo htmlspecialchars($brandUrl); ?>" 
          class="px-3 py-1.5 rounded-xl border border-slate-200 hover:border-joule-brightblue hover:bg-blue-50 text-xs font-semibold text-slate-700 flex items-center gap-1.5 transition-colors"
        >
          <span><?php echo htmlspecialchars($bName); ?></span>
          <span class="text-[10px] text-slate-400 font-mono">(<?php echo $bCount; ?>)</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
