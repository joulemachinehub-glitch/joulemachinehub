<?php
// Joule Machine Hub - Dedicated Manufacturer / Brand Hub Page
// Features priority manufacturers: Komatsu, Amada, Dingli, Haitian & all inventory OEMs
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';

$mfgSlug = isset($_GET['manufacturer']) ? strtolower(trim($_GET['manufacturer'])) : '';

// Priority Manufacturer Profiles
$mfgProfiles = [
    'komatsu' => [
        'name' => 'Komatsu',
        'full_name' => 'Komatsu Ltd. / Komatsu Industries Corp.',
        'country' => 'Japan',
        'flag' => '🇯🇵',
        'headquarters' => 'Tokyo, Japan',
        'specialty' => 'Heavy Mechanical Power Presses & Stamping Systems',
        'h1' => 'Used Komatsu Industrial Machinery for Sale',
        'meta_title' => 'Used Komatsu Machinery for Sale | Power Presses & Stamping | Joule Machine Hub',
        'meta_desc' => 'Explore verified used Komatsu industrial machinery for sale. Browse mechanical straight-side power presses, gap-frame stamping presses, and plant floor listings on Joule Machine Hub.',
        'intro' => 'Komatsu is a premier Japanese industrial engineering powerhouse celebrated globally for heavy-duty metal stamping presses, straight-side double-crank presses, and sheet metal fabrication equipment. Known for massive cast-iron frames, minimal bed deflection, and long-lasting hydraulic clutch mechanisms, used Komatsu power presses remain top choices for automotive component stamping and progressive die manufacturing worldwide.',
        'tech_overview' => 'Komatsu mechanical power presses (including the celebrated E2M and H1F series) feature rigid straight-side or heavy C-frame structures, hydraulic overload protection (HOLP), hardened alloy steel eccentric shafts, and high-performance wet or pneumatic clutch/brake packages. Their high dynamic rigidity ensures exceptional die life and tight part stamping tolerances across multi-shift duty cycles.',
        'faqs' => [
            [
                'q' => 'What types of used Komatsu machinery are available on Joule Machine Hub?',
                'a' => 'We feature verified listings of used Komatsu mechanical power presses, including 200-ton and 300-ton straight-side presses and gap-frame stamping equipment directly from manufacturing facilities.'
            ],
            [
                'q' => 'How can I verify the authentic build year and specifications of a Komatsu press?',
                'a' => 'Every listing includes photographs of the original stamped metal Komatsu builder plate riveted to the press body, detailing model number, serial number, stroke length, tonnage, and build date.'
            ],
            [
                'q' => 'Is Joule Machine Hub an authorized Komatsu distributor?',
                'a' => 'No. Joule Machine Hub operates as an independent global B2B machinery marketplace connecting machinery buyers with verified private machine owners, dealers, and plant liquidation sellers. We are not officially affiliated with or authorized by Komatsu Ltd.'
            ],
            [
                'q' => 'Can I schedule a physical inspection for a used Komatsu press?',
                'a' => 'Yes. Submit an enquiry through the machine listing to coordinate directly with the seller for plant visits, dry-run inspections, and full technical walk-throughs.'
            ]
        ]
    ],

    'amada' => [
        'name' => 'Amada',
        'full_name' => 'Amada Co., Ltd.',
        'country' => 'Japan',
        'flag' => '🇯🇵',
        'headquarters' => 'Kanagawa, Japan',
        'specialty' => 'Precision Mechanical Power Presses & Sheet Metal Machinery',
        'h1' => 'Used Amada Industrial Machinery for Sale',
        'meta_title' => 'Used Amada Machinery for Sale | Power Presses & Stamping | Joule Machine Hub',
        'meta_desc' => 'Buy verified used Amada machinery for sale. Explore heavy mechanical power presses, straight-side stamping equipment, and sheet metal fabrication machines on Joule Machine Hub.',
        'intro' => 'Amada Co., Ltd. is globally recognized as an elite Japanese manufacturer of high-precision metal stamping presses, sheet metal shearing systems, and turret punch presses. Amada power presses (such as the PDL and TPX series) deliver remarkable structural integrity, advanced bridge frames, and precision ram guiding, making them industry benchmarks for progressive stamping, blanking, and forming.',
        'tech_overview' => 'Amada stamping equipment is engineered with rigid bridge frames, precision 8-point gib guiding, and electronic digital stroke monitors. The heavy bolster beds and low-deflection tie-rod designs allow high-tonnage stamping with minimal tool wear, supporting tight-tolerance automotive and electrical stamping runs.',
        'faqs' => [
            [
                'q' => 'What used Amada models can be found on Joule Machine Hub?',
                'a' => 'Our marketplace features verified used Amada equipment such as the PDL-300 300-ton mechanical power press and related sheet metal fabrication machinery from verified plant floors.'
            ],
            [
                'q' => 'What inspection steps are recommended before buying a used Amada press?',
                'a' => 'Check the pneumatic clutch and brake timing, slide parallelism across the bolster plate, crankshaft bearing clearances, and the operation of hydraulic overload release systems.'
            ],
            [
                'q' => 'Is Joule Machine Hub affiliated with Amada Co., Ltd.?',
                'a' => 'No. Joule Machine Hub is an independent online marketplace connecting buyers and sellers of secondary industrial machinery. We do not claim official authorization or affiliation with Amada Co., Ltd.'
            ],
            [
                'q' => 'How are ocean transport and export packing arranged for Amada presses?',
                'a' => 'Machine transport requires crane rigging, moisture-proof shrink wrapping, and specialized low-bed trailers to seaport terminals, handled via experienced multimodal freight partners.'
            ]
        ]
    ],

    'dingli' => [
        'name' => 'Dingli',
        'full_name' => 'Zhejiang Dingli Machinery Co., Ltd.',
        'country' => 'China',
        'flag' => '🇨🇳',
        'headquarters' => 'Zhejiang, China',
        'specialty' => 'Electric & Rough-Terrain Aerial Work Platforms & Scissor Lifts',
        'h1' => 'Used Dingli Aerial Work Platforms & Scissor Lifts for Sale',
        'meta_title' => 'Used Dingli Scissor Lifts for Sale | Aerial Work Platforms | Joule Machine Hub',
        'meta_desc' => 'Explore verified used Dingli scissor lifts and aerial work platforms for sale. View electric self-propelled models, technical specs, and connect directly with sellers on Joule Machine Hub.',
        'intro' => 'Zhejiang Dingli is one of the world\'s premier manufacturers of intelligent aerial work platforms, producing electric scissor lifts, articulating boom lifts, and vertical mast platforms. Dingli machines feature heavy-duty hydraulic lifting scissor arms, energy-efficient 24V electric drive motors, and proportional joystick controls, providing dependable access for warehouse logistics, plant construction, and facility maintenance.',
        'tech_overview' => 'Dingli self-propelled electric scissor lifts (such as the JCPT1412HD) deliver working heights up to 13.8 meters with rated capacities of 320 kg (2 persons plus tools). Engineered with non-marking solid tires, roll-out deck extensions, automatic pothole protection, and dual emergency stop circuits, Dingli lifts provide exceptional maneuverability in tight warehouse aisles.',
        'faqs' => [
            [
                'q' => 'What specifications distinguish the Dingli JCPT1412HD scissor lift?',
                'a' => 'The Dingli JCPT1412HD features a 13.8-meter working height, 320 kg load capacity, 24V DC / 4.5 kW power system, proportional electric drive, and compact 1.15m overall travel width.'
            ],
            [
                'q' => 'How can I check the battery and hydraulic health of a used Dingli lift?',
                'a' => 'Examine battery manufacturing dates, measure terminal voltage retention under load, and inspect hydraulic cylinder chrome rods and seal packing for leaks.'
            ],
            [
                'q' => 'Is Joule Machine Hub an official dealer for Zhejiang Dingli?',
                'a' => 'No. Joule Machine Hub is an independent B2B portal connecting machinery sellers and buyers. We do not represent Zhejiang Dingli in an official OEM capacity.'
            ],
            [
                'q' => 'Can Dingli scissor lifts be transported easily?',
                'a' => 'Yes. Folding guardrails and compact transport dimensions allow Dingli scissor lifts to be loaded onto tilt-tray tow trucks or packed inside standard ISO shipping containers.'
            ]
        ]
    ],

    'haitian' => [
        'name' => 'Haitian',
        'full_name' => 'Haitian Plastics Machinery Group Co., Ltd.',
        'country' => 'China',
        'flag' => '🇨🇳',
        'headquarters' => 'Ningbo, China',
        'specialty' => 'Servo-Hydraulic & All-Electric Plastic Injection Molding Machines',
        'h1' => 'Used Haitian Plastic Injection Molding Machines for Sale',
        'meta_title' => 'Used Haitian Injection Molding Machines for Sale | Mars II Series | Joule Machine Hub',
        'meta_desc' => 'Buy verified used Haitian plastic injection molding machines for sale. Browse Haitian Mars II series servo-hydraulic presses from 120T to 1000T on Joule Machine Hub.',
        'intro' => 'Haitian Plastics Machinery is the world\'s largest manufacturer of plastic injection molding machines by unit volume. The flagship Haitian Mars Series (MA series) revolutionized energy efficiency by pairing permanent magnet servo motors with high-pressure gear pumps, delivering up to 50% power savings, fast cycle times, and shot-weight repeatability for automotive, consumer electronics, and packaging molding.',
        'tech_overview' => 'Haitian Mars II machines utilize 5-point twin-toggle clamping mechanisms, hardened bimetallic barrels, high-efficiency servo drives, and multilingual KEBA microcomputer controllers. The servo pump architecture minimizes oil heating and delivers precise pressure-holding control for demanding technical engineering plastics.',
        'faqs' => [
            [
                'q' => 'Why is the Haitian Mars II series so popular on the secondary market?',
                'a' => 'Haitian Mars II machines combine exceptional energy savings (via permanent magnet servo hydraulics), robust toggle clamping, affordable spare parts availability, and universal technician familiarity worldwide.'
            ],
            [
                'q' => 'What clamping tonnages are available for used Haitian machines?',
                'a' => 'Used Haitian machines commonly range from 120 tonnes (MA1200II) up to 1,000+ tonnes for large automotive panels and industrial crates.'
            ],
            [
                'q' => 'Is Joule Machine Hub an authorized Haitian agent?',
                'a' => 'No. Joule Machine Hub is an independent B2B industrial machinery marketplace and is not affiliated with or authorized by Haitian International.'
            ],
            [
                'q' => 'Can mold clamp testing and dry runs be arranged for Haitian machines?',
                'a' => 'Yes. Buyers can request operating videos, screw pull-out inspections, and scheduled factory visits directly through the seller via our mediator desk.'
            ]
        ]
    ]
];

// Determine active manufacturer
$currentMfg = null;
if (isset($mfgProfiles[$mfgSlug])) {
    $currentMfg = $mfgProfiles[$mfgSlug];
} else {
    // Dynamic fallback: search in machines by brand
    foreach ($machines as $m) {
        $bClean = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $m['brand'] ?? ''));
        $targetClean = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $mfgSlug));
        if ($bClean === $targetClean || str_contains($bClean, $targetClean)) {
            $brandName = trim($m['brand']);
            $currentMfg = [
                'name' => $brandName,
                'full_name' => $brandName,
                'country' => $m['country'] ?? 'International',
                'flag' => '🌐',
                'headquarters' => $m['country'] ?? 'International',
                'specialty' => $m['category'] ?? 'Industrial Machinery',
                'h1' => 'Used ' . $brandName . ' Industrial Machinery for Sale',
                'meta_title' => 'Used ' . $brandName . ' Machinery for Sale | Joule Machine Hub',
                'meta_desc' => 'Explore verified used ' . $brandName . ' machinery for sale. Browse specifications, photos, and contact sellers directly on Joule Machine Hub.',
                'intro' => $brandName . ' is an established industrial equipment builder. Browse verified machinery listings from verified sellers on our global marketplace.',
                'tech_overview' => 'Equipment manufactured by ' . $brandName . ' is engineered for industrial duty cycles and reliable plant production.',
                'faqs' => [
                    [
                        'q' => 'What types of ' . $brandName . ' machinery are listed on Joule Machine Hub?',
                        'a' => 'We feature verified listings of used ' . $brandName . ' equipment directly from manufacturing plants and machinery dealers.'
                    ],
                    [
                        'q' => 'How can I submit an enquiry for ' . $brandName . ' equipment?',
                        'a' => 'Click "Enquire Now" on any machine listing to connect directly with the seller via our B2B mediator desk.'
                    ]
                ]
            ];
            break;
        }
    }
}

// 404 if not found
if (!$currentMfg) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Filter machines by manufacturer
$mfgName = $currentMfg['name'];
$mfgMachines = array_filter($machines, function($m) use ($mfgName) {
    return stripos($m['brand'] ?? '', $mfgName) !== false || stripos($m['title'] ?? '', $mfgName) !== false;
});

// SEO Metadata Configuration
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$canonicalUrl = $protocol . $host . '/manufacturer/' . $mfgSlug;

$pageTitle = $currentMfg['meta_title'];
$pageDesc = $currentMfg['meta_desc'];
$pageKeywords = "used " . strtolower($mfgName) . " machinery, " . strtolower($mfgName) . " machines for sale, second hand " . strtolower($mfgName) . ", industrial " . strtolower($mfgName) . " equipment";
$ogTitle = $pageTitle;
$ogDesc = $pageDesc;
$ogImage = !empty($mfgMachines) ? ($protocol . $host . '/' . ltrim(reset($mfgMachines)['images'][0], '/')) : ($protocol . $host . '/assets/images/haas-vmc-1.jpg');
$ogType = "website";
$pageRobots = "index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1";

// JSON-LD Schema
$itemListElements = [];
$pos = 1;
foreach ($mfgMachines as $m) {
    $itemListElements[] = [
        '@type' => 'ListItem',
        'position' => $pos++,
        'name' => ($m['year'] ? $m['year'] . ' ' : '') . $m['title'],
        'url' => $protocol . $host . '/' . getMachineUrl($m),
        'image' => !empty($m['images'][0]) ? ($protocol . $host . '/' . ltrim($m['images'][0], '/')) : null
    ];
}

$faqEntities = [];
foreach ($currentMfg['faqs'] as $mf) {
    $faqEntities[] = [
        '@type' => 'Question',
        'name' => $mf['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $mf['a']
        ]
    ];
}

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
                    'name' => 'Manufacturers',
                    'item' => $protocol . $host . '/#catalog'
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $mfgName,
                    'item' => $canonicalUrl
                ]
            ]
        ],
        [
            '@type' => 'ItemList',
            'name' => 'Available Used ' . $mfgName . ' Machinery for Sale',
            'numberOfItems' => count($mfgMachines),
            'itemListElement' => $itemListElements
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => $faqEntities
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
    <a href="index.php#catalog" class="hover:text-joule-brightblue font-medium">Manufacturers</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-black flex items-center gap-1.5">
      <span><?php echo htmlspecialchars($mfgName); ?></span>
    </span>
  </div>
</div>

<!-- MANUFACTURER HERO BANNER -->
<section class="bg-gradient-to-br from-joule-navy via-joule-blue to-blue-950 text-white relative overflow-hidden border-b-4 border-joule-yellow py-10 sm:py-14">
  <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#facc15_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-4 relative z-10 space-y-6">
    <div class="max-w-3xl space-y-3">
      <div class="inline-flex items-center gap-2 bg-joule-yellow/20 border border-joule-yellow/50 text-joule-yellow px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider">
        <i class="fa-solid fa-industry"></i>
        <span>Industrial Machinery Manufacturer • <?php echo htmlspecialchars($currentMfg['flag'] . ' ' . $currentMfg['country']); ?></span>
      </div>

      <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-white leading-tight">
        <?php echo htmlspecialchars($currentMfg['h1']); ?>
      </h1>

      <p class="text-blue-100 text-xs sm:text-sm leading-relaxed font-normal">
        <?php echo htmlspecialchars($currentMfg['intro']); ?>
      </p>

      <div class="text-[11px] text-blue-200/80 bg-blue-950/60 border border-blue-400/20 rounded-xl p-3 mt-2">
        <i class="fa-solid fa-circle-info text-amber-400 mr-1"></i>
        <span><strong>Independent Marketplace Notice:</strong> Joule Machine Hub is an independent B2B industrial machinery marketplace and is not officially affiliated with or authorized by <?php echo htmlspecialchars($currentMfg['full_name']); ?>. All trademarks are the property of their respective owners.</span>
      </div>
    </div>

    <!-- Quick Stat Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 pt-4 border-t border-blue-800/80 text-xs">
      <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10">
        <div class="text-[10px] text-blue-200 uppercase font-bold">Specialty</div>
        <div class="text-white font-black text-xs line-clamp-1"><?php echo htmlspecialchars($currentMfg['specialty']); ?></div>
      </div>
      <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10">
        <div class="text-[10px] text-blue-200 uppercase font-bold">Origin / HQ</div>
        <div class="text-white font-black text-xs"><?php echo htmlspecialchars($currentMfg['headquarters']); ?></div>
      </div>
      <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10">
        <div class="text-[10px] text-blue-200 uppercase font-bold">Verified Units</div>
        <div class="text-joule-yellow font-black text-xs"><?php echo count($mfgMachines); ?> Available Now</div>
      </div>
      <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10">
        <div class="text-[10px] text-blue-200 uppercase font-bold">Verification</div>
        <div class="text-emerald-400 font-black text-xs">Stamped OEM Plates</div>
      </div>
    </div>
  </div>
</section>

<!-- MAIN CONTENT CONTAINER -->
<main class="max-w-7xl mx-auto px-4 py-8 sm:py-12 space-y-10">

  <!-- Machine Listings Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
    <div>
      <h2 class="text-xl sm:text-2xl font-black text-slate-950 flex items-center gap-2">
        <span>Available Used <?php echo htmlspecialchars($mfgName); ?> Machinery</span>
        <span class="text-xs bg-joule-yellow text-slate-950 font-black px-2.5 py-0.5 rounded-full border border-amber-300">
          <?php echo count($mfgMachines); ?> Units
        </span>
      </h2>
      <p class="text-xs text-slate-500 mt-0.5">
        Explore verified machines with stamped builder plates and submit direct enquiries.
      </p>
    </div>

    <div>
      <button type="button" onclick="openRequirementModal()" class="px-4 py-2 bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black text-xs rounded-xl shadow-xs border border-amber-300 cursor-pointer">
        <i class="fa-solid fa-file-signature mr-1"></i> Post RFQ for <?php echo htmlspecialchars($mfgName); ?>
      </button>
    </div>
  </div>

  <!-- Machine Card Grid -->
  <?php if (!empty($mfgMachines)): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php foreach ($mfgMachines as $m): 
        $url = getMachineUrl($m);
        $img = !empty($m['images'][0]) ? $m['images'][0] : 'assets/images/haas-vmc-1.jpg';
        $specs = $m['specs'] ?? [];
      ?>
        <div class="bg-white rounded-2xl border-2 border-slate-200 hover:border-joule-brightblue transition-all overflow-hidden flex flex-col shadow-xs hover:shadow-lg group">
          <a href="<?php echo htmlspecialchars($url); ?>" class="relative block aspect-[4/3] overflow-hidden bg-slate-950">
            <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($m['title']); ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            <div class="absolute top-2.5 left-2.5 bg-slate-950/80 backdrop-blur-md text-white text-[10px] font-black uppercase px-2.5 py-1 rounded-full border border-white/20">
              <?php echo htmlspecialchars($m['brand']); ?>
            </div>
            <?php if (!empty($m['year'])): ?>
              <div class="absolute top-2.5 right-2.5 bg-joule-yellow text-slate-950 text-[10px] font-black px-2.5 py-1 rounded-full shadow-xs">
                <?php echo htmlspecialchars($m['year']); ?>
              </div>
            <?php endif; ?>
          </a>

          <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
            <div>
              <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-1">
                <?php echo htmlspecialchars($m['category']); ?> • <?php echo htmlspecialchars($m['city'] . ', ' . $m['country']); ?>
              </div>
              <h3 class="font-black text-slate-900 text-sm group-hover:text-joule-brightblue transition-colors line-clamp-2">
                <a href="<?php echo htmlspecialchars($url); ?>"><?php echo htmlspecialchars($m['title']); ?></a>
              </h3>

              <?php if (!empty($specs)): ?>
                <div class="mt-2.5 space-y-1 text-[11px] text-slate-600 border-t border-slate-100 pt-2">
                  <?php foreach (array_slice($specs, 0, 3) as $k => $v): ?>
                    <div class="flex items-center justify-between">
                      <span class="text-slate-400"><?php echo htmlspecialchars($k); ?>:</span>
                      <span class="font-bold text-slate-800"><?php echo htmlspecialchars($v); ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
              <div>
                <span class="text-[10px] text-slate-400 block uppercase">Price</span>
                <span class="font-black text-sm text-slate-950"><?php echo htmlspecialchars($m['price_display'] ?? 'Contact Seller'); ?></span>
              </div>
              <a href="<?php echo htmlspecialchars($url); ?>" class="bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black text-xs px-3.5 py-2 rounded-xl transition-colors shadow-2xs border border-amber-300">
                View Machine →
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="bg-slate-50 border-2 border-dashed border-slate-300 rounded-2xl p-8 text-center space-y-3">
      <i class="fa-solid fa-boxes-packing text-slate-400 text-3xl"></i>
      <h3 class="font-black text-slate-900 text-base">No Current Listings for <?php echo htmlspecialchars($mfgName); ?></h3>
      <p class="text-xs text-slate-500 max-w-md mx-auto">
        We frequently receive surplus equipment from manufacturing plant liquidations. Submit your RFQ below to be alerted when matching <?php echo htmlspecialchars($mfgName); ?> machines arrive.
      </p>
      <button type="button" onclick="openRequirementModal()" class="px-5 py-2.5 bg-joule-yellow text-slate-950 font-black text-xs rounded-xl shadow-xs">
        Submit <?php echo htmlspecialchars($mfgName); ?> Requirement
      </button>
    </div>
  <?php endif; ?>

  <!-- Technical Overview Box -->
  <section class="bg-gradient-to-r from-blue-50 to-slate-50 rounded-2xl border-2 border-blue-200/80 p-6 sm:p-8 space-y-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-joule-blue text-joule-yellow flex items-center justify-center text-lg font-bold">
        <i class="fa-solid fa-gears"></i>
      </div>
      <div>
        <h2 class="text-base sm:text-lg font-black text-slate-950">
          Engineering &amp; Technical Capabilities: <?php echo htmlspecialchars($mfgName); ?>
        </h2>
        <p class="text-xs text-slate-600">
          Why manufacturing plants select used <?php echo htmlspecialchars($mfgName); ?> machinery for long-term production.
        </p>
      </div>
    </div>
    <div class="text-xs sm:text-sm text-slate-700 leading-relaxed pt-1">
      <?php echo htmlspecialchars($currentMfg['tech_overview']); ?>
    </div>
  </section>

  <!-- FAQs Section -->
  <?php if (!empty($currentMfg['faqs'])): ?>
    <section class="bg-white rounded-2xl border-2 border-slate-200 p-6 sm:p-8 space-y-6 shadow-xs">
      <div class="border-b border-slate-100 pb-4">
        <div class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-joule-brightblue bg-blue-100/80 border border-blue-200 px-3 py-1 rounded-full mb-2">
          <i class="fa-solid fa-circle-question"></i>
          <span>Frequently Asked Questions</span>
        </div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-950">
          FAQs – Buying Used <?php echo htmlspecialchars($mfgName); ?> Machinery
        </h2>
        <p class="text-xs text-slate-500 mt-1">
          Technical, inspection, and commercial questions answered for industrial buyers.
        </p>
      </div>

      <div class="space-y-3">
        <?php foreach ($currentMfg['faqs'] as $idx => $f): ?>
          <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 space-y-1.5">
            <h3 class="font-black text-slate-900 text-xs sm:text-sm flex items-start gap-2.5">
              <span class="w-5 h-5 rounded-md bg-blue-100 text-blue-700 font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5"><?php echo $idx + 1; ?></span>
              <span><?php echo htmlspecialchars($f['q']); ?></span>
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed pl-7">
              <?php echo htmlspecialchars($f['a']); ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Buyer RFQ CTA -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
        <div>
          <div class="font-black text-slate-900 text-sm">Need a specific <?php echo htmlspecialchars($mfgName); ?> model?</div>
          <div class="text-slate-600 text-[11px] mt-0.5">Submit your target machine specifications. Our mediator desk connects you with sellers globally.</div>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
          <button type="button" onclick="openRequirementModal()" class="px-4 py-2.5 bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black rounded-xl text-xs flex items-center gap-1.5 shadow-sm border border-amber-300 cursor-pointer">
            <i class="fa-solid fa-file-signature"></i>
            <span>Submit RFQ</span>
          </button>
          <a href="register.php?role=seller" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5">
            <i class="fa-solid fa-store text-amber-400"></i>
            <span>List <?php echo htmlspecialchars($mfgName); ?> Machine</span>
          </a>
        </div>
      </div>
    </section>
  <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
