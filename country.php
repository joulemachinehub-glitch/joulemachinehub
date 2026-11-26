<?php
// Joule Machine Hub - Dedicated Country Manufacturing Hub & Cities Page
require_once __DIR__ . '/data/machines.php';

// Determine requested country
$countryParam = isset($_GET['country']) ? trim($_GET['country']) : '';
$currentHub = !empty($countryParam) ? getCountryHub($countryParam) : $countryHubs['Japan'];
if (!empty($countryParam) && !$currentHub) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
if (!$currentHub) {
    $currentHub = $countryHubs['Japan'];
}

// STRICT CLEAN SEO SLUG ENFORCEMENT: Redirect legacy .php URLs, spaces, or non-canonical slugs to canonical slug
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$canonicalCountryPath = getCountryUrl($currentHub['name']);
$requestedPath = trim(parse_url($requestUri, PHP_URL_PATH), '/');
$hasRawSpace = str_contains($requestUri, ' ') || str_contains($requestUri, '%20') || str_contains($requestUri, '+');

if (str_contains($requestUri, 'country.php') || $hasRawSpace || ($requestedPath !== $canonicalCountryPath && !empty($countryParam))) {
    $qs = parse_url($requestUri, PHP_URL_QUERY);
    $targetUrl = '/' . $canonicalCountryPath . ($qs ? ('?' . $qs) : '');
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $targetUrl);
    exit;
}

$selectedCity = isset($_GET['city']) ? trim($_GET['city']) : 'All Cities';
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : 'All Categories';
$sortBy = isset($_GET['sort']) ? trim($_GET['sort']) : 'featured';

// Filter machines for this country
$countryMachines = array_filter($machines, function($m) use ($currentHub, $selectedCity, $selectedCategory) {
    $cleanHub = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $currentHub['name']));
    $cleanMCountry = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $m['country'] ?? ''));
    $matchCountry = ($cleanHub === $cleanMCountry) || ($cleanHub === 'southkorea' && $cleanMCountry === 'korea');
    if (!$matchCountry) return false;

    if ($selectedCity !== 'All Cities' && (!isset($m['city']) || strtolower($m['city']) !== strtolower($selectedCity))) {
        return false;
    }

    if ($selectedCategory !== 'All Categories' && (!isset($m['category']) || strtolower($m['category']) !== strtolower($selectedCategory))) {
        return false;
    }

    return true;
});

// Sort machines
usort($countryMachines, function($a, $b) use ($sortBy) {
    if ($sortBy === 'priceLow') {
        return ($a['price'] ?? 0) <=> ($b['price'] ?? 0);
    } elseif ($sortBy === 'priceHigh') {
        return ($b['price'] ?? 0) <=> ($a['price'] ?? 0);
    } elseif ($sortBy === 'yearNew') {
        return ($b['year'] ?? 0) <=> ($a['year'] ?? 0);
    }
    return ($b['is_featured'] ? 1 : 0) <=> ($a['is_featured'] ? 1 : 0);
});

// SEO Setup (Clean URLs, Primary Keywords, Enriched Tags)
$canonicalBase = 'https://joulemachinehub.com';
$canonicalUrl = $canonicalBase . '/' . ltrim(getCountryUrl($currentHub['name']), '/');
$pageTitle = !empty($currentHub['meta_title']) ? $currentHub['meta_title'] : ("Used & New Industrial Machinery in " . $currentHub['name'] . " | Joule Machine Hub");
$pageDesc = !empty($currentHub['meta_desc']) ? $currentHub['meta_desc'] : ("Explore verified used and new industrial machinery across " . $currentHub['name'] . ".");
$pageKeywords = "used machinery " . strtolower($currentHub['name']) . ", new industrial machinery " . strtolower($currentHub['name']) . ", second hand machinery " . strtolower($currentHub['name']) . ", buy cnc " . strtolower($currentHub['name']) . ", factory liquidation " . strtolower($currentHub['name']) . ", manufacturing machinery " . strtolower($currentHub['name']);
$geoPlacename = $currentHub['name'];
$geoRegion = $currentHub['code'] ?? '';
$ogTitle = $pageTitle;
$ogDesc = $pageDesc;
$ogImage = $canonicalBase . '/' . ltrim($currentHub['image'], '/');
$ogImageAlt = "Used & New Industrial Machinery Hub in " . $currentHub['name'];
$pageRobots = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

// JSON-LD Breadcrumbs and CollectionPage Schema
$jsonLd = [
    '@context' => 'https://schema.org/',
    '@graph' => [
        [
            '@type' => 'CollectionPage',
            'name' => $pageTitle,
            'description' => $pageDesc,
            'url' => $canonicalUrl,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Joule Machine Hub',
                'url' => $canonicalBase . '/'
            ]
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => $canonicalBase . '/'
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Global Hubs',
                    'item' => $canonicalBase . '/#cities'
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $currentHub['name'] . ' Hub',
                    'item' => $canonicalUrl
                ]
            ]
        ],
        [
            '@type' => 'ItemList',
            'name' => 'Available Machinery in ' . $currentHub['name'],
            'numberOfItems' => count($countryMachines),
            'itemListElement' => array_values(array_map(function($m, $pos) use ($canonicalBase) {
                return [
                    '@type' => 'ListItem',
                    'position' => $pos + 1,
                    'name' => ($m['year'] ? $m['year'] . ' ' : '') . ($m['title'] ?? ''),
                    'url' => $canonicalBase . '/' . ltrim(getMachineUrl($m), '/'),
                    'image' => !empty($m['images'][0]) ? ($canonicalBase . '/' . ltrim($m['images'][0], '/')) : null
                ];
            }, array_slice($countryMachines, 0, 10), array_keys(array_slice($countryMachines, 0, 10))))
        ]
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- SEO BREADCRUMB NAVIGATION -->
<div class="bg-white border-b border-slate-200 py-3 px-4">
  <div class="max-w-7xl mx-auto flex items-center flex-wrap gap-2 text-xs text-slate-500">
    <a href="/" class="hover:text-joule-brightblue font-bold flex items-center gap-1">
      <i class="fa-solid fa-house text-slate-400 text-[10px]"></i>
      <span>Home</span>
    </a>
    <span class="text-slate-300">/</span>
    <a href="/#cities" class="hover:text-joule-brightblue font-medium flex items-center gap-1">
      <i class="fa-solid fa-earth-americas text-blue-600"></i>
      <span>Global Hubs</span>
    </a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-black flex items-center gap-1.5">
      <?php echo getCountryFlagHtml($currentHub['code'] ?? 'JP', 'w-5 h-3.5'); ?>
      <span><?php echo htmlspecialchars($currentHub['name']); ?> Hub</span>
    </span>
    <?php if ($selectedCity !== 'All Cities'): ?>
      <span class="text-slate-300">/</span>
      <span class="text-blue-700 font-bold"><?php echo htmlspecialchars($selectedCity); ?></span>
    <?php endif; ?>
  </div>
</div>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-10">

  <!-- 1. COUNTRY HERO BANNER -->
  <div class="bg-gradient-to-br from-joule-blue via-blue-900 to-joule-navy text-white rounded-2xl p-4 sm:p-6 lg:p-8 border-2 border-joule-yellow shadow-2xl relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute -right-12 -bottom-12 w-64 h-64 rounded-full bg-white/5 pointer-events-none"></div>
    <div class="absolute right-32 -top-12 w-48 h-48 rounded-full bg-joule-yellow/10 pointer-events-none"></div>

    <div class="relative z-10 space-y-5 sm:space-y-6">
      
      <!-- Top Badges & Country Switcher -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
          <span class="inline-flex items-center gap-1.5 bg-joule-yellow text-slate-950 font-black text-[11px] sm:text-xs px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
            <?php echo getCountryFlagHtml($currentHub['code'] ?? 'JP', 'w-5 h-3.5'); ?>
            <span><?php echo htmlspecialchars($currentHub['name']); ?> Manufacturing Hub</span>
          </span>
          <span class="bg-white/20 text-white font-bold text-[11px] sm:text-xs px-2.5 sm:px-3 py-1 rounded-full border border-white/20">
            <?php echo count($currentHub['cities']); ?> Industrial Cities
          </span>
          <span class="bg-emerald-500 text-white font-bold text-[11px] sm:text-xs px-2.5 sm:px-3 py-1 rounded-full flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
            <span><?php echo count($countryMachines); ?> Verified Machines Live</span>
          </span>
        </div>

        <!-- Quick Switcher to Other Countries -->
        <div class="w-full sm:w-auto flex items-center justify-between sm:justify-start gap-2 text-xs">
          <span class="text-blue-200 font-bold hidden md:inline">Switch Country:</span>
          <select 
            onchange="location = this.value;" 
            class="w-full sm:w-auto bg-white text-slate-900 font-bold px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:outline-none cursor-pointer shadow-sm"
          >
            <?php foreach ($countryHubs as $cName => $cData): ?>
              <option value="<?php echo htmlspecialchars(getCountryUrl($cName)); ?>" <?php echo $cName === $currentHub['name'] ? 'selected' : ''; ?>>
                <?php echo $cData['flag'] . ' ' . htmlspecialchars($cName) . ' (' . count($cData['cities']) . ' Cities)'; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Main Headline & Description -->
      <div class="max-w-3xl space-y-3">
        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-white leading-tight">
          Second Hand / Used / Old / Imported &amp; New Industrial Machinery in <span class="text-joule-yellow"><?php echo htmlspecialchars($currentHub['name']); ?></span>
        </h1>
        <p class="text-blue-100 text-xs sm:text-sm sm:leading-relaxed font-medium">
          <?php echo htmlspecialchars($currentHub['description']); ?>
        </p>
      </div>

      <!-- Quick Trust Highlights Bar -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 pt-3 sm:pt-4 border-t border-blue-800/80 text-xs">
        <div class="bg-white/10 backdrop-blur-md rounded-xl sm:rounded-2xl p-2.5 sm:p-3 border border-white/10 flex items-center gap-2 sm:gap-3">
          <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-joule-yellow text-slate-950 flex items-center justify-center font-bold text-sm sm:text-base shrink-0">
            <i class="fa-solid fa-file-invoice"></i>
          </div>
          <div class="min-w-0">
            <div class="text-white font-black text-[11px] sm:text-xs">B2B Mediator Desk</div>
            <div class="text-[10px] sm:text-[11px] text-blue-200 truncate">Verified inventory</div>
          </div>
        </div>

        <div class="bg-white/10 backdrop-blur-md rounded-xl sm:rounded-2xl p-2.5 sm:p-3 border border-white/10 flex items-center gap-2 sm:gap-3">
          <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-sm sm:text-base shrink-0">
            <i class="fa-solid fa-bolt"></i>
          </div>
          <div class="min-w-0">
            <div class="text-white font-black text-[11px] sm:text-xs">Inspection Desk</div>
            <div class="text-[10px] sm:text-[11px] text-blue-200 truncate">Scheduled on agreement</div>
          </div>
            <div class="text-[10px] sm:text-[11px] text-blue-200 truncate">Factory floor visits</div>
          </div>
        </div>

        <div class="bg-white/10 backdrop-blur-md rounded-xl sm:rounded-2xl p-2.5 sm:p-3 border border-white/10 flex items-center gap-2 sm:gap-3">
          <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-blue-500 text-white flex items-center justify-center font-bold text-sm sm:text-base shrink-0">
            <i class="fa-solid fa-city"></i>
          </div>
          <div class="min-w-0">
            <div class="text-white font-black text-[11px] sm:text-xs"><?php echo count($currentHub['cities']); ?> Hubs</div>
            <div class="text-[10px] sm:text-[11px] text-blue-200 truncate">Top industrial clusters</div>
          </div>
        </div>

        <div class="bg-white/10 backdrop-blur-md rounded-xl sm:rounded-2xl p-2.5 sm:p-3 border border-white/10 flex items-center gap-2 sm:gap-3">
          <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center font-bold text-sm sm:text-base shrink-0">
            <i class="fa-solid fa-truck-fast"></i>
          </div>
          <div class="min-w-0">
            <div class="text-white font-black text-[11px] sm:text-xs">Global Rigging</div>
            <div class="text-[10px] sm:text-[11px] text-blue-200 truncate">Container export help</div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- 2. REGIONAL INDUSTRIAL CITIES & CLUSTERS SECTION (Exact Visual Design) -->
  <section id="clusters" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-joule-brightblue bg-blue-100/80 border border-blue-200 px-3 py-1 rounded-full mb-1">
          <i class="fa-solid fa-map-location-dot"></i>
          <span><?php echo htmlspecialchars($currentHub['name']); ?> Industrial Clusters</span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-950">
          <?php echo htmlspecialchars($currentHub['name']); ?> Manufacturing Cities (<?php echo count($currentHub['cities']); ?> Hubs)
        </h2>
        <p class="text-xs text-slate-600 mt-0.5">
          Select any manufacturing cluster below to filter machines ready for physical plant visit and on-site inspection.
        </p>
      </div>

      <?php if ($selectedCity !== 'All Cities'): ?>
        <a 
          href="<?php echo htmlspecialchars(getCountryUrl($currentHub['name'])); ?>#clusters" 
          class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 border border-rose-200 px-3 py-1.5 rounded-xl self-start sm:self-auto transition-colors"
        >
          <i class="fa-solid fa-rotate-left"></i>
          <span>Reset City Filter (Show All <?php echo htmlspecialchars($currentHub['name']); ?>)</span>
        </a>
      <?php endif; ?>
    </div>

    <!-- Visual City Grid with User-Provided Landmark Place Images -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
      <?php foreach ($currentHub['cities'] as $city): 
        $isCityActive = strtolower($selectedCity) === strtolower($city['name']);
        $cityMachinesCount = count(array_filter($machines, function($m) use ($city) {
            return isset($m['city']) && strtolower(trim($m['city'])) === strtolower(trim($city['name']));
        }));
      ?>
        <a 
          href="<?php echo htmlspecialchars(getCityUrl($city['name'])); ?>" 
          class="bg-white p-2.5 sm:p-3.5 rounded-2xl border-2 <?php echo $isCityActive ? 'border-joule-blue ring-4 ring-blue-100 shadow-xl' : 'border-slate-200 hover:border-joule-brightblue hover:shadow-xl'; ?> transition-all group flex flex-col justify-between overflow-hidden"
        >
          <div>
            <!-- Landmark Place Image -->
            <div class="w-full aspect-[4/3] bg-amber-50/40 rounded-xl overflow-hidden mb-2.5 border border-amber-100 flex items-center justify-center p-1 group-hover:scale-105 transition-transform duration-300">
              <img 
                src="<?php echo htmlspecialchars('/' . ltrim($city['image'], '/')); ?>" 
                alt="<?php echo htmlspecialchars($city['name']); ?> Landmark" 
                class="w-full h-full object-contain"
                loading="lazy"
              >
            </div>

            <!-- City Title & Tag -->
            <div class="flex items-center justify-between">
              <h3 class="font-black text-slate-900 text-sm group-hover:text-joule-brightblue transition-colors">
                <?php echo htmlspecialchars($city['name']); ?>
              </h3>
              <span class="text-[9px] <?php echo $city['badge_color'] ?? 'bg-blue-50 text-blue-700'; ?> font-bold px-2 py-0.5 rounded-full">
                Hub
              </span>
            </div>

            <!-- Industrial Subzone & Equipment Specialty -->
            <p class="text-[11px] text-slate-500 font-bold mt-0.5 truncate" title="<?php echo htmlspecialchars($city['subzone']); ?>">
              <?php echo htmlspecialchars($city['subzone']); ?>
            </p>
            <p class="text-[10px] text-blue-600 font-medium mt-1 truncate" title="<?php echo htmlspecialchars($city['specialty']); ?>">
              <?php echo htmlspecialchars($city['specialty']); ?>
            </p>
          </div>

          <!-- Bottom Live Status & Direct Arrow -->
          <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
            <span class="font-black text-emerald-700 flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
              <span><?php echo $cityMachinesCount; ?> Machines Live</span>
            </span>
            <span class="text-slate-400 group-hover:text-joule-brightblue group-hover:translate-x-1 transition-all">
              <i class="fa-solid fa-arrow-right"></i>
            </span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- 3. VERIFIED MACHINERY CATALOG FOR THIS COUNTRY -->
  <section id="catalog" class="space-y-6 pt-4 border-t-2 border-slate-200">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-black text-slate-950 flex items-center gap-2.5">
          <span>Available Machinery in <?php echo htmlspecialchars($currentHub['name']); ?></span>
          <span class="text-xs bg-joule-yellow text-slate-950 font-black px-2.5 py-0.5 rounded-full border border-amber-300">
            <?php echo count($countryMachines); ?> Listed
          </span>
        </h2>
        <p class="text-xs text-slate-500 mt-1">
          <?php if ($selectedCity !== 'All Cities'): ?>
            Filtering equipment in <strong><?php echo htmlspecialchars($selectedCity); ?></strong>. 
            <a href="<?php echo htmlspecialchars(getCountryUrl($currentHub['name'])); ?>#catalog" class="text-blue-600 hover:underline font-bold">Clear city filter</a>
          <?php else: ?>
            Click on <strong>"Enquire Now"</strong> to submit your inquiry directly to the machine owner in <?php echo htmlspecialchars($currentHub['name']); ?>.
          <?php endif; ?>
        </p>
      </div>

      <!-- Sort Options -->
      <div class="flex items-center gap-2 self-end sm:self-auto text-xs">
        <span class="text-slate-500 font-bold">Sort By:</span>
        <select 
          onchange="location = this.value;" 
          class="bg-white border-2 border-slate-300 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-700 focus:outline-none focus:border-joule-blue cursor-pointer"
        >
          <option value="<?php echo htmlspecialchars(getCountryUrl($currentHub['name'])); ?>#catalog" selected>Featured / Recommended</option>
        </select>
      </div>
    </div>

    <!-- Product Grid -->
    <?php if (empty($countryMachines)): ?>
      <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-12 text-center space-y-4 shadow-xs">
        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-2xl font-bold">
          <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <h3 class="text-xl font-black text-slate-900">No Machines Found in <?php echo htmlspecialchars($selectedCity !== 'All Cities' ? $selectedCity : $currentHub['name']); ?></h3>
        <p class="text-xs text-slate-500 max-w-sm mx-auto">
          We are adding verified listings from this cluster daily. Submit an instant RFQ to request direct factory quotes.
        </p>
        <div class="flex items-center justify-center gap-3 pt-2">
          <a href="<?php echo htmlspecialchars(getCountryUrl($currentHub['name'])); ?>#catalog" class="bg-joule-blue text-joule-yellow font-black text-xs py-2.5 px-6 rounded-xl shadow-sm">
            View All in <?php echo htmlspecialchars($currentHub['name']); ?>
          </a>
          <button type="button" onclick="openRequirementModal()" class="bg-joule-yellow text-slate-950 font-black text-xs py-2.5 px-6 rounded-xl shadow-sm cursor-pointer">
            Post RFQ Requirement
          </button>
        </div>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <?php foreach ($countryMachines as $machine): 
          $topSpecs = array_slice($machine['specs'], 0, 3);
        ?>
          <div class="bg-white rounded-2xl border border-slate-200 hover:border-joule-brightblue hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden group">
            
            <!-- Image Banner -->
            <div class="relative aspect-[16/10] bg-slate-900 overflow-hidden">
              <a href="<?php echo htmlspecialchars(getMachineUrl($machine)); ?>" class="block w-full h-full" title="View Full Specs for <?php echo htmlspecialchars($machine['title']); ?>">
                <img 
                  src="<?php echo htmlspecialchars('/' . ltrim($machine['images'][0], '/')); ?>" 
                  alt="<?php echo htmlspecialchars($machine['year'] . ' ' . $machine['brand'] . ' ' . $machine['model'] . ' ' . $machine['category'] . ' - Verified in ' . $currentHub['name']); ?>" 
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                  loading="lazy"
                  decoding="async"
                >
              </a>
              <div class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-transparent to-black/30 pointer-events-none"></div>

              <!-- Category Tag -->
              <div class="absolute top-3 left-3 flex flex-wrap gap-1.5 z-10 pointer-events-none">
                
                <span class="bg-joule-blue text-white text-[10px] font-black px-2.5 py-1 rounded-md shadow-xs">
                  <?php echo htmlspecialchars($machine['category']); ?>
                </span>
              </div>

              <!-- Photos Badge -->
              <div class="absolute top-3 right-3 z-10">
                <span class="bg-slate-900/85 backdrop-blur-md text-white text-[10px] font-bold px-2 py-1 rounded-md border border-white/20 flex items-center gap-1 shadow-sm">
                  <i class="fa-solid fa-camera text-joule-yellow text-[10px]"></i> <?php echo count($machine['images']); ?> Photos
                </span>
              </div>

              <!-- Bottom Stats -->
              <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-xs z-10">
                <span class="bg-slate-900/85 backdrop-blur-md px-2.5 py-1 rounded-md border border-white/20 font-semibold text-[11px]">
                  Year: <?php echo $machine['year']; ?> • <?php echo $machine['hours_run']; ?>
                </span>
                <span class="bg-emerald-600 font-bold px-2 py-1 rounded-md text-[10px]">
                  ✓ Running
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
                    <span><?php echo htmlspecialchars($machine['city'] ?? $machine['country']); ?></span>
                  </span>
                </div>

                <h3 class="text-base font-black text-slate-950 group-hover:text-joule-brightblue transition-colors line-clamp-2 min-h-[3rem] leading-snug">
                  <a href="<?php echo htmlspecialchars(getMachineUrl($machine)); ?>">
                    <?php echo htmlspecialchars($machine['title']); ?>
                  </a>
                </h3>

                <!-- Price -->
                <div class="flex items-baseline gap-2 mt-2">
                  <span class="text-2xl font-black text-slate-950 machine-price-display" data-price-inr="<?php echo $machine['price']; ?>" data-price-default="<?php echo htmlspecialchars(formatMachinePrice($machine)); ?>">
                    <?php echo htmlspecialchars(formatMachinePrice($machine)); ?>
                  </span>
                  <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                    Negotiable
                  </span>
                </div>

                <!-- Specs -->
                <div class="grid grid-cols-2 gap-2 bg-slate-50 rounded-2xl p-3 border border-slate-100 text-xs mt-3">
                  <?php foreach ($topSpecs as $sKey => $sVal): ?>
                    <div class="truncate">
                      <span class="text-slate-400 block text-[9px] uppercase font-bold"><?php echo htmlspecialchars($sKey); ?></span>
                      <span class="font-bold text-slate-800 truncate block"><?php echo htmlspecialchars($sVal); ?></span>
                    </div>
                  <?php endforeach; ?>
                  <div class="truncate">
                    <span class="text-slate-400 block text-[9px] uppercase font-bold">Inspection</span>
                    <span class="font-bold text-emerald-600 truncate block">Plant Inspection</span>
                  </div>
                </div>

                <!-- Location & Seller Info -->
                <div class="border-t border-slate-100 pt-3 mt-3 text-xs text-slate-600 space-y-1">
                  <div class="flex items-center gap-1.5 truncate">
                    <i class="fa-solid fa-location-dot text-slate-400 text-xs"></i>
                    <span class="truncate"><?php echo htmlspecialchars($machine['location']); ?></span>
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

              <!-- Call & Action Buttons -->
              <div class="pt-2 border-t border-slate-100 space-y-2">
                <button 
                  type="button" 
                  onclick="openInquiryModal('<?php echo $machine['id']; ?>')" 
                  class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black py-3 px-4 rounded-xl flex items-center justify-center gap-2.5 shadow-md shadow-emerald-600/30 transition-all text-xs sm:text-sm cursor-pointer"
                >
                  <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-file-signature text-white text-xs"></i>
                  </div>
                  <span>Enquire Now (Fill Form)</span>
                </button>

                <div class="grid grid-cols-2 gap-2">
                  <button 
                    type="button" 
                    onclick="openInquiryModal('<?php echo $machine['id']; ?>')" 
                    class="bg-blue-50 hover:bg-blue-100 text-joule-brightblue border border-blue-200 font-bold py-2 px-3 rounded-lg text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
                  >
                    <i class="fa-solid fa-comment-dots"></i>
                    <span>Get Best Price</span>
                  </button>

                  <a 
                    href="<?php echo htmlspecialchars(getMachineUrl($machine)); ?>" 
                    class="bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-2 px-3 rounded-lg text-xs flex items-center justify-center gap-1.5 transition-colors text-center"
                  >
                    <span>Full Specs</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                  </a>
                </div>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </section>

  <!-- 3. SEO-OPTIMIZED COUNTRY LOCATION GUIDE (User Requested) -->
  <section class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-8 shadow-xs space-y-8">
    <div>
      <div class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-joule-brightblue bg-blue-100/80 border border-blue-200 px-3 py-1 rounded-full mb-1">
        <i class="fa-solid fa-earth-americas"></i>
        <span>Manufacturing Industry Overview</span>
      </div>
      <h2 class="text-2xl sm:text-3xl font-black text-slate-950">
        Buying Used &amp; New Industrial Machinery in <?php echo htmlspecialchars($currentHub['name']); ?>
      </h2>
      <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-3xl leading-relaxed">
        Essential guide for equipment buyers looking to inspect, acquire, and transport verified CNC machining centers, lathes, moulding machines, and heavy fabrication machinery across <?php echo htmlspecialchars($currentHub['name']); ?>.
      </p>
    </div>

    <!-- Country Pillars -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-2">
        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg font-bold">
          <i class="fa-solid fa-city"></i>
        </div>
        <h3 class="font-black text-slate-900 text-sm"><?php echo count($currentHub['cities']); ?> Key Manufacturing Clusters</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
          Surplus and new machinery is located in established industrial zones across <?php echo htmlspecialchars($currentHub['tagline']); ?> with direct factory owner and supplier access.
        </p>
      </div>

      <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-2">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg font-bold">
          <i class="fa-solid fa-clipboard-check"></i>
        </div>
        <h3 class="font-black text-slate-900 text-sm">Direct Factory Floor Equipment Inspection</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
          Every machine is located in verified industrial plants. Buyers and technical inspectors can perform physical checks, nameplate verification, and review maintenance logs before purchase.
        </p>
      </div>

      <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-2">
        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg font-bold">
          <i class="fa-solid fa-truck-ramp-box"></i>
        </div>
        <h3 class="font-black text-slate-900 text-sm">Domestic & Global Transport</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
          Overhead crane loading, multi-axle low-bed trailer logistics, sea-worthy wooden crating, and export container packing assistance available.
        </p>
      </div>
    </div>

    <div class="prose max-w-none text-xs sm:text-sm text-slate-700 space-y-4 leading-relaxed border-t border-slate-100 pt-6">
      <h3 class="text-base sm:text-lg font-black text-slate-950">
        Industrial Manufacturing Ecosystem in <?php echo htmlspecialchars($currentHub['name']); ?>
      </h3>
      <p>
        <?php echo htmlspecialchars($currentHub['description']); ?>
      </p>
      <p>
        Whether you are expanding workshop capacity, upgrading to high-speed multi-axis CNC centers, or securing heavy plant utilities, sourcing pre-owned or new machines in <strong><?php echo htmlspecialchars($currentHub['name']); ?></strong> through Joule Machine Hub provides verified equipment listings with authentic maintenance records through our dedicated B2B mediator desk.
      </p>
    </div>
  </section>

  <!-- 4. LOCATION FAQS (Interactive Accordion - User Requested) -->
  <section class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-8 shadow-xs space-y-6">
    <div>
      <div class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-joule-brightblue bg-blue-100/80 border border-blue-200 px-3 py-1 rounded-full mb-1">
        <i class="fa-solid fa-circle-question"></i>
        <span>Help & FAQs</span>
      </div>
      <h2 class="text-2xl sm:text-3xl font-black text-slate-950">
        Frequently Asked Questions: Buying Used &amp; New Machinery in <?php echo htmlspecialchars($currentHub['name']); ?>
      </h2>
      <p class="text-xs text-slate-600 mt-1">
        Answers to essential buyer questions about physical plant inspection, payment terms, and machinery transport across <?php echo htmlspecialchars($currentHub['name']); ?>.
      </p>
    </div>

    <div class="space-y-3">
      <?php 
      $countryFaqs = [
        [
          'q' => "How can I inspect a machine listed in " . $currentHub['name'] . "?",
          'a' => "Once an enquiry is submitted, you can request operating videos, maintenance records, and coordinate on-site physical inspection directly with the listing seller in " . $currentHub['name'] . "."
        ],
        [
          'q' => "Can operational condition and photographs be verified before purchase?",
          'a' => "Yes. Buyers can request timestamped photos, live walkthrough videos, and technical specification sheets directly from the listing seller."
        ],
        [
          'q' => "Does Joule Machine Hub provide rigging or transport in " . $currentHub['name'] . "?",
          'a' => "No. Joule Machine Hub is an online marketplace that connects buyers and sellers. Physical inspection, machinery rigging, transportation, export documentation, and delivery are arranged directly between the buyer and seller."
        ],
        [
          'q' => "What commercial documentation is provided by the seller?",
          'a' => "Sellers provide commercial documentation (e.g. GST invoice in India, CE declaration or commercial invoice for international equipment) directly to the buyer upon agreement."
        ],
        [
          'q' => "How does Joule Machine Hub facilitate machinery sourcing in " . $currentHub['name'] . "?",
          'a' => "Joule Machine Hub operates as an online B2B mediator portal connecting machinery buyers with verified equipment owners and sellers in " . $currentHub['name'] . " and globally."
        ]
      ];

      foreach ($countryFaqs as $idx => $faq): 
      ?>
        <div class="border-2 border-slate-200 hover:border-joule-brightblue rounded-2xl overflow-hidden transition-all shadow-xs">
          <button 
            type="button" 
            onclick="toggleCountryFaq(<?php echo $idx; ?>)" 
            class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 font-black text-slate-900 text-xs sm:text-sm cursor-pointer bg-slate-50/50 hover:bg-blue-50/40"
          >
            <span class="flex items-center gap-3">
              <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs shrink-0 font-bold">
                <?php echo $idx + 1; ?>
              </span>
              <span><?php echo htmlspecialchars($faq['q']); ?></span>
            </span>
            <span id="country-faq-icon-<?php echo $idx; ?>" class="w-7 h-7 rounded-xl bg-white border border-slate-200 text-slate-500 flex items-center justify-center text-xs shrink-0 transition-transform duration-200">
              <i class="fa-solid fa-chevron-down"></i>
            </span>
          </button>
          <div id="country-faq-ans-<?php echo $idx; ?>" class="hidden px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 leading-relaxed border-t border-slate-100 bg-white">
            <p class="mt-3">
              <?php echo htmlspecialchars($faq['a']); ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

</main>

<script>
function toggleCountryFaq(idx) {
  const ans = document.getElementById('country-faq-ans-' + idx);
  const icon = document.getElementById('country-faq-icon-' + idx);
  if (!ans) return;

  const isHidden = ans.classList.contains('hidden');
  if (isHidden) {
    ans.classList.remove('hidden');
    if (icon) icon.classList.add('rotate-180');
  } else {
    ans.classList.add('hidden');
    if (icon) icon.classList.remove('rotate-180');
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
