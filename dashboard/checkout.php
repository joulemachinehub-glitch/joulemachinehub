<?php
$pageTitle = "Checkout & Payment";
require_once __DIR__ . '/header.php';

$type = $_GET['type'] ?? 'plan';
$key = $_GET['key'] ?? 'basic';
$curr = $_GET['curr'] ?? 'INR';
$currSym = ($curr === 'USD') ? '$' : '₹';

$plans = getMembershipPlans();
$promos = getFeaturedPromotionPackages();

$itemTitle = 'Plan Upgrade';
$amount = 999;
$description = '';

if ($type === 'plan') {
    if (isset($plans[$key])) {
        $p = $plans[$key];
        $itemTitle = $p['name'];
        $amount = ($curr === 'USD') ? $p['price_usd'] : $p['price_inr'];
        $description = $p['tagline'] . ' (' . $p['listing_limit'] . ' Machine Listings)';
    }
} elseif ($type === 'featured') {
    if (isset($promos[$key])) {
        $pr = $promos[$key];
        $itemTitle = $pr['name'];
        $amount = ($curr === 'USD') ? $pr['price_usd'] : $pr['price_inr'];
        $description = $pr['description'];
    }
}

$gstRate = ($curr === 'INR') ? 0.18 : 0.00;
$taxAmount = round($amount * $gstRate, 2);
$grandTotal = $amount + $taxAmount;

$error = '';

// Process Payment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_payment') {
    $gateway = $_POST['gateway'] ?? 'razorpay';
    $txnId = 'TXN-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

    if ($type === 'plan') {
        $result = activateUserSubscription($userId, $key, $grandTotal, $curr, $gateway, $txnId);
        if ($result['success']) {
            header('Location: payments.php?payment_success=1');
            exit;
        } else {
            $error = $result['message'];
        }
    } else {
        createPaymentRecord($userId, 'featured_listing', $itemTitle, $grandTotal, $curr, $gateway, 'paid', $txnId);
        header('Location: payments.php?payment_success=1');
        exit;
    }
}
?>

<!-- BREADCRUMB -->
<div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
  <a href="/dashboard" class="hover:text-blue-600">Dashboard</a>
  <span>/</span>
  <a href="membership.php" class="hover:text-blue-600">Membership</a>
  <span>/</span>
  <span class="text-slate-800 font-bold">Checkout</span>
</div>

<div class="max-w-4xl mx-auto py-4 space-y-6">
  
  <div>
    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Complete Your Payment</h1>
    <p class="text-xs text-slate-500">Secure checkout for Joule Machine Hub seller subscriptions &amp; promotions.</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <!-- Left Column: Payment Gateway Selection & Billing Details -->
    <div class="lg:col-span-7 space-y-6">
      
      <form action="checkout.php?type=<?php echo urlencode($type); ?>&key=<?php echo urlencode($key); ?>&curr=<?php echo $curr; ?>" method="POST" class="space-y-6">
        <input type="hidden" name="action" value="process_payment">

        <!-- Gateway Selector (Section 9 Specification: Razorpay / Stripe) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
          <h3 class="font-bold text-sm text-slate-900">Select Payment Method</h3>

          <div class="space-y-3 text-xs">
            
            <!-- Gateway 1: Razorpay (India & UPI) -->
            <label class="flex items-start gap-3.5 p-4 rounded-xl border-2 border-blue-600 bg-blue-50/40 cursor-pointer">
              <input type="radio" name="gateway" value="razorpay" checked class="w-4 h-4 text-blue-600 mt-0.5">
              <div class="flex-1">
                <div class="flex items-center justify-between">
                  <span class="font-bold text-slate-900">Razorpay (India &amp; UPI)</span>
                  <span class="text-[10px] font-black uppercase px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full">Recommended</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">
                  UPI (GPay, PhonePe, Paytm), NetBanking, RuPay, Visa, Mastercard, Corporate Cards.
                </p>
              </div>
            </label>

            <!-- Gateway 2: Stripe (International Cards) -->
            <label class="flex items-start gap-3.5 p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer">
              <input type="radio" name="gateway" value="stripe" class="w-4 h-4 text-blue-600 mt-0.5">
              <div class="flex-1">
                <div class="flex items-center justify-between">
                  <span class="font-bold text-slate-900">Stripe (International Cards)</span>
                  <span class="text-[10px] text-slate-500 font-semibold">USD / EUR / Global</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">
                  Accepts global credit &amp; debit cards from 135+ countries with instant settlement.
                </p>
              </div>
            </label>

            <!-- Gateway 3: Bank Transfer / NEFT -->
            <label class="flex items-start gap-3.5 p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer">
              <input type="radio" name="gateway" value="bank_transfer" class="w-4 h-4 text-blue-600 mt-0.5">
              <div class="flex-1">
                <span class="font-bold text-slate-900">Direct Bank Transfer (NEFT / RTGS / Wire)</span>
                <p class="text-[11px] text-slate-500 mt-0.5">
                  Corporate plant payments with manual invoice confirmation and tax receipts.
                </p>
              </div>
            </label>

          </div>
        </div>

        <!-- Billing Entity Details -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
          <h3 class="font-bold text-sm text-slate-900">Billing Information</h3>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
              <label class="block text-slate-600 font-bold mb-1">Company / Billing Name</label>
              <input 
                type="text" 
                name="billing_name" 
                value="<?php echo htmlspecialchars($currentUser['company'] ?? $currentUser['name']); ?>" 
                required 
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 font-semibold"
              >
            </div>
            <div>
              <label class="block text-slate-600 font-bold mb-1">Business Email</label>
              <input 
                type="email" 
                name="billing_email" 
                value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" 
                required 
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 font-semibold"
              >
            </div>
            <div>
              <label class="block text-slate-600 font-bold mb-1">GSTIN / Tax ID (Optional)</label>
              <input 
                type="text" 
                name="gstin" 
                value="<?php echo htmlspecialchars($currentUser['gst'] ?? ''); ?>" 
                placeholder="e.g. 29AAAAA0000A1Z5" 
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 font-semibold"
              >
            </div>
            <div>
              <label class="block text-slate-600 font-bold mb-1">Country</label>
              <input 
                type="text" 
                name="billing_country" 
                value="<?php echo htmlspecialchars($currentUser['country'] ?? 'India'); ?>" 
                required 
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 font-semibold"
              >
            </div>
          </div>
        </div>

        <!-- Submit & Pay Button -->
        <button 
          type="submit" 
          class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm py-4 px-6 rounded-2xl shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer"
        >
          <i class="fa-solid fa-lock text-xs"></i>
          <span>Pay <?php echo $currSym . number_format($grandTotal, 2); ?> &amp; Activate Now</span>
        </button>

      </form>

    </div>

    <!-- Right Column: Order Summary Card -->
    <div class="lg:col-span-5 space-y-4">
      <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <h3 class="font-bold text-sm text-slate-900 pb-3 border-b border-slate-100">
          Order Summary
        </h3>

        <div class="flex items-start justify-between gap-4">
          <div>
            <div class="font-bold text-slate-900 text-sm"><?php echo htmlspecialchars($itemTitle); ?></div>
            <div class="text-xs text-slate-500 mt-0.5"><?php echo htmlspecialchars($description); ?></div>
          </div>
          <div class="font-bold text-slate-900 text-sm whitespace-nowrap">
            <?php echo $currSym . number_format($amount, 2); ?>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-100 space-y-2 text-xs text-slate-600">
          <div class="flex items-center justify-between">
            <span>Subtotal:</span>
            <span><?php echo $currSym . number_format($amount, 2); ?></span>
          </div>

          <?php if ($curr === 'INR'): ?>
            <div class="flex items-center justify-between">
              <span>GST (18% Input Tax Credit):</span>
              <span>₹<?php echo number_format($taxAmount, 2); ?></span>
            </div>
          <?php endif; ?>

          <div class="pt-2 border-t border-slate-200 flex items-center justify-between font-black text-slate-900 text-base">
            <span>Grand Total:</span>
            <span class="text-blue-600"><?php echo $currSym . number_format($grandTotal, 2); ?></span>
          </div>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 space-y-1">
          <div class="font-bold text-slate-800">
            <i class="fa-solid fa-shield-halved text-emerald-600 mr-1"></i>
            100% Secure Transaction
          </div>
          <p>Instant activation. Official GST invoice generated immediately upon payment confirmation.</p>
        </div>
      </div>
    </div>

  </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
