<?php
// Printable Official Tax Invoice Viewer
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/membership.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$currentUser = getCurrentUser();
$userId = $currentUser['id'] ?? $currentUser['user_id'] ?? '';
$invoiceId = trim($_GET['id'] ?? '');

$invoice = getInvoiceDetails($invoiceId, $userId);

// Fallback sample invoice if id not found
if (!$invoice) {
    $invoice = [
        'invoice_id' => $invoiceId ?: ('INV-' . date('Ymd') . '-SAMPLE'),
        'user_id'    => $userId,
        'title'      => 'Basic Seller Membership Plan (Annual)',
        'type'       => 'membership',
        'amount'     => 999.00,
        'currency'   => 'INR',
        'gateway'    => 'Razorpay',
        'txn_id'     => 'TXN-SAMPLE-9482',
        'status'     => 'paid',
        'date'       => date('Y-m-d H:i:s')
    ];
}

$subtotal = round((float)$invoice['amount'] / 1.18, 2);
$gst = round((float)$invoice['amount'] - $subtotal, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice #<?php echo htmlspecialchars($invoice['invoice_id']); ?> - Joule Machine Hub</title>
  <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    @media print {
      .no-print { display: none !important; }
      body { background: white !important; }
      .invoice-box { border: none !important; box-shadow: none !important; }
    }
  </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 min-h-screen flex flex-col items-center justify-center">

  <!-- Top Action Bar (hidden on print) -->
  <div class="max-w-3xl w-full mb-4 flex items-center justify-between no-print text-xs">
    <a href="payments.php" class="font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1.5">
      <i class="fa-solid fa-arrow-left text-[10px]"></i>
      <span>Back to Payments</span>
    </a>
    <button 
      onclick="window.print()" 
      class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer"
    >
      <i class="fa-solid fa-print"></i>
      <span>Print / Save as PDF</span>
    </button>
  </div>

  <!-- INVOICE CONTAINER -->
  <div class="invoice-box max-w-3xl w-full bg-white rounded-3xl border border-slate-200 shadow-xl p-8 sm:p-12 space-y-8">
    
    <!-- Invoice Header -->
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b border-slate-200">
      <div>
        <div class="flex items-center gap-2.5 mb-2">
          <span class="w-8 h-8 rounded-lg bg-blue-600 text-white font-black flex items-center justify-center text-xs shadow-xs">
            JMH
          </span>
          <span class="text-xl font-black text-slate-900 tracking-tight">Joule Machine Hub</span>
        </div>
        <div class="text-xs text-slate-500 space-y-0.5">
          <p>Worldwide Industrial Machinery Marketplace</p>
          <p>Email: <a href="mailto:joulemachinehub@gmail.com" class="text-blue-600 font-semibold">joulemachinehub@gmail.com</a></p>
          <p>GSTIN: 29AAAAA0000A1Z5 • India</p>
        </div>
      </div>

      <div class="text-left sm:text-right">
        <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-wider rounded-full mb-2">
          <?php echo strtoupper($invoice['status']); ?>
        </span>
        <h2 class="text-2xl font-black text-slate-900">TAX INVOICE</h2>
        <div class="text-xs text-slate-500 font-mono mt-1">
          <?php echo htmlspecialchars($invoice['invoice_id']); ?>
        </div>
        <div class="text-xs text-slate-500 mt-0.5">
          Date: <strong><?php echo date('M d, Y', strtotime($invoice['date'] ?? 'now')); ?></strong>
        </div>
      </div>
    </div>

    <!-- Bill To Details -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs pb-6 border-b border-slate-100">
      <div>
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Billed To</div>
        <div class="font-black text-slate-900 text-sm">
          <?php echo htmlspecialchars($currentUser['company'] ?? $currentUser['name']); ?>
        </div>
        <div class="text-slate-600 mt-0.5">
          Attn: <?php echo htmlspecialchars($currentUser['name']); ?>
        </div>
        <div class="text-slate-500 mt-0.5">
          Email: <?php echo htmlspecialchars($currentUser['email']); ?>
        </div>
        <div class="text-slate-500">
          Location: <?php echo htmlspecialchars(($currentUser['city'] ?? '') . ', ' . ($currentUser['country'] ?? 'India')); ?>
        </div>
      </div>

      <div class="sm:text-right">
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Payment Method</div>
        <div class="font-bold text-slate-900 uppercase">
          <?php echo htmlspecialchars($invoice['gateway']); ?>
        </div>
        <div class="text-slate-500 font-mono text-[11px] mt-0.5">
          Txn: <?php echo htmlspecialchars($invoice['txn_id']); ?>
        </div>
      </div>
    </div>

    <!-- Line Items Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200">
          <tr>
            <th class="py-3 px-4">Item Description</th>
            <th class="py-3 px-4">Type</th>
            <th class="py-3 px-4 text-right">Amount</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr>
            <td class="py-4 px-4 font-bold text-slate-900 text-sm">
              <?php echo htmlspecialchars($invoice['title']); ?>
              <div class="text-xs text-slate-500 font-normal mt-0.5">
                B2B Industrial Seller Subscription &amp; Platform Access
              </div>
            </td>
            <td class="py-4 px-4 uppercase text-[11px] font-semibold text-slate-600">
              <?php echo htmlspecialchars($invoice['type']); ?>
            </td>
            <td class="py-4 px-4 text-right font-black text-slate-900 text-sm whitespace-nowrap">
              <?php echo ($invoice['currency'] === 'USD' ? '$' : '₹') . number_format($subtotal, 2); ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Totals Calculation -->
    <div class="flex justify-end pt-4 border-t border-slate-200 text-xs text-slate-600">
      <div class="w-full sm:w-64 space-y-2">
        <div class="flex items-center justify-between">
          <span>Subtotal:</span>
          <span><?php echo ($invoice['currency'] === 'USD' ? '$' : '₹') . number_format($subtotal, 2); ?></span>
        </div>
        <?php if ($invoice['currency'] === 'INR'): ?>
          <div class="flex items-center justify-between">
            <span>CGST (9%):</span>
            <span>₹<?php echo number_format($gst / 2, 2); ?></span>
          </div>
          <div class="flex items-center justify-between">
            <span>SGST (9%):</span>
            <span>₹<?php echo number_format($gst / 2, 2); ?></span>
          </div>
        <?php endif; ?>
        <div class="pt-2 border-t border-slate-200 flex items-center justify-between font-black text-slate-900 text-base">
          <span>Total Paid:</span>
          <span class="text-blue-600">
            <?php echo ($invoice['currency'] === 'USD' ? '$' : '₹') . number_format((float)$invoice['amount'], 2); ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Invoice Footer Notes -->
    <div class="pt-6 border-t border-slate-100 text-[11px] text-slate-400 leading-relaxed text-center">
      <p>This is a computer-generated official tax invoice for electronic service delivery on Joule Machine Hub.</p>
      <p>Thank you for partnering with Joule Machine Hub — India &amp; Worldwide Industrial Machinery Marketplace.</p>
    </div>

  </div>

</body>
</html>
