<?php
$pageTitle = "Payments & Billing";
require_once __DIR__ . '/header.php';

$payments = getUserPayments($userId);

// Calculate total amount paid
$totalPaid = 0;
foreach ($payments as $p) {
    if (strtolower($p['status'] ?? '') === 'paid') {
        $totalPaid += (float)($p['amount'] ?? 0);
    }
}
?>

<!-- BREADCRUMB & HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
  <div>
    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
      <a href="index.php" class="hover:text-blue-600">Dashboard</a>
      <span>/</span>
      <span class="text-slate-800 font-bold">Payments &amp; Billing</span>
    </div>
    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Payments &amp; Invoices</h1>
    <p class="text-xs text-slate-500">Manage your active subscription, download tax invoices, and review billing history.</p>
  </div>

  <a 
    href="membership.php" 
    class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-md transition-all inline-flex items-center gap-2"
  >
    <i class="fa-solid fa-crown text-[10px] text-amber-300"></i>
    <span>Upgrade / Change Plan</span>
  </a>
</div>

<!-- 3 BILLING SUMMARY CARDS (Section 6 Specification) -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
  
  <!-- Card 1: Current Plan & Status -->
  <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Active Subscription</div>
    <div class="text-xl font-black text-slate-900 flex items-center gap-2">
      <span><?php echo htmlspecialchars($userSub['plan_name']); ?></span>
      <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
        Active
      </span>
    </div>
    <div class="text-xs text-slate-500 mt-2">
      Renewal Date: <strong class="text-slate-800"><?php echo date('M d, Y', strtotime($userSub['end_date'])); ?></strong>
    </div>
  </div>

  <!-- Card 2: Total Paid -->
  <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Total Amount Paid</div>
    <div class="text-xl font-black text-slate-900">
      ₹<?php echo number_format($totalPaid, 2); ?>
    </div>
    <div class="text-xs text-slate-500 mt-2">
      Lifetime transactions on Joule Hub
    </div>
  </div>

  <!-- Card 3: Invoices Count -->
  <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Official Invoices</div>
    <div class="text-xl font-black text-blue-600">
      <?php echo count($payments); ?> Invoices
    </div>
    <div class="text-xs text-slate-500 mt-2">
      Available for tax &amp; business deductions
    </div>
  </div>

</div>

<!-- PAYMENT TRANSACTIONS & INVOICES TABLE (Section 6 Specification) -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
  <div class="p-5 border-b border-slate-200 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <i class="fa-solid fa-receipt text-blue-600"></i>
      <h3 class="font-bold text-sm text-slate-900">Transaction History</h3>
    </div>
  </div>

  <?php if (empty($payments)): ?>
    <div class="p-12 text-center text-slate-500 space-y-3">
      <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center mx-auto text-2xl text-slate-400">
        <i class="fa-solid fa-file-invoice"></i>
      </div>
      <div>
        <h4 class="font-bold text-slate-800 text-sm">No payment transactions yet</h4>
        <p class="text-xs text-slate-500 mt-1">You are currently on the Free plan. When you upgrade or feature machines, invoices will be generated here.</p>
      </div>
      <a 
        href="membership.php" 
        class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2.5 rounded-xl shadow-xs"
      >
        <i class="fa-solid fa-crown text-[10px]"></i>
        <span>Explore Paid Plans</span>
      </a>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-500 font-semibold text-[11px] uppercase tracking-wider border-b border-slate-200">
          <tr>
            <th class="p-4">Invoice #</th>
            <th class="p-4">Service / Plan Description</th>
            <th class="p-4">Amount</th>
            <th class="p-4">Payment Method</th>
            <th class="p-4">Status</th>
            <th class="p-4">Date</th>
            <th class="p-4 text-right">Invoice</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($payments as $pay): ?>
            <?php
            $st = strtolower($pay['status'] ?? 'paid');
            $stClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
            if ($st === 'pending') $stClass = 'bg-amber-100 text-amber-800 border-amber-200';
            if ($st === 'failed') $stClass = 'bg-rose-100 text-rose-800 border-rose-200';
            if ($st === 'refunded') $stClass = 'bg-blue-100 text-blue-800 border-blue-200';
            ?>
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="p-4 font-mono font-bold text-slate-900">
                <?php echo htmlspecialchars($pay['invoice_id'] ?? ''); ?>
              </td>
              <td class="p-4 font-semibold text-slate-800">
                <?php echo htmlspecialchars($pay['title'] ?? 'Seller Subscription'); ?>
              </td>
              <td class="p-4 font-black text-slate-900 whitespace-nowrap">
                <?php echo ($pay['currency'] === 'USD' ? '$' : '₹') . number_format((float)($pay['amount'] ?? 0), 2); ?>
              </td>
              <td class="p-4 uppercase text-[11px] text-slate-600 font-semibold">
                <?php echo htmlspecialchars($pay['gateway'] ?? 'Razorpay'); ?>
              </td>
              <td class="p-4 whitespace-nowrap">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border <?php echo $stClass; ?>">
                  <?php echo htmlspecialchars(ucfirst($st)); ?>
                </span>
              </td>
              <td class="p-4 whitespace-nowrap text-slate-500 text-[11px]">
                <?php echo date('M d, Y', strtotime($pay['date'] ?? 'now')); ?>
              </td>
              <td class="p-4 text-right whitespace-nowrap">
                <a 
                  href="invoices.php?id=<?php echo urlencode($pay['invoice_id'] ?? ''); ?>" 
                  target="_blank" 
                  class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors"
                >
                  <i class="fa-solid fa-download text-[10px]"></i>
                  <span>Download Invoice</span>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
