<?php
$pageTitle = "My Membership & Plans";
require_once __DIR__ . '/header.php';

$plans = getMembershipPlans();
$promos = getFeaturedPromotionPackages();
$activePlanKey = $userSub['plan_key'] ?? 'free';
$curr = $_GET['curr'] ?? 'INR';
$currSym = ($curr === 'USD') ? '$' : '₹';
?>

<!-- BREADCRUMB & HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
  <div>
    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
      <a href="index.php" class="hover:text-blue-600">Dashboard</a>
      <span>/</span>
      <span class="text-slate-800 font-bold">My Membership</span>
    </div>
    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Seller Membership Plans</h1>
    <p class="text-xs text-slate-500">Upgrade your account to list more industrial machinery and unlock premium promotion tools.</p>
  </div>

  <!-- Currency Switcher (INR / USD) -->
  <div class="bg-white rounded-xl p-1 border border-slate-200 inline-flex shadow-xs text-xs font-bold">
    <a 
      href="membership.php?curr=INR" 
      class="px-3 py-1.5 rounded-lg transition-colors <?php echo $curr === 'INR' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'; ?>"
    >
      ₹ INR (India)
    </a>
    <a 
      href="membership.php?curr=USD" 
      class="px-3 py-1.5 rounded-lg transition-colors <?php echo $curr === 'USD' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'; ?>"
    >
      $ USD (International)
    </a>
  </div>
</div>

<!-- CURRENT PLAN CALLOUT -->
<div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div class="flex items-center gap-4">
    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center font-black text-xl shrink-0">
      <i class="fa-solid fa-crown"></i>
    </div>
    <div>
      <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Current Active Membership</div>
      <div class="text-base font-black text-slate-900 flex items-center gap-2 mt-0.5">
        <span><?php echo htmlspecialchars($userSub['plan_name']); ?></span>
        <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">Active</span>
      </div>
      <div class="text-xs text-slate-500 mt-0.5">
        Quotas: <strong><?php echo $userStats['total']; ?> / <?php echo $userSub['listing_limit']; ?></strong> Machines Listed • 
        Valid until <strong><?php echo date('M d, Y', strtotime($userSub['end_date'])); ?></strong>
      </div>
    </div>
  </div>

  <div class="flex items-center gap-3">
    <a 
      href="payments.php" 
      class="text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 px-4 py-2.5 rounded-xl transition-colors border border-slate-200"
    >
      View Payment History
    </a>
  </div>
</div>

<!-- MEMBERSHIP PRICING CARDS (Section 3 & 4 Specification) -->
<div>
  <div class="mb-4">
    <h2 class="text-lg font-black text-slate-900">Choose Your Seller Plan</h2>
    <p class="text-xs text-slate-500">Pick the plan that fits your factory inventory scale. All plans include 0% brokerage fees on machinery deals.</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <?php foreach ($plans as $key => $p): ?>
      <?php
      $isCurrent = ($activePlanKey === $key);
      $price = ($curr === 'USD') ? $p['price_usd'] : $p['price_inr'];
      $cardBorder = $p['popular'] ? 'border-2 border-blue-600 shadow-xl relative' : 'border border-slate-200 shadow-xs';
      ?>
      <div class="bg-white rounded-3xl p-6 flex flex-col justify-between <?php echo $cardBorder; ?>">
        
        <?php if ($p['popular']): ?>
          <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-blue-600 text-white font-black text-[10px] uppercase tracking-wider px-3 py-1 rounded-full shadow-md">
            Most Popular
          </div>
        <?php endif; ?>

        <div>
          <!-- Plan Name & Tagline -->
          <div class="flex items-center justify-between mb-2">
            <h3 class="font-black text-base text-slate-900"><?php echo htmlspecialchars($p['name']); ?></h3>
            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full <?php echo $p['badge_color']; ?> border">
              <?php echo htmlspecialchars($p['badge']); ?>
            </span>
          </div>
          <p class="text-xs text-slate-500 mb-4 min-h-[32px]">
            <?php echo htmlspecialchars($p['tagline']); ?>
          </p>

          <!-- Price Display -->
          <div class="mb-6 pb-4 border-b border-slate-100">
            <div class="flex items-baseline gap-1">
              <span class="text-3xl font-black text-slate-900">
                <?php echo $currSym . number_format($price); ?>
              </span>
              <span class="text-xs text-slate-500 font-semibold">
                <?php echo $p['billing_period']; ?>
              </span>
            </div>
            <div class="text-[11px] text-emerald-600 font-bold mt-1">
              <i class="fa-solid fa-check text-[10px] mr-1"></i>
              Up to <?php echo $p['listing_limit']; ?> Machine Listings
            </div>
          </div>

          <!-- Features List -->
          <ul class="space-y-2.5 text-xs text-slate-600 mb-6">
            <?php foreach ($p['features'] as $feat): ?>
              <li class="flex items-start gap-2">
                <i class="fa-solid fa-circle-check text-emerald-500 text-xs shrink-0 mt-0.5"></i>
                <span><?php echo htmlspecialchars($feat); ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Action CTA Button -->
        <div>
          <?php if ($isCurrent): ?>
            <button 
              type="button" 
              disabled 
              class="w-full bg-slate-100 text-slate-500 font-bold text-xs py-3 px-4 rounded-xl cursor-default text-center"
            >
              Current Active Plan
            </button>
          <?php elseif ($key === 'free'): ?>
            <button 
              type="button" 
              disabled 
              class="w-full bg-slate-100 text-slate-500 font-bold text-xs py-3 px-4 rounded-xl cursor-default text-center"
            >
              Default Plan
            </button>
          <?php else: ?>
            <a 
              href="checkout.php?type=plan&key=<?php echo urlencode($key); ?>&curr=<?php echo $curr; ?>" 
              class="w-full inline-flex items-center justify-center gap-2 <?php echo $p['popular'] ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/30' : 'bg-joule-yellow hover:bg-amber-400 text-slate-950'; ?> font-black text-xs py-3 px-4 rounded-xl transition-all"
            >
              <span><?php echo htmlspecialchars($p['button_text']); ?></span>
              <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
          <?php endif; ?>
        </div>

      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- SECTION 7: FEATURED MACHINE PROMOTION PACKAGES -->
<div class="pt-4">
  <div class="mb-4">
    <div class="flex items-center gap-2">
      <div class="w-7 h-7 rounded-lg bg-amber-500 text-slate-950 flex items-center justify-center font-black text-xs">
        <i class="fa-solid fa-bolt"></i>
      </div>
      <h2 class="text-lg font-black text-slate-900">Featured Machine Promotions</h2>
    </div>
    <p class="text-xs text-slate-500">Accelerate plant sales by placing individual high-value machines at the top of buyer searches.</p>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    <?php foreach ($promos as $promoKey => $promo): ?>
      <?php
      $promoPrice = ($curr === 'USD') ? $promo['price_usd'] : $promo['price_inr'];
      ?>
      <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-col justify-between hover:border-amber-400 transition-colors">
        <div>
          <div class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-1">
            <?php echo $promo['days']; ?>-Day Boost
          </div>
          <h3 class="font-black text-slate-900 text-sm mb-2"><?php echo htmlspecialchars($promo['name']); ?></h3>
          <p class="text-xs text-slate-500 leading-relaxed mb-4">
            <?php echo htmlspecialchars($promo['description']); ?>
          </p>
        </div>

        <div>
          <div class="text-2xl font-black text-slate-900 mb-3">
            <?php echo $currSym . number_format($promoPrice); ?>
          </div>
          <a 
            href="checkout.php?type=featured&key=<?php echo urlencode($promoKey); ?>&curr=<?php echo $curr; ?>" 
            class="w-full inline-flex items-center justify-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs py-2.5 px-3 rounded-xl transition-all"
          >
            <i class="fa-solid fa-crown text-amber-400 text-[10px]"></i>
            <span>Promote Machine</span>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
