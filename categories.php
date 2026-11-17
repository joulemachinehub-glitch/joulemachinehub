<?php
// Joule Machine Hub - Master Industrial Machinery Category Directory
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';

// SEO Settings
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$canonicalUrl = $protocol . $host . '/category';

$pageTitle = "Industrial Machinery Categories | Second Hand / Used / Old / Imported & New Machines | Joule Machine Hub";
$pageDesc = "Explore all verified industrial machinery categories on Joule Machine Hub. Browse power presses, cold formers, CNC centers, lathes, injection molding, access lifts, and specialized industrial equipment worldwide with zero buyer commission.";
$pageKeywords = "industrial machinery categories, used machinery directory, second hand industrial machines, buy used cnc, power presses, cold formers, injection molding machines for sale, plant equipment categories";
$ogTitle = $pageTitle;
$ogDesc = $pageDesc;
$ogImage = $protocol . $host . '/assets/images/haas-vmc-1.jpg';
$ogImageAlt = "Joule Machine Hub - Industrial Machinery Categories Directory";
$ogType = "website";
$pageRobots = "index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1";

// Calculate dynamic machine counts & origin countries per category
$categoryStats = [];
$totalMachines = count($machines);

foreach ($categoryMap as $slug => $cat) {
    $catName = $cat['name'];
    $matching = array_filter($machines, function($m) use ($catName) {
        return strtolower(trim($m['category'] ?? '')) === strtolower(trim($catName));
    });
    $count = count($matching);
    
    // Extract unique origin countries for this category
    $origins = array_values(array_unique(array_filter(array_column($matching, 'country'))));
    if (empty($origins)) {
        $origins = ['Worldwide'];
    }
    
    // Extract sample brands
    $brands = array_values(array_unique(array_filter(array_column($matching, 'brand'))));
    
    $categoryStats[$slug] = [
        'count' => $count,
        'origins' => $origins,
        'brands' => array_slice($brands, 0, 4)
    ];
}

// Build Google SERP Structured Data (CollectionPage + BreadcrumbList + ItemList + FAQPage)
$itemListElements = [];
$pos = 1;
foreach ($categoryMap as $slug => $cat) {
    $itemListElements[] = [
        '@type' => 'ListItem',
        'position' => $pos++,
        'name' => $cat['name'],
        'url' => $protocol . $host . '/' . getCategoryUrl($cat['name']),
        'image' => !empty($cat['image']) ? ($protocol . $host . '/' . ltrim($cat['image'], '/')) : null
    ];
}

$categoryFaqs = [
    [
        'q' => "What categories of industrial machinery are available on Joule Machine Hub?",
        'a' => "Joule Machine Hub hosts over 13 primary machinery categories including Mechanical & Hydraulic Power Presses, Multi-Die Cold Formers & Bolt Headers, CNC Jig Borers & Machining Centers, Multi-Spindle Automatic Lathes, Plastic Injection Molding Machines, Self-Propelled Electric Scissor Lifts, Cartridge Sealant Filling & Packaging lines, Industrial Digital Textile Printers, and Geotechnical Drilling Rigs."
    ],
    [
        'q' => "How are machine inspections scheduled across these categories?",
        'a' => "Physical machine inspections and live technical trials are arranged through our mediator desk following formal commercial agreement and transaction milestones between buyer and seller. Verified specifications and nameplate records are provided prior to on-site access."
    ],
    [
        'q' => "How does Joule Machine Hub facilitate machinery purchases?",
        'a' => "Joule Machine Hub operates as a structured B2B machinery mediator portal connecting buyers and equipment sellers worldwide. Our platform provides verified specifications, structured pricing, transparent terms, and coordinated dispatch support."
    ],
    [
        'q' => "What if I cannot find the specific machinery category or model I need?",
        'a' => "You can submit an instant RFQ (Request for Quotation) through our platform. Our verified international seller network spans 50+ countries and will review your required technical parameters to source matching surplus machinery directly."
    ]
];

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
                    'name' => 'Machinery Categories',
                    'item' => $canonicalUrl
                ]
            ]
        ],
        [
            '@type' => 'ItemList',
            'name' => 'Industrial Machinery Categories Directory',
            'numberOfItems' => count($itemListElements),
            'itemListElement' => $itemListElements
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(function($faq) {
                return [
                    '@type' => 'Question',
                    'name' => $faq['q'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => strip_tags($faq['a'])
                    ]
                ];
            }, $categoryFaqs)
        ]
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. BREADCRUMBS NAVIGATION -->
<div class="bg-slate-100 border-b border-slate-200">
  <div class="max-w-7xl mx-auto px-4 py-2.5 text-xs text-slate-600 flex items-center gap-2 overflow-x-auto whitespace-nowrap">
    <a href="/" class="hover:text-joule-brightblue flex items-center gap-1 font-medium">
      <i class="fa-solid fa-house text-slate-400 text-xs"></i>
      <span>Home</span>
    </a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-bold">Machinery Categories</span>
  </div>
</div>

<!-- 2. HERO HEADER SECTION -->
<section class="bg-gradient-to-b from-joule-navy to-slate-900 text-white py-10 sm:py-14 relative overflow-hidden border-b-4 border-joule-yellow">
  <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-4 relative z-10 space-y-5 text-center">
    
    <div class="inline-flex items-center gap-2 bg-joule-yellow/20 border border-joule-yellow/50 text-joule-yellow px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider shadow-xs">
      <i class="fa-solid fa-layer-group text-joule-yellow"></i>
      <span>DIRECT FACTORY DIRECTORY • <?php echo count($categoryMap); ?> INDUSTRIAL MACHINERY CATEGORIES</span>
    </div>

    <h1 class="text-2xl sm:text-4xl md:text-5xl font-black text-white leading-tight tracking-tight max-w-4xl mx-auto">
      Second Hand / Used / Old / Imported & New <br class="hidden sm:inline">
      <span class="text-joule-yellow">Industrial Machinery Categories</span>
    </h1>

    <p class="text-blue-100 text-xs sm:text-base leading-relaxed max-w-3xl mx-auto">
      Explore all verified industrial machinery categories on Joule Machine Hub. Connect directly to verified factory owners and equipment manufacturers worldwide with zero buyer commission and direct factory floor equipment inspection.
    </p>

    <!-- Key Metrics Badges -->
    <div class="flex flex-wrap items-center justify-center gap-3 pt-2 text-xs">
      <span class="bg-white/10 backdrop-blur-md border border-white/20 text-white font-bold px-3.5 py-1.5 rounded-xl flex items-center gap-1.5">
        <i class="fa-solid fa-boxes-stacked text-joule-yellow"></i>
        <span><strong><?php echo $totalMachines; ?></strong> Verified Machines</span>
      </span>
      <span class="bg-white/10 backdrop-blur-md border border-white/20 text-white font-bold px-3.5 py-1.5 rounded-xl flex items-center gap-1.5">
        <i class="fa-solid fa-shapes text-joule-yellow"></i>
        <span><strong><?php echo count($categoryMap); ?></strong> Machine Categories</span>
      </span>
      <span class="bg-white/10 backdrop-blur-md border border-white/20 text-white font-bold px-3.5 py-1.5 rounded-xl flex items-center gap-1.5">
        <i class="fa-solid fa-globe text-joule-yellow"></i>
        <span><strong>9+</strong> Manufacturing Hubs</span>
      </span>
      <span class="bg-emerald-500/20 border border-emerald-400 text-emerald-300 font-bold px-3.5 py-1.5 rounded-xl flex items-center gap-1.5">
        <i class="fa-solid fa-shield-check"></i>
        <span>0% Buyer Commission</span>
      </span>
    </div>

    <!-- Quick Search / Filter Input Inside Hero -->
    <div class="max-w-xl mx-auto pt-4">
      <div class="relative">
        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
        <input 
          type="text" 
          id="categorySearchInput" 
          onkeyup="filterCategoryCards()" 
          placeholder="Filter categories: CNC, press, lathe, injection molding, cold former..." 
          class="w-full bg-white text-slate-900 placeholder:text-slate-400 text-xs sm:text-sm pl-11 pr-4 py-3.5 rounded-2xl border-2 border-joule-yellow shadow-xl focus:outline-none focus:ring-2 focus:ring-joule-brightblue"
        >
      </div>
    </div>

  </div>
</section>

<!-- 3. MAIN CATEGORY DIRECTORY GRID -->
<main class="max-w-7xl mx-auto px-4 py-8 sm:py-12 pb-24 lg:pb-16 flex-1 w-full space-y-10">

  <!-- Active Results Count Bar -->
  <div class="flex items-center justify-between border-b border-slate-200 pb-4">
    <div>
      <h2 class="text-xl sm:text-2xl font-black text-slate-950 flex items-center gap-2">
        <i class="fa-solid fa-grip text-joule-brightblue"></i>
        <span>Available Machinery Categories</span>
      </h2>
      <p class="text-xs text-slate-500 mt-0.5">
        Select a category below to explore verified equipment listings, technical specifications, and direct factory price offers.
      </p>
    </div>
    <span id="categoryCountBadge" class="hidden sm:inline-flex text-xs font-black text-slate-700 bg-slate-100 border border-slate-300 px-3 py-1.5 rounded-full">
      <?php echo count($categoryMap); ?> Categories Listed
    </span>
  </div>

  <!-- CATEGORIES GRID -->
  <div id="categoryCardsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($categoryMap as $slug => $cat): 
      $stats = $categoryStats[$slug] ?? ['count' => 0, 'origins' => ['Worldwide'], 'brands' => []];
      $catUrl = '/' . getCategoryUrl($cat['name']);
      $searchKeywords = strtolower($cat['name'] . ' ' . $cat['headline'] . ' ' . $cat['tagline'] . ' ' . implode(' ', $cat['key_specs']) . ' ' . implode(' ', $stats['brands']));
    ?>
      <article 
        class="category-card bg-white rounded-2xl border-2 border-slate-200 hover:border-joule-brightblue overflow-hidden shadow-xs hover:shadow-xl transition-all flex flex-col justify-between group"
        data-search="<?php echo htmlspecialchars($searchKeywords); ?>"
      >
        <div>
          <!-- Card Header / Image Container -->
          <div class="relative h-48 sm:h-52 overflow-hidden bg-slate-900">
            <img 
              src="<?php echo htmlspecialchars('/' . ltrim($cat['image'], '/')); ?>" 
              alt="<?php echo htmlspecialchars($cat['name']); ?> - Second Hand / Used / Old / Imported & New Industrial Machinery" 
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              loading="lazy"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
            
            <!-- Category Icon & Badge -->
            <div class="absolute top-3 left-3 flex items-center gap-2">
              <span class="w-9 h-9 rounded-xl bg-joule-yellow text-slate-950 flex items-center justify-center font-bold text-sm shadow-md">
                <i class="fa-solid <?php echo htmlspecialchars($cat['icon']); ?>"></i>
              </span>
              <span class="bg-slate-950/80 backdrop-blur-md text-white text-[11px] font-black px-2.5 py-1 rounded-lg border border-slate-700">
                <?php echo $stats['count']; ?> Available Units
              </span>
            </div>

            <!-- Origin Countries -->
            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-xs">
              <span class="text-[11px] text-slate-300 font-bold flex items-center gap-1.5">
                <i class="fa-solid fa-location-dot text-joule-yellow text-[10px]"></i>
                <span class="truncate max-w-[200px]"><?php echo htmlspecialchars(implode(', ', $stats['origins'])); ?></span>
              </span>
              <span class="text-[10px] bg-emerald-500/90 text-white font-black px-2 py-0.5 rounded">
                Verified Plant Floor
              </span>
            </div>
          </div>

          <!-- Card Content Body -->
          <div class="p-5 space-y-3.5">
            <div>
              <div class="text-[10px] font-black uppercase tracking-wider text-joule-brightblue mb-1">
                Industrial Machinery Category
              </div>
              <h3 class="text-lg font-black text-slate-950 group-hover:text-joule-brightblue transition-colors leading-snug">
                <a href="<?php echo htmlspecialchars($catUrl); ?>">
                  Second Hand / Used / Old / Imported & New <?php echo htmlspecialchars($cat['name']); ?>
                </a>
              </h3>
            </div>

            <!-- Headline Overview -->
            <p class="text-xs text-slate-600 leading-relaxed line-clamp-2">
              <?php echo htmlspecialchars($cat['headline']); ?>
            </p>

            <!-- Key Specs Badges -->
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 space-y-1.5">
              <div class="text-[9px] font-black uppercase text-slate-400 tracking-wider">
                Category Highlights & Capabilities
              </div>
              <div class="flex flex-wrap gap-1.5">
                <?php foreach (array_slice($cat['key_specs'], 0, 3) as $spec): ?>
                  <span class="text-[10px] font-bold text-slate-700 bg-white px-2 py-0.5 rounded border border-slate-200 truncate max-w-full">
                    <?php echo htmlspecialchars($spec); ?>
                  </span>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Representative Builders / Brands -->
            <?php if (!empty($stats['brands'])): ?>
              <div class="text-[11px] text-slate-500 truncate">
                <strong class="text-slate-700">Leading Brands:</strong> <?php echo htmlspecialchars(implode(', ', $stats['brands'])); ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Card Footer Action -->
        <div class="p-5 pt-0">
          <a 
            href="<?php echo htmlspecialchars($catUrl); ?>" 
            class="w-full bg-slate-900 hover:bg-joule-blue text-white group-hover:bg-joule-blue font-black py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition-colors shadow-xs"
          >
            <span>Explore <?php echo htmlspecialchars($cat['name']); ?> Listings</span>
            <i class="fa-solid fa-arrow-right text-[11px] group-hover:translate-x-1 transition-transform"></i>
          </a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <!-- Empty Search Fallback (Hidden By Default) -->
  <div id="noCategoriesFound" class="hidden text-center py-12 bg-white rounded-2xl border-2 border-dashed border-slate-300 p-8 space-y-3">
    <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-2xl mx-auto">
      <i class="fa-solid fa-magnifying-glass"></i>
    </div>
    <h3 class="text-lg font-black text-slate-950">No Matching Category Found</h3>
    <p class="text-xs text-slate-500 max-w-md mx-auto">
      We couldn't find a machinery category matching your search. Submit your requirement below and our team will broadcast it to verified manufacturing facilities.
    </p>
    <button 
      type="button" 
      onclick="openRequirementModal()" 
      class="bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black py-2.5 px-6 rounded-xl text-xs cursor-pointer shadow-md"
    >
      Post Machinery Requirement (RFQ)
    </button>
  </div>

  <!-- 4. CUSTOM RFQ & PROCUREMENT CALLOUT BANNER -->
  <section class="bg-gradient-to-r from-joule-navy to-joule-blue text-white rounded-3xl p-6 sm:p-10 border-2 border-joule-yellow shadow-xl flex flex-col lg:flex-row items-center justify-between gap-6">
    <div class="space-y-2 text-center lg:text-left">
      <div class="inline-flex items-center gap-1.5 bg-joule-yellow/20 border border-joule-yellow/40 text-joule-yellow text-[10px] font-black uppercase px-3 py-1 rounded-full">
        <i class="fa-solid fa-bullhorn"></i>
        <span>Can't Find Your Exact Category or Model?</span>
      </div>
      <h2 class="text-xl sm:text-2xl lg:text-3xl font-black text-white">
        Submit Your Custom Machinery Specification
      </h2>
      <p class="text-xs sm:text-sm text-blue-100 max-w-2xl leading-relaxed">
        Our verified seller network spans 50+ countries. Post your target tonnage, brand, model, and budget to receive structured offers through our mediated B2B machinery desk.
      </p>
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0 w-full sm:w-auto">
      <button 
        type="button" 
        onclick="openRequirementModal()" 
        class="w-full sm:w-auto bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black text-xs sm:text-sm py-3.5 px-6 rounded-xl shadow-lg cursor-pointer transition-transform hover:scale-105 border border-amber-300 flex items-center justify-center gap-2"
      >
        <i class="fa-solid fa-clipboard-list"></i>
        <span>Post RFQ Requirement</span>
      </button>

      <button 
        type="button" 
        onclick="openPostModal()" 
        class="w-full sm:w-auto bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm py-3.5 px-6 rounded-xl border border-white/20 transition-colors flex items-center justify-center gap-2 cursor-pointer"
      >
        <i class="fa-solid fa-cloud-arrow-up"></i>
        <span>Publish Machine Listing</span>
      </button>
    </div>
  </section>

  <!-- 5. CATEGORY FAQ ACCORDIONS (Enriched for Google SERP FAQPage Schema) -->
  <section class="bg-white rounded-2xl border-2 border-slate-200 p-6 sm:p-8 space-y-6">
    <div class="border-b border-slate-200 pb-4">
      <span class="text-[10px] uppercase font-black tracking-wider text-joule-brightblue block mb-1">
        Industrial Procurement Insights
      </span>
      <h2 class="text-xl sm:text-2xl font-black text-slate-950 flex items-center gap-2">
        <i class="fa-solid fa-circle-question text-joule-brightblue"></i>
        <span>Frequently Asked Questions About Machinery Categories</span>
      </h2>
    </div>

    <div class="space-y-3">
      <?php foreach ($categoryFaqs as $idx => $faq): ?>
        <details class="group bg-slate-50 border border-slate-200 rounded-xl overflow-hidden transition-all duration-200" <?php echo $idx === 0 ? 'open' : ''; ?>>
          <summary class="flex items-center justify-between p-4 cursor-pointer font-black text-slate-900 text-xs sm:text-sm hover:text-joule-brightblue select-none">
            <span class="flex items-center gap-2.5">
              <i class="fa-solid fa-circle-chevron-right text-joule-brightblue group-open:rotate-90 transition-transform"></i>
              <span><?php echo htmlspecialchars($faq['q']); ?></span>
            </span>
          </summary>
          <div class="px-4 pb-4 pt-1 text-xs text-slate-600 leading-relaxed border-t border-slate-200/60 font-medium">
            <?php echo htmlspecialchars($faq['a']); ?>
          </div>
        </details>
      <?php endforeach; ?>
    </div>
  </section>

</main>

<script>
function filterCategoryCards() {
  const query = (document.getElementById('categorySearchInput').value || '').trim().toLowerCase();
  const cards = document.querySelectorAll('.category-card');
  let visibleCount = 0;

  cards.forEach(card => {
    const searchData = card.getAttribute('data-search') || '';
    if (!query || searchData.includes(query)) {
      card.style.display = 'flex';
      visibleCount++;
    } else {
      card.style.display = 'none';
    }
  });

  const noFound = document.getElementById('noCategoriesFound');
  if (noFound) {
    noFound.style.display = visibleCount === 0 ? 'block' : 'none';
  }

  const badge = document.getElementById('categoryCountBadge');
  if (badge) {
    badge.textContent = visibleCount + ' Categories Found';
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
