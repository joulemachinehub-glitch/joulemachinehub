<?php
// Joule Machine Hub - Dedicated Blog Article Reader & Technical Inspection Guide
// High-conversion "Used Machines" SEO & Responsive Engineering Layout
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';
require_once __DIR__ . '/data/articles.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$article = getArticleBySlug($slug);

// 404 Handling: If article slug is not found
if (!$article) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Canonical and SEO tags
$pageTitle = $article['meta_title'] ?? ($article['title'] . " | Joule Machine Hub");
$pageDesc = $article['meta_desc'] ?? $article['excerpt'];
$canonicalUrl = "https://joulemachinehub.com/blog/" . $article['slug'];
$pageRobots = "index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1";
$ogType = "article";
$ogTitle = $pageTitle;
$ogDesc = $pageDesc;
$ogImage = $article['featured_image'];

$relatedArticles = getRelatedArticles($article['slug'], 3);

// Find related machines from inventory matching this article's focus
$relatedMachines = [];
$catFilter = $article['related_categories'] ?? [];
foreach ($machines as $m) {
    if (in_array($m['category'], $catFilter)) {
        $relatedMachines[] = $m;
        if (count($relatedMachines) >= 4) break;
    }
}
if (count($relatedMachines) < 4) {
    foreach ($machines as $m) {
        if (!in_array($m, $relatedMachines, true)) {
            $relatedMachines[] = $m;
            if (count($relatedMachines) >= 4) break;
        }
    }
}

// Dynamically extract Table of Contents from H2 tags and add id anchors
$toc = [];
$processedContent = preg_replace_callback('/<h2([^>]*)>(.*?)<\/h2>/i', function($matches) use (&$toc) {
    $attrs = $matches[1];
    $title = strip_tags($matches[2]);
    $id = '';
    if (preg_match('/id=["\']([^"\']+)["\']/i', $attrs, $idMatch)) {
        $id = $idMatch[1];
    } else {
        $id = 'guide-sec-' . (count($toc) + 1);
        $attrs .= ' id="' . $id . '"';
    }
    $toc[] = [
        'id' => $id,
        'title' => $title
    ];
    return '<h2' . $attrs . ' class="scroll-mt-24">' . $matches[2] . '</h2>';
}, $article['content']);

// Make tables responsive inside article content
$processedContent = preg_replace(
    '/<table([^>]*)>/i',
    '<div class="blog-reader-table-wrap"><div class="table-swipe-hint sm:hidden"><i class="fa-solid fa-arrows-left-right text-slate-400"></i> Swipe sideways to view full table &rarr;</div><table$1>',
    $processedContent
);
$processedContent = str_replace('</table>', '</table></div>', $processedContent);

// Schemas
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
            'name' => 'Used Machinery Guides & Blog',
            'item' => 'https://joulemachinehub.com/blog'
        ],
        [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $article['title'],
            'item' => $canonicalUrl
        ]
    ]
];

$schemaArticle = [
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $article['title'],
    'description' => $article['excerpt'],
    'image' => 'https://joulemachinehub.com' . $article['featured_image'],
    'datePublished' => $article['iso_date'],
    'dateModified' => $article['iso_date'],
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => $canonicalUrl
    ],
    'author' => [
        '@type' => 'Organization',
        'name' => $article['author'],
        'url' => 'https://joulemachinehub.com'
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'Joule Machine Hub',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => 'https://joulemachinehub.com/assets/images/logo.png'
        ]
    ]
];

if (!empty($article['faqs'])) {
    $schemaFaq = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(function($f) {
            return [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags($f['a'])
                ]
            ];
        }, $article['faqs'])
    ];
}

$shareTitle = urlencode($article['title'] . " - Joule Machine Hub");
$shareUrl = urlencode($canonicalUrl);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Reading Progress Bar -->
<div id="readingProgressBar" class="fixed top-0 left-0 h-1 bg-joule-yellow z-50 transition-all duration-75 shadow-xs" style="width: 0%;" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>

<script type="application/ld+json">
<?php echo json_encode($schemaBreadcrumb, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>
<script type="application/ld+json">
<?php echo json_encode($schemaArticle, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>
<?php if (!empty($schemaFaq)): ?>
<script type="application/ld+json">
<?php echo json_encode($schemaFaq, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>
<?php endif; ?>

<!-- BREADCRUMBS -->
<nav class="bg-white border-b border-slate-200 py-2.5 px-3.5 sm:px-4 text-[11px] sm:text-xs font-semibold text-slate-500" aria-label="Breadcrumb">
  <div class="max-w-7xl mx-auto flex items-center gap-1.5 sm:gap-2 flex-wrap">
    <a href="/" class="hover:text-joule-brightblue flex items-center gap-1 transition-colors">
      <i class="fa-solid fa-house text-slate-400 text-[10px]"></i>
      <span>Home</span>
    </a>
    <i class="fa-solid fa-chevron-right text-[8px] text-slate-300"></i>
    <a href="/blog" class="hover:text-joule-brightblue transition-colors">
      <span>Used Machines Blog &amp; Guides</span>
    </a>
    <i class="fa-solid fa-chevron-right text-[8px] text-slate-300"></i>
    <span class="text-slate-900 font-bold truncate max-w-[200px] sm:max-w-md">
      <?php echo htmlspecialchars($article['short_title'] ?? $article['title']); ?>
    </span>
  </div>
</nav>

<!-- ARTICLE HERO / HEADER -->
<header class="bg-gradient-to-b from-[#0a192f] via-joule-navy to-[#0c1f3d] text-white py-6 sm:py-12 md:py-16 px-3.5 sm:px-4">
  <div class="max-w-4xl mx-auto space-y-4 sm:space-y-5">
    
    <!-- Badges Row -->
    <div class="flex items-center gap-2 text-[11px] sm:text-xs font-bold text-slate-300 flex-wrap">
      <span class="bg-joule-yellow text-slate-950 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-md font-black uppercase tracking-wider text-[10px] sm:text-[11px] shadow-xs">
        <?php echo htmlspecialchars($article['category']); ?>
      </span>
      <span class="text-slate-500">&bull;</span>
      <span class="inline-flex items-center gap-1 text-slate-200">
        <i class="fa-regular fa-clock text-joule-yellow"></i>
        <span><?php echo htmlspecialchars($article['read_time']); ?></span>
      </span>
      <span class="text-slate-500">&bull;</span>
      <span class="inline-flex items-center gap-1 text-slate-300">
        <i class="fa-regular fa-calendar text-slate-400"></i>
        <span><?php echo htmlspecialchars($article['published_date']); ?></span>
      </span>
      <span class="hidden sm:inline text-slate-500">&bull;</span>
      <span class="hidden sm:inline-flex items-center gap-1 text-emerald-400 bg-emerald-950/60 border border-emerald-500/30 px-2 py-0.5 rounded text-[10px]">
        <i class="fa-solid fa-shield-check"></i>
        <span>Verified Engineering Desk</span>
      </span>
    </div>

    <!-- Main Title -->
    <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-white tracking-tight leading-tight sm:leading-snug">
      <?php echo htmlspecialchars($article['title']); ?>
    </h1>

    <!-- Excerpt / Subheading -->
    <p class="text-slate-200 text-xs sm:text-sm md:text-base leading-relaxed max-w-3xl font-normal">
      <?php echo htmlspecialchars($article['excerpt']); ?>
    </p>

    <!-- Author Badge & Quick Share Bar -->
    <div class="pt-4 border-t border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-joule-blue text-white font-black text-xs sm:text-sm flex items-center justify-center shadow-md border border-white/20 shrink-0">
          JMH
        </div>
        <div>
          <div class="text-xs sm:text-sm font-black text-white"><?php echo htmlspecialchars($article['author']); ?></div>
          <div class="text-[10px] sm:text-[11px] text-slate-300 font-medium"><?php echo htmlspecialchars($article['author_role']); ?></div>
        </div>
      </div>

      <!-- Quick Social Share Buttons -->
      <div class="flex items-center gap-1.5 flex-wrap">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1 hidden sm:inline">Share:</span>
        <a 
          href="https://api.whatsapp.com/send?text=<?php echo $shareTitle; ?>%20<?php echo $shareUrl; ?>" 
          target="_blank" 
          rel="noopener noreferrer" 
          class="flex-1 sm:flex-none h-8 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center gap-1.5 text-xs transition-colors shadow-sm"
          title="Share on WhatsApp"
        >
          <i class="fa-brands fa-whatsapp"></i>
          <span class="sm:hidden text-[11px] font-bold">WhatsApp</span>
        </a>
        <a 
          href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $shareUrl; ?>" 
          target="_blank" 
          rel="noopener noreferrer" 
          class="flex-1 sm:flex-none h-8 px-3 rounded-lg bg-[#0077b5] hover:bg-blue-600 text-white flex items-center justify-center gap-1.5 text-xs transition-colors shadow-sm"
          title="Share on LinkedIn"
        >
          <i class="fa-brands fa-linkedin-in"></i>
          <span class="sm:hidden text-[11px] font-bold">LinkedIn</span>
        </a>
        <button 
          type="button" 
          onclick="copyGuideLink('<?php echo $canonicalUrl; ?>')" 
          id="copyLinkBtnTop"
          class="flex-1 sm:flex-none px-3 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white text-[11px] font-bold flex items-center justify-center gap-1 transition-colors border border-white/15"
          title="Copy Link to Clipboard"
        >
          <i class="fa-regular fa-copy text-xs"></i>
          <span class="copyText">Copy</span>
        </button>
      </div>
    </div>

  </div>
</header>

<!-- ARTICLE MAIN BODY CONTAINER -->
<main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-10 overflow-hidden w-full max-w-full">
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start w-full max-w-full">
    
    <!-- LEFT COLUMN: Article Body (8 cols) -->
    <div class="lg:col-span-8 space-y-6 sm:space-y-8 min-w-0 w-full">
      
      <!-- Featured Image Banner -->
      <div class="relative w-full bg-slate-950 rounded-xl sm:rounded-2xl overflow-hidden border border-slate-200 shadow-md blog-post-hero-img-container h-52 sm:h-72 md:h-96" style="height: 220px; max-height: 380px;">
        <img 
          src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
          alt="<?php echo htmlspecialchars($article['title']); ?>" 
          class="w-full h-full object-cover"
          style="height: 100%; width: 100%; object-fit: cover; display: block;"
          loading="eager"
        >
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-transparent to-black/20 pointer-events-none"></div>
        <div class="absolute bottom-2.5 left-2.5 sm:bottom-3 sm:left-3 bg-slate-950/85 backdrop-blur-md text-white text-[10px] sm:text-[11px] font-bold px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg border border-white/20 flex items-center gap-1.5">
          <i class="fa-solid fa-camera text-joule-yellow text-[10px]"></i>
          <span>Verified Industrial Inspection</span>
        </div>
      </div>

      <!-- MOBILE COLLAPSIBLE TABLE OF CONTENTS (Visible on Mobile & Tablet < 1024px) -->
      <?php if (!empty($toc)): ?>
      <div class="lg:hidden bg-blue-50/70 border border-blue-200 rounded-xl p-3 sm:p-4">
        <details class="group">
          <summary class="flex items-center justify-between cursor-pointer list-none font-black text-slate-900 text-xs sm:text-sm">
            <span class="flex items-center gap-2">
              <i class="fa-solid fa-list-ol text-joule-blue"></i>
              <span>In This Guide (Table of Contents)</span>
            </span>
            <span class="text-[11px] text-joule-blue font-bold group-open:hidden">Show &darr;</span>
            <span class="text-[11px] text-joule-blue font-bold hidden group-open:inline">Hide &uarr;</span>
          </summary>
          <div class="pt-3 mt-2.5 border-t border-blue-200/60 space-y-1 text-xs">
            <?php foreach ($toc as $idx => $tItem): ?>
              <a 
                href="#<?php echo htmlspecialchars($tItem['id']); ?>" 
                class="flex items-start gap-2 text-slate-700 hover:text-joule-blue font-medium py-1 hover:translate-x-1 transition-all"
              >
                <span class="w-4 h-4 rounded-full bg-blue-100 text-joule-blue font-bold text-[9px] flex items-center justify-center shrink-0 mt-0.5">
                  <?php echo ($idx + 1); ?>
                </span>
                <span class="leading-tight"><?php echo htmlspecialchars($tItem['title']); ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </details>
      </div>
      <?php endif; ?>

      <!-- Article Rich Content -->
      <article class="bg-white rounded-xl sm:rounded-2xl border border-slate-200/90 p-4 sm:p-7 md:p-9 shadow-xs blog-reader-body">
        <div class="article-inner-content">
          <?php echo $processedContent; ?>
        </div>

        <!-- In-Article Direct Sourcing Banner -->
        <div class="my-6 sm:my-8 p-4 sm:p-6 bg-gradient-to-r from-slate-900 to-joule-navy text-white rounded-xl border border-blue-900 shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="space-y-1 text-left">
            <div class="inline-flex items-center gap-1 text-joule-yellow text-[10px] sm:text-[11px] font-black uppercase tracking-wider">
              <i class="fa-solid fa-bolt"></i>
              <span>Equipment Sourcing Desk</span>
            </div>
            <h4 class="text-sm sm:text-base md:text-lg font-black text-white">Looking for Verified Used Industrial Machinery?</h4>
            <p class="text-xs text-slate-300">Submit your technical requirement. Our B2B machinery desk connects you directly with matching available inventory.</p>
          </div>
          <button 
            type="button" 
            onclick="openRequirementModal()" 
            class="w-full sm:w-auto bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black py-2.5 px-5 rounded-xl text-xs whitespace-nowrap shadow-md transition-all cursor-pointer border border-amber-300 text-center"
          >
            Post Machine RFQ
          </button>
        </div>

        <!-- Tags Bar -->
        <div class="pt-6 mt-8 border-t border-slate-200 flex items-center gap-2 flex-wrap">
          <span class="text-xs font-black text-slate-700 uppercase tracking-wider mr-1">Target Keywords:</span>
          <?php foreach ($article['tags'] as $tag): ?>
            <a 
              href="/blog?q=<?php echo urlencode($tag); ?>" 
              class="text-xs bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-joule-blue px-3 py-1.5 rounded-lg font-semibold transition-colors border border-slate-200/80"
            >
              #<?php echo htmlspecialchars($tag); ?>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- Bottom Share Bar -->
        <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between flex-wrap gap-3">
          <div class="text-xs font-black text-slate-700">Found this technical checklist valuable? Share with your plant engineering team:</div>
          <div class="flex items-center gap-1.5">
            <a 
              href="https://api.whatsapp.com/send?text=<?php echo $shareTitle; ?>%20<?php echo $shareUrl; ?>" 
              target="_blank" 
              rel="noopener noreferrer" 
              class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs"
            >
              <i class="fa-brands fa-whatsapp text-sm"></i>
              <span>WhatsApp</span>
            </a>
            <a 
              href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $shareUrl; ?>" 
              target="_blank" 
              rel="noopener noreferrer" 
              class="px-3 py-1.5 rounded-lg bg-[#0077b5] hover:bg-blue-600 text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs"
            >
              <i class="fa-brands fa-linkedin text-sm"></i>
              <span>LinkedIn</span>
            </a>
            <button 
              type="button" 
              onclick="copyGuideLink('<?php echo $canonicalUrl; ?>')" 
              class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5 transition-colors border border-slate-300"
            >
              <i class="fa-regular fa-copy text-xs"></i>
              <span class="copyText">Copy Link</span>
            </button>
          </div>
        </div>
      </article>

      <!-- FREQUENTLY ASKED QUESTIONS ACCORDION -->
      <?php if (!empty($article['faqs'])): ?>
      <section class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-4">
        <div class="flex items-center gap-2">
          <div class="w-9 h-9 rounded-xl bg-blue-50 text-joule-blue flex items-center justify-center font-bold text-base shadow-xs">
            <i class="fa-solid fa-circle-question"></i>
          </div>
          <div>
            <h3 class="text-base sm:text-lg font-black text-slate-900">Frequently Asked Questions: Used Machinery Inspection &amp; Buying</h3>
            <p class="text-[11px] text-slate-500 font-medium">Key engineering, maintenance, and commercial takeaways for plant owners and procurement engineers</p>
          </div>
        </div>

        <div class="space-y-3 pt-2">
          <?php foreach ($article['faqs'] as $fIdx => $faq): ?>
            <details class="group bg-slate-50 hover:bg-slate-100/70 rounded-xl border border-slate-200 open:bg-blue-50/40 open:border-blue-300 transition-colors">
              <summary class="flex items-center justify-between p-4 cursor-pointer font-bold text-xs sm:text-sm text-slate-900 select-none">
                <span class="flex items-center gap-2.5">
                  <span class="w-5 h-5 rounded-full bg-blue-100 text-joule-blue text-[10px] font-black flex items-center justify-center shrink-0">
                    Q<?php echo ($fIdx + 1); ?>
                  </span>
                  <span><?php echo htmlspecialchars($faq['q']); ?></span>
                </span>
                <i class="fa-solid fa-chevron-down text-xs text-slate-400 group-open:rotate-180 transition-transform ml-2"></i>
              </summary>
              <div class="px-5 pb-4 text-xs sm:text-sm text-slate-700 leading-relaxed border-t border-slate-200/60 pt-3">
                <?php echo htmlspecialchars($faq['a']); ?>
              </div>
            </details>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <!-- AUTHOR BIO BOX -->
      <section class="bg-gradient-to-br from-slate-50 to-blue-50/50 rounded-2xl border border-slate-200 p-6 flex flex-col sm:flex-row items-center sm:items-start gap-4 shadow-xs">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-joule-navy to-blue-900 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-md border-2 border-white">
          JMH
        </div>
        <div class="space-y-1.5 text-center sm:text-left">
          <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
            <h4 class="font-black text-slate-900 text-sm sm:text-base"><?php echo htmlspecialchars($article['author']); ?></h4>
            <span class="bg-blue-100 text-blue-800 text-[10px] font-black px-2 py-0.5 rounded">Verified Industrial Desk</span>
            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2 py-0.5 rounded">B2B Portal</span>
          </div>
          <p class="text-xs text-slate-500 font-semibold"><?php echo htmlspecialchars($article['author_role']); ?></p>
          <p class="text-xs text-slate-600 leading-relaxed">
            The Joule Machine Hub technical desk inspects second-hand and surplus industrial equipment across Japan, Germany, Switzerland, South Korea, China, and India. Every guide is drafted from practical plant floor teardowns, OEM plate verifications, and international shipping protocols.
          </p>
        </div>
      </section>

      <!-- RELATED ARTICLES IN SAME OR ADJACENT TOPIC -->
      <?php if (!empty($relatedArticles)): ?>
      <section class="space-y-4 pt-2">
        <div class="flex items-center justify-between">
          <h3 class="text-base sm:text-lg font-black text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-book-bookmark text-joule-brightblue"></i>
            <span>Recommended Used Machinery Guides</span>
          </h3>
          <a href="/blog" class="text-xs font-black text-joule-brightblue hover:text-blue-800 flex items-center gap-1">
            <span>View All Guides</span>
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
          </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <?php foreach ($relatedArticles as $ra): ?>
            <a 
              href="/blog/<?php echo htmlspecialchars($ra['slug']); ?>" 
              class="bg-white rounded-xl border border-slate-200 hover:border-joule-blue p-3.5 flex flex-col justify-between transition-all hover:shadow-md group"
            >
              <div>
                <div class="h-28 sm:h-32 w-full bg-slate-900 rounded-lg overflow-hidden mb-2.5 relative">
                  <img src="<?php echo htmlspecialchars($ra['featured_image']); ?>" alt="<?php echo htmlspecialchars($ra['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform" loading="lazy">
                  <span class="absolute top-1.5 left-1.5 bg-slate-950/80 text-white text-[9px] font-bold px-2 py-0.5 rounded">
                    <?php echo htmlspecialchars($ra['category']); ?>
                  </span>
                </div>
                <h4 class="text-xs font-black text-slate-900 group-hover:text-joule-blue transition-colors line-clamp-2 leading-snug">
                  <?php echo htmlspecialchars($ra['short_title'] ?? $ra['title']); ?>
                </h4>
              </div>
              <div class="pt-2 text-[10px] text-slate-400 font-semibold flex items-center justify-between border-t border-slate-100 mt-2">
                <span><?php echo htmlspecialchars($ra['read_time']); ?></span>
                <span class="text-joule-blue font-bold group-hover:translate-x-0.5 transition-transform">Read &rarr;</span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

    </div>

    <!-- RIGHT COLUMN: Sticky Sidebar & Live Machinery Inventory (4 cols) -->
    <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
      
      <!-- DESKTOP STICKY TABLE OF CONTENTS -->
      <?php if (!empty($toc)): ?>
      <div class="hidden lg:block bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
        <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2.5">
          <i class="fa-solid fa-list-check text-joule-blue"></i>
          <span>In This Engineering Guide</span>
        </h4>
        <nav class="space-y-1.5 text-xs max-h-72 overflow-y-auto pr-1">
          <?php foreach ($toc as $idx => $tItem): ?>
            <a 
              href="#<?php echo htmlspecialchars($tItem['id']); ?>" 
              class="flex items-start gap-2 py-1 px-1.5 rounded-lg text-slate-600 hover:text-joule-blue hover:bg-blue-50/70 font-semibold transition-colors"
            >
              <span class="text-[10px] font-black text-slate-400 mt-0.5 shrink-0"><?php echo ($idx + 1); ?>.</span>
              <span class="leading-tight"><?php echo htmlspecialchars($tItem['title']); ?></span>
            </a>
          <?php endforeach; ?>
        </nav>
      </div>
      <?php endif; ?>

      <!-- Direct Equipment Sourcing RFQ Box -->
      <div class="bg-gradient-to-br from-joule-navy to-blue-950 text-white rounded-2xl p-5 sm:p-6 border border-blue-800 shadow-md space-y-3.5">
        <div class="inline-flex items-center gap-1.5 bg-joule-yellow text-slate-950 text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-md">
          <i class="fa-solid fa-bolt"></i>
          <span>B2B Machinery Sourcing</span>
        </div>
        <h4 class="text-base font-black text-white leading-snug">
          Need Verified Used Machinery for Your Plant?
        </h4>
        <p class="text-xs text-slate-300 leading-relaxed">
          Connect with verified machinery listings. Submit your machine requirements to receive structured pricing from our mediated B2B desk.
        </p>
        <button 
          type="button" 
          onclick="openRequirementModal()" 
          class="w-full bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black py-2.5 px-4 rounded-xl text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer border border-amber-300"
        >
          <i class="fa-solid fa-paper-plane"></i>
          <span>Post an Equipment RFQ</span>
        </button>
      </div>

      <!-- Relevant Machinery for Sale Carousel / Cards -->
      <?php if (!empty($relatedMachines)): ?>
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3.5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
          <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-industry text-joule-brightblue"></i>
            <span>Verified Machinery for Sale</span>
          </h4>
          <a href="/category" class="text-[10px] font-bold text-joule-blue hover:underline">View All</a>
        </div>

        <div class="space-y-3">
          <?php foreach ($relatedMachines as $rm): ?>
            <a 
              href="<?php echo htmlspecialchars(getMachineUrl($rm)); ?>" 
              class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-200 group"
            >
              <div class="w-16 h-14 rounded-lg bg-slate-900 overflow-hidden shrink-0 relative">
                <img src="<?php echo htmlspecialchars('/' . ltrim($rm['images'][0] ?? '', '/')); ?>" alt="<?php echo htmlspecialchars($rm['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform" loading="lazy">
              </div>
              <div class="min-w-0 flex-1">
                <div class="text-[10px] font-bold text-joule-blue uppercase tracking-tight"><?php echo htmlspecialchars($rm['brand']); ?></div>
                <div class="text-xs font-black text-slate-900 truncate group-hover:text-joule-blue transition-colors">
                  <?php echo htmlspecialchars($rm['title']); ?>
                </div>
                <div class="text-[10px] text-slate-500 font-semibold flex items-center gap-1 mt-0.5">
                  <i class="fa-solid fa-location-dot text-slate-400 text-[9px]"></i>
                  <span><?php echo htmlspecialchars($rm['city'] . ', ' . $rm['country']); ?></span>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Quick Category Links -->
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
        <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
          <i class="fa-solid fa-boxes-stacked text-amber-500"></i>
          <span>Popular Categories</span>
        </h4>
        <div class="grid grid-cols-1 gap-1 text-xs">
          <a href="/category/used-presses-stamping" class="py-1.5 px-2.5 rounded-lg hover:bg-slate-50 font-bold text-slate-700 hover:text-joule-blue flex items-center justify-between transition-colors">
            <span>Power Presses &amp; Stamping</span>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
          </a>
          <a href="/category/used-cold-formers-headers" class="py-1.5 px-2.5 rounded-lg hover:bg-slate-50 font-bold text-slate-700 hover:text-joule-blue flex items-center justify-between transition-colors">
            <span>Cold Formers &amp; Headers</span>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
          </a>
          <a href="/category/used-lathes-turning" class="py-1.5 px-2.5 rounded-lg hover:bg-slate-50 font-bold text-slate-700 hover:text-joule-blue flex items-center justify-between transition-colors">
            <span>Lathes &amp; Turning Centers</span>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
          </a>
          <a href="/category/used-cnc-machining-centers" class="py-1.5 px-2.5 rounded-lg hover:bg-slate-50 font-bold text-slate-700 hover:text-joule-blue flex items-center justify-between transition-colors">
            <span>CNC Machining Centers</span>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
          </a>
          <a href="/category" class="py-2 px-2.5 rounded-lg bg-blue-50 text-joule-blue font-black flex items-center justify-between mt-1 hover:bg-blue-100 transition-colors">
            <span>Explore All 13 Categories</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
          </a>
        </div>
      </div>

    </aside>

  </div>
</main>

<!-- In-Page JavaScript: Reading Progress Bar & Copy Link Toast -->
<script>
window.addEventListener('scroll', function() {
  var doc = document.documentElement;
  var top = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
  var height = doc.scrollHeight - doc.clientHeight;
  var percent = height > 0 ? (top / height) * 100 : 0;
  var bar = document.getElementById('readingProgressBar');
  if (bar) {
    bar.style.width = Math.min(100, Math.max(0, percent)) + '%';
  }
}, { passive: true });

function copyGuideLink(url) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url).then(function() {
      showCopyToast();
    }).catch(function() {
      fallbackCopy(url);
    });
  } else {
    fallbackCopy(url);
  }
}

function fallbackCopy(url) {
  var ta = document.createElement('textarea');
  ta.value = url;
  ta.style.position = 'fixed';
  ta.style.opacity = '0';
  document.body.appendChild(ta);
  ta.select();
  try {
    document.execCommand('copy');
    showCopyToast();
  } catch (e) {}
  document.body.removeChild(ta);
}

function showCopyToast() {
  var labels = document.querySelectorAll('.copyText');
  labels.forEach(function(el) {
    var orig = el.textContent;
    el.textContent = 'Copied!';
    setTimeout(function() {
      el.textContent = orig;
    }, 2000);
  });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
