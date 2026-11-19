<?php
// About Us & Business Model - Joule Machine Hub
// Blueprint & Layout strictly aligned with Joule Machine Hub Business Model Infographic
// Section 1: Online Industrial Machinery Marketplace / Our Business Model – How It Works (Pure Animated HTML/CSS/SVG)
// Section 2: About Joule Machine Hub (Mission, Platform Story & Stats)
// Section 3: Who We Serve (Sellers & Buyers Cards)
// Section 4 & 5: What We Do & What We Don't Do
// Section 6: Key Platform Benefits
// Section 7: Closing Banner & Dual CTAs

$pageTitle = 'About Joule Machine Hub | Industrial Machinery Marketplace';
$pageDesc = 'Learn about Joule Machine Hub, an online industrial machinery marketplace connecting machinery sellers with potential buyers through paid listings and enquiries.';
$canonicalUrl = 'https://joulemachinehub.com/about';
$ogTitle = $pageTitle;
$ogDesc = $pageDesc;

// Structured JSON-LD for AboutPage & Organization
$jsonLd = [
    "@context" => "https://schema.org",
    "@graph" => [
        [
            "@type" => "AboutPage",
            "@id" => "https://joulemachinehub.com/about#webpage",
            "url" => "https://joulemachinehub.com/about",
            "name" => $pageTitle,
            "description" => $pageDesc,
            "inLanguage" => "en-US",
            "isPartOf" => [
                "@type" => "WebSite",
                "@id" => "https://joulemachinehub.com/#website",
                "name" => "Joule Machine Hub",
                "url" => "https://joulemachinehub.com/"
            ]
        ],
        [
            "@type" => "Organization",
            "@id" => "https://joulemachinehub.com/#organization",
            "name" => "Joule Machine Hub",
            "url" => "https://joulemachinehub.com/",
            "logo" => "https://joulemachinehub.com/assets/images/logo-v2.png",
            "description" => "Online B2B industrial machinery marketplace connecting machinery sellers with potential buyers worldwide through paid listings and enquiries.",
            "areaServed" => "Worldwide"
        ]
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<main class="min-h-screen bg-slate-50 text-slate-800 font-sans">

  <!-- ========================================================
       SECTION 1: ABOUT JOULE MACHINE HUB (HERO, MISSION & STORY)
       "Platform Mission & Story
        About Joule Machine Hub
        Connecting Industrial Machinery Buyers and Sellers"
       ======================================================== -->
  <section id="about-hub" class="relative bg-gradient-to-b from-[#030e1d] via-[#081d38] to-[#041224] text-white pt-8 pb-14 sm:pt-10 sm:pb-20 px-4 overflow-hidden border-b border-slate-800">
    <!-- Ambient Warehouse Blueprint Grid Overlay -->
    <div class="absolute inset-0 opacity-15 pointer-events-none" style="background-image: radial-gradient(rgba(255,255,255,0.15) 1px, transparent 1px), radial-gradient(rgba(250,204,21,0.1) 1px, transparent 1px); background-size: 32px 32px, 96px 96px; background-position: 0 0, 16px 16px;"></div>
    
    <!-- Ambient Blue & Amber Spotlights -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[850px] h-[850px] bg-blue-600/15 rounded-full blur-[160px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto relative z-10">

      <!-- Top Breadcrumbs -->
      <nav class="flex items-center gap-2 text-xs text-slate-400 mb-8 font-semibold" aria-label="Breadcrumb">
        <a href="index.php" class="hover:text-amber-400 transition-colors flex items-center gap-1.5">
          <i class="fa-solid fa-house"></i>
          <span>Home</span>
        </a>
        <i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i>
        <span class="text-amber-400">About Us</span>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
        
        <!-- Left Column: Mission & Core Value Proposition -->
        <div class="lg:col-span-7 space-y-4 text-left">
          
          <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-400/15 border border-amber-400/30 text-amber-300 text-xs font-black tracking-wide uppercase">
            <i class="fa-solid fa-shield-halved text-amber-400"></i>
            <span>Platform Mission &amp; Story</span>
          </div>

          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight tracking-tight">
            About <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-yellow-300">Joule Machine Hub</span>
          </h1>

          <p class="text-amber-300 text-base sm:text-lg font-bold tracking-tight">
            Connecting Industrial Machinery Buyers and Sellers
          </p>

          <p class="text-slate-200 text-sm sm:text-base leading-relaxed font-normal">
            <strong>Joule Machine Hub</strong> is an online B2B industrial machinery marketplace designed to connect machinery sellers with potential buyers across different industries and locations.
          </p>

          <p class="text-slate-300 text-sm leading-relaxed">
            We provide a digital platform where machinery owners, dealers, traders, manufacturers and businesses can register, choose suitable paid listing plans and showcase their new and used industrial machinery.
          </p>

          <p class="text-slate-300 text-sm leading-relaxed">
            Through our platform, buyers can explore machinery listings, review technical specifications, compare available options and submit enquiries directly to sellers.
          </p>

          <p class="text-slate-400 text-xs sm:text-sm leading-relaxed font-medium">
            Our goal is to simplify industrial machinery discovery by bringing machinery listings and buyer enquiries together on one accessible platform.
          </p>

          <!-- Action Links (2 buttons in a row on mobile, flex on desktop) -->
          <div class="pt-3 grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 sm:gap-3 max-w-xl sm:max-w-none jmh-mobile-grid-2">
            <a href="#how-it-works" class="col-span-2 span-full w-full sm:w-auto px-4 sm:px-5 py-3 rounded-xl bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 text-slate-950 font-black text-xs uppercase tracking-wider flex items-center justify-center text-center gap-2 shadow-lg shadow-amber-400/20 transition-all hover:scale-[1.02] min-h-[46px]">
              <i class="fa-solid fa-diagram-project text-xs sm:text-sm shrink-0"></i>
              <span>See How It Works (5 Steps)</span>
            </a>
            <a href="register.php?role=seller" class="col-span-1 col-1 w-full sm:w-auto px-2 sm:px-5 py-3 rounded-xl bg-white/10 hover:bg-white/15 text-white font-bold text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider border border-white/20 transition-all flex items-center justify-center text-center gap-1.5 sm:gap-2 min-h-[46px] hover:scale-105">
              <i class="fa-solid fa-store text-amber-400 text-xs shrink-0"></i>
              <span class="leading-tight">Become a Verified Seller</span>
            </a>
            <a href="index.php#catalog" class="col-span-1 col-1 w-full sm:w-auto px-2 sm:px-5 py-3 rounded-xl bg-white/10 hover:bg-white/15 text-white font-bold text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider border border-white/20 transition-all flex items-center justify-center text-center gap-1.5 sm:gap-2 min-h-[46px] hover:scale-105">
              <i class="fa-solid fa-boxes-stacked text-amber-400 text-xs shrink-0"></i>
              <span class="leading-tight">Explore Machinery</span>
            </a>
          </div>

        </div>

        <!-- Right Column: Stats Grid -->
        <div class="lg:col-span-5 space-y-4">
          <div class="bg-gradient-to-b from-[#061833] to-[#020b18] border-2 border-amber-400/40 p-5 rounded-3xl shadow-2xl overflow-hidden group">
            <div class="flex items-center justify-between pb-3 border-b border-white/10">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                <span class="text-xs font-bold text-slate-300 uppercase tracking-wide">Live B2B Network</span>
              </div>
              <span class="text-xs font-black text-amber-400 tracking-wider uppercase">JMH Verified Model</span>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-3 pt-4">
              <div class="bg-white/5 border border-white/10 rounded-2xl p-4 text-left">
                <div class="text-2xl font-black text-white">5,000+</div>
                <div class="text-xs font-bold text-amber-400 uppercase tracking-wide mt-1">Listed Units</div>
                <p class="text-[11px] text-slate-400 mt-1">VMC, CNC, Lathes, Press Brakes, Moulding.</p>
              </div>
              <div class="bg-white/5 border border-white/10 rounded-2xl p-4 text-left">
                <div class="text-2xl font-black text-white">50+</div>
                <div class="text-xs font-bold text-blue-400 uppercase tracking-wide mt-1">Countries</div>
                <p class="text-[11px] text-slate-400 mt-1">India, Europe, Americas, Middle East &amp; Asia.</p>
              </div>
              <div class="bg-white/5 border border-white/10 rounded-2xl p-4 text-left">
                <div class="text-2xl font-black text-white">100%</div>
                <div class="text-xs font-bold text-emerald-400 uppercase tracking-wide mt-1">Direct Leads</div>
                <p class="text-[11px] text-slate-400 mt-1">Direct buyer-seller negotiation.</p>
              </div>
              <div class="bg-white/5 border border-white/10 rounded-2xl p-4 text-left">
                <div class="text-2xl font-black text-white">Zero %</div>
                <div class="text-xs font-bold text-purple-400 uppercase tracking-wide mt-1">Commission</div>
                <p class="text-[11px] text-slate-400 mt-1">Flat paid subscription plans for sellers.</p>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </section>


  <!-- ========================================================
       SECTION 2: OUR BUSINESS MODEL – HOW IT WORKS
       "Online Industrial Machinery Marketplace
        Our Business Model – How It Works
        A seamless 5-step digital ecosystem connecting heavy machinery 
        sellers with verified buyers across global manufacturing hubs."
       Fully Animated HTML / CSS / SVG Circular Workflow
       ======================================================== -->
  <section id="how-it-works" class="relative bg-gradient-to-b from-[#041224] via-[#081d38] to-[#041224] text-white py-14 sm:py-20 px-3 sm:px-6 overflow-hidden border-b border-slate-800">
    
    <!-- Ambient Warehouse Blueprint Grid Overlay -->
    <div class="absolute inset-0 opacity-15 pointer-events-none" style="background-image: radial-gradient(rgba(255,255,255,0.15) 1px, transparent 1px), radial-gradient(rgba(250,204,21,0.1) 1px, transparent 1px); background-size: 32px 32px, 96px 96px; background-position: 0 0, 16px 16px;"></div>
    
    <!-- Ambient Blue & Amber Spotlights -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[850px] h-[850px] bg-blue-600/15 rounded-full blur-[160px] pointer-events-none"></div>

    <div class="max-w-6xl mx-auto relative z-10">

      <!-- Section Title & Badges: Exact user requested copy -->
      <div class="text-center max-w-4xl mx-auto space-y-3 mb-8 sm:mb-12">
        
        <h2 class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-white drop-shadow-[0_4px_16px_rgba(0,0,0,0.9)]">
          Online Industrial Machinery Marketplace
        </h2>

        <!-- Golden Yellow Badge -->
        <div class="inline-block pt-1 pb-1">
          <div class="bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-400 text-slate-950 font-black text-xs sm:text-sm md:text-base uppercase tracking-wider px-6 sm:px-10 py-2 sm:py-2.5 rounded-full shadow-2xl border-2 border-slate-950">
            Our Business Model – How It Works
          </div>
        </div>

        <p class="text-slate-300 text-xs sm:text-sm md:text-base max-w-3xl mx-auto pt-1 leading-relaxed font-normal">
          A seamless 5-step digital ecosystem connecting heavy machinery sellers with verified buyers across global manufacturing hubs.
        </p>

      </div>

      <!-- =====================================================
           CIRCULAR 5-STEP BUSINESS MODEL WORKFLOW (UNIVERSAL RESPONSIVE)
           Identical visual design across Desktop, Tablet & Mobile.
           Auto-scaling 1024x682 vector canvas maintains 100% exact layout.
           ===================================================== -->
      <style>
        .circular-scaler-wrap {
          width: 100%;
          max-width: 1024px;
          margin: 0 auto;
          position: relative;
          overflow: hidden;
          aspect-ratio: 1024 / 682;
          background: #031024;
          touch-action: pan-y;
        }
        .circular-scaler-inner {
          width: 1024px;
          height: 682px;
          position: absolute;
          top: 0;
          left: 0;
          transform-origin: 0 0;
          will-change: transform;
        }
      </style>

      <div id="workflowCanvasWrap" class="circular-scaler-wrap w-full max-w-5xl mx-auto rounded-2xl sm:rounded-3xl overflow-hidden border-2 border-amber-400/50 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.85)] bg-[#031024] relative">
        
        <div id="workflowCanvasInner" class="circular-scaler-inner relative" 
             style="width: 1024px; height: 682px; transform-origin: top left; background: url('/assets/images/about-warehouse-bg.jpg') center top / cover no-repeat;">
        
        <!-- SVG LAYER: GOLDEN GLOWING RING & ANIMATED 3D ARROWS -->
        <svg class="absolute inset-0 w-full h-full pointer-events-none z-10" viewBox="0 0 1024 682" fill="none">
          <defs>
            <!-- Golden Gradient for 3D Connecting Arrows -->
            <linearGradient id="arrGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#FDE047" />
              <stop offset="50%" stop-color="#F59E0B" />
              <stop offset="100%" stop-color="#D97706" />
            </linearGradient>

            <!-- Drop Shadow for 3D Depth on Arrows -->
            <filter id="svgArrShadow" x="-20%" y="-20%" width="140%" height="140%">
              <feDropShadow dx="0" dy="4" stdDeviation="4" flood-color="#000000" flood-opacity="0.65"/>
            </filter>

            <!-- Golden Halo Glow Filter for Center Ring -->
            <filter id="svgHaloGlow" x="-30%" y="-30%" width="160%" height="160%">
              <feGaussianBlur stdDeviation="8" result="blur" />
              <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>
          </defs>

          <!-- Central Radiant Golden Ring (Center 512, 328) -->
          <circle cx="512" cy="328" r="148" stroke="#FBBF24" stroke-width="3" stroke-opacity="0.85" filter="url(#svgHaloGlow)" />
          <circle cx="512" cy="328" r="156" stroke="#FDE047" stroke-width="1.5" stroke-opacity="0.45" stroke-dasharray="6 4" class="flow-arrow-animated" />

          <!-- 1. ARROW 1 -> 2: From Card 1 Right to Card 2 Left -->
          <g filter="url(#svgArrShadow)">
            <path d="M 445 160 Q 512 130 562 155" stroke="url(#arrGrad1)" stroke-width="14" stroke-linecap="round" fill="none" />
            <polygon points="580,165 554,145 560,175" fill="#F59E0B" stroke="#FDE047" stroke-width="1.5" />
            <path d="M 445 160 Q 512 130 562 155" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" fill="none" class="flow-arrow-animated" stroke-opacity="0.95" />
          </g>

          <!-- 2. ARROW 2 -> 3: From Card 2 Bottom-Right to Card 3 Top -->
          <g filter="url(#svgArrShadow)">
            <path d="M 732 225 Q 798 260 782 312" stroke="url(#arrGrad1)" stroke-width="14" stroke-linecap="round" fill="none" />
            <polygon points="778,328 772,298 798,312" fill="#F59E0B" stroke="#FDE047" stroke-width="1.5" />
            <path d="M 732 225 Q 798 260 782 312" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" fill="none" class="flow-arrow-animated" stroke-opacity="0.95" />
          </g>

          <!-- 3. ARROW 3 -> 4: From Card 3 Bottom to Card 4 Right -->
          <g filter="url(#svgArrShadow)">
            <path d="M 728 440 Q 722 505 665 528" stroke="url(#arrGrad1)" stroke-width="14" stroke-linecap="round" fill="none" />
            <polygon points="650,533 675,515 675,545" fill="#F59E0B" stroke="#FDE047" stroke-width="1.5" />
            <path d="M 728 440 Q 722 505 665 528" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" fill="none" class="flow-arrow-animated" stroke-opacity="0.95" />
          </g>

          <!-- 4. ARROW 4 -> 5: From Card 4 Left to Card 5 Bottom -->
          <g filter="url(#svgArrShadow)">
            <path d="M 360 528 Q 302 505 296 440" stroke="url(#arrGrad1)" stroke-width="14" stroke-linecap="round" fill="none" />
            <polygon points="293,425 284,453 310,443" fill="#F59E0B" stroke="#FDE047" stroke-width="1.5" />
            <path d="M 360 528 Q 302 505 296 440" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" fill="none" class="flow-arrow-animated" stroke-opacity="0.95" />
          </g>

          <!-- 5. ARROW 5 -> 1: From Card 5 Top to Card 1 Bottom-Left -->
          <g filter="url(#svgArrShadow)">
            <path d="M 242 312 Q 226 260 290 225" stroke="url(#arrGrad1)" stroke-width="14" stroke-linecap="round" fill="none" />
            <polygon points="305,218 280,215 288,242" fill="#F59E0B" stroke="#FDE047" stroke-width="1.5" />
            <path d="M 242 312 Q 226 260 290 225" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" fill="none" class="flow-arrow-animated" stroke-opacity="0.95" />
          </g>
        </svg>

        <!-- CENTER WEBSITE LOGO (JOULE MACHINE HUB OFFICIAL LOGO) -->
        <div class="absolute z-20 flex items-center justify-center -translate-x-1/2 -translate-y-1/2" 
             style="left: 50%; top: 48%;">
          <a href="index.php" title="Joule Machine Hub - Online Industrial Machinery Marketplace" class="block group">
            <img 
              src="/assets/images/logo-v2.png" 
              alt="Joule Machine Hub - Online Industrial Machinery Marketplace" 
              class="w-[250px] h-auto object-contain drop-shadow-[0_0_30px_rgba(250,204,21,0.7)] group-hover:scale-105 group-hover:drop-shadow-[0_0_42px_rgba(250,204,21,0.95)] transition-all duration-300"
              width="250"
              height="77"
            >
          </a>
        </div>

        <!-- 5 HTML CARDS (EXACT PERCENTAGE POSITIONS ON 1024x682 CANVAS) -->

        <!-- CARD 1: SELLER REGISTRATION -->
        <a href="register.php?role=seller" 
           title="Step 1: Seller Registration - Click to Register"
           class="absolute z-20 bg-gradient-to-b from-white to-slate-50 text-slate-900 rounded-3xl p-3.5 shadow-[0_16px_32px_-4px_rgba(0,0,0,0.5)] border border-slate-200/80 hover:border-amber-400 hover:ring-2 hover:ring-amber-400/60 hover:-translate-y-1 transition-all duration-300 block group"
           style="left: 21.5%; top: 15.4%; width: 21.5%; min-height: 115px;">
          <div class="flex items-center gap-2 mb-1.5">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-yellow-500 text-slate-950 font-black text-xs flex items-center justify-center shadow-md shrink-0">
              1
            </div>
            <div class="text-xs font-black text-[#071d38] uppercase tracking-tight leading-tight group-hover:text-amber-600 transition-colors">
              Seller Registration
            </div>
          </div>
          <div class="flex items-start gap-2.5 pt-1">
            <div class="w-9 h-9 rounded-xl bg-slate-100 text-[#071d38] flex items-center justify-center text-lg shrink-0">
              <i class="fa-solid fa-user-plus"></i>
            </div>
            <p class="text-[11px] text-slate-600 leading-snug font-medium">
              Create a seller account with business details.
            </p>
          </div>
        </a>

        <!-- CARD 2: CHOOSE A PLAN -->
        <a href="register.php?role=seller" 
           title="Step 2: Choose a Plan - View Paid Subscription Options"
           class="absolute z-20 bg-gradient-to-b from-white to-slate-50 text-slate-900 rounded-3xl p-3.5 shadow-[0_16px_32px_-4px_rgba(0,0,0,0.5)] border border-slate-200/80 hover:border-amber-400 hover:ring-2 hover:ring-amber-400/60 hover:-translate-y-1 transition-all duration-300 block group"
           style="left: 57.0%; top: 15.4%; width: 21.5%; min-height: 115px;">
          <div class="flex items-center gap-2 mb-1.5">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-yellow-500 text-slate-950 font-black text-xs flex items-center justify-center shadow-md shrink-0">
              2
            </div>
            <div class="text-xs font-black text-[#071d38] uppercase tracking-tight leading-tight group-hover:text-amber-600 transition-colors">
              Choose a Plan
            </div>
          </div>
          <div class="flex items-start gap-2.5 pt-1">
            <div class="w-9 h-9 rounded-xl bg-slate-100 text-[#071d38] flex items-center justify-center text-lg shrink-0">
              <i class="fa-solid fa-layer-group"></i>
            </div>
            <p class="text-[11px] text-slate-600 leading-snug font-medium">
              Select from our paid listing plans and activate your account.
            </p>
          </div>
        </a>

        <!-- CARD 3: LIST MACHINERY -->
        <a href="register.php?role=seller" 
           title="Step 3: List Machinery - Upload Machine Details, Photos & Price"
           class="absolute z-20 bg-gradient-to-b from-white to-slate-50 text-slate-900 rounded-3xl p-3.5 shadow-[0_16px_32px_-4px_rgba(0,0,0,0.5)] border border-slate-200/80 hover:border-amber-400 hover:ring-2 hover:ring-amber-400/60 hover:-translate-y-1 transition-all duration-300 block group"
           style="left: 66.4%; top: 47.6%; width: 22.5%; min-height: 115px;">
          <div class="flex items-center gap-2 mb-1.5">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-yellow-500 text-slate-950 font-black text-xs flex items-center justify-center shadow-md shrink-0">
              3
            </div>
            <div class="text-xs font-black text-[#071d38] uppercase tracking-tight leading-tight group-hover:text-amber-600 transition-colors">
              List Machinery
            </div>
          </div>
          <div class="flex items-start gap-2.5 pt-1">
            <div class="w-9 h-9 rounded-xl bg-slate-100 text-[#071d38] flex items-center justify-center text-lg shrink-0">
              <i class="fa-solid fa-cloud-arrow-up"></i>
            </div>
            <p class="text-[11px] text-slate-600 leading-snug font-medium">
              Upload your machinery details, images, price and location.
            </p>
          </div>
        </a>

        <!-- CARD 4: BUYER ENQUIRY -->
        <a href="index.php#catalog" 
           title="Step 4: Buyer Enquiry - Browse Catalog & Submit RFQs"
           class="absolute z-20 bg-gradient-to-b from-white to-slate-50 text-slate-900 rounded-3xl p-3.5 shadow-[0_16px_32px_-4px_rgba(0,0,0,0.5)] border border-slate-200/80 hover:border-amber-400 hover:ring-2 hover:ring-amber-400/60 hover:-translate-y-1 transition-all duration-300 block group"
           style="left: 36.3%; top: 69.6%; width: 27.3%; min-height: 115px;">
          <div class="flex items-center gap-2 mb-1.5">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-yellow-500 text-slate-950 font-black text-xs flex items-center justify-center shadow-md shrink-0">
              4
            </div>
            <div class="text-xs font-black text-[#071d38] uppercase tracking-tight leading-tight group-hover:text-amber-600 transition-colors">
              Buyer Enquiry
            </div>
          </div>
          <div class="flex items-start gap-2.5 pt-1">
            <div class="w-9 h-9 rounded-xl bg-slate-100 text-[#071d38] flex items-center justify-center text-lg shrink-0">
              <i class="fa-solid fa-envelope"></i>
            </div>
            <p class="text-[11px] text-slate-600 leading-snug font-medium">
              Interested buyers send enquiries through the platform.
            </p>
          </div>
        </a>

        <!-- CARD 5: DIRECT CONNECTION -->
        <a href="#who-we-serve" 
           title="Step 5: Direct Connection - Direct Buyer-Seller Deal Execution"
           class="absolute z-20 bg-gradient-to-b from-white to-slate-50 text-slate-900 rounded-3xl p-3.5 shadow-[0_16px_32px_-4px_rgba(0,0,0,0.5)] border border-slate-200/80 hover:border-amber-400 hover:ring-2 hover:ring-amber-400/60 hover:-translate-y-1 transition-all duration-300 block group"
           style="left: 11.1%; top: 47.6%; width: 22.5%; min-height: 115px;">
          <div class="flex items-center gap-2 mb-1.5">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-yellow-500 text-slate-950 font-black text-xs flex items-center justify-center shadow-md shrink-0">
              5
            </div>
            <div class="text-xs font-black text-[#071d38] uppercase tracking-tight leading-tight group-hover:text-amber-600 transition-colors">
              Direct Connection
            </div>
          </div>
          <div class="flex items-start gap-2.5 pt-1">
            <div class="w-9 h-9 rounded-xl bg-slate-100 text-[#071d38] flex items-center justify-center text-lg shrink-0">
              <i class="fa-solid fa-handshake"></i>
            </div>
            <p class="text-[10px] text-slate-600 leading-snug font-medium">
              We share buyer leads with seller. Both parties discuss details and finalize transaction.
            </p>
          </div>
        </a>

        <!-- FLANKING MACHINERY ON FLOOR -->
        <!-- Left Flank: CNC Machine + Yellow Forklift -->
        <div class="absolute bottom-1 left-2 z-10 pointer-events-none w-[30%] max-w-[320px]">
          <img src="/assets/images/machines-flank-left.png" alt="CNC Machine and Forklift" class="w-full h-auto drop-shadow-[0_12px_12px_rgba(0,0,0,0.7)]">
        </div>

        <!-- Right Flank: Excavator + Power Press -->
        <div class="absolute bottom-1 right-2 z-10 pointer-events-none w-[30%] max-w-[320px]">
          <img src="/assets/images/machines-flank-right.png" alt="Excavator and Hydraulic Press" class="w-full h-auto drop-shadow-[0_12px_12px_rgba(0,0,0,0.7)]">
        </div>

      </div>

    </div>

    <!-- AUTO-SCALE RESIZER FOR ALL DEVICES (DESKTOP, TABLET & MOBILE) -->
    <script>
    (function() {
      function scaleWorkflowCanvas() {
        var wrap = document.getElementById('workflowCanvasWrap');
        if (!wrap) return;
        var inner = document.getElementById('workflowCanvasInner');
        if (!inner) return;
        var w = wrap.clientWidth || wrap.offsetWidth || window.innerWidth;
        if (w > 1024) w = 1024;
        if (w > 0) {
          var s = w / 1024;
          inner.style.transform = 'scale(' + s + ')';
          wrap.style.height = Math.round(682 * s) + 'px';
        }
      }
      if (typeof ResizeObserver !== 'undefined') {
        var ro = new ResizeObserver(scaleWorkflowCanvas);
        var target = document.getElementById('workflowCanvasWrap');
        if (target) ro.observe(target);
      }
      window.addEventListener('resize', scaleWorkflowCanvas);
      window.addEventListener('orientationchange', scaleWorkflowCanvas);
      document.addEventListener('DOMContentLoaded', scaleWorkflowCanvas);
      window.addEventListener('load', scaleWorkflowCanvas);
      scaleWorkflowCanvas();
      setTimeout(scaleWorkflowCanvas, 50);
      setTimeout(scaleWorkflowCanvas, 250);
    })();
    </script>


      <!-- Quick Action CTAs below the Infographic (2 buttons in a row on mobile, flex row on desktop) -->
      <div class="mt-8 grid grid-cols-2 sm:flex sm:flex-wrap items-center justify-center gap-2 sm:gap-3 max-w-xl sm:max-w-none mx-auto jmh-mobile-grid-2">
        <a href="register.php?role=seller" class="col-span-1 col-1 w-full sm:w-auto px-2.5 sm:px-6 py-3 rounded-xl bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 text-slate-950 font-black text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider flex items-center justify-center text-center gap-1.5 sm:gap-2 shadow-lg shadow-amber-400/20 transition-all hover:scale-105 min-h-[46px]">
          <i class="fa-solid fa-store text-xs shrink-0"></i>
          <span class="leading-tight">Register as Seller</span>
        </a>
        <a href="index.php#catalog" class="col-span-1 col-1 w-full sm:w-auto px-2.5 sm:px-6 py-3 rounded-xl bg-white/10 hover:bg-white/15 text-white font-bold text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider border border-white/20 transition-all flex items-center justify-center text-center gap-1.5 sm:gap-2 hover:scale-105 min-h-[46px]">
          <i class="fa-solid fa-boxes-stacked text-amber-400 text-xs shrink-0"></i>
          <span class="leading-tight">Explore Machinery</span>
        </a>
        <button type="button" onclick="openRequirementModal()" class="col-span-1 col-1 w-full sm:w-auto px-2.5 sm:px-6 py-3 rounded-xl bg-emerald-600/30 hover:bg-emerald-600/50 text-emerald-300 font-bold text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider border border-emerald-400/30 transition-all flex items-center justify-center text-center gap-1.5 sm:gap-2 cursor-pointer hover:scale-105 min-h-[46px]">
          <i class="fa-solid fa-file-signature text-emerald-400 text-xs shrink-0"></i>
          <span class="leading-tight">Post Machinery RFQ</span>
        </button>
        <a href="#who-we-serve" class="col-span-1 col-1 w-full sm:w-auto px-2.5 sm:px-6 py-3 rounded-xl bg-blue-600/20 hover:bg-blue-600/40 text-blue-300 font-bold text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider border border-blue-400/30 transition-all flex items-center justify-center text-center gap-1.5 sm:gap-2 hover:scale-105 min-h-[46px]">
          <i class="fa-solid fa-users text-amber-400 text-xs shrink-0"></i>
          <span class="leading-tight">Who We Serve</span>
        </a>
      </div>

    </div>
  </section>


  <!-- ========================================================
       SECTION 3: WHO WE SERVE – SELLERS & BUYERS CARDS
       ======================================================== -->
  <section id="who-we-serve" class="py-14 sm:py-20 px-4 bg-slate-900 text-white border-b border-slate-800">
    <div class="max-w-7xl mx-auto space-y-8">
      
      <div class="text-center max-w-3xl mx-auto space-y-3">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800 text-amber-400 text-xs font-bold uppercase tracking-wider border border-slate-700">
          <i class="fa-solid fa-users"></i>
          <span>Target Audience &amp; Value Proposition</span>
        </div>
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-white tracking-tight">
          Who We Serve
        </h2>
        <p class="text-slate-300 text-xs sm:text-sm max-w-2xl mx-auto leading-relaxed">
          Connecting plant owners, machine dealers, manufacturers, and industrial buyers through a dedicated, commission-free platform.
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
        
        <!-- CARD 1: FOR MACHINERY SELLERS -->
        <div class="bg-white rounded-3xl border-2 border-amber-400 overflow-hidden shadow-2xl flex flex-col justify-between text-slate-900">
          
          <div class="bg-[#051833] p-3.5 sm:p-4 border-b-2 border-amber-400 flex items-center gap-3 text-left">
            <div class="bg-gradient-to-r from-amber-400 to-yellow-400 text-slate-950 font-black text-xs sm:text-sm uppercase px-4 py-1.5 rounded-xl shadow-md flex items-center gap-2 shrink-0">
              <i class="fa-solid fa-store text-slate-950"></i>
              <span>Sellers</span>
            </div>
            <div class="h-5 w-px bg-white/20 shrink-0"></div>
            <div class="text-[11px] sm:text-xs font-bold text-white leading-tight">
              Machinery Owners, Dealers, Traders, Manufacturers and Businesses
            </div>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 p-3 sm:p-4 bg-white text-center gap-2 sm:gap-0">
            <div class="p-2 flex flex-col items-center justify-start text-center">
              <div class="w-10 h-10 rounded-xl bg-amber-400/20 text-slate-900 flex items-center justify-center text-lg mb-2">
                <i class="fa-solid fa-user-plus"></i>
              </div>
              <div class="text-xs font-bold text-slate-800 leading-snug">
                Register as a Seller
              </div>
            </div>

            <div class="p-2 flex flex-col items-center justify-start text-center">
              <div class="w-10 h-10 rounded-xl bg-amber-400/20 text-slate-900 flex items-center justify-center text-lg mb-2">
                <i class="fa-solid fa-layer-group"></i>
              </div>
              <div class="text-xs font-bold text-slate-800 leading-snug">
                Choose a Listing Plan <span class="text-[10px] text-amber-600 block">(Paid Options)</span>
              </div>
            </div>

            <div class="p-2 flex flex-col items-center justify-start text-center">
              <div class="w-10 h-10 rounded-xl bg-amber-400/20 text-slate-900 flex items-center justify-center text-lg mb-2">
                <i class="fa-solid fa-cloud-arrow-up"></i>
              </div>
              <div class="text-xs font-bold text-slate-800 leading-snug">
                Upload Machine Details <span class="text-[10px] text-slate-500 block">(Photos, Specs, Price, Location)</span>
              </div>
            </div>

            <div class="p-2 flex flex-col items-center justify-start text-center">
              <div class="w-10 h-10 rounded-xl bg-amber-400/20 text-slate-900 flex items-center justify-center text-lg mb-2">
                <i class="fa-solid fa-bullhorn"></i>
              </div>
              <div class="text-xs font-bold text-slate-800 leading-snug">
                Receive Buyer Enquiries <span class="text-[10px] text-emerald-600 block">(Leads)</span>
              </div>
            </div>

            <div class="p-2 flex flex-col items-center justify-start text-center col-span-2 sm:col-span-1">
              <div class="w-10 h-10 rounded-xl bg-amber-400/20 text-slate-900 flex items-center justify-center text-lg mb-2">
                <i class="fa-solid fa-chart-column"></i>
              </div>
              <div class="text-xs font-bold text-slate-800 leading-snug">
                Manage Listings &amp; Enquiries in Seller Dashboard
              </div>
            </div>
          </div>

          <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-center">
            <a href="register.php?role=seller" class="text-xs font-black text-slate-950 bg-amber-400 hover:bg-amber-300 px-5 py-2 rounded-xl transition-all shadow-sm flex items-center gap-2">
              <i class="fa-solid fa-plus-circle"></i>
              <span>Register as a Seller &amp; View Plans</span>
            </a>
          </div>

        </div>

        <!-- CARD 2: FOR MACHINERY BUYERS -->
        <div class="bg-white rounded-3xl border-2 border-amber-400 overflow-hidden shadow-2xl flex flex-col justify-between text-slate-900">
          
          <div class="bg-[#051833] p-3.5 sm:p-4 border-b-2 border-amber-400 flex items-center gap-3 text-left">
            <div class="bg-gradient-to-r from-amber-400 to-yellow-400 text-slate-950 font-black text-xs sm:text-sm uppercase px-4 py-1.5 rounded-xl shadow-md flex items-center gap-2 shrink-0">
              <i class="fa-solid fa-users text-slate-950"></i>
              <span>Buyers</span>
            </div>
            <div class="h-5 w-px bg-white/20 shrink-0"></div>
            <div class="text-[11px] sm:text-xs font-bold text-white leading-tight">
              Manufacturers, Industries, Contractors, SMEs and Investors
            </div>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 p-3 sm:p-4 bg-white text-center gap-2 sm:gap-0">
            <div class="p-2 flex flex-col items-center justify-start text-center">
              <div class="w-10 h-10 rounded-xl bg-blue-500/15 text-slate-900 flex items-center justify-center text-lg mb-2">
                <i class="fa-solid fa-magnifying-glass"></i>
              </div>
              <div class="text-xs font-bold text-slate-800 leading-snug">
                Search Machinery <span class="text-[10px] text-slate-500 block">by Type, Brand or Location</span>
              </div>
            </div>

            <div class="p-2 flex flex-col items-center justify-start text-center">
              <div class="w-10 h-10 rounded-xl bg-blue-500/15 text-slate-900 flex items-center justify-center text-lg mb-2">
                <i class="fa-solid fa-layer-group"></i>
              </div>
              <div class="text-xs font-bold text-slate-800 leading-snug">
                Explore Multiple Listings
              </div>
            </div>

            <div class="p-2 flex flex-col items-center justify-start text-center">
              <div class="w-10 h-10 rounded-xl bg-blue-500/15 text-slate-900 flex items-center justify-center text-lg mb-2">
                <i class="fa-solid fa-envelope"></i>
              </div>
              <div class="text-xs font-bold text-slate-800 leading-snug">
                Send Enquiry to Sellers
              </div>
            </div>

            <div class="p-2 flex flex-col items-center justify-start text-center">
              <div class="w-10 h-10 rounded-xl bg-blue-500/15 text-slate-900 flex items-center justify-center text-lg mb-2">
                <i class="fa-solid fa-handshake"></i>
              </div>
              <div class="text-xs font-bold text-slate-800 leading-snug">
                Connect Directly with Sellers
              </div>
            </div>

            <div class="p-2 flex flex-col items-center justify-start text-center col-span-2 sm:col-span-1">
              <div class="w-10 h-10 rounded-xl bg-blue-500/15 text-slate-900 flex items-center justify-center text-lg mb-2">
                <i class="fa-solid fa-scale-balanced"></i>
              </div>
              <div class="text-xs font-bold text-slate-800 leading-snug">
                Negotiate, Inspect and Complete Transaction Independently
              </div>
            </div>
          </div>

          <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-center">
            <a href="index.php#catalog" class="text-xs font-black text-white bg-[#051833] hover:bg-slate-800 px-5 py-2 rounded-xl transition-all shadow-sm flex items-center gap-2">
              <i class="fa-solid fa-magnifying-glass text-amber-400"></i>
              <span>Browse 5,000+ Machinery Listings</span>
            </a>
          </div>

        </div>

      </div>

      <!-- BOTTOM CENTER GOLD RIBBON -->
      <div class="text-center pt-2">
        <div class="relative inline-flex items-center justify-center">
          <div class="hidden md:block w-24 lg:w-40 h-0.5 bg-gradient-to-r from-transparent to-amber-400 mr-4"></div>
          <div class="bg-[#051833] border-2 border-amber-400 rounded-full px-6 sm:px-10 py-2.5 sm:py-3 shadow-2xl flex items-center gap-3">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-yellow-500 text-slate-950 flex items-center justify-center text-xs font-black shrink-0 shadow-sm">
              <i class="fa-solid fa-users"></i>
            </div>
            <span class="text-xs sm:text-sm font-black text-white uppercase tracking-wider drop-shadow-sm">
              Connect Buyers and Sellers for Industrial Machinery Deals
            </span>
          </div>
          <div class="hidden md:block w-24 lg:w-40 h-0.5 bg-gradient-to-l from-transparent to-amber-400 ml-4"></div>
        </div>
      </div>

    </div>
  </section>


  <!-- ========================================================
       SECTION 4 & 5: WHAT WE DO vs WHAT WE DON'T DO
       ======================================================== -->
  <section class="py-14 sm:py-20 px-4 bg-slate-100 text-slate-900 border-b border-slate-200">
    <div class="max-w-7xl mx-auto space-y-10">
      
      <div class="text-center max-w-3xl mx-auto space-y-3">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-200 text-slate-800 text-xs font-bold uppercase tracking-wider">
          <i class="fa-solid fa-scale-balanced"></i>
          <span>Platform Clarity &amp; Transparency</span>
        </div>
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
          What We Do &amp; What We Don't Do
        </h2>
        <p class="text-slate-600 text-xs sm:text-sm max-w-2xl mx-auto leading-relaxed">
          Joule Machine Hub connects industrial machinery buyers and sellers transparently. Here is the clear scope of our digital marketplace services:
        </p>
      </div>

      <!-- Two Side-by-Side Panels -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch">
        
        <!-- PANEL 1: WHAT WE DO (Light Green Theme) -->
        <div class="bg-white border-2 border-emerald-500/40 rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col justify-between text-left space-y-6">
          <div class="space-y-4">
            
            <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-emerald-600 text-white font-black text-xs sm:text-sm uppercase tracking-wider shadow-sm">
              <i class="fa-solid fa-circle-check text-base"></i>
              <span>What We Do</span>
            </div>

            <ul class="space-y-3.5 text-xs sm:text-sm font-semibold text-slate-700 pt-2">
              <li class="flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                <span>Provide an online platform for industrial machinery listings.</span>
              </li>
              <li class="flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                <span>Offer paid machinery listing packages for sellers.</span>
              </li>
              <li class="flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                <span>Connect machinery buyers with sellers.</span>
              </li>
              <li class="flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                <span>Generate and share buyer enquiries (qualified leads).</span>
              </li>
              <li class="flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                <span>Help buyers explore machinery specifications, pricing and location.</span>
              </li>
              <li class="flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                <span>Provide digital tools for sellers to manage their listings and enquiries.</span>
              </li>
            </ul>

          </div>

          <div class="pt-4 border-t border-slate-100">
            <span class="text-xs font-bold text-emerald-700 flex items-center gap-2">
              <i class="fa-solid fa-circle-info"></i>
              <span>Empowering verified machinery discovery with 100% direct contact.</span>
            </span>
          </div>
        </div>

        <!-- PANEL 2: WHAT WE DON'T DO (Light Red Theme) -->
        <div class="bg-white border-2 border-rose-500/40 rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col justify-between text-left space-y-6">
          <div class="space-y-4">
            
            <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-rose-600 text-white font-black text-xs sm:text-sm uppercase tracking-wider shadow-sm">
              <i class="fa-solid fa-circle-xmark text-base"></i>
              <span>What We Don't Do</span>
            </div>

            <ul class="space-y-3.5 text-xs sm:text-sm font-semibold text-slate-700 pt-2">
              <li class="flex items-start gap-3">
                <i class="fa-solid fa-circle-xmark text-rose-600 text-lg shrink-0 mt-0.5"></i>
                <span>We do not own the listed machinery.</span>
              </li>
              <li class="flex items-start gap-3">
                <i class="fa-solid fa-circle-xmark text-rose-600 text-lg shrink-0 mt-0.5"></i>
                <span>We do not purchase or resell machinery.</span>
              </li>
              <li class="flex items-start gap-3">
                <i class="fa-solid fa-circle-xmark text-rose-600 text-lg shrink-0 mt-0.5"></i>
                <span>We do not physically inspect machinery.</span>
              </li>
              <li class="flex items-start gap-3">
                <i class="fa-solid fa-circle-xmark text-rose-600 text-lg shrink-0 mt-0.5"></i>
                <span>We do not provide machinery inspection or certification.</span>
              </li>
              <li class="flex items-start gap-3">
                <i class="fa-solid fa-circle-xmark text-rose-600 text-lg shrink-0 mt-0.5"></i>
                <span>We do not handle dismantling or material handling.</span>
              </li>
              <li class="flex items-start gap-3">
                <i class="fa-solid fa-circle-xmark text-rose-600 text-lg shrink-0 mt-0.5"></i>
                <span>We do not arrange transportation or logistics.</span>
              </li>
              <li class="flex items-start gap-3">
                <i class="fa-solid fa-circle-xmark text-rose-600 text-lg shrink-0 mt-0.5"></i>
                <span>We do not manage buyer-seller payments or delivery.</span>
              </li>
            </ul>

          </div>

          <!-- Explicit Disclaimer Callout -->
          <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200">
            <p class="text-[11px] sm:text-xs text-rose-900 leading-relaxed font-medium">
              <strong>Important Notice:</strong> Joule Machine Hub operates as an online marketplace connecting buyers and sellers. Physical inspections, negotiations, payments, transportation and delivery are independently handled by the respective buyer and seller.
            </p>
          </div>
        </div>

      </div>

    </div>
  </section>


  <!-- ========================================================
       SECTION 6: OUR PLATFORM BENEFITS
       ======================================================== -->
  <section class="py-14 sm:py-20 px-4 bg-gradient-to-r from-[#051427] via-[#0a2343] to-[#051427] text-white border-b border-slate-800">
    <div class="max-w-7xl mx-auto space-y-10">
      
      <div class="text-center max-w-3xl mx-auto space-y-3">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-400/20 text-amber-300 text-xs font-bold uppercase tracking-wider border border-amber-400/30">
          <i class="fa-solid fa-award"></i>
          <span>Value For Industry</span>
        </div>
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-white tracking-tight">
          Key Platform Benefits
        </h2>
        <p class="text-slate-300 text-xs sm:text-sm max-w-2xl mx-auto leading-relaxed">
          Engineered to give factory owners, machinery dealers, and procurement teams an edge in speed, clarity, and reach.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <div class="bg-white/5 border border-white/10 rounded-3xl p-6 text-left hover:border-amber-400/50 transition-all group">
          <div class="w-12 h-12 rounded-2xl bg-amber-400/20 text-amber-400 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-laptop"></i>
          </div>
          <h3 class="text-base font-black text-white">Online Industrial Machinery Marketplace</h3>
          <p class="text-xs text-slate-300 mt-2 leading-relaxed">
            A specialized 24/7 digital hub to showcase, discover, and enquire about heavy manufacturing machinery and workshop assets.
          </p>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-3xl p-6 text-left hover:border-amber-400/50 transition-all group">
          <div class="w-12 h-12 rounded-2xl bg-amber-400/20 text-amber-400 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-layer-group"></i>
          </div>
          <h3 class="text-base font-black text-white">Paid Seller Listing Plans</h3>
          <p class="text-xs text-slate-300 mt-2 leading-relaxed">
            Transparent subscription packages for dealers and manufacturers with zero hidden transaction commissions on final sale values.
          </p>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-3xl p-6 text-left hover:border-amber-400/50 transition-all group">
          <div class="w-12 h-12 rounded-2xl bg-amber-400/20 text-amber-400 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-location-dot"></i>
          </div>
          <h3 class="text-base font-black text-white">Seller and Machine Location Details</h3>
          <p class="text-xs text-slate-300 mt-2 leading-relaxed">
            Clear visibility of machinery location, origin city, and manufacturing plant details so buyers plan inspection trips efficiently.
          </p>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-3xl p-6 text-left hover:border-amber-400/50 transition-all group">
          <div class="w-12 h-12 rounded-2xl bg-amber-400/20 text-amber-400 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-industry"></i>
          </div>
          <h3 class="text-base font-black text-white">Wide Range of Industrial Machinery</h3>
          <p class="text-xs text-slate-300 mt-2 leading-relaxed">
            Comprehensive catalog spanning CNC VMC, turning centers, hydraulic press brakes, plastic moulding, compressors, and plant tooling.
          </p>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-3xl p-6 text-left hover:border-amber-400/50 transition-all group">
          <div class="w-12 h-12 rounded-2xl bg-amber-400/20 text-amber-400 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-users"></i>
          </div>
          <h3 class="text-base font-black text-white">Buyer Enquiry Generation</h3>
          <p class="text-xs text-slate-300 mt-2 leading-relaxed">
            Direct routing of high-intent buyer leads with verified machinery specifications, target timelines, and real contact information.
          </p>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-3xl p-6 text-left hover:border-amber-400/50 transition-all group">
          <div class="w-12 h-12 rounded-2xl bg-amber-400/20 text-amber-400 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-globe"></i>
          </div>
          <h3 class="text-base font-black text-white">Local and Global Reach</h3>
          <p class="text-xs text-slate-300 mt-2 leading-relaxed">
            Bridging equipment suppliers with machine buyers across major industrial zones in India, Europe, the Americas, and Asia.
          </p>
        </div>

      </div>

    </div>
  </section>


  <!-- ========================================================
       SECTION 7: CLOSING BANNER & DUAL CONVERSION CTAS
       ======================================================== -->
  <section class="py-16 sm:py-24 px-4 bg-gradient-to-r from-[#041224] via-[#092243] to-[#041224] text-white text-center relative overflow-hidden">
    <div class="max-w-4xl mx-auto space-y-6 relative z-10">
      
      <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-400/20 text-amber-300 text-xs font-bold uppercase tracking-wider border border-amber-400/30">
        <i class="fa-solid fa-handshake"></i>
        <span>Start Trading Industrial Equipment</span>
      </div>

      <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight leading-tight">
        Connecting Industrial Machinery <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-yellow-300">Buyers and Sellers</span>
      </h2>

      <p class="text-slate-200 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
        List your machinery, reach potential buyers and connect directly through Joule Machine Hub.
      </p>

      <div class="pt-4 grid grid-cols-2 sm:flex sm:flex-wrap items-center justify-center gap-2 sm:gap-4 max-w-xl sm:max-w-none mx-auto jmh-mobile-grid-2">
        <a href="index.php#catalog" class="col-span-2 span-full w-full sm:w-auto px-4 sm:px-7 py-3.5 sm:py-4 rounded-xl bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-400/25 transition-all hover:scale-105 flex items-center justify-center text-center gap-2 min-h-[46px]">
          <i class="fa-solid fa-boxes-stacked text-xs sm:text-sm shrink-0"></i>
          <span>Explore Machinery</span>
        </a>

        <a href="register.php?role=seller" class="col-span-1 col-1 w-full sm:w-auto px-2 sm:px-7 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider border border-white/20 transition-all flex items-center justify-center text-center gap-1.5 sm:gap-2 min-h-[46px]">
          <i class="fa-solid fa-store text-amber-400 text-xs shrink-0"></i>
          <span class="leading-tight">Register as Seller</span>
        </a>

        <button type="button" onclick="openRequirementModal()" class="col-span-1 col-1 w-full sm:w-auto px-2 sm:px-7 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-[11px] sm:text-xs uppercase tracking-tight sm:tracking-wider transition-all shadow-md flex items-center justify-center text-center gap-1.5 sm:gap-2 cursor-pointer min-h-[46px]">
          <i class="fa-solid fa-file-signature text-xs shrink-0"></i>
          <span class="leading-tight">Post Machinery RFQ</span>
        </button>
      </div>

    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
