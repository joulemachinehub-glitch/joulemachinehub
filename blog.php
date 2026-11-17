<?php
// Joule Machine Hub - Industrial Machinery Intelligence & Engineering Blog
// Highly Optimized for "Used Machines", "Used Industrial Machinery", "Second Hand Equipment"
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';
require_once __DIR__ . '/data/articles.php';

$pageTitle = "Used Machines Buying Guides & Technical Blog | Joule Machine Hub";
$pageDesc = "Authoritative guides for buying verified used industrial machines, second hand machinery, and imported equipment. Step-by-step plant floor inspection checklists, CNC backlash tests, and pricing formulas.";
$canonicalUrl = "https://joulemachinehub.com/blog";
$pageKeywords = "used machines for sale, used industrial machinery, second hand machines, buy used cnc, used power press, used lathe machines, used machinery buying guide, used equipment inspection";
$pageRobots = "noindex, nofollow";
$ogType = "blog";
$ogTitle = $pageTitle;
$ogDesc = $pageDesc;
$ogImage = "/assets/images/machines/12/used-aida-pmx-l2-400-stamping-press-01.jpg";

// Extract categories
$allCategories = ['All'];
foreach ($articles as $a) {
    if (!in_array($a['category'], $allCategories)) {
        $allCategories[] = $a['category'];
    }
}

$featuredArticle = $articles[0];
$gridArticles = array_slice($articles, 1);

// Schema markup
$schemaBreadcrumb = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => 'https://joulemachinehub.com/'
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Used Machines Blog & Guides',
            'item' => 'https://joulemachinehub.com/blog'
        ]
    ]
];

$schemaBlog = [
    '@context' => 'https://schema.org',
    '@type' => 'Blog',
    'name' => 'Joule Machine Hub Used Machinery Intelligence',
    'description' => $pageDesc,
    'url' => 'https://joulemachinehub.com/blog',
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'Joule Machine Hub',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => 'https://joulemachinehub.com/assets/images/logo.png'
        ]
    ],
    'blogPost' => array_map(function($a) {
        return [
            '@type' => 'BlogPosting',
            'headline' => $a['title'],
            'url' => 'https://joulemachinehub.com/blog/' . $a['slug'],
            'datePublished' => $a['iso_date'],
            'author' => [
                '@type' => 'Organization',
                'name' => $a['author']
            ],
            'image' => 'https://joulemachinehub.com' . $a['featured_image']
        ];
    }, $articles)
];

require_once __DIR__ . '/includes/header.php';
?>

<script type="application/ld+json">
<?php echo json_encode($schemaBreadcrumb, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>
<script type="application/ld+json">
<?php echo json_encode($schemaBlog, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>

<!-- BREADCRUMBS -->
<nav class="bg-white border-b border-slate-200 py-2.5 px-3.5 sm:px-4 text-[11px] sm:text-xs font-semibold text-slate-500" aria-label="Breadcrumb">
  <div class="max-w-7xl mx-auto flex items-center gap-1.5 sm:gap-2 flex-wrap">
    <a href="/" class="hover:text-joule-brightblue flex items-center gap-1 transition-colors">
      <i class="fa-solid fa-house text-slate-400 text-[10px]"></i>
      <span>Home</span>
    </a>
    <i class="fa-solid fa-chevron-right text-[8px] text-slate-300"></i>
    <span class="text-slate-900 font-bold">Used Machines Blog &amp; Guides</span>
  </div>
</nav>

<!-- HERO SECTION: High-Impact Industrial Blue Theme with Keyword Emphasis -->
<section class="bg-gradient-to-b from-[#0a192f] via-joule-navy to-[#0c1f3d] text-white py-6 sm:py-12 md:py-16 px-3.5 sm:px-4 relative overflow-hidden">
  <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(37,99,235,0.2),transparent)] pointer-events-none"></div>
  
  <div class="max-w-5xl mx-auto text-center relative z-10 space-y-3.5 sm:space-y-5">
    
    <!-- Trust Badge -->
    <div class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-1 rounded-full bg-blue-500/15 border border-blue-400/30 text-joule-yellow text-[10px] sm:text-[11px] font-black uppercase tracking-wider shadow-sm">
      <i class="fa-solid fa-industry text-[10px]"></i>
      <span>Used Machinery &bull; Engineering Knowledge Hub</span>
    </div>

    <!-- Main Headline with Keyword Power -->
    <h1 class="text-xl sm:text-3xl md:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
      Used Machines Buying Guides, <br class="hidden sm:inline">
      <span class="text-joule-yellow">Inspection Manuals &amp; Plant Valuation</span>
    </h1>

    <p class="text-slate-300 text-xs sm:text-sm md:text-base max-w-3xl mx-auto leading-relaxed font-normal">
      Practical engineering handbooks for plant owners and procurement managers acquiring <strong>verified used machines, second hand equipment, and imported machine tools</strong> through our global B2B machinery network.
    </p>

    <!-- Search Input & Quick Topic Filter Chips -->
    <div class="pt-2 max-w-2xl mx-auto space-y-2.5 sm:space-y-3">
      <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
          <i class="fa-solid fa-magnifying-glass text-xs sm:text-sm text-joule-yellow"></i>
        </span>
        <input 
          type="text" 
          id="blogSearchInput" 
          onkeyup="filterArticles()" 
          placeholder="Search used machines: CNC backlash, press, lathe..." 
          class="w-full pl-10 pr-3.5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-white/10 border border-white/20 text-white placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-joule-yellow focus:bg-white/15 backdrop-blur-md transition-all shadow-lg"
        >
      </div>

      <!-- Quick Keyword Trigger Chips: Horizontally Swipeable on Mobile -->
      <div class="swipe-ribbon flex items-center gap-1.5 sm:gap-2 w-full max-w-full overflow-x-auto sm:flex-wrap sm:justify-center text-[10px] sm:text-[11px] pt-1">
        <button type="button" onclick="setSearchFilter('used power press')" class="whitespace-nowrap px-2.5 py-1 rounded-lg bg-white/10 hover:bg-joule-yellow hover:text-slate-950 text-slate-200 border border-white/15 transition-all font-semibold shrink-0 cursor-pointer">Used Power Presses</button>
        <button type="button" onclick="setSearchFilter('used cnc')" class="whitespace-nowrap px-2.5 py-1 rounded-lg bg-white/10 hover:bg-joule-yellow hover:text-slate-950 text-slate-200 border border-white/15 transition-all font-semibold shrink-0 cursor-pointer">Used CNC</button>
        <button type="button" onclick="setSearchFilter('used lathe')" class="whitespace-nowrap px-2.5 py-1 rounded-lg bg-white/10 hover:bg-joule-yellow hover:text-slate-950 text-slate-200 border border-white/15 transition-all font-semibold shrink-0 cursor-pointer">Used Lathes</button>
        <button type="button" onclick="setSearchFilter('cold former')" class="whitespace-nowrap px-2.5 py-1 rounded-lg bg-white/10 hover:bg-joule-yellow hover:text-slate-950 text-slate-200 border border-white/15 transition-all font-semibold shrink-0 cursor-pointer">Cold Formers</button>
        <button type="button" onclick="setSearchFilter('injection molding')" class="whitespace-nowrap px-2.5 py-1 rounded-lg bg-white/10 hover:bg-joule-yellow hover:text-slate-950 text-slate-200 border border-white/15 transition-all font-semibold shrink-0 cursor-pointer">Injection Molding</button>
        <button type="button" onclick="setSearchFilter('import')" class="whitespace-nowrap px-2.5 py-1 rounded-lg bg-white/10 hover:bg-joule-yellow hover:text-slate-950 text-slate-200 border border-white/15 transition-all font-semibold shrink-0 cursor-pointer">Import &amp; Customs</button>
      </div>

      <!-- Quick Metrics Bar -->
      <div class="flex items-center justify-center gap-2 sm:gap-6 pt-2 text-[10px] sm:text-[11px] text-slate-300 font-bold flex-wrap">
        <span class="flex items-center gap-1">
          <i class="fa-solid fa-newspaper text-joule-yellow"></i>
          <span><?php echo count($articles); ?> Technical Manuals</span>
        </span>
        <span class="text-slate-600">&bull;</span>
        <span class="flex items-center gap-1">
          <i class="fa-solid fa-industry text-emerald-400"></i>
          <span>Verified Equipment Listings</span>
        </span>
        <span class="text-slate-600">&bull;</span>
        <span class="flex items-center gap-1">
          <i class="fa-solid fa-certificate text-blue-400"></i>
          <span>Inspection Checklists</span>
        </span>
      </div>
    </div>
  </div>
</section>

<!-- MAIN CONTENT CONTAINER -->
<main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-10 space-y-6 sm:space-y-10 overflow-hidden w-full max-w-full">

  <!-- CATEGORY FILTER PILLS BAR: Swipeable Ribbon on Mobile -->
  <div class="w-full max-w-full overflow-hidden pb-2.5 border-b border-slate-200 flex items-center justify-between gap-2">
    <div class="swipe-ribbon flex items-center gap-1.5 sm:gap-2 w-full max-w-full overflow-x-auto">
      <?php foreach ($allCategories as $cat): ?>
        <button 
          type="button" 
          onclick="filterByCategory('<?php echo htmlspecialchars($cat); ?>', this)"
          class="blog-cat-btn px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs font-black transition-all whitespace-nowrap cursor-pointer shrink-0 <?php echo $cat === 'All' ? 'bg-joule-blue text-white shadow-md shadow-blue-600/30 ring-2 ring-blue-600' : 'bg-white text-slate-700 border border-slate-200 hover:border-joule-blue hover:text-joule-blue'; ?>"
        >
          <?php echo htmlspecialchars($cat); ?>
        </button>
      <?php endforeach; ?>
    </div>

    <span id="articleCountBadge" class="text-xs font-bold text-slate-500 hidden sm:inline shrink-0">
      Showing all <?php echo count($articles); ?> published guides
    </span>
  </div>

  <!-- FEATURED SPOTLIGHT ARTICLE (Hero Card) -->
  <?php if (!empty($featuredArticle)): ?>
  <section id="featuredArticleBox" class="bg-white rounded-xl sm:rounded-3xl border border-slate-200 hover:border-joule-blue overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 w-full max-w-full">
    <div class="grid grid-cols-1 lg:grid-cols-12 items-stretch">
      <!-- Image Column (5 cols) -->
      <div class="lg:col-span-5 relative w-full bg-slate-950 overflow-hidden shrink-0 blog-featured-img-container h-48 sm:h-64 lg:h-full" style="min-height: 190px; max-height: 240px;">
        <a href="/blog/<?php echo htmlspecialchars($featuredArticle['slug']); ?>" class="block w-full h-full">
          <img 
            src="<?php echo htmlspecialchars($featuredArticle['featured_image']); ?>" 
            alt="<?php echo htmlspecialchars($featuredArticle['title']); ?>" 
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
            style="height: 100%; width: 100%; object-fit: cover; display: block;"
            loading="eager"
          >
          <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-black/30 pointer-events-none"></div>
          <span class="absolute top-3 left-3 bg-amber-400 text-slate-950 text-[9px] sm:text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md shadow-md flex items-center gap-1">
            <i class="fa-solid fa-star text-[10px]"></i>
            <span>Must-Read Buyer Handbook</span>
          </span>
        </a>
      </div>

      <!-- Text Column (7 cols) -->
      <div class="lg:col-span-7 p-4 sm:p-7 lg:p-9 flex flex-col justify-between space-y-3.5 min-w-0 w-full">
        <div class="space-y-2.5 min-w-0">
          <div class="flex items-center gap-2 text-[11px] sm:text-xs font-bold text-slate-500 flex-wrap">
            <span class="bg-blue-50 text-joule-blue px-2 py-0.5 rounded-md border border-blue-200 font-black text-[10px] sm:text-xs shrink-0">
              <?php echo htmlspecialchars($featuredArticle['category']); ?>
            </span>
            <span>&bull;</span>
            <span class="shrink-0"><i class="fa-regular fa-clock mr-1 text-slate-400"></i><?php echo htmlspecialchars($featuredArticle['read_time']); ?></span>
            <span>&bull;</span>
            <span class="shrink-0"><i class="fa-regular fa-calendar mr-1 text-slate-400"></i><?php echo htmlspecialchars($featuredArticle['published_date']); ?></span>
          </div>

          <h2 class="text-base sm:text-xl lg:text-2xl font-black text-slate-900 tracking-tight leading-snug hover:text-joule-brightblue transition-colors break-words">
            <a href="/blog/<?php echo htmlspecialchars($featuredArticle['slug']); ?>">
              <?php echo htmlspecialchars($featuredArticle['title']); ?>
            </a>
          </h2>

          <p class="text-slate-600 text-xs sm:text-sm leading-relaxed line-clamp-2 sm:line-clamp-3 break-words">
            <?php echo htmlspecialchars($featuredArticle['excerpt']); ?>
          </p>

          <div class="flex items-center gap-1 flex-wrap pt-1">
            <?php foreach ($featuredArticle['tags'] as $tag): ?>
              <span class="text-[9px] sm:text-[10px] bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-semibold break-words">
                #<?php echo htmlspecialchars($tag); ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pt-3 border-t border-slate-100 gap-3">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-black text-xs flex items-center justify-center shadow-xs shrink-0">
              JMH
            </div>
            <div>
              <div class="text-xs font-bold text-slate-900"><?php echo htmlspecialchars($featuredArticle['author']); ?></div>
              <div class="text-[10px] text-slate-400 font-semibold"><?php echo htmlspecialchars($featuredArticle['author_role']); ?></div>
            </div>
          </div>

          <a 
            href="/blog/<?php echo htmlspecialchars($featuredArticle['slug']); ?>" 
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-joule-navy hover:bg-blue-900 text-joule-yellow text-xs font-black px-4 py-2.5 rounded-xl transition-all shadow-sm group cursor-pointer text-center"
          >
            <span>Read Complete Guide</span>
            <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
          </a>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ARTICLE CARDS GRID: Highly Responsive 1, 2, or 3 columns -->
  <div>
    <div class="flex items-center justify-between mb-4 sm:mb-6">
      <h3 class="text-base sm:text-lg lg:text-xl font-black text-slate-900 flex items-center gap-2">
        <i class="fa-solid fa-layer-group text-joule-brightblue"></i>
        <span>Used Industrial Machinery Engineering Guides</span>
      </h3>
    </div>

    <div id="articlesGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-6 w-full max-w-full">
      <?php foreach ($articles as $a): 
        $keywordsSearch = strtolower($a['title'] . ' ' . $a['excerpt'] . ' ' . $a['category'] . ' ' . implode(' ', $a['tags']) . ' ' . implode(' ', $a['target_keywords'] ?? []));
      ?>
        <article 
          class="article-card w-full max-w-full min-w-0 bg-white rounded-xl sm:rounded-2xl border border-slate-200 hover:border-joule-brightblue overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between group"
          data-category="<?php echo htmlspecialchars(strtolower($a['category'])); ?>"
          data-search="<?php echo htmlspecialchars($keywordsSearch); ?>"
        >
          <div class="w-full min-w-0">
            <!-- Image Container -->
            <div class="relative w-full bg-slate-950 overflow-hidden shrink-0 blog-card-img-container h-44 sm:h-48" style="height: 180px; max-height: 180px; min-height: 180px;">
              <a href="/blog/<?php echo htmlspecialchars($a['slug']); ?>" class="block w-full h-full">
                <img 
                  src="<?php echo htmlspecialchars($a['featured_image']); ?>" 
                  alt="<?php echo htmlspecialchars($a['title']); ?>" 
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                  style="height: 100%; width: 100%; object-fit: cover; display: block;"
                  loading="lazy"
                  decoding="async"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-transparent to-black/30 pointer-events-none"></div>
                
                <!-- Category Badge -->
                <div class="absolute top-2.5 left-2.5 sm:top-3 sm:left-3">
                  <span class="bg-joule-blue text-white text-[9px] sm:text-[10px] font-black uppercase tracking-wider px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md shadow-sm">
                    <?php echo htmlspecialchars($a['category']); ?>
                  </span>
                </div>

                <!-- Reading Time -->
                <div class="absolute bottom-2.5 right-2.5 sm:bottom-3 sm:right-3 bg-slate-950/80 backdrop-blur-md text-white text-[9px] sm:text-[10px] font-bold px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md border border-white/20 flex items-center gap-1">
                  <i class="fa-regular fa-clock text-joule-yellow"></i>
                  <span><?php echo htmlspecialchars($a['read_time']); ?></span>
                </div>
              </a>
            </div>

            <!-- Card Body -->
            <div class="p-3.5 sm:p-5 space-y-2 sm:space-y-3 min-w-0 w-full">
              <div class="text-[10px] sm:text-[11px] font-bold text-slate-400 flex items-center gap-1.5">
                <i class="fa-regular fa-calendar text-slate-400"></i>
                <span><?php echo htmlspecialchars($a['published_date']); ?></span>
              </div>

              <h4 class="font-black text-slate-900 text-xs sm:text-sm md:text-base leading-snug group-hover:text-joule-brightblue transition-colors line-clamp-2 break-words">
                <a href="/blog/<?php echo htmlspecialchars($a['slug']); ?>">
                  <?php echo htmlspecialchars($a['short_title'] ?? $a['title']); ?>
                </a>
              </h4>

              <p class="text-xs text-slate-600 leading-relaxed line-clamp-2 break-words">
                <?php echo htmlspecialchars($a['excerpt']); ?>
              </p>

              <!-- Tags -->
              <div class="flex items-center gap-1 flex-wrap pt-0.5">
                <?php foreach (array_slice($a['tags'], 0, 3) as $t): ?>
                  <span class="text-[9px] bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-semibold break-words">
                    #<?php echo htmlspecialchars($t); ?>
                  </span>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- Card Footer -->
          <div class="px-3.5 sm:px-5 py-2.5 sm:py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[10px] sm:text-[11px] font-bold text-slate-500 truncate max-w-[140px] sm:max-w-none">
              By <?php echo htmlspecialchars($a['author']); ?>
            </span>

            <a 
              href="/blog/<?php echo htmlspecialchars($a['slug']); ?>" 
              class="text-xs font-black text-joule-brightblue hover:text-blue-800 flex items-center gap-1 group-hover:translate-x-0.5 transition-transform"
            >
              <span>Read Guide</span>
              <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Empty Results Fallback -->
  <div id="noResultsBox" class="hidden text-center py-16 bg-white rounded-3xl border border-slate-200 p-8 space-y-3">
    <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mx-auto text-2xl">
      <i class="fa-solid fa-magnifying-glass"></i>
    </div>
    <h4 class="text-base font-black text-slate-900">No Used Machinery Guides Match Your Search</h4>
    <p class="text-xs text-slate-500 max-w-md mx-auto">
      Try adjusting your keywords (e.g. "used power press", "used cnc", "lathe", "molding", "valuation") or reset the filter to view all guides.
    </p>
    <button 
      type="button" 
      onclick="resetBlogFilter()" 
      class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition-all cursor-pointer inline-flex items-center gap-1.5 mt-2"
    >
      <i class="fa-solid fa-rotate-left"></i>
      <span>Reset Filter</span>
    </button>
  </div>

  <!-- B2B PLANT EQUIPMENT RFQ & SOURCING CTA -->
  <section class="bg-gradient-to-r from-joule-navy via-blue-900 to-[#0a192f] text-white rounded-3xl p-6 sm:p-10 border border-blue-800 shadow-xl relative overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
      <div class="lg:col-span-8 space-y-2">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase tracking-wider">
          <i class="fa-solid fa-bolt"></i>
          <span>B2B Machinery Sourcing Desk</span>
        </div>
        <h3 class="text-xl sm:text-2xl font-black text-white">
          Looking to Buy Second Hand / Used / Old / Imported Industrial Machinery?
        </h3>
        <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-2xl">
          Connect with verified industrial machinery listings across power presses, CNC lathes, cold formers, and injection molding equipment through Joule Machine Hub.
        </p>
      </div>

      <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3 justify-end">
        <button 
          type="button" 
          onclick="openRequirementModal()" 
          class="bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black py-3 px-6 rounded-xl text-xs sm:text-sm shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer border border-amber-300 text-center"
        >
          <i class="fa-solid fa-paper-plane"></i>
          <span>Post an Equipment RFQ</span>
        </button>
        <a 
          href="/category" 
          class="bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold py-3 px-6 rounded-xl text-xs sm:text-sm text-center transition-all flex items-center justify-center gap-2"
        >
          <i class="fa-solid fa-boxes-stacked"></i>
          <span>Browse 13 Used Machine Hubs</span>
        </a>
      </div>
    </div>
  </section>

</main>

<script>
  let activeCategory = 'all';

  function filterByCategory(cat, btn) {
    activeCategory = cat.toLowerCase();
    document.querySelectorAll('.blog-cat-btn').forEach(b => {
      b.classList.remove('bg-joule-blue', 'text-white', 'shadow-md', 'shadow-blue-600/30', 'ring-2', 'ring-blue-600');
      b.classList.add('bg-white', 'text-slate-700', 'border', 'border-slate-200');
    });
    btn.classList.add('bg-joule-blue', 'text-white', 'shadow-md', 'shadow-blue-600/30', 'ring-2', 'ring-blue-600');
    btn.classList.remove('bg-white', 'text-slate-700', 'border', 'border-slate-200');
    filterArticles();
  }

  function setSearchFilter(keyword) {
    const input = document.getElementById('blogSearchInput');
    input.value = keyword;
    filterArticles();
    // Scroll smoothly to results
    const grid = document.getElementById('articlesGrid');
    if (grid) {
      grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function filterArticles() {
    const query = (document.getElementById('blogSearchInput').value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.article-card');
    let visibleCount = 0;

    cards.forEach(card => {
      const cardCat = card.getAttribute('data-category');
      const cardSearch = card.getAttribute('data-search');
      
      const matchCat = (activeCategory === 'all' || cardCat === activeCategory);
      const matchQuery = (!query || cardSearch.includes(query));

      if (matchCat && matchQuery) {
        card.style.display = 'flex';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    const noResultsBox = document.getElementById('noResultsBox');
    if (visibleCount === 0) {
      noResultsBox.classList.remove('hidden');
    } else {
      noResultsBox.classList.add('hidden');
    }

    const badge = document.getElementById('articleCountBadge');
    if (badge) {
      badge.textContent = `Showing ${visibleCount} matching guides`;
    }
  }

  function resetBlogFilter() {
    document.getElementById('blogSearchInput').value = '';
    const firstBtn = document.querySelector('.blog-cat-btn');
    if (firstBtn) filterByCategory('All', firstBtn);
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
