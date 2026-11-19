<?php
// Joule Machine Hub - Dedicated Industrial City Hub & Verified Machinery Page
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';

// Determine requested city, fallback to Nagoya (available hub with multiple listings)
$cityParam = isset($_GET['city']) ? trim($_GET['city']) : 'Nagoya';
$cityData = getCityData($cityParam);
$cityName = $cityData['name'];
$countryName = $cityData['country'];
$countryFlag = $cityData['country_flag'];
$subzone = $cityData['subzone'];
$specialty = $cityData['specialty'];

// STRICT CLEAN SEO SLUG ENFORCEMENT: Redirect legacy .php URLs to canonical slug
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (str_contains($requestUri, 'city.php')) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: /" . getCityUrl($cityParam));
    exit;
}

$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : 'All Categories';
$sortBy = isset($_GET['sort']) ? trim($_GET['sort']) : 'featured';

// Filter machines strictly for this verified city
$cityMachines = array_values(array_filter($machines, function($m) use ($cityName, $selectedCategory) {
    if (strtolower(trim($m['city'])) !== strtolower(trim($cityName))) {
        return false;
    }
    if ($selectedCategory !== 'All Categories' && (!isset($m['category']) || strtolower($m['category']) !== strtolower($selectedCategory))) {
        return false;
    }
    return true;
}));

// Sort machines
usort($cityMachines, function($a, $b) use ($sortBy) {
    if ($sortBy === 'priceLow') return ($a['price'] ?? 0) - ($b['price'] ?? 0);
    if ($sortBy === 'priceHigh') return ($b['price'] ?? 0) - ($a['price'] ?? 0);
    if ($sortBy === 'yearNew') return ($b['year'] ?? 0) - ($a['year'] ?? 0);
    return ($b['is_featured'] ? 1 : 0) - ($a['is_featured'] ? 1 : 0);
});

// Accurate industrial profiles for ONLY the 8 active cities where machines are located
$cityProfiles = [
    'Nagoya' => [
        'state' => 'Aichi Prefecture, Japan',
        'estates' => 'Chukyo Heavy Industrial Belt, Minato & Meiko Machinery Corridor',
        'overview' => 'Nagoya is the metal forming and automotive manufacturing capital of Japan. Industrial facilities here operate high-precision parts formers by Daido Machinery, Ltd. and high-speed stamping presses from Yamada Dobby Co., Ltd.',
        'power_info' => '200V / 220V 50/60Hz stable industrial power with dual-feed substations for comprehensive plant inspections and equipment verifications.',
        'logistics' => 'Direct access to Port of Nagoya (Japan\'s largest cargo volume port) and Isewangan Expressway for ocean export container stuffing.',
    ],
    'Osaka' => [
        'state' => 'Kansai Region, Japan',
        'estates' => 'Kansai Precision Cluster, Higashiosaka & Sakai Industrial Zone',
        'overview' => 'Osaka and Higashiosaka represent Japan\'s premier center for cold heading and parts forming technology. Facilities here host heavy-tonnage parts formers from Joto Kikai Kogyo (Jyoto) with up to 290 Ton•f forging capacity.',
        'power_info' => '200V 60Hz industrial power with facility infrastructure for complete plant floor inspection.',
        'logistics' => 'Immediate proximity to Port of Osaka and Hanshin Expressway network with heavy crane transport contractors.',
    ],
    'Tokyo' => [
        'state' => 'Kanto Region, Japan',
        'estates' => 'Keihin Industrial Zone, Ota & Kawasaki Heavy Equipment Districts',
        'overview' => 'Tokyo metropolitan industrial corridor houses heavy mechanical power presses from Komatsu Ltd. Equipment is maintained under rigorous preventive maintenance guidelines.',
        'power_info' => '200V 50Hz clean industrial power with dedicated switchgear for 200-ton press stroke and clutch trials.',
        'logistics' => 'Direct links to Port of Tokyo, Port of Yokohama, and Shuto Expressway for breakbulk and flat-rack shipping.',
    ],
    'Yokohama' => [
        'state' => 'Kanagawa Prefecture, Japan',
        'estates' => 'Kanagawa Precision Machinery Cluster, Tsurumi & Kanazawa Industrial Estates',
        'overview' => 'Yokohama is a strategic maritime engineering hub hosting precision cold headers and parts formers from Naitoh Machinery Co., Ltd.',
        'power_info' => 'Industrial power grid supporting 37 kW 4-pole high-torque main drive motors.',
        'logistics' => 'Direct access to Port of Yokohama container terminals for rapid worldwide sea freight.',
    ],
    'Shanghai' => [
        'state' => 'East China Hub, China',
        'estates' => 'East China Equipment Belt, Pudong & Jiading Industrial Zones',
        'overview' => 'Shanghai equipment corridor hosts verified Zhejiang Dingli self-propelled electric scissor lifts with 13.8m working heights.',
        'power_info' => 'Integrated 24V DC battery charging banks and hydraulic diagnostic test benches.',
        'logistics' => 'Direct dispatch through Port of Shanghai (world\'s busiest container port) with sea-freight container packing.',
    ],
    'Shijiazhuang' => [
        'state' => 'Hebei Province, China',
        'estates' => 'Hebei Automated Machinery Science & Technology Industrial Park',
        'overview' => 'Shijiazhuang machinery center features automated cartridge sealant filling and packing systems engineered by Shijiazhuang Xiaojin Machinery Manufacturing Science & Technology Co., Ltd.',
        'power_info' => '380V 50Hz 3-phase automated lines with PLC touchscreens and pneumatic pressure manifolds.',
        'logistics' => 'Direct highway access to Tianjin Port for global ocean container transport.',
    ],
    'Moutier' => [
        'state' => 'Canton of Bern, Switzerland',
        'estates' => 'Jura Precision Turning Valley, Moutier Industrial District',
        'overview' => 'Moutier is the birthplace of Swiss high-precision screw machines, home to Tornos Bechler multi-spindle automatic lathes engineered for micrometer-tolerance turning.',
        'power_info' => '400V 50Hz European precision electrical grid with clean harmonic filtering.',
        'logistics' => 'Centrally located on Swiss Trans-Jura Highway with insured overland transport to Antwerp and Rotterdam seaports.',
    ],
    'Singapore' => [
        'state' => 'Singapore',
        'estates' => 'Jurong Industrial Estate, Tuas Heavy Equipment Zone',
        'overview' => 'Singapore\'s Jurong industrial belt features heavy 300-ton to 400-ton mechanical power presses from Aida and Amada with large bolster beds, tested and prepared for regional maritime shipping.',
        'power_info' => '200V / 220V 50/60Hz heavy industrial power substations for full tonnage and emergency stop testing.',
        'logistics' => 'Connected to PSA Singapore seaport terminals for rapid flat-rack and container shipping worldwide.',
    ],
    'Okayama' => [
        'state' => 'Okayama Prefecture, Japan',
        'estates' => 'Chugoku Machine Tool Corridor, Mizushima Heavy Industrial Zone',
        'overview' => 'Okayama represents one of Japan\'s premier regions for sub-micron precision toolmaking and CNC jig boring technology, hosting Yasda YBM-640V2 ultra-rigid vertical machining centers.',
        'power_info' => '200V / 220V 50/60Hz temperature-compensated clean industrial power for sub-micron spindle runout trials.',
        'logistics' => 'Direct access to Sanyo Expressway and Port of Mizushima for crated ocean and air cargo transport.',
    ],
    'Kuala Lumpur' => [
        'state' => 'Federal Territory & Selangor, Malaysia',
        'estates' => 'Klang Valley Ground Engineering Cluster, Shah Alam & Port Klang Industrial Corridor',
        'overview' => 'Kuala Lumpur and the Klang Valley serve as Malaysia\'s geotechnical machinery hub, hosting heavy deep vibrator crawler rigs and vibro hangers maintained by international engineering leader Keller.',
        'power_info' => 'Heavy diesel hydraulic test benches and 415V 50Hz 3-phase yard power for continuous winch and crawler drive diagnostics.',
        'logistics' => 'Immediate highway links to Port Klang (Westports and Northport) for heavy RORO and flat-rack ocean shipping across Southeast Asia and globally.',
    ],
    'Düsseldorf' => [
        'state' => 'North Rhine-Westphalia, Germany',
        'estates' => 'Rhine-Ruhr Heavy Machine Tool Belt, Düsseldorf & Neuss Industrial Area',
        'overview' => 'Düsseldorf is the historic engineering hub for German heavy machinery manufacturing, home to Wotan-Werke heavy internal cylindrical grinders designed for large-diameter deep bore machining.',
        'power_info' => '400V 50Hz European industrial grid with dedicated hydraulic and spindle test switchgear.',
        'logistics' => 'Direct access to Autobahn A46 / A52 and Rhine river barge terminals connecting to Rotterdam and Antwerp seaports.',
    ],
    'Kaohsiung' => [
        'state' => 'Southern Taiwan Heavy Industrial Zone, Taiwan',
        'estates' => 'Kaohsiung Global Fastener Machinery Corridor, Gangshan & Benzhou Industrial Parks',
        'overview' => 'Kaohsiung and Gangshan form the world\'s densest fastener manufacturing cluster, hosting high-performance bolt cold formers and parts headers by Chun Zu Machinery Industry Co., Ltd.',
        'power_info' => '220V / 380V 60Hz high-capacity industrial power for multi-station wire feeding and forging stroke load tests.',
        'logistics' => 'Direct access to Port of Kaohsiung (Taiwan\'s largest deepwater port) with turnkey container stuffing and sea freight.',
    ],
    'Taichung' => [
        'state' => 'Central Taiwan Precision Machinery Belt, Taiwan',
        'estates' => 'Taichung Precision Machinery Innovation Park, Taichung Harbor Zone',
        'overview' => 'Taichung is Taiwan\'s global capital for precision metal forming and multislide machinery, hosting TS Precision PROFORMA NF-40 multislide strip forming systems.',
        'power_info' => '220V 60Hz stable industrial power for 4-slide synchronized cam rotation and material feed cycle tests.',
        'logistics' => 'Direct highway links to Port of Taichung and Taiwan High Speed Rail freight hubs.',
    ],
    'Incheon' => [
        'state' => 'Incheon Metropolitan City, South Korea',
        'estates' => 'Incheon Industrial Fastener Belt, Namdong Industrial Complex',
        'overview' => 'Incheon is South Korea\'s leading industrial manufacturing cluster for high-speed multi-station cold formers and precision parts heading equipment engineered by Jinu Precision.',
        'power_info' => '220V / 380V 60Hz high-capacity clean industrial power for continuous multi-station heading trials.',
        'logistics' => 'Direct connection to Port of Incheon container terminals and Incheon International Airport for heavy machinery sea and air freight.',
    ],
    'Kanagawa' => [
        'state' => 'Kanagawa Prefecture, Japan',
        'estates' => 'Keihin Industrial Zone, Kawasaki & Sagamihara Machinery Corridor',
        'overview' => 'Kanagawa Prefecture is renowned for heavy engineering and precision metal forming technology, housing verified high-speed cold headers and industrial stamping equipment.',
        'power_info' => '200V 50Hz high-stability industrial power grid with dedicated test load connections.',
        'logistics' => 'Adjacent to Port of Yokohama and Tomei Expressway for expedited global export packing and shipping.',
    ],
    'Mulfingen' => [
        'state' => 'Baden-Württemberg, Germany',
        'estates' => 'Hohenlohe Precision Engineering Corridor, Southwestern Germany',
        'overview' => 'Mulfingen and the Baden-Württemberg region are the global standard for precision electromechanical systems and automated motor manufacturing equipment, home to heritage builders ebm-papst.',
        'power_info' => '400V 50Hz 3-phase European standard precision industrial grid.',
        'logistics' => 'Centrally connected to A81 Autobahn and Stuttgart freight transport hubs for seamless overland and ocean export.',
    ],
    'Ishikawa' => [
        'state' => 'Hokuriku Region, Japan',
        'estates' => 'Ishikawa Machine Tool Corridor, Kanazawa Industrial Zone',
        'overview' => 'Ishikawa Prefecture has a distinguished legacy of heavy forging and machine tool manufacturing, supporting verified mechanical power presses and forming machinery.',
        'power_info' => '200V 60Hz robust industrial power grid.',
        'logistics' => 'Direct access to Port of Kanazawa and Hokuriku Expressway for breakbulk and container cargo.',
    ],
    'Aichi' => [
        'state' => 'Chukyo Region, Japan',
        'estates' => 'Greater Nagoya Heavy Automotive and Forming Belt',
        'overview' => 'Aichi Prefecture is Japan\'s industrial manufacturing heartland, boasting the world\'s densest concentration of metal stamping presses, cold formers, and precision machine tools.',
        'power_info' => '200V / 220V 60Hz industrial power with dual-feed substations.',
        'logistics' => 'Connected directly to Port of Nagoya with specialized heavy crane loading berths.',
    ],
    'Como' => [
        'state' => 'Lombardy, Italy',
        'estates' => 'Como Silk & Textile Equipment Cluster, Northern Italy',
        'overview' => 'Como is the historic global capital for high-end silk, textile printing, and fabric finishing technology, hosting cutting-edge digital textile printing machinery by MS Printing Solutions.',
        'power_info' => '400V 50Hz 3-phase European standard grid with dedicated industrial transformer feeds.',
        'logistics' => 'Direct highway links to Milan Malpensa cargo airport and Genoa seaport container terminals.',
    ],
    'Kyoto' => [
        'state' => 'Kansai Region, Japan',
        'estates' => 'Kyoto Advanced Machinery Corridor & Minami Industrial Zone',
        'overview' => 'Kyoto is an elite engineering and precision machine tool center in Japan, home to cold forging pioneer Sakamura Machine Co., Ltd. producing high-speed multi-station bolt formers.',
        'power_info' => '200V / 220V 60Hz high-stability industrial power grid with heavy forging test circuits.',
        'logistics' => 'Direct connection to Meishin Expressway and Port of Osaka for ocean container transport.',
    ],
    'Shenzhen' => [
        'state' => 'Guangdong Province, China',
        'estates' => 'Baoan & Guangming Advanced Precision CNC Equipment Corridor',
        'overview' => 'Shenzhen is China\'s premier high-tech and CNC machine tool manufacturing metropolis, hosting high-speed drilling, tapping, and milling machining centers by Shenzhen Taide CNC Machinery.',
        'power_info' => '380V 50Hz 3-phase clean industrial power with spindle test benches.',
        'logistics' => 'Direct access to Yantian and Shekou deepwater container ports for worldwide sea freight.',
    ],
    'Ningbo' => [
        'state' => 'Zhejiang Province, China',
        'estates' => 'Ningbo Global Plastics Machinery Park, Beilun Industrial Zone',
        'overview' => 'Ningbo is universally recognized as the world\'s plastic injection molding machinery capital, housing the headquarters and premier manufacturing plants of Haitian Plastics Machinery Group.',
        'power_info' => '380V 50Hz high-capacity electrical substations for continuous toggle clamp and injection load testing.',
        'logistics' => 'Immediate proximity to Port of Ningbo-Zhoushan (world\'s top cargo volume seaport) for container shipping.',
    ]
];

$profile = $cityProfiles[$cityName] ?? $cityProfiles['Nagoya'];

// SEO Setup
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'joulemachinehub.com';
$canonicalUrl = $protocol . $host . '/' . getCityUrl($cityName);
$pageTitle = !empty($cityData['meta_title']) ? $cityData['meta_title'] : ("Used & New Industrial Machinery in " . $cityName . " | Joule Machine Hub");
$pageDesc = !empty($cityData['meta_desc']) ? $cityData['meta_desc'] : ("Buy verified used and new machinery in " . $cityName . ", " . $countryName . ".");
$pageKeywords = "used machinery " . strtolower($cityName) . ", new industrial machinery " . strtolower($cityName) . ", used industrial equipment " . strtolower($cityName) . ", " . strtolower($specialty) . " " . strtolower($cityName);
$geoPlacename = $cityName . ", " . $countryName;
$geoRegion = $cityData['country_code'];
$ogTitle = $pageTitle;
$ogDesc = $pageDesc;
$ogImage = $protocol . $host . '/' . ltrim($cityData['image'], '/');
$ogType = "website";
$pageRobots = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

// City FAQs
$cityFaqs = [
    [
        'q' => "Where can I physically inspect used or new machines in {$cityName}?",
        'a' => "All machines listed in {$cityName} are installed in operational manufacturing facilities across {$profile['estates']}. You can schedule an on-site visit by submitting an online inquiry form directly to the verified plant desk."
    ],
    [
        'q' => "Can I perform power testing and test cycles under load in {$cityName}?",
        'a' => "Yes! Every verified machine in {$cityName} is connected to industrial power ({$profile['power_info']}). You can inspect stroke speeds, clutch engagement, hydraulic pressures, and cycle runouts before purchase."
    ],
    [
        'q' => "How does crane lifting, export crating, and freight transport work from {$cityName}?",
        'a' => "The seller's plant provides overhead or mobile crane loading. Furthermore, {$profile['logistics']} enables fast worldwide container and breakbulk dispatch."
    ],
    [
        'q' => "How are machine inquiries and inspections handled in {$cityName}?",
        'a' => "Inquiries for machines in {$cityName} are coordinated through our B2B mediator desk. Technical specs and documentation are verified upfront, and physical inspections are scheduled following commercial agreement."
    ]
];

// JSON-LD Schema
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
                    'name' => 'Cities',
                    'item' => $protocol . $host . '/#cities'
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $cityName . ' Hub',
                    'item' => $canonicalUrl
                ]
            ]
        ],
        [
            '@type' => 'ItemList',
            'name' => 'Available Machinery in ' . $cityName,
            'numberOfItems' => count($cityMachines),
            'itemListElement' => array_values(array_map(function($m, $pos) use ($protocol, $host) {
                return [
                    '@type' => 'ListItem',
                    'position' => $pos + 1,
                    'name' => ($m['year'] ? $m['year'] . ' ' : '') . ($m['title'] ?? ''),
                    'url' => $protocol . $host . '/' . getMachineUrl($m),
                    'image' => !empty($m['images'][0]) ? ($protocol . $host . '/' . ltrim($m['images'][0], '/')) : null
                ];
            }, array_slice($cityMachines, 0, 10), array_keys(array_slice($cityMachines, 0, 10))))
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(function($f) {
                return [
                    '@type' => 'Question',
                    'name' => $f['q'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $f['a']
                    ]
                ];
            }, $cityFaqs)
        ]
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- BREADCRUMBS -->
<div class="bg-white border-b border-slate-200 py-3 px-4">
  <div class="max-w-7xl mx-auto flex items-center flex-wrap gap-2 text-xs text-slate-500">
    <a href="index.php" class="hover:text-joule-brightblue font-bold flex items-center gap-1">
      <i class="fa-solid fa-house text-slate-400 text-[10px]"></i>
      <span>Home</span>
    </a>
    <span class="text-slate-300">/</span>
    <a href="index.php#cities" class="hover:text-joule-brightblue font-medium">Manufacturing Hubs</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-black flex items-center gap-1.5">
      <?php echo getCountryFlagHtml($countryName, 'w-5 h-3.5'); ?>
      <span><?php echo htmlspecialchars($cityName); ?> Hub (<?php echo htmlspecialchars($countryName); ?>)</span>
    </span>
  </div>
</div>

<!-- CITY HERO BANNER -->
<section class="bg-gradient-to-br from-joule-navy via-joule-blue to-blue-950 text-white relative overflow-hidden border-b-4 border-joule-yellow py-10 sm:py-14">
  <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#facc15_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-4 relative z-10 space-y-6">
    <div class="max-w-3xl space-y-3">
      <div class="inline-flex items-center gap-2 bg-joule-yellow/20 border border-joule-yellow/50 text-joule-yellow px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider">
        <i class="fa-solid fa-city"></i>
        <span>Verified Industrial Hub • <?php echo getCountryFlagHtml($countryName, 'w-4 h-3'); ?> <?php echo htmlspecialchars($countryName); ?></span>
      </div>

      <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-white leading-tight">
        Second Hand / Used / Old / Imported &amp; New Industrial Machinery in <span class="text-joule-yellow"><?php echo htmlspecialchars($cityName); ?></span>
      </h1>

      <p class="text-blue-100 text-xs sm:text-sm sm:leading-relaxed font-medium">
        <?php echo htmlspecialchars($profile['overview']); ?>
      </p>

      <div class="flex flex-wrap gap-2 pt-2">
        <span class="bg-white/10 backdrop-blur-md border border-white/20 text-white px-3 py-1 rounded-lg text-xs font-bold">
          📍 <?php echo htmlspecialchars($subzone); ?>
        </span>
        <span class="bg-white/10 backdrop-blur-md border border-white/20 text-joule-yellow px-3 py-1 rounded-lg text-xs font-bold">
          ⚙️ <?php echo htmlspecialchars($specialty); ?>
        </span>
      </div>
    </div>

    <!-- Quick Trust Badges Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 pt-4 border-t border-blue-800/80 text-xs">
      <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10 flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-joule-yellow text-slate-950 flex items-center justify-center font-bold text-sm shrink-0">
          <i class="fa-solid fa-phone-volume"></i>
        </div>
        <div>
          <div class="text-white font-black text-xs">B2B Mediator Desk</div>
          <div class="text-[10px] text-blue-200">Verified inventory</div>
        </div>
      </div>

      <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10 flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-sm shrink-0">
          <i class="fa-solid fa-bolt"></i>
        </div>
        <div>
          <div class="text-white font-black text-xs">Inspection Desk</div>
          <div class="text-[10px] text-blue-200">Scheduled on agreement</div>
        </div>
      </div>

      <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10 flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-blue-500 text-white flex items-center justify-center font-bold text-sm shrink-0">
          <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div>
          <div class="text-white font-black text-xs"><?php echo count($cityMachines); ?> Available</div>
          <div class="text-[10px] text-blue-200">In <?php echo htmlspecialchars($cityName); ?></div>
        </div>
      </div>

      <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10 flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center font-bold text-sm shrink-0">
          <i class="fa-solid fa-ship"></i>
        </div>
        <div>
          <div class="text-white font-black text-xs">Port Access</div>
          <div class="text-[10px] text-blue-200">Seaworthy loading</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- MAIN CONTENT -->
<main class="max-w-7xl mx-auto px-4 py-8 sm:py-10 space-y-8 flex-1 w-full">

  <!-- ACTIVE CITIES QUICK-SWITCH BAR (ONLY Real Cities With Available Products) -->
  <div class="space-y-2">
    <div class="flex items-center justify-between text-xs font-black text-slate-500 uppercase tracking-wider">
      <span>Verified Active Manufacturing Hubs (Only Cities With Stock):</span>
      <span class="text-joule-brightblue font-bold"><?php echo count($cityHubs); ?> Cities Available</span>
    </div>

    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2">
      <?php foreach ($cityHubs as $cKey => $cMeta): 
        $isActive = ($cKey === $cityName);
        $hubCount = count(array_filter($machines, fn($m) => strtolower(trim($m['city'])) === strtolower(trim($cKey))));
      ?>
        <a 
          href="<?php echo htmlspecialchars(getCityUrl($cKey)); ?>"
          class="shrink-0 px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 <?php echo $isActive ? 'bg-[#071626] text-white shadow-sm ring-2 ring-joule-yellow' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'; ?>"
        >
          <?php echo getCountryFlagHtml($cMeta['country_code'] ?? 'JP', 'w-4 h-3'); ?>
          <span><?php echo htmlspecialchars($cKey); ?></span>
          <span class="text-[10px] <?php echo $isActive ? 'text-amber-300 font-black' : 'text-slate-400 font-mono'; ?>">(<?php echo $hubCount; ?>)</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Available Machines Section Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
    <div>
      <h2 class="text-xl sm:text-2xl font-black text-slate-950 flex items-center gap-2">
        <span>Available Machinery in <?php echo htmlspecialchars($cityName); ?></span>
        <span class="text-xs bg-joule-yellow text-slate-950 font-black px-2.5 py-0.5 rounded-full border border-amber-300">
          <?php echo count($cityMachines); ?> Available Now
        </span>
      </h2>
      <p class="text-xs text-slate-500 mt-0.5">
        Verified machinery inventory in <?php echo htmlspecialchars($subzone); ?> available through our B2B sourcing desk.
      </p>
    </div>

    <div class="flex items-center gap-2 text-xs">
      <span class="text-slate-500 font-bold">Sort By:</span>
      <select 
        onchange="location = this.value;" 
        class="bg-white border-2 border-slate-300 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-700 focus:outline-none focus:border-joule-blue cursor-pointer"
      >
        <option value="<?php echo htmlspecialchars(getCityUrl($cityName)); ?>?sort=featured" <?php echo $sortBy === 'featured' ? 'selected' : ''; ?>>Featured / Recommended</option>
        <option value="<?php echo htmlspecialchars(getCityUrl($cityName)); ?>?sort=priceLow" <?php echo $sortBy === 'priceLow' ? 'selected' : ''; ?>>Price: Low to High</option>
        <option value="<?php echo htmlspecialchars(getCityUrl($cityName)); ?>?sort=priceHigh" <?php echo $sortBy === 'priceHigh' ? 'selected' : ''; ?>>Price: High to Low</option>
        <option value="<?php echo htmlspecialchars(getCityUrl($cityName)); ?>?sort=yearNew" <?php echo $sortBy === 'yearNew' ? 'selected' : ''; ?>>Mfg Year: Newest First</option>
      </select>
    </div>
  </div>

  <!-- Machines Grid for this city -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($cityMachines as $machine): 
      $topSpecs = array_slice($machine['specs'], 0, 3);
    ?>
      <div class="bg-white rounded-2xl border-2 border-slate-200 overflow-hidden hover:border-joule-blue hover:shadow-xl transition-all duration-300 flex flex-col group relative">
        
        <!-- Image Section -->
        <div class="relative aspect-[16/10] bg-slate-900 overflow-hidden">
          <a href="<?php echo htmlspecialchars(getMachineUrl($machine)); ?>" class="block w-full h-full">
            <img 
              src="<?php echo htmlspecialchars('/' . ltrim($machine['images'][0], '/')); ?>" 
              alt="<?php echo htmlspecialchars($machine['year'] . ' ' . $machine['brand'] . ' ' . $machine['model'] . ' ' . $machine['category'] . ' in ' . $cityName . ', ' . $countryName); ?>" 
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              loading="lazy"
              decoding="async"
            >
          </a>
          
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/30 pointer-events-none"></div>

          <!-- Stock / Item Badge -->
          

          <!-- Photos Badge -->
          <div class="absolute top-2.5 right-2.5 z-10">
            <span class="bg-slate-950/85 backdrop-blur-md text-white text-[10px] font-bold px-2 py-1 rounded-md border border-white/20 flex items-center gap-1 shadow-sm">
              <i class="fa-solid fa-camera text-joule-yellow text-[10px]"></i> <?php echo count($machine['images']); ?> Photos
            </span>
          </div>

          <!-- Bottom stats -->
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
              <a href="<?php echo htmlspecialchars(getCategoryUrl($machine['category'])); ?>" class="text-[10px] font-black uppercase tracking-wider text-joule-brightblue hover:underline">
                <?php echo htmlspecialchars($machine['brand']); ?> • <?php echo htmlspecialchars($machine['category']); ?>
              </a>
              <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">
                <?php echo getCountryFlagHtml($machine['country'] ?? 'Japan', 'w-4 h-3'); ?>
                <span><?php echo htmlspecialchars($machine['city']); ?></span>
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
                Negotiable
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
                <span class="text-slate-400 block text-[9px] uppercase font-bold">Power Test</span>
                <span class="font-bold text-emerald-600 truncate block">In <?php echo htmlspecialchars($cityName); ?></span>
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

          <!-- CTAs (2 buttons in a row on mobile) -->
          <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 jmh-mobile-grid-2">
            <button 
              type="button" 
              onclick="openInquiryModal('<?php echo $machine['id']; ?>')"
              class="col-span-1 col-1 w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black py-2.5 px-2 rounded-xl flex items-center justify-center gap-1.5 shadow-sm transition-all text-xs cursor-pointer text-center min-h-[42px]"
            >
              <i class="fa-solid fa-file-signature text-xs shrink-0"></i>
              <span class="truncate">Enquire Now</span>
            </button>

            <a 
              href="<?php echo htmlspecialchars(getMachineUrl($machine)); ?>" 
              class="col-span-1 col-1 w-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-2.5 px-2 rounded-xl text-xs flex items-center justify-center gap-1 transition-colors text-center min-h-[42px]"
            >
              <span class="truncate">Full Specs</span>
              <i class="fa-solid fa-arrow-right text-[10px] shrink-0"></i>
            </a>
          </div>

        </div>

      </div>
    <?php endforeach; ?>
  </div>

  <!-- City Industrial Profile & Rigging Guide -->
  <div class="bg-white rounded-2xl border-2 border-slate-200 p-6 sm:p-8 space-y-6 shadow-xs">
    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
      <div class="w-10 h-10 rounded-xl bg-joule-blue text-joule-yellow flex items-center justify-center text-lg font-bold">
        <i class="fa-solid fa-industry"></i>
      </div>
      <div>
        <h3 class="text-base sm:text-lg font-black text-slate-950">
          <?php echo htmlspecialchars($cityName); ?> Industrial Logistics & Plant Power Testing
        </h3>
        <p class="text-xs text-slate-500">
          Technical parameter verification and dispatch logistics from <?php echo htmlspecialchars($cityName); ?>, <?php echo htmlspecialchars($countryName); ?>.
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs leading-relaxed text-slate-600">
      <div class="space-y-2">
        <div class="font-black text-slate-900 text-sm flex items-center gap-2">
          <i class="fa-solid fa-plug-circle-bolt text-emerald-600"></i>
          <span>Live Electrical Power Connection</span>
        </div>
        <p><?php echo htmlspecialchars($profile['power_info']); ?></p>
      </div>

      <div class="space-y-2">
        <div class="font-black text-slate-900 text-sm flex items-center gap-2">
          <i class="fa-solid fa-truck-ramp-box text-blue-600"></i>
          <span>Heavy Crane Rigging & Seaport Dispatch</span>
        </div>
        <p><?php echo htmlspecialchars($profile['logistics']); ?></p>
      </div>
    </div>
  </div>

  <!-- Frequently Asked Questions -->
  <div class="bg-white rounded-2xl border-2 border-slate-200 p-6 sm:p-8 space-y-4 shadow-xs">
    <h3 class="text-base sm:text-lg font-black text-slate-950 flex items-center gap-2">
      <i class="fa-solid fa-circle-question text-joule-brightblue"></i>
      <span>Frequently Asked Questions for <?php echo htmlspecialchars($cityName); ?> Machinery</span>
    </h3>

    <div class="space-y-3 pt-2 text-xs">
      <?php foreach ($cityFaqs as $faq): ?>
        <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 space-y-1.5">
          <div class="font-bold text-slate-900"><?php echo htmlspecialchars($faq['q']); ?></div>
          <p class="text-slate-600 leading-relaxed"><?php echo htmlspecialchars($faq['a']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
