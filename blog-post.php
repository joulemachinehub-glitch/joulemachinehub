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

// Smart Category Prioritization: Find relevant machines matching this article's focus
$primaryCategory = $article['related_categories'][0] ?? '';
$matchedMachines = [];

// 1. Primary category matches first
foreach ($machines as $m) {
    if (!empty($primaryCategory) && $m['category'] === $primaryCategory) {
        $matchedMachines[] = $m;
    }
}

// 2. Secondary categories
$catFilter = $article['related_categories'] ?? [];
foreach ($machines as $m) {
    if (in_array($m['category'], $catFilter, true) && !in_array($m, $matchedMachines, true)) {
        $matchedMachines[] = $m;
    }
}

// 3. Fallback to featured machines if fewer than 4
if (count($matchedMachines) < 4) {
    foreach ($machines as $m) {
        if (!in_array($m, $matchedMachines, true)) {
            $matchedMachines[] = $m;
            if (count($matchedMachines) >= 4) break;
        }
    }
}

// Top featured spotlight machine for in-article highlight
$spotlightMachine = $matchedMachines[0] ?? null;
// Related machines for the bottom showcase and sidebar (up to 4)
$showcaseMachines = array_slice($matchedMachines, 0, 4);

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
    return '<h2' . $attrs . ' class="scroll-mt-24 font-black text-slate-900 text-xl sm:text-2xl mt-10 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">' . $matches[2] . '</h2>';
}, $article['content']);

// Make tables responsive inside article content
$processedContent = preg_replace(
    '/<table([^>]*)>/i',
    '<div class="blog-reader-table-wrap"><div class="table-swipe-hint sm:hidden"><i class="fa-solid fa-arrows-left-right text-slate-400"></i> Swipe sideways to view full table &rarr;</div><table$1>',
    $processedContent
);
$processedContent = str_replace('</table>', '</table></div>', $processedContent);

// Inject In-Article Featured Product Spotlight after the first section
if ($spotlightMachine) {
    $spotlightImg = '/' . ltrim($spotlightMachine['images'][0] ?? '', '/');
    $spotlightUrl = '/' . ltrim(getMachineUrl($spotlightMachine), '/');
    $spotlightTitle = htmlspecialchars($spotlightMachine['title']);
    $spotlightBrand = htmlspecialchars($spotlightMachine['brand']);
    $spotlightCat = htmlspecialchars($spotlightMachine['category']);
    $spotlightDesc = htmlspecialchars($spotlightMachine['short_desc']);
    $spotlightLoc = htmlspecialchars($spotlightMachine['city'] . ', ' . $spotlightMachine['country']);
    $spotlightFlag = $spotlightMachine['flag'];
    $spotlightYear = htmlspecialchars((string)$spotlightMachine['year']);
    $spotlightPrice = htmlspecialchars($spotlightMachine['price_display'] ?: 'Price on Request');
    
    // Pick 2 key specs
    $specsSnippet = '';
    if (!empty($spotlightMachine['specs'])) {
        $cnt = 0;
        foreach ($spotlightMachine['specs'] as $sKey => $sVal) {
            if (in_array(strtolower($sKey), ['model', 'brand', 'manufacturer', 'year of manufacture'])) continue;
            $specsSnippet .= '<div class="bg-white/10 rounded-xl p-2.5 border border-white/10 text-left">';
            $specsSnippet .= '<span class="spotlight-spec-title text-[10px] block uppercase font-bold tracking-wider" style="color: #fde047 !important;">' . htmlspecialchars((string)$sKey) . '</span>';
            $specsSnippet .= '<span class="spotlight-spec-val text-xs font-black truncate block mt-0.5" style="color: #ffffff !important;">' . htmlspecialchars((string)$sVal) . '</span>';
            $specsSnippet .= '</div>';
            $cnt++;
            if ($cnt >= 2) break;
        }
    }
    
    $spotlightCard = '
    <div class="jmh-spotlight-card my-10 rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#051833] via-[#092243] to-[#041224] p-5 sm:p-8 text-white border-2 border-amber-400 shadow-2xl relative overflow-hidden not-prose">
      <div class="flex items-center justify-between pb-4 border-b border-white/15 gap-2 flex-wrap">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gradient-to-r from-amber-400 to-yellow-400 text-slate-950 text-[11px] font-black uppercase tracking-wider shadow-sm">
          <i class="fa-solid fa-bolt text-xs"></i> Verified Equipment Spotlight
        </span>
        <span class="inline-flex items-center gap-1.5 text-emerald-400 text-xs font-bold bg-emerald-950/60 px-3 py-1 rounded-full border border-emerald-500/30">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
          Ready for Plant Floor Inspection
        </span>
      </div>

      <div class="mt-5 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
        <div class="md:col-span-5 relative rounded-2xl overflow-hidden bg-slate-950 border border-white/20 aspect-[4/3] group shadow-inner">
          <img src="' . $spotlightImg . '" alt="' . $spotlightTitle . '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
          <div class="absolute bottom-2 left-2 bg-slate-950/85 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-lg border border-white/20 flex items-center gap-1.5 pointer-events-none">
            <span>' . $spotlightFlag . '</span>
            <span>' . $spotlightLoc . '</span>
          </div>
        </div>

        <div class="md:col-span-7 space-y-3 text-left">
          <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider">
            <span style="color: #facc15 !important;">' . $spotlightBrand . '</span>
            <span class="text-white/40">&bull;</span>
            <span style="color: #cbd5e1 !important;">Year ' . $spotlightYear . '</span>
            <span class="text-white/40">&bull;</span>
            <span style="color: #cbd5e1 !important;">' . $spotlightCat . '</span>
          </div>
          <h3 class="spotlight-title text-lg sm:text-xl md:text-2xl font-black leading-snug" style="color: #ffffff !important; margin: 0 0 0.5rem 0 !important; font-size: 1.35rem !important; line-height: 1.35 !important; border-bottom: none !important; padding-bottom: 0 !important;">
            ' . $spotlightTitle . '
          </h3>
          <p class="spotlight-desc text-xs sm:text-sm leading-relaxed line-clamp-2" style="color: #e2e8f0 !important; margin-bottom: 0.75rem !important; font-size: 0.875rem !important; line-height: 1.6 !important;">
            ' . $spotlightDesc . '
          </p>

          ' . (!empty($specsSnippet) ? '<div class="grid grid-cols-2 gap-2.5 pt-1">' . $specsSnippet . '</div>' : '') . '

          <div class="pt-3 grid grid-cols-2 sm:flex sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 jmh-mobile-grid-2">
            <a href="' . $spotlightUrl . '" class="col-span-1 col-1 w-full sm:w-auto sm:flex-1 px-3 sm:px-5 py-3 rounded-xl bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 text-slate-950 font-black text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider flex items-center justify-center gap-1.5 transition-transform hover:scale-[1.02] shadow-lg shadow-amber-400/20 text-center min-h-[46px]">
              <span>View Details</span>
              <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
            <button type="button" onclick="openRequirementModal()" class="col-span-1 col-1 w-full sm:w-auto px-3 sm:px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider flex items-center justify-center gap-1.5 border border-white/20 transition-colors cursor-pointer min-h-[46px]">
              <i class="fa-solid fa-file-signature text-amber-400 text-xs"></i>
              <span>Request Quote</span>
            </button>
          </div>
        </div>
      </div>
    </div>';

    // Insert after the first </p> following the first </h2>
    $h2Pos = strpos($processedContent, '</h2>');
    if ($h2Pos !== false) {
        $pPos = strpos($processedContent, '</p>', $h2Pos);
        if ($pPos !== false) {
            $insertIdx = $pPos + 4;
            $processedContent = substr($processedContent, 0, $insertIdx) . $spotlightCard . substr($processedContent, $insertIdx);
        } else {
            $processedContent = substr($processedContent, 0, $h2Pos) . $spotlightCard . substr($processedContent, $h2Pos);
        }
    }
}

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
            'url' => 'https://joulemachinehub.com/assets/images/logo-v2.png'
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

<!-- BREADCRUMBS: Snaps to max-w-7xl Grid -->
<nav class="bg-white border-b border-slate-200 py-3 px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 shadow-xs" aria-label="Breadcrumb">
  <div class="max-w-7xl mx-auto flex items-center gap-2 flex-wrap">
    <a href="/" class="hover:text-joule-brightblue flex items-center gap-1.5 transition-colors">
      <i class="fa-solid fa-house text-slate-400 text-xs"></i>
      <span>Home</span>
    </a>
    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
    <a href="/blog" class="hover:text-joule-brightblue transition-colors">
      <span>Machinery Blog &amp; Guides</span>
    </a>
    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
    <span class="text-amber-600 font-bold">
      <?php echo htmlspecialchars($article['category']); ?>
    </span>
    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300 hidden sm:inline"></i>
    <span class="text-slate-900 font-bold truncate max-w-[200px] sm:max-w-md hidden sm:inline">
      <?php echo htmlspecialchars($article['short_title'] ?? $article['title']); ?>
    </span>
  </div>
</nav>

<!-- ARTICLE HERO / HEADER: Shared 12-Column Grid Alignment with Main Content Below -->
<header class="relative bg-gradient-to-b from-[#051326] via-[#092243] to-[#041224] text-white pt-8 pb-12 sm:pt-12 sm:pb-16 px-4 sm:px-6 lg:px-8 overflow-hidden border-b border-slate-800">
  <!-- Blueprint Ambient Texture Overlay -->
  <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(rgba(255,255,255,0.15) 1px, transparent 1px); background-size: 24px 24px;"></div>
  <div class="absolute -top-32 -right-32 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>

  <div class="max-w-7xl mx-auto relative z-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
      
      <!-- 8 COLUMNS: Perfectly Aligns with 8-col Reading Content Below -->
      <div class="lg:col-span-8 space-y-4 sm:space-y-6 text-left">
        
        <!-- Badges Row -->
        <div class="flex items-center gap-2 text-xs font-bold text-slate-300 flex-wrap">
          <span class="bg-gradient-to-r from-amber-400 to-yellow-400 text-slate-950 px-3 py-1 rounded-full font-black uppercase tracking-wider text-[11px] shadow-sm">
            <?php echo htmlspecialchars($article['category']); ?>
          </span>
          <span class="text-slate-500">&bull;</span>
          <span class="inline-flex items-center gap-1.5 text-slate-200">
            <i class="fa-regular fa-clock text-amber-400"></i>
            <span><?php echo htmlspecialchars($article['read_time']); ?></span>
          </span>
          <span class="text-slate-500">&bull;</span>
          <span class="inline-flex items-center gap-1.5 text-slate-300">
            <i class="fa-regular fa-calendar text-slate-400"></i>
            <span><?php echo htmlspecialchars($article['published_date']); ?></span>
          </span>
          <span class="hidden sm:inline text-slate-500">&bull;</span>
          <span class="inline-flex items-center gap-1.5 text-emerald-400 bg-emerald-950/70 border border-emerald-500/30 px-2.5 py-0.5 rounded-full text-[11px]">
            <i class="fa-solid fa-shield-check"></i>
            <span>Verified Engineering Desk</span>
          </span>
        </div>

        <!-- Main Title -->
        <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight sm:leading-snug drop-shadow-sm">
          <?php echo htmlspecialchars($article['title']); ?>
        </h1>

        <!-- Excerpt / Subheading -->
        <p class="text-slate-200 text-sm sm:text-base md:text-lg leading-relaxed font-normal">
          <?php echo htmlspecialchars($article['excerpt']); ?>
        </p>

        <!-- Author & Social Share Bar -->
        <div class="pt-5 border-t border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-500 text-slate-950 font-black text-sm flex items-center justify-center shadow-lg border border-amber-300 shrink-0">
              JMH
            </div>
            <div>
              <div class="text-xs sm:text-sm font-black text-white flex items-center gap-1.5">
                <span><?php echo htmlspecialchars($article['author']); ?></span>
                <i class="fa-solid fa-circle-check text-emerald-400 text-xs" title="Verified Author"></i>
              </div>
              <div class="text-[11px] text-slate-300 font-medium"><?php echo htmlspecialchars($article['author_role']); ?></div>
            </div>
          </div>

          <!-- Quick Social Share Buttons -->
          <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-1 hidden sm:inline">Share:</span>
            <a 
              href="https://api.whatsapp.com/send?text=<?php echo $shareTitle; ?>%20<?php echo $shareUrl; ?>" 
              target="_blank" 
              rel="noopener noreferrer" 
              class="h-9 px-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center gap-1.5 text-xs font-bold transition-transform hover:scale-105 shadow-sm"
              title="Share on WhatsApp"
            >
              <i class="fa-brands fa-whatsapp text-sm"></i>
              <span>WhatsApp</span>
            </a>
            <a 
              href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $shareUrl; ?>" 
              target="_blank" 
              rel="noopener noreferrer" 
              class="h-9 px-3.5 rounded-xl bg-[#0077b5] hover:bg-blue-600 text-white flex items-center justify-center gap-1.5 text-xs font-bold transition-transform hover:scale-105 shadow-sm"
              title="Share on LinkedIn"
            >
              <i class="fa-brands fa-linkedin-in text-sm"></i>
              <span>LinkedIn</span>
            </a>
            <button 
              type="button" 
              onclick="copyGuideLink('<?php echo $canonicalUrl; ?>')" 
              id="copyLinkBtnTop"
              class="h-9 px-3.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold flex items-center justify-center gap-1.5 transition-colors border border-white/20 cursor-pointer"
              title="Copy Link to Clipboard"
            >
              <i class="fa-regular fa-copy text-xs"></i>
              <span class="copyText">Copy Link</span>
            </button>
          </div>
        </div>

      </div>

      <!-- 4 COLUMNS: Header Companion Widget (Desktop Only) -->
      <div class="hidden lg:flex lg:col-span-4 flex-col justify-end pt-2">
        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/15 text-left space-y-3 shadow-lg">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
              <span class="text-xs font-black text-amber-400 uppercase tracking-wider">Direct Machinery Desk</span>
            </div>
            <span class="text-[10px] text-slate-300 font-bold bg-white/10 px-2 py-0.5 rounded">B2B Portal</span>
          </div>
          <h4 class="text-sm font-black text-white leading-snug">
            Need Equipment Sourcing for Your Plant?
          </h4>
          <p class="text-xs text-slate-200 leading-relaxed font-normal">
            Post your technical machinery requirements to receive direct quotes from verified factory owners.
          </p>
          <button 
            type="button" 
            onclick="openRequirementModal()" 
            class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 text-slate-950 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-md hover:scale-[1.02] transition-transform cursor-pointer"
          >
            <i class="fa-solid fa-paper-plane text-xs"></i>
            <span>Submit Instant Machinery RFQ</span>
          </button>
        </div>
      </div>

    </div>
  </div>
</header>

<!-- ARTICLE MAIN BODY CONTAINER: Shared 12-Column Grid Alignment -->
<main class="bg-slate-50 min-h-screen py-8 sm:py-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
      
      <!-- 8 COLUMNS: Main Article Reading Column -->
      <div class="lg:col-span-8 space-y-8 min-w-0 w-full text-left">
        
        <!-- 1. Featured Image Banner (16:9 Responsive Ratio, Full 8-col Width) -->
        <div class="relative w-full rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-200 shadow-md bg-slate-950 aspect-[16/9] sm:aspect-[21/9] max-h-[440px]">
          <img 
            src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
            alt="<?php echo htmlspecialchars($article['title']); ?>" 
            class="w-full h-full object-cover"
            loading="eager"
          >
          <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-black/20 pointer-events-none"></div>
          <div class="absolute bottom-3 left-3 sm:bottom-4 sm:left-4 bg-slate-950/85 backdrop-blur-md text-white text-[11px] sm:text-xs font-bold px-3 py-1.5 rounded-xl border border-white/20 flex items-center gap-2 shadow-lg">
            <i class="fa-solid fa-camera text-amber-400 text-xs"></i>
            <span>Authentic Plant Floor Inspection Photo</span>
          </div>
        </div>

        <!-- 2. Mobile Table of Contents (Visible on Mobile & Tablet < 1024px) -->
        <?php if (!empty($toc)): ?>
        <div class="lg:hidden bg-white border-2 border-blue-100 rounded-2xl p-4 shadow-xs">
          <details class="group">
            <summary class="flex items-center justify-between cursor-pointer list-none font-black text-slate-900 text-xs sm:text-sm">
              <span class="flex items-center gap-2">
                <i class="fa-solid fa-list-ol text-joule-brightblue"></i>
                <span>In This Technical Guide (Table of Contents)</span>
              </span>
              <span class="text-xs text-joule-brightblue font-bold group-open:hidden">Show &darr;</span>
              <span class="text-xs text-joule-brightblue font-bold hidden group-open:inline">Hide &uarr;</span>
            </summary>
            <div class="pt-3 mt-3 border-t border-blue-100 space-y-1 text-xs">
              <?php foreach ($toc as $idx => $tItem): ?>
                <a 
                  href="#<?php echo htmlspecialchars($tItem['id']); ?>" 
                  class="flex items-start gap-2 text-slate-700 hover:text-joule-brightblue font-semibold py-1.5 hover:translate-x-1 transition-all"
                >
                  <span class="w-5 h-5 rounded-full bg-blue-100 text-joule-blue font-black text-[10px] flex items-center justify-center shrink-0 mt-0.5">
                    <?php echo ($idx + 1); ?>
                  </span>
                  <span class="leading-tight"><?php echo htmlspecialchars($tItem['title']); ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </details>
        </div>
        <?php endif; ?>

        <!-- 3. Article Prose Container -->
        <article class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 p-6 sm:p-10 lg:p-12 shadow-xs">
          <div class="blog-reader-body">
            <?php echo $processedContent; ?>
          </div>

          <!-- In-Article Direct Sourcing Banner -->
          <div class="my-8 p-5 sm:p-7 bg-gradient-to-r from-slate-900 via-joule-navy to-slate-900 text-white rounded-2xl border-2 border-amber-400/50 shadow-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 not-prose">
            <div class="space-y-1.5 text-left">
              <div class="inline-flex items-center gap-1.5 text-amber-300 text-[11px] font-black uppercase tracking-wider">
                <i class="fa-solid fa-bolt text-amber-400"></i>
                <span>Equipment Sourcing Desk</span>
              </div>
              <h4 class="text-base sm:text-lg md:text-xl font-black text-white">Looking for Verified Industrial Machinery?</h4>
              <p class="text-xs text-slate-300 leading-relaxed max-w-xl">Submit your technical requirement. Our B2B machinery desk connects you directly with matching available inventory.</p>
            </div>
            <button 
              type="button" 
              onclick="openRequirementModal()" 
              class="w-full sm:w-auto bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 text-slate-950 font-black py-3 px-6 rounded-xl text-xs uppercase tracking-wider whitespace-nowrap shadow-lg shadow-amber-400/20 transition-all hover:scale-105 cursor-pointer border border-amber-300 text-center"
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
                class="text-xs bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-joule-brightblue px-3 py-1.5 rounded-lg font-bold transition-colors border border-slate-200/80"
              >
                #<?php echo htmlspecialchars($tag); ?>
              </a>
            <?php endforeach; ?>
          </div>

          <!-- Bottom Share Bar -->
          <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between flex-wrap gap-3">
            <div class="text-xs font-black text-slate-700">Found this technical checklist valuable? Share with your plant engineering team:</div>
            <div class="flex items-center gap-2">
              <a 
                href="https://api.whatsapp.com/send?text=<?php echo $shareTitle; ?>%20<?php echo $shareUrl; ?>" 
                target="_blank" 
                rel="noopener noreferrer" 
                class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs"
              >
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>WhatsApp</span>
              </a>
              <a 
                href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $shareUrl; ?>" 
                target="_blank" 
                rel="noopener noreferrer" 
                class="px-3.5 py-2 rounded-xl bg-[#0077b5] hover:bg-blue-600 text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs"
              >
                <i class="fa-brands fa-linkedin-in text-sm"></i>
                <span>LinkedIn</span>
              </a>
              <button 
                type="button" 
                onclick="copyGuideLink('<?php echo $canonicalUrl; ?>')" 
                class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5 transition-colors border border-slate-300 cursor-pointer"
              >
                <i class="fa-regular fa-copy text-xs"></i>
                <span class="copyText">Copy Link</span>
              </button>
            </div>
          </div>
        </article>

        <!-- ========================================================
             4. DEDICATED PRODUCTS SECTION: VERIFIED MACHINERY FOR SALE
             High-conversion product showcase highlighted right below the article
             ======================================================== -->
        <?php if (!empty($showcaseMachines)): ?>
        <section class="space-y-5">
          <!-- Section Header with golden industrial flair -->
          <div class="bg-gradient-to-r from-[#041224] via-[#092243] to-[#041224] text-white rounded-2xl p-5 sm:p-7 shadow-xl border-2 border-amber-400/60 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-[11px] font-black uppercase tracking-wider border border-amber-400/30">
                <i class="fa-solid fa-industry text-amber-400"></i>
                <span>Verified Live Inventory</span>
              </div>
              <h3 class="text-xl sm:text-2xl md:text-3xl font-black text-white tracking-tight leading-tight">
                Verified Machinery Available for Sale
              </h3>
              <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                Explore in-stock industrial equipment matching this engineering guide. Connect directly with verified plant sellers with zero commission.
              </p>
            </div>
            <a href="/category" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 text-slate-950 font-black text-xs uppercase tracking-wider shrink-0 transition-transform hover:scale-105 shadow-lg shadow-amber-400/20 border border-amber-300">
              <span>Browse All Machines</span>
              <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
          </div>

          <!-- 2x2 Product Grid (1 column on phone, 2 on tablet/desktop) -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
            <?php foreach ($showcaseMachines as $m): 
              $mImg = '/' . ltrim($m['images'][0] ?? '', '/');
              $mUrl = '/' . ltrim(getMachineUrl($m), '/');
            ?>
              <div class="bg-white rounded-2xl border-2 border-slate-200 hover:border-amber-400 p-4 sm:p-5 transition-all hover:shadow-xl group flex flex-col justify-between">
                <div>
                  <!-- Image container with badges -->
                  <div class="relative w-full aspect-[4/3] rounded-xl overflow-hidden bg-slate-950 mb-3.5">
                    <a href="<?php echo $mUrl; ?>" class="block w-full h-full">
                      <img src="<?php echo htmlspecialchars($mImg); ?>" alt="<?php echo htmlspecialchars($m['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                    </a>
                    <!-- Verified Pill -->
                    <div class="absolute top-2 left-2 flex items-center gap-1.5 flex-wrap pointer-events-none">
                      <span class="bg-emerald-600 text-white text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded shadow-sm flex items-center gap-1">
                        <i class="fa-solid fa-shield-check"></i> Verified
                      </span>
                      <span class="bg-slate-950/80 backdrop-blur-xs text-amber-300 text-[10px] font-bold px-2 py-0.5 rounded border border-amber-400/30">
                        <?php echo htmlspecialchars($m['condition']); ?>
                      </span>
                    </div>
                    <!-- Location Pill -->
                    <div class="absolute bottom-2 right-2 bg-slate-950/85 backdrop-blur-xs text-white text-[10px] font-bold px-2 py-0.5 rounded border border-white/20 flex items-center gap-1 pointer-events-none">
                      <span><?php echo $m['flag']; ?></span>
                      <span><?php echo htmlspecialchars($m['city'] . ', ' . $m['country']); ?></span>
                    </div>
                  </div>

                  <!-- Brand & Year -->
                  <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-1">
                    <span class="text-amber-600 uppercase tracking-wider font-black"><?php echo htmlspecialchars($m['brand']); ?></span>
                    <span>Year <?php echo htmlspecialchars((string)$m['year']); ?></span>
                  </div>

                  <!-- Title -->
                  <h4 class="text-sm sm:text-base font-black text-slate-900 group-hover:text-joule-brightblue transition-colors line-clamp-2 leading-snug mb-2">
                    <a href="<?php echo $mUrl; ?>">
                      <?php echo htmlspecialchars($m['title']); ?>
                    </a>
                  </h4>

                  <!-- Specs row -->
                  <?php if (!empty($m['specs'])): ?>
                  <div class="grid grid-cols-2 gap-1.5 py-2 border-y border-slate-100 text-[11px] mb-3">
                    <?php 
                    $specCnt = 0;
                    foreach ($m['specs'] as $sk => $sv) {
                      if (in_array(strtolower($sk), ['model', 'brand', 'manufacturer', 'year of manufacture'])) continue;
                      echo '<div class="truncate"><span class="text-slate-400 font-semibold">' . htmlspecialchars((string)$sk) . ':</span> <strong class="text-slate-700">' . htmlspecialchars((string)$sv) . '</strong></div>';
                      $specCnt++;
                      if ($specCnt >= 2) break;
                    }
                    ?>
                  </div>
                  <?php endif; ?>

                  <!-- Price -->
                  <div class="mb-3">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Asking Price:</span>
                    <span class="text-sm font-black text-joule-brightblue"><?php echo htmlspecialchars($m['price_display'] ?: 'Price on Request'); ?></span>
                  </div>
                </div>

                <!-- Action Buttons: 2 IN A ROW ON MOBILE VIEW! -->
                <div class="grid grid-cols-2 gap-2 jmh-mobile-grid-2 pt-2 border-t border-slate-100">
                  <a href="<?php echo $mUrl; ?>" class="col-span-1 col-1 w-full px-2 sm:px-3 py-2.5 rounded-xl bg-slate-900 hover:bg-joule-blue text-white font-black text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider flex items-center justify-center gap-1 sm:gap-1.5 transition-colors text-center shadow-sm min-h-[42px]">
                    <span class="truncate">View Details</span>
                    <i class="fa-solid fa-arrow-right text-[10px] shrink-0"></i>
                  </a>
                  <button type="button" onclick="openRequirementModal()" class="col-span-1 col-1 w-full px-2 sm:px-3 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 text-slate-950 font-black text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider flex items-center justify-center gap-1 sm:gap-1.5 transition-all text-center border border-amber-300 shadow-sm cursor-pointer min-h-[42px]">
                    <i class="fa-solid fa-file-signature text-xs shrink-0"></i>
                    <span class="truncate">Enquire Now</span>
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Custom Sourcing Bar -->
          <div class="p-4 sm:p-5 rounded-2xl bg-blue-50/70 border border-blue-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-blue-100 text-joule-brightblue flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-magnifying-glass"></i>
              </div>
              <div>
                <div class="text-xs sm:text-sm font-black text-slate-900">Looking for a specific model or technical specification?</div>
                <div class="text-[11px] text-slate-600">Post your technical machinery requirement. Our B2B desk will source and verify it for you.</div>
              </div>
            </div>
            <button type="button" onclick="openRequirementModal()" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-joule-blue text-white font-bold text-xs uppercase tracking-wider transition-colors shrink-0 cursor-pointer">
              Post Machine Requirement &rarr;
            </button>
          </div>
        </section>
        <?php endif; ?>

        <!-- 5. FREQUENTLY ASKED QUESTIONS ACCORDION -->
        <?php if (!empty($article['faqs'])): ?>
        <section class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-joule-brightblue flex items-center justify-center font-bold text-base shadow-xs shrink-0">
              <i class="fa-solid fa-circle-question text-lg"></i>
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
                    <span class="w-5 h-5 rounded-full bg-blue-100 text-joule-brightblue text-[10px] font-black flex items-center justify-center shrink-0">
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

        <!-- 6. AUTHOR BIO BOX -->
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

        <!-- 7. RECOMMENDED GUIDES IN SAME OR ADJACENT TOPIC -->
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
                class="bg-white rounded-xl border border-slate-200 hover:border-amber-400 p-3.5 flex flex-col justify-between transition-all hover:shadow-md group"
              >
                <div>
                  <div class="h-28 sm:h-32 w-full bg-slate-900 rounded-lg overflow-hidden mb-2.5 relative">
                    <img src="<?php echo htmlspecialchars($ra['featured_image']); ?>" alt="<?php echo htmlspecialchars($ra['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform" loading="lazy">
                    <span class="absolute top-1.5 left-1.5 bg-slate-950/80 text-white text-[9px] font-bold px-2 py-0.5 rounded">
                      <?php echo htmlspecialchars($ra['category']); ?>
                    </span>
                  </div>
                  <h4 class="text-xs font-black text-slate-900 group-hover:text-joule-brightblue transition-colors line-clamp-2 leading-snug">
                    <?php echo htmlspecialchars($ra['short_title'] ?? $ra['title']); ?>
                  </h4>
                </div>
                <div class="pt-2 text-[10px] text-slate-400 font-semibold flex items-center justify-between border-t border-slate-100 mt-2">
                  <span><?php echo htmlspecialchars($ra['read_time']); ?></span>
                  <span class="text-joule-brightblue font-bold group-hover:translate-x-0.5 transition-transform">Read &rarr;</span>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

      </div>

      <!-- 4 COLUMNS: Sticky Sidebar & Live Machinery Inventory -->
      <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
        
        <!-- DESKTOP STICKY TABLE OF CONTENTS -->
        <?php if (!empty($toc)): ?>
        <div class="hidden lg:block bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
          <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2.5">
            <i class="fa-solid fa-list-check text-joule-brightblue"></i>
            <span>In This Engineering Guide</span>
          </h4>
          <nav class="space-y-1.5 text-xs max-h-72 overflow-y-auto pr-1">
            <?php foreach ($toc as $idx => $tItem): ?>
              <a 
                href="#<?php echo htmlspecialchars($tItem['id']); ?>" 
                class="flex items-start gap-2 py-1.5 px-2 rounded-lg text-slate-600 hover:text-joule-brightblue hover:bg-blue-50/70 font-semibold transition-colors"
              >
                <span class="text-[10px] font-black text-slate-400 mt-0.5 shrink-0"><?php echo ($idx + 1); ?>.</span>
                <span class="leading-tight"><?php echo htmlspecialchars($tItem['title']); ?></span>
              </a>
            <?php endforeach; ?>
          </nav>
        </div>
        <?php endif; ?>

        <!-- Direct Equipment Sourcing RFQ Box -->
        <div class="bg-gradient-to-br from-joule-navy to-blue-950 text-white rounded-2xl p-5 sm:p-6 border-2 border-amber-400/50 shadow-lg space-y-3.5">
          <div class="inline-flex items-center gap-1.5 bg-gradient-to-r from-amber-400 to-yellow-400 text-slate-950 text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-md shadow-xs">
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
            class="w-full bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 text-slate-950 font-black py-2.5 px-4 rounded-xl text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer border border-amber-300"
          >
            <i class="fa-solid fa-paper-plane"></i>
            <span>Post an Equipment RFQ</span>
          </button>
        </div>

        <!-- SIDEBAR LIVE MACHINERY INVENTORY WIDGET -->
        <?php if (!empty($showcaseMachines)): ?>
        <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 shadow-xs space-y-3.5">
          <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
              <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider">
                Live Machinery Inventory
              </h4>
            </div>
            <a href="/category" class="text-[10px] font-black text-joule-brightblue hover:underline">View All &rarr;</a>
          </div>

          <div class="space-y-3">
            <?php foreach ($showcaseMachines as $rm): 
              $rmImg = '/' . ltrim($rm['images'][0] ?? '', '/');
              $rmUrl = '/' . ltrim(getMachineUrl($rm), '/');
            ?>
              <a 
                href="<?php echo $rmUrl; ?>" 
                class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-200 group"
              >
                <div class="w-20 h-16 rounded-xl bg-slate-900 overflow-hidden shrink-0 relative shadow-inner">
                  <img src="<?php echo htmlspecialchars($rmImg); ?>" alt="<?php echo htmlspecialchars($rm['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform" loading="lazy">
                  <span class="absolute bottom-1 right-1 text-[9px]"><?php echo $rm['flag']; ?></span>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="text-[10px] font-black text-amber-600 uppercase tracking-tight"><?php echo htmlspecialchars($rm['brand']); ?></div>
                  <div class="text-xs font-black text-slate-900 truncate group-hover:text-joule-brightblue transition-colors">
                    <?php echo htmlspecialchars($rm['title']); ?>
                  </div>
                  <div class="text-[10px] text-slate-500 font-semibold flex items-center justify-between mt-1">
                    <span><?php echo htmlspecialchars($rm['city']); ?></span>
                    <span class="text-emerald-700 font-bold">Inspect Ready &rarr;</span>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>

          <button 
            type="button" 
            onclick="openRequirementModal()" 
            class="w-full py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-1.5 transition-colors border border-slate-300 cursor-pointer"
          >
            <i class="fa-solid fa-file-signature text-amber-500 text-xs"></i>
            <span>Submit Custom Sourcing RFQ</span>
          </button>
        </div>
        <?php endif; ?>

        <!-- Quick Category Links -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
          <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-boxes-stacked text-amber-500"></i>
            <span>Popular Machinery Categories</span>
          </h4>
          <div class="grid grid-cols-1 gap-1 text-xs">
            <a href="/category/used-presses-stamping" class="py-2 px-2.5 rounded-lg hover:bg-slate-50 font-bold text-slate-700 hover:text-joule-brightblue flex items-center justify-between transition-colors">
              <span>Power Presses &amp; Stamping</span>
              <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            </a>
            <a href="/category/used-cold-formers-headers" class="py-2 px-2.5 rounded-lg hover:bg-slate-50 font-bold text-slate-700 hover:text-joule-brightblue flex items-center justify-between transition-colors">
              <span>Cold Formers &amp; Headers</span>
              <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            </a>
            <a href="/category/used-lathes-turning" class="py-2 px-2.5 rounded-lg hover:bg-slate-50 font-bold text-slate-700 hover:text-joule-brightblue flex items-center justify-between transition-colors">
              <span>Lathes &amp; Turning Centers</span>
              <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            </a>
            <a href="/category/used-cnc-machining-centers" class="py-2 px-2.5 rounded-lg hover:bg-slate-50 font-bold text-slate-700 hover:text-joule-brightblue flex items-center justify-between transition-colors">
              <span>CNC Machining Centers</span>
              <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            </a>
            <a href="/category" class="py-2.5 px-3 rounded-xl bg-blue-50 text-joule-brightblue font-black flex items-center justify-between mt-2 hover:bg-blue-100 transition-colors">
              <span>Explore All 13 Categories</span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
          </div>
        </div>

      </aside>

    </div>
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
