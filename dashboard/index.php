<?php
$pageTitle = "Dashboard Overview";
require_once __DIR__ . '/header.php';

// Get recent products uploaded by user (up to 5)
$userProducts = getSellerProductsList($userId);
$recentProducts = array_slice($userProducts, 0, 5);

// Get recent inquiries (up to 5)
$leadsFile = __DIR__ . '/../data/leads.json';
$leads = file_exists($leadsFile) ? (json_decode(file_get_contents($leadsFile), true) ?: []) : [];
$recentLeads = array_slice($leads, 0, 5);
?>

<?php if (!empty($_GET['registered'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof gtag === 'function') {
    gtag('event', 'seller_registration', {
      user_id: <?php echo json_encode((string)$userId); ?>,
      role: 'seller'
    });
  }
});
</script>
<?php endif; ?>

<?php if (!empty($_GET['plan_upgraded']) || !empty($_GET['plan_purchased'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof gtag === 'function') {
    gtag('event', 'seller_plan_purchase', {
      user_id: <?php echo json_encode((string)$userId); ?>,
      plan: <?php echo json_encode($userSub['plan_name'] ?? ''); ?>
    });
  }
});
</script>
<?php endif; ?>

<!-- TOP WELCOME & MEMBERSHIP STATUS BANNER -->
<div class="bg-gradient-to-r from-joule-blue to-blue-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border-b-4 border-joule-yellow">
  <!-- Accent icon -->
  <div class="absolute -right-6 -bottom-6 text-white/5 text-8xl pointer-events-none">
    <i class="fa-solid fa-industry"></i>
  </div>

  <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
    <div class="space-y-2">
      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-yellow-400 text-slate-950">
          <?php echo htmlspecialchars($userSub['plan_name']); ?>
        </span>
        <span class="text-xs text-blue-200">
          User ID: <strong><?php echo htmlspecialchars($userId); ?></strong>
        </span>
      </div>
      <h1 class="text-2xl sm:text-3xl font-black text-white">
        Hello, <?php echo htmlspecialchars($currentUser['name'] ?? 'Partner'); ?>!
      </h1>
      <p class="text-xs sm:text-sm text-blue-100 max-w-xl">
        Manage your industrial machinery inventory, monitor incoming buyer RFQs, and track your listing performance across global plant buyers.
      </p>
    </div>

    <!-- Listing Quota Meter -->
    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/20 min-w-[240px]">
      <div class="flex items-center justify-between text-xs mb-2">
        <span class="text-blue-200">Listing Quota:</span>
        <span class="font-bold text-white">
          <?php echo $userStats['total']; ?> / <?php echo $userStats['listing_limit']; ?> Used
        </span>
      </div>
      
      <!-- Progress Bar -->
      <?php
      $pct = ($userStats['listing_limit'] > 0) ? min(100, round(($userStats['total'] / $userStats['listing_limit']) * 100)) : 0;
      $barColor = $pct >= 90 ? 'bg-rose-500' : ($pct >= 70 ? 'bg-amber-400' : 'bg-emerald-400');
      ?>
      <div class="w-full bg-black/20 rounded-full h-2.5 overflow-hidden mb-3">
        <div class="<?php echo $barColor; ?> h-2.5 rounded-full transition-all duration-500" style="width: <?php echo $pct; ?>%;"></div>
      </div>

      <div class="flex items-center justify-between gap-2">
        <span class="text-[11px] text-blue-200">
          <?php echo $userStats['remaining']; ?> listings left
        </span>
        <a 
          href="membership.php" 
          class="text-xs font-black text-slate-950 bg-joule-yellow hover:bg-amber-400 px-3 py-1.5 rounded-xl shadow-xs transition-all inline-flex items-center gap-1"
        >
          <i class="fa-solid fa-bolt text-[10px]"></i>
          <span>Upgrade</span>
        </a>
      </div>
    </div>
  </div>
</div>

<!-- 4 DASHBOARD OVERVIEW CARDS (Section 4 Specification) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
  
  <!-- Card 1: Total Machines -->
  <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-xs hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Machines</span>
      <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
        <i class="fa-solid fa-boxes-stacked"></i>
      </div>
    </div>
    <div class="text-2xl sm:text-3xl font-black text-slate-900">
      # <?php echo $userStats['total']; ?>
    </div>
    <p class="text-[11px] text-slate-500 mt-1">Uploaded to marketplace</p>
  </div>

  <!-- Card 2: Approved -->
  <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-xs hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Approved</span>
      <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
        <i class="fa-solid fa-circle-check"></i>
      </div>
    </div>
    <div class="text-2xl sm:text-3xl font-black text-emerald-600">
      # <?php echo $userStats['approved']; ?>
    </div>
    <p class="text-[11px] text-slate-500 mt-1">Live &amp; visible to buyers</p>
  </div>

  <!-- Card 3: Pending Approval -->
  <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-xs hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Approval</span>
      <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
        <i class="fa-solid fa-clock-rotate-left"></i>
      </div>
    </div>
    <div class="text-2xl sm:text-3xl font-black text-amber-600">
      # <?php echo $userStats['pending']; ?>
    </div>
    <p class="text-[11px] text-slate-500 mt-1">Under verification review</p>
  </div>

  <!-- Card 4: Total Enquiries -->
  <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-xs hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Enquiries</span>
      <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base">
        <i class="fa-solid fa-comments"></i>
      </div>
    </div>
    <div class="text-2xl sm:text-3xl font-black text-purple-600">
      # <?php echo $userStats['total_enquiries']; ?>
    </div>
    <p class="text-[11px] text-slate-500 mt-1">Buyer RFQs &amp; inquiries</p>
  </div>

</div>

<!-- QUICK ACTION BUTTONS -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
  <a 
    href="add-machine.php" 
    class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-3.5 px-4 rounded-2xl flex items-center justify-center gap-2 shadow-md shadow-blue-600/20 transition-all text-center"
  >
    <i class="fa-solid fa-plus text-sm"></i>
    <span>Add New Machine</span>
  </a>

  <a 
    href="my-machines.php" 
    class="bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs py-3.5 px-4 rounded-2xl border border-slate-200 flex items-center justify-center gap-2 shadow-xs transition-all text-center"
  >
    <i class="fa-solid fa-boxes-stacked text-blue-600 text-sm"></i>
    <span>Manage Inventory</span>
  </a>

  <a 
    href="enquiries.php" 
    class="bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs py-3.5 px-4 rounded-2xl border border-slate-200 flex items-center justify-center gap-2 shadow-xs transition-all text-center"
  >
    <i class="fa-solid fa-inbox text-purple-600 text-sm"></i>
    <span>View Enquiries</span>
  </a>

  <a 
    href="membership.php" 
    class="bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs py-3.5 px-4 rounded-2xl border border-slate-200 flex items-center justify-center gap-2 shadow-xs transition-all text-center"
  >
    <i class="fa-solid fa-crown text-amber-500 text-sm"></i>
    <span>Upgrade Plan</span>
  </a>
</div>

<!-- TWO-COLUMN SECTION: Recent Uploaded Machines & Recent Enquiries -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  
  <!-- Left Column: Recent Uploaded Machines -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-slate-200 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-boxes-stacked text-blue-600"></i>
        <h3 class="font-bold text-sm text-slate-900">Recent Machines</h3>
      </div>
      <a href="my-machines.php" class="text-xs font-bold text-blue-600 hover:text-blue-800">
        View All (<?php echo $userStats['total']; ?>) &rarr;
      </a>
    </div>

    <?php if (empty($recentProducts)): ?>
      <div class="p-8 text-center text-slate-500 space-y-3">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400 text-xl">
          <i class="fa-solid fa-box-open"></i>
        </div>
        <p class="text-xs">You haven't uploaded any machinery yet.</p>
        <a 
          href="add-machine.php" 
          class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl shadow-xs"
        >
          <i class="fa-solid fa-plus text-[10px]"></i>
          <span>Upload First Machine</span>
        </a>
      </div>
    <?php else: ?>
      <div class="divide-y divide-slate-100 overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-500 font-semibold text-[11px] uppercase tracking-wider">
            <tr>
              <th class="p-3.5">Machine</th>
              <th class="p-3.5">Status</th>
              <th class="p-3.5 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($recentProducts as $prod): ?>
              <?php
              $status = strtolower($prod['status'] ?? 'pending');
              $badgeClass = 'bg-amber-100 text-amber-800';
              if (in_array($status, ['approved', 'active', 'published'])) $badgeClass = 'bg-emerald-100 text-emerald-800';
              if ($status === 'sold') $badgeClass = 'bg-blue-100 text-blue-800';
              if ($status === 'rejected') $badgeClass = 'bg-rose-100 text-rose-800';
              ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="p-3.5">
                  <div class="font-bold text-slate-900 truncate max-w-[200px]">
                    <?php echo htmlspecialchars($prod['title'] ?? 'Industrial Machine'); ?>
                  </div>
                  <div class="text-[11px] text-slate-500">
                    <?php echo htmlspecialchars($prod['category'] ?? ''); ?> • <?php echo htmlspecialchars($prod['value_raw'] ?? ''); ?>
                  </div>
                </td>
                <td class="p-3.5">
                  <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $badgeClass; ?>">
                    <?php echo htmlspecialchars(ucfirst($status)); ?>
                  </span>
                </td>
                <td class="p-3.5 text-right">
                  <a 
                    href="edit-machine.php?ref=<?php echo urlencode($prod['product_ref'] ?? ''); ?>" 
                    class="text-blue-600 hover:text-blue-800 font-bold text-xs"
                  >
                    Edit
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Right Column: Recent Buyer Enquiries -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-slate-200 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-comments text-purple-600"></i>
        <h3 class="font-bold text-sm text-slate-900">Recent Enquiries</h3>
      </div>
      <a href="enquiries.php" class="text-xs font-bold text-blue-600 hover:text-blue-800">
        View All (<?php echo $userStats['total_enquiries']; ?>) &rarr;
      </a>
    </div>

    <?php if (empty($recentLeads)): ?>
      <div class="p-8 text-center text-slate-500 space-y-3">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400 text-xl">
          <i class="fa-solid fa-inbox"></i>
        </div>
        <p class="text-xs">No buyer inquiries received yet. Approved machines start receiving RFQs directly.</p>
      </div>
    <?php else: ?>
      <div class="divide-y divide-slate-100 overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-500 font-semibold text-[11px] uppercase tracking-wider">
            <tr>
              <th class="p-3.5">Buyer</th>
              <th class="p-3.5">Machine</th>
              <th class="p-3.5 text-right">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($recentLeads as $lead): ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="p-3.5">
                  <div class="font-bold text-slate-900">
                    <?php echo htmlspecialchars($lead['name'] ?? 'Buyer'); ?>
                  </div>
                  <div class="text-[11px] text-slate-500">
                    <?php echo htmlspecialchars($lead['company'] ?? 'Plant Purchaser'); ?>
                  </div>
                </td>
                <td class="p-3.5">
                  <div class="text-slate-800 font-medium truncate max-w-[180px]">
                    <?php echo htmlspecialchars($lead['machine_title'] ?? 'General Machinery Enquiry'); ?>
                  </div>
                </td>
                <td class="p-3.5 text-right text-[11px] text-slate-500 whitespace-nowrap">
                  <?php echo date('M d', strtotime($lead['created_at'] ?? 'now')); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php
require_once __DIR__ . '/footer.php';
?>
