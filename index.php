<?php
// Canonical 301 Permanent Redirect: If accessed explicitly via /index.php or /index.html, 301 redirect to root / (GSC Canonical Fix)
if (isset($_SERVER['REQUEST_URI']) && preg_match('#^/index\.(php|html)(\?.*)?$#i', $_SERVER['REQUEST_URI'], $m)) {
    $qs = !empty($m[2]) ? $m[2] : '';
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: /" . $qs);
    exit;
}

header("X-LiteSpeed-Purge: *");
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0, s-maxage=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';

// Dynamic filter categories, countries, and cities from machines
$categories = array_merge(['All Categories'], array_values(array_unique(array_filter(array_column($machines, 'category')))));
$countries = array_merge(['All Countries'], array_values(array_unique(array_filter(array_column($machines, 'country')))));
$cityCounts = array_count_values(array_filter(array_column($machines, 'city')));
arsort($cityCounts);
$cities = array_merge(['All Cities'], array_keys($cityCounts));

// Filter parameters
$catFilter = isset($_GET['category']) ? $_GET['category'] : 'All Categories';
$countryFilter = isset($_GET['country']) ? $_GET['country'] : 'All Countries';
$cityFilter = isset($_GET['city']) ? $_GET['city'] : 'All Cities';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$priceFilter = isset($_GET['price']) ? $_GET['price'] : 'all';
$brandFilter = isset($_GET['brand']) ? trim($_GET['brand']) : 'All Brands';
$yearFilter = isset($_GET['year_range']) ? trim($_GET['year_range']) : 'all';
$hoursFilter = isset($_GET['hours']) ? trim($_GET['hours']) : 'all';
$assuranceFilter = isset($_GET['assurance']) ? trim($_GET['assurance']) : 'all';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'featured';

// Helper function to build dynamic filter URLs preserving parameters
if (!function_exists('buildFilterUrl')) {
function buildFilterUrl($overrides = []) {
    global $catFilter, $countryFilter, $cityFilter, $searchQuery, $priceFilter, $brandFilter, $yearFilter, $hoursFilter, $assuranceFilter, $sortBy;
    $params = [
        'category' => $catFilter,
        'country' => $countryFilter,
        'city' => $cityFilter,
        'search' => $searchQuery,
        'price' => $priceFilter,
        'brand' => $brandFilter,
        'year_range' => $yearFilter,
        'hours' => $hoursFilter,
        'assurance' => $assuranceFilter,
        'sort' => $sortBy
    ];
    $merged = array_merge($params, $overrides);
    $query = [];
    if (!empty($merged['category']) && $merged['category'] !== 'All Categories') $query['category'] = $merged['category'];
    if (!empty($merged['country']) && $merged['country'] !== 'All Countries') $query['country'] = $merged['country'];
    if (!empty($merged['city']) && $merged['city'] !== 'All Cities') $query['city'] = $merged['city'];
    if (!empty($merged['search'])) $query['search'] = $merged['search'];
    if (!empty($merged['price']) && $merged['price'] !== 'all') $query['price'] = $merged['price'];
    if (!empty($merged['brand']) && $merged['brand'] !== 'All Brands') $query['brand'] = $merged['brand'];
    if (!empty($merged['year_range']) && $merged['year_range'] !== 'all') $query['year_range'] = $merged['year_range'];
    if (!empty($merged['hours']) && $merged['hours'] !== 'all') $query['hours'] = $merged['hours'];
    if (!empty($merged['assurance']) && $merged['assurance'] !== 'all') $query['assurance'] = $merged['assurance'];
    if (!empty($merged['sort']) && $merged['sort'] !== 'featured') $query['sort'] = $merged['sort'];
    
    $qs = http_build_query($query);
    return '/' . ($qs ? '?' . $qs : '') . '#catalog';
}
}

// Catalog inventory for live client-side filtering (Separated & Grouped cleanly by Country & City)
$filteredMachines = $machines;
usort($filteredMachines, function($a, $b) {
    $cOrder = [
        'Japan' => 1,
        'China' => 2,
        'Germany' => 3,
        'Taiwan' => 4,
        'Singapore' => 5,
        'Switzerland' => 6,
        'Malaysia' => 7,
        'South Korea' => 8,
        'Italy' => 9
    ];
    $cA = $cOrder[$a['country'] ?? ''] ?? 99;
    $cB = $cOrder[$b['country'] ?? ''] ?? 99;
    if ($cA !== $cB) return $cA - $cB;
    
    $cityComp = strcmp($a['city'] ?? '', $b['city'] ?? '');
    if ($cityComp !== 0) return $cityComp;
    
    return ($a['id'] ?? 0) - ($b['id'] ?? 0);
});
$pageRobots = "index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1";

// SEO Metadata Configuration (Clean Primary Keywords, Enriched OpenGraph & Schema)
$canonicalBase = 'https://joulemachinehub.com';
$canonicalUrl = $canonicalBase . '/';
$pageTitle = "Used Industrial Machinery for Sale | Joule Machine Hub";
$pageDesc = "Buy used and new industrial machinery from verified sellers. Browse CNC machines, power presses, lathes and packaging equipment on Joule Machine Hub.";
$pageKeywords = "used industrial machinery for sale, new industrial machinery, b2b machinery marketplace, industrial equipment for sale, used manufacturing equipment, global machinery marketplace, used cnc machines for sale, power presses for sale, industrial lathes, injection moulding machines";
$ogTitle = $pageTitle;
$ogDesc = $pageDesc;
$ogImage = $canonicalBase . '/assets/images/haas-vmc-1.jpg';
$ogImageAlt = "Joule Machine Hub - Global B2B Industrial Machinery Marketplace";
$ogType = "website";
$pageRobots = "index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1";

// Build Featured Machines ItemList for Google SERP Carousel / Rich List
$homeItemListElements = [];
foreach (array_slice($machines, 0, 8) as $pos => $m) {
    if (empty($m['slug'])) continue;
    $homeItemListElements[] = [
        '@type' => 'ListItem',
        'position' => $pos + 1,
        'name' => ($m['year'] ? $m['year'] . ' ' : '') . ($m['title'] ?? ''),
        'url' => $canonicalBase . '/' . ltrim(getMachineUrl($m), '/'),
        'image' => !empty($m['images'][0]) ? ($canonicalBase . '/' . ltrim($m['images'][0], '/')) : null
    ];
}

$jsonLd = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            '@id' => $canonicalBase . '/#organization',
            'name' => 'Joule Machine Hub',
            'url' => $canonicalBase . '/',
            'logo' => $canonicalBase . '/assets/images/logo-v2.png',
            'image' => $canonicalBase . '/assets/images/haas-vmc-1.jpg',
            'description' => 'Worldwide B2B marketplace for verified used and new industrial machinery directly from plant floors and OEMs worldwide.',
            'foundingDate' => '2018',
            'areaServed' => ['Worldwide', 'Japan', 'China', 'Germany', 'Switzerland', 'Singapore', 'Taiwan', 'India'],
            'knowsAbout' => [
                'Used & New Industrial Machinery',
                'Mechanical Power Presses',
                'Cold Formers & Headers',
                'CNC Machining Centers',
                'Multi-Spindle Lathes',
                'Plastic Injection Molding Machines'
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'email' => 'joulemachinehub@gmail.com',
                'contactType' => 'customer service',
                'availableLanguage' => ['English', 'German', 'Japanese']
            ]
        ],
        [
            '@type' => 'WebSite',
            '@id' => $canonicalBase . '/#website',
            'url' => $canonicalBase . '/',
            'name' => 'Joule Machine Hub',
            'publisher' => ['@id' => $canonicalBase . '/#organization'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $canonicalBase . '/?search={search_term_string}#catalog',
                'query-input' => 'required name=search_term_string'
            ]
        ],
        [
            '@type' => 'ItemList',
            'name' => 'Featured Used / New Industrial Machinery for Sale',
            'numberOfItems' => count($homeItemListElements),
            'itemListElement' => $homeItemListElements
        ],
        [
            '@type' => 'FAQPage',
            '@id' => $canonicalBase . '/#faq',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'How do I submit an enquiry for a machine?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'When you click "Enquire Now" or "Get Best Price", enter your contact details and machine requirements. Your enquiry is forwarded directly to the listing seller so you can discuss machine availability, pricing, and purchase arrangements.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Can I inspect the machine before making a purchase?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Yes. Once an enquiry is submitted, you can connect directly with the listing seller to request operating videos, maintenance logs, and coordinate an on-site physical inspection at the machine location before completing your purchase.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'What is Joule Machine Hub and how does it work?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Joule Machine Hub is an online B2B industrial machinery portal that acts as a mediator connecting machinery buyers with machine owners and sellers worldwide. Sellers list their surplus or new machinery on our portal, and interested buyers connect directly with sellers for pricing and purchase terms.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Does Joule Machine Hub provide machine rigging, transportation, or delivery?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'No. Joule Machine Hub is an online marketplace connecting machinery buyers and sellers. Physical inspection, machinery rigging, transportation, delivery, and payment arrangements are handled independently and directly between the buyer and seller.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Does Joule Machine Hub own or physically inspect the listed equipment?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'No. Machine specifications, photographs, pricing, availability, and location details are provided by the respective listing sellers. Joule Machine Hub operates as an online marketplace connecting machinery buyers and sellers and does not own or physically inspect the listed equipment. Buyers are responsible for independently verifying all machine details with the seller before purchasing.'
                    ]
                ]
            ]
        ]
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. HERO BANNER WITH INTEGRATED SEARCH ENGINE & FILTERS (User Requested) -->
<section class="bg-gradient-to-br from-joule-navy via-joule-blue to-blue-950 text-white relative overflow-hidden border-b-4 border-joule-yellow py-10 sm:py-16">
  
  <!-- Subtle Industrial Grid Backdrop -->
  <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#facc15_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-4 relative z-10 space-y-8">
    
    <!-- Top Hero Header -->
    <div class="text-center max-w-4xl mx-auto space-y-4">
      <div class="inline-flex items-center gap-2 bg-joule-yellow/20 border border-joule-yellow/50 text-joule-yellow px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider shadow-xs">
        <i class="fa-solid fa-bolt text-joule-yellow"></i>
        <span>GLOBAL B2B INDUSTRIAL MACHINERY MARKETPLACE</span>
      </div>

      <h1 class="text-2xl sm:text-4xl md:text-5xl font-black text-white leading-tight tracking-tight">
        Buy Used &amp; New Industrial Machinery from Verified Sellers
      </h1>

      <p class="text-blue-100 text-xs sm:text-base leading-relaxed max-w-2xl mx-auto">
        <strong>Joule Machine Hub</strong> is a premier <strong>global machinery marketplace</strong> connecting qualified buyers with verified machine sellers worldwide. Find <strong>used industrial machinery for sale</strong>, verified <strong>new industrial machinery</strong>, and surplus <strong>used manufacturing equipment</strong> directly from factory floors and certified equipment sellers.
      </p>

      <!-- Clear Hero CTAs (2 buttons in a row on mobile, flex row on desktop) -->
      <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center justify-center gap-2 sm:gap-3 pt-1 max-w-lg sm:max-w-none mx-auto jmh-mobile-grid-2">
        <a href="#catalog" onclick="scrollToCatalog();" class="col-span-2 sm:col-span-1 span-full w-full sm:w-auto px-5 py-2.5 rounded-xl bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black text-xs sm:text-sm uppercase tracking-wider flex items-center justify-center text-center gap-2 shadow-lg shadow-amber-400/20 transition-all hover:scale-105 border border-amber-300 min-h-[44px]">
          <i class="fa-solid fa-boxes-stacked"></i>
          <span>Browse Machinery</span>
        </a>
        <button type="button" onclick="openRequirementModal()" class="col-span-1 col-1 w-full sm:w-auto px-2 sm:px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-[11px] sm:text-sm uppercase tracking-tight sm:tracking-wider flex items-center justify-center text-center gap-1.5 sm:gap-2 shadow-lg shadow-emerald-500/20 transition-all hover:scale-105 border border-emerald-300 cursor-pointer min-h-[44px]">
          <i class="fa-solid fa-file-signature"></i>
          <span>Submit RFQ</span>
        </button>
        <a href="register.php?role=seller" class="col-span-1 col-1 w-full sm:w-auto px-2 sm:px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-black text-[11px] sm:text-sm uppercase tracking-tight sm:tracking-wider flex items-center justify-center text-center gap-1.5 sm:gap-2 border border-white/20 transition-all hover:scale-105 min-h-[44px]">
          <i class="fa-solid fa-store text-amber-400"></i>
          <span>List Machinery</span>
        </a>
      </div>
    </div>

    <!-- MAIN SEARCH ENGINE & MULTI-FILTER BAR (Placed prominently in Hero Section) -->
    <div class="max-w-5xl mx-auto">
      <div class="bg-white rounded-2xl p-3 sm:p-4 shadow-xl border-2 border-joule-yellow">
        
        <form id="heroFilterForm" onsubmit="event.preventDefault(); applyCatalogFilters(); scrollToCatalog();" class="space-y-2.5 sm:space-y-3">
          <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy); ?>">

          <!-- Top Input Row: Responsive 2-Col Mobile / 12-Col Desktop Layout -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-2 sm:gap-2.5 items-center">
            
            <!-- 1. Keyword Search (Full width on mobile, 5 cols on desktop) -->
            <div class="md:col-span-5 h-11 sm:h-12 flex items-center bg-slate-50 border-2 border-slate-200 rounded-xl sm:rounded-2xl px-3.5 focus-within:border-joule-brightblue focus-within:bg-white transition-colors">
              <i class="fa-solid fa-magnifying-glass text-slate-400 mr-2.5 text-sm shrink-0"></i>
              <input 
                type="text" 
                id="heroSearchInput" 
                oninput="syncLiveSearch(this.value)" 
                placeholder="Search used / new machines, CNC, presses, lathes..." 
                class="w-full text-xs sm:text-sm text-slate-900 placeholder-slate-400 bg-transparent focus:outline-none font-semibold"
              >
            </div>

            <!-- 2 & 3. Category & Country Selectors (Stacked on mobile to show full text, side-by-side on tablet/desktop) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:col-span-5 md:gap-2.5">
              
              <!-- Category Selector -->
              <div class="h-11 sm:h-12 flex items-center bg-slate-50 border-2 border-slate-200 rounded-xl sm:rounded-2xl px-3 focus-within:border-joule-brightblue focus-within:bg-white transition-colors">
                <i class="fa-solid fa-gears text-slate-400 mr-2 text-xs sm:text-sm shrink-0"></i>
                <select id="heroCategorySelect" onchange="setLiveCategory(this.value)" class="w-full text-xs font-bold text-slate-800 bg-transparent focus:outline-none cursor-pointer">
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $catFilter === $cat ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($cat); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Country Selector -->
              <div class="h-11 sm:h-12 flex items-center bg-slate-50 border-2 border-slate-200 rounded-xl sm:rounded-2xl px-3 focus-within:border-joule-brightblue focus-within:bg-white transition-colors">
                <i class="fa-solid fa-earth-americas text-slate-400 mr-2 text-xs sm:text-sm shrink-0"></i>
                <select id="heroCountrySelect" onchange="setLiveCountry(this.value)" class="w-full text-xs font-bold text-slate-800 bg-transparent focus:outline-none cursor-pointer">
                  <?php foreach ($countries as $c): ?>
                    <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $countryFilter === $c ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($c); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

            </div>

            <!-- 4. Big Search Button (Full width on mobile, 2 cols on desktop) -->
            <div class="md:col-span-2">
              <button 
                type="button" 
                onclick="applyCatalogFilters(); scrollToCatalog();" 
                class="w-full h-11 sm:h-12 bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black px-4 rounded-xl sm:rounded-2xl text-xs sm:text-sm shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer border border-amber-300"
              >
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Search</span>
              </button>
            </div>

          </div>

          <!-- Quick Budget & Filter Chips Row (Hero Inline Filter - Flex Wrap on Mobile so all text is visible) -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2 border-t border-slate-100 text-xs text-slate-600">
            
            <div class="flex flex-wrap items-center gap-1.5 py-1">
              <span class="font-black text-slate-900 text-[11px] uppercase tracking-wider shrink-0 flex items-center gap-1 mr-0.5">
                <i class="fa-solid fa-filter text-joule-brightblue"></i> Budget:
              </span>
              <?php
                $heroBudgets = [
                  'all' => 'All Budgets',
                  'under10' => 'Under ₹10L',
                  '10to40' => '₹10L - ₹40L',
                  '40to70' => '₹40L - ₹70L',
                  'above70' => 'Above ₹70L'
                ];
                foreach ($heroBudgets as $hbKey => $hbLabel):
              ?>
                <label class="cursor-pointer shrink-0">
                  <input 
                    type="radio" 
                    name="heroBudgetRadio" 
                    value="<?php echo $hbKey; ?>" 
                    onchange="setLiveBudget(this.value)" 
                    <?php echo $hbKey === 'all' ? 'checked' : ''; ?>
                    class="hidden peer"
                  >
                  <span class="px-2.5 py-1 rounded-lg border text-[11px] font-bold peer-checked:bg-joule-blue peer-checked:text-joule-yellow peer-checked:border-joule-blue bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 shrink-0 inline-block">
                    <?php echo $hbLabel; ?>
                  </span>
                </label>
              <?php endforeach; ?>
            </div>

            <div class="flex items-center gap-3 text-[11px] font-medium text-slate-500">
              <span class="flex items-center gap-1 text-emerald-700 font-bold">
                <i class="fa-solid fa-circle-check"></i> 100% Free Direct Inquiry
              </span>
              <span class="hidden sm:inline">•</span>
              <span class="hidden sm:inline"><?php echo count($machines); ?> Verified Units</span>
            </div>

          </div>

        </form>

      </div>

      <!-- Trending Search Tags Under Hero Search Bar -->
      <div class="flex items-center justify-center gap-2 flex-wrap mt-3 text-xs text-blue-200">
        <span class="text-joule-yellow font-black uppercase text-[10px] tracking-wider">Trending:</span>
        <a href="product/used-komatsu-e2m200-mechanical-power-press" class="bg-blue-950/60 hover:bg-blue-900 border border-blue-400/30 px-2.5 py-0.5 rounded-full text-slate-200 hover:text-white text-[11px]">Komatsu 200T Press</a>
        <a href="product/used-amada-pdl-300-mechanical-power-press" class="bg-blue-950/60 hover:bg-blue-900 border border-blue-400/30 px-2.5 py-0.5 rounded-full text-slate-200 hover:text-white text-[11px]">Amada 300T Press</a>
        <a href="category/used-cold-formers-headers" class="bg-blue-950/60 hover:bg-blue-900 border border-blue-400/30 px-2.5 py-0.5 rounded-full text-slate-200 hover:text-white text-[11px]">Parts Former / Cold Header</a>
        <a href="product/used-tornos-bechler-sas-16-6-multi-spindle-automatic-lathe" class="bg-blue-950/60 hover:bg-blue-900 border border-blue-400/30 px-2.5 py-0.5 rounded-full text-slate-200 hover:text-white text-[11px]">Tornos Lathe</a>
        <a href="product/used-dingli-jcpt1412hd-self-propelled-scissor-lift" class="bg-blue-950/60 hover:bg-blue-900 border border-blue-400/30 px-2.5 py-0.5 rounded-full text-slate-200 hover:text-white text-[11px]">Dingli Scissor Lift</a>
      </div>

    </div>

  </div>
</section>

<?php
$countrySidebarCodes = [
  'All Countries' => ['code' => '🌐', 'icon' => 'fa-earth-americas', 'short' => 'All Countries'],
  'Japan' => ['code' => 'JP', 'flag' => '🇯🇵', 'short' => 'Japan'],
  'China' => ['code' => 'CN', 'flag' => '🇨🇳', 'short' => 'China'],
  'Switzerland' => ['code' => 'CH', 'flag' => '🇨🇭', 'short' => 'Switzerland'],
  'Singapore' => ['code' => 'SG', 'flag' => '🇸🇬', 'short' => 'Singapore'],
  'Germany' => ['code' => 'DE', 'flag' => '🇩🇪', 'short' => 'Germany'],
  'United States' => ['code' => 'US', 'flag' => '🇺🇸', 'short' => 'USA'],
  'India' => ['code' => 'IN', 'flag' => '🇮🇳', 'short' => 'India']
];

$topBrandsList = [
  'All Brands' => 'All Brands / OEMs',
  'Amada' => 'Amada Machinery',
  'Komatsu' => 'Komatsu Ltd',
  'Yamada Dobby' => 'Yamada Dobby',
  'Daido' => 'Daido Machinery',
  'Naitoh' => 'Naitoh Machinery',
  'Jyoto' => 'Jyoto / Joto Kikai',
  'Tornos' => 'Tornos Bechler',
  'Dingli' => 'Dingli Lift',
  'Xiaojin' => 'Xiaojin Machinery'
];

$yearRangesList = [
  'all' => 'All Manufacturing Years',
  '2010plus' => '2010 & Newer (Modern)',
  '2000to2009' => '2000 - 2009 (Millennium)',
  '1990to1999' => '1990 - 1999 (High Durability)',
  'pre1990' => 'Pre-1990 (Heavy Duty Vintage)'
];

$budgetsList = [
  'all' => 'All Budgets',
  'under10' => 'Under ₹10 Lakh',
  '10to40' => '₹10 Lakh - ₹40 Lakh',
  '40to70' => '₹40 Lakh - ₹70 Lakh',
  'above70' => 'Above ₹70 Lakh'
];

$hasActiveFilters = ($catFilter !== 'All Categories' || $countryFilter !== 'All Countries' || $cityFilter !== 'All Cities' || !empty($searchQuery) || $priceFilter !== 'all' || $brandFilter !== 'All Brands' || $yearFilter !== 'all');
?>

<!-- 2. MAIN MARKETPLACE SECTION: Machines with Sticky Left Filter Sidebar -->
<main id="catalog" class="max-w-7xl mx-auto px-4 py-8 sm:py-10 pb-20 lg:pb-12 flex-1 w-full scroll-mt-20">
  
  <!-- Section Title & Sort -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h2 class="text-2xl font-black text-slate-950 flex items-center gap-2.5">
        <span>Used Industrial Machinery for Sale &amp; Verified Equipment Catalog</span>
        <span id="catalogCountBadge" class="text-xs bg-joule-yellow text-slate-950 font-black px-2.5 py-0.5 rounded-full border border-amber-300">
          Showing <?php echo count($machines); ?> of <?php echo count($machines); ?> Available Now
        </span>
      </h2>
      <p class="text-xs text-slate-500 mt-1">
        Browse verified industrial equipment for sale across CNC machining centers, power presses, lathes, injection moulding, and heavy manufacturing. Click <strong>"Enquire Now"</strong> to connect directly with verified sellers.
      </p>
    </div>

    <!-- Sort Options (Desktop & Tablet) -->
    <div class="hidden sm:flex items-center gap-2 self-end sm:self-auto text-xs">
      <span class="text-slate-500 font-bold">Sort By:</span>
      <select 
        id="desktopSortSelect" 
        onchange="setLiveSort(this.value);" 
        class="bg-white border-2 border-slate-300 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-700 focus:outline-none focus:border-joule-blue cursor-pointer"
      >
        <option value="featured">Featured / Recommended</option>
        <option value="priceLow">Price: Low to High</option>
        <option value="priceHigh">Price: High to Low</option>
        <option value="yearNew">Mfg Year: Newest First</option>
      </select>
    </div>
  </div>

  <!-- MODERN EASY-USAGE MOBILE FILTER TOOLBAR (< 1024px) -->
  <div class="lg:hidden mb-5 space-y-2.5">
    
    <!-- Top Modern Filter Matrix: Clean, Fast, 1-Tap Access -->
    <div class="bg-white rounded-2xl border border-slate-200 p-2.5 shadow-xs space-y-2">
      
      <!-- Primary Controls Row: Filter Drawer + Sort (Side-by-Side) -->
      <div class="grid grid-cols-2 gap-2">
        <!-- 1. All Filters Drawer Trigger -->
        <button 
          type="button" 
          onclick="openMobileFilterDrawer()" 
          class="h-10 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center justify-between shadow-xs transition-colors cursor-pointer"
        >
          <span class="flex items-center gap-1.5 font-bold whitespace-nowrap">
            <i class="fa-solid fa-sliders text-joule-yellow text-xs"></i>
            <span>All Filters</span>
            <?php if ($hasActiveFilters): ?>
              <span class="w-2 h-2 rounded-full bg-joule-yellow animate-pulse"></span>
            <?php endif; ?>
          </span>
          <span class="text-[10px] font-semibold text-slate-300 bg-white/15 px-2 py-0.5 rounded-full whitespace-nowrap">
            <?php echo count($filteredMachines); ?> Units
          </span>
        </button>

        <!-- 2. Sort Dropdown (Instant Live Filtering) -->
        <div class="h-10 flex items-center bg-slate-50 border border-slate-200 rounded-xl px-2.5 hover:border-slate-300 focus-within:border-joule-brightblue transition-colors">
          <i class="fa-solid fa-arrow-down-wide-short text-slate-400 mr-2 text-xs shrink-0"></i>
          <select 
            id="mobileSortSelect"
            onchange="setLiveSort(this.value);" 
            class="w-full text-xs font-bold text-slate-800 bg-transparent focus:outline-none cursor-pointer"
          >
            <option value="featured" <?php echo $sortBy === 'featured' ? 'selected' : ''; ?>>Sort: Featured</option>
            <option value="priceLow" <?php echo $sortBy === 'priceLow' ? 'selected' : ''; ?>>Sort: Price Low</option>
            <option value="priceHigh" <?php echo $sortBy === 'priceHigh' ? 'selected' : ''; ?>>Sort: Price High</option>
            <option value="yearNew" <?php echo $sortBy === 'yearNew' ? 'selected' : ''; ?>>Sort: Newest</option>
          </select>
        </div>
      </div>

      <!-- Quick Category & Country Selectors (Instant Live Filtering) -->
      <div class="space-y-2">
        
        <!-- 3. Category Selector -->
        <div class="h-10 flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 hover:border-slate-300 focus-within:border-joule-brightblue transition-colors">
          <i class="fa-solid fa-gears text-joule-brightblue mr-2.5 text-xs shrink-0"></i>
          <select 
            id="mobileCategorySelect"
            onchange="setLiveCategory(this.value);" 
            class="w-full text-xs font-bold text-slate-800 bg-transparent focus:outline-none cursor-pointer"
          >
            <?php foreach ($categories as $cat): 
              $catCount = ($cat === 'All Categories') ? count($machines) : count(array_filter($machines, fn($m) => $m['category'] === $cat));
            ?>
              <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $catFilter === $cat ? 'selected' : ''; ?>>
                <?php echo ($cat === 'All Categories') ? 'Category: All Categories' : 'Category: ' . htmlspecialchars($cat); ?> (<?php echo $catCount; ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- 4. Country Selector -->
        <div class="h-10 flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 hover:border-slate-300 focus-within:border-joule-brightblue transition-colors">
          <span class="text-xs mr-2.5 shrink-0"><?php echo ($countryFilter === 'All Countries') ? '🌐' : getCountryFlagHtml($countryFilter, 'w-3.5 h-2.5 inline'); ?></span>
          <select 
            id="mobileCountrySelect"
            onchange="setLiveCountry(this.value);" 
            class="w-full text-xs font-bold text-slate-800 bg-transparent focus:outline-none cursor-pointer"
          >
            <?php foreach ($countries as $c): 
              $cCount = ($c === 'All Countries') ? count($machines) : count(array_filter($machines, fn($m) => isset($m['country']) && $m['country'] === $c));
            ?>
              <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $countryFilter === $c ? 'selected' : ''; ?>>
                <?php echo ($c === 'All Countries') ? 'Country: All Countries' : 'Country: ' . htmlspecialchars($c); ?> (<?php echo $cCount; ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

      </div>

    </div>

    <!-- Active Filter Tags (Appears when any filter is selected) -->
    <?php if ($hasActiveFilters): ?>
      <div class="bg-blue-50/80 border border-blue-200 rounded-xl p-2.5 flex items-center justify-between gap-2 text-xs">
        <div class="flex items-center gap-1.5 flex-wrap">
          <span class="text-[10px] font-black uppercase text-blue-900 tracking-wider flex items-center gap-1">
            <i class="fa-solid fa-filter text-joule-brightblue"></i> Active:
          </span>
          <?php if ($countryFilter !== 'All Countries'): ?>
            <a href="<?php echo buildFilterUrl(['country' => 'All Countries']); ?>" class="inline-flex items-center gap-1 bg-white border border-blue-300 text-blue-900 font-bold px-2 py-0.5 rounded-lg text-[11px] hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 transition-colors">
              <span><?php echo htmlspecialchars($countryFilter); ?></span>
              <i class="fa-solid fa-xmark text-[10px]"></i>
            </a>
          <?php endif; ?>
          <?php if ($catFilter !== 'All Categories'): ?>
            <a href="<?php echo buildFilterUrl(['category' => 'All Categories']); ?>" class="inline-flex items-center gap-1 bg-white border border-blue-300 text-blue-900 font-bold px-2 py-0.5 rounded-lg text-[11px] hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 transition-colors">
              <span><?php echo htmlspecialchars($catFilter); ?></span>
              <i class="fa-solid fa-xmark text-[10px]"></i>
            </a>
          <?php endif; ?>
          <?php if ($brandFilter !== 'All Brands'): ?>
            <a href="<?php echo buildFilterUrl(['brand' => 'All Brands']); ?>" class="inline-flex items-center gap-1 bg-white border border-blue-300 text-blue-900 font-bold px-2 py-0.5 rounded-lg text-[11px] hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 transition-colors">
              <span><?php echo htmlspecialchars($brandFilter); ?></span>
              <i class="fa-solid fa-xmark text-[10px]"></i>
            </a>
          <?php endif; ?>
          <?php if ($priceFilter !== 'all'): ?>
            <a href="<?php echo buildFilterUrl(['price' => 'all']); ?>" class="inline-flex items-center gap-1 bg-white border border-blue-300 text-blue-900 font-bold px-2 py-0.5 rounded-lg text-[11px] hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 transition-colors">
              <span><?php echo htmlspecialchars($priceFilter); ?></span>
              <i class="fa-solid fa-xmark text-[10px]"></i>
            </a>
          <?php endif; ?>
        </div>
        <a href="/#catalog" class="text-rose-700 font-bold text-[11px] hover:underline shrink-0">
          Clear All
        </a>
      </div>
    <?php endif; ?>

  </div>

  </div>

  <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    
    <!-- LEFT FILTER SIDEBAR (Fixed/Sticky on Desktop - Screen-fitting High-Density Layout) -->
    <div class="hidden lg:block lg:col-span-1">
      <aside id="filterSidebar" class="bg-white rounded-2xl border border-slate-200/90 p-3.5 space-y-3.5 shadow-xs sticky top-20 max-h-[calc(100vh-6rem)] overflow-y-auto sidebar-sticky-scroll">
      
      <!-- 1. Sidebar Header & In-Sidebar Search & Active Filters -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-1.5 font-black text-slate-900 text-xs">
            <i class="fa-solid fa-sliders text-joule-brightblue"></i>
            <span class="tracking-wide">FILTER EQUIPMENT</span>
            <span class="bg-slate-100 text-slate-600 font-mono text-[10px] px-1.5 py-0.5 rounded-full font-bold"><?php echo count($filteredMachines); ?></span>
          </div>
          <?php if ($hasActiveFilters): ?>
            <a href="/#catalog" class="text-[11px] text-rose-600 hover:text-rose-700 hover:underline font-bold flex items-center gap-1">
              <i class="fa-solid fa-rotate-left text-[10px]"></i> Reset
            </a>
          <?php endif; ?>
        </div>

        <!-- Compact Search -->
        <form method="GET" action="/#catalog" class="relative">
          <?php if ($catFilter !== 'All Categories'): ?><input type="hidden" name="category" value="<?php echo htmlspecialchars($catFilter); ?>"><?php endif; ?>
          <?php if ($countryFilter !== 'All Countries'): ?><input type="hidden" name="country" value="<?php echo htmlspecialchars($countryFilter); ?>"><?php endif; ?>
          <?php if ($cityFilter !== 'All Cities'): ?><input type="hidden" name="city" value="<?php echo htmlspecialchars($cityFilter); ?>"><?php endif; ?>
          <?php if ($priceFilter !== 'all'): ?><input type="hidden" name="price" value="<?php echo htmlspecialchars($priceFilter); ?>"><?php endif; ?>
          <?php if ($brandFilter !== 'All Brands'): ?><input type="hidden" name="brand" value="<?php echo htmlspecialchars($brandFilter); ?>"><?php endif; ?>
          <?php if ($yearFilter !== 'all'): ?><input type="hidden" name="year_range" value="<?php echo htmlspecialchars($yearFilter); ?>"><?php endif; ?>
          <input 
            type="text" 
            name="search" 
            value="<?php echo htmlspecialchars($searchQuery); ?>" 
            placeholder="Search make, model, city..." 
            class="w-full pl-7 pr-3 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-joule-brightblue focus:bg-white"
          >
          <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-2.5 top-2 text-[10px]"></i>
        </form>

        <!-- Active Filter Chips -->
        <?php if ($hasActiveFilters): ?>
          <div class="flex flex-wrap gap-1 mt-2 pt-2 border-t border-slate-100">
            <?php if ($countryFilter !== 'All Countries'): ?>
              <a href="<?php echo buildFilterUrl(['country' => 'All Countries']); ?>" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-blue-50 text-blue-900 text-[10px] font-bold hover:bg-rose-100 hover:text-rose-700 border border-blue-200">
                <span><?php echo htmlspecialchars($countryFilter); ?></span> <i class="fa-solid fa-xmark text-[9px]"></i>
              </a>
            <?php endif; ?>
            <?php if ($catFilter !== 'All Categories'): ?>
              <a href="<?php echo buildFilterUrl(['category' => 'All Categories']); ?>" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-blue-50 text-blue-900 text-[10px] font-bold hover:bg-rose-100 hover:text-rose-700 border border-blue-200">
                <span><?php echo htmlspecialchars($catFilter); ?></span> <i class="fa-solid fa-xmark text-[9px]"></i>
              </a>
            <?php endif; ?>
            <?php if ($brandFilter !== 'All Brands'): ?>
              <a href="<?php echo buildFilterUrl(['brand' => 'All Brands']); ?>" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-50 text-amber-900 text-[10px] font-bold hover:bg-rose-100 hover:text-rose-700 border border-amber-200">
                <span><?php echo htmlspecialchars($brandFilter); ?></span> <i class="fa-solid fa-xmark text-[9px]"></i>
              </a>
            <?php endif; ?>
            <?php if ($yearFilter !== 'all'): ?>
              <a href="<?php echo buildFilterUrl(['year_range' => 'all']); ?>" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-50 text-amber-900 text-[10px] font-bold hover:bg-rose-100 hover:text-rose-700 border border-amber-200">
                <span><?php echo htmlspecialchars($yearFilter); ?></span> <i class="fa-solid fa-xmark text-[9px]"></i>
              </a>
            <?php endif; ?>
            <?php if ($priceFilter !== 'all'): ?>
              <a href="<?php echo buildFilterUrl(['price' => 'all']); ?>" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-slate-100 text-slate-800 text-[10px] font-bold hover:bg-rose-100 hover:text-rose-700 border border-slate-200">
                <span>Price</span> <i class="fa-solid fa-xmark text-[9px]"></i>
              </a>
            <?php endif; ?>
            <?php if ($cityFilter !== 'All Cities'): ?>
              <a href="<?php echo buildFilterUrl(['city' => 'All Cities']); ?>" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-slate-100 text-slate-800 text-[10px] font-bold hover:bg-rose-100 hover:text-rose-700 border border-slate-200">
                <span><?php echo htmlspecialchars($cityFilter); ?></span> <i class="fa-solid fa-xmark text-[9px]"></i>
              </a>
            <?php endif; ?>
            <?php if (!empty($searchQuery)): ?>
              <a href="<?php echo buildFilterUrl(['search' => '']); ?>" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-slate-100 text-slate-800 text-[10px] font-bold hover:bg-rose-100 hover:text-rose-700 border border-slate-200">
                <span>"<?php echo htmlspecialchars($searchQuery); ?>"</span> <i class="fa-solid fa-xmark text-[9px]"></i>
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- 2. Origin Countries (2-Column Compact Grid) -->
      <div class="border-t border-slate-100 pt-2.5">
        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1.5 flex items-center justify-between">
          <span>ORIGIN COUNTRY</span>
          <i class="fa-solid fa-earth-americas text-blue-600 text-xs"></i>
        </label>
        <div class="grid grid-cols-2 gap-1 text-[11px]">
          <?php foreach ($countries as $c): 
            $cCount = ($c === 'All Countries') ? count($machines) : count(array_filter($machines, fn($m) => isset($m['country']) && $m['country'] === $c));
            $cMeta = $countrySidebarCodes[$c] ?? ['code' => '🌍', 'flag' => '', 'short' => $c];
            $isActive = ($countryFilter === $c);
          ?>
            <a 
              href="<?php echo buildFilterUrl(['country' => $c]); ?>" 
              class="px-2 py-1 rounded-lg font-semibold transition-all flex items-center justify-between <?php echo $isActive ? 'bg-[#071626] text-white font-bold shadow-xs ring-1 ring-[#071626]' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 hover:text-slate-950 border border-slate-200/60'; ?>"
              title="<?php echo htmlspecialchars($c); ?>"
            >
              <span class="flex items-center gap-1 truncate">
                <span class="text-xs flex items-center"><?php echo ($c === 'All Countries') ? '🌐' : getCountryFlagHtml($c, 'w-4 h-3'); ?></span>
                <span class="truncate text-[11px]"><?php echo htmlspecialchars($cMeta['short'] ?? $c); ?></span>
              </span>
              <span class="text-[10px] <?php echo $isActive ? 'text-amber-300 font-bold' : 'text-slate-400 font-mono'; ?>">
                <?php echo $cCount; ?>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 2.5 Manufacturing Cities (2-Column Compact Grid) -->
      <div class="border-t border-slate-100 pt-2.5">
        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1.5 flex items-center justify-between">
          <span class="flex items-center gap-1">
            <i class="fa-solid fa-city text-blue-600 text-xs"></i>
            <span>CITY HUB</span>
          </span>
          <span class="text-[9px] font-bold text-slate-400 font-mono"><?php echo (count($cities) - 1); ?> Hubs</span>
        </label>
        <div class="grid grid-cols-2 gap-1 text-[11px] max-h-44 overflow-y-auto pr-0.5 no-scrollbar" id="sidebarCitiesGrid">
          <?php foreach ($cities as $ct): 
            $ctCount = ($ct === 'All Cities') ? count($machines) : count(array_filter($machines, fn($m) => isset($m['city']) && $m['city'] === $ct));
            $isCtActive = ($cityFilter === $ct);
            $ctMachine = array_values(array_filter($machines, fn($m) => isset($m['city']) && $m['city'] === $ct))[0] ?? null;
            $ctCountry = $ctMachine['country'] ?? 'Japan';
          ?>
            <a 
              href="<?php echo buildFilterUrl(['city' => $ct]); ?>" 
              onclick="event.preventDefault(); setLiveCity('<?php echo addslashes($ct); ?>');"
              data-city="<?php echo htmlspecialchars($ct); ?>"
              class="sidebar-city-btn px-2 py-1 rounded-lg font-semibold transition-all flex items-center justify-between <?php echo $isCtActive ? 'bg-[#071626] text-white font-bold shadow-xs ring-1 ring-[#071626]' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 hover:text-slate-950 border border-slate-200/60'; ?>"
              title="<?php echo htmlspecialchars($ct); ?>"
            >
              <span class="flex items-center gap-1 truncate">
                <span class="text-xs flex items-center"><?php echo ($ct === 'All Cities') ? '🌐' : getCountryFlagHtml($ctCountry, 'w-3.5 h-2.5'); ?></span>
                <span class="truncate text-[10px]"><?php echo htmlspecialchars($ct === 'All Cities' ? 'All Cities' : $ct); ?></span>
              </span>
              <span class="text-[10px] <?php echo $isCtActive ? 'text-amber-300 font-bold' : 'text-slate-400 font-mono'; ?>">
                <?php echo $ctCount; ?>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 3. Machinery Category (Compact List) -->
      <div class="border-t border-slate-100 pt-2.5">
        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1.5 flex items-center justify-between">
          <span>CATEGORY</span>
          <i class="fa-solid fa-layer-group text-slate-400 text-xs"></i>
        </label>
        <div class="space-y-0.5">
          <?php 
            $catCompactLabels = [
              'All Categories' => 'All Machinery',
              'Presses & Stamping' => 'Presses & Stamping',
              'Cold Formers & Headers' => 'Cold Formers & Headers',
              'Lathes & Turning' => 'Lathes & Turning',
              'Lifts & Access Equipment' => 'Lifts & Access Equip.',
              'Filling & Packaging' => 'Filling & Packaging'
            ];
            foreach ($categories as $cat): 
              $catCount = ($cat === 'All Categories') ? count($machines) : count(array_filter($machines, fn($m) => $m['category'] === $cat));
              $isCatActive = ($catFilter === $cat);
            ?>
            <a 
              href="<?php echo buildFilterUrl(['category' => $cat]); ?>" 
              class="w-full text-left px-2 py-1 rounded-lg text-[11px] font-semibold transition-all flex items-center justify-between <?php echo $isCatActive ? 'bg-[#071626] text-white font-bold shadow-xs' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'; ?>"
            >
              <span class="truncate"><?php echo htmlspecialchars($catCompactLabels[$cat] ?? $cat); ?></span>
              <span class="text-[10px] <?php echo $isCatActive ? 'text-amber-300 font-bold' : 'text-slate-400 font-mono'; ?>">
                <?php echo $catCount; ?>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 4. Top Brands (Pill Badges) -->
      <div class="border-t border-slate-100 pt-2.5">
        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1.5 flex items-center justify-between">
          <span>POPULAR OEMS</span>
          <i class="fa-solid fa-award text-amber-500 text-xs"></i>
        </label>
        <div class="flex flex-wrap gap-1">
          <?php 
            $keyBrands = ['All Brands', 'Amada', 'Komatsu', 'Yamada Dobby', 'Daido', 'Naitoh', 'Jyoto', 'Tornos', 'Dingli', 'Xiaojin'];
            foreach ($keyBrands as $bKey):
              $isBActive = ($brandFilter === $bKey);
              $bLabel = ($bKey === 'All Brands') ? 'All OEMs' : $bKey;
          ?>
            <a 
              href="<?php echo buildFilterUrl(['brand' => $bKey]); ?>" 
              class="px-2 py-0.5 rounded text-[10px] font-bold transition-all <?php echo $isBActive ? 'bg-[#071626] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900'; ?>"
            >
              <?php echo htmlspecialchars($bLabel); ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 5. Budget, Year & City Hub (Compact Dropdowns / Selectors) -->
      <div class="border-t border-slate-100 pt-2.5 space-y-2">
        <div class="grid grid-cols-2 gap-2">
          <!-- Year Selector -->
          <div>
            <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">
              Mfg Year
            </label>
            <select 
              onchange="location.href = this.value;" 
              class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-[11px] font-bold text-slate-800 focus:outline-none focus:border-joule-brightblue cursor-pointer"
            >
              <?php foreach ($yearRangesList as $yKey => $yLabel): ?>
                <option value="<?php echo buildFilterUrl(['year_range' => $yKey]); ?>" <?php echo $yearFilter === $yKey ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($yLabel); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- City Hub Selector -->
          <div>
            <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">
              City Hub
            </label>
            <select 
              id="sidebarCitySelect"
              onchange="setLiveCity(this.value);" 
              class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-[11px] font-bold text-slate-800 focus:outline-none focus:border-joule-brightblue cursor-pointer"
            >
              <?php foreach ($cities as $ct): 
                $ctCount = ($ct === 'All Cities') ? count($machines) : count(array_filter($machines, fn($m) => isset($m['city']) && $m['city'] === $ct));
              ?>
                <option value="<?php echo htmlspecialchars($ct); ?>" <?php echo $cityFilter === $ct ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($ct); ?> (<?php echo $ctCount; ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Budget Quick Filter Row -->
        <div class="grid grid-cols-4 gap-1 text-center">
          <?php 
            $budgetCompact = [
              'all' => 'Any',
              'under5' => '< ₹5L',
              '5to15' => '₹5-15L',
              'above30' => '₹30L+'
            ];
            foreach ($budgetCompact as $bKey => $bShort):
              $isBActive = ($priceFilter === $bKey);
          ?>
            <a 
              href="<?php echo buildFilterUrl(['price' => $bKey]); ?>" 
              class="px-1 py-1 rounded text-[10px] font-bold transition-all truncate <?php echo $isBActive ? 'bg-joule-blue text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900'; ?>"
              title="<?php echo htmlspecialchars($budgetsList[$bKey] ?? $bShort); ?>"
            >
              <?php echo $bShort; ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 6. Buyer Direct Assistance & RFQ Callout -->
      <div class="border-t border-slate-100 pt-2.5 space-y-2">
        <div class="bg-gradient-to-br from-slate-900 to-joule-blue text-white p-2.5 rounded-xl shadow-xs space-y-1.5">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-black text-amber-300 uppercase tracking-wide flex items-center gap-1">
              <i class="fa-solid fa-headset"></i> Procurement Desk
            </span>
            <span class="text-[9px] bg-emerald-500/20 text-emerald-300 font-bold px-1.5 py-0.5 rounded-full flex items-center gap-1 border border-emerald-500/30">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Direct
            </span>
          </div>
          <div class="pt-0.5">
            <button type="button" onclick="openRequirementModal()" class="w-full bg-amber-400 hover:bg-amber-300 text-slate-950 font-black py-1.5 px-2 rounded-lg text-[10px] text-center transition-colors flex items-center justify-center gap-1 shadow-xs cursor-pointer">
              <i class="fa-solid fa-file-signature text-[9px]"></i> Submit Machine Inquiry
            </button>
          </div>
        </div>

        <button 
          type="button" 
          onclick="openRequirementModal()" 
          class="w-full bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 font-bold py-1.5 rounded-lg text-[11px] flex items-center justify-center gap-1.5 transition-colors cursor-pointer border border-slate-200"
        >
          <i class="fa-solid fa-plus-circle text-blue-600"></i>
          <span>Can't Find Model? Post RFQ</span>
        </button>
      </div>

      </aside>
    </div>

    <!-- RIGHT PRODUCTS GRID (All 10 Machines) -->
    <div class="lg:col-span-3">

      <!-- CITY HUB QUICK-FILTER TABS (Instant 1-Click Separation by City) -->
      <div class="mb-4 bg-white border border-slate-200 rounded-2xl p-2.5 sm:p-3 shadow-xs">
        <div class="flex items-center justify-between gap-2 mb-2 pb-1.5 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <span class="w-5 h-5 rounded-md bg-blue-100 text-blue-800 flex items-center justify-center text-[10px]">
              <i class="fa-solid fa-location-dot"></i>
            </span>
            <span class="text-xs font-black uppercase tracking-wider text-slate-900">
              Browse Machinery by Verified Industrial City:
            </span>
          </div>
          <span class="text-[11px] text-slate-500 font-semibold hidden md:inline">
            Click any city to view local factory inventory
          </span>
        </div>
        
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 text-xs" id="quickCityTabsBar">
          <?php foreach ($cities as $ct): 
            $ctCount = ($ct === 'All Cities') ? count($machines) : count(array_filter($machines, fn($m) => isset($m['city']) && $m['city'] === $ct));
            $isCtActive = ($cityFilter === $ct);
            $ctMachine = array_values(array_filter($machines, fn($m) => isset($m['city']) && $m['city'] === $ct))[0] ?? null;
            $ctCountry = $ctMachine['country'] ?? 'Japan';
          ?>
            <a 
              href="<?php echo buildFilterUrl(['city' => $ct]); ?>" 
              onclick="event.preventDefault(); setLiveCity('<?php echo addslashes($ct); ?>');"
              data-city="<?php echo htmlspecialchars($ct); ?>"
              class="quick-city-tab shrink-0 px-3 py-1.5 rounded-xl font-bold transition-all flex items-center gap-1.5 border <?php echo $isCtActive ? 'bg-[#071626] text-white border-[#071626] shadow-xs ring-1 ring-amber-400' : 'bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-slate-950 border-slate-200'; ?>"
            >
              <span class="flex items-center"><?php echo ($ct === 'All Cities') ? '🌐' : getCountryFlagHtml($ctCountry, 'w-3.5 h-2.5'); ?></span>
              <span><?php echo htmlspecialchars($ct); ?></span>
              <span class="text-[10px] <?php echo $isCtActive ? 'text-amber-300 font-black' : 'text-slate-400 font-mono'; ?>">(<?php echo $ctCount; ?>)</span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php if (empty($filteredMachines)): ?>
        <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-12 text-center space-y-4 shadow-xs">
          <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-2xl font-bold">
            <i class="fa-solid fa-magnifying-glass"></i>
          </div>
          <h3 class="text-xl font-black text-slate-900">No Machines Found Matching Filters</h3>
          <p class="text-xs text-slate-500 max-w-sm mx-auto">
            Try resetting your search query or view all 10 available machinery listings.
          </p>
          <a href="/#catalog" class="inline-block bg-joule-blue text-joule-yellow font-black text-xs py-2.5 px-6 rounded-xl">
            Reset Filters
          </a>
        </div>
      <?php else: ?>
        <div id="catalogMachinesGrid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <?php foreach ($filteredMachines as $idx => $machine): 
            $topSpecs = !empty($machine['specs']) && is_array($machine['specs']) ? array_slice($machine['specs'], 0, 3) : [];
            $isExtra = ($idx >= 10);
            $mId = htmlspecialchars($machine['id'] ?? '');
            $specsStr = !empty($machine['specs']) && is_array($machine['specs']) ? implode(' ', $machine['specs']) : '';
            $searchBlob = strtolower(($machine['title'] ?? '') . ' ' . ($machine['brand'] ?? '') . ' ' . ($machine['model'] ?? '') . ' ' . ($machine['category'] ?? '') . ' ' . ($machine['city'] ?? '') . ' ' . ($machine['country'] ?? '') . ' ' . $specsStr);
          ?>
            <div 
              class="bg-white rounded-2xl border border-slate-200 hover:border-joule-brightblue catalog-machine-card machine-card-interactive flex flex-col justify-between overflow-hidden group shadow-xs hover:shadow-xl transition-all h-full <?php echo $isExtra ? 'machine-card-extra hidden' : ''; ?>"
              data-id="<?php echo $mId; ?>"
              data-category="<?php echo htmlspecialchars($machine['category'] ?? ''); ?>"
              data-country="<?php echo htmlspecialchars($machine['country'] ?? ''); ?>"
              data-city="<?php echo htmlspecialchars($machine['city'] ?? ''); ?>"
              data-brand="<?php echo htmlspecialchars($machine['brand'] ?? ''); ?>"
              data-year="<?php echo (int)($machine['year'] ?? 0); ?>"
              data-price="<?php echo (float)($machine['price'] ?? 0); ?>"
              data-search="<?php echo htmlspecialchars($searchBlob); ?>"
              data-index="<?php echo $idx; ?>"
            >
              
              <!-- Image Banner with Badges -->
              <div class="relative aspect-[16/10] bg-slate-900 overflow-hidden">
                <a href="<?php echo htmlspecialchars(getMachineUrl($machine)); ?>" class="block w-full h-full" title="View Full Specs for <?php echo htmlspecialchars($machine['title']); ?>">
                  <img 
                    src="<?php echo htmlspecialchars('/' . ltrim($machine['images'][0], '/')); ?>" 
                    alt="<?php echo htmlspecialchars($machine['title']); ?> - Plant Floor in <?php echo htmlspecialchars($machine['city'] . ', ' . $machine['country']); ?>" 
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    loading="lazy"
                    decoding="async"
                  >
                </a>

                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-transparent to-black/30 pointer-events-none"></div>

                <!-- Category & Verified Tag -->
                <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5 z-10 pointer-events-none">
                  
                  <span class="bg-joule-blue text-white text-[10px] font-black px-2.5 py-1 rounded-md shadow-xs">
                    <?php echo htmlspecialchars($machine['category']); ?>
                  </span>
                </div>

                <!-- 5 Inspection Photos Badge -->
                <div class="absolute top-2.5 right-2.5 z-10">
                  <span class="bg-slate-950/85 backdrop-blur-md text-white text-[10px] font-bold px-2 py-1 rounded-md border border-white/20 flex items-center gap-1 shadow-sm">
                    <i class="fa-solid fa-camera text-joule-yellow text-[10px]"></i> <?php echo count($machine['images']); ?> Photos
                  </span>
                </div>

                <!-- Bottom stats over photo -->
                <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between gap-1 text-white text-xs z-10">
                  <span class="bg-slate-900/85 backdrop-blur-md px-2.5 py-1 rounded-md border border-white/20 font-semibold text-[10px] sm:text-[11px] truncate max-w-[58%]">
                    Year: <?php echo $machine['year']; ?> • <?php echo $machine['hours_run']; ?>
                  </span>
                  <span class="bg-emerald-600 font-bold px-2 py-1 rounded-md text-[9px] sm:text-[10px] shrink-0">
                    ✓ Running Order
                  </span>
                </div>
              </div>

              <!-- Card Body Content -->
              <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between space-y-3.5 sm:space-y-4">
                <div>
                  <div class="flex items-center justify-between mb-1">
                    <div class="text-[10px] font-black uppercase tracking-wider text-joule-brightblue flex items-center gap-1.5">
                      <span><?php echo htmlspecialchars($machine['brand']); ?></span>
                      <span class="text-slate-300">•</span>
                      <a href="<?php echo htmlspecialchars(getCategoryUrl($machine['category'])); ?>" class="hover:underline text-slate-500 font-bold"><?php echo htmlspecialchars($machine['category']); ?></a>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-800 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200/90 shadow-2xs shrink-0" title="<?php echo htmlspecialchars($machine['city'] . ', ' . ($machine['country'] ?? 'Worldwide')); ?>">
                      <span><?php echo getCountryFlagHtml($machine['country'] ?? 'Japan', 'w-4 h-3'); ?></span>
                      <span class="font-black text-slate-900"><?php echo htmlspecialchars($machine['city'] ?? ''); ?></span>
                      <span class="text-slate-400 text-[10px]">, <?php echo htmlspecialchars($machine['country'] ?? 'Worldwide'); ?></span>
                    </span>
                  </div>

                  <h3 class="text-base sm:text-lg font-black text-slate-950 group-hover:text-joule-brightblue transition-colors line-clamp-2 min-h-[3rem] sm:min-h-[3.25rem] leading-snug">
                    <a href="<?php echo htmlspecialchars(getMachineUrl($machine)); ?>">
                      <?php echo htmlspecialchars($machine['title']); ?>
                    </a>
                  </h3>

                  <!-- Price -->
                  <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-2xl font-black text-slate-950 machine-price-display" data-price-inr="<?php echo (float)($machine['price'] ?? 0); ?>" data-price-usd="<?php echo (float)($machine['price_usd'] ?? 0); ?>">
                      <?php echo formatMachinePrice($machine); ?>
                    </span>
                    <?php if ((float)($machine['price'] ?? 0) > 0 || (float)($machine['price_usd'] ?? 0) > 0): ?>
                      <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        Negotiable (Ex-Works)
                      </span>
                    <?php else: ?>
                      <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                        Price on Request
                      </span>
                    <?php endif; ?>
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
                    <div class="flex items-center gap-1.5 truncate text-slate-700">
                      <i class="fa-solid fa-location-dot text-rose-500 text-xs shrink-0"></i>
                      <span class="font-bold text-slate-900 shrink-0"><?php echo htmlspecialchars(($machine['city'] ?? '') . ', ' . ($machine['country'] ?? '')); ?></span>
                      <span class="text-slate-300 shrink-0">•</span>
                      <span class="truncate text-slate-600 text-[11px]"><?php echo htmlspecialchars(formatMachineLocation($machine)); ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-1.5 truncate">
                        <i class="fa-solid fa-building text-emerald-600 text-xs"></i>
                        <span class="font-bold text-slate-800 truncate"><?php echo htmlspecialchars($machine['seller']['name'] ?? ($machine['seller_company'] ?? ($machine['brand'] ?? 'Factory Desk'))); ?></span>
                      </div>
                      <span class="text-[10px] bg-amber-50 text-amber-900 border border-amber-200 font-black px-1.5 py-0.5 rounded">
                        ★ <?php echo htmlspecialchars($machine['seller']['rating'] ?? '4.9'); ?>
                      </span>
                    </div>
                  </div>
                </div>

                <!-- CORE ACTION BUTTONS: DIRECT CALL & BEST PRICE -->
                <div class="pt-2 border-t border-slate-100 space-y-2">
                  
                  <!-- DIRECT SELLER INQUIRY (FILL FORM) -->
                  <button 
                    type="button" 
                    onclick="openInquiryModal('<?php echo $mId; ?>')" 
                    class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black py-3 px-4 rounded-xl flex items-center justify-center gap-2.5 shadow-md shadow-emerald-600/30 transition-all text-xs sm:text-sm cursor-pointer"
                  >
                    <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                      <i class="fa-solid fa-file-signature text-white text-xs"></i>
                    </div>
                    <span>Enquire Now (Fill Form)</span>
                  </button>

                  <!-- Secondary Actions & Interactive Utilities -->
                  <div class="grid grid-cols-2 gap-2">
                    <button 
                      type="button" 
                      onclick="openInquiryModal('<?php echo $mId; ?>')" 
                      class="bg-blue-50 hover:bg-blue-100 text-joule-brightblue border border-blue-200 font-bold py-2 px-3 rounded-lg text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
                    >
                      <i class="fa-solid fa-comment-dots"></i>
                      <span>Get Best Price</span>
                    </button>

                    <a 
                      href="<?php echo htmlspecialchars(getMachineUrl($machine)); ?>" 
                      class="bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-300 font-black py-2 px-3 rounded-lg text-xs flex items-center justify-center gap-1.5 transition-all text-center shadow-xs"
                    >
                      <span>Full Specs</span>
                      <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                  </div>

                  <!-- Compare & Share Quick Row -->
                  <div class="flex items-center justify-between pt-1 text-xs">
                    <button 
                      type="button" 
                      data-compare-btn-id="<?php echo $mId; ?>"
                      onclick="toggleCompareMachine('<?php echo $mId; ?>')" 
                      class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:text-joule-blue hover:bg-blue-50 flex items-center gap-1.5 transition-all text-[11px] font-bold cursor-pointer"
                    >
                      <i class="fa-solid fa-code-compare text-blue-500"></i>
                      <span>Compare</span>
                    </button>

                    <button 
                      type="button" 
                      onclick="shareMachine('<?php echo addslashes($machine['title']); ?>', '<?php echo $canonicalUrl . getMachineUrl($machine); ?>')" 
                      class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 flex items-center gap-1.5 transition-all text-[11px] font-bold cursor-pointer"
                      title="Share Machine"
                    >
                      <i class="fa-solid fa-share-nodes text-emerald-600"></i>
                      <span>Share</span>
                    </button>
                  </div>

                </div>

              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if (count($filteredMachines) > 10): ?>
          <!-- VIEW ALL 20 MACHINES CTA BUTTON (User Requested: Showcase 10 machines, 5 rows, others view all) -->
          <div id="viewAllContainer" class="mt-8 text-center bg-gradient-to-r from-blue-50 via-slate-50 to-blue-50 p-6 sm:p-8 rounded-2xl border border-blue-200 shadow-sm">
            <div class="max-w-md mx-auto space-y-3">
              <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-joule-brightblue bg-blue-100 border border-blue-200 px-3 py-1 rounded-full">
                <i class="fa-solid fa-layer-group"></i>
                <span>Showing 10 of <?php echo count($filteredMachines); ?> Machines (5 Rows)</span>
              </span>
              <h3 class="text-base sm:text-lg font-black text-slate-950">
                Want to Explore All Available Equipment?
              </h3>
              <p class="text-xs text-slate-600">
                Browse our complete verified inventory of CNCs, Lathes, Press Brakes, Injection Moulding and Compressors.
              </p>
              <button 
                type="button" 
                id="viewAllBtn" 
                onclick="toggleViewAllMachines()"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-3 bg-joule-blue hover:bg-blue-900 text-white font-black py-3.5 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 text-xs sm:text-sm border-2 border-joule-yellow cursor-pointer group"
              >
                <div class="w-6 h-6 rounded-lg bg-joule-yellow text-slate-950 flex items-center justify-center text-xs font-black group-hover:scale-110 transition-transform">
                  <i id="viewAllIcon" class="fa-solid fa-boxes-stacked"></i>
                </div>
                <span id="viewAllText">View All Machines (<?php echo count($filteredMachines); ?> Available - Load More)</span>
                <i id="viewAllArrow" class="fa-solid fa-chevron-down text-joule-yellow text-xs group-hover:translate-y-0.5 transition-transform"></i>
              </button>
            </div>
          </div>
        <?php endif; ?>

      <?php endif; ?>

      <!-- Seller Banner -->
      <div class="mt-8 bg-gradient-to-r from-joule-blue to-joule-navy text-white rounded-2xl p-5 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-4 border-2 border-joule-yellow shadow-lg">
        <div class="space-y-1 text-center sm:text-left">
          <div class="font-black text-lg text-white flex items-center justify-center sm:justify-start gap-2">
            <i class="fa-solid fa-circle-plus text-joule-yellow"></i>
            Have Surplus, Used, or New Industrial Machinery to Sell?
          </div>
          <p class="text-xs text-blue-200">
            List your new or used CNC, Lathe, Press Brake, or Moulding machinery for free. Receive direct inquiries from 25,000+ verified buyers worldwide.
          </p>
        </div>
        <button 
          onclick="openPostModal()" 
          class="bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black text-xs py-3 px-6 rounded-xl shrink-0 shadow-lg cursor-pointer border border-amber-300"
        >
          Post Machine Listing
        </button>
      </div>

    </div>

  </div>

  <!-- MOBILE BOTTOM-SHEET FILTER DRAWER (< 1024px) -->
  <div id="mobileFilterDrawerModal" class="fixed inset-0 z-50 hidden flex flex-col justify-end sm:justify-center items-center lg:hidden modal-backdrop p-0 sm:p-4">
    <div class="fixed inset-0" onclick="closeMobileFilterDrawer()"></div>
    <div class="relative bg-white rounded-t-2xl sm:rounded-2xl border-t-4 sm:border-2 border-joule-yellow shadow-2xl max-h-[88vh] sm:max-h-[82vh] flex flex-col z-10 w-full sm:max-w-xl animate-in slide-in-from-bottom duration-300 overflow-hidden">
      
      <!-- Mobile Pull Indicator -->
      <div class="w-10 h-1 bg-slate-300 rounded-full mx-auto my-2 sm:hidden shrink-0"></div>

      <!-- Drawer Header -->
      <div class="p-3.5 sm:p-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-joule-blue text-joule-yellow flex items-center justify-center font-bold text-sm">
            <i class="fa-solid fa-sliders"></i>
          </div>
          <div>
            <h3 class="text-sm font-black text-slate-900 leading-tight">Filter Machines</h3>
            <p class="text-[10px] text-slate-500 font-bold"><?php echo count($filteredMachines); ?> of <?php echo count($machines); ?> Verified Machines</p>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <button type="button" onclick="resetAllFilters()" class="text-xs text-rose-600 font-bold px-2.5 py-1 rounded-lg bg-rose-50 border border-rose-200 cursor-pointer hover:bg-rose-100">
            Reset
          </button>
          <button type="button" onclick="closeMobileFilterDrawer()" class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 hover:text-slate-900 flex items-center justify-center font-bold text-sm cursor-pointer">
            ✕
          </button>
        </div>
      </div>

      <!-- Drawer Body -->
      <div class="p-4 overflow-y-auto space-y-4 flex-1">
        
        <!-- Search Input in Drawer -->
        <div>
          <div class="relative">
            <input 
              type="text" 
              id="drawerSearchInput" 
              value="<?php echo htmlspecialchars($searchQuery); ?>" 
              oninput="syncLiveSearch(this.value)" 
              placeholder="Search machinery, model, brand..." 
              class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-joule-brightblue"
            >
            <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-2.5 top-3 text-xs"></i>
          </div>
        </div>

        <!-- Country (Worldwide) -->
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-slate-500 mb-2 flex items-center justify-between">
            <span>COUNTRY (WORLDWIDE)</span>
            <i class="fa-solid fa-earth-americas text-blue-600"></i>
          </label>
          <div class="grid grid-cols-2 gap-1.5">
            <?php foreach ($countries as $c): 
              $cCount = ($c === 'All Countries') ? count($machines) : count(array_filter($machines, fn($m) => isset($m['country']) && $m['country'] === $c));
              $cMeta = $countrySidebarCodes[$c] ?? ['code' => '🌍', 'flag' => ''];
              $isActive = ($countryFilter === $c);
            ?>
              <button 
                type="button" 
                onclick="setLiveCountry('<?php echo addslashes($c); ?>')" 
                data-country="<?php echo htmlspecialchars($c); ?>"
                class="drawer-country-btn px-2.5 py-2 rounded-xl text-xs font-semibold flex items-center justify-between cursor-pointer transition-all <?php echo $isActive ? 'bg-[#071626] text-white font-black' : 'bg-slate-50 border border-slate-200 text-slate-700'; ?>"
              >
                <span class="flex items-center gap-1.5 truncate">
                  <span><?php echo ($c === 'All Countries') ? '🌐' : getCountryFlagHtml($c, 'w-4 h-3'); ?></span>
                  <span class="truncate"><?php echo htmlspecialchars($c); ?></span>
                </span>
                <span class="text-[10px] font-mono opacity-80">(<?php echo $cCount; ?>)</span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Category -->
        <div class="border-t border-slate-100 pt-3">
          <label class="block text-[11px] font-black uppercase tracking-wider text-slate-500 mb-2">
            MACHINERY CATEGORY
          </label>
          <div class="space-y-1">
            <?php foreach ($categories as $cat): 
              $catCount = ($cat === 'All Categories') ? count($machines) : count(array_filter($machines, fn($m) => $m['category'] === $cat));
              $isCatActive = ($catFilter === $cat);
            ?>
              <button 
                type="button" 
                onclick="setLiveCategory('<?php echo addslashes($cat); ?>')" 
                data-category="<?php echo htmlspecialchars($cat); ?>"
                class="drawer-category-btn w-full px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-between cursor-pointer transition-all <?php echo $isCatActive ? 'bg-[#071626] text-white font-black' : 'bg-slate-50 border border-slate-200 text-slate-700'; ?>"
              >
                <span class="truncate"><?php echo htmlspecialchars($cat); ?></span>
                <span class="text-[10px] font-mono opacity-80">(<?php echo $catCount; ?>)</span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Top Brands / OEMs -->
        <div class="border-t border-slate-100 pt-3">
          <label class="block text-[11px] font-black uppercase tracking-wider text-slate-500 mb-2 flex items-center justify-between">
            <span>POPULAR BRANDS / OEMS</span>
            <i class="fa-solid fa-award text-amber-500"></i>
          </label>
          <div class="grid grid-cols-2 gap-1.5 max-h-40 overflow-y-auto">
            <?php foreach ($topBrandsList as $bKey => $bLabel): 
              $bCount = ($bKey === 'All Brands') ? count($machines) : count(array_filter($machines, fn($m) => isset($m['brand']) && str_contains(strtolower($m['brand']), strtolower($bKey))));
              $isBActive = ($brandFilter === $bKey);
            ?>
              <button 
                type="button" 
                onclick="setLiveBrand('<?php echo addslashes($bKey); ?>')" 
                data-brand="<?php echo htmlspecialchars($bKey); ?>"
                class="drawer-brand-btn px-2 py-1.5 rounded-lg text-xs font-semibold flex items-center justify-between cursor-pointer transition-all <?php echo $isBActive ? 'bg-[#071626] text-white font-black' : 'bg-slate-50 border border-slate-200 text-slate-700'; ?>"
              >
                <span class="truncate text-[11px]"><?php echo htmlspecialchars($bLabel); ?></span>
                <span class="text-[10px] font-mono opacity-80">(<?php echo $bCount; ?>)</span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Budget Range -->
        <div class="border-t border-slate-100 pt-3">
          <label class="block text-[11px] font-black uppercase tracking-wider text-slate-500 mb-2">
            BUDGET RANGE
          </label>
          <div class="grid grid-cols-2 gap-1.5">
            <?php foreach ($budgetsList as $bKey => $bLabel): 
              $isBActive = ($priceFilter === $bKey);
            ?>
              <button 
                type="button" 
                onclick="setLiveBudget('<?php echo addslashes($bKey); ?>')" 
                data-budget="<?php echo htmlspecialchars($bKey); ?>"
                class="drawer-budget-btn px-2.5 py-2 rounded-xl text-xs font-semibold flex items-center justify-center text-center cursor-pointer transition-all <?php echo $isBActive ? 'bg-joule-blue text-joule-yellow font-black' : 'bg-slate-50 border border-slate-200 text-slate-700'; ?>"
              >
                <span><?php echo $bLabel; ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Manufacturing Year -->
        <div class="border-t border-slate-100 pt-3">
          <label class="block text-[11px] font-black uppercase tracking-wider text-slate-500 mb-2">
            MANUFACTURING YEAR
          </label>
          <div class="grid grid-cols-2 gap-1.5">
            <?php foreach ($yearRangesList as $yKey => $yLabel): 
              $isYActive = ($yearFilter === $yKey);
            ?>
              <button 
                type="button" 
                onclick="setLiveYear('<?php echo addslashes($yKey); ?>')" 
                data-year="<?php echo htmlspecialchars($yKey); ?>"
                class="drawer-year-btn px-2.5 py-2 rounded-xl text-xs font-semibold flex items-center justify-center text-center cursor-pointer transition-all <?php echo $isYActive ? 'bg-slate-900 text-white font-black' : 'bg-slate-50 border border-slate-200 text-slate-700'; ?>"
              >
                <span><?php echo $yLabel; ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- City Hubs -->
        <div class="border-t border-slate-100 pt-3">
          <label class="block text-[11px] font-black uppercase tracking-wider text-slate-500 mb-2">
            INDUSTRIAL CITY
          </label>
          <div class="grid grid-cols-2 gap-1.5 max-h-48 overflow-y-auto">
            <?php foreach ($cities as $ct): 
              $ctCount = ($ct === 'All Cities') ? count($machines) : count(array_filter($machines, fn($m) => $m['city'] === $ct));
              $isCtActive = ($cityFilter === $ct);
            ?>
              <button 
                type="button" 
                onclick="setLiveCity('<?php echo addslashes($ct); ?>')" 
                data-city="<?php echo htmlspecialchars($ct); ?>"
                class="drawer-city-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold flex items-center justify-between cursor-pointer transition-all <?php echo $isCtActive ? 'bg-slate-900 text-white font-bold' : 'bg-slate-50 border border-slate-200 text-slate-700'; ?>"
              >
                <span class="truncate"><?php echo htmlspecialchars($ct); ?></span>
                <span class="text-[10px] font-mono opacity-80">(<?php echo $ctCount; ?>)</span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

      <!-- Drawer Footer -->
      <div class="p-3 bg-slate-50 border-t border-slate-200 flex items-center gap-2 shrink-0">
        <button 
          type="button" 
          onclick="resetAllFilters()" 
          class="w-1/3 text-center py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 cursor-pointer"
        >
          Reset All
        </button>
        <button 
          type="button" 
          onclick="closeMobileFilterDrawer(); scrollToCatalog();" 
          class="w-2/3 py-2.5 bg-joule-yellow text-slate-950 font-black rounded-xl text-xs shadow-md flex items-center justify-center gap-1.5 cursor-pointer hover:bg-amber-400"
        >
          <span>Show <span id="drawerCountSpan"><?php echo count($filteredMachines); ?></span> Machines</span>
          <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </button>
      </div>

    </div>
  </div>

  <!-- FLOATING MACHINE COMPARISON BAR (Visible when machines selected) -->
  <div id="compareFloatingBar" class="hidden fixed bottom-16 lg:bottom-6 right-4 z-40 bg-slate-950 text-white border-2 border-joule-yellow rounded-2xl shadow-2xl p-3 flex items-center gap-3">
    <div class="flex items-center gap-2">
      <span class="w-7 h-7 rounded-xl bg-joule-yellow text-slate-950 font-black flex items-center justify-center text-xs" id="compareCountBadge">0</span>
      <div class="text-xs">
        <span class="font-black text-white block">Compare Machines</span>
        <span class="text-[10px] text-slate-400">Side-by-Side</span>
      </div>
    </div>
    <button type="button" onclick="openComparisonModal()" class="bg-joule-blue hover:bg-blue-900 text-joule-yellow border border-joule-yellow font-black text-xs px-3 py-1.5 rounded-xl cursor-pointer shadow-sm">
      Compare Now →
    </button>
    <button type="button" onclick="clearComparison()" class="text-slate-400 hover:text-rose-400 text-xs px-1 cursor-pointer">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <!-- MACHINE COMPARISON MODAL -->
  <div id="comparisonModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 modal-backdrop">
    <div class="fixed inset-0" onclick="closeComparisonModal()"></div>
    <div class="relative bg-white rounded-2xl max-w-5xl w-full p-4 sm:p-6 shadow-2xl z-10 max-h-[90vh] flex flex-col border-2 border-joule-yellow overflow-hidden">
      <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-xl bg-joule-blue text-joule-yellow flex items-center justify-center font-bold text-sm">
            <i class="fa-solid fa-code-compare"></i>
          </div>
          <div>
            <h3 class="text-base sm:text-lg font-black text-slate-950">Side-by-Side Machinery Comparison</h3>
            <p class="text-[11px] text-slate-500">Compare verified specs, prices, and direct seller contacts</p>
          </div>
        </div>
        <button type="button" onclick="closeComparisonModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:text-slate-900 flex items-center justify-center font-bold text-sm cursor-pointer">
          ✕
        </button>
      </div>

      <div id="comparisonTableBody" class="py-4 overflow-y-auto flex-1">
        <!-- Injected via JavaScript -->
      </div>

      <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
        <button type="button" onclick="clearComparison()" class="text-rose-600 hover:underline font-bold">
          Clear Comparison
        </button>
        <button type="button" onclick="closeComparisonModal()" class="bg-slate-900 text-white font-bold px-4 py-2 rounded-xl">
          Close
        </button>
      </div>
    </div>
  </div>

</main>

<!-- 2B. BROWSE BY INDUSTRIAL MACHINERY CATEGORY (Homepage SEO Internal Linking) -->
<section id="categories-directory" class="bg-white py-12 border-t-2 border-b-2 border-slate-200">
  <div class="max-w-7xl mx-auto px-4 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-joule-brightblue bg-blue-100/80 border border-blue-200 px-3 py-1 rounded-full mb-1">
          <i class="fa-solid fa-shapes"></i>
          <span>Machinery Taxonomy • 13 Core Sectors</span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-950">
          Browse Used &amp; New Industrial Machinery by Category
        </h2>
        <p class="text-xs sm:text-sm text-slate-600 mt-0.5">
          Explore specialized machinery categories to find verified used industrial machinery for sale, new manufacturing equipment, and surplus plant assets.
        </p>
      </div>
      <a href="category" class="inline-flex items-center gap-2 text-xs font-black text-joule-brightblue hover:text-blue-800 transition-colors self-start sm:self-auto">
        <span>View Full Categories Directory</span>
        <i class="fa-solid fa-arrow-right text-[10px]"></i>
      </a>
    </div>

    <!-- Category Grid: 13 Internal Links with Target Keyword Anchors -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 pt-2">
      <?php foreach ($categoryMap as $cSlug => $cData): ?>
        <a href="category/<?php echo htmlspecialchars($cSlug); ?>" class="group bg-slate-50 hover:bg-blue-50/60 border border-slate-200 hover:border-joule-brightblue p-3.5 rounded-2xl transition-all shadow-2xs hover:shadow-md flex flex-col justify-between">
          <div>
            <div class="flex items-center gap-2.5 mb-2">
              <div class="w-8 h-8 rounded-xl bg-joule-blue/10 text-joule-blue group-hover:bg-joule-blue group-hover:text-joule-yellow flex items-center justify-center text-xs shrink-0 transition-colors">
                <i class="fa-solid <?php echo htmlspecialchars($cData['icon']); ?>"></i>
              </div>
              <h3 class="font-black text-slate-900 text-xs sm:text-sm group-hover:text-joule-brightblue transition-colors line-clamp-1">
                <?php echo htmlspecialchars($cData['h1'] ?? ($cData['name'] . ' for Sale')); ?>
              </h3>
            </div>
            <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed">
              <?php echo htmlspecialchars($cData['tagline']); ?>
            </p>
          </div>
          <div class="flex items-center justify-between pt-3 mt-2 border-t border-slate-200/60 text-[11px]">
            <span class="text-slate-600 font-bold">Explore Listings</span>
            <span class="text-joule-brightblue font-black group-hover:translate-x-1 transition-transform">→</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 2C. MARKETPLACE BUSINESS MODEL & PAID SELLER REGISTRATION PLANS -->
<section id="how-it-works-marketplace" class="bg-gradient-to-br from-slate-950 via-[#071626] to-slate-900 text-white py-14 sm:py-16 border-b-4 border-joule-yellow">
  <div class="max-w-7xl mx-auto px-4 space-y-8">
    <div class="text-center max-w-3xl mx-auto space-y-2.5">
      <div class="inline-flex items-center gap-2 bg-joule-yellow/20 border border-joule-yellow/40 text-joule-yellow px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider">
        <i class="fa-solid fa-handshake"></i>
        <span>Transparent B2B Operation</span>
      </div>
      <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-white leading-tight">
        Marketplace Business Model &amp; Paid Seller Registration Plans
      </h2>
      <p class="text-blue-100 text-xs sm:text-sm leading-relaxed">
        Joule Machine Hub operates as a dedicated B2B mediator connecting verified buyers with industrial machinery sellers worldwide through a transparent, paid listing model.
      </p>
    </div>

    <!-- 5 Workflow Steps -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
      
      <!-- Step 1 -->
      <div class="bg-white/5 border border-white/10 hover:border-joule-yellow/50 rounded-2xl p-4.5 space-y-2.5 backdrop-blur-md transition-all">
        <div class="w-8 h-8 rounded-xl bg-joule-yellow text-slate-950 font-black text-xs flex items-center justify-center">
          1
        </div>
        <h3 class="font-black text-white text-xs sm:text-sm uppercase tracking-wide">Seller Registration</h3>
        <p class="text-slate-300 text-[11px] leading-relaxed">
          Create a verified seller account with legitimate industrial business details, contact information, and plant location.
        </p>
      </div>

      <!-- Step 2 -->
      <div class="bg-white/5 border border-white/10 hover:border-joule-yellow/50 rounded-2xl p-4.5 space-y-2.5 backdrop-blur-md transition-all">
        <div class="w-8 h-8 rounded-xl bg-joule-yellow text-slate-950 font-black text-xs flex items-center justify-center">
          2
        </div>
        <h3 class="font-black text-white text-xs sm:text-sm uppercase tracking-wide">Choose a Plan</h3>
        <p class="text-slate-300 text-[11px] leading-relaxed">
          Select from our transparent paid listing plans tailored for individual machine owners, machinery dealers, and large manufacturers.
        </p>
      </div>

      <!-- Step 3 -->
      <div class="bg-white/5 border border-white/10 hover:border-joule-yellow/50 rounded-2xl p-4.5 space-y-2.5 backdrop-blur-md transition-all">
        <div class="w-8 h-8 rounded-xl bg-joule-yellow text-slate-950 font-black text-xs flex items-center justify-center">
          3
        </div>
        <h3 class="font-black text-white text-xs sm:text-sm uppercase tracking-wide">List Machinery</h3>
        <p class="text-slate-300 text-[11px] leading-relaxed">
          Upload detailed technical specifications, OEM nameplate photographs, manufacturing year, machine condition, and location.
        </p>
      </div>

      <!-- Step 4 -->
      <div class="bg-white/5 border border-white/10 hover:border-joule-yellow/50 rounded-2xl p-4.5 space-y-2.5 backdrop-blur-md transition-all">
        <div class="w-8 h-8 rounded-xl bg-joule-yellow text-slate-950 font-black text-xs flex items-center justify-center">
          4
        </div>
        <h3 class="font-black text-white text-xs sm:text-sm uppercase tracking-wide">Buyer Enquiries</h3>
        <p class="text-slate-300 text-[11px] leading-relaxed">
          Qualified buyers explore machinery listings, review technical specifications, compare models, and submit enquiries or RFQs.
        </p>
      </div>

      <!-- Step 5 -->
      <div class="bg-white/5 border border-white/10 hover:border-joule-yellow/50 rounded-2xl p-4.5 space-y-2.5 backdrop-blur-md transition-all">
        <div class="w-8 h-8 rounded-xl bg-joule-yellow text-slate-950 font-black text-xs flex items-center justify-center">
          5
        </div>
        <h3 class="font-black text-white text-xs sm:text-sm uppercase tracking-wide">Direct Connection</h3>
        <p class="text-slate-300 text-[11px] leading-relaxed">
          We forward buyer leads to the seller. Both parties connect directly to discuss pricing, schedule on-site inspections, and finalize transactions.
        </p>
      </div>

    </div>

    <!-- Seller Registration CTAs (2 buttons in a row on mobile, flex on desktop) -->
    <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center justify-center gap-2 sm:gap-3 pt-3 max-w-lg sm:max-w-none mx-auto jmh-mobile-grid-2">
      <a href="register.php?role=seller" class="col-span-1 col-1 w-full sm:w-auto px-2 sm:px-6 py-3 rounded-xl bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider flex items-center justify-center text-center gap-1.5 sm:gap-2 shadow-lg shadow-amber-400/20 transition-all hover:scale-105 border border-amber-300 min-h-[44px]">
        <i class="fa-solid fa-store"></i>
        <span>Register as Seller</span>
      </a>
      <a href="register.php?role=seller#plans" class="col-span-1 col-1 w-full sm:w-auto px-2 sm:px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider flex items-center justify-center text-center gap-1.5 sm:gap-2 border border-white/20 transition-all hover:scale-105 min-h-[44px]">
        <i class="fa-solid fa-layer-group text-amber-400"></i>
        <span>View Paid Plans</span>
      </a>
      <button type="button" onclick="openRequirementModal()" class="col-span-2 sm:col-span-1 span-full w-full sm:w-auto px-4 sm:px-6 py-3 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 font-bold text-xs uppercase tracking-wider flex items-center justify-center text-center gap-2 border border-emerald-400/30 transition-all hover:scale-105 cursor-pointer min-h-[44px]">
        <i class="fa-solid fa-file-signature text-emerald-400"></i>
        <span>Submit Buyer RFQ</span>
      </button>
    </div>
  </div>
</section>

<!-- 3. TOP CITIES & INDUSTRIAL HUBS WITH SPECIFIC PLACE ICONS (User Requested) -->
<section id="cities" class="bg-slate-100 py-12 border-b-2 border-slate-200">
  <div class="max-w-7xl mx-auto px-4 space-y-6">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-joule-brightblue bg-blue-100/80 border border-blue-200 px-3 py-1 rounded-full mb-1">
          <i class="fa-solid fa-earth-americas"></i>
          <span>Worldwide Industrial Network • All Countries</span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-950">
          Global Machinery Marketplace – Manufacturing Countries &amp; Regional Sourcing Hubs
        </h2>
        <p class="text-xs text-slate-600 mt-0.5">
          Select your manufacturing country or regional hub to explore verified surplus machinery ready for physical inspection or international export.
        </p>
      </div>

      <div class="text-xs text-slate-500 font-bold">
        <span>Click any country or city to view local equipment</span>
      </div>
    </div>

    <!-- Global Countries Quick Filter Bar -->
    <div class="bg-white p-3 rounded-2xl border-2 border-slate-200 shadow-xs flex items-center gap-2 overflow-x-auto pb-3">
      <span class="text-xs font-black uppercase text-slate-400 whitespace-nowrap pl-2 flex items-center gap-1.5">
        <i class="fa-solid fa-earth-americas text-joule-blue"></i>
        <span>Filter by Country:</span>
      </span>
      <?php foreach ($countries as $c): 
        $cVal = ($c === 'All Countries') ? '' : $c;
        $isActive = ($c === 'All Countries' && empty($countryFilter)) || ($countryFilter === $c);
      ?>
        <a 
          href="/<?php echo $cVal ? ('?country=' . urlencode($cVal)) : ''; ?>#catalog" 
          class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-xl border transition-all flex items-center gap-1.5 <?php echo $isActive ? 'bg-joule-blue text-joule-yellow border-joule-blue shadow-sm' : 'bg-slate-50 hover:bg-blue-50 text-slate-800 border-slate-200'; ?>"
        >
          <span><?php echo ($countryFlags[$c] ?? '🌍'); ?></span>
          <span><?php echo htmlspecialchars($c); ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- 1. WORLDWIDE COUNTRY HUBS (7 Manufacturing Powerhouses) -->
    <div id="globalHubsSection" class="space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
          <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-earth-americas text-joule-brightblue"></i>
            <span>Worldwide Industrial Hubs & Countries</span>
          </h3>
          <p class="text-xs text-slate-500 mt-0.5">Click any country to explore its regional manufacturing cities & local factory equipment</p>
        </div>
        <span class="text-xs text-slate-500 font-semibold bg-slate-100 px-3 py-1 rounded-full border border-slate-200 self-start sm:self-auto">
          <?php echo count($countryHubs); ?> Manufacturing Powerhouses
        </span>
      </div>

      <!-- Country Cards Grid (Global Manufacturing Countries With Active Machinery) -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
        <?php foreach ($countryHubs as $cName => $cHub): 
          $cCount = count(array_filter($machines, function($m) use ($cName) {
            return isset($m['country']) && strtolower($m['country']) === strtolower($cName);
          }));
        ?>
          <a 
            href="<?php echo htmlspecialchars(getCountryUrl($cName)); ?>" 
            class="bg-white p-2.5 sm:p-3.5 rounded-xl sm:rounded-2xl border-2 border-slate-200 hover:border-joule-brightblue hover:shadow-xl transition-all group flex flex-col justify-between overflow-hidden shadow-2xs"
          >
            <div>
              <!-- Flag Banner Container with Floating Currency Pill -->
              <div class="w-full aspect-[16/10] bg-gradient-to-br from-slate-50 via-white to-slate-100 rounded-lg sm:rounded-xl overflow-hidden mb-2 sm:mb-3 border border-slate-200/90 flex items-center justify-center p-2 sm:p-3.5 group-hover:border-joule-brightblue transition-all duration-300 relative shadow-2xs">
                <img 
                  src="<?php echo htmlspecialchars('/' . ltrim($cHub['flag'], '/')); ?>" 
                  alt="<?php echo htmlspecialchars($cName); ?> Flag" 
                  class="w-auto h-auto max-w-[85%] max-h-[85%] object-contain drop-shadow-md rounded-xs border border-slate-200/70 group-hover:scale-105 transition-transform duration-300" 
                  loading="lazy"
                >
                <!-- Floating Currency Badge Overlay (Saves Space on Mobile) -->
                <span class="absolute top-1.5 right-1.5 sm:top-2 sm:right-2 text-[8px] sm:text-[9px] bg-slate-950/85 backdrop-blur-xs text-amber-300 font-black px-1.5 sm:px-2 py-0.5 rounded-full border border-white/20 shadow-xs flex items-center gap-1 z-10">
                  <span><?php echo htmlspecialchars($cHub['code']); ?></span>
                  <span class="text-slate-500">•</span>
                  <span><?php echo htmlspecialchars($cHub['symbol'] ?? $cHub['currency']); ?></span>
                </span>
              </div>

              <!-- Country Title (Gets 100% of Width - No Truncation!) -->
              <div class="space-y-0.5">
                <h3 class="font-black text-slate-900 text-xs sm:text-sm group-hover:text-joule-brightblue transition-colors flex items-center gap-1.5 truncate">
                  <span class="truncate"><?php echo htmlspecialchars($cName); ?></span>
                </h3>
                <p class="text-[10px] sm:text-[11px] text-slate-500 font-semibold line-clamp-1 leading-snug" title="<?php echo htmlspecialchars($cHub['tagline']); ?>">
                  <?php echo htmlspecialchars($cHub['tagline']); ?>
                </p>
                <p class="text-[9px] sm:text-[10px] text-joule-blue font-semibold line-clamp-1 flex items-center gap-1" title="<?php echo htmlspecialchars($cHub['specialties'] ?? ''); ?>">
                  <i class="fa-solid fa-industry text-[8px] text-joule-brightblue shrink-0"></i>
                  <span class="truncate"><?php echo htmlspecialchars($cHub['specialties'] ?? ''); ?></span>
                </p>
              </div>
            </div>

            <!-- Card Footer: Live Count & Direct Arrow -->
            <div class="mt-2.5 pt-2 sm:mt-3 sm:pt-2.5 border-t border-slate-100 flex items-center justify-between text-[10px] sm:text-[11px]">
              <span class="font-black text-emerald-700 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                <span><?php echo $cCount; ?> Units Live</span>
              </span>
              <span class="text-slate-400 group-hover:text-joule-brightblue group-hover:translate-x-1 transition-all font-bold flex items-center gap-0.5">
                <span class="hidden sm:inline text-[10px]">Hub</span>
                <i class="fa-solid fa-arrow-right text-[9px]"></i>
              </span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- 4. FAST RFQ LEAD GENERATION DESK (Worldwide Machinery RFQ) -->
<section class="bg-gradient-to-r from-blue-900 via-joule-blue to-slate-900 text-white py-12 border-t-4 border-joule-yellow">
  <div class="max-w-7xl mx-auto px-4">
    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 sm:p-6 lg:p-10 border border-white/20 shadow-2xl">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        
        <div class="lg:col-span-7 space-y-3">
          <span class="bg-joule-yellow text-slate-950 font-black text-[10px] uppercase px-3 py-1 rounded-full">
            Global Machinery RFQ Broadcast
          </span>
          <h2 class="text-2xl sm:text-3xl font-black text-white leading-snug">
            Submit RFQ – Global Industrial Machinery Sourcing Desk
          </h2>
          <p class="text-xs sm:text-sm text-blue-100 leading-relaxed">
            Submit your machinery requirements below. We broadcast your request to verified manufacturing plants, importers and dealers across 50+ countries. Get direct factory price offers with competitive international pricing.
          </p>
          <div class="flex flex-wrap items-center gap-4 text-xs text-slate-300 pt-2">
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-joule-yellow"></i> 100% Free Service</span>
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-joule-yellow"></i> 0% Buyer Commission</span>
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-joule-yellow"></i> GST Verified Plants</span>
          </div>
        </div>

        <div class="lg:col-span-5 bg-white rounded-2xl p-4 sm:p-5 text-slate-800 shadow-xl border-2 border-joule-yellow">
          <h3 class="font-black text-slate-950 text-base mb-1">Instant RFQ Requirement</h3>
          <p class="text-[11px] text-slate-500 mb-3">Verified sellers with matching equipment will send direct price quotations.</p>

          <form onsubmit="submitRequirement(event)" class="space-y-3 text-xs">
            <div>
              <input type="text" required placeholder="Machine Needed (e.g. 200T Moulding, Haas VMC)" class="w-full px-3 py-2.5 border-2 border-slate-300 rounded-xl focus:outline-none focus:border-joule-blue text-xs font-semibold">
            </div>
            <div class="grid grid-cols-2 gap-2">
              <input type="text" required placeholder="Target Budget (e.g. ₹15 Lakh)" class="w-full px-3 py-2 border-2 border-slate-300 rounded-xl focus:outline-none focus:border-joule-blue text-xs font-semibold">
              <input type="text" placeholder="Your City (e.g. Pune)" class="w-full px-3 py-2 border-2 border-slate-300 rounded-xl focus:outline-none focus:border-joule-blue text-xs font-semibold">
            </div>
            <div>
              <input type="tel" required placeholder="Your Contact Number for Quotation *" class="w-full px-3 py-2.5 border-2 border-slate-300 rounded-xl focus:outline-none focus:border-joule-blue text-xs font-black text-slate-900">
            </div>
            <button type="submit" class="w-full bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black py-3 rounded-xl text-xs sm:text-sm shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer border border-amber-300">
              <i class="fa-solid fa-paper-plane"></i>
              <span>Receive Direct Factory Quotes</span>
            </button>
          </form>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- 5. FREQUENTLY ASKED QUESTIONS (Simple Accordion Design) -->
<section id="faqs" class="bg-slate-50 py-12 sm:py-16 border-t border-slate-200 scroll-mt-20">
  <div class="max-w-4xl mx-auto px-4 space-y-8">
    
    <div class="text-center space-y-2">
      <div class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-joule-brightblue bg-blue-100/80 border border-blue-200 px-3 py-1 rounded-full">
        <i class="fa-solid fa-circle-question"></i>
        <span>Common Questions</span>
      </div>
      <h2 class="text-2xl sm:text-3xl font-black text-slate-950">
        Frequently Asked Questions – Buying &amp; Selling Industrial Equipment
      </h2>
      <p class="text-xs sm:text-sm text-slate-500 max-w-lg mx-auto">
        Everything you need to know about buying and selling used and new industrial machinery through our verified B2B portal.
      </p>
    </div>

    <!-- Accordion Items Container -->
    <div class="space-y-3">

      <!-- FAQ 1 -->
      <div class="bg-white rounded-2xl border-2 border-slate-200 hover:border-joule-brightblue transition-all overflow-hidden shadow-xs">
        <button 
          type="button" 
          onclick="toggleFaq(1)" 
          class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 font-black text-slate-900 text-xs sm:text-sm cursor-pointer"
        >
          <span class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs shrink-0 font-bold">1</span>
            <span>How do I submit an enquiry for a machine?</span>
          </span>
          <span id="faq-icon-1" class="w-7 h-7 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-xs shrink-0 transition-transform duration-200 rotate-180">
            <i class="fa-solid fa-chevron-down"></i>
          </span>
        </button>
        <div id="faq-ans-1" class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 leading-relaxed border-t border-slate-100">
          <p class="mt-2.5">
            When you click <strong>"Enquire Now"</strong> or <strong>"Get Best Price"</strong>, simply enter your contact details and machine requirements. Your enquiry is forwarded directly to the listing seller so you can discuss machine availability, pricing, and purchase arrangements.
          </p>
        </div>
      </div>

      <!-- FAQ 2 -->
      <div class="bg-white rounded-2xl border-2 border-slate-200 hover:border-joule-brightblue transition-all overflow-hidden shadow-xs">
        <button 
          type="button" 
          onclick="toggleFaq(2)" 
          class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 font-black text-slate-900 text-xs sm:text-sm cursor-pointer"
        >
          <span class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs shrink-0 font-bold">2</span>
            <span>Can I inspect the machine before making a purchase?</span>
          </span>
          <span id="faq-icon-2" class="w-7 h-7 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-xs shrink-0 transition-transform duration-200">
            <i class="fa-solid fa-chevron-down"></i>
          </span>
        </button>
        <div id="faq-ans-2" class="hidden px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 leading-relaxed border-t border-slate-100">
          <p class="mt-2.5">
            Yes. Once an enquiry is submitted, you can connect directly with the listing seller to request operating videos, maintenance logs, and coordinate an on-site physical inspection at the machine's current location before completing your purchase.
          </p>
        </div>
      </div>

      <!-- FAQ 3 -->
      <div class="bg-white rounded-2xl border-2 border-slate-200 hover:border-joule-brightblue transition-all overflow-hidden shadow-xs">
        <button 
          type="button" 
          onclick="toggleFaq(3)" 
          class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 font-black text-slate-900 text-xs sm:text-sm cursor-pointer"
        >
          <span class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs shrink-0 font-bold">3</span>
            <span>What is Joule Machine Hub and how does it work?</span>
          </span>
          <span id="faq-icon-3" class="w-7 h-7 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-xs shrink-0 transition-transform duration-200">
            <i class="fa-solid fa-chevron-down"></i>
          </span>
        </button>
        <div id="faq-ans-3" class="hidden px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 leading-relaxed border-t border-slate-100">
          <p class="mt-2.5">
            Joule Machine Hub is an online B2B industrial machinery portal that acts as a mediator connecting machinery buyers with machine owners and sellers worldwide. Sellers list their surplus, used, or new machinery on our portal, and interested buyers can browse machine specifications and connect directly with sellers for pricing and purchase terms.
          </p>
        </div>
      </div>

      <!-- FAQ 4 -->
      <div class="bg-white rounded-2xl border-2 border-slate-200 hover:border-joule-brightblue transition-all overflow-hidden shadow-xs">
        <button 
          type="button" 
          onclick="toggleFaq(4)" 
          class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 font-black text-slate-900 text-xs sm:text-sm cursor-pointer"
        >
          <span class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs shrink-0 font-bold">4</span>
            <span>How are machine listings added to Joule Machine Hub?</span>
          </span>
          <span id="faq-icon-4" class="w-7 h-7 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-xs shrink-0 transition-transform duration-200">
            <i class="fa-solid fa-chevron-down"></i>
          </span>
        </button>
        <div id="faq-ans-4" class="hidden px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 leading-relaxed border-t border-slate-100">
          <p class="mt-2.5">
            Machine owners and sellers register their machines with detailed technical specifications, manufacturer details, photographs, and current machine location. Once submitted, listings are published on our marketplace for industrial buyers across India and internationally.
          </p>
        </div>
      </div>

      <!-- FAQ 5 -->
      <div class="bg-white rounded-2xl border-2 border-slate-200 hover:border-joule-brightblue transition-all overflow-hidden shadow-xs">
        <button 
          type="button" 
          onclick="toggleFaq(5)" 
          class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 font-black text-slate-900 text-xs sm:text-sm cursor-pointer"
        >
          <span class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs shrink-0 font-bold">5</span>
            <span>Does Joule Machine Hub provide machine rigging, transportation, or delivery?</span>
          </span>
          <span id="faq-icon-5" class="w-7 h-7 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-xs shrink-0 transition-transform duration-200">
            <i class="fa-solid fa-chevron-down"></i>
          </span>
        </button>
        <div id="faq-ans-5" class="hidden px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 leading-relaxed border-t border-slate-100">
          <p class="mt-2.5">
            No. Joule Machine Hub is an online machinery marketplace that connects buyers and sellers. Physical inspection, machinery rigging, transportation, delivery, and payment arrangements are handled independently and directly between the buyer and seller.
          </p>
        </div>
      </div>

      <!-- FAQ 6 -->
      <div class="bg-white rounded-2xl border-2 border-slate-200 hover:border-joule-brightblue transition-all overflow-hidden shadow-xs">
        <button 
          type="button" 
          onclick="toggleFaq(6)" 
          class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 font-black text-slate-900 text-xs sm:text-sm cursor-pointer"
        >
          <span class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs shrink-0 font-bold">6</span>
            <span>How can a machine owner list equipment for sale?</span>
          </span>
          <span id="faq-icon-6" class="w-7 h-7 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-xs shrink-0 transition-transform duration-200">
            <i class="fa-solid fa-chevron-down"></i>
          </span>
        </button>
        <div id="faq-ans-6" class="hidden px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 leading-relaxed border-t border-slate-100">
          <p class="mt-2.5">
            Machine owners can click the yellow <strong>"Sell"</strong> button in the navigation header, select an appropriate seller plan, and enter their machine details (brand, model, manufacturing year, technical parameters, location, and photos). Once listed, buyers can discover and enquire about your machine directly.
          </p>
        </div>
      </div>

      <!-- FAQ 7 -->
      <div class="bg-white rounded-2xl border-2 border-slate-200 hover:border-joule-brightblue transition-all overflow-hidden shadow-xs">
        <button 
          type="button" 
          onclick="toggleFaq(7)" 
          class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 font-black text-slate-900 text-xs sm:text-sm cursor-pointer"
        >
          <span class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs shrink-0 font-bold">7</span>
            <span>Does Joule Machine Hub own or physically inspect the listed equipment?</span>
          </span>
          <span id="faq-icon-7" class="w-7 h-7 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-xs shrink-0 transition-transform duration-200">
            <i class="fa-solid fa-chevron-down"></i>
          </span>
        </button>
        <div id="faq-ans-7" class="hidden px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 leading-relaxed border-t border-slate-100">
          <p class="mt-2.5">
            No. Machine specifications, photographs, pricing, availability, and location details are provided by the respective listing sellers. Joule Machine Hub operates as an online marketplace connecting machinery buyers and sellers and does not own or physically inspect the listed equipment. Buyers are responsible for independently verifying machine condition, specifications, seller credentials, commercial terms, and location before completing a transaction.
          </p>
        </div>
      </div>

    </div>

    <!-- Quick Help Contact Banner inside FAQ -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 sm:p-6 text-center space-y-2">
      <h3 class="font-black text-slate-900 text-sm sm:text-base">Have More Questions?</h3>
      <p class="text-xs text-slate-600 max-w-md mx-auto">
        Connect with our B2B mediator portal to submit your machinery requirements or post an RFQ.
      </p>
      <div class="grid grid-cols-2 sm:flex sm:items-center sm:justify-center gap-2 sm:gap-3 pt-1 max-w-sm sm:max-w-none mx-auto jmh-mobile-grid-2">
        <button type="button" onclick="openRequirementModal()" class="col-span-1 col-1 w-full sm:w-auto bg-joule-blue text-joule-yellow hover:bg-blue-900 font-black text-xs px-2 sm:px-4 py-2.5 rounded-xl flex items-center justify-center text-center gap-1.5 shadow-sm transition-colors cursor-pointer min-h-[40px]">
          <i class="fa-solid fa-file-signature"></i>
          <span>Instant RFQ</span>
        </button>
        <button type="button" onclick="openRequirementModal()" class="col-span-1 col-1 w-full sm:w-auto bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 font-bold text-xs px-2 sm:px-4 py-2.5 rounded-xl flex items-center justify-center text-center gap-1.5 transition-colors cursor-pointer min-h-[40px]">
          <span>Submit Query</span>
        </button>
      </div>
    </div>

  </div>
</section>

<!-- ==================== LIVE CLIENT-SIDE INSTANT FILTER ENGINE ==================== -->
<!-- High-performance instant mobile & desktop filtering (0ms latency, zero page reloads) -->
<script>
(function() {
  window.viewAllExpanded = false;

  const liveState = {
    search: <?php echo json_encode($searchQuery); ?>,
    category: <?php echo json_encode($catFilter); ?>,
    country: <?php echo json_encode($countryFilter); ?>,
    city: <?php echo json_encode($cityFilter); ?>,
    brand: <?php echo json_encode($brandFilter); ?>,
    year: <?php echo json_encode($yearFilter); ?>,
    budget: <?php echo json_encode($priceFilter); ?>,
    sort: <?php echo json_encode($sortBy); ?>
  };

  // Safe global drawer open/close
  window.openMobileFilterDrawer = function() {
    const modal = document.getElementById('mobileFilterDrawerModal');
    if (modal) {
      modal.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }
  };

  window.closeMobileFilterDrawer = function() {
    const modal = document.getElementById('mobileFilterDrawerModal');
    if (modal) {
      modal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }
  };

  function getCards() {
    const grid = document.getElementById("catalogMachinesGrid");
    return grid ? Array.from(grid.querySelectorAll(".catalog-machine-card")) : [];
  }

  window.syncLiveSearch = function(val) {
    liveState.search = (val || "").trim().toLowerCase();
    const s1 = document.getElementById("heroSearchInput");
    const s2 = document.getElementById("sidebarSearchInput");
    const s3 = document.getElementById("drawerSearchInput");
    if (s1 && s1.value !== val) s1.value = val;
    if (s2 && s2.value !== val) s2.value = val;
    if (s3 && s3.value !== val) s3.value = val;
    applyCatalogFilters();
  };

  window.setLiveCategory = function(cat) {
    liveState.category = cat || "All Categories";
    const c1 = document.getElementById("heroCategorySelect");
    const c2 = document.getElementById("mobileCategorySelect");
    if (c1 && c1.value !== cat) c1.value = cat;
    if (c2 && c2.value !== cat) c2.value = cat;
    applyCatalogFilters();
  };

  window.setLiveCountry = function(c) {
    liveState.country = c || "All Countries";
    const c1 = document.getElementById("heroCountrySelect");
    const c2 = document.getElementById("mobileCountrySelect");
    if (c1 && c1.value !== c) c1.value = c;
    if (c2 && c2.value !== c) c2.value = c;
    applyCatalogFilters();
  };

  window.setLiveCity = function(ct) {
    liveState.city = ct || "All Cities";
    const ctSelect = document.getElementById("sidebarCitySelect");
    if (ctSelect && ctSelect.value !== liveState.city) ctSelect.value = liveState.city;
    applyCatalogFilters();
  };

  window.setLiveBrand = function(brand) {
    liveState.brand = brand || "All Brands";
    applyCatalogFilters();
  };

  window.setLiveYear = function(year) {
    liveState.year = year || "all";
    const y1 = document.getElementById("sidebarYearSelect");
    if (y1 && y1.value !== year) y1.value = year;
    applyCatalogFilters();
  };

  window.setLiveBudget = function(budget) {
    liveState.budget = budget || "all";
    const b1 = document.getElementById("sidebarBudgetSelect");
    if (b1 && b1.value !== budget) b1.value = budget;
    const radios = document.querySelectorAll("input[name='heroBudgetRadio']");
    radios.forEach(r => { r.checked = (r.value === budget); });
    applyCatalogFilters();
  };

  window.setLiveSort = function(sort) {
    liveState.sort = sort || "featured";
    const s1 = document.getElementById("desktopSortSelect");
    const s2 = document.getElementById("mobileSortSelect");
    if (s1 && s1.value !== sort) s1.value = sort;
    if (s2 && s2.value !== sort) s2.value = sort;
    applyCatalogFilters();
  };

  window.resetAllFilters = function() {
    liveState.search = "";
    liveState.category = "All Categories";
    liveState.country = "All Countries";
    liveState.city = "All Cities";
    liveState.brand = "All Brands";
    liveState.year = "all";
    liveState.budget = "all";
    liveState.sort = "featured";

    const s1 = document.getElementById("heroSearchInput"); if (s1) s1.value = "";
    const s2 = document.getElementById("sidebarSearchInput"); if (s2) s2.value = "";
    const s3 = document.getElementById("drawerSearchInput"); if (s3) s3.value = "";

    const c1 = document.getElementById("heroCategorySelect"); if (c1) c1.value = "All Categories";
    const c2 = document.getElementById("mobileCategorySelect"); if (c2) c2.value = "All Categories";

    const co1 = document.getElementById("heroCountrySelect"); if (co1) co1.value = "All Countries";
    const co2 = document.getElementById("mobileCountrySelect"); if (co2) co2.value = "All Countries";

    const y1 = document.getElementById("sidebarYearSelect"); if (y1) y1.value = "all";
    const b1 = document.getElementById("sidebarBudgetSelect"); if (b1) b1.value = "all";
    const ctSelect = document.getElementById("sidebarCitySelect"); if (ctSelect) ctSelect.value = "All Cities";

    const sDesk = document.getElementById("desktopSortSelect"); if (sDesk) sDesk.value = "featured";
    const sMob = document.getElementById("mobileSortSelect"); if (sMob) sMob.value = "featured";

    const radios = document.querySelectorAll("input[name='heroBudgetRadio']");
    radios.forEach(r => { r.checked = (r.value === "all"); });

    applyCatalogFilters();
  };

  window.scrollToCatalog = function() {
    const el = document.getElementById("catalog");
    if (el) el.scrollIntoView({ behavior: "smooth", block: "start" });
  };

  window.toggleViewAllMachines = function() {
    window.viewAllExpanded = !window.viewAllExpanded;
    applyCatalogFilters();
    const btnText = document.getElementById('viewAllText');
    const btnArrow = document.getElementById('viewAllArrow');
    const totalCount = getCards().length;
    if (btnText) {
      btnText.innerText = window.viewAllExpanded ? "Show Less (Show 10 Machines Only)" : "View All Machines (" + totalCount + " Available - Load More)";
    }
    if (btnArrow) {
      if (window.viewAllExpanded) {
        btnArrow.classList.remove('fa-chevron-down');
        btnArrow.classList.add('fa-chevron-up');
      } else {
        btnArrow.classList.remove('fa-chevron-up');
        btnArrow.classList.add('fa-chevron-down');
      }
    }
  };

  window.applyCatalogFilters = function() {
    const cards = getCards();
    if (!cards.length) return;

    let visibleCount = 0;
    const totalCount = cards.length;

    // Determine if any active filter is applied
    const hasActive = (
      liveState.search !== "" ||
      (liveState.category !== "All Categories" && liveState.category !== "") ||
      (liveState.country !== "All Countries" && liveState.country !== "") ||
      (liveState.city !== "All Cities" && liveState.city !== "") ||
      (liveState.brand !== "All Brands" && liveState.brand !== "") ||
      (liveState.year !== "all" && liveState.year !== "") ||
      (liveState.budget !== "all" && liveState.budget !== "")
    );

    cards.forEach((card, idx) => {
      let match = true;

      // 1. Keyword search
      if (liveState.search) {
        const text = (card.dataset.search || "").toLowerCase();
        if (!text.includes(liveState.search)) match = false;
      }

      // 2. Category
      if (match && liveState.category && liveState.category !== "All Categories") {
        const cat = (card.dataset.category || "").toLowerCase();
        if (cat !== liveState.category.toLowerCase()) match = false;
      }

      // 3. Country
      if (match && liveState.country && liveState.country !== "All Countries") {
        const c = (card.dataset.country || "").toLowerCase();
        if (c !== liveState.country.toLowerCase()) match = false;
      }

      // 4. City
      if (match && liveState.city && liveState.city !== "All Cities") {
        const ct = (card.dataset.city || "").toLowerCase();
        if (ct !== liveState.city.toLowerCase()) match = false;
      }

      // 5. Brand
      if (match && liveState.brand && liveState.brand !== "All Brands") {
        const b = (card.dataset.brand || "").toLowerCase();
        if (!b.includes(liveState.brand.toLowerCase())) match = false;
      }

      // 6. Year Range
      if (match && liveState.year && liveState.year !== "all") {
        const y = parseInt(card.dataset.year || 0, 10);
        if (liveState.year === "2010plus" && y < 2010) match = false;
        else if (liveState.year === "2000to2009" && (y < 2000 || y > 2009)) match = false;
        else if (liveState.year === "1990to1999" && (y < 1990 || y > 1999)) match = false;
        else if (liveState.year === "pre1990" && y >= 1990) match = false;
      }

      // 7. Budget Range
      if (match && liveState.budget && liveState.budget !== "all") {
        const price = parseFloat(card.dataset.price || 0);
        if (liveState.budget === "under10" && price >= 1000000) match = false;
        else if (liveState.budget === "10to40" && (price < 1000000 || price > 4000000)) match = false;
        else if (liveState.budget === "40to70" && (price < 4000000 || price > 7000000)) match = false;
        else if (liveState.budget === "above70" && price <= 7000000) match = false;
      }

      if (match) {
        if (!hasActive && !window.viewAllExpanded && idx >= 10) {
          card.classList.add("hidden");
          card.style.display = "";
        } else {
          card.classList.remove("hidden");
          card.style.display = "";
          visibleCount++;
        }
      } else {
        card.classList.add("hidden");
        card.style.display = "none";
      }
    });

    // Toggle View All container
    const viewAllCont = document.getElementById("viewAllContainer");
    if (viewAllCont) {
      if (hasActive || window.viewAllExpanded) {
        viewAllCont.classList.add("hidden");
      } else {
        viewAllCont.classList.remove("hidden");
      }
    }

    // Sort visible cards inside container
    const grid = document.getElementById("catalogMachinesGrid");
    if (grid) {
      const sorted = cards.slice().sort((a, b) => {
        if (liveState.sort === "priceLow") {
          return parseFloat(a.dataset.price || 0) - parseFloat(b.dataset.price || 0);
        } else if (liveState.sort === "priceHigh") {
          return parseFloat(b.dataset.price || 0) - parseFloat(a.dataset.price || 0);
        } else if (liveState.sort === "yearNew") {
          return parseInt(b.dataset.year || 0, 10) - parseInt(a.dataset.year || 0, 10);
        }
        return parseInt(a.dataset.index || 0, 10) - parseInt(b.dataset.index || 0, 10);
      });
      sorted.forEach(c => grid.appendChild(c));
    }

    // Update Counters
    const headerBadge = document.getElementById("catalogCountBadge");
    if (headerBadge) {
      headerBadge.textContent = "Showing " + visibleCount + " of " + totalCount + " Available Now";
    }

    const sideBadge = document.getElementById("sidebarCountBadge");
    if (sideBadge) sideBadge.textContent = visibleCount;

    const mobBadge = document.getElementById("mobileCountBadge");
    if (mobBadge) mobBadge.textContent = visibleCount + " Units";

    const drawSpan = document.getElementById("drawerCountSpan");
    if (drawSpan) drawSpan.textContent = visibleCount;

    // Show / Hide No Results
    const noRes = document.getElementById("catalogNoResults");
    if (noRes) {
      if (visibleCount === 0) noRes.classList.remove("hidden");
      else noRes.classList.add("hidden");
    }

    // Update Active Filter Badges & Reset Button
    const resetBtn = document.getElementById("sidebarResetBtn");
    if (resetBtn) {
      if (hasActive) resetBtn.classList.remove("hidden");
      else resetBtn.classList.add("hidden");
    }

    const mobDot = document.getElementById("mobileFilterDot");
    if (mobDot) {
      if (hasActive) mobDot.classList.remove("hidden");
      else mobDot.classList.add("hidden");
    }

    // Update Sidebar Active Chips Container
    const chipsCont = document.getElementById("activeFilterChipsContainer");
    if (chipsCont) {
      chipsCont.innerHTML = "";
      if (hasActive) {
        chipsCont.classList.remove("hidden");
        if (liveState.country !== "All Countries") {
          chipsCont.appendChild(createChip(liveState.country, () => setLiveCountry("All Countries")));
        }
        if (liveState.category !== "All Categories") {
          chipsCont.appendChild(createChip(liveState.category, () => setLiveCategory("All Categories")));
        }
        if (liveState.city !== "All Cities") {
          chipsCont.appendChild(createChip(liveState.city, () => setLiveCity("All Cities")));
        }
        if (liveState.brand !== "All Brands") {
          chipsCont.appendChild(createChip(liveState.brand, () => setLiveBrand("All Brands")));
        }
        if (liveState.budget !== "all") {
          chipsCont.appendChild(createChip("Budget: " + liveState.budget, () => setLiveBudget("all")));
        }
        if (liveState.year !== "all") {
          chipsCont.appendChild(createChip("Year: " + liveState.year, () => setLiveYear("all")));
        }
        if (liveState.search !== "") {
          chipsCont.appendChild(createChip('"' + liveState.search + '"', () => syncLiveSearch("")));
        }
      } else {
        chipsCont.classList.add("hidden");
      }
    }

    // Update Mobile Active Tags Bar
    const mobTagsBar = document.getElementById("mobileActiveTagsBar");
    const mobTagsList = document.getElementById("mobileActiveTagsList");
    if (mobTagsBar && mobTagsList) {
      mobTagsList.innerHTML = "";
      if (hasActive) {
        mobTagsBar.classList.remove("hidden");
        if (liveState.country !== "All Countries") {
          mobTagsList.appendChild(createChip(liveState.country, () => setLiveCountry("All Countries")));
        }
        if (liveState.category !== "All Categories") {
          mobTagsList.appendChild(createChip(liveState.category, () => setLiveCategory("All Categories")));
        }
        if (liveState.city !== "All Cities") {
          mobTagsList.appendChild(createChip(liveState.city, () => setLiveCity("All Cities")));
        }
        if (liveState.brand !== "All Brands") {
          mobTagsList.appendChild(createChip(liveState.brand, () => setLiveBrand("All Brands")));
        }
        if (liveState.budget !== "all") {
          mobTagsList.appendChild(createChip("Budget: " + liveState.budget, () => setLiveBudget("all")));
        }
        if (liveState.search !== "") {
          mobTagsList.appendChild(createChip('"' + liveState.search + '"', () => syncLiveSearch("")));
        }
      } else {
        mobTagsBar.classList.add("hidden");
      }
    }

    // Highlight active buttons in UI
    highlightActiveButtons();
  };

  function createChip(text, onRemove) {
    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-blue-50 text-blue-900 text-[10px] font-bold hover:bg-rose-100 hover:text-rose-700 border border-blue-200 cursor-pointer shadow-2xs";
    btn.innerHTML = '<span>' + escapeHtml(text) + '</span> <i class="fa-solid fa-xmark text-[9px]"></i>';
    btn.onclick = onRemove;
    return btn;
  }

  function highlightActiveButtons() {
    // Country buttons
    document.querySelectorAll(".live-country-btn, .drawer-country-btn").forEach(btn => {
      const c = btn.dataset.country || "";
      const isActive = (c.toLowerCase() === liveState.country.toLowerCase());
      if (isActive) {
        btn.classList.add("bg-[#071626]", "text-white", "font-black");
        btn.classList.remove("bg-slate-50", "border-slate-200", "text-slate-700");
      } else {
        btn.classList.remove("bg-[#071626]", "text-white", "font-black");
        btn.classList.add("bg-slate-50", "border-slate-200", "text-slate-700");
      }
    });

    // Category buttons
    document.querySelectorAll(".live-cat-btn, .drawer-cat-btn, .drawer-category-btn").forEach(btn => {
      const cat = btn.dataset.category || "";
      const isActive = (cat.toLowerCase() === liveState.category.toLowerCase());
      if (isActive) {
        btn.classList.add("bg-[#071626]", "text-white", "font-black");
        btn.classList.remove("bg-slate-50", "border-slate-200", "text-slate-700");
      } else {
        btn.classList.remove("bg-[#071626]", "text-white", "font-black");
        btn.classList.add("bg-slate-50", "border-slate-200", "text-slate-700");
      }
    });

    // Brand buttons
    document.querySelectorAll(".live-brand-btn, .drawer-brand-btn").forEach(btn => {
      const b = btn.dataset.brand || "";
      const isActive = (b.toLowerCase() === liveState.brand.toLowerCase());
      if (isActive) {
        btn.classList.add("bg-[#071626]", "text-white", "font-black");
        btn.classList.remove("bg-slate-50", "border-slate-200", "text-slate-700");
      } else {
        btn.classList.remove("bg-[#071626]", "text-white", "font-black");
        btn.classList.add("bg-slate-50", "border-slate-200", "text-slate-700");
      }
    });

    // Budget buttons
    document.querySelectorAll(".live-budget-btn, .drawer-budget-btn").forEach(btn => {
      const bg = btn.dataset.budget || "";
      const isActive = (bg === liveState.budget);
      if (isActive) {
        btn.classList.add("bg-joule-blue", "text-joule-yellow", "font-black");
        btn.classList.remove("bg-slate-50", "border-slate-200", "text-slate-700");
      } else {
        btn.classList.remove("bg-joule-blue", "text-joule-yellow", "font-black");
        btn.classList.add("bg-slate-50", "border-slate-200", "text-slate-700");
      }
    });

    // Year buttons
    document.querySelectorAll(".drawer-year-btn").forEach(btn => {
      const yr = btn.dataset.year || "";
      const isActive = (yr === liveState.year);
      if (isActive) {
        btn.classList.add("bg-slate-900", "text-white", "font-black");
        btn.classList.remove("bg-slate-50", "border-slate-200", "text-slate-700");
      } else {
        btn.classList.remove("bg-slate-900", "text-white", "font-black");
        btn.classList.add("bg-slate-50", "border-slate-200", "text-slate-700");
      }
    });

    // City buttons & Quick Tabs
    document.querySelectorAll(".sidebar-city-btn, .drawer-city-btn").forEach(btn => {
      const ct = btn.dataset.city || "";
      const isActive = (ct.toLowerCase() === liveState.city.toLowerCase());
      if (isActive) {
        btn.classList.add("bg-[#071626]", "text-white", "font-bold", "shadow-xs");
        btn.classList.remove("bg-slate-50", "text-slate-700");
      } else {
        btn.classList.remove("bg-[#071626]", "text-white", "font-bold", "shadow-xs");
        btn.classList.add("bg-slate-50", "text-slate-700");
      }
    });

    document.querySelectorAll(".quick-city-tab").forEach(tab => {
      const ct = tab.dataset.city || "";
      const isActive = (ct.toLowerCase() === liveState.city.toLowerCase());
      if (isActive) {
        tab.classList.add("bg-[#071626]", "text-white", "border-[#071626]", "shadow-xs", "ring-1", "ring-amber-400");
        tab.classList.remove("bg-slate-50", "text-slate-700", "border-slate-200");
      } else {
        tab.classList.remove("bg-[#071626]", "text-white", "border-[#071626]", "shadow-xs", "ring-1", "ring-amber-400");
        tab.classList.add("bg-slate-50", "text-slate-700", "border-slate-200");
      }
    });
  }

  function escapeHtml(str) {
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
  }

  // Initialize on page load
  document.addEventListener("DOMContentLoaded", function() {
    applyCatalogFilters();
  });
})();
</script>

<?php 
unset($machine);
require_once __DIR__ . '/includes/footer.php'; 
?>
