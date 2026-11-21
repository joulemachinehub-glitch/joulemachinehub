<?php
$pageTitle = "My Enquiries & RFQs";
require_once __DIR__ . '/header.php';

$leadsFile = __DIR__ . '/../data/leads.json';
$allLeads = file_exists($leadsFile) ? (json_decode(file_get_contents($leadsFile), true) ?: []) : [];

// Handle Inquiry Status Update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $leadRef = trim($_POST['lead_ref'] ?? '');
    $newStatus = trim($_POST['status'] ?? 'Contacted');
    
    foreach ($allLeads as &$l) {
        if (($l['lead_ref'] ?? '') === $leadRef) {
            $l['inquiry_status'] = $newStatus;
            $message = "Inquiry status updated to '{$newStatus}'.";
            break;
        }
    }
    file_put_contents($leadsFile, json_encode($allLeads, JSON_PRETTY_PRINT));
}

$statusFilter = $_GET['status'] ?? 'all';
$filteredLeads = array_filter($allLeads, function($l) use ($statusFilter) {
    if ($statusFilter === 'all') return true;
    $st = strtolower($l['inquiry_status'] ?? 'new');
    return ($st === strtolower($statusFilter));
});
?>

<!-- BREADCRUMB & HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
  <div>
    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
      <a href="/dashboard" class="hover:text-blue-600">Dashboard</a>
      <span>/</span>
      <span class="text-slate-800 font-bold">My Enquiries</span>
    </div>
    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Buyer Enquiries &amp; RFQ Leads</h1>
    <p class="text-xs text-slate-500">Incoming inquiries from verified manufacturing buyers for your plant machinery.</p>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2.5">
    <i class="fa-solid fa-circle-check text-base text-emerald-600 shrink-0"></i>
    <div><?php echo htmlspecialchars($message); ?></div>
  </div>
<?php endif; ?>

<!-- STATUS FILTER PILLS (Section 7 Specification) -->
<div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-200 text-xs">
  <a 
    href="enquiries.php" 
    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-colors <?php echo $statusFilter === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>"
  >
    All Leads (<?php echo count($allLeads); ?>)
  </a>
  <a 
    href="enquiries.php?status=new" 
    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-colors <?php echo $statusFilter === 'new' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white text-purple-700 hover:bg-purple-50 border border-slate-200'; ?>"
  >
    New
  </a>
  <a 
    href="enquiries.php?status=contacted" 
    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-colors <?php echo $statusFilter === 'contacted' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-blue-700 hover:bg-blue-50 border border-slate-200'; ?>"
  >
    Contacted
  </a>
  <a 
    href="enquiries.php?status=negotiation" 
    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-colors <?php echo $statusFilter === 'negotiation' ? 'bg-amber-500 text-slate-950 shadow-xs' : 'bg-white text-amber-700 hover:bg-amber-50 border border-slate-200'; ?>"
  >
    Negotiation
  </a>
  <a 
    href="enquiries.php?status=closed" 
    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-colors <?php echo $statusFilter === 'closed' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-slate-200'; ?>"
  >
    Closed Deal
  </a>
</div>

<!-- ENQUIRIES TABLE CONTAINER -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
  <?php if (empty($filteredLeads)): ?>
    <div class="p-12 text-center text-slate-500 space-y-3">
      <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center mx-auto text-2xl text-slate-400">
        <i class="fa-solid fa-comments"></i>
      </div>
      <div>
        <h3 class="font-bold text-slate-800 text-sm">No enquiries found</h3>
        <p class="text-xs text-slate-500 mt-1">When buyers submit an RFQ on your machine pages, enquiries appear here in real time.</p>
      </div>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-500 font-semibold text-[11px] uppercase tracking-wider border-b border-slate-200">
          <tr>
            <th class="p-4">Buyer / Plant Info</th>
            <th class="p-4">Target Machine</th>
            <th class="p-4">Inquiry Note</th>
            <th class="p-4">Status</th>
            <th class="p-4">Received</th>
            <th class="p-4 text-right">Direct Connect</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($filteredLeads as $lead): ?>
            <?php
            $leadStatus = strtolower($lead['inquiry_status'] ?? 'new');
            $badgeColor = 'bg-purple-100 text-purple-800 border-purple-200';
            if ($leadStatus === 'contacted') $badgeColor = 'bg-blue-100 text-blue-800 border-blue-200';
            if ($leadStatus === 'negotiation') $badgeColor = 'bg-amber-100 text-amber-800 border-amber-200';
            if ($leadStatus === 'closed') $badgeColor = 'bg-emerald-100 text-emerald-800 border-emerald-200';

            $cleanPhone = preg_replace('/[^0-9]/', '', $lead['phone'] ?? '');
            $waMsg = urlencode("Hello " . ($lead['name'] ?? 'Sir') . ", regarding your inquiry on Joule Machine Hub for " . ($lead['machine_title'] ?? 'our machinery') . ", we would like to discuss specifications and commercial terms.");
            ?>
            <tr class="hover:bg-slate-50/80 transition-colors">
              
              <!-- Buyer Details -->
              <td class="p-4">
                <div class="font-bold text-slate-900 text-sm">
                  <?php echo htmlspecialchars($lead['name'] ?? 'Industrial Buyer'); ?>
                </div>
                <div class="text-[11px] text-slate-600 font-medium">
                  <?php echo htmlspecialchars($lead['company'] ?? 'Manufacturing Plant'); ?>
                </div>
                <div class="text-[10px] text-slate-400 mt-0.5">
                  <?php echo htmlspecialchars($lead['city'] ?? ''); ?>
                </div>
              </td>

              <!-- Machine -->
              <td class="p-4">
                <div class="font-bold text-slate-800 text-xs truncate max-w-[200px]">
                  <?php echo htmlspecialchars($lead['machine_title'] ?? 'General Machinery Enquiry'); ?>
                </div>
                <div class="text-[10px] text-blue-600 font-semibold mt-0.5">
                  <?php echo htmlspecialchars($lead['machine_price'] ?? ''); ?>
                </div>
              </td>

              <!-- Message / Note -->
              <td class="p-4">
                <p class="text-xs text-slate-600 line-clamp-2 max-w-xs">
                  <?php echo htmlspecialchars($lead['note'] ?? 'Buyer requested detailed inspection report and commercial quotation.'); ?>
                </p>
              </td>

              <!-- Status Dropdown Form -->
              <td class="p-4 whitespace-nowrap">
                <form action="enquiries.php" method="POST" class="inline">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="lead_ref" value="<?php echo htmlspecialchars($lead['lead_ref'] ?? ''); ?>">
                  <select 
                    name="status" 
                    onchange="this.form.submit()" 
                    class="text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded-full border <?php echo $badgeColor; ?> cursor-pointer focus:outline-none"
                  >
                    <option value="New" <?php echo $leadStatus === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="Contacted" <?php echo $leadStatus === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                    <option value="Negotiation" <?php echo $leadStatus === 'negotiation' ? 'selected' : ''; ?>>Negotiation</option>
                    <option value="Closed" <?php echo $leadStatus === 'closed' ? 'selected' : ''; ?>>Closed Deal</option>
                  </select>
                </form>
              </td>

              <!-- Date -->
              <td class="p-4 whitespace-nowrap text-[11px] text-slate-500">
                <?php echo date('M d, Y', strtotime($lead['created_at'] ?? 'now')); ?>
              </td>

              <!-- Direct Connect Actions (WhatsApp / Call / Email) -->
              <td class="p-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <?php if (!empty($cleanPhone)): ?>
                    <a 
                      href="https://wa.me/<?php echo $cleanPhone; ?>?text=<?php echo $waMsg; ?>" 
                      target="_blank" 
                      class="p-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold transition-colors"
                      title="Chat on WhatsApp"
                    >
                      <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    <a 
                      href="tel:<?php echo $cleanPhone; ?>" 
                      class="p-2 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold transition-colors"
                      title="Call Buyer Directly"
                    >
                      <i class="fa-solid fa-phone"></i>
                    </a>
                  <?php endif; ?>
                  
                  <?php if (!empty($lead['email'])): ?>
                    <a 
                      href="mailto:<?php echo htmlspecialchars($lead['email']); ?>?subject=<?php echo urlencode("Regarding your machinery enquiry: " . ($lead['machine_title'] ?? '')); ?>" 
                      class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors"
                      title="Send Email"
                    >
                      <i class="fa-regular fa-envelope"></i>
                    </a>
                  <?php endif; ?>
                </div>
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
