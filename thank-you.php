<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'joulemachinehub.com';
$pageTitle = "Thank You - Inquiry Received | Joule Machine Hub";
$pageDesc = "Thank you for your machinery inquiry. Our technical procurement desk and verified plant owners will review your requirement and email you shortly.";
$canonicalUrl = $protocol . $host . "/thank-you";
$pageRobots = "noindex, follow";

require_once __DIR__ . '/data/machines.php';

$leadRef = htmlspecialchars($_GET['ref'] ?? ('JMH-' . strtoupper(substr(uniqid(), -6))));
$machineTitle = htmlspecialchars($_GET['machine'] ?? '');
$formType = htmlspecialchars($_GET['type'] ?? 'inquiry');

// Determine friendly context text
$heading = "Thank You! Your Inquiry Has Been Received";
$badgeText = "Verified Inquiry Submitted";
if ($formType === 'post_machine') {
    $heading = "Thank You! Machine Listing Submitted";
    $badgeText = "Listing Under Review";
} elseif ($formType === 'requirement') {
    $heading = "Thank You! Buy Requirement Broadcasted";
    $badgeText = "RFQ Active & Dispatched";
}

// 4 suggested machines for continued browsing
$suggestedMachines = array_slice($machines, 0, 4);

require_once __DIR__ . '/includes/header.php';
?>

<main class="min-h-[75vh] bg-slate-50 py-10 sm:py-16 px-4 flex flex-col justify-center">
  <div class="max-w-3xl mx-auto w-full space-y-8">
    
    <!-- Hero Confirmation Card -->
    <div class="bg-white rounded-3xl border-2 border-joule-blue shadow-xl overflow-hidden text-center">
      
      <!-- Top banner strip -->
      <div class="bg-joule-blue text-white p-8 sm:p-10 border-b-4 border-joule-yellow relative">
        <div class="w-20 h-20 rounded-full bg-emerald-500 text-white flex items-center justify-center font-black text-3xl mx-auto mb-4 shadow-lg ring-8 ring-emerald-500/20 animate-bounce">
          <i class="fa-solid fa-check"></i>
        </div>
        <span class="inline-block uppercase tracking-wider text-[11px] font-black bg-white/20 text-yellow-300 px-3.5 py-1 rounded-full mb-2">
          <?php echo $badgeText; ?>
        </span>
        <h1 class="text-2xl sm:text-4xl font-black text-white">
          <?php echo $heading; ?>
        </h1>
        <p class="text-xs sm:text-sm text-blue-200 mt-2 max-w-lg mx-auto">
          Our Technical Desk & Verified Plant Owners have been instantly notified. Expect direct quotation and inspection details sent to your email.
        </p>
      </div>

      <!-- Lead Details & Next Steps -->
      <div class="p-6 sm:p-10 space-y-6">
        
        <!-- Summary Box -->
        <div class="bg-slate-50 border-2 border-slate-200 rounded-2xl p-4 sm:p-6 text-left space-y-3">
          <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-200 pb-3">
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Inquiry ID:</span>
            <span class="text-xs font-mono font-black text-joule-blue bg-blue-50 px-3 py-1 rounded-lg border border-blue-200">
              #<?php echo $leadRef; ?>
            </span>
          </div>

          <?php if (!empty($machineTitle)): ?>
          <div class="flex items-start justify-between flex-wrap gap-2 border-b border-slate-200 pb-3">
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Inquired Equipment:</span>
            <span class="text-sm font-black text-slate-950 text-right">
              <?php echo $machineTitle; ?>
            </span>
          </div>
          <?php endif; ?>

          <div class="flex items-center justify-between flex-wrap gap-2">
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Expected Response:</span>
            <span class="text-xs font-black text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200 flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
              <span>Under 15 Minutes (During Business Hours)</span>
            </span>
          </div>
        </div>

        <!-- 3 What Happens Next Steps -->
        <div class="text-left space-y-3">
          <h2 class="text-xs font-black uppercase tracking-wider text-slate-500">What Happens Next:</h2>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="bg-blue-50/60 border border-blue-200 rounded-xl p-3.5 space-y-1">
              <div class="w-6 h-6 rounded-lg bg-joule-blue text-white flex items-center justify-center text-xs font-black">1</div>
              <div class="font-black text-slate-900 text-xs">Technical Check</div>
              <p class="text-[11px] text-slate-600 leading-relaxed">Plant floor power run logs & lowest ex-works quotation compiled.</p>
            </div>
            <div class="bg-blue-50/60 border border-blue-200 rounded-xl p-3.5 space-y-1">
              <div class="w-6 h-6 rounded-lg bg-joule-blue text-white flex items-center justify-center text-xs font-black">2</div>
              <div class="font-black text-slate-900 text-xs">Direct Connect</div>
              <p class="text-[11px] text-slate-600 leading-relaxed">Our technical desk connects directly via email with complete specs.</p>
            </div>
            <div class="bg-blue-50/60 border border-blue-200 rounded-xl p-3.5 space-y-1">
              <div class="w-6 h-6 rounded-lg bg-joule-blue text-white flex items-center justify-center text-xs font-black">3</div>
              <div class="font-black text-slate-900 text-xs">Physical Inspection</div>
              <p class="text-[11px] text-slate-600 leading-relaxed">Schedule physical plant visit or HD equipment walkthrough inspection.</p>
            </div>
          </div>
        </div>

        <!-- Immediate Action CTAs -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
          <a 
            href="/" 
            class="w-full sm:w-auto bg-joule-blue hover:bg-blue-900 text-joule-yellow font-black py-3.5 px-8 rounded-xl text-sm flex items-center justify-center gap-2 shadow-lg shadow-blue-950/20 transition-transform hover:scale-105"
          >
            <i class="fa-solid fa-industry"></i>
            <span>Browse More Machinery</span>
          </a>

          <a 
            href="/#catalog" 
            class="w-full sm:w-auto bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-3.5 px-6 rounded-xl text-sm flex items-center justify-center gap-2 border border-slate-300 transition-colors"
          >
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Back to Catalog</span>
          </a>
        </div>

      </div>
    </div>

    <!-- Recommended Equipment -->
    <div class="space-y-4 text-center sm:text-left">
      <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
          <h3 class="text-lg font-black text-slate-950">Explore Other Verified Industrial Equipment</h3>
          <p class="text-xs text-slate-500">Verified industrial equipment through our mediated B2B desk</p>
        </div>
        <a href="/#catalog" class="text-xs font-black text-joule-brightblue hover:underline">
          View All 42 Machines →
        </a>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($suggestedMachines as $sm): ?>
        <a href="<?php echo htmlspecialchars($sm['slug']); ?>" class="bg-white rounded-2xl border border-slate-200 p-3 shadow-xs hover:shadow-md transition-all group flex flex-col justify-between">
          <div>
            <div class="aspect-[16/10] bg-slate-950 rounded-xl overflow-hidden mb-2.5">
              <img src="<?php echo htmlspecialchars($sm['images'][0]); ?>" alt="<?php echo htmlspecialchars($sm['title']); ?>" class="w-full h-full object-contain p-1 group-hover:scale-105 transition-transform" loading="lazy">
            </div>
            <div class="text-[10px] text-slate-500 font-bold uppercase"><?php echo htmlspecialchars($sm['category']); ?></div>
            <div class="font-black text-slate-900 text-xs line-clamp-2 mt-0.5 group-hover:text-joule-brightblue">
              <?php echo htmlspecialchars($sm['title']); ?>
            </div>
          </div>
          <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="font-black text-joule-blue"><?php echo htmlspecialchars($sm['price_display']); ?></span>
            <span class="text-[10px] font-bold text-slate-500"><?php echo htmlspecialchars($sm['city']); ?></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
