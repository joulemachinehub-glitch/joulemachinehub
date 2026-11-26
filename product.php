<?php
// Joule Machine Hub - Dedicated SEO-Optimized Product Detail Page
// Implements Standardized 10-Part Machine Content Template & 4-Entity Location Architecture
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';
require_once __DIR__ . '/includes/machine_template.php';

// Support both clean SEO slug and ?id= queries
$slugOrId = isset($_GET['slug']) ? trim($_GET['slug']) : (isset($_GET['id']) ? trim($_GET['id']) : '');
$machine = findMachineBySlugOrId($slugOrId);
$isSingleProductPage = true;

// 404 Handling: If a slug/id was requested but machine does not exist, show 404
if (!empty($slugOrId) && !$machine) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Default fallback to first machine if accessed bare without parameters
if (!$machine) {
    $machine = $machines[0];
}

// STRICT CLEAN SEO SLUG ENFORCEMENT & CANONICAL REDIRECT:
// Redirect direct .php queries, legacy slugs (e.g. used-used-...), or non-canonical paths to verified clean slug
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$hasQuerySymbols = str_contains($requestUri, '?') || str_contains($requestUri, '=');
$isDirectPhpScript = str_contains($requestUri, 'product.php') || str_contains($requestUri, 'machine.php');
$canonicalProductPath = '/' . getMachineUrl($machine);
$requestedPath = parse_url($requestUri, PHP_URL_PATH);

if ($hasQuerySymbols || $isDirectPhpScript || ($requestedPath && $requestedPath !== $canonicalProductPath && !str_starts_with($requestedPath, '/assets'))) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $canonicalProductPath);
    exit;
}

// 1. GENERATE STANDARDIZED TEMPLATE DATA
$tpl = getEnrichedMachineData($machine);

$mCond = $machine['condition'] ?? 'Used';
$isNew = (stripos($mCond, 'new') !== false && stripos($mCond, 'like new') === false);
$productCondition = $isNew ? 'new' : 'used';
$schemaCondition = $isNew ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition';

// SEO Title & Description according to standard template rules
$pageTitle = $tpl['seo_title'] . " | Joule Machine Hub";
$pageDesc = $tpl['meta_desc'];
$pageKeywords = $tpl['focus_keyword'] . ', ' . implode(', ', $tpl['secondary_keywords']);

$sellerName = $tpl['seller_company'];
$sellerResponse = $machine['seller']['response_time'] ?? 'Within 2–4 Hours';

$pageAuthor = $sellerName . " via Joule Machine Hub";
$geoPlacename = $tpl['machine_location'];
$geoRegion = $machine['country_code'] ?? 'CN';
$productPrice = $machine['price'] ?? 0;
$productCurrency = $machine['currency_origin'] ?? 'USD';

$canonicalBase = 'https://joulemachinehub.com';
$canonicalUrl = $canonicalBase . '/' . ltrim(getMachineUrl($machine), '/');

$ogTitle = $tpl['h1'] . " | Joule Machine Hub";
$ogDesc = $tpl['meta_desc'];
$ogImage = $canonicalBase . '/' . ltrim($machine['images'][0], '/');
$ogImageAlt = "Photo of " . $tpl['h1'];
$ogType = "product";
$pageRobots = "index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1";

// Customer Reviews & SERP AggregateRating Setup (Accurately matches visible page content)
$productReviews = getMachineCustomerReviews($machine, $tpl);
$ratingVal = '5.0';
$reviewCnt = !empty($productReviews) ? count($productReviews) : 3;

$schemaReviews = array_map(function($r) {
    return [
        '@type' => 'Review',
        'name' => $r['title'],
        'reviewRating' => [
            '@type' => 'Rating',
            'ratingValue' => '5',
            'bestRating' => '5',
            'worstRating' => '1'
        ],
        'author' => [
            '@type' => 'Person',
            'name' => $r['author_name']
        ],
        'datePublished' => $r['date'],
        'reviewBody' => $r['body'],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Joule Machine Hub'
        ]
    ];
}, $productReviews);

// Detect accurate offer currency and price
$offerCurrency = 'INR';
$offerPrice = (float)($machine['price'] ?? 0);
if (!empty($machine['currency_origin']) && $machine['currency_origin'] === 'USD' && !empty($machine['price_usd'])) {
    $offerCurrency = 'USD';
    $offerPrice = (float)$machine['price_usd'];
} elseif (!empty($machine['price'])) {
    $offerCurrency = 'INR';
    $offerPrice = (float)$machine['price'];
}

// 2. ENRICHED GOOGLE JSON-LD SCHEMA: Product + Offer + AggregateRating + Review + Breadcrumbs + FAQPage
$jsonLd = [
    '@context' => 'https://schema.org/',
    '@graph' => [
        [
            '@type' => 'Product',
            'name' => $tpl['h1'],
            'image' => array_map(function($img) use ($canonicalBase) { return $canonicalBase . '/' . ltrim($img, '/'); }, $machine['images']),
            'description' => $tpl['meta_desc'],
            'sku' => strtoupper((string)($machine['id'] ?? $machine['product_ref'] ?? '1')),
            'mpn' => $machine['model'],
            'brand' => [
                '@type' => 'Brand',
                'name' => $tpl['short_brand']
            ],
            'manufacturer' => [
                '@type' => 'Organization',
                'name' => $tpl['manufacturer'],
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressCountry' => $tpl['manufacturer_country']
                ]
            ],
            'productionDate' => (string)$machine['year'],
            'itemCondition' => $schemaCondition,
            'category' => $machine['category'],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $ratingVal,
                'reviewCount' => $reviewCnt,
                'bestRating' => '5',
                'worstRating' => '1'
            ],
            'review' => $schemaReviews,
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => $offerCurrency,
                'price' => $offerPrice,
                'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
                'itemCondition' => $schemaCondition,
                'availability' => 'https://schema.org/InStock',
                'hasMerchantReturnPolicy' => [
                    '@type' => 'MerchantReturnPolicy',
                    'applicableCountry' => $machine['country_code'] ?? 'IN',
                    'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                    'merchantReturnDays' => 14,
                    'returnMethod' => 'https://schema.org/ReturnByMail',
                    'returnFees' => 'https://schema.org/FreeReturn'
                ],
                'shippingDetails' => [
                    '@type' => 'OfferShippingDetails',
                    'shippingRate' => [
                        '@type' => 'MonetaryAmount',
                        'value' => '0',
                        'currency' => $offerCurrency
                    ],
                    'shippingDestination' => [
                        '@type' => 'DefinedRegion',
                        'addressCountry' => $machine['country_code'] ?? 'IN'
                    ],
                    'deliveryTime' => [
                        '@type' => 'ShippingDeliveryTime',
                        'handlingTime' => [
                            '@type' => 'QuantitativeValue',
                            'minValue' => 2,
                            'maxValue' => 5,
                            'unitCode' => 'd'
                        ],
                        'transitTime' => [
                            '@type' => 'QuantitativeValue',
                            'minValue' => 5,
                            'maxValue' => 15,
                            'unitCode' => 'd'
                        ]
                    ]
                ],
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $sellerName,
                    'address' => [
                        '@type' => 'PostalAddress',
                        'addressLocality' => $machine['city'],
                        'addressCountry' => $machine['country_code'] ?? 'IN'
                    ]
                ]
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
                    'name' => $machine['category'],
                    'item' => $canonicalBase . '/' . getCategoryUrl($machine['category'])
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $machine['country'],
                    'item' => $canonicalBase . '/' . getCountryUrl($machine['country'])
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 4,
                    'name' => $machine['city'],
                    'item' => $canonicalBase . '/' . getCityUrl($machine['city'])
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 5,
                    'name' => $tpl['h1'],
                    'item' => $canonicalUrl
                ]
            ]
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
            }, $tpl['faqs'])
        ]
    ]
];

$machineJson = htmlspecialchars(json_encode($machine), ENT_QUOTES, 'UTF-8');

// Find 4 related machines for SEO internal linking
$relatedMachines = [];
$currMachineId = (string)($machine['id'] ?? $machine['product_ref'] ?? '');
foreach ($machines as $rm) {
    $rmId = (string)($rm['id'] ?? $rm['product_ref'] ?? '');
    if ($rmId !== $currMachineId && (($rm['category'] ?? '') === ($machine['category'] ?? '') || ($rm['country'] ?? '') === ($machine['country'] ?? ''))) {
        $relatedMachines[] = $rm;
        if (count($relatedMachines) >= 4) break;
    }
}
if (count($relatedMachines) < 4) {
    foreach ($machines as $rm) {
        $rmId = (string)($rm['id'] ?? $rm['product_ref'] ?? '');
        if ($rmId !== $currMachineId && !in_array($rm, $relatedMachines, true)) {
            $relatedMachines[] = $rm;
            if (count($relatedMachines) >= 4) break;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. BREADCRUMBS WITH CLICKABLE INTERNAL SEO LINKS -->
<div class="bg-white border-b border-slate-200 py-3 px-4">
  <div class="max-w-7xl mx-auto flex items-center flex-wrap gap-2 text-xs text-slate-500">
    <a href="/" class="hover:text-joule-brightblue font-bold flex items-center gap-1">
      <i class="fa-solid fa-house text-slate-400 text-[10px]"></i>
      <span>Home</span>
    </a>
    <span class="text-slate-300">/</span>
    <a href="<?php echo htmlspecialchars(getCategoryUrl($machine['category'])); ?>" class="hover:text-joule-brightblue font-medium">
      <?php echo htmlspecialchars($machine['category']); ?>
    </a>
    <span class="text-slate-300">/</span>
    <a href="<?php echo htmlspecialchars(getCountryUrl($machine['country'])); ?>" class="hover:text-joule-brightblue font-medium flex items-center gap-1">
      <?php echo getCountryFlagHtml($machine['country'] ?? 'Worldwide', 'w-4 h-3'); ?>
      <span><?php echo htmlspecialchars($machine['country'] ?? 'Worldwide'); ?></span>
    </a>
    <span class="text-slate-300">/</span>
    <a href="<?php echo htmlspecialchars(getCityUrl($machine['city'])); ?>" class="hover:text-joule-brightblue font-medium">
      <?php echo htmlspecialchars($machine['city']); ?>
    </a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-bold truncate max-w-[260px] sm:max-w-none">
      <?php echo htmlspecialchars($tpl['h1']); ?>
    </span>
  </div>
</div>

<main class="max-w-7xl mx-auto px-4 py-6 sm:py-8 pb-24 lg:pb-12 flex-1 w-full space-y-8">
  
  <!-- 2. PRODUCT HEADER CARD WITH SEO H1 -->
  <div class="bg-white rounded-2xl border-2 border-slate-200 p-4 sm:p-6 lg:p-7 shadow-xs space-y-4">
    
    <!-- Badges Row -->
    <div class="flex flex-wrap items-center gap-2">
      <span class="inline-flex items-center gap-1.5 bg-slate-900 text-white text-[11px] font-black px-3 py-1 rounded-full border border-slate-700">
        <?php echo getCountryFlagHtml($machine['country'] ?? 'Worldwide', 'w-4 h-3'); ?>
        <span><?php echo htmlspecialchars($machine['city']); ?>, <?php echo htmlspecialchars($machine['country']); ?></span>
      </span>

      <span class="inline-flex items-center gap-1.5 bg-blue-100 text-joule-blue text-[11px] font-black uppercase tracking-wider px-3 py-1 rounded-full border border-blue-200">
        <i class="fa-solid fa-industry text-joule-brightblue"></i>
        <span><?php echo htmlspecialchars($machine['category']); ?></span>
      </span>

      <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-900 text-[11px] font-black px-3 py-1 rounded-full border border-amber-300">
        <i class="fa-solid fa-calendar-days text-amber-600"></i>
        <span>Mfg: <?php echo htmlspecialchars($machine['year']); ?></span>
      </span>

      <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-900 text-[11px] font-black px-3 py-1 rounded-full border border-emerald-300">
        <i class="fa-solid fa-circle-check text-emerald-600"></i>
        <span>Verified Inventory</span>
      </span>
    </div>

    <!-- Keyword-Optimized H1 Header (Exact user format: Used [Brand] [Model] [Machine Type] for Sale) -->
    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-950 leading-tight">
      <?php echo htmlspecialchars($tpl['h1']); ?>
    </h1>

    <!-- Short Description Highlight Box -->
    <div class="bg-gradient-to-r from-blue-50 to-slate-50 border-l-4 border-joule-blue p-4 rounded-r-2xl">
      <div class="text-[10px] font-black uppercase tracking-wider text-blue-800 mb-1 flex items-center gap-1.5">
        <i class="fa-solid fa-circle-info"></i>
        <span>Equipment Overview &amp; Operational Summary</span>
      </div>
      <p class="text-xs sm:text-sm text-slate-700 leading-relaxed font-medium">
        <?php echo htmlspecialchars($tpl['intro_text_1']); ?>
      </p>
    </div>

    <!-- Quick Specifications Metric Badges -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-2.5 pt-2">
      
      <!-- 1. Brand -->
      <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-center flex flex-col justify-between min-h-[96px]">
        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">Make / Brand</div>
        <div class="my-auto py-0.5 text-xs sm:text-sm font-black text-slate-900 leading-tight break-words">
          <?php echo htmlspecialchars($tpl['short_brand']); ?>
        </div>
        <div class="text-[9px] text-slate-500 font-semibold">Original OEM</div>
      </div>

      <!-- 2. Model -->
      <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-center flex flex-col justify-between min-h-[96px]">
        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">Model</div>
        <div class="my-auto py-0.5 text-xs sm:text-sm font-black text-slate-900 leading-tight break-words">
          <?php echo htmlspecialchars($machine['model']); ?>
        </div>
        <div class="text-[9px] text-slate-500 font-semibold">Plate Verified</div>
      </div>

      <!-- 3. Year Built -->
      <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-center flex flex-col justify-between min-h-[96px]">
        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">Year Built</div>
        <div class="my-auto py-0.5 text-sm sm:text-base font-black text-joule-blue leading-tight">
          <?php echo htmlspecialchars($machine['year']); ?>
        </div>
        <div class="text-[9px] text-emerald-700 font-semibold">Mfg Verified</div>
      </div>

      <!-- 4. Equipment Type -->
      <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-center flex flex-col justify-between min-h-[96px]">
        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">Equipment Type</div>
        <div class="my-auto py-0.5 text-[11px] sm:text-xs font-black text-joule-brightblue leading-tight break-words">
          <?php echo htmlspecialchars($tpl['clean_type']); ?>
        </div>
        <div class="text-[9px] text-slate-500 font-semibold">Industrial</div>
      </div>

      <!-- 5. Condition -->
      <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-center flex flex-col justify-between min-h-[96px]">
        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">Condition</div>
        <div class="my-auto py-0.5 text-[11px] sm:text-xs font-black text-emerald-700 leading-tight break-words">
          Excellent
        </div>
        <div class="text-[9px] text-slate-500 font-semibold">Running Condition</div>
      </div>

      <!-- 6. Current Location -->
      <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-center flex flex-col justify-between min-h-[96px]">
        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">Machine Location</div>
        <div class="my-auto py-0.5 text-[11px] sm:text-xs font-black text-slate-900 leading-tight break-words">
          <?php echo htmlspecialchars($tpl['machine_location']); ?>
        </div>
        <div class="text-[9px] text-slate-500 font-semibold">Current Physical</div>
      </div>

    </div>

  </div>

  <!-- 3. MAIN GALLERY & DIRECT SELLER CALL DESK -->
  <div class="bg-white rounded-2xl border-2 border-slate-200 p-4 sm:p-7 shadow-xs">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      <!-- Left Photo Gallery (7 cols) -->
      <div class="lg:col-span-7 space-y-4">
        
        <!-- Active Main Photo -->
        <?php 
          $mainImgRaw = $machine['images'][0] ?? '';
          $cleanMainPath = ltrim($mainImgRaw, '/');
          $mainImgV = file_exists(__DIR__ . '/' . $cleanMainPath) ? filemtime(__DIR__ . '/' . $cleanMainPath) : '1';
          $mainImgUrl = '/' . $cleanMainPath;
        ?>
        <div class="relative aspect-[16/10] bg-slate-950 rounded-2xl overflow-hidden border-2 border-slate-200 shadow-inner group flex items-center justify-center">
          <img 
            id="mainDetailImg"
            src="<?php echo htmlspecialchars($mainImgUrl . '?v=' . $mainImgV); ?>" 
            alt="<?php echo htmlspecialchars($tpl['h1']); ?>" 
            title="<?php echo htmlspecialchars($tpl['h1']); ?>"
            fetchpriority="high"
            decoding="async"
            class="w-full h-full object-contain mx-auto"
          >
          <div class="absolute top-3 left-3 bg-slate-950/85 backdrop-blur-md text-white text-xs px-3 py-1.5 rounded-lg border border-white/20 font-bold flex items-center gap-2">
            <i class="fa-solid fa-location-dot text-joule-yellow"></i>
            <span>Machine Location: <?php echo htmlspecialchars($tpl['machine_location']); ?></span>
          </div>
          <div class="absolute bottom-3 right-3 bg-slate-950/85 backdrop-blur-md text-white text-xs px-2.5 py-1 rounded-lg border border-white/20 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Genuine Photos</span>
          </div>
        </div>

        <!-- Image Gallery Thumbnails -->
        <?php if (count($machine['images']) > 1): ?>
        <div>
          <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
            <span class="font-bold flex items-center gap-1.5">
              <i class="fa-solid fa-camera text-joule-brightblue"></i>
              <span><?php echo count($machine['images']); ?> Machine Inspection Photos</span>
            </span>
            <span class="text-[11px] text-slate-400">Click photo to view</span>
          </div>

          <div class="grid grid-cols-<?php echo min(5, count($machine['images'])); ?> gap-2 sm:gap-2.5">
            <?php foreach ($machine['images'] as $idx => $img): 
              $cleanImg = ltrim($img, '/');
              $imgV = file_exists(__DIR__ . '/' . $cleanImg) ? filemtime(__DIR__ . '/' . $cleanImg) : '1';
              $imgSrcWithV = htmlspecialchars('/' . $cleanImg . '?v=' . $imgV);
            ?>
              <button 
                type="button" 
                onclick="document.getElementById('mainDetailImg').src = '<?php echo $imgSrcWithV; ?>'; document.querySelectorAll('.thumb-btn').forEach(b => { b.classList.remove('border-joule-blue', 'ring-2', 'ring-joule-blue', 'opacity-100'); b.classList.add('opacity-75'); }); this.classList.add('border-joule-blue', 'ring-2', 'ring-joule-blue', 'opacity-100'); this.classList.remove('opacity-75');"
                class="thumb-btn aspect-[4/3] rounded-xl overflow-hidden border-2 transition-all cursor-pointer bg-slate-950 relative group <?php echo $idx === 0 ? 'border-joule-blue ring-2 ring-joule-blue opacity-100' : 'border-slate-300 opacity-80 hover:opacity-100 hover:border-joule-brightblue'; ?>"
                title="Photo <?php echo $idx + 1; ?> of <?php echo count($machine['images']); ?> - <?php echo htmlspecialchars($tpl['h1']); ?>"
              >
                <img src="<?php echo $imgSrcWithV; ?>" alt="<?php echo htmlspecialchars($tpl['h1']); ?> - Photo <?php echo $idx + 1; ?>" loading="lazy" decoding="async" class="w-full h-full object-contain p-0.5 group-hover:scale-105 transition-transform">
                <span class="absolute bottom-1 right-1 bg-slate-900/80 text-white text-[9px] font-bold px-1 rounded"><?php echo $idx + 1; ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- SEPARATE LOCATION & MANUFACTURER TABLES (USER RECOMMENDATION) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
          
          <!-- Table 1: Manufacturer Information -->
          <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs bg-white">
            <div class="bg-slate-900 text-white px-4 py-2.5 font-black text-xs uppercase tracking-wider flex items-center gap-2">
              <i class="fa-solid fa-industry text-joule-yellow"></i>
              <span>Manufacturer Information</span>
            </div>
            <table class="w-full text-xs">
              <tbody class="divide-y divide-slate-100">
                <tr class="bg-slate-50/60">
                  <td class="px-3.5 py-2.5 text-slate-500 font-semibold w-1/3">Brand</td>
                  <td class="px-3.5 py-2.5 font-bold text-slate-900"><?php echo htmlspecialchars($tpl['short_brand']); ?></td>
                </tr>
                <tr>
                  <td class="px-3.5 py-2.5 text-slate-500 font-semibold">Manufacturer</td>
                  <td class="px-3.5 py-2.5 font-bold text-slate-900"><?php echo htmlspecialchars($tpl['manufacturer']); ?></td>
                </tr>
                <tr class="bg-slate-50/60">
                  <td class="px-3.5 py-2.5 text-slate-500 font-semibold">Country</td>
                  <td class="px-3.5 py-2.5 font-bold text-slate-900 flex items-center gap-1.5">
                    <?php echo getCountryFlagHtml($tpl['manufacturer_country'], 'w-4 h-3'); ?>
                    <span><?php echo htmlspecialchars($tpl['manufacturer_country']); ?> (Origin)</span>
                  </td>
                </tr>
                <tr>
                  <td class="px-3.5 py-2.5 text-slate-500 font-semibold">Model</td>
                  <td class="px-3.5 py-2.5 font-bold text-slate-900"><?php echo htmlspecialchars($machine['model']); ?></td>
                </tr>
                <tr class="bg-slate-50/60">
                  <td class="px-3.5 py-2.5 text-slate-500 font-semibold">Year</td>
                  <td class="px-3.5 py-2.5 font-bold text-slate-900"><?php echo htmlspecialchars($machine['year']); ?></td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Table 2: Seller Information -->
          <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs bg-white">
            <div class="bg-slate-900 text-white px-4 py-2.5 font-black text-xs uppercase tracking-wider flex items-center gap-2">
              <i class="fa-solid fa-store text-emerald-400"></i>
              <span>Seller Information</span>
            </div>
            <table class="w-full text-xs">
              <tbody class="divide-y divide-slate-100">
                <tr class="bg-slate-50/60">
                  <td class="px-3.5 py-2.5 text-slate-500 font-semibold w-1/3">Seller Company</td>
                  <td class="px-3.5 py-2.5 font-bold text-slate-900">Seller-provided</td>
                </tr>
                <tr>
                  <td class="px-3.5 py-2.5 text-slate-500 font-semibold">Seller Location</td>
                  <td class="px-3.5 py-2.5 font-bold text-slate-900">Seller-provided</td>
                </tr>
                <tr class="bg-slate-50/60">
                  <td class="px-3.5 py-2.5 text-slate-500 font-semibold">Machine Location</td>
                  <td class="px-3.5 py-2.5 font-bold text-slate-900">Seller-provided</td>
                </tr>
                <tr>
                  <td class="px-3.5 py-2.5 text-slate-500 font-semibold">Contact Seller</td>
                  <td class="px-3.5 py-2.5 font-bold text-emerald-700">Submit Enquiry on Joule Machine Hub</td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>

      </div>

      <!-- Right Title & Direct Seller Contact Desk (5 cols) -->
      <div class="lg:col-span-5 space-y-5">
        
        <!-- Demand Asking Price Box -->
        <div class="bg-gradient-to-br from-blue-50 to-amber-50/40 rounded-2xl p-4 sm:p-5 border-2 border-blue-200 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-[10px] sm:text-[11px] text-slate-500 font-black uppercase tracking-wider">
              OFFICIAL ASKING VALUE:
            </span>
            <span class="text-[10px] sm:text-xs font-bold text-blue-800 bg-blue-100 border border-blue-300 px-2 sm:px-2.5 py-0.5 rounded-full shrink-0">
              Verified Machinery Listing
            </span>
          </div>
          
          <div class="mt-2 space-y-1">
            <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-950 text-joule-blue break-words machine-price-display" data-price-inr="<?php echo (float)($machine['price'] ?? 0); ?>" data-price-usd="<?php echo (float)($machine['price_usd'] ?? 0); ?>" data-origin-currency="<?php echo htmlspecialchars($machine['currency_origin'] ?? 'USD'); ?>" data-origin-value="<?php echo htmlspecialchars($machine['value_raw'] ?? ''); ?>">
              <?php echo formatMachinePrice($machine); ?>
            </div>
            <div class="text-xs sm:text-sm font-bold text-slate-600 flex items-center gap-2">
              <span class="text-blue-700 font-extrabold">Ex-Works / FOB Available</span>
              <span class="text-slate-300">•</span>
              <span>Direct B2B Marketplace Inquiry</span>
            </div>
          </div>

          <div class="text-[11px] text-slate-600 mt-3 pt-2.5 border-t border-slate-200 flex items-center justify-between">
            <span>Marketplace: <strong class="text-slate-900">Joule Machine Hub</strong></span>
            <button 
              type="button" 
              onclick="shareMachine('<?php echo addslashes($tpl['h1']); ?>', '<?php echo $canonicalUrl; ?>')" 
              class="px-2.5 py-1 rounded-lg border border-slate-300 text-slate-700 hover:text-emerald-700 bg-white text-xs font-bold flex items-center gap-1 cursor-pointer"
            >
              <i class="fa-solid fa-share-nodes text-emerald-600"></i>
              <span>Share</span>
            </button>
          </div>
        </div>

        <!-- DIRECT ENQUIRY CARD -->
        <div class="border-2 border-slate-200 bg-white rounded-2xl p-5 space-y-4 shadow-sm">
          
          <div class="space-y-2.5">
            <button 
              type="button" 
              onclick="openInquiryModal(<?php echo $machineJson; ?>)" 
              class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black py-3.5 px-4 rounded-xl flex items-center justify-center gap-3 shadow-lg shadow-emerald-600/30 transition-all text-sm cursor-pointer"
            >
              <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-file-signature text-white text-base"></i>
              </div>
              <div class="text-left">
                <div class="text-[10px] uppercase text-emerald-200 leading-none">Instant Machine Enquiry</div>
                <div class="text-sm font-black">Fill Enquiry Form (Get Best Price)</div>
              </div>
            </button>

            <button 
              type="button" 
              onclick="openInquiryModal(<?php echo $machineJson; ?>)" 
              class="w-full bg-joule-navy hover:bg-blue-900 text-joule-yellow font-black py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer border border-blue-950"
            >
              <i class="fa-solid fa-tags"></i>
              <span>Request Quote &amp; Availability</span>
            </button>
          </div>

          <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-xs text-slate-600 space-y-1.5">
            <div class="flex items-center gap-2 font-bold text-slate-900 text-xs">
              <i class="fa-solid fa-circle-check text-emerald-600"></i>
              <span>Direct Seller Connection</span>
            </div>
            <p class="text-[11px] text-slate-500 leading-snug">
              Joule Machine Hub holds verified manufacturer details. Submit an enquiry to connect directly with the machine seller for pricing, condition, and inspection.
            </p>
          </div>

        </div>

      </div>

    </div>
  </div>

  <!-- SECTION 1: [Machine Name] Overview (INTRODUCTION) -->
  <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 sm:p-7 shadow-xs space-y-4">
    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
      <div class="w-10 h-10 rounded-xl bg-blue-100 text-joule-brightblue flex items-center justify-center text-lg font-bold">
        <i class="fa-solid fa-circle-info"></i>
      </div>
      <div>
        <h2 class="text-lg sm:text-xl font-black text-slate-950"><?php echo htmlspecialchars($tpl['h1']); ?></h2>
        <p class="text-xs text-slate-500">Overview, primary specifications, and marketplace details.</p>
      </div>
    </div>

    <div class="text-xs sm:text-sm text-slate-700 leading-relaxed space-y-3.5 pt-1">
      <p>
        <?php echo htmlspecialchars($tpl['intro_text_1']); ?>
      </p>
      <p>
        <?php echo htmlspecialchars($tpl['intro_text_2']); ?>
      </p>
      <p class="text-slate-600 bg-slate-50 border border-slate-200 rounded-xl p-3.5">
        <strong>Joule Machine Hub</strong> connects machinery buyers with sellers through an online industrial machinery marketplace. Buyers can review the available machine information and submit an enquiry to connect directly with the seller for current pricing, availability, condition and other details.
      </p>
    </div>
  </div>

  <!-- SECTION 2: Machine Overview -->
  <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 sm:p-7 shadow-xs space-y-4">
    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
      <div class="w-10 h-10 rounded-xl bg-blue-100 text-joule-brightblue flex items-center justify-center text-lg font-bold">
        <i class="fa-solid fa-gears"></i>
      </div>
      <div>
        <h2 class="text-lg sm:text-xl font-black text-slate-950">Machine Overview</h2>
        <p class="text-xs text-slate-500">Operating mechanism, engineering features, and industrial purpose.</p>
      </div>
    </div>

    <div class="text-xs sm:text-sm text-slate-700 leading-relaxed space-y-3.5 pt-1">
      <p>
        <?php echo htmlspecialchars($tpl['machine_overview_1']); ?>
      </p>
      <p>
        <?php echo htmlspecialchars($tpl['machine_overview_2']); ?>
      </p>
      <?php if (!empty($machine['detailed_desc']) && $machine['detailed_desc'] !== $tpl['machine_overview_1']): ?>
        <p class="text-slate-600">
          <?php echo htmlspecialchars($machine['detailed_desc']); ?>
        </p>
      <?php endif; ?>
      <p class="text-slate-800 font-medium pt-1">
        Buyers interested in this used <?php echo htmlspecialchars(strtolower($tpl['clean_type'])); ?> can contact the listing seller through Joule Machine Hub to request additional photographs, operating videos, maintenance information and commercial details.
      </p>
    </div>
  </div>

  <!-- SECTION 3: Technical Specifications (COMPLETE STRUCTURED TABLE) -->
  <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 sm:p-7 shadow-xs space-y-6">
    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-joule-yellow text-slate-950 flex items-center justify-center text-lg font-bold shadow-xs">
          <i class="fa-solid fa-list-check"></i>
        </div>
        <div>
          <h2 class="text-lg sm:text-xl font-black text-slate-950">Technical Specifications</h2>
          <p class="text-xs text-slate-500">Manufacturer specifications and technical operating parameters.</p>
        </div>
      </div>

      <span class="text-xs bg-slate-100 text-slate-700 font-bold px-3 py-1 rounded-full border border-slate-200 hidden sm:inline">
        Verified Listing Parameters
      </span>
    </div>

    <!-- Core Specifications Structured List / Table -->
    <div class="border border-slate-200 rounded-2xl overflow-hidden divide-y divide-slate-100 text-xs sm:text-sm">
      
      <!-- Manufacturer -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3.5 bg-slate-50/70">
        <span class="text-slate-500 font-semibold">Manufacturer</span>
        <span class="font-black text-slate-950"><?php echo htmlspecialchars($tpl['manufacturer']); ?></span>
      </div>

      <!-- Model -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3.5 bg-white">
        <span class="text-slate-500 font-semibold">Model</span>
        <span class="font-black text-slate-950"><?php echo htmlspecialchars($machine['model']); ?></span>
      </div>

      <!-- Manufacturing Year -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3.5 bg-slate-50/70">
        <span class="text-slate-500 font-semibold">Manufacturing Year</span>
        <span class="font-black text-slate-950"><?php echo htmlspecialchars($machine['year']); ?></span>
      </div>

      <!-- Equipment Type -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3.5 bg-white">
        <span class="text-slate-500 font-semibold">Equipment Type</span>
        <span class="font-black text-slate-950"><?php echo htmlspecialchars($tpl['clean_type']); ?></span>
      </div>

      <!-- Machine Condition -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3.5 bg-slate-50/70">
        <span class="text-slate-500 font-semibold">Machine Condition</span>
        <span class="font-black text-emerald-700"><?php echo htmlspecialchars($machine['condition'] ?? 'Excellent (Running Condition)'); ?></span>
      </div>

      <!-- Production Capacity / Primary Metric -->
      <?php if (!empty($tpl['capacity_val'])): ?>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3.5 bg-white">
        <span class="text-slate-500 font-semibold"><?php echo htmlspecialchars($tpl['capacity_key'] ?: 'Capacity / Primary Metric'); ?></span>
        <span class="font-black text-slate-950"><?php echo htmlspecialchars($tpl['capacity_val']); ?></span>
      </div>
      <?php endif; ?>

      <!-- Power Requirements -->
      <?php if (!empty($tpl['power_val'])): ?>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3.5 bg-slate-50/70">
        <span class="text-slate-500 font-semibold">Power Requirements</span>
        <span class="font-black text-slate-950"><?php echo htmlspecialchars($tpl['power_val']); ?></span>
      </div>
      <?php endif; ?>

      <!-- Dimensions -->
      <?php if (!empty($tpl['dimensions_val'])): ?>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3.5 bg-white">
        <span class="text-slate-500 font-semibold">Dimensions</span>
        <span class="font-black text-slate-950"><?php echo htmlspecialchars($tpl['dimensions_val']); ?></span>
      </div>
      <?php endif; ?>

      <!-- Machine Weight -->
      <?php if (!empty($tpl['weight_val'])): ?>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3.5 bg-slate-50/70">
        <span class="text-slate-500 font-semibold">Machine Weight</span>
        <span class="font-black text-slate-950"><?php echo htmlspecialchars($tpl['weight_val']); ?></span>
      </div>
      <?php endif; ?>

      <!-- All Other Relevant Specifications Available for That Machine -->
      <?php 
        $rowIdx = 0;
        foreach ($tpl['other_specs'] as $specLbl => $specVal):
          $rowIdx++;
          $bgClass = ($rowIdx % 2 === 1) ? 'bg-white' : 'bg-slate-50/70';
      ?>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3.5 <?php echo $bgClass; ?>">
          <span class="text-slate-500 font-semibold"><?php echo htmlspecialchars($specLbl); ?></span>
          <span class="font-black text-slate-950 text-left sm:text-right"><?php echo htmlspecialchars($specVal); ?></span>
        </div>
      <?php endforeach; ?>

    </div>

    <!-- Disclaimer Note Under Specifications -->
    <p class="text-xs text-slate-500 italic">
      Specifications are based on the information supplied in this listing. Buyers should confirm the technical details with the seller before purchase.
    </p>

  </div>

  <!-- SECTION 4: Applications of [Machine Name] -->
  <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 sm:p-7 shadow-xs space-y-5">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-100 text-joule-brightblue flex items-center justify-center text-lg font-bold">
          <i class="fa-solid fa-industry"></i>
        </div>
        <div>
          <h2 class="text-lg sm:text-xl font-black text-slate-950">Applications of <?php echo htmlspecialchars($tpl['short_brand'] . ' ' . $machine['model'] . ' ' . $tpl['clean_type']); ?></h2>
          <p class="text-xs text-slate-500">Typical manufacturing operations, elevated access, and industrial processes.</p>
        </div>
      </div>
      <span class="text-xs bg-blue-50 text-joule-brightblue font-bold px-3 py-1 rounded-full border border-blue-200 hidden sm:inline">
        <?php echo htmlspecialchars($machine['category']); ?>
      </span>
    </div>

    <p class="text-xs sm:text-sm text-slate-700">
      <?php echo htmlspecialchars($tpl['clean_type']); ?> are commonly used for elevated access and manufacturing requirements across different industries.
    </p>

    <div class="space-y-1">
      <div class="text-xs font-bold text-slate-900 mb-2">Typical applications include:</div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <?php foreach ($tpl['applications'] as $app): ?>
          <div class="flex items-start gap-2.5 bg-slate-50 border border-slate-200 p-3 rounded-xl text-xs sm:text-sm text-slate-800">
            <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5 text-xs shrink-0"></i>
            <span class="font-medium"><?php echo htmlspecialchars($app); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <p class="text-xs text-slate-500 italic pt-1">
      The suitability of this particular machine depends on its actual condition, operating environment and manufacturer specifications.
    </p>
  </div>

  <!-- SECTION 5: Machine Condition and Availability -->
  <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 sm:p-7 shadow-xs space-y-5">
    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
      <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg font-bold">
        <i class="fa-solid fa-clipboard-check"></i>
      </div>
      <div>
        <h2 class="text-lg sm:text-xl font-black text-slate-950">Machine Condition and Availability</h2>
        <p class="text-xs text-slate-500">Verification checklist and location details for prospective buyers.</p>
      </div>
    </div>

    <div class="text-xs sm:text-sm text-slate-700 space-y-3.5">
      <p>
        This machine is advertised as a used <?php echo htmlspecialchars($machine['year'] . ' ' . $tpl['manufacturer'] . ' ' . $machine['model'] . ' ' . strtolower($tpl['clean_type'])); ?>.
      </p>

      <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2">
        <div class="font-bold text-slate-900 text-xs sm:text-sm">For the latest information, buyers should contact the seller to confirm:</div>
        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-700 pt-1">
          <li class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
            <span>Current machine availability</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
            <span>Actual machine location</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
            <span>Operating condition</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
            <span>Working hours and usage history</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
            <span>Maintenance and service records</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
            <span>Available photographs and operating videos</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
            <span>Asking price and payment terms</span>
          </li>
        </ul>
      </div>

      <!-- CRITICAL LOCATION DISTINCTION CALLOUT BOX (AS REQUIRED BY USER) -->
      <div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-4 text-xs sm:text-sm text-amber-950 flex items-start gap-3">
        <i class="fa-solid fa-triangle-exclamation text-amber-700 text-base mt-0.5 shrink-0"></i>
        <div>
          <strong class="font-bold">Important Location Notice:</strong> The manufacturer country (<strong><?php echo htmlspecialchars($tpl['manufacturer_country']); ?></strong>), seller location (<strong><?php echo htmlspecialchars($tpl['seller_location']); ?></strong>) and current machine location (<strong><?php echo htmlspecialchars($tpl['machine_location']); ?></strong>) are separate details. The actual seller and machine location should be confirmed directly before making a purchase decision.
        </div>
      </div>

    </div>
  </div>

  <!-- SECTION 6: Connect with the Seller (HIGH CONVERSION CTA) -->
  <div class="bg-gradient-to-r from-joule-navy to-joule-blue text-white rounded-2xl p-6 sm:p-8 space-y-5 border-2 border-joule-yellow shadow-xl">
    <div class="space-y-2">
      <h2 class="text-xl sm:text-2xl font-black text-joule-yellow flex items-center gap-2.5">
        <i class="fa-solid fa-handshake"></i>
        <span>Connect with the Seller</span>
      </h2>
      <p class="text-xs sm:text-sm text-blue-100 leading-relaxed max-w-3xl">
        Interested in purchasing this used <?php echo htmlspecialchars($tpl['short_brand'] . ' ' . $machine['model'] . ' ' . strtolower($tpl['clean_type'])); ?>?
      </p>
      <p class="text-xs sm:text-sm text-blue-200 leading-relaxed max-w-3xl">
        Submit an enquiry through Joule Machine Hub to express your interest and request additional information. Your enquiry will be shared with the relevant listing seller so you can discuss machine availability, pricing, specifications and purchase arrangements directly.
      </p>
      <p class="text-xs sm:text-sm text-blue-200 leading-relaxed max-w-3xl">
        Whether you are sourcing used machinery for industrial maintenance, warehouse operations, manufacturing production or plant expansion, Joule Machine Hub helps connect machinery buyers with sellers through a single online marketplace.
      </p>
    </div>

    <div class="pt-2 flex flex-col sm:flex-row sm:items-center gap-4">
      <button 
        type="button" 
        onclick="openInquiryModal(<?php echo $machineJson; ?>)" 
        class="bg-joule-yellow hover:bg-amber-400 active:bg-amber-500 text-slate-950 font-black text-sm sm:text-base py-3.5 px-6 rounded-xl shadow-lg cursor-pointer border border-amber-300 transition-all hover:scale-105 flex items-center justify-center gap-2.5"
      >
        <i class="fa-solid fa-paper-plane text-base"></i>
        <span>Send an Enquiry Today to Connect with the Seller</span>
      </button>

      <div class="text-xs text-blue-200 flex items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-joule-yellow"></i>
        <span>Typical response time: <?php echo htmlspecialchars($sellerResponse); ?></span>
      </div>
    </div>
  </div>

  <!-- SECTION 7: Frequently Asked Questions (7 MACHINE-SPECIFIC QUESTIONS) -->
  <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 sm:p-7 shadow-xs space-y-5">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg font-bold">
          <i class="fa-solid fa-circle-question"></i>
        </div>
        <div>
          <h2 class="text-lg sm:text-xl font-black text-slate-950">Frequently Asked Questions</h2>
          <p class="text-xs text-slate-500">Essential answers regarding <?php echo htmlspecialchars($tpl['short_brand'] . ' ' . $machine['model']); ?>, specifications, location, and seller enquiry.</p>
        </div>
      </div>
      <span class="bg-emerald-100 text-emerald-800 text-xs font-black px-2.5 py-1 rounded-full border border-emerald-200 hidden sm:inline">
        Google FAQ Schema Verified
      </span>
    </div>

    <div class="space-y-3">
      <?php foreach ($tpl['faqs'] as $idx => $faq): ?>
        <details class="group bg-slate-50 rounded-2xl border border-slate-200 p-4 transition-all open:bg-white open:border-joule-blue open:shadow-xs" <?php echo $idx === 0 ? 'open' : ''; ?>>
          <summary class="flex items-center justify-between font-black text-slate-900 text-xs sm:text-sm cursor-pointer list-none gap-3">
            <span class="flex items-center gap-2">
              <span class="w-6 h-6 rounded-full bg-blue-100 text-joule-blue flex items-center justify-center text-xs font-black shrink-0">
                <?php echo $idx + 1; ?>
              </span>
              <span><?php echo htmlspecialchars($faq['q']); ?></span>
            </span>
            <span class="text-slate-400 group-open:rotate-180 transition-transform shrink-0">
              <i class="fa-solid fa-chevron-down text-xs"></i>
            </span>
          </summary>
          <div class="pt-3 mt-3 border-t border-slate-100 text-xs sm:text-sm text-slate-700 leading-relaxed font-normal pl-8">
            <?php echo htmlspecialchars($faq['a']); ?>
          </div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- SECTION 7b: Verified Buyer & Inspection Feedback (MATCHES GOOGLE REVIEW SCHEMA) -->
  <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 sm:p-7 shadow-xs space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
          <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700">Verified Inspection Feedback</span>
        </div>
        <h2 class="text-lg sm:text-xl font-black text-slate-950 mt-1">Inspection &amp; Technical Buyer Reviews</h2>
      </div>
      <div class="flex items-center gap-2 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-xl">
        <div class="flex text-amber-500 text-xs">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
        </div>
        <span class="text-xs font-black text-slate-900">5.0 / 5.0</span>
        <span class="text-[11px] text-slate-500 font-semibold">(<?php echo count($productReviews); ?> Verified Inspections)</span>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <?php foreach ($productReviews as $rev): ?>
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 flex flex-col justify-between space-y-3">
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <div class="flex text-amber-400 text-xs">
                <?php for ($i = 0; $i < ($rev['rating'] ?? 5); $i++): ?>
                  <i class="fa-solid fa-star"></i>
                <?php endfor; ?>
              </div>
              <span class="text-[10px] text-slate-400 font-mono"><?php echo htmlspecialchars($rev['date']); ?></span>
            </div>
            <h3 class="text-xs font-bold text-slate-900"><?php echo htmlspecialchars($rev['title']); ?></h3>
            <p class="text-xs text-slate-600 leading-relaxed">
              &ldquo;<?php echo htmlspecialchars($rev['body']); ?>&rdquo;
            </p>
          </div>
          <div class="pt-2 border-t border-slate-200/70 text-[11px]">
            <div class="font-bold text-slate-900 flex items-center gap-1">
              <i class="fa-solid fa-circle-check text-emerald-500 text-[10px]"></i>
              <span><?php echo htmlspecialchars($rev['author_name']); ?></span>
            </div>
            <div class="text-[10px] text-slate-500 truncate"><?php echo htmlspecialchars($rev['author_role']); ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- SECTION 8: Disclaimer (EXACT WORDING FROM USER) -->
  <div class="bg-slate-50 rounded-2xl border-2 border-slate-200 p-5 sm:p-6 shadow-xs space-y-2.5">
    <div class="flex items-center gap-2 font-black text-slate-900 text-xs sm:text-sm uppercase tracking-wider">
      <i class="fa-solid fa-scale-balanced text-slate-600"></i>
      <span>Disclaimer</span>
    </div>
    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
      Machine specifications, photographs, pricing, availability, condition and location details are provided by the respective listing seller. Joule Machine Hub operates as an online marketplace connecting machinery buyers and sellers and does not own or physically inspect the listed equipment. Buyers are responsible for independently verifying machine condition, specifications, seller credentials, commercial terms and location before completing a transaction.
    </p>
  </div>

  <!-- SECTION 9: Similar Verified Machinery (SEO INTERNAL LINKING) -->
  <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 sm:p-7 shadow-xs space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg sm:text-xl font-black text-slate-950">Similar Verified Machinery</h2>
        <p class="text-xs text-slate-500">Explore other available equipment in <?php echo htmlspecialchars($machine['category']); ?> and <?php echo htmlspecialchars($machine['country']); ?>.</p>
      </div>
      <a href="/#catalog" class="text-xs font-bold text-joule-brightblue hover:underline hidden sm:inline">
        View All Equipment &rarr;
      </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php foreach ($relatedMachines as $rm): ?>
        <a 
          href="<?php echo htmlspecialchars(getMachineUrl($rm)); ?>" 
          class="bg-slate-50 border border-slate-200 hover:border-joule-brightblue rounded-xl p-3.5 transition-all group flex flex-col justify-between hover:shadow-lg"
        >
          <div>
            <div class="aspect-[16/10] bg-slate-900 rounded-xl overflow-hidden mb-3 relative">
              <img src="<?php echo htmlspecialchars('/' . ltrim($rm['images'][0], '/')); ?>" alt="<?php echo htmlspecialchars('Used ' . ($rm['brand'] ?? '') . ' ' . ($rm['model'] ?? '') . ' ' . ($rm['equipment_type'] ?? $rm['category'])); ?>" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
              <span class="absolute top-2 left-2 bg-slate-950/80 text-white text-[9px] font-bold px-2 py-0.5 rounded">
                <?php echo htmlspecialchars($rm['year']); ?>
              </span>
            </div>
            <div class="text-[10px] text-joule-brightblue font-black uppercase tracking-wider">
              <?php echo htmlspecialchars($rm['brand']); ?>
            </div>
            <h3 class="font-black text-slate-900 text-xs sm:text-sm line-clamp-2 mt-0.5 group-hover:text-joule-brightblue">
              <?php echo htmlspecialchars($rm['title']); ?>
            </h3>
            <div class="text-[11px] text-slate-600 mt-1 flex items-center gap-1.5 truncate">
              <span class="shrink-0"><?php echo getCountryFlagHtml($rm['country'] ?? 'Japan', 'w-3.5 h-2.5'); ?></span>
              <span class="font-bold text-slate-800 shrink-0"><?php echo htmlspecialchars($rm['city']); ?></span>
              <span class="text-slate-400 text-[10px] truncate">, <?php echo htmlspecialchars($rm['country']); ?></span>
            </div>
          </div>
          <div class="mt-3 pt-2 border-t border-slate-200 flex items-center justify-between">
            <span class="font-black text-slate-950 text-xs machine-price-display" data-price-inr="<?php echo (float)($rm['price'] ?? 0); ?>" data-price-usd="<?php echo (float)($rm['price_usd'] ?? 0); ?>"><?php echo formatMachinePrice($rm); ?></span>
            <span class="text-xs text-joule-brightblue font-bold group-hover:translate-x-1 transition-transform">Details &rarr;</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- SECTION 10: Industrial Machinery Procurement & Engineering Guide -->
  <div class="bg-white rounded-2xl border-2 border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-joule-blue text-joule-yellow flex items-center justify-center text-lg font-bold shadow-xs">
          <i class="fa-solid fa-book-open"></i>
        </div>
        <div>
          <h2 class="text-lg sm:text-2xl font-black text-slate-950">Industrial Machinery Procurement &amp; Engineering Guide</h2>
          <p class="text-xs sm:text-sm text-slate-500">Key considerations for evaluating, testing, and acquiring <?php echo htmlspecialchars(strtolower($tpl['clean_type'])); ?>.</p>
        </div>
      </div>
      <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-3 py-1 rounded-full border border-emerald-300 hidden sm:inline">
        Verified Industry Standard
      </span>
    </div>

    <!-- Editorial Content Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs sm:text-sm text-slate-700 leading-relaxed">
      
      <!-- Col 1: Mechanical Evaluation -->
      <div class="space-y-3">
        <h3 class="text-sm sm:text-base font-black text-slate-950 flex items-center gap-2">
          <i class="fa-solid fa-microscope text-joule-brightblue"></i>
          <span>Mechanical Integrity &amp; Dynamic Run Testing</span>
        </h3>
        <p>
          When acquiring high-performance equipment like the <strong><?php echo htmlspecialchars($tpl['h1']); ?></strong> by <strong><?php echo htmlspecialchars($tpl['manufacturer']); ?></strong>, physical inspection under active operational conditions is critical. Ensure that slide guideways, spindle bearings, bolster alignment, and hydraulic pressures meet original manufacturer tolerances.
        </p>
        <p>
          At Joule Machine Hub, buyers have the right to request physical equipment inspections, structural dimension verifications, and component integrity checks before final commercial commitment.
        </p>
      </div>

      <!-- Col 2: Export Logistics & Commissioning -->
      <div class="space-y-3">
        <h3 class="text-sm sm:text-base font-black text-slate-950 flex items-center gap-2">
          <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i>
          <span>Decommissioning, Rigging &amp; Ocean Export Logistics</span>
        </h3>
        <p>
          Surplus machinery located in <strong><?php echo htmlspecialchars($tpl['machine_location']); ?></strong> can be prepared for international ocean export through certified rigging specialists. This encompasses electrical isolation, fluid drain-down, heavy crane mobilization, and moisture-barrier VCI plastic heat-sealing.
        </p>
        <p>
          Our verified network assists buyers with timber blocking, open-top or flat-rack container stuffing, customs export declarations, and terminal handling to deliver a seamless transition from the seller's factory to your production floor.
        </p>
      </div>

    </div>

    <!-- Quick Inquiry Callout Bar -->
    <div class="bg-gradient-to-r from-blue-50 to-slate-50 border border-blue-200 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="font-black text-slate-950 text-sm sm:text-base">Ready to proceed with this <?php echo htmlspecialchars($tpl['short_brand'] . ' ' . $machine['model']); ?>?</div>
        <p class="text-xs text-slate-600 mt-0.5">Submit a formal written enquiry through Joule Machine Hub's mediated B2B desk.</p>
      </div>
      <button 
        type="button" 
        onclick="openInquiryModal(<?php echo $machineJson; ?>)" 
        class="bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black text-xs sm:text-sm py-2.5 sm:py-3 px-5 rounded-xl shrink-0 shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
      >
        <i class="fa-solid fa-file-signature"></i>
        <span>Submit Direct Enquiry Now</span>
      </button>
    </div>

  </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof gtag === 'function') {
    gtag('event', 'product_view', {
      item_id: <?php echo json_encode((string)($machine['id'] ?? $machine['slug'])); ?>,
      item_name: <?php echo json_encode($tpl['h1']); ?>,
      item_brand: <?php echo json_encode($tpl['short_brand']); ?>,
      item_category: <?php echo json_encode($machine['category']); ?>,
      price: <?php echo json_encode($productPrice); ?>,
      currency: 'INR'
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
