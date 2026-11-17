<?php
// Joule Machine Hub - Dedicated Machinery Category Page
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';

// Resolve requested category by slug or query parameter
$slugOrName = isset($_GET['slug']) ? trim($_GET['slug']) : (isset($_GET['category']) ? trim($_GET['category']) : '');
$categoryInfo = getCategoryBySlug($slugOrName);

// 404 Handling: If a category slug was requested but does not exist, show 404 redirect
if (!empty($slugOrName) && !$categoryInfo) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// If accessed bare without parameters, load the master category directory directly (prevents any redirect loop)
if (empty($slugOrName) && !$categoryInfo) {
    require __DIR__ . '/categories.php';
    exit;
}

$categoryName = $categoryInfo['name'];
$categorySlug = $categoryInfo['slug'];

// STRICT CLEAN SEO SLUG ENFORCEMENT: Redirect legacy .php URLs to canonical slug
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (str_contains($requestUri, 'category.php')) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: /category/" . $categorySlug);
    exit;
}

// Filter parameters within this category
$countryFilter = isset($_GET['country']) ? trim($_GET['country']) : 'All Countries';
$sortBy = isset($_GET['sort']) ? trim($_GET['sort']) : 'featured';

// Filter machines belonging to this category
$catMachines = array_values(array_filter($machines, function($m) use ($categoryName, $countryFilter) {
    if (strtolower(trim($m['category'])) !== strtolower(trim($categoryName))) {
        return false;
    }
    if ($countryFilter !== 'All Countries' && isset($m['country']) && strtolower($m['country']) !== strtolower($countryFilter)) {
        return false;
    }
    return true;
}));

// Sort machines
usort($catMachines, function($a, $b) use ($sortBy) {
    if ($sortBy === 'priceLow') return $a['price'] - $b['price'];
    if ($sortBy === 'priceHigh') return $b['price'] - $a['price'];
    if ($sortBy === 'yearNew') return $b['year'] - $a['year'];
    return ($b['is_featured'] ? 1 : 0) - ($a['is_featured'] ? 1 : 0);
});

// SEO Metadata Configuration
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$canonicalUrl = $protocol . $host . '/' . getCategoryUrl($categoryName);

$pageTitle = !empty($categoryInfo['meta_title']) ? $categoryInfo['meta_title'] : ("Used & New " . $categoryName . " for Sale | Joule Machine Hub");
$pageDesc = !empty($categoryInfo['meta_desc']) ? $categoryInfo['meta_desc'] : ("Browse verified used and new " . strtolower($categoryName) . " from manufacturing plants and OEMs.");
$pageKeywords = "used " . strtolower($categoryName) . ", new " . strtolower($categoryName) . ", second hand " . strtolower($categoryName) . ", buy " . strtolower($categoryName) . ", industrial " . strtolower($categoryName) . " for sale, plant liquidation";
$ogTitle = $pageTitle;
$ogDesc = $pageDesc;
$ogImage = $protocol . $host . '/' . ltrim($categoryInfo['image'], '/');
$ogType = "website";
$pageRobots = "index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1"; // Clean SEO indexable collections page

// Build Category ItemList & FAQPage for Google SERP Rich Results
$categoryItemListElements = [];
foreach (array_slice($catMachines, 0, 10) as $pos => $m) {
    $categoryItemListElements[] = [
        '@type' => 'ListItem',
        'position' => $pos + 1,
        'name' => ($m['year'] ? $m['year'] . ' ' : '') . $m['title'],
        'url' => $protocol . $host . '/' . getMachineUrl($m),
        'image' => !empty($m['images'][0]) ? ($protocol . $host . '/' . ltrim($m['images'][0], '/')) : null
    ];
}

// JSON-LD Schema (CollectionPage + BreadcrumbList + ItemList + FAQPage)
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
                    'name' => 'Categories',
                    'item' => $protocol . $host . '/#catalog'
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $categoryName,
                    'item' => $canonicalUrl
                ]
            ]
        ],
        [
            '@type' => 'ItemList',
            'name' => 'Available Used & New ' . $categoryName . ' for Sale',
            'numberOfItems' => count($catMachines),
            'itemListElement' => $categoryItemListElements
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => "What types of used and new $categoryName are available on Joule Machine Hub?",
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => "We list verified used and new $categoryName from leading international manufacturers worldwide through our structured B2B mediator desk."
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => "How are machine inspections scheduled for $categoryName?",
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => "Physical on-site equipment walkthroughs and technical dry runs are scheduled through our mediator desk following formal commercial agreement and transaction milestones."
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => "How are export shipping and logistics handled for $categoryName?",
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => "Full industrial crane rigging, sea-worthy vapor-barrier packing, and multimodal ocean container freight forwarding can be arranged to any major seaport worldwide with full export clearance documentation."
                    ]
                ]
            ]
        ]
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- BREADCRUMB BAR -->
<div class="bg-white border-b border-slate-200 py-3 px-4">
  <div class="max-w-7xl mx-auto flex items-center flex-wrap gap-2 text-xs text-slate-500">
    <a href="index.php" class="hover:text-joule-brightblue font-bold flex items-center gap-1">
      <i class="fa-solid fa-house text-slate-400 text-[10px]"></i>
      <span>Home</span>
    </a>
    <span class="text-slate-300">/</span>
    <a href="index.php#catalog" class="hover:text-joule-brightblue font-medium">Categories</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-black flex items-center gap-1.5">
      <i class="fa-solid <?php echo htmlspecialchars($categoryInfo['icon']); ?> text-joule-brightblue"></i>
      <span><?php echo htmlspecialchars($categoryName); ?></span>
    </span>
  </div>
</div>

<!-- CATEGORY HERO BANNER -->
<section class="bg-gradient-to-br from-joule-navy via-joule-blue to-blue-950 text-white relative overflow-hidden border-b-4 border-joule-yellow py-10 sm:py-14">
  <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#facc15_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-4 relative z-10 space-y-6">
    <div class="max-w-3xl space-y-3">
      <div class="inline-flex items-center gap-2 bg-joule-yellow/20 border border-joule-yellow/50 text-joule-yellow px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider">
        <i class="fa-solid <?php echo htmlspecialchars($categoryInfo['icon']); ?>"></i>
        <span>Verified Machinery Category</span>
      </div>

      <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-white leading-tight">
        Second Hand / Used / Old / Imported &amp; New <span class="text-joule-yellow"><?php echo htmlspecialchars($categoryName); ?></span>
      </h1>

      <p class="text-blue-100 text-xs sm:text-sm sm:leading-relaxed font-medium">
        <?php echo htmlspecialchars($categoryInfo['tagline']); ?>
      </p>

      <!-- Key Specifications Highlights -->
      <?php if (!empty($categoryInfo['key_specs'])): ?>
        <div class="flex flex-wrap gap-2 pt-2">
          <?php foreach ($categoryInfo['key_specs'] as $kspec): ?>
            <span class="bg-white/10 backdrop-blur-md border border-white/20 text-white px-3 py-1 rounded-lg text-xs font-bold">
              ✓ <?php echo htmlspecialchars($kspec); ?>
            </span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick Trust Badges Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 pt-4 border-t border-blue-800/80 text-xs">
      <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10 flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-joule-yellow text-slate-950 flex items-center justify-center font-bold text-sm shrink-0">
          <i class="fa-solid fa-phone-volume"></i>
        </div>
        <div>
          <div class="text-white font-black text-xs">Mediated B2B Desk</div>
          <div class="text-[10px] text-blue-200">Verified inventory sourcing</div>
        </div>
      </div>

      <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10 flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-sm shrink-0">
          <i class="fa-solid fa-bolt"></i>
        </div>
        <div>
          <div class="text-white font-black text-xs">Scheduled Inspection</div>
          <div class="text-[10px] text-blue-200">Arranged upon agreement</div>
        </div>
      </div>

      <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10 flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-blue-500 text-white flex items-center justify-center font-bold text-sm shrink-0">
          <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div>
          <div class="text-white font-black text-xs"><?php echo count($catMachines); ?> Available</div>
          <div class="text-[10px] text-blue-200">Ready for liquidation</div>
        </div>
      </div>

      <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10 flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center font-bold text-sm shrink-0">
          <i class="fa-solid fa-ship"></i>
        </div>
        <div>
          <div class="text-white font-black text-xs">Seaworthy Freight</div>
          <div class="text-[10px] text-blue-200">Container loading help</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- MAIN CATEGORY CATALOG & MACHINES LIST -->
<main class="max-w-7xl mx-auto px-4 py-8 sm:py-10 space-y-8 flex-1 w-full">
  
  <!-- Category Navigation Bar (Cross-link other categories) -->
  <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2 border-b border-slate-200">
    <span class="text-xs font-black text-slate-500 uppercase tracking-wider shrink-0 mr-1">Other Categories:</span>
    <?php foreach ($categoryMap as $cslug => $cinfo): 
      $isActive = ($cslug === $categorySlug);
    ?>
      <a 
        href="<?php echo htmlspecialchars(getCategoryUrl($cinfo['name'])); ?>"
        class="shrink-0 px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 <?php echo $isActive ? 'bg-[#071626] text-white shadow-sm ring-2 ring-joule-yellow' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'; ?>"
      >
        <i class="fa-solid <?php echo htmlspecialchars($cinfo['icon']); ?> text-[11px] <?php echo $isActive ? 'text-joule-yellow' : 'text-slate-400'; ?>"></i>
        <span><?php echo htmlspecialchars($cinfo['name']); ?></span>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Header & Sort Bar -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h2 class="text-xl sm:text-2xl font-black text-slate-950 flex items-center gap-2">
        <span>Available <?php echo htmlspecialchars($categoryName); ?> Units</span>
        <span class="text-xs bg-joule-yellow text-slate-950 font-black px-2.5 py-0.5 rounded-full border border-amber-300">
          <?php echo count($catMachines); ?> Available
        </span>
      </h2>
      <p class="text-xs text-slate-500 mt-0.5">
        Direct plant liquidations. All machines available for physical inspection and power run.
      </p>
    </div>

    <!-- Sort Options (Client-Side Instant Sorting - No New URL Created) -->
    <div class="flex items-center gap-2 self-end sm:self-auto text-xs">
      <span class="text-slate-500 font-bold">Sort By:</span>
      <select 
        id="categorySortSelect"
        onchange="sortCategoryMachines(this.value);" 
        class="bg-white border-2 border-slate-300 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-700 focus:outline-none focus:border-joule-blue cursor-pointer"
      >
        <option value="featured">Featured / Recommended</option>
        <option value="priceLow">Price: Low to High</option>
        <option value="priceHigh">Price: High to Low</option>
        <option value="yearNew">Mfg Year: Newest First</option>
      </select>
    </div>
  </div>

  <!-- Machines Grid -->
  <?php if (!empty($catMachines)): ?>
    <div id="categoryMachinesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($catMachines as $idx => $machine): 
        $topSpecs = !empty($machine['specs']) && is_array($machine['specs']) ? array_slice($machine['specs'], 0, 3) : [];
      ?>
        <div 
          class="cat-machine-card bg-white rounded-2xl border-2 border-slate-200 overflow-hidden hover:border-joule-blue hover:shadow-xl transition-all duration-300 flex flex-col group relative"
          data-id="<?php echo $machine['id']; ?>"
          data-index="<?php echo $idx; ?>"
          data-price="<?php echo floatval($machine['value_raw'] ?? 0); ?>"
          data-year="<?php echo intval($machine['year'] ?? 0); ?>"
        >
          
          <!-- Image Section -->
          <div class="relative aspect-[16/10] bg-slate-900 overflow-hidden">
            <a href="<?php echo htmlspecialchars(getMachineUrl($machine)); ?>" class="block w-full h-full">
              <img 
                src="<?php echo htmlspecialchars('/' . ltrim($machine['images'][0], '/')); ?>" 
                alt="<?php echo htmlspecialchars($machine['year'] . ' ' . $machine['brand'] . ' ' . $machine['model'] . ' ' . $categoryName . ' - Verified in ' . $machine['city'] . ', ' . $machine['country']); ?>" 
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                loading="lazy"
                decoding="async"
              >
            </a>
            
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/30 pointer-events-none"></div>

            <!-- Item Number / Stock ID Badge -->
            

            <!-- Inspection Photos Badge -->
            <div class="absolute top-2.5 right-2.5 z-10">
              <span class="bg-slate-950/85 backdrop-blur-md text-white text-[10px] font-bold px-2 py-1 rounded-md border border-white/20 flex items-center gap-1 shadow-sm">
                <i class="fa-solid fa-camera text-joule-yellow text-[10px]"></i> <?php echo count($machine['images']); ?> Photos
              </span>
            </div>

            <!-- Bottom photo stats -->
            <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between text-white text-xs z-10">
              <span class="bg-slate-900/85 backdrop-blur-md px-2.5 py-1 rounded-md border border-white/20 font-semibold text-[11px]">
                Year: <?php echo $machine['year']; ?> • <?php echo $machine['hours_run']; ?>
              </span>
              <span class="bg-emerald-600 font-bold px-2 py-1 rounded-md text-[10px]">
                ✓ Running Condition
              </span>
            </div>
          </div>

          <!-- Card Body Content -->
          <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
            <div>
              <div class="flex items-center justify-between mb-1">
                <div class="text-[10px] font-black uppercase tracking-wider text-joule-brightblue">
                  <?php echo htmlspecialchars($machine['brand']); ?>
                </div>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">
                  <?php echo getCountryFlagHtml($machine['country'] ?? 'Japan', 'w-4 h-3'); ?>
                  <span><?php echo htmlspecialchars($machine['country'] ?? 'Worldwide'); ?></span>
                </span>
              </div>

              <h3 class="text-base sm:text-lg font-black text-slate-950 group-hover:text-joule-brightblue transition-colors line-clamp-2 min-h-[3.25rem] leading-snug">
                <a href="<?php echo htmlspecialchars(getMachineUrl($machine)); ?>">
                  <?php echo htmlspecialchars($machine['title']); ?>
                </a>
              </h3>

              <!-- Price -->
              <div class="flex items-baseline gap-2 mt-2">
                <span class="text-2xl font-black text-slate-950 machine-price-display" data-price-inr="<?php echo $machine['price']; ?>" data-price-usd="<?php echo $machine['price_usd']; ?>">
                  <?php echo formatMachinePrice($machine); ?>
                </span>
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                  Negotiable (Ex-Works)
                </span>
              </div>

              <!-- Specs Grid -->
              <div class="grid grid-cols-2 gap-2 bg-slate-50 rounded-2xl p-3 border border-slate-100 text-xs mt-3">
                <?php foreach ($topSpecs as $sKey => $sVal): ?>
                  <div class="truncate">
                    <span class="text-slate-400 block text-[9px] uppercase font-bold"><?php echo htmlspecialchars($sKey); ?></span>
                    <span class="font-bold text-slate-800 truncate block"><?php echo htmlspecialchars($sVal); ?></span>
                  </div>
                <?php endforeach; ?>
                <div class="truncate">
                  <span class="text-slate-400 block text-[9px] uppercase font-bold">Inspection</span>
                  <span class="font-bold text-emerald-600 truncate block">Plant Floor Ready</span>
                </div>
              </div>

              <!-- Location & Seller Info -->
              <div class="border-t border-slate-100 pt-3 mt-3 text-xs text-slate-600 space-y-1">
                <div class="flex items-center gap-1.5 truncate">
                  <i class="fa-solid fa-location-dot text-slate-400 text-xs"></i>
                  <span class="truncate"><?php echo htmlspecialchars(formatMachineLocation($machine)); ?></span>
                </div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-1.5 truncate">
                    <i class="fa-solid fa-building text-emerald-600 text-xs"></i>
                    <span class="font-bold text-slate-800 truncate"><?php echo htmlspecialchars($machine['seller']['name']); ?></span>
                  </div>
                  <span class="text-[10px] bg-amber-50 text-amber-900 border border-amber-200 font-black px-1.5 py-0.5 rounded">
                    ★ <?php echo $machine['seller']['rating']; ?>
                  </span>
                </div>
              </div>
            </div>

            <!-- CTA: Instant Enquiry Button -->
            <div class="space-y-2 pt-2 border-t border-slate-100">
              <button 
                type="button" 
                onclick="openInquiryModal('<?php echo $machine['id']; ?>')"
                class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all text-xs cursor-pointer"
              >
                <i class="fa-solid fa-file-signature"></i>
                <span>Enquire Now (Fill Form)</span>
              </button>

              <a 
                href="<?php echo htmlspecialchars(getMachineUrl($machine)); ?>" 
                class="w-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors"
              >
                <span>View Complete Technical Specs</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
              </a>
            </div>

          </div>

        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="bg-white rounded-2xl border-2 border-dashed border-slate-300 p-12 text-center space-y-3">
      <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400 text-xl">
        <i class="fa-solid fa-box-open"></i>
      </div>
      <h3 class="font-bold text-slate-800 text-base">No listings currently active in this category filter</h3>
      <p class="text-xs text-slate-500 max-w-md mx-auto">
        Please reset your filters or post a requirement to notify verified plant liquidators immediately.
      </p>
      <div class="pt-2">
        <button type="button" onclick="openRequirementModal()" class="bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black text-xs px-4 py-2.5 rounded-xl">
          Post Buy Requirement (RFQ)
        </button>
      </div>
    </div>
  <?php endif; ?>

  <!-- Category Industrial Buying Guide & Plant Verification Box -->
  <div class="bg-gradient-to-r from-blue-50 to-slate-50 rounded-2xl border-2 border-blue-200/80 p-6 sm:p-8 space-y-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-joule-blue text-joule-yellow flex items-center justify-center text-lg font-bold">
        <i class="fa-solid fa-shield-halved"></i>
      </div>
      <div>
        <h3 class="text-base sm:text-lg font-black text-slate-950">
          Direct Plant Verification & Factory Floor Inspection Guarantee
        </h3>
        <p class="text-xs text-slate-600">
          How Joule Machine Hub ensures authentic machine conditions for <?php echo htmlspecialchars($categoryName); ?>.
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 text-xs">
      <div class="bg-white rounded-xl p-4 border border-slate-200 space-y-1.5">
        <div class="font-black text-slate-900 flex items-center gap-1.5 text-xs">
          <i class="fa-solid fa-building-circle-check text-emerald-600"></i>
          <span>Factory Floor Inspection</span>
        </div>
        <p class="text-slate-600 leading-relaxed text-[11px]">
          Schedule an on-site plant visit to physically inspect the machine, examine structural assemblies, and verify control cabinets.
        </p>
      </div>

      <div class="bg-white rounded-xl p-4 border border-slate-200 space-y-1.5">
        <div class="font-black text-slate-900 flex items-center gap-1.5 text-xs">
          <i class="fa-solid fa-id-card-clip text-blue-600"></i>
          <span>Physical Nameplate Match</span>
        </div>
        <p class="text-slate-600 leading-relaxed text-[11px]">
          Every unit includes high-resolution manufacturer plate photos documenting technical specifications, build year, voltage, and original OEM stamping.
        </p>
      </div>

      <div class="bg-white rounded-xl p-4 border border-slate-200 space-y-1.5">
        <div class="font-black text-slate-900 flex items-center gap-1.5 text-xs">
          <i class="fa-solid fa-truck-ramp-box text-amber-600"></i>
          <span>Rigging & Loading Assistance</span>
        </div>
        <p class="text-slate-600 leading-relaxed text-[11px]">
          Factory floor rigging, heavy crane loading onto low-bed trailers, and ocean export container preparation arranged directly.
        </p>
      </div>
    </div>
  </div>

</main>

<script>
function sortCategoryMachines(criteria) {
  const grid = document.getElementById('categoryMachinesGrid');
  if (!grid) return;
  const cards = Array.from(grid.querySelectorAll('.cat-machine-card'));
  
  cards.sort((a, b) => {
    if (criteria === 'priceLow') {
      return parseFloat(a.dataset.price || 0) - parseFloat(b.dataset.price || 0);
    } else if (criteria === 'priceHigh') {
      return parseFloat(b.dataset.price || 0) - parseFloat(a.dataset.price || 0);
    } else if (criteria === 'yearNew') {
      return parseInt(b.dataset.year || 0, 10) - parseInt(a.dataset.year || 0, 10);
    }
    // Default / Featured: original DOM index
    return parseInt(a.dataset.index || 0, 10) - parseInt(b.dataset.index || 0, 10);
  });
  
  cards.forEach(card => grid.appendChild(card));
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
