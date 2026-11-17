<?php
$pageTitle = "My Uploaded Machines";
require_once __DIR__ . '/header.php';

$message = '';
$messageType = 'success';

// Handle Actions (Delete, Mark Sold, Mark Active)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $ref = trim($_POST['ref'] ?? '');
    $act = $_POST['action'];

    $jsonFile = __DIR__ . '/../data/seller_products.json';
    $existing = file_exists($jsonFile) ? (json_decode(file_get_contents($jsonFile), true) ?: []) : [];

    if ($act === 'delete') {
        $filtered = [];
        $deleted = false;
        foreach ($existing as $p) {
            if (($p['product_ref'] ?? '') === $ref && (($p['user_id'] ?? '') === $userId || empty($p['user_id']))) {
                $deleted = true;
            } else {
                $filtered[] = $p;
            }
        }
        if ($deleted) {
            file_put_contents($jsonFile, json_encode($filtered, JSON_PRETTY_PRINT));
            $message = "Machine listing #{$ref} deleted successfully.";
        }
    } elseif ($act === 'toggle_sold') {
        foreach ($existing as &$p) {
            if (($p['product_ref'] ?? '') === $ref && (($p['user_id'] ?? '') === $userId || empty($p['user_id']))) {
                $currentStatus = strtolower($p['status'] ?? 'active');
                $p['status'] = ($currentStatus === 'sold') ? 'active' : 'sold';
                $message = ($p['status'] === 'sold') ? "Machine marked as SOLD." : "Machine marked as ACTIVE.";
                break;
            }
        }
        file_put_contents($jsonFile, json_encode($existing, JSON_PRETTY_PRINT));
    } elseif ($act === 'resubmit') {
        foreach ($existing as &$p) {
            if (($p['product_ref'] ?? '') === $ref) {
                $p['status'] = 'pending';
                $message = "Machine #{$ref} resubmitted for admin approval.";
                break;
            }
        }
        file_put_contents($jsonFile, json_encode($existing, JSON_PRETTY_PRINT));
    }
}

// Fetch user's machines
$allUserMachines = getSellerProductsList($userId);
$activeTab = $_GET['status'] ?? 'all';

// Filter machines by tab if requested
$displayMachines = array_filter($allUserMachines, function($m) use ($activeTab) {
    if ($activeTab === 'all') return true;
    $st = strtolower($m['status'] ?? 'pending');
    if ($activeTab === 'approved' && in_array($st, ['approved', 'active', 'published'])) return true;
    if ($activeTab === 'pending' && in_array($st, ['pending', 'pending_approval'])) return true;
    if ($activeTab === 'rejected' && $st === 'rejected') return true;
    if ($activeTab === 'draft' && $st === 'draft') return true;
    if ($activeTab === 'sold' && $st === 'sold') return true;
    return false;
});
?>

<!-- BREADCRUMB & HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
  <div>
    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
      <a href="index.php" class="hover:text-blue-600">Dashboard</a>
      <span>/</span>
      <span class="text-slate-800 font-bold">My Machines</span>
    </div>
    <h1 class="text-2xl font-black text-slate-900 tracking-tight">My Uploaded Machinery</h1>
    <p class="text-xs text-slate-500">Monitor status, edit technical specifications, update pricing, and mark units as sold.</p>
  </div>

  <div class="flex items-center gap-3">
    <a 
      href="add-machine.php" 
      class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-md transition-all inline-flex items-center gap-2"
    >
      <i class="fa-solid fa-plus text-[10px]"></i>
      <span>Add New Machine</span>
    </a>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2.5 animate-in fade-in">
    <i class="fa-solid fa-circle-check text-base text-emerald-600 shrink-0"></i>
    <div><?php echo htmlspecialchars($message); ?></div>
  </div>
<?php endif; ?>

<!-- STATUS FILTER TABS (Section 6 Specification) -->
<div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-200 text-xs">
  <a 
    href="my-machines.php" 
    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-colors <?php echo $activeTab === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>"
  >
    All Machines (<?php echo count($allUserMachines); ?>)
  </a>

  <a 
    href="my-machines.php?status=approved" 
    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-colors <?php echo $activeTab === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-slate-200'; ?>"
  >
    <i class="fa-solid fa-circle-check text-[10px] mr-1"></i>
    Approved (<?php echo $userStats['approved']; ?>)
  </a>

  <a 
    href="my-machines.php?status=pending" 
    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-colors <?php echo $activeTab === 'pending' ? 'bg-amber-500 text-slate-950 shadow-xs' : 'bg-white text-amber-700 hover:bg-amber-50 border border-slate-200'; ?>"
  >
    <i class="fa-solid fa-clock-rotate-left text-[10px] mr-1"></i>
    Pending (<?php echo $userStats['pending']; ?>)
  </a>

  <a 
    href="my-machines.php?status=sold" 
    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-colors <?php echo $activeTab === 'sold' ? 'bg-blue-800 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>"
  >
    Sold (<?php echo $userStats['sold']; ?>)
  </a>

  <a 
    href="my-machines.php?status=rejected" 
    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-colors <?php echo $activeTab === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-rose-700 hover:bg-rose-50 border border-slate-200'; ?>"
  >
    Rejected (<?php echo $userStats['rejected']; ?>)
  </a>

  <a 
    href="my-machines.php?status=draft" 
    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-colors <?php echo $activeTab === 'draft' ? 'bg-slate-700 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>"
  >
    Draft (<?php echo $userStats['draft']; ?>)
  </a>
</div>

<!-- MACHINES TABLE CONTAINER -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
  
  <?php if (empty($displayMachines)): ?>
    <div class="p-12 text-center text-slate-500 space-y-4">
      <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center mx-auto text-2xl text-slate-400">
        <i class="fa-solid fa-boxes-stacked"></i>
      </div>
      <div>
        <h3 class="font-bold text-slate-800 text-sm">No machines found</h3>
        <p class="text-xs text-slate-500 mt-1">There are no machinery listings matching the selected tab.</p>
      </div>
      <a 
        href="add-machine.php" 
        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-xs"
      >
        <i class="fa-solid fa-plus text-[10px]"></i>
        <span>Add New Machine</span>
      </a>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-500 font-semibold text-[11px] uppercase tracking-wider border-b border-slate-200">
          <tr>
            <th class="p-4">Machine Details</th>
            <th class="p-4">Category</th>
            <th class="p-4">Price</th>
            <th class="p-4">Status</th>
            <th class="p-4">Date</th>
            <th class="p-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($displayMachines as $m): ?>
            <?php
            $ref = $m['product_ref'] ?? '';
            $status = strtolower($m['status'] ?? 'pending');
            $statusClass = 'bg-amber-100 text-amber-800 border-amber-200';
            if (in_array($status, ['approved', 'active', 'published'])) $statusClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
            if ($status === 'sold') $statusClass = 'bg-blue-100 text-blue-800 border-blue-200';
            if ($status === 'rejected') $statusClass = 'bg-rose-100 text-rose-800 border-rose-200';
            if ($status === 'draft') $statusClass = 'bg-slate-100 text-slate-800 border-slate-200';
            
            $img = !empty($m['images'][0]) ? $m['images'][0] : 'assets/images/haas-vmc-1.jpg';
            if (!str_starts_with($img, 'assets/') && !str_starts_with($img, 'http')) {
                $img = '../' . ltrim($img, '/');
            } else {
                $img = '../' . ltrim($img, '/');
            }
            ?>
            <tr class="hover:bg-slate-50/80 transition-colors">
              
              <!-- Machine Thumbnail & Title -->
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <img 
                    src="<?php echo htmlspecialchars($img); ?>" 
                    alt="Machine" 
                    class="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0 bg-slate-100"
                    onerror="this.src='../assets/images/haas-vmc-1.jpg'"
                  >
                  <div class="min-w-0">
                    <div class="font-bold text-slate-900 text-sm line-clamp-1">
                      <?php echo htmlspecialchars($m['title'] ?? 'Industrial Machine'); ?>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-0.5">
                      <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($m['brand'] ?? ''); ?></span> • 
                      <span><?php echo htmlspecialchars($m['model'] ?? ''); ?></span> • 
                      <span><?php echo htmlspecialchars($m['year'] ?? ''); ?></span>
                    </div>
                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                      Ref: <?php echo htmlspecialchars($ref); ?>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Category -->
              <td class="p-4 whitespace-nowrap">
                <span class="font-semibold text-slate-800">
                  <?php echo htmlspecialchars($m['category'] ?? 'Machinery'); ?>
                </span>
                <div class="text-[11px] text-slate-500">
                  <?php echo htmlspecialchars($m['city'] ?? ''); ?>, <?php echo htmlspecialchars($m['country'] ?? ''); ?>
                </div>
              </td>

              <!-- Price -->
              <td class="p-4 whitespace-nowrap">
                <div class="font-black text-blue-600 text-sm">
                  <?php echo htmlspecialchars($m['value_raw'] ?? 'Request Price'); ?>
                </div>
                <div class="text-[10px] text-slate-500">
                  <?php echo htmlspecialchars($m['features'][0] ?? 'Negotiable'); ?>
                </div>
              </td>

              <!-- Status Badge (Section 6 Specification) -->
              <td class="p-4 whitespace-nowrap">
                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border <?php echo $statusClass; ?>">
                  <?php echo htmlspecialchars(ucfirst($status)); ?>
                </span>
              </td>

              <!-- Date -->
              <td class="p-4 whitespace-nowrap text-slate-500 text-[11px]">
                <?php echo date('M d, Y', strtotime($m['created_at'] ?? 'now')); ?>
              </td>

              <!-- Actions (Section 6 Specification: Edit, View Live, Mark Sold, Delete) -->
              <td class="p-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  
                  <!-- Edit Button -->
                  <a 
                    href="edit-machine.php?ref=<?php echo urlencode($ref); ?>" 
                    class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors"
                    title="Edit Machine Details"
                  >
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>

                  <!-- View Live Product Page -->
                  <a 
                    href="../product.php?slug=<?php echo urlencode($m['slug'] ?? $ref); ?>" 
                    target="_blank" 
                    class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-blue-600 font-bold transition-colors"
                    title="View Live Listing"
                  >
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                  </a>

                  <!-- Mark as Sold Button Form -->
                  <form action="my-machines.php" method="POST" class="inline">
                    <input type="hidden" name="action" value="toggle_sold">
                    <input type="hidden" name="ref" value="<?php echo htmlspecialchars($ref); ?>">
                    <button 
                      type="submit" 
                      class="p-2 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-700 hover:text-blue-700 font-bold transition-colors cursor-pointer"
                      title="<?php echo $status === 'sold' ? 'Mark as Active' : 'Mark as Sold'; ?>"
                    >
                      <i class="fa-solid fa-tag"></i>
                    </button>
                  </form>

                  <!-- Promote / Feature Machine CTA -->
                  <a 
                    href="checkout.php?type=featured&ref=<?php echo urlencode($ref); ?>" 
                    class="p-2 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold transition-colors"
                    title="Feature this machine"
                  >
                    <i class="fa-solid fa-crown"></i>
                  </a>

                  <!-- Delete Machine Form -->
                  <form action="my-machines.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this machinery listing? This action cannot be undone.');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="ref" value="<?php echo htmlspecialchars($ref); ?>">
                    <button 
                      type="submit" 
                      class="p-2 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-700 hover:text-rose-700 font-bold transition-colors cursor-pointer"
                      title="Delete Listing"
                    >
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  </form>

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
